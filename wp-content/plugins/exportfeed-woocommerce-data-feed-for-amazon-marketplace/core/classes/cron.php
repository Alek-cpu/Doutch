<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
//Create a custom refresh_interval so that scheduled events will be able to display
//  in Cron job manager
/**
 * @return array
 */
function amwscp_add_xml_refresh_interval()
{
    $current_delay = get_option('amwscpf_feed_delay');
    return array(
        'refresh_interval' => array('interval' => $current_delay, 'display' => 'Amazon Feed refresh interval'),
    );
}

if (!class_exists('AMWSCPF_Cron')) {
    class AMWSCPF_Cron
    {
        public static function doSetup()
        {
            add_filter('cron_schedules', 'amwscp_add_xml_refresh_interval');
            //Delete old (faulty) scheduled cron job from prior versions
            // $next_refresh = wp_next_scheduled('refresh_interval');
            // if ($next_refresh)
                // wp_unschedule_event($next_refresh, 'refresh_interval');
            $next_refresh = wp_next_scheduled('purple_xml_updatefeeds_hook');
            if ($next_refresh)
                wp_unschedule_event($next_refresh, 'purple_xml_updatefeeds_hook');
        }

        public static function scheduleUpdate()
        {
            //Set the Cron job here. Params are (when, display, hook)
            $next_refresh = wp_next_scheduled('amwscpf_update_feeds_hook');
            if (!$next_refresh)
                wp_schedule_event(time(), 'refresh_interval', 'amwscpf_update_feeds_hook');
        }

    }
}
