<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Setting;

use WC_Payment_Gateway;

/**
 * Interface SettingsGatewayModel
 * @package WCPayPalPlus\Setting
 */
interface SettingsGatewayModel
{
    /**
     * Return a collection of settings for a Gateway
     *
     * @return array
     */
    public function settings(WC_Payment_Gateway $gateway);
}
