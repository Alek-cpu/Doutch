<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Uninstall;

use WCPayPalPlus\Http\PayPalAssetsCache\ResourceDictionary;
use WCPayPalPlus\Notice\DismissibleNoticeOption;
use WCPayPalPlus\Setting\SharedPersistor;
use WCPayPalPlus\Utils\NetworkState;
use WP_Filesystem_Base;
use wpdb;

/**
 * Class Uninstaller
 * @package WCPayPalPlus\Uninstall
 */
class Uninstaller
{
    const OPTION_PAYPAL_PLUS_GATEWAY = 'woocommerce_paypal_plus_settings';
    const OPTION_EXPRESS_CHECKOUT_GATEWAY = 'woocommerce_paypal_express_settings';
    const OPTION_ADMIN_NOTICE_MESSAGE_ID = 'ppplus_message_id';
    const OPTION_ADMIN_NOTICE_CONTENT = 'ppplus_message_content';

    /**
     * @var wpdb
     */
    private $wpdb;

    /**
     * @var ResourceDictionary
     */
    private $resourceDictionary;

    /**
     * @var WP_Filesystem_Base
     */
    private $fileSystem;

    /**
     * Uninstaller constructor.
     * @param wpdb $wpdb
     * @param WP_Filesystem_Base | null $fileSystem
     */
    public function __construct(wpdb $wpdb, $fileSystem)
    {
        $this->wpdb = $wpdb;
        if ($fileSystem) {
            $this->fileSystem = $fileSystem;
        }
    }

    /**
     * Uninstall Plugin
     */
    public function multisiteUninstall()
    {
        $noticePrefix = DismissibleNoticeOption::OPTION_PREFIX;
        $this->wpdb->query(
            "DELETE FROM {$this->wpdb->sitemeta} WHERE 'meta_key' LIKE '{$noticePrefix}%'"
        );

        $sites = get_sites(['fields' => 'ids']);

        $networkState = NetworkState::create();
        foreach ($sites as $blogId) {
            switch_to_blog($blogId);
            $this->deleteOptions();
        }
        $networkState->restore();

        $this->cleanUp();
        if ($this->fileSystem) {
            $this->deleteCacheAssetsFiles();
        }
    }

    /**
     * Uninstall Plugin From all of the Sites of a Multisite Installation
     */
    public function uninstall()
    {
        $this->deleteOptions();
        $this->cleanUp();
        if ($this->fileSystem) {
            $this->deleteCacheAssetsFiles();
        }
    }

    /**
     * Delete PayPal Assets Files
     */
    protected function deleteCacheAssetsFiles()
    {
        $uploadDir = wp_upload_dir();
        $uploadDir = isset($uploadDir['basedir']) ? $uploadDir['basedir'] : '';
        $uploadDir = untrailingslashit($uploadDir);

        if (!$uploadDir) {
            return;
        }

        $this->fileSystem->exists("{$uploadDir}/woo-paypalplus") and $this->fileSystem->delete(
            "{$uploadDir}/woo-paypalplus",
            true
        );
    }

    /**
     * Clean up After Uninstall
     */
    private function cleanUp()
    {
        wp_cache_flush();
    }

    /**
     * Delete options
     */
    private function deleteOptions()
    {
        $noticePrefix = DismissibleNoticeOption::OPTION_PREFIX;

        delete_option(self::OPTION_PAYPAL_PLUS_GATEWAY);
        delete_option(self::OPTION_EXPRESS_CHECKOUT_GATEWAY);
        delete_option(SharedPersistor::OPTION_NAME);

        delete_site_transient(self::OPTION_ADMIN_NOTICE_MESSAGE_ID);
        delete_site_transient(self::OPTION_ADMIN_NOTICE_CONTENT);

        $this->wpdb->query(
            "DELETE FROM {$this->wpdb->options} WHERE 'option_name' LIKE '{$noticePrefix}%'"
        );
        $this->wpdb->query(
            "DELETE FROM {$this->wpdb->options} WHERE 'option_name' LIKE 'woocommerce_ppec_payer_id_%'"
        );
    }
}
