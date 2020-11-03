<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Payment;

use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use WCPayPalPlus\WC\RequestSuccessHandler;

/**
 * Class PaymentPerformer
 *
 * @package WCPayPalPlus\Payment
 */
class PaymentPerformer
{
    /**
     * PaymentExecutionData object.
     *
     * @var PaymentExecutionData
     */
    private $data;

    /**
     * SuccessHandler object.
     *
     * @var RequestSuccessHandler
     */
    private $successHandlers;

    /**
     * PaymentPerformer constructor.
     *
     * @param PaymentExecutionData $data PaymentExecutionData object.
     * @param RequestSuccessHandler[] $successHandler Array of SuccessHandler objects.
     */
    public function __construct(
        PaymentExecutionData $data,
        RequestSuccessHandler ...$successHandler
    ) {

        $this->data = $data;
        $this->successHandlers = $successHandler;
    }

    /**
     * Execute Payment
     * Be aware all of the call made by the PayPal SDK may throw a PayPalConnectionException
     *
     * @return void
     * @throws PaymentProcessException
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        $payment = $this->data->get_payment();
        $payment->execute($this->data->get_payment_execution(), $this->data->get_context());

        if (!$this->data->isApproved()) {
            throw PaymentProcessException::becauseInvalidPaymentState(
                sprintf(
                    esc_html__(
                        'There was an error executing the payment. Payment state: %s. Please restart the process and choose a different PayPal wallet.',
                        'woo-paypalplus'
                    ),
                    $this->data->paymentState()
                )
            );
        }

        foreach ($this->successHandlers as $success_handler) {
            $success_handler->execute();
        }
    }
}
