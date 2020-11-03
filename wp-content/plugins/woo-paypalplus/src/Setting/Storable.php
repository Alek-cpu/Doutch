<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Setting;

/**
 * Interface Storable
 * @package WCPayPalPlus\Setting
 */
interface Storable
{
    const OPTION_ON = 'yes';
    const OPTION_OFF = 'no';

    const OPTION_PREFIX = 'paypalplus_';

    const PAYPAL_SANDBOX_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    const PAYPAL_LIVE_URL = 'https://www.paypal.com/cgi-bin/webscr';

    const OPTION_PROFILE_ID_SANDBOX_NAME = 'sandbox_experience_profile_id';
    const OPTION_PROFILE_ID_PRODUCTION_NAME = 'live_experience_profile_id';
    const OPTION_PROFILE_CHECKOUT_LOGO = 'checkout_logo';
    const OPTION_PROFILE_BRAND_NAME = 'brand_name';

    const OPTION_CANCEL_URL_NAME = 'cancel_url';
    const OPTION_CANCEL_CUSTOM_URL_NAME = 'cancel_custom_url';

    const OPTION_TEST_MODE_NAME = 'testmode';

    const OPTION_CLIENT_ID = 'rest_client_id';
    const OPTION_SECRET_ID = 'rest_secret_id';

    const OPTION_CLIENT_ID_SANDBOX = 'rest_client_id_sandbox';
    const OPTION_SECRET_ID_SANDBOX = 'rest_secret_id_sandbox';

    const OPTION_INVOICE_PREFIX = 'invoice_prefix';
    const OPTION_CACHE_PAYPAL_JS_FILES = 'cache_paypal_js_files';

    const ACTION_AFTER_SETTINGS_UPDATE = 'paypalplus.after_settings_update';

    /**
     * @return bool
     */
    public function isSandboxed();

    /**
     * @return string
     */
    public function paypalUrl();

    /**
     * @return string
     */
    public function userAgent();

    /**
     * @return string
     */
    public function cancelUrl();

    /**
     * @return string
     */
    public function cancelCustomUrl();

    /**
     * @return string
     */
    public function returnUrl();

    /**
     * @return string
     */
    public function experienceProfileId();

    /**
     * @return string
     */
    public function clientIdSandBox();

    /**
     * @return string
     */
    public function secretIdSandBox();

    /**
     * @return string
     */
    public function clientIdProduction();

    /**
     * @return string
     */
    public function secretIdProduction();

    /**
     * @return string
     */
    public function invoicePrefix();
}
