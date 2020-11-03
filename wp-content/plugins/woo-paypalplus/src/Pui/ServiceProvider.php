<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Pui;

use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Pui
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    public function register(Container $container)
    {
        $container[Renderer::class] = function (Container $container) {
            return new Renderer(
                $container[Setting\PlusStorable::class]
            );
        };
    }

    public function bootstrap(Container $container)
    {
        $pui = $container[Renderer::class];

        add_action('woocommerce_thankyou_paypal_plus', [$pui, 'delegateThankyou'], 10, 1);
        add_action('woocommerce_email_before_order_table', [$pui, 'delegateEmail'], 10, 3);
        add_action('woocommerce_view_order', [$pui, 'delegateViewOrder'], 10, 1);
    }
}
