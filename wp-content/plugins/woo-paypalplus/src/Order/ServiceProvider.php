<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Order;

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\ServiceProvider as ServiceProviderInterface;
use WCPayPalPlus\Session\Session;
use WCPayPalPlus\Setting\Storable;
use WooCommerce;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Order
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[OrderStatuses::class] = function () {
            return new OrderStatuses();
        };
        $container[OrderFactory::class] = function () {
            return new OrderFactory();
        };
        $container[OrderUpdaterFactory::class] = function (Container $container) {
            return new OrderUpdaterFactory(
                $container[WooCommerce::class],
                $container[OrderStatuses::class],
                $container[OrderFactory::class],
                $container[Request::class],
                $container[Storable::class],
                $container[Logger::class]
            );
        };
        $container[OrderDataProviderFactory::class] = function (Container $container) {
            return new OrderDataProviderFactory(
                $container[OrderFactory::class],
                $container[Session::class],
                $container[WooCommerce::class]
            );
        };
    }
}
