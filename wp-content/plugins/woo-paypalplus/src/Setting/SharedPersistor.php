<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Setting;

/**
 * Class SharedPersistor
 * @package WCPayPalPlus\Setting
 */
class SharedPersistor
{
    const OPTION_NAME = 'paypalplus_shared_options';

    /**
     * Update the Shared Settings
     *
     * @param array $settings
     */
    public function update(array $settings)
    {
        $settings = SharedSettingsFilter::intersect($settings);

        if (!$settings) {
            return;
        }

        update_option(self::OPTION_NAME, $settings);
    }

    /**
     * Delete all Options
     *
     * @return void
     */
    public function deleteAll()
    {
        delete_option(self::OPTION_NAME);
    }
}
