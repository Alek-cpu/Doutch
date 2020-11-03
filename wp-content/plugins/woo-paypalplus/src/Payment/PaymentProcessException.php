<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Payment;

use Exception;
use WCPayPalPlus\Api\ErrorData\Error;
use WCPayPalPlus\Api\ErrorData\Message;

/**
 * Class PaymentProcessException
 * @package WCPayPalPlus\Payment
 */
class PaymentProcessException extends Exception
{
    /**
     * @return PaymentProcessException
     */
    public static function forInsufficientData()
    {
        return new self(
            esc_html__(
                'Payment Execution: Insufficient data to make payment.',
                'woo-paypalplus'
            )
        );
    }

    /**
     * @param $orderId
     * @return PaymentProcessException
     */
    public static function becauseInvalidOrderId($orderId)
    {
        assert(is_int($orderId));

        return new self(
            sprintf(
                esc_html__('Invalid Order ID %s', 'woo-paypalplus'),
                $orderId
            )
        );
    }

    /**
     * @param Error $apiError
     * @return PaymentProcessException
     */
    public static function byApiError(Error $apiError)
    {
        $apiErrorMessage = Message::fromError($apiError);
        return new self($apiErrorMessage());
    }

    /**
     * @param $message
     * @return PaymentProcessException
     */
    public static function becauseInvalidPaymentState($message)
    {
        assert(is_string($message));

        return new self($message);
    }
}
