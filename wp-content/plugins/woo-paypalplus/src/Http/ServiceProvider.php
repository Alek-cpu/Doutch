<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Http;

use WCPayPalPlus\Http\PayPalAssetsCache\AssetsStoreUpdater;
use WCPayPalPlus\Http\PayPalAssetsCache\CronScheduler;
use WCPayPalPlus\Http\PayPalAssetsCache\RemoteResourcesStorer;
use WCPayPalPlus\Http\PayPalAssetsCache\RemoteResourcesStorerFactory;
use WCPayPalPlus\Http\PayPalAssetsCache\ResourceDictionary;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Http
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register(Container $container)
    {
        $uploadDir = wp_upload_dir();
        $uploadDir = isset($uploadDir['basedir']) ? $uploadDir['basedir']
            : '';

        if (!$uploadDir) {
            return;
        }

        $container->addService(
            CronScheduler::class,
            function (Container $container) {
                return new CronScheduler(
                    $container[AssetsStoreUpdater::class]
                );
            }
        );

        $container->addService(
            RemoteResourcesStorer::class,
            function (Container $container) {
                $cachedPayPalJsFiles = $container->get('cache_PayPal_Js_Files');
                $fileSystem = $container->get('wp_filesystem');
                return RemoteResourcesStorerFactory::create(
                    $fileSystem,
                    $cachedPayPalJsFiles
                );
            }
        );

        $container->addService(
            'banner_sdk_script_url',
            function () {
                $clientId = get_option('banner_settings_client_id');
                $currency = get_woocommerce_currency();
                if (!is_string($clientId) || !is_string($currency)) {
                    return false;
                }

                return "https://www.paypal.com/sdk/js?client-id={$clientId}&components=messages&currency={$currency}";
            }
        );

        $container->addService(
            ResourceDictionary::class,
            function (Container $container) use ($uploadDir) {
                return new ResourceDictionary(
                    [
                        "{$uploadDir}/woo-paypalplus/resources/js/paypal/expressCheckout.min.js" => 'https://www.paypalobjects.com/api/checkout.min.js',
                        "{$uploadDir}/woo-paypalplus/resources/js/paypal/payPalplus.min.js" => 'https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js',
                        "{$uploadDir}/woo-paypalplus/resources/js/paypal/paypalBanner.min.js" => $container->get(
                            'banner_sdk_script_url'
                        ),
                    ]
                );
            }
        );

        $container->addService(
            AssetsStoreUpdater::class,
            function (Container $container) {
                return new AssetsStoreUpdater(
                    $container->get(RemoteResourcesStorer::class),
                    $container->get(ResourceDictionary::class)
                );
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function bootstrap(Container $container)
    {
        $cachedPayPalJsFiles = $container->get('cache_PayPal_Js_Files');
        if (!$cachedPayPalJsFiles) {
            return;
        }
        $cronScheduler = $container->get(CronScheduler::class);

        add_filter(
            'cron_schedules',
            static function (array $schedules) use ($cronScheduler) {
                return $cronScheduler->addWeeklyRecurrence($schedules);
            }
        );

        add_action('wp_enqueue_scripts', [$cronScheduler, 'schedule'], 0);

        add_action(
            CronScheduler::CRON_HOOK_NAME,
            [$container->get(AssetsStoreUpdater::class), 'update']
        );
    }
}
