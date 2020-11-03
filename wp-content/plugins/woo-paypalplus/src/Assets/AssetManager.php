<?php
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Assets;

use WCPayPalPlus\Admin\Notice;
use WCPayPalPlus\ExpressCheckoutGateway\AjaxHandler;
use WCPayPalPlus\PluginProperties;

/**
 * Class AssetManager
 * @package WCPayPalPlus\Assets
 */
class AssetManager
{
    use AssetManagerTrait;

    const FILTER_EXPRESS_CHECKOUT_JS_DATA = 'paypalplus.express_checkout_data';

    /**
     * @var PluginProperties
     */
    private $pluginProperties;

    /**
     * @var SmartButtonArguments
     */
    private $smartButtonArguments;

    /**
     * AssetManager constructor.
     * @param PluginProperties $pluginProperties
     * @param SmartButtonArguments $smartButtonArguments
     */
    public function __construct(
        PluginProperties $pluginProperties,
        SmartButtonArguments $smartButtonArguments
    ) {

        /** @noinspection UnusedConstructorDependenciesInspection */
        $this->pluginProperties = $pluginProperties;
        $this->smartButtonArguments = $smartButtonArguments;
    }

    /**
     * Enqueue Admin Scripts
     */
    public function enqueueAdminScripts()
    {
        list($assetPath, $assetUrl) = $this->assetUrlPath();

        wp_enqueue_script(
            'paypalplus-woocommerce-admin',
            "{$assetUrl}/public/js/admin.min.js",
            ['jquery'],
            filemtime("{$assetPath}/public/js/admin.min.js"),
            true
        );
        wp_localize_script(
            'paypalplus-woocommerce-admin',
            'paypalplus',
            [
                'adminNotice' => [
                    'action' => Notice\AjaxDismisser::AJAX_NONCE_ACTION,
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'ajaxNonce' => wp_create_nonce(Notice\AjaxDismisser::AJAX_NONCE_ACTION),
                ],
            ]
        );
        wp_enqueue_script(
            'paypalplus-woocommerce-adminNoticeBanner',
            "{$assetUrl}/public/js/adminNoticeBanner.min.js",
            ['jquery'],
            filemtime("{$assetPath}/public/js/adminNoticeBanner.min.js"),
            true
        );
        $this->loadScriptsData(
            'paypalplus-woocommerce-adminNoticeBanner',
            'adminNoticeBannerData',
            [
                'urls' => [
                    'ajax' => admin_url('admin-ajax.php'),
                    'banner_settings_tab' => admin_url(
                        'admin.php?page=wc-settings&tab=paypalplus-banner'
                    ),
                ],
            ]
        );
    }

    /**
     * Enqueue Admin Styles
     */
    public function enqueueAdminStyles()
    {
        list($assetPath, $assetUrl) = $this->assetUrlPath();

        wp_enqueue_style(
            'paypalplus-woocommerce-admin',
            "{$assetUrl}/public/css/admin.min.css",
            [],
            filemtime("{$assetPath}/public/css/admin.min.css"),
            'screen'
        );
    }

    /**
     * Enqueue Frontend Scripts
     */
    public function enqueueFrontEndScripts()
    {
        list($assetPath, $assetUrl) = $this->assetUrlPath();

        wp_enqueue_script(
            'paypalplus-woocommerce-front',
            "{$assetUrl}/public/js/front.min.js",
            ['underscore', 'jquery'],
            filemtime("{$assetPath}/public/js/front.min.js"),
            true
        );
        $this->loadScriptsData(
            'paypalplus-woocommerce-front',
            'pppFrontDataCollection',
            [
                'pageinfo'=>
                [
                    'isCheckout' => is_checkout(),
                    'isCheckoutPayPage' => is_checkout_pay_page(),
                ],
                'isConflictVersion' => version_compare(
                    wc()->version,
                    '3.9',
                    '>='
                ),
            ]
        );

        $this->enqueuePayPalFrontEndScripts();
    }

    /**
     * Enqueue Frontend Styles
     */
    public function enqueueFrontendStyles()
    {
        list($assetPath, $assetUrl) = $this->assetUrlPath();

        wp_enqueue_style(
            'paypalplus-woocommerce-front',
            "{$assetUrl}/public/css/front.min.css",
            [],
            filemtime("{$assetPath}/public/css/front.min.css"),
            'screen'
        );
    }

    /**
     * Enqueue PayPal Specific Scripts
     */
    private function enqueuePayPalFrontEndScripts()
    {
        list($assetPath, $assetUrl) = $this->assetUrlPath();

        wp_register_script(
            'bluebird',
            'https://cdn.jsdelivr.net/npm/bluebird@3.5.3/js/browser/bluebird.js',
            []
        );

        wp_register_script(
            'paypalplus-woocommerce-plus-paypal-redirect',
            "{$assetUrl}/public/js/payPalRedirect.min.js",
            ['jquery'],
            filemtime("{$assetPath}/public/js/payPalRedirect.min.js"),
            true
        );
        $this->loadScriptsData(
            'paypalplus-woocommerce-plus-paypal-redirect',
            'payPalRedirect',
            [
                'message' => __(
                    'Thank you for your order. We are now redirecting you to PayPal to make payment.',
                    'woo-paypalplus'
                ),
            ]
        );

        wp_enqueue_script(
            'paypalplus-express-checkout',
            "{$assetUrl}/public/js/expressCheckout.min.js",
            ['underscore', 'jquery', 'paypal-express-checkout', 'bluebird'],
            filemtime("{$assetPath}/public/js/expressCheckout.min.js"),
            true
        );
        $this->loadScriptsData(
            'paypalplus-express-checkout',
            'wooPayPalPlusExpressCheckout',
            $this->expressCheckoutScriptData()
        );
    }

    /**
     * Build the Express Checkout Data
     *
     * @return array
     */
    private function expressCheckoutScriptData()
    {
        $data = [
            'validContexts' => AjaxHandler::VALID_CONTEXTS,
            'request' => [
                'action' => AjaxHandler::ACTION,
                'ajaxUrl' => home_url('/wp-admin/admin-ajax.php'),
            ],
            'paymentButtonRenderEvents' => [
                'wc_fragments_refreshed',
                'wc_fragments_loaded',
                'removed_from_cart',
                'added_to_cart',
                'updated_shipping_method',
            ],
        ];

        /**
         * Filter Express Checkout Data
         *
         * @param array $data List of the data to consume client side
         */
        $data = apply_filters(self::FILTER_EXPRESS_CHECKOUT_JS_DATA, $data);

        /** @noinspection AdditionOperationOnArraysInspection */
        return $data + $this->smartButtonArguments->toArray();
    }
}
