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
class ProfilesTable extends WP_List_Table {

    var $total_items;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'profile',     //singular name of the listed records
            'plural'    => 'profiles',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }

    function no_items() {
        _e( 'No profiles found.', 'wp-lister-for-ebay' );
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
            case 'type':
                $type = $item[$column_name] == 'FixedPriceItem' ? __( 'Fixed Price', 'wp-lister-for-ebay' ) : __('Listing', 'wp-lister-for-ebay' );
                if ( $item[$column_name] == 'ClassifiedAd' ) $type = __( 'Classified Ad', 'wp-lister-for-ebay' );
                return $type;
            case 'listing_duration':
                if ( 'GTC' == $item['listing_duration'] ) return '<span style="color:silver">'.$item['listing_duration'].'</span>';
                return str_replace('Days_','',$item[$column_name]) .' '. __( 'days', 'wp-lister-for-ebay' );
            case 'price':
                return $item['details']->start_price;
            case 'category':
                return $item['details']->ebay_category_1_name;
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
    function column_profile_name($item){
        
        // get current page with paging as url param
        $page = wple_clean($_REQUEST['page']);
        if ( isset( $_REQUEST['paged'] ))           $page .= '&paged='.wple_clean($_REQUEST['paged']);
        if ( isset( $_REQUEST['s'] ))               $page .= '&s=' . urlencode( wple_clean($_REQUEST['s']) );

        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&profile=%s">%s</a>',$page,'edit',$item['profile_id'],__( 'Edit', 'wp-lister-for-ebay' )),
            'duplicate' => sprintf('<a href="?page=%s&action=%s&profile=%s&_wpnonce=%s">%s</a>',$page,'duplicate_auction_profile', $item['profile_id'], wp_create_nonce( 'duplicate_auction_profile' ), __( 'Duplicate', 'wp-lister-for-ebay' )),
            'download'  => sprintf('<a href="?page=%s&action=%s&profile=%s&_wpnonce=%s">%s</a>',$page,'download_listing_profile', $item['profile_id'], wp_create_nonce( 'download_listing_profile' ), __( 'Download', 'wp-lister-for-ebay' )),
            'delete'    => sprintf('<a href="?page=%s&action=%s&profile=%s&_wpnonce=%s">%s</a>',$page,'wplister_delete_profile',$item['profile_id'], wp_create_nonce( 'wplister_delete_profile' ),__( 'Delete', 'wp-lister-for-ebay' )),
        );

        // make title link to edit page
        $title = sprintf('<a href="?page=%s&action=%s&profile=%s" class="title_link">%s</a>', $page, 'edit', $item['profile_id'], $item['profile_name'] );
        
        //Return the title contents
        return sprintf('%1$s <br><span style="color:silver">%2$s</span>%3$s',
            /*$1%s*/ $title,
            /*$2%s*/ $item['profile_description'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    function column_template($item){

        $template_id = basename( $item['details']->template );
        $template_name = TemplatesModel::getNameFromCache( $template_id );

        return sprintf(
            '<a href="admin.php?page=wplister-templates&action=edit&template=%1$s" title="%2$s">%3$s</a>',
            /*$1%s*/ $template_id,  
            /*$2%s*/ __( 'Edit', 'wp-lister-for-ebay' ),  
            /*$3%s*/ $template_name        
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
            /*$2%s*/ $item['profile_id']        //The value of the checkbox should be the record's id
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
            'profile_name'  	=> __( 'Profile', 'wp-lister-for-ebay' ),
            'listing_duration' 	=> __( 'Duration', 'wp-lister-for-ebay' ),
            'price'				=> __( 'Price', 'wp-lister-for-ebay' ),
            'category'			=> __( 'Category', 'wp-lister-for-ebay' ),
            'type'				=> __( 'Type', 'wp-lister-for-ebay' ),
            'template'			=> __( 'Template', 'wp-lister-for-ebay' ),
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
            'profile_name'  	=> array('profile_name',true),     //true means its already sorted
            'listing_duration'  => array('listing_duration',false),
            'type'  			=> array('type',false)
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
            'wplister_delete_profile'    => __( 'Delete', 'wp-lister-for-ebay' )
        );
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
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            #wp_die('Items deleted (or they would be if we had items to delete)!');
        }
        
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
        $per_page = $this->get_items_per_page('profiles_per_page', 20);
        
        // define columns
        $this->_column_headers = $this->get_column_info();
        
        // fetch profiles from model
        $profilesModel = new ProfilesModel();
        $this->items = $profilesModel->getPageItems( $current_page, $per_page );
        $total_items = $profilesModel->total_items;

        // register our pagination options & calculations.
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );

    }
    
}


