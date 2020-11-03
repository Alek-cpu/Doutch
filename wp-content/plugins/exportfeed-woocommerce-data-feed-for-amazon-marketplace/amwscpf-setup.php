<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once 'core/classes/amazon_cron.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

//callback function
function amwscpf_activate_plugin()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . "amwscp_feeds";
    $sql = "
			CREATE TABLE {$table_name} (
			`id` INT NOT NULL AUTO_INCREMENT,
			`category` varchar(250) NOT NULL,
            `remote_category` varchar(1000) NOT NULL,
			`amazon_category` varchar(1000) NOT NULL,
			`filename` varchar(250) NOT NULL,
			`url` varchar(500) NOT NULL,
			`type` varchar(50) NOT NULL DEFAULT 'amazonsc',
			`own_overrides` int(10),
			`feed_overrides` text,
			`product_count` int,
            `previous_product_count` int NOT NULL,
			`feed_errors` text,
			`feed_title` varchar(250),
			`submitted` INT NOT NULL DEFAULT 0,
            `verified` INT NOT NULL DEFAULT 0,
      `variation_theme` varchar(255) ,
      `feed_product_type` varchar(255),
      `recommended_browse_nodes` varchar(255),
      `item_type_keyword` varchar(255),
			PRIMARY KEY (`id`)
		) $charset_collate";
    dbDelta($sql);

    $table_name = $wpdb->prefix . "amwscp_template_values";
    $sql = "CREATE TABLE `$table_name` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `fields` varchar(255) DEFAULT NULL,
          `labels` varchar(255) DEFAULT NULL,
          `examples` longtext,
          `definition` text,
          `valid_values` longblob,
          `tmpl_id` int(11) DEFAULT NULL,
          `country` varchar(15) DEFAULT NULL,
          `required` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) $charset_collate";
    dbDelta($sql);


    $table_name = $wpdb->prefix . "amwscp_feed_product_record";
    $sql = "CREATE TABLE `$table_name` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `product_id` bigint(20) DEFAULT 0,
          `sku` varchar(100),
          `feed_id` int(11) DEFAULT NULL,
          `product_name` varchar (255) DEFAULT NULL ,
          `stock_quantity` int(11) DEFAULT 0,
          `uploaded` tinyint(1) DEFAULT -1,
          `upload_result` text,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) $charset_collate";
    dbDelta($sql);

    $table_name = $wpdb->prefix . "amwscp_amazon_templates";
    $sql = "CREATE TABLE `$table_name` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `tpl_name` varchar(255) DEFAULT NULL,
          `tmpl_id` int DEFAULT NULL,
          `version` varchar(255) DEFAULT NULL,
          `country` varchar(50) NOT NULL,
          `raw` longtext,
          PRIMARY KEY (`id`)
        ) $charset_collate";
    dbDelta($sql);

    $table_name = $wpdb->prefix . "amwscp_amazon_feeds";
    $sql = "CREATE TABLE `$table_name` (
           `id` int(11) NOT NULL AUTO_INCREMENT,
           `FeedSubmissionId` varchar(255) DEFAULT NULL,
           `FeedType` varchar(80) DEFAULT NULL,
           `SubmittedDate` datetime DEFAULT NULL,
           `FeedProcessingStatus` varchar(40) DEFAULT NULL,
           `result` text,
           `data` text,
           `type_id` varchar(255) DEFAULT NULL,
           `type` varchar(15) DEFAULT NULL,
           `status` int(11) DEFAULT NULL,
           `account_id` int(11) DEFAULT NULL,
           `feed_title` varchar(255) DEFAULT NULL,
           `message` varchar(255) DEFAULT NULL,
           `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
           PRIMARY KEY (`id`)
        ) $charset_collate";
    dbDelta($sql);

    $table_name = $wpdb->prefix . "amwscp_amazon_accounts";
    $sql = "CREATE TABLE `$table_name` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(125) DEFAULT NULL,
          `merchant_id` varchar(50) DEFAULT NULL,
          `marketplace_id` varchar(50) DEFAULT NULL,
          `access_key_id` varchar(70) DEFAULT NULL,
          `secret_key` varchar(70) DEFAULT NULL,
          `mws_auth_token` varchar(90) DEFAULT NULL,
          `market_id` int(11) DEFAULT NULL,
          `allowed_markets` longtext,
          `active` int(11) DEFAULT NULL,
          `is_valid` int(11) DEFAULT NULL,
          `market_code` varchar(10) NOT NULL,
          `last_ordered` DATETIME NULL,
          PRIMARY KEY (`id`)
        ) $charset_collate";
    dbDelta($sql);

    $table_name = $wpdb->prefix . "amwscp_orders";
    $sql = "CREATE TABLE `$table_name` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `order_id` varchar(255) NOT NULL,
          `date_created` datetime NOT NULL,
          `updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `items` text,
          `status` varchar(100) NOT NULL,
          `account_id` int(11) DEFAULT NULL,
          `sync_state` varchar(25) NOT NULL,
          `data` text NOT NULL,
          `post_id` int(11) DEFAULT NULL,
          `processed_count` int(11) DEFAULT NULL,
          `message` text DEFAULT NULL ,
          `fetchedby` varchar(50) NOT NULL,
          `woo_order_created` enum('0','1') DEFAULT '0' COMMENT '0=false, 1= true',
          `makewoo_order` enum('0','1') DEFAULT '0' COMMENT '0=false, 1= true',
          PRIMARY KEY (`id`)
        ) $charset_collate";
    dbDelta($sql);
}

function amwscpf_deactivate_plugin()
{
    $next_refresh = wp_next_scheduled('amwscpf_update_feeds_hook');
    if ($next_refresh)
        wp_unschedule_event($next_refresh, 'amwscpf_update_feeds_hook');
}
