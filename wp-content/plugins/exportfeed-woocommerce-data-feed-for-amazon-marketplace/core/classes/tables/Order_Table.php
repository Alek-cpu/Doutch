<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class AMWS_Order_Table extends WP_List_Table
{

    function __construct()
    {
        #$this->get_default_account();
        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => 'amazon_buyer_list',     //singular name of the listed records
            'plural' => 'amazon_buyer_list',    //plural name of the listed records
            'ajax' => false        //does this table support ajax?
        ));
    }

    function column_default($item, $column_name)
    {

        switch ($column_name) {
            case 'cb':
                return '<input type="checkbox" name="selectall" />';
            case 'order_id':
                echo $this->column_title($item);
                break;
            case 'buyer':
                return $this->buyerDetail($item);
            case 'date_created':
                return $this->getformattedtime($item[$column_name]);
            case 'status':
                return $this->orderStatus($item[$column_name]);
                return $item[$column_name];
                break;
            case 'order_detail' :
                return $this->showDetails($item['post_id']);
            case 'total':
                return $this->givemeWhatIneed('ordertotal','CurrencyCode',$item).' '.$this->givemeWhatIneed('ordertotal','Amount',$item);
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    public function orderStatus($status){
        if(isset($status)){
            if($status=='Shipped'){
                $html = '<p class="shipped">'.$status.'</p>';
            }elseif($status=='Unshipped'){
                $html = '<p>'.$status.'</p>';
            }
            elseif($status=='Canceled'){
                $html = '<p style="color:red;">'.$status.'</p>';
            }else{
                $html = '<p class="shipped">'.$status.'</p>';
            }
            return $html;
        }
    }

    public function buyerDetail($item)
    {
        $orderDAta = maybe_unserialize($item['data']);
        $buyername = isset($orderDAta->BuyerName)?$orderDAta->BuyerName:'Buyer name not available';
        $placedon = isset($orderDAta->SalesChannel)?$orderDAta->SalesChannel:'N/A';
        $ordertype = isset($orderDAta->OrderType)?$orderDAta->OrderType:'N/A';
        /*echo"<pre>";
        print_r($orderDAta);exit;*/
        if ($item['post_id'] == '') {
            $anchorlink = '';
        } else {
            $anchorlink = sprintf('<a href="%s" target="_blank">View details</a>', '?page=amwscpf-feed-orders&action=createorder&post=' . $item['post_id']);
        }

        $html = '
                <p> ' . $buyername . ' </p>
                <p class="subText">Placed on ' . $placedon . '</p>
                <p class="orderName">Order type: ' . $ordertype . '</p>
                <p class="">'.$anchorlink.'</p>
                       ';
        return $html;
    }

    public function getformattedtime($datedata)
    {
        $date = date("Y-M-d", strtotime($datedata));
        $time = date("H:i:s", strtotime($datedata));
        $formatedDate = explode('-', $date);
        $html = '
                <p>' . $formatedDate[2] . ',' . $formatedDate[1] . ' ' . $formatedDate[0] . '</p>
                <p class="subText">' . $time . '</p>
                ';
        return $html;
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/
            $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/
            $item['id']                //The value of the checkbox should be the record's id
        );
    }

    function showDetails($id)
    {
        if ($id == '') {
            $always_visible = true;
            return '<span style="color:red">Order items do not resembles the woocommerce product.</span>';
        } else {
            return sprintf('<a href="%s" target="_blank">View details</a>', '?page=amwscpf-feed-orders&action=createorder&post=' . $id);
        }
    }

    function givemeWhatIneed($type=null,$param,$data){
        $orderData = maybe_unserialize($data['data']);
        if(isset($param)&&strlen($param)>1){
            if($type==null){
                return isset($data[$param])?$data[$param]:'N/A';
            }
            elseif($type=='shipping'){
                return isset($orderData->ShippingAddress->$param)?$orderData->ShippingAddress->$param:'N/A';
            }
            elseif($type=='ordertotal'){
                return isset($orderData->OrderTotal->$param)?$orderData->OrderTotal->$param:'N/A';
            }elseif($type=='paymentdetails'){
                return isset($orderData->PaymentMethodDetails->$param)?$orderData->PaymentMethodDetails->$param:'N/A';
            }else{
                return null;
            }
        }else{
            return null;
        }
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'DELETE FROM databse',
            'update' => 'Resync all orders',
        );
        return $actions;
    }

    function column_title($item)
    {
        $html = '
                <p>'.$item['order_id'].'</p>
                <p class="subText"> city:'.$this->givemeWhatIneed('shipping','City',$item).' ('.$this->givemeWhatIneed('shipping','CountryCode',$item).')</p>
                 ';
        return $html;
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'order_id' => 'Order ID',
            'buyer' => 'Buyer',
            'date_created' => 'Date Created',
            'status' => 'Status',
            'order_detail' => 'Action',
            'total'=>'Subtotal'
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

    function process_bulk_action()
    {

        //Detect when a bulk action is being triggered...
        if ('delete' === $this->current_action()) {

        }

    }

    function display()
    {

        $display = parent::display();

        return $display;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'order_id' => array('order_id', false),     //true means it's already sorted
            'date_created' => array('date_created', false)
        );
        return $sortable_columns;
    }

    function prepare_items()
    {
        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 15;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        $this->_column_headers = array($columns, $hidden, $sortable);


        $this->process_bulk_action();


        $data = $this->get_items();


         function usort_reorder($a, $b)
         {
             $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'date_created'; //If no sort, default to title
             $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
             $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
             return ($order === 'desc') ? $result : -$result; //Send final sort direction to usort
         }

         usort($data, 'usort_reorder');


        $current_page = $this->get_pagenum();
        $total_items = count($data);

        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->items = $data;

        $this->set_pagination_args(array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page' => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ));
    }

    function get_items()
    {
        global $wpdb;

        $where = "";
        $table = $wpdb->prefix . "amwscp_orders";
        $sql = "SELECT *  FROM {$table}" . $where . " ORDER BY date_created DESC";
        $data = $wpdb->get_results($sql, ARRAY_A);
        return $data;
    }

    static function formatIntervalOption($value, $descriptor, $current_delay)
    {
        $selected = '';
        if ($value == $current_delay) {
            $selected = ' selected="selected"';
        }
        return '<option value="' . $value . '"' . $selected . '>' . $descriptor . '</option>';
    }

    static function fetchRefreshIntervalSelect()
    {
        $current_delay = get_option('amwscp_order_fetch_interval');
        return '
                    <select name="delay" class="select_medium" id="selectDelay">' . "\r\n" .
            self::formatIntervalOption(604800, '1 Week', $current_delay) . "\r\n" .
            self::formatIntervalOption(86400, '24 Hours', $current_delay) . "\r\n" .
            self::formatIntervalOption(43200, '12 Hours', $current_delay) . "\r\n" .
            self::formatIntervalOption(21600, '6 Hours', $current_delay) . "\r\n" .
            self::formatIntervalOption(3600, '3 Hour', $current_delay) . "\r\n" .
            self::formatIntervalOption(3600, '1 Hour', $current_delay) . "\r\n" .
            self::formatIntervalOption(1800, '30 Minutes', $current_delay) . "\r\n" .
            self::formatIntervalOption(900, '15 Minutes', $current_delay) . "\r\n" .
            self::formatIntervalOption(600, '10 Minutes', $current_delay) . "\r\n" .
            self::formatIntervalOption(300, '5 Minutes', $current_delay) . "\r\n" . '
                    </select>';
    }
}