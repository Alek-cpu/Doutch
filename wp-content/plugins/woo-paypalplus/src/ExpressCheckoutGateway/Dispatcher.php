<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

/**
 * Class Dispatcher
 * @package WCPayPalPlus\ExpressCheckout
 */
class Dispatcher
{
    const ACTION_DISPATCH_CONTEXT = 'woopaypalplus.express_checkout_request';

    /**
     * Dispatch a request by the given context
     *
     * @param string $context
     * @param string $action
     * @param array $data
     * @return mixed
     */
    public function dispatch($context, $action, array $data)
    {
        assert(is_string($context));
        assert(is_string($action));

        /**
         * Dispatch action
         *
         * @param array $data
         */
        do_action(self::ACTION_DISPATCH_CONTEXT . "/{$context}/{$action}", $data);
    }
}
