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
class TransactionsTable extends WP_List_Table {

    var $total_items;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'transaction',     //singular name of the listed records
            'plural'    => 'transactions',    //plural name of the listed records
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
            case 'item_id':
            case 'transaction_id':
            case 'quantity':
            case 'buyer_userid':
            case 'buyer_name':
            case 'PaymentMethod':
            case 'eBayPaymentStatus':
            case 'CheckoutStatus':
            case 'CompleteStatus':
            case 'status':
                return $item[$column_name];
            case 'price':
                return number_format( $item[$column_name], 2, ',', '.' );
            case 'date_created':
            case 'LastTimeModified':
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
    function column_item_title($item){
        
        // get current page with paging as url param
        $page = wple_clean($_REQUEST['page']);
        if ( isset( $_REQUEST['paged'] ))           $page .= '&paged='.wple_clean($_REQUEST['paged']);
        if ( isset( $_REQUEST['s'] ))               $page .= '&s=' . urlencode( wple_clean($_REQUEST['s']) );

        //Build row actions
        $actions = array(
            'view_trx_details' => sprintf('<a href="?page=%s&action=%s&transaction=%s&width=600&height=470" class="thickbox">%s</a>',$page,'view_trx_details',$item['id'],__( 'Details', 'wp-lister-for-ebay' )),
            // 'print_invoice' => sprintf('<a href="?page=%s&action=%s&transaction=%s" target="_blank">%s</a>',$page,'wple_print_invoice',$item['id'],__( 'Invoice', 'wp-lister-for-ebay' )),
            // 'edit'      => sprintf('<a href="?page=%s&action=%s&auction=%s">%s</a>',$page,'edit',$item['id'],__( 'Edit', 'wp-lister-for-ebay' )),
        );

        // item title
        $title = $item['item_title'];
        if ( $item['order_id'] ) {
            $title .= '<br><i style="color:silver">#'.$item['order_id'].'</i>';
        }

        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/ $title,
            /*$2%s*/ $this->row_actions($actions)
        );
    }

    function column_item_id($item){
        //Return buyer name and ID
        return sprintf('%1$s <br><span style="color:silver">%2$s</span>',
            /*$1%s*/ $item['item_id'],
            /*$2%s*/ $item['transaction_id']
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
        //Return buyer name and ID
        return sprintf('%1$s <br><span style="color:silver">%2$s</span>',
            /*$1%s*/ $item['PaymentMethod'],
            /*$2%s*/ $item['eBayPaymentStatus']
        );
    }

    function column_CompleteStatus($item){

        switch( $item['CompleteStatus'] ){
            case 'InComplete':
                $color = 'darkorange';
                $value = __( 'InComplete', 'wp-lister-for-ebay' );
				break;
            case 'Complete':
                $color = 'green';
                $value = __( 'Complete', 'wp-lister-for-ebay' );
				break;
            default:
                $color = 'black';
                $value = $item['CompleteStatus'];
        }

        //Return the title contents
        return sprintf('<span style="color:%1$s">%2$s</span><br><span style="color:silver">%3$s</span>',
            /*$1%s*/ $color,
            /*$2%s*/ $value,
            /*$2%s*/ $item['CheckoutStatus']
        );
	}
	  
    function column_account($item) {
        $account_title = isset( WPLE()->accounts[ $item['account_id'] ] ) ? WPLE()->accounts[ $item['account_id'] ]->title : 'NONE';
        return sprintf('%1$s <br><span style="color:silver">%2$s</span>',
            /*$1%s*/ $account_title,
            /*$2%s*/ EbayController::getEbaySiteCode( $item['site_id'] )
        );
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
            'item_id'  			=> __( 'eBay ID', 'wp-lister-for-ebay' ),
            'item_title'  		=> __( 'Product', 'wp-lister-for-ebay' ),
            #'transaction_id'  	=> __( 'Transaction ID', 'wp-lister-for-ebay' ),
            'price'				=> __( 'Price', 'wp-lister-for-ebay' ),
            'quantity'			=> __( 'Quantity', 'wp-lister-for-ebay' ),
            #'buyer_userid'		=> __( 'User ID', 'wp-lister-for-ebay' ),
            'buyer_name'		=> __( 'Name', 'wp-lister-for-ebay' ),
            'PaymentMethod'		=> __( 'Payment method', 'wp-lister-for-ebay' ),
            #'eBayPaymentStatus'	=> __( 'Payment status', 'wp-lister-for-ebay' ),
            #'CheckoutStatus'	=> __( 'Checkout status', 'wp-lister-for-ebay' ),
            'CompleteStatus'	=> __( 'Status', 'wp-lister-for-ebay' ),
            #'status'		 	=> __( 'Status', 'wp-lister-for-ebay' ),
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
            'wple_update_transactions' 	=> __( 'Update transaction from eBay', 'wp-lister-for-ebay' ),
            'wple_delete_transactions'  => __( 'Delete', 'wp-lister-for-ebay' )
        );

        // delete transactions is only for developers
        if ( ! get_option('wplister_log_level') )
            unset( $actions['delete'] );

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
       $views    = array();
       $current  = ( !empty($_REQUEST['transaction_status']) ? wple_clean($_REQUEST['transaction_status']) : 'all');
       $base_url = esc_url_raw( remove_query_arg( array( 'action', 'order', 'transaction_status' ) ) );

       // get transaction status summary
       $om = new TransactionsModel();
       $summary = $om->getStatusSummary();

       // All link
       $class = ($current == 'all' ? ' class="current"' :'');
       $all_url = remove_query_arg( 'transaction_status', $base_url );
       $views['all']  = "<a href='{$all_url }' {$class} >".__( 'All', 'wp-lister-for-ebay' )."</a>";
       $views['all'] .= '<span class="count">('.$summary->total_items.')</span>';

       // Completed link
       $Completed_url = add_query_arg( 'transaction_status', 'Completed', $base_url );
       $class = ($current == 'Completed' ? ' class="current"' :'');
       $views['Completed'] = "<a href='{$Completed_url}' {$class} >".__( 'Completed', 'wp-lister-for-ebay' )."</a>";
       if ( isset($summary->Completed) ) $views['Completed'] .= '<span class="count">('.$summary->Completed.')</span>';

       // Active link
       $Active_url = add_query_arg( 'transaction_status', 'Active', $base_url );
       $class = ($current == 'Active' ? ' class="current"' :'');
       $views['Active'] = "<a href='{$Active_url}' {$class} >".__( 'Active', 'wp-lister-for-ebay' )."</a>";
       if ( isset($summary->Active) ) $views['Active'] .= '<span class="count">('.$summary->Active.')</span>';

       // Cancelled link
       $Cancelled_url = add_query_arg( 'transaction_status', 'Cancelled', $base_url );
       $class = ($current == 'Cancelled' ? ' class="current"' :'');
       $views['Cancelled'] = "<a href='{$Cancelled_url}' {$class} >".__( 'Cancelled', 'wp-lister-for-ebay' )."</a>";
       if ( isset($summary->Cancelled) ) $views['Cancelled'] .= '<span class="count">('.$summary->Cancelled.')</span>';

       return $views;
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
        $per_page = $this->get_items_per_page('transactions_per_page', 20);
        
        // define columns
        $this->_column_headers = $this->get_column_info();
        
        // fetch profiles from model
        $transactionsModel = new TransactionsModel();
        $this->items = $transactionsModel->getPageItems( $current_page, $per_page );
        $total_items = $transactionsModel->total_items;

        // register our pagination options & calculations.
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );

    }
    
}

