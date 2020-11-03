<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
//Create a custom order_interval so that scheduled events will be able to display
//  in Cron job manager
function amwscp_order_interval()
{
    $current_delay = get_option('amwscp_order_fetch_interval');
    return array(
        'order_interval' => array('interval' => $current_delay, 'display' => 'Order refresh interval'),
    );
}

function amwscp_reportfetch_interval()
{
    $current_delay = '300';
    return array(
        'reportfetch_interval' => array('interval' => $current_delay, 'display' => 'Report fetch interval'),
    );
}

function amwscp_listin_loader_interval()
{
    $current_delay = get_option('amwscp_order_fetch_interval');
    return array(
        'listing_loader_interval' => array('interval' => $current_delay, 'display' => 'Listing loader interval'),
    );
}

class AMWSCPF_Order_Cron
{
    public static function doSetup()
    {
        add_filter('cron_schedules', 'amwscp_order_interval');
        //Delete old (faulty) scheduled cron job from prior versions
        $next_refresh = wp_next_scheduled('order_interval');
        if ($next_refresh) {
            wp_unschedule_event($next_refresh, 'order_interval');
        }
        
    }
    
    public static function scheduleUpdate()
    {
        $current_delay = get_option('amwscp_order_fetch_interval');
        //Set the Cron job here. Params are (when, display, hook)
        $next_refresh = wp_next_scheduled('amwscpf_order_import_hook');
        if (!$next_refresh) {
            wp_schedule_event(strtotime($current_delay . ' seconds'), 'order_interval', 'amwscpf_order_import_hook');
        }
        
    }
    
    public static function scheduleOrderFetchEveryFiveMinute()
    {
        $next_refresh = wp_next_scheduled('amwscpf_order_import_five_min_hook');
        if (!$next_refresh) {
            wp_schedule_event(time(), 'five_min', 'amwscpf_order_import_five_min_hook');
        }
    }
    
    public static function scheduleOrderUpdateEveryFiveMinute()
    {
        $next_refresh = wp_next_scheduled('amwscpf_order_update_five_min_hook');
        if (!$next_refresh) {
            wp_schedule_event(time(), 'five_min', 'amwscpf_order_update_five_min_hook');
        }
    }
    
}

add_action('includefiles', 'includewpinclude');
do_action('includefiles');

function includewpinclude()
{
    require_once 'amwscpf-wpincludes.php';
}

class AMWSCPF_Reports_Cron
{
    public static function doSetupReports()
    {
        add_filter('cron_schedules', 'amwscp_reportfetch_interval');
        //Delete old (faulty) scheduled cron job from prior versions
        $next_refresh = wp_next_scheduled('reportfetch_interval');
        if ($next_refresh) {
            wp_unschedule_event($next_refresh, 'reportfetch_interval');
        }
        
    }
    
    public static function scheduleUpdateReports()
    {
        $current_delay = '300';
        //Set the Cron job here. Params are (when, display, hook)
        $next_refresh = wp_next_scheduled('amwscpf_report_fetch_hook');
        if (!$next_refresh) {
            wp_schedule_event(strtotime($current_delay . ' seconds'), 'reportfetch_interval', 'amwscpf_report_fetch_hook');
        }
        
    }
    
}


class AMWSCP_ListingLoader_Cron
{
    public function __construct()
    {
        add_filter('cron_schedules', array(&$this, 'cron_add_custom_schedules'));
    }
    
    public function cron_add_custom_schedules($schedules)
    {
        $schedules['every_minute'] = array(
            'interval' => 60,
            'display' => 'Every Minute'
        );
        $schedules['five_min'] = array(
            'interval' => 60 * 5,
            'display' => 'Once every five minutes'
        );
        $schedules['ten_min'] = array(
            'interval' => 60 * 10,
            'display' => 'Once every ten minutes'
        );
        $schedules['fifteen_min'] = array(
            'interval' => 60 * 15,
            'display' => 'Once every fifteen minutes'
        );
        $schedules['thirty_min'] = array(
            'interval' => 60 * 30,
            'display' => 'Once every thirty minutes'
        );
        $schedules['three_hours'] = array(
            'interval' => 60 * 60 * 3,
            'display' => 'Once every three hours'
        );
        $schedules['six_hours'] = array(
            'interval' => 60 * 60 * 6,
            'display' => 'Once every six hours'
        );
        $schedules['twelve_hours'] = array(
            'interval' => 60 * 60 * 12,
            'display' => 'Once every twelve hours'
        );
        $schedules['daily'] = array(
            'interval' => 60 * 60 * 24,
            'display' => 'Once every twenty four hours'
        );
        
        $schedules['monthly'] = array(
            'interval' => 60 * 60 * 24 * 30,
            'display' => __('Monthly', 'Etsy'),
        );
        
        $schedules['amwscp_feed_refresh_interval'] = array(
            'interval' => get_option('amwscp_feed_update_interval'),
            'display' => 'Amwscp cron interval time'
        );
        
        if (get_option('amwscp_feed_submission_interval'))
            $schedules['amwscp_feed_submit_interval'] = array(
                'interval' => get_option('amwscp_feed_submission_interval'),
                'display' => 'Amazon feed submission interval'
            );
        
        return $schedules;
    }
    
    public function amwscpListingLoader()
    {
        /*$current_delay = get_option('et_cp_feed_delay');
        $next_refresh = wp_next_scheduled('update_etsyfeeds_hook');*/
        //var_dump(wp_unschedule_event(wp_next_scheduled('amwscp_listing_loader_update'), 'amwscp_listing_loader_update'));exit();
        if (!wp_next_scheduled('amwscp_listing_loader_update')) {
            wp_schedule_event(time(), 'fifteen_min', 'amwscp_listing_loader_update');
        }
    }
    
    public function amwscp_feed_submission()
    {
        if (!wp_next_scheduled('amwscp_auto_feed_submission')) {
            wp_schedule_event(strtotime(get_option('amwscp_feed_submission_interval') . ' seconds'), 'amwscp_feed_submit_interval', 'amwscp_auto_feed_submission');
        }
    }
    
}

function fetch_all_reports()
{
    require_once 'amwscpf-wpincludes.php'; //The rest of the required-files moved here
    require_once 'core/crons/reportfetching.php';
    $reportObj = new AMWSCP_GETREPORTS();
    $reportObj->getReports();
}
