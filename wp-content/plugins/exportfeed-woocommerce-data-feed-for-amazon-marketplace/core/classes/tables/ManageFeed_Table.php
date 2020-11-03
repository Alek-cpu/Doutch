<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
class AMWS_ManageFeed_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;
        //Set parent defaults
        parent::__construct(
            array(
                'singular'	=> 'amwscpf_managefeed',
                'plural'	=> 'amwscpf_managefeeds',
                'ajax'		=> false
            )
        );
    }

    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'category':
                return 'Phone';
                break;

            case 'remote_category':
            case 'type':
            case 'url':
            case 'product_count':
                return $item[ $column_name ];
                break;

            case 'last_updated':
                return 'DNE';
                break;
            default:
                //Show the whole array for troubleshooting purposes
                return $this->column_title($item);
        }
    }

    function column_title( $item ) {

        $actions['update'] = sprintf( '<a href="?page=%s&action=%s&id=%s">Update</a>', $_REQUEST['page'], 'update', 1 );
        $actions['Report'] = sprintf( '<a href="?page=%s&action=%s&id=%s">Download Report</a>', $_REQUEST['page'], 'report', 1 );
        //Build row actions

        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/ 1,
            /*$2%s*/ $this->row_actions( $actions )
        );
    }

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  	//Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ 1			//The value of the checkbox should be the record's id
        );
    }

    function get_columns() {
        return $columns = array(
            'cb'		            => '<input type="checkbox" />', //Render a checkbox instead of text
            'filename'		    => 'Name',
            'category'          => 'Local Category',
            'remote_category'	=> 'Template',
            'type'	            => 'Type',
            'last_updated'	    => 'Last Updated',
        );
    }

    function get_sortable_columns() {
        return $sortable_columns = array(
            'id'            => array('id', false),
            'filename'	 	=> array( 'filename', false ),	//true means it's already sorted
            'category'	        => array( 'category', false ),
            'type'            => array( 'type', false ),
            'product_count'  => array('product_count',false),
        );
    }

    function prepare_items() {
        global $wpdb; //This is used only if making any database queries
        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 20;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $data = $this->list_feeds();
        function usort_reorder( $a, $b ) {
            //If no sort, default to title
            $orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'id';
            //If no order, default to asc
            $order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc';
            //Determine sort order
            $result = strcmp( $a[ $orderby ], $b[ $orderby ] );
            //Send final sort direction to usort
            return ( 'asc' === $order ) ? $result : -$result;
        }
        usort( $data, 'usort_reorder' );


        $current_page = $this->get_pagenum();

        $total_items = count($data);

        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

        $this->items = $data;



        $this->set_pagination_args(
            array(
                //WE have to calculate the total number of items
                'total_items'	=> $total_items,
                //WE have to determine how many items to show on a page
                'per_page'	=> $per_page,
                //WE have to calculate the total number of pages
                'total_pages'	=> ceil( $total_items / $per_page ),
                // Set ordering values if needed (useful for AJAX)
                'orderby'	=> ! empty( $_REQUEST['orderby'] ) && '' != $_REQUEST['orderby'] ? $_REQUEST['orderby'] : 'id',
                'order'		=> ! empty( $_REQUEST['order'] ) && '' != $_REQUEST['order'] ? $_REQUEST['order'] : 'desc'
            )
        );
    }

    function list_feeds(){
        global $wpdb;
        $feed_table = $wpdb->prefix . 'amwscp_feeds';
        $sql_feeds = ("SELECT f.*,description FROM $feed_table as f LEFT JOIN $wpdb->term_taxonomy on ( f.category=term_id and taxonomy='product_cat'  ) ORDER BY f.id");
        $list_of_feeds = $wpdb->get_results($sql_feeds, ARRAY_A);
        #echo "<pre>";print_r($list_of_feeds);die;
        return $list_of_feeds;
    }
}