<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once dirname(__FILE__) . '/../../classes/Amazon_Orders.php';
$type = sanitize_text_field($_REQUEST['type']);
$amazon = new Amazon_Orders(null);
$days = sanitize_text_field($_REQUEST['days']);
if($type==='fetch_amazon_order'){
    $result = $amazon->getOrders($days);
}elseif($type==='create_woo_order'){
    $result = $amazon->create_amazon_order();
}

if ($result === false) {
    echo json_encode(array('success' => false));
} else {
    echo json_encode(array('success' => true));
}
