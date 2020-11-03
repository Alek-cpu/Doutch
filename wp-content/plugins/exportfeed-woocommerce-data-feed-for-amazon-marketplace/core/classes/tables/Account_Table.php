<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class AMWS_Account_Table extends WP_List_Table {

    public $message = "";
    public $result = [];
    public $default_account = "";
    function __construct(){
        #$this->get_default_account();
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'account',     //singular name of the listed records
            'plural'    => 'accounts',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }

    function get_default_account(){
        $account = $this->get_account_by_id();
        $this->default_account = $this->result;
    }

    function column_default($item, $column_name){

        switch($column_name){
            case 'title':
            case 'market_code':
                return $item[$column_name];
                break;
            /*case 'market_id':
                global $wpdb;
                $table = $wpdb->prefix."amwscp_amazon_accounts";
                $sql = "SELECT allowed_markets FROM $table WHERE id = ".$item['ID'];
                $allowed_market = maybe_unserialize($wpdb->get_var($sql));
                $html = "";
                #echo "<pre>";print_r($allowed_market);echo"</pre>";
                if(count($allowed_market) > 0){
                    foreach ($allowed_market as $key => $market){
                        if($market->DefaultCountryCode == $item['market_code'])
                            $html .= '<strong>'.$market->Name.'</strong><br>';
                        else
                            $html .= $market->Name.'<br>';
                    }
                } else {
                    $html = "This account is not valid! You cannot submit feed or any do any activities with this account.";
                }
                return $html;
                break;*/
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_title($item){

        //Build row actions
        if(isset($_REQUEST['action']) && $_REQUEST['action']=='aabfs'){
            $makeaccoutdefaulturl = sprintf('<a href="?page=%s&action=%s&id=%s&feed_id=%s">Make Default</a>',$_REQUEST['page'],'default',$item['ID'],$_REQUEST['feed_id']);
        }else{
            $makeaccoutdefaulturl = sprintf('<a href="?page=%s&action=%s&id=%s">Make Default</a>',$_REQUEST['page'],'default',$item['ID']);
        }
        $actions = array(
            'edit'      => sprintf('<a href="#" onclick="amwscpf_editaccount(this)" data-id="%s">Edit</a>',$item['ID']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
            'make_default'    => $makeaccoutdefaulturl,
        );
        $default = "";
        if($item['active'] == 1)
            $default = '(Default Account)';
        //Return the title contents
        return sprintf('%1$s %4$s<span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['title'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions),
            /*$3%s*/ $default
        );
    }

    function get_columns(){
        $columns = array(
//            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'title'     => 'Title',
            'market_code'    => 'Site',
            //'market_id'  => 'Marketplace',
        );
        return $columns;
    }

    /*function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }*/

    function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            global $wpdb;
            $table = $wpdb->prefix."amwscp_amazon_accounts";
            $id = $_REQUEST['id'];
            $wpdb->delete($table,['id'=>$id]);
            $this->message = 'Items deleted!';
        }

    }

    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 5;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        $this->_column_headers = array($columns, $hidden, $sortable);


        $this->process_bulk_action();


        $data = $this->get_items();


        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'ID'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');


        $current_page = $this->get_pagenum();

        $total_items = count($data);


        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }

    function get_items(){
        global $wpdb;

        $where = "";
        if (isset($_REQUEST['account_id'])){
            $id = intval($_REQUEST['account_id']);
            $where = ' WHERE id = '.$id;
        }

        $table = $wpdb->prefix."amwscp_amazon_accounts";
        $sql = "SELECT id as ID, title, marketplace_id, market_id,market_code,active  FROM {$table}".$where;
        $data = $wpdb->get_results($sql,ARRAY_A);
        return $data;
    }

    function save(){}

    function get_market_details($id){
        global $wpdb;
        $table = $wpdb->prefix."amwscpf_amazon_markets";
        $sql = "SELECT * FROM $table WHERE id = $id";
        $result = $wpdb->get_row($sql);
        return $result;
    }

    function get_account_by_id(){
        global $wpdb;
        if (isset($_REQUEST['account_id'])){
            $id = intval($_REQUEST['account_id']);
            $where = ' WHERE id = '.$id;
        } else {
            $where = ' WHERE active = 1';
        }

        $table = $wpdb->prefix."amwscp_amazon_accounts";
        $sql = "SELECT * FROM {$table}".$where;

        $data = $wpdb->get_row($sql,ARRAY_A);
        return $data;
    }

    function delete_account($id){
        global $wpdb;
        $result = 'Cannot delete';
        $table = $wpdb->prefix."amwscp_amazon_accounts";
        if ($wpdb->delete($table,['id'=>$id]))
            $result = 'Account Deleted!';
        return $result;
    }

    function default_mws_account($id){
        global $wpdb;
        $result = 'Cannot Update';
        $table = $wpdb->prefix."amwscp_amazon_accounts";
        $data = ['active'=>1];
        $wpdb->query("UPDATE $table SET active = 0");
        if ($wpdb->update($table,$data,['id'=>$id])){
            $result = 'Default account selected successfully.';
            if(isset($_REQUEST['feed_id'])){
                $location = admin_url().'admin.php?page=exportfeed-amazon-amwscpf-admin&action=amwscpf_submit_feed&id='.$_REQUEST['feed_id'];
                wp_redirect($location);
            }
        }
        return $result;
    }
}