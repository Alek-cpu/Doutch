<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Api;

use WCPayPalPlus\ExpressCheckoutGateway\Gateway as ExpressCheckoutGateway;
use WCPayPalPlus\Gateway\CurrentPaymentMethod;
use WCPayPalPlus\PlusGateway\Gateway as PlusGateway;

/**
 * Class BnCode
 * @package WCPayPalPlus\Api
 */
class PartnerAttributionId
{
    const FILTER_API_BN_CODE = 'woopaypalplus.api_bncode';

    const PAYPAL_PLUS_BN_CODE = 'WooCommerce_Cart_Plus';
    const PAYPAL_EXPRESS_CHECKOUT_BN_CODE = 'Woo_Cart_ECS';
    const PAYPAL_DEFAULT_BN_CODE = self::PAYPAL_PLUS_BN_CODE;

    const PAYMENT_METHODS = [
        PlusGateway::GATEWAY_ID => self::PAYPAL_PLUS_BN_CODE,
        ExpressCheckoutGateway::GATEWAY_ID => self::PAYPAL_EXPRESS_CHECKOUT_BN_CODE,
    ];

    /**
     * @var CurrentPaymentMethod
     */
    private $currentPaymentMethod;

    /**
     * BnCode constructor.
     * @param CurrentPaymentMethod $currentPaymentMethod
     */
    public function __construct(CurrentPaymentMethod $currentPaymentMethod)
    {
        $this->currentPaymentMethod = $currentPaymentMethod;
    }

    /**
     * @return string
     */
    public function bnCode()
    {
        $paymentMethod = $this->currentPaymentMethod->payment();

        if (!array_key_exists($paymentMethod, self::PAYMENT_METHODS)) {
            return self::PAYPAL_DEFAULT_BN_CODE;
        }

        return self::PAYMENT_METHODS[$paymentMethod];
    }
}
