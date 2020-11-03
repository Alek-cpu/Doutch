<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Setting;

use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\PlusGateway\Gateway as PlusGateway;
use WCPayPalPlus\ExpressCheckoutGateway\Gateway as ExpressCheckoutGateway;
use WooCommerce;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Setting
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container->share(Storable::class, function (Container $container) {
            return $container[SharedRepository::class];
        });
        $container->share(PlusStorable::class, function (Container $container) {
            return $container[PlusGateway::class];
        });
        $container->share(ExpressCheckoutStorable::class, function (Container $container) {
            return $container[ExpressCheckoutGateway::class];
        });
        $container[SharedRepository::class] = function (Container $container) {
            return new SharedRepository(
                $container[WooCommerce::class]
            );
        };
        $container[SharedSettingsModel::class] = function () {
            return new SharedSettingsModel();
        };
        $container[SharedPersistor::class] = function () {
            return new SharedPersistor();
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        add_filter(
            Storable::ACTION_AFTER_SETTINGS_UPDATE,
            [$container[SharedPersistor::class], 'update']
        );

        add_filter(
            'woocommerce_settings_api_sanitized_fields_' . ExpressCheckoutGateway::GATEWAY_ID,
            [SharedSettingsFilter::class, 'diff']
        );
        add_filter(
            'woocommerce_settings_api_sanitized_fields_' . PlusGateway::GATEWAY_ID,
            [SharedSettingsFilter::class, 'diff']
        );
    }
}
