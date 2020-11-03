<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!isset($_POST['setting']) || !isset($_POST['value'])) {
    echo 'Error in setting';
    return;
}
require_once dirname(__FILE__) . '/../../classes/amazon_cron.php';
require_once dirname(__FILE__) . '/../../data/feedcore.php';

//$cronObj = amwscp_add_xml_refresh_interval();
// print_r($cronObj['refresh_interval']);exit;
$setting = $_POST['setting'];
if (isset($_POST['feedid']))
    $feedid = $_POST['feedid'];
else
    $feedid = '';
$value = $_POST['value'];

//Don't update here - security issue would allow any option to be updated
//Only update within an if()
if ($setting == 'amwscp_feed_update_interval') {
    update_option($setting, $value);
    //Is this event scheduled?
    $next_refresh = wp_next_scheduled('amwscpf_update_feeds_hook');
    if ($next_refresh)
        wp_unschedule_event($next_refresh, 'amwscpf_update_feeds_hook');
    wp_schedule_event(strtotime($value. ' seconds'), 'amwscp_feed_refresh_interval', 'amwscpf_update_feeds_hook');
}

if($setting=="amwscp_order_fetch_interval"){
    update_option($setting,$value);
    $next_refresh = wp_next_scheduled('amwscpf_order_import_hook');
    if ($next_refresh)
        wp_unschedule_event($next_refresh, 'amwscpf_order_import_hook');
    wp_schedule_event(strtotime($value. ' seconds'), 'order_interval', 'amwscpf_order_import_hook');
}

if ($setting == 'gts_licensekey')
    update_option($setting, $value);
if ($setting == 'amwscpf_licensekey'){
	global $amwcore;
	$license_key_id = $amwcore->checkLicense($value);
	echo "Id:" .$license_key_id;
	if($license_key_id){
		echo "License key already in use";
		return;
	}else{
		update_option($setting, $value);
	}
}


//Some PHPs don't return the post correctly when it's long data
if (strlen($setting) == 0) {
    $lines = explode('&', file_get_contents("php://input"));
    foreach ($lines as $line) {
        if ((strpos($line, 'feedid') == 0) && (strlen($feedid) == 0))
            $feedid = substr($line, 7);
        if ((strpos($line, 'setting') == 0) && (strlen($setting) == 0))
            $setting = substr($line, 8);
    }
}

if (strpos($setting, 'cp_advancedFeedSetting') !== false) {

    //$value may get truncated on an & because $_POST can't parse
    //so pull value manually
    $postdata = file_get_contents("php://input");
    $i = strpos($postdata, '&value=');
    if ($i !== false)
        $postdata = substr($postdata, $i + 7);

    //Strip the provider name out of the setting
    $target = substr($setting, strpos($setting, '-') + 1);

    //Save new advanced setting
    if (strlen($feedid) == 0)
        update_option($target . '-amwscp-settings', $postdata);
    else {
        global $wpdb;
        $feed_table = $wpdb->prefix . 'amwscp_feeds';
        $sql = "
				UPDATE $feed_table 
				SET
					`own_overrides`=1,
					`feed_overrides`='$postdata'
				WHERE `id`=$feedid";
        $wpdb->query($sql);
    }
}

echo 'Updated.';


?>