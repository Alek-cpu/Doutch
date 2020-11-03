<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
//require_once  AMWSCPF_PATH.'/core/classes/amazon.php';
//error_reporting( ~E_NOTICE );
class AMWS_FeedReport_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;
        //Set parent defaults
        parent::__construct(
            array(
                //singular name of the listed records
                'singular'	=> 'amwscpf_report',
                //plural name of the listed records
                'plural'	=> 'amwscpf_reports',
                //does this table support ajax?
                'ajax'		=> true
            )
        );

    }

    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'feed_title':
            case 'SubmittedDate':
            case 'FeedProcessingStatus':
            case 'type':
                return $item[ $column_name ];
            case 'message':
                return $item[ $column_name ];
            case 'UpdatedDate' :
                return $item['updated_at'];
            default:
                //Show the whole array for troubleshooting purposes
                return $this->column_title($item);
        }
    }

    function column_title( $item ) {
        if ($item['type'] == 'feed' && ($item['FeedProcessingStatus'] == '_DONE_' || $item['FeedProcessingStatus'] == '_COMPLETED_WITH_ERRORS_' || $item['FeedProcessingStatus']=='_UNSUCCESSFUL_')){
            if ($item['FeedProcessingStatus'] == '_DONE_'){
                $actions['update'] = sprintf( '<a href="?page=%s&action=%s&id=%s">Update</a>', $_REQUEST['page'], 'update', $item['FeedSubmissionId'] );
            }
            if ($item['FeedProcessingStatus'] == '_COMPLETED_WITH_ERRORS_' && $item['result']!=="Result is not ready yet"){
               $actions['null'] = '<i style="color:red">Some error may have occured. Click View Result for details.</i>';
               $actions['Reports'] = sprintf( '<a href="%s" target="_blank">View Result</a>', $item['result'] );
               $actions['edit'] = sprintf( '<a href="?page=%s&action=%s&id=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['FeedSubmissionId'] );
              }
            if($item['result']!=="Result is not ready yet"){
              $actions['Reports'] = sprintf( '<a href="%s" target="_blank">View Result</a>', $item['result'] );
            }
            else{
                $actions['null'] = '<i style="color:red">Feed result is not ready yet</i>';
             }
        } else {
            if ($item['FeedProcessingStatus'] == '_DONE_'){
                $actions['update'] = sprintf( '<a href="?page=%s&action=%s&id=%s">Update</a>', $_REQUEST['page'], 'update', $item['FeedSubmissionId'] );
            }
            if ($item['FeedProcessingStatus'] == '_COMPLETED_WITH_ERRORS_' && $item['result']!=="Result is not ready yet"){
               $actions['null'] = '<i style="color:red">Some error may have occured. Click View Result for details.</i>';
               $actions['Reports'] = sprintf( '<a href="%s" target="_blank">View Result</a>', $item['result'] );
               $actions['edit'] = sprintf( '<a href="?page=%s&action=%s&id=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['FeedSubmissionId'] );
              }
            else{
                $actions['null'] = '<i style="color:red">Feed result is not ready yet</i>';
             }
            
        }
        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/ $item['FeedSubmissionId'],
            /*$2%s*/ $this->row_actions( $actions, 1 )
        );
    }

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  	//Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['FeedSubmissionId']			//The value of the checkbox should be the record's id
        );
    }

    function get_columns() {
        return $columns = array(
            'cb'		            => '<input type="checkbox" />', //Render a checkbox instead of text
            'FeedSubmissionId'		=> 'Submission ID',
            'feed_title'            => 'Feed Title',
            'SubmittedDate'	        => 'Submitted Date',
            'UpdatedDate'           =>  'Updated Date',
            'FeedProcessingStatus'	=> 'Status',
            'type'	                => 'Type',
            'message'               => 'Message',
        );
    }

    function get_sortable_columns() {
        return $sortable_columns = array(
            'FeedSubmissionId'	 	=> array( 'FeedSubmissionId', false ),	//true means it's already sorted
            'SubmittedDate'	        => array( 'SubmittedDate', false ),
            'feed_title'            => array( 'feed_title', false ),
            'FeedProcessingStatus'  => array('FeedProcessingStatus',false),
            'type'                  => array('type',false),
            'UpdatedDate'           => array('updated_at',false),
        );
    }

    /*function get_bulk_actions() {
        return $actions = array(
            'update'        => 'Update'
        );
    }*/

    function process_bulk_action() {

        if ($this->current_action()){ 

            global $wpdb, $amwcore;
            require_once AMWSCPF_PATH.'/core/data/feedfolders.php';
            $feedtable = $wpdb->prefix."amwscp_amazon_feeds";
            $cp_table = $wpdb->prefix."amwscp_feeds";
            $tpl_table = $wpdb->prefix."amwscp_amazon_templates";

            $action = $this->current_action();

            // gathering ids and depricated because bulk action is removed
//            $ids = [];
//            $ids = isset($_REQUEST['amwscpf_report']) ? $_REQUEST['amwscpf_report'] : [$_REQUEST['id']];
            $id = $_REQUEST['id'];
            $feed = $wpdb->get_row($wpdb->prepare("SELECT * FROM $feedtable WHERE FeedSubmissionId = %s",[$id]));
            switch ($action){
                case 'report':
                    return  AMWSCP_PFeedFolder::uploadURL().$feed->feed_title.'.txt';
                    break;

                case 'update':
                    ob_start(null);
                    require_once AMWSCPF_PATH . '/amwscpf-wpincludes.php';

                    $sql = $wpdb->prepare("SELECT * FROM $cp_table WHERE id = %d",[$feed->type_id]);
                    $cp_feed = $wpdb->get_row($sql);

                    $remote_category = 'listingloader';
                    $requestCode = 'AmazonSC';
                    $local_category = $cp_feed->category;
                    $filename = $cp_feed->filename;
                    $feedIdentifier = round(microtime(true)*1000);
                    $saved_feed_id = $cp_feed->id;

                    $output = new stdClass();
                    $output->url = '';
                    $dir = AMWSCP_PFeedFolder::uploadRoot();
                    if ((strlen($local_category) * strlen($requestCode) == 0) &&
                        (strlen($remote_category) == 0) &&
                        (!is_writable($dir)) &&
                        (!is_dir($dir))){
                        mkdir($dir);
                        $output->errors = 'Your Feed is not properly created. Please go to Manage Feed And Edit the feed once again.';
                        $this->doOutput($output);
                    }

                    $providerFile = 'feeds/'.strtolower($requestCode).'/feed.php';
                    if (!file_exists(AMWSCPF_PATH.'/core/'.$providerFile))
                        if (!class_exists('AMWSCP_P'.$requestCode.'Feed')){
                            $output->errors = 'Error: Provider not found.';
                            $this->doOutput($output);
                        }

                    $providerFileFull = AMWSCPF_PATH.'/core/'.$providerFile;
                    if (file_exists($providerFileFull))
                        require_once $providerFileFull;

                    if (strlen($saved_feed_id) >0 && $saved_feed_id > -1){
                        require_once AMWSCPF_PATH.'/core/data/savedfeed.php';
                        $saved_feed = new AMWSCPF_SavedFeed($saved_feed_id);
                    }

                    $providerClass = 'AMWSCP_P'.$requestCode.'Feed';
                    $x = new $providerClass;
                    if (strlen($feedIdentifier) > 0)
                        $x->activityLogger = new AMWSCP_PFeedActivityLog($feedIdentifier);
                    $x->getFeedData($local_category,$remote_category,$filename,$saved_feed);

                    if ($x->success)
                        $output->url = AMWSCP_PFeedFolder::uploadURL().$x->providerName.'/'.$filename.'.'.$x->fileformat;
                    $output->errors = $x->getErrorMessages();
                    $this->doOutput($output);

                    $url = admin_url('admin.php?page=exportfeed-amazon-amwscpf-admin');
                    $sendback = add_query_arg([
                        'action'=>'amwscpf_submit_feed',
                        'id'=>$saved_feed_id,
                        'type'=>'update'
                    ],$url);
                    wp_redirect($sendback);
                    break;

                    case 'edit':
                    // print_r("yahooooo!!!!!!!!");exit;
                    ob_start(null);
                    require_once AMWSCPF_PATH . '/amwscpf-wpincludes.php';

                    $sql = $wpdb->prepare("SELECT * FROM $cp_table WHERE id = %d",[$feed->type_id]);
                    $cp_feed = $wpdb->get_row($sql);

                    $remote_category = 'listingloader';
                    $requestCode = 'AmazonSC';
                    $local_category = $cp_feed->category;
                    $filename = $cp_feed->filename;
                    $feedIdentifier = round(microtime(true)*1000);
                    $saved_feed_id = $cp_feed->id;
                    $url = admin_url('admin.php?page=exportfeed-amazon-amwscpf-admin');
                    $sendback = add_query_arg([
                        'action'=>'edit',
                        'id'=>$saved_feed_id,
                        'type'=>'edit'
                    ],$url);
                    wp_redirect($sendback);
                    break;
            }
        }

        // print_r("out of function");exit;
    }

    function doOutput($output){
        ob_clean();
        echo json_encode($output);
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
        $this->process_bulk_action();
        $data = $this->list_feeds();
        function usort_reorder( $a, $b ) {
            //If no sort, default to SubmittedDate
            $orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'SubmittedDate';
            //If no order, default to asc
            $order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc';
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
                'orderby'	=> ! empty( $_REQUEST['orderby'] ) && '' != $_REQUEST['orderby'] ? $_REQUEST['orderby'] : 'SubmittedDate',
                'order'		=> ! empty( $_REQUEST['order'] ) && '' != $_REQUEST['order'] ? $_REQUEST['order'] : 'desc'
            )
        );
    }

    function list_feeds(){
        global $wpdb;
        $table = $wpdb->prefix."amwscp_amazon_feeds";
        $sql = "SELECT * FROM {$table} WHERE type !='product' ORDER BY SubmittedDate DESC";
        $result = $wpdb->get_results($sql,ARRAY_A);
        return $result;
    }

    function display() {
        echo '<input type="hidden" id="order" name="order" value="' . $this->_pagination_args['order'] . '" />';
        echo '<input type="hidden" id="orderby" name="orderby" value="' . $this->_pagination_args['orderby'] . '" />';
        parent::display();
    }
}