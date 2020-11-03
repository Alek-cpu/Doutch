<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
$id = intval($_REQUEST['account_id']);
global $wpdb;

$table = $wpdb->prefix."amwscp_amazon_accounts";
$sql = $wpdb->prepare("SELECT * FROM $table WHERE id = %d",array($id));
$account = $wpdb->get_row($sql);

echo json_encode($account);
die;