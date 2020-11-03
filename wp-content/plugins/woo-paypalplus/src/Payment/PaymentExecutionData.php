<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Payment;

use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Api\PaymentExecution;
use Inpsyde\Lib\PayPal\Rest\ApiContext;

/**
 * Class PaymentExecutionData
 *
 * @package WCPayPalPlus\Payment
 */
class PaymentExecutionData
{
    const PAYMENT_STATE_APPROVED = 'approved';

    /**
     * The payer ID.
     *
     * @var string
     */
    private $payer_id;

    /**
     * The Payment ID.
     *
     * @var string
     */
    private $payment_id;

    /**
     * The PayPal SDK ApiContext object.
     *
     * @var ApiContext
     */
    private $context;

    /**
     * The WooCommerce Order object.
     *
     * @var \WC_Order
     */
    private $order;

    /**
     * PaymentExecutionData constructor.
     *
     * @param \WC_Order $order The WooCommerce Order object.
     * @param string $payer_id The payer ID.
     * @param string $payment_id The Payment ID.
     * @param ApiContext $context The PayPal SDK ApiContext object.
     */
    public function __construct(
        \WC_Order $order,
        $payer_id,
        $payment_id,
        ApiContext $context
    ) {

        $this->payer_id = $payer_id;
        $this->payment_id = $payment_id;
        $this->context = $context;
        $this->order = $order;
    }

    /**
     * Check if this is a Pay Upon Invoice Payment
     *
     * @return bool
     */
    public function is_pui()
    {
        $instructions = $this->get_payment_instruction();

        return (isset($instructions) && !empty($instructions));
    }

    /**
     * Returns the Payment Instruction object, if it exists
     *
     * @return \Inpsyde\Lib\PayPal\Api\PaymentInstruction
     */
    public function get_payment_instruction()
    {
        return $this
            ->get_payment()
            ->getPaymentInstruction();
    }

    /**
     * Fetches and returns a PayPal Payment object
     *
     * @return Payment
     */
    public function get_payment()
    {
        static $payment = null;

        if ($payment !== null) {
            return $payment;
        }

        $payment or $payment = Payment::get($this->payment_id, $this->context);

        return $payment;
    }

    /**
     * Checks if the Payment status is approved
     *
     * @return bool;
     */
    public function isApproved()
    {
        return $this->paymentState() === self::PAYMENT_STATE_APPROVED;
    }

    /**
     * Returns the Payment state.
     *
     * @return string
     */
    public function paymentState()
    {
        return $this->get_payment()->state;
    }

    /**
     * Returns the sale object of the payment
     *
     * @return \PayPal\Api\Sale
     */
    public function get_sale()
    {
        $transactions = $this
            ->get_payment()
            ->getTransactions();
        $related_resources = $transactions[0]->getRelatedResources();

        return $related_resources[0]->getSale();
    }

    /**
     * Returns a configured PaymentExecution object.
     *
     * @return PaymentExecution
     * @throws \InvalidArgumentException
     */
    public function get_payment_execution()
    {
        $execution = new PaymentExecution();
        $execution->setPayerId($this->payer_id);

        return $execution;
    }

    /**
     * Returns the Payment ID.
     *
     * @return string
     */
    public function get_payment_id()
    {
        return $this->payment_id;
    }

    /**
     * Returns the payer ID
     *
     * @return string
     */
    public function get_payer_id()
    {
        return $this->payer_id;
    }

    /**
     * Returns the PayPal SDK ApiContext object.
     *
     * @return ApiContext
     */
    public function get_context()
    {
        return $this->context;
    }

    /**
     * Returns the WooCommerce Order object
     *
     * @return \WC_Order
     */
    public function get_order()
    {
        return $this->order;
    }
}
