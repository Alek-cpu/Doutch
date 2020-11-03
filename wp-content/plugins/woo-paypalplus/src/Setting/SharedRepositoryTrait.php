<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Setting;

/**
 * Trait SharedRepositoryHelper
 * @package WCPayPalPlus\Setting
 */
trait SharedRepositoryTrait
{
    /**
     * @inheritdoc
     */
    public function isSandboxed()
    {
        $option = $this->get_option(self::OPTION_TEST_MODE_NAME, self::OPTION_ON);

        return $option === self::OPTION_ON;
    }

    /**
     * @inheritdoc
     */
    public function experienceProfileId()
    {
        $option = $this->isSandboxed()
            ? Storable::OPTION_PROFILE_ID_SANDBOX_NAME
            : Storable::OPTION_PROFILE_ID_PRODUCTION_NAME;

        return $this->get_option($option, '');
    }

    /**
     * @inheritdoc
     */
    public function cancelUrl()
    {
        $option = $this->get_option(Storable::OPTION_CANCEL_URL_NAME, '');

        switch ($option) {
            case 'cart':
                $url = wc_get_cart_url();
                break;
            case 'checkout':
                $url = wc_get_checkout_url();
                break;
            case 'account':
                $url = wc_get_account_endpoint_url('dashboard');
                break;
            case 'custom':
                $url = esc_url($this->cancelCustomUrl());
                break;
            case 'shop':
            default:
                $url = get_permalink(wc_get_page_id('shop'));
                break;
        }

        return $url;
    }

    /**
     * @inheritdoc
     */
    public function cancelCustomUrl()
    {
        return $this->get_option(Storable::OPTION_CANCEL_CUSTOM_URL_NAME, '');
    }

    /**
     * @inheritdoc
     */
    public function returnUrl()
    {
        return wc_get_checkout_url();
    }

    /**
     * @inheritdoc
     */
    public function paypalUrl()
    {
        return $this->isSandboxed() ? self::PAYPAL_SANDBOX_URL : self::PAYPAL_LIVE_URL;
    }

    /**
     * @inheritdoc
     */
    public function userAgent()
    {
        return 'WooCommerce/' . $this->wooCommerce->version;
    }

    /**
     * @return string
     */
    public function clientIdSandBox()
    {
        return $this->get_option(Storable::OPTION_CLIENT_ID_SANDBOX, '');
    }

    /**
     * @return string
     */
    public function secretIdSandBox()
    {
        return $this->get_option(Storable::OPTION_SECRET_ID_SANDBOX, '');
    }

    /**
     * @return string
     */
    public function clientIdProduction()
    {
        return $this->get_option(Storable::OPTION_CLIENT_ID, '');
    }

    /**
     * @return string
     */
    public function secretIdProduction()
    {
        return $this->get_option(Storable::OPTION_SECRET_ID, '');
    }

    /**
     * @inheritdoc
     */
    public function invoicePrefix()
    {
        return $this->get_option(Storable::OPTION_INVOICE_PREFIX, '');
    }
}
