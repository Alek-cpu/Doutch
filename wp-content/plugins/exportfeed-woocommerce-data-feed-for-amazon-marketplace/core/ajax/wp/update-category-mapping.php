<?php
if (!defined('ABSPATH')) {
    exit;
}
update_Settings($_POST);
function update_Settings($data){
    if(isset($data['value']['amazon_category']) && isset($data['value']['woo_category']) ){
        try{
            update_option('amwscp_'.str_replace(' ','_',$data['value']['amazon_category']),$data['value']['woo_category']);
            if ($data['value']['amazon_category'] == 'feed_update_interval') {
                update_option($data['value']['amazon_category'], $data['value']['woo_category']);
                //Is this event scheduled?
                $next_refresh = wp_next_scheduled('amwscpf_update_feeds_hook');
                if ($next_refresh)
                    wp_unschedule_event($next_refresh, 'amwscpf_update_feeds_hook');
                wp_schedule_event(strtotime($data['value']['woo_category']. ' seconds'), 'amwscp_feed_refresh_interval', 'amwscpf_update_feeds_hook');
            }
    
            if($data['value']['amazon_category']=="order_fetch_interval"){
                update_option($data['value']['amazon_category'],$data['value']['woo_category']);
                $next_refresh = wp_next_scheduled('amwscpf_order_import_hook');
                if ($next_refresh)
                    wp_unschedule_event($next_refresh, 'amwscpf_order_import_hook');
                wp_schedule_event(strtotime($data['value']['woo_category']. ' seconds'), 'order_interval', 'amwscpf_order_import_hook');
            }
        }catch (Exception $e){
            echo "<pre>";
            print_r($e);
            echo "</pre>";
            exit;
        }
        echo json_encode(array('status' => true)); exit;
    }
    echo json_encode(array('status' => false)); exit;
}