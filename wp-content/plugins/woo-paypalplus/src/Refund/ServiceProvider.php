<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Refund;

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Order\OrderStatuses;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\ServiceProvider as ServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Refund
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[RefundFactory::class] = function (Container $container) {
            return new RefundFactory(
                $container[OrderStatuses::class],
                $container[Logger::class]
            );
        };
    }
}
