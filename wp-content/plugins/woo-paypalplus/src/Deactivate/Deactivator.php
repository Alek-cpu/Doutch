<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Deactivate;

use WCPayPalPlus\Http\PayPalAssetsCache\CronScheduler;

/**
 * Class Deactivator
 * @package WCPayPalPlus\Deactivate
 */
class Deactivator
{
    /**
     * Deactivate
     *
     * @return void
     */
    public function deactivate()
    {
        $this->unscheduleCron();
    }

    /**
     * Unschedule Cron Events
     *
     * @return void
     */
    protected function unscheduleCron()
    {
        $timestamp = wp_next_scheduled(CronScheduler::CRON_HOOK_NAME);
        wp_unschedule_event($timestamp, CronScheduler::CRON_HOOK_NAME);
    }
}
