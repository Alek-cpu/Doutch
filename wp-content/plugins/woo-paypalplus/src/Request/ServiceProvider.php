<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Request;

use Brain\Nonces\NonceContextInterface;
use Brain\Nonces\RequestGlobalsContext;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\ServiceProvider as ServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Request
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[NonceContextInterface::class] = function () {
            return new RequestGlobalsContext();
        };
        $container[Request::class] = function () {
            return new Request(filter_input_array(INPUT_POST) ?: []);
        };
    }
}
