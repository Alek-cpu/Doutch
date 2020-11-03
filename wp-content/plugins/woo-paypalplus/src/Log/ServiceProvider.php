<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Log;

use WCPayPalPlus\Service\ServiceProvider as ServiceProviderInterface;
use WCPayPalPlus\Service\Container;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Log
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[Logger::class] = function () {
            return new WcPsrLoggerAdapter(\wc_get_logger());
        };
    }
}
