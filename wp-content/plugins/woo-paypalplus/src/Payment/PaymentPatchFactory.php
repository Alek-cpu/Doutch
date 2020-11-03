<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Payment;

use Inpsyde\Lib\PayPal\Rest\ApiContext;
use WC_Order;
use WCPayPalPlus\Gateway\CurrentPaymentMethod;
use WCPayPalPlus\Order\OrderDataProviderFactory;

/**
 * Class PaymentPatchFactory
 * @package WCPayPalPlus\Payment
 */
class PaymentPatchFactory
{
    /**
     * @var CurrentPaymentMethod
     */
    private $currentPaymentMethod;

    /**
     * @var OrderDataProviderFactory
     */
    private $orderDataProviderFactory;

    /**
     * PaymentPatchFactory constructor.
     * @param CurrentPaymentMethod $currentPaymentMethod
     * @param OrderDataProviderFactory $orderDataProviderFactory
     */
    public function __construct(
        CurrentPaymentMethod $currentPaymentMethod,
        OrderDataProviderFactory $orderDataProviderFactory
    ) {

        $this->currentPaymentMethod = $currentPaymentMethod;
        $this->orderDataProviderFactory = $orderDataProviderFactory;
    }

    /**
     * @param WC_Order $order
     * @param string $paymentId
     * @param string $invoicePrefix
     * @param ApiContext $context
     *
     * @return PaymentPatcher
     */
    public function create(WC_Order $order, $paymentId, $invoicePrefix, ApiContext $context)
    {
        assert(is_string($paymentId));
        assert(is_string($invoicePrefix));

        $orderDataProvider = $this->orderDataProviderFactory->create();
        $patchProvider = new PatchProvider($order, $orderDataProvider);
        $patchData = new PaymentPatchData(
            $order,
            $paymentId,
            $invoicePrefix,
            $context,
            $patchProvider,
            $this->currentPaymentMethod
        );

        return new PaymentPatcher($patchData);
    }
}
