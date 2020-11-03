<?php

/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}




/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be profiles.
 */
class EbayOrdersTable extends WP_List_Table {

    var $total_items;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'ebay_order',     //singular name of the listed records
            'plural'    => 'orders',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
    
    
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'order_id':
            case 'buyer_userid':
            case 'buyer_name':
            case 'PaymentMethod':
            case 'eBayPaymentStatus':
            case 'CheckoutStatus':
            case 'CompleteStatus':
            case 'status':
                return $item[$column_name];
            case 'total':
                return number_format( $item[$column_name], 2, ',', '.' );
            case 'date_created':
            case 'LastTimeModified':
            	// use date format from wp
                //$date = mysql2date( get_option('date_format'), $item[$column_name] );
                //$time = mysql2date( 'H:i', $item[$column_name] );
                $date = get_date_from_gmt( $item[ $column_name ], get_option( 'date_format' ) );
                $time = get_date_from_gmt( $item[ $column_name ], 'H:i' );
                return sprintf('%1$s <br><span style="color:silver">%2$s</span>', $date, $time );
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
        
    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (profile title only)
     **************************************************************************/
    function column_details($item){
        
        // get current page with paging as url param
        $page = wple_clean($_REQUEST['page']);
        if ( isset( $_REQUEST['paged'] ))           $page .= '&paged='.wple_clean($_REQUEST['paged']);
        if ( isset( $_REQUEST['s'] ))               $page .= '&s=' . urlencode( wple_clean($_REQUEST['s']) );
        if ( isset( $_REQUEST['order_status'] ))    $page .= '&order_status='.wple_clean($_REQUEST['order_status']);

        //Build row actions
        $actions = array(
            'view_ebay_order_details' => sprintf('<a href="?page=%s&action=%s&ebay_order=%s&_wpnonce=%s&width=600&height=470" class="thickbox">%s</a>',$page,'view_ebay_order_details',$item['id'], wp_create_nonce( 'wplister_view_order_details' ), __( 'Details', 'wp-lister-for-ebay' )),
            // 'create_order' => sprintf('<a href="?page=%s&action=%s&ebay_order=%s">%s</a>',$page,'create_order',$item['id'],__( 'Create Order', 'wp-lister-for-ebay' )),
            // 'edit'         => sprintf('<a href="?page=%s&action=%s&auction=%s">%s</a>',$page,'edit',$item['id'],__( 'Edit', 'wp-lister-for-ebay' )),
        );


        // try to find created order
        $order_post_id = $item['post_id'];
        $order_exists  = false;
        $order_msg     = '';

        if ( $order_post_id ) {

            $order = OrderWrapper::getOrder( $order_post_id );
            if ( $order ) {
                //$status

                // order exists - but might be trashed
                if ( $order->get_status() == 'trash' ) {
                    $order_msg = '<br><small style="color:darkred;">Order #'.$order_post_id.' has been trashed.</small>';
                } else {
                    $order_exists = true;
                    $order_msg = '<br><small>Order #'.$order->get_order_number().' is '.$order->get_status().'.</small>';
                }

            } else {
                // order does not exist - probably deleted
                $order_msg = '<br><small style="color:darkred;">Order #'.$order_post_id.' has been deleted.</small>';
            }

        }

        // create or edit order link
        if ( $order_exists ) {
            $actions['edit_order'] = sprintf('<a href="post.php?action=%s&post=%s">%s</a>','edit',$item['post_id'],__( 'View Order', 'wp-lister-for-ebay' ));
        } else {
            $actions['wple_create_order'] = sprintf('<a href="?page=%s&action=%s&ebay_order=%s&_wpnonce=%s">%s</a>',$page,'wple_create_order',$item['id'], wp_create_nonce( 'wplister_create_order' ), __( 'Create Order', 'wp-lister-for-ebay' ));
        }

        // free version can't create orders
        if ( WPLE_IS_LITE_VERSION ) unset( $actions['wple_create_order'] );


        // item title
        $title = $item['buyer_name'];
        if ( $item['buyer_userid'] ) {
            $title .= ' <i style="color:silver">'.$item['buyer_userid'].'</i>';
        }

        $item_details = maybe_unserialize( $item['details'] );
        if ( $item_details ) {
            if ( $item_details->IsMultiLegShipping ) {
                $title .= '<br><small>Global Shipping Program</small>';
            }
            if ( $item_details->ContainseBayPlusTransaction == true ) {
                $title .= '<br><small><strong>eBay Plus</strong></small>';
            }
        }

        // add warning message if details column is corrupted
        if ( $item['details'] && ! is_object( $item_details ) ) {
            $tip_msg = 'This order is missing some additional data, possibly due to a problem with the database.<br><br>Please use the <i>Update from eBay</i> bulk action to reload the order data, which should make this warning disappear.<br><br>If that does not fix the problem, you may have to run the <i>Convert tables to utf8mb4</i> process on the <i>eBay &raquo; Tools</i> page first.';
            $img_url = WPLE_PLUGIN_URL . 'img/error.gif';
            $title  .= '&nbsp;<img src="'.$img_url.'" style="height:12px; padding:0;" class="tips" data-tip="'.$tip_msg.'"/>&nbsp;';            
        }

        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/ $title . $order_msg,
            /*$2%s*/ $this->row_actions($actions)
        );
    }

    function column_total($item){
        
        // count purchased items
        $items = maybe_unserialize( $item['items'] );
        if ( is_array($items) ) {
            $item_count = 0;
            foreach ($items as $line_item) {
                $item_count += $line_item['quantity'];
            }
            $item_count .= $item_count == 1 ? ' item' : ' items';
        } else {
            $item_count = '&mdash;';
        }

        if ( $item['currency'] ) {
            $display_price = $item['total'] .' '. $item['currency'];
        } else {
            $display_price = wc_price( $item['total'] );
        }

        // check if WP-Lister did reduce stock for purchased items
        $history = maybe_unserialize( $item['history'] );
        $history = is_array( $history ) ? $history : array();
        $icon    = '';
        $tipmsgs = '';

        foreach ( $history as $record ) {
            if ( $record->action == 'reduce_stock' ) {
                if ( ! isset( $record->details['product_id'] ) ) continue;

                // build tooltip message
                $qty_purchased = isset( $record->details['quantity_purchased'] ) ? $record->details['quantity_purchased'] : '?';
                $tipmsgs      .= 'Stock level for product #'.$record->details['product_id'].' has been reduced by '.$qty_purchased.' unit(s).' . '<br>';
            }
        } // each history record

        if ( $tipmsgs ) {
            $img_url  = WPLE_PLUGIN_URL . 'img/icon-success.png';
            $icon     = '&nbsp;<img src="'.$img_url.'" style="height:12px; padding:0;" class="tips" data-tip="'.$tipmsgs.'"/>';                            
        }


        return sprintf('%1$s <br><span style="color:silver">%2$s</span>%3$s',
            /*$1%s*/ $display_price,
            /*$2%s*/ $item_count,
            /*$3%s*/ $icon 
        );
    }

    function column_buyer_name($item){
        //Return buyer name and ID
        return sprintf('%1$s <br><span style="color:silver">%2$s</span>',
            /*$1%s*/ $item['buyer_name'],
            /*$2%s*/ $item['buyer_userid']
        );
    }
    function column_PaymentMethod($item){

        switch( $item['eBayPaymentStatus'] ){
            case 'NoPaymentFailure':
                $color = 'silver';
                $value = __( 'Payment complete', 'wp-lister-for-ebay' );
                break;
            case 'PayPalPaymentInProcess':
                $color = 'darkorange';
                $value = __( 'Payment in Progress', 'wp-lister-for-ebay' );
                break;
            case 'BuyerECheckBounced':
                $color = 'darkred';
                $value = __( 'The buyer\'s eCheck bounced', 'wp-lister-for-ebay' );
                break;
            default:
                $color = 'darkorange';
                $value = $item['eBayPaymentStatus'];
        }

        $item_details = maybe_unserialize( $item['details'] );
        $paid = '';
        if ( $item_details ) {
            $PaidTime = self::convertEbayDateToSql( $item_details->PaidTime );
            if ( $PaidTime ) $paid = __( 'Paid', 'wp-lister-for-ebay' ) .': '. sprintf( __('%s ago', 'wp-lister-for-ebay' ), human_time_diff( strtotime( $PaidTime ) ) );
        }
        
        // return formatted html
        return sprintf('<span>%1$s</span><br><span style="color:%2$s">%3$s</span><br><small style="color:gray;">%4$s</small>',
            /*$1%s*/ $item['PaymentMethod'],
            /*$2%s*/ $color,
            /*$3%s*/ $value,
            /*$4%s*/ $paid
        );
    }

    function column_CompleteStatus($item){

        switch( $item['CompleteStatus'] ){
            case 'Active':
                $color = 'darkorange';
                $value = __( 'Active', 'wp-lister-for-ebay' );
                break;
            case 'Completed':
                $color = 'green';
                $value = __( 'Completed', 'wp-lister-for-ebay' );
                break;
            case 'Cancelled':
                $color = 'gray';
                $value = __( 'Cancelled', 'wp-lister-for-ebay' );
                break;
            default:
                $color = 'black';
                $value = $item['CompleteStatus'];
        }

        $item_details = maybe_unserialize( $item['details'] );
        $shipped_status = '';
        if ( $item_details ) {
            $ShippedTime = self::convertEbayDateToSql( $item_details->ShippedTime );
            if ( $ShippedTime ) $shipped_status = __( 'Shipped', 'wp-lister-for-ebay' ) .': '. sprintf( __('%s ago', 'wp-lister-for-ebay' ), human_time_diff( strtotime( $ShippedTime ) ) );
        }

        // return formatted html
        return sprintf('<span style="color:%1$s">%2$s</span><br><span style="color:%3$s">%4$s</span><br><small style="color:gray;">%5$s</small>',
            /*$1%s*/ $color,
            /*$2%s*/ $value,
            /*$2%s*/ 'silver',
            /*$2%s*/ $item['CheckoutStatus'],
            /*$2%s*/ $shipped_status
        );
	}
	  
    function column_account($item) {
        $account_title = isset( WPLE()->accounts[ $item['account_id'] ] ) ? WPLE()->accounts[ $item['account_id'] ]->title : 'NONE';
        return sprintf('%1$s <br><span style="color:silver">%2$s</span>',
            /*$1%s*/ $account_title,
            /*$2%s*/ EbayController::getEbaySiteCode( $item['site_id'] )
        );
    }
    
    // convert 2013-02-14T08:00:58.000Z to 2013-02-14 08:00:58
    static public function convertEbayDateToSql( $ebay_date ) {
        $search = array( 'T', '.000Z' );
        $replace = array( ' ', '' );
        $sql_date = str_replace( $search, $replace, $ebay_date );
        return $sql_date;
    }


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (profile title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("profile")
            /*$2%s*/ $item['id']       			//The value of the checkbox should be the record's id
        );
    }
    
    
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        		=> '<input type="checkbox" />', //Render a checkbox instead of text
            'date_created'		=> __( 'Created at', 'wp-lister-for-ebay' ),
            // 'item_id'  			=> __( 'eBay ID', 'wp-lister-for-ebay' ),
            // 'item_title'  		=> __( 'Product', 'wp-lister-for-ebay' ),
            'details'           => __( 'Buyer', 'wp-lister-for-ebay' ),
            'total'				=> __( 'Total', 'wp-lister-for-ebay' ),
            // 'buyer_name'		=> __( 'Name', 'wp-lister-for-ebay' ),
            'PaymentMethod'		=> __( 'Payment method', 'wp-lister-for-ebay' ),
            'CompleteStatus'	=> __( 'Status', 'wp-lister-for-ebay' ),
            'order_id'          => __( 'Order ID', 'wp-lister-for-ebay' ),
            // 'status'		 	=> __( 'Status', 'wp-lister-for-ebay' ),
            'LastTimeModified'	=> __( 'Last change', 'wp-lister-for-ebay' ),
            'account'           => __( 'Account', 'wp-lister-for-ebay' ),
        );
        if ( ! WPLE()->multi_account ) unset( $columns['account'] );

        return $columns;
    }
    
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'date_created'  	=> array('date_created',false),     //true means its already sorted
            'LastTimeModified' 	=> array('LastTimeModified',false)
        );
        return $sortable_columns;
    }
    
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'wple_bulk_update_orders' 	=> __( 'Update selected orders from eBay', 'wp-lister-for-ebay' ),
            'wple_bulk_delete_orders'   => __( 'Delete selected orders', 'wp-lister-for-ebay' )
        );

        // wpl_delete_order orders is only for developers
        if ( ! get_option('wplister_log_level') )
            unset( $actions['wple_bulk_delete_orders'] );

        return $actions;
    }
    
    
    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        global $wbdb;
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            #wp_die('Items deleted (or they would be if we had items to delete)!');
            #$wpdb->query("DELETE FROM {$wpdb->prefix}ebay_auctions WHERE id = ''",)
        }

        if( 'update'===$this->current_action() ) {
			#echo "<br>verify handler<br>";			
        }
        
    }
    

    // status filter links
    // http://wordpress.stackexchange.com/questions/56883/how-do-i-create-links-at-the-top-of-wp-list-table
    function get_views(){
        $views      = array();
        $current    = ( !empty($_REQUEST['order_status']) ? wple_clean($_REQUEST['order_status']) : 'all');
        $current_wc = ( !empty($_REQUEST['has_wc_order']) ? wple_clean($_REQUEST['has_wc_order']) : '');
        $base_url   = esc_url_raw( remove_query_arg( array( 'action', 'order', 'order_status' ) ) );

        // handle search query
        if ( isset($_REQUEST['s']) && wple_clean($_REQUEST['s']) ) {
            $base_url = add_query_arg( 's', wple_clean($_REQUEST['s']), $base_url );
        }

        // get order status summary
        $om = new EbayOrdersModel();
        $summary = $om->getStatusSummary();

        // All link
        $class = ($current == 'all' ? ' class="current"' :'');
        $all_url = remove_query_arg( 'order_status', $base_url );
        $views['all']  = "<a href='{$all_url }' {$class} >".__( 'All', 'wp-lister-for-ebay' )."</a>";
        $views['all'] .= '<span class="count">('.$summary->total_items.')</span>';

        // Shipped link
        $Shipped_url = add_query_arg( 'shipped', 'yes', $base_url );
        $class = ($current == 'Shipped' ? ' class="current"' :'');
        $views['Shipped'] = "<a href='{$Shipped_url}' {$class} >".__( 'Shipped', 'wp-lister-for-ebay' )."</a>";
        if ( isset($summary->shipped) ) $views['Shipped'] .= '<span class="count">('.$summary->shipped.')</span>';

        // Unshipped link
        $Unshipped_url = add_query_arg( 'shipped', 'no', $base_url );
        $class = ($current == 'Unshipped' ? ' class="current"' :'');
        $views['Unshipped'] = "<a href='{$Unshipped_url}' {$class} >".__( 'Unshipped', 'wp-lister-for-ebay' )."</a>";
        if ( isset($summary->unshipped) ) $views['Unshipped'] .= '<span class="count">('.$summary->unshipped.')</span>';

        // Completed link
        $Completed_url = add_query_arg( 'order_status', 'Completed', $base_url );
        $class = ($current == 'Completed' ? ' class="current"' :'');
        $views['Completed'] = "<a href='{$Completed_url}' {$class} >".__( 'Completed', 'wp-lister-for-ebay' )."</a>";
        if ( isset($summary->Completed) ) $views['Completed'] .= '<span class="count">('.$summary->Completed.')</span>';

        // Active link
        $Active_url = add_query_arg( 'order_status', 'Active', $base_url );
        $class = ($current == 'Active' ? ' class="current"' :'');
        $views['Active'] = "<a href='{$Active_url}' {$class} >".__( 'Active', 'wp-lister-for-ebay' )."</a>";
        if ( isset($summary->Active) ) $views['Active'] .= '<span class="count">('.$summary->Active.')</span>';

        // Cancelled link
        $Cancelled_url = add_query_arg( 'order_status', 'Cancelled', $base_url );
        $class = ($current == 'Cancelled' ? ' class="current"' :'');
        $views['Cancelled'] = "<a href='{$Cancelled_url}' {$class} >".__( 'Cancelled', 'wp-lister-for-ebay' )."</a>";
        if ( isset($summary->Cancelled) ) $views['Cancelled'] .= '<span class="count">('.$summary->Cancelled.')</span>';

	    // Refunded link
	    $Refunded_url = add_query_arg( 'order_status', 'Refunded', $base_url );
	    $class = ($current == 'Refunded' ? ' class="current"' :'');
	    $views['Refunded'] = "<a href='{$Refunded_url}' {$class} >".__( 'Refunded', 'wp-lister-for-ebay' )."</a>";
	    if ( isset($summary->Refunded) ) $views['Refunded'] .= '<span class="count">('.$summary->Refunded.')</span>';


        return $views;
    }    


    function extra_tablenav( $which ) {
        if ( 'top' != $which ) return;
        $account_id = ( isset($_REQUEST['account_id']) ? intval($_REQUEST['account_id']) : false);
        ?>
        <div class="alignleft actions" style="">

            <?php if ( WPLE()->multi_account ) : ?>

            <select name="account_id">
                <option value=""><?php _e( 'All accounts', 'wp-lister-for-ebay' ) ?></option>
                <?php foreach ( WPLE()->accounts as $account ) : ?>
                    <option value="<?php echo $account->id ?>"
                        <?php if ( $account_id == $account->id ) echo 'selected'; ?>
                        ><?php echo $account->title ?></option>
                <?php endforeach; ?>
            </select>            

            <input type="submit" name="" id="post-query-submit" class="button" value="Filter">

            <?php endif; ?>

        </div>
        <?php
    }

    
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        
        // process bulk actions
        $this->process_bulk_action();
                        
        // get pagination state
        $current_page = $this->get_pagenum();
        $per_page = $this->get_items_per_page('orders_per_page', 20);

        // regard max table rows limit
        if ( $max_per_page = get_option( 'wplister_force_table_items_limit' ) )
            $per_page = min( $per_page, $max_per_page );
                
        // define columns
        $this->_column_headers = $this->get_column_info();
        
        // fetch profiles from model
        $ordersModel = new EbayOrdersModel();
        $this->items = $ordersModel->getPageItems( $current_page, $per_page );
        $total_items = $ordersModel->total_items;

        // register our pagination options & calculations.
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );

    }
    
}

