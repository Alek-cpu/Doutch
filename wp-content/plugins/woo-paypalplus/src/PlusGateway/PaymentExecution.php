<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\PlusGateway;

use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Api\ErrorData\ApiErrorExtractor;
use WCPayPalPlus\ExpressCheckoutGateway\PaymentExecutionTrait;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Payment\PaymentExecutionFactory;
use WCPayPalPlus\Payment\PaymentIdValidator;
use WCPayPalPlus\Payment\PaymentPatcher;
use WCPayPalPlus\Payment\PaymentSessionDestructor;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Session\Session;
use WC_Order;
use WCPayPalPlus\WC\CheckoutDropper;

/**
 * Class PaymentExecution
 * @package WCPayPalPlus\PlusGateway
 */
class PaymentExecution
{
    use PaymentExecutionTrait;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var PaymentExecutionFactory
     */
    private $paymentExecutionFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CheckoutDropper
     */
    private $checkoutDropper;

    /**
     * @var ApiErrorExtractor
     */
    private $apiErrorDataExtractor;

    /**
     * @var PaymentIdValidator
     */
    private $paymentIdValidator;

    /**
     * @var PaymentSessionDestructor
     */
    private $paymentSessionDestructor;

    /**
     * PaymentExecution constructor.
     * @param OrderFactory $orderFactory
     * @param Session $session
     * @param PaymentExecutionFactory $paymentExecutionFactory
     * @param Logger $logger
     * @param CheckoutDropper $checkoutDropper
     * @param ApiErrorExtractor $apiErrorDataExtractor
     * @param PaymentIdValidator $paymentIdValidator
     * @param PaymentSessionDestructor $paymentSessionDestructor
     */
    public function __construct(
        OrderFactory $orderFactory,
        Session $session,
        PaymentExecutionFactory $paymentExecutionFactory,
        Logger $logger,
        CheckoutDropper $checkoutDropper,
        ApiErrorExtractor $apiErrorDataExtractor,
        PaymentIdValidator $paymentIdValidator,
        PaymentSessionDestructor $paymentSessionDestructor
    ) {

        $this->orderFactory = $orderFactory;
        $this->session = $session;
        $this->paymentExecutionFactory = $paymentExecutionFactory;
        $this->logger = $logger;
        $this->checkoutDropper = $checkoutDropper;
        $this->apiErrorDataExtractor = $apiErrorDataExtractor;
        $this->paymentIdValidator = $paymentIdValidator;
        $this->paymentSessionDestructor = $paymentSessionDestructor;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \WCPayPalPlus\Order\OrderFactoryException
     * @throws \WCPayPalPlus\Payment\PaymentProcessException
     */
    public function execute()
    {
        $payerId = filter_input(INPUT_GET, Request::INPUT_PAYER_ID, FILTER_SANITIZE_STRING);
        $paymentId = filter_input(INPUT_GET, Request::INPUT_PAYMENT_ID, FILTER_SANITIZE_STRING);
        $orderId = $this->session->get(Session::ORDER_ID);

        if (!$paymentId) {
            $paymentId = $this->session->get(Session::PAYMENT_ID);
        }
        if (!$payerId || !$orderId) {
            return;
        }

        /*
         * This is usually not necessary because we catch the problem when we perform the patch
         * but just to cover all scenarios and ensure the user get a valid feedback about the
         * problem.
         */
        if (!$this->paymentIdValidator->isPaymentIdValid($paymentId)) {
            $this->paymentSessionDestructor->becauseInvalidPaymentId();
            wp_safe_redirect(wc_get_cart_url());
            exit;
        }

        $order = $this->orderFactory->createById($orderId);

        $payment = $this->paymentExecutionFactory->create(
            $order,
            $payerId,
            $paymentId,
            ApiContextFactory::getFromConfiguration()
        );

        try {
            $payment->execute();
        } catch (PayPalConnectionException $exc) {
            $apiError = $this->apiErrorDataExtractor->extractByException($exc);
            $this->checkoutDropper->abortSessionBecauseOfApiError($apiError);
        }

        /**
         * Action After Payment has been Executed
         *
         * @param PaymentPatcher $payment
         * @param WC_Order $order
         */
        do_action(Gateway::ACTION_AFTER_PAYMENT_EXECUTION, $payment, $order);

        wp_safe_redirect($order->get_checkout_order_received_url());
        exit;
    }
}
