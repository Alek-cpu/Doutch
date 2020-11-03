<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Payment\PaymentExecutionFactory;
use WC_Order;
use WCPayPalPlus\Session\Session;

/**
 * Trait PaymentExecutionTrait
 * @property PaymentExecutionFactory $paymentExecutionFactory
 * @property Logger $logger
 * @property Session $session
 * @package WCPayPalPlus\ExpressCheckoutGateway
 */
trait PaymentExecutionTrait
{
    /**
     * Execute Payment
     *
     * @param $order
     * @param $payerId
     * @param $paymentId
     * @throws \InvalidArgumentException
     * @throws \WCPayPalPlus\Payment\PaymentProcessException
     */
    private function execute($order, $payerId, $paymentId)
    {
        $payment = $this->paymentExecutionFactory->create(
            $order,
            $payerId,
            $paymentId,
            ApiContextFactory::getFromConfiguration()
        );

        $payment->execute();

        /**
         * Action After Payment has been Executed
         *
         * @param Payment $payment
         * @param WC_Order $order
         */
        do_action(Gateway::ACTION_AFTER_PAYMENT_EXECUTION, $payment, $order);
    }
}
