<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly
// require_once dirname(__FILE__) . '/../../data/feedcore.php';

$keywordfororderstatus = $_POST['keywordfororderstatus'];
$keywordfororderid = $_POST['keywordfororderid'];
$searchtype = $_POST['searchtype'];

if (class_exists('FetchSearchedOrder')) {
    $obj = new FetchSearchedOrder();

    $html_optins = $obj->getSearchResult($keywordfororderstatus, $keywordfororderid);
    echo $html_optins;
    exit;
} else {
    print_r("Class doesn't exists");
    exit;
}

Class FetchSearchedOrder
{

    function __construct()
    {

    }


    function getSearchResult($keywordfororderstatus, $keywordfororderid)
    {
        $html = '';
        $data = $this->search($keywordfororderstatus, $keywordfororderid);
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $html .= '<tr>
                           <th scope="row" class="check-column"><input type="checkbox" name="amazon_buyer_list[]" value="' . $value['id'] . '"></th>
                           <td class="order_id column-order_id has-row-actions column-primary" data-colname="Order ID">
                                <p>' . $value['order_id'] . '</p>
                                <p class="subText"> city:' . $this->givemeWhatIneed('shipping', 'City', $value) . ' (' . $this->givemeWhatIneed('shipping', 'CountryCode', $value) . '</p>
                                 <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td>
                           <td class="buyer column-buyer" data-colname="Buyer">
                               ' . $this->buyerDetail($value) . '
                                       </td>
                           <td class="date_created column-date_created" data-colname="Date Created">
                                ' . $this->getformattedtime($value['date_created']) . '
                           </td>
                           <td class="status column-status" data-colname="Status">'.$this->orderStatus($value['status']).'</p></td>
                           <td class="order_detail column-order_detail" data-colname="Action">'.$this->showDetails($value['post_id']).'</td>
                           <td class="total column-total" data-colname="Subtotal">'.$this->givemeWhatIneed('ordertotal','CurrencyCode',$value).' '.$this->givemeWhatIneed('ordertotal','Amount',$value).'</td>
                          </tr>';
            }
        } else {
            $html .= '<tr class="no-items"><td class="colspanchange" colspan="5">No items found.</td></tr>';
        }

        return $html;
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

    function givemeWhatIneed($type = null, $param, $data)
    {
        $orderData = maybe_unserialize($data['data']);
        if (isset($param) && strlen($param) > 1) {
            if ($type == null) {
                return isset($data[$param]) ? $data[$param] : 'N/A';
            } elseif ($type == 'shipping') {
                return isset($orderData->ShippingAddress->$param) ? $orderData->ShippingAddress->$param : 'N/A';
            } elseif ($type == 'ordertotal') {
                return isset($orderData->OrderTotal->$param) ? $orderData->OrderTotal->$param : 'N/A';
            } elseif ($type == 'paymentdetails') {
                return isset($orderData->PaymentMethodDetails->$param) ? $orderData->PaymentMethodDetails->$param : 'N/A';
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function buyerDetail($value)
    {
        $orderDAta = maybe_unserialize($value['data']);
        $buyername = isset($orderDAta->BuyerName) ? $orderDAta->BuyerName : 'Buyer name not available';
        $placedon = isset($orderDAta->SalesChannel) ? $orderDAta->SalesChannel : 'N/A';
        $ordertype = isset($orderDAta->OrderType) ? $orderDAta->OrderType : 'N/A';
        /*echo"<pre>";
        print_r($orderDAta);exit;*/
        if ($value['post_id'] == '') {
            $anchorlink = '';
        } else {
            $anchorlink = ' | ' . sprintf('<a href="%s" target="_blank">View details</a>', '?page=amwscpf-feed-orders&action=createorder&post=' . $value['post_id']);
        }

        $html = '
                <p> ' . $buyername . ' </p>
                <p class="subText">Placed on ' . $placedon . '</p>
                <p class="orderName">Order type: ' . $ordertype . '</p>
                <p class=""><a>Details</a>' . $anchorlink . '</p>
                       ';
        return $html;
    }


    function search($keywordfororderstatus, $keywordfororderid)
    {

        global $wpdb;

        if ($keywordfororderstatus && $keywordfororderid) {
            $where = " WHERE status LIKE '" . $keywordfororderstatus . "'" . " AND order_id='" . $keywordfororderid . "'";

        } elseif ($keywordfororderstatus) {
            $where = " WHERE status LIKE '" . $keywordfororderstatus . "'";

        } else {

            $where = " WHERE order_id='" . $keywordfororderid . "'";

        }
        $table = $wpdb->prefix . "amwscp_orders";
        $sql = "SELECT *  FROM {$table}" . $where . " ORDER BY date_created DESC";
        $data = $wpdb->get_results($sql, ARRAY_A);
        return $data;

    }


}


?>