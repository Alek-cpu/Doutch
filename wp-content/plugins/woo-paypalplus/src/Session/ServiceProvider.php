<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Session;

use WCPayPalPlus\Gateway\CurrentPaymentMethod;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WooCommerce;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Session
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[Session::class] = function (Container $container) {
            return new WooCommerceSession(
                $container[WooCommerce::class]
            );
        };
        $container[SessionCleaner::class] = function (Container $container) {
            return new SessionCleaner(
                $container[Session::class],
                $container[CurrentPaymentMethod::class]
            );
        };
    }

    /**
     * @inheritDoc
     */
    public function bootstrap(Container $container)
    {
        add_filter(
            'template_redirect',
            [$container[SessionCleaner::class], 'cleanByReferer']
        );
    }
}
