<?php
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Notice;

use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Notice
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    public function register(Container $container)
    {
        $container[Admin::class] = function (Container $container) {
            return new Admin();
        };
    }

    public function bootstrap(Container $container)
    {
        $container[Admin::class]->setupActions();

        add_action(
            Admin::ACTION_ADMIN_MESSAGES,
            [$container[Admin::class], 'display'],
            20
        );
    }
}
