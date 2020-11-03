<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
// Create a custom refresh_interval so that scheduled events will be able to display
class AMWSCP_Cron_Custom
{
    public function __construct()
    {
        add_filter('cron_schedules', array(&$this, 'amwscp_cron_add_custom_schedules'));
    }

    public function amwscp_cron_add_custom_schedules($schedules)
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

        $schedules['weekly'] = array(
            'interval' => strtotime(604800 . ' seconds'), // 1 week in seconds
            'display' => __('Once Weekly'),
        );

        $schedules['monthly'] = array(
            'interval' => 2635200,
            'display' => __('Monthly', 'Etsy'),
        );
        $schedules['amazon_feed'] = array(
            'interval' => strtotime(get_option('amwscp_feed_delay') . ' seconds'),
            'display' => __('Custom', 'Etsy'),
        );
        return $schedules;
    }

    public function amazonFeedUpdateCron()
    {
        /*$current_delay = get_option('amwscp_feed_delay');
        $next_refresh = wp_next_scheduled('update_amazonfeeds_hook');*/

        if (!wp_next_scheduled('update_amazonfeeds_hook')) {
            wp_schedule_event(time(), get_option('amwscp_feed_update_interval'), 'update_amazonfeeds_hook');
        }
    }

    public function scheduleamazonUpload()
    {
        if (!wp_next_scheduled('auto_feed_submission_hook')) {
            wp_schedule_event(time(), get_option('amwscp_feed_submission_interval'), 'amwscp_auto_feed_submission_hook');
        }
    }

    public function scheduleamazonOrder()
    {
        if (!wp_next_scheduled('auto_amazon_order_hook')) {
            wp_schedule_event(time(), get_option('order_fetch_interval'), 'auto_amazon_order_hook');
        }
    }

    public function scheduleOrderFetchEveryFiveMinute(){
       /* $timestamp = wp_next_scheduled('amwscpf_order_import_five_min_hook');
        wp_unschedule_event( $timestamp, 'amwscpf_order_import_five_min_hook' );*/
        $next_refresh = wp_next_scheduled('amwscpf_order_import_five_min_hook');
        if (!$next_refresh) {
            //wp_schedule_event(time(), 'hourly', 'amwscpf_order_import_five_min_hook');
            wp_schedule_event(time(), 'every_minute', 'amwscpf_order_import_five_min_hook');
        }
    }

    public function scheduleOrderUpdateEveryFiveMinute(){
        /*$timestamp = wp_next_scheduled('amwscpf_order_update_five_min_hook');
        wp_unschedule_event( $timestamp, 'amwscpf_order_update_five_min_hook' );*/
        $next_refresh = wp_next_scheduled('amwscpf_order_update_five_min_hook');
        if (!$next_refresh) {
            //wp_schedule_event(time(), 'hourly', 'amwscpf_order_update_five_min_hook');
            wp_schedule_event(time(), 'every_minute', 'amwscpf_order_update_five_min_hook');
        }
    }

}
