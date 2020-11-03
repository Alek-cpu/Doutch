<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Order;

use Exception;

/**
 * Class OrderFactoryException
 * @package WCPayPalPlus\Order
 */
class OrderFactoryException extends Exception
{
    /**
     * @param $orderId
     * @return OrderFactoryException
     */
    public static function forInvalidOrderId($orderId)
    {
        assert(is_int($orderId));

        return new self("Cannot create order by value {$orderId}");
    }
}
