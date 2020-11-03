<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use WCPayPalPlus\Gateway\CurrentPaymentMethod;
use WCPayPalPlus\Session\Session;

/**
 * Class CheckoutGatewayOverride
 */
class CheckoutGatewayOverride
{
    /**
     * @var CurrentPaymentMethod
     */
    private $currentPaymentMethod;

    /**
     * CheckoutGatewayOverride constructor.
     * @param CurrentPaymentMethod $currentPaymentMethod
     */
    public function __construct(CurrentPaymentMethod $currentPaymentMethod)
    {
        $this->currentPaymentMethod = $currentPaymentMethod;
    }

    /**
     * Overwrite Payment Gateways
     * @param array $availableGateways
     *
     * @return array
     */
    public function maybeOverridden(array $availableGateways)
    {
        if (!isset($availableGateways[Gateway::GATEWAY_ID])) {
            return $availableGateways;
        }

        $gateway = $availableGateways[Gateway::GATEWAY_ID];
        unset($availableGateways[Gateway::GATEWAY_ID]);

        if (Gateway::GATEWAY_ID === $this->currentPaymentMethod->payment()) {
            $availableGateways = [
                Gateway::GATEWAY_ID => $gateway,
            ];
        }

        return $availableGateways;
    }
}
