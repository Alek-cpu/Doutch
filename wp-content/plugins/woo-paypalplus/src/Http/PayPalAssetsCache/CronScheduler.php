<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Http\PayPalAssetsCache;

/**
 * Class CronScheduler
 * @package WCPayPalPlus\Http\PayPalAssetsCache
 */
class CronScheduler
{
    const CRON_HOOK_NAME = 'paypalplus.assets_cache_cron_schedule_hook';

    /**
     * @var AssetsStoreUpdater
     */
    private $assetsStoreUpdater;

    /**
     * CronScheduler constructor.
     * @param AssetsStoreUpdater $assetsStoreUpdater
     */
    public function __construct(AssetsStoreUpdater $assetsStoreUpdater)
    {
        $this->assetsStoreUpdater = $assetsStoreUpdater;
    }

    /**
     * Add new Schedule Recurrence
     *
     * @param array $schedules
     * @return array
     */
    public function addWeeklyRecurrence(array $schedules)
    {
        !isset($schedules['weekly']) and $schedules['weekly'] = [
            'interval' => WEEK_IN_SECONDS,
            'display' => __('Weekly', 'woo-paypalplus'),
        ];

        return $schedules;
    }

    /**
     * Schedule the event
     *
     * @return void
     */
    public function schedule()
    {
        if (!wp_next_scheduled(self::CRON_HOOK_NAME)) {
            wp_schedule_event(time() + MINUTE_IN_SECONDS, 'weekly', self::CRON_HOOK_NAME);
            $this->assetsStoreUpdater->update();
        }
    }
}
