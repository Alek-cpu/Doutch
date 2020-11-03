<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
//Create a custom refresh_interval so that scheduled events will be able to display
//  in Cron job manager

/**
 * @return array
 */
class AMWSCPF_Cron
{
    public function amwscpfeedupdate()
    {
        $current_delay = get_option('amwscp_feed_update_interval');
        $next_refresh = wp_next_scheduled('amwscpf_update_feeds_hook');
        if ($next_refresh) {
            wp_unschedule_event($next_refresh, 'amwscpf_update_feeds_hook', false);
        }
        if (!wp_next_scheduled('amwscpf_update_feeds_hook')) {
            wp_schedule_event(strtotime($current_delay . ' seconds'), 'amwscp_feed_refresh_interval', 'amwscpf_update_feeds_hook');
        }
    }
    
}
