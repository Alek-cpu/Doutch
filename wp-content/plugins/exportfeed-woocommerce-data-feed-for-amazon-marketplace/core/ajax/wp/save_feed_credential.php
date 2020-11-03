<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
global $wpdb;
$table = $wpdb->prefix."amwscp_amazon_accounts";
$credential = sanitize_text_field($_REQUEST['credential']);
$feed_id = intval($_REQUEST['feed_id']);

update_option('amwscpf_feed_id_'.$feed_id.'_credential',$credential);
echo 'credential saved';
