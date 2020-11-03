<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Utils;

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\ServiceProvider as ServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Utils
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[AjaxJsonRequest::class] = function (Container $container) {
            return new AjaxJsonRequest(
                $container[Logger::class]
            );
        };
    }
}
