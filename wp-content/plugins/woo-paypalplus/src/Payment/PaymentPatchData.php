<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Payment;

use InvalidArgumentException;
use Inpsyde\Lib\PayPal\Api\PatchRequest;
use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Rest\ApiContext;
use WC_Order;
use WCPayPalPlus\ExpressCheckoutGateway\Gateway as ExpressCheckoutGateway;
use WCPayPalPlus\Gateway\CurrentPaymentMethod;

/**
 * Class PaymentPatchData
 *
 * @package WCPayPalPlus\Payment
 */
class PaymentPatchData
{
    /**
     * WooCommerce Order object.
     *
     * @var WC_Order
     */
    private $order;

    /**
     * The Payment ID.
     *
     * @var string
     */
    private $paymentId;

    /**
     * The invoice prefix.
     *
     * @var string
     */
    private $invoicePrefix;

    /**
     * The PayPal SDK ApiContext object.
     *
     * @var ApiContext
     */
    private $apiContext;

    /**
     * The PatchProvider object
     *
     * @var PatchProvider
     */
    private $patchProvider;

    /**
     * @var CurrentPaymentMethod
     */
    private $currentPaymentMethod;

    /**
     * PaymentPatchData constructor.
     *
     * @param WC_Order $order WooCommerce Order object.
     * @param string $paymentId The Payment ID.
     * @param string $invoicePrefix The invoice prefix.
     * @param ApiContext $apiContext The PayPal SDK ApiContext object.
     * @param PatchProvider $patchProvider The PatchProvider object.
     * @param CurrentPaymentMethod $currentPaymentMethod
     */
    public function __construct(
        WC_Order $order,
        $paymentId,
        $invoicePrefix,
        ApiContext $apiContext,
        PatchProvider $patchProvider,
        CurrentPaymentMethod $currentPaymentMethod
    ) {

        assert(is_string($paymentId));
        assert(is_string($invoicePrefix));

        $this->order = $order;
        $this->paymentId = $paymentId;
        $this->invoicePrefix = $invoicePrefix;
        $this->apiContext = $apiContext;
        $this->patchProvider = $patchProvider;
        $this->currentPaymentMethod = $currentPaymentMethod;
    }

    /**
     * Returns the WooCommerce Order object
     *
     * @return WC_Order
     */
    public function get_order()
    {
        return $this->order;
    }

    /**
     * Fetches an existing Payment object via API call
     *
     * @return Payment
     */
    public function get_payment()
    {
        return Payment::get($this->get_payment_id(), $this->get_api_context());
    }

    /**
     * Returns the payment ID.
     *
     * @return string
     */
    public function get_payment_id()
    {
        return $this->paymentId;
    }

    /**
     * Returns the APIContext object.
     *
     * @return ApiContext
     */
    public function get_api_context()
    {
        return $this->apiContext;
    }

    /**
     * Returns a configured PatchRequest object.
     *
     * @return PatchRequest
     * @throws InvalidArgumentException
     */
    public function get_patch_request()
    {
        $patch_request = new PatchRequest();
        $patch_request->setPatches($this->get_patches());

        return $patch_request;
    }

    /**
     * Returns an array of configured Patch objects relevant to the current request
     *
     * Use the current payment method as workaround, the solution need a deeper discussion in order
     * to make the patches selectable based on client requests.
     *
     * @return array
     * @throws InvalidArgumentException
     */
    private function get_patches()
    {
        $orderNeedProcessing = $this->order->needs_processing();

        $patches = [
            $this->patchProvider->amount(),
            $this->patchProvider->custom(),
            $this->patchProvider->invoice($this->invoicePrefix),
            $this->patchProvider->items(),
        ];

        if ($orderNeedProcessing) {
            $patches[] = $this->patchProvider->shipping();
        }

        return $patches;
    }
}
