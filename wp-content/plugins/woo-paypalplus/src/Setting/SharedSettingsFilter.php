<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Setting;

/**
 * Class SharedSettingsFilter
 * @package WCPayPalPlus\Setting
 */
class SharedSettingsFilter
{
    /**
     * Filter the Gateway Settings by Remove the Shared Ones
     *
     * @param array $settings
     * @return array
     */
    public static function diff(array $settings)
    {
        $settings = array_diff_key($settings, SharedSettingsModel::SHARED_OPTIONS);

        return $settings;
    }

    /**
     * Filter the Gateway Settings to Retrieve Only the Shared Ones
     *
     * @param array $settings
     * @return array
     */
    public static function intersect(array $settings)
    {
        $settings = array_intersect_key($settings, SharedSettingsModel::SHARED_OPTIONS);

        return $settings;
    }
}
