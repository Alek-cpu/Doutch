<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\WC;

use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Api\ErrorData\ApiErrorExtractor;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Payment\PaymentIdValidator;
use WCPayPalPlus\Payment\PaymentSessionDestructor;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\Payment\PaymentPatchFactory;
use WCPayPalPlus\Session\Session;
use OutOfBoundsException;

/**
 * Class RedirectablePatcher
 * @package WCPayPalPlus\WC
 */
class RedirectablePatcher
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var PaymentPatchFactory
     */
    private $paymentPatchFactory;

    /**
     * @var PlusStorable
     */
    private $settingRepository;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CheckoutDropper
     */
    private $checkoutDropper;

    /**
     * @var Logger
     */
    private $logger;

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
     * ReceiptPageRenderer constructor.
     * @param OrderFactory $orderFactory
     * @param PaymentPatchFactory $paymentPatchFactory
     * @param PlusStorable $settingRepository
     * @param Session $session
     * @param CheckoutDropper $checkoutDropper
     * @param Logger $logger
     * @param ApiErrorExtractor $apiErrorDataExtractor
     * @param PaymentIdValidator $paymentIdValidator
     * @param PaymentSessionDestructor $paymentSessionDestructor
     */
    public function __construct(
        OrderFactory $orderFactory,
        PaymentPatchFactory $paymentPatchFactory,
        PlusStorable $settingRepository,
        Session $session,
        CheckoutDropper $checkoutDropper,
        Logger $logger,
        ApiErrorExtractor $apiErrorDataExtractor,
        PaymentIdValidator $paymentIdValidator,
        PaymentSessionDestructor $paymentSessionDestructor
    ) {

        $this->orderFactory = $orderFactory;
        $this->paymentPatchFactory = $paymentPatchFactory;
        $this->settingRepository = $settingRepository;
        $this->session = $session;
        $this->checkoutDropper = $checkoutDropper;
        $this->logger = $logger;
        $this->apiErrorDataExtractor = $apiErrorDataExtractor;
        $this->paymentIdValidator = $paymentIdValidator;
        $this->paymentSessionDestructor = $paymentSessionDestructor;
    }

    /**
     * @param $orderId
     * @throws OutOfBoundsException
     * @throws \WCPayPalPlus\Order\OrderFactoryException
     */
    public function patchOrder($orderId)
    {
        assert(is_int($orderId));

        $this->session->set(Session::ORDER_ID, $orderId);
        $order = $this->orderFactory->createById($orderId);
        $paymentId = $this->session->get(Session::PAYMENT_ID);

        $paymentId or $this->abortPatchingBecausePaymentId($paymentId);

        if (!$this->paymentIdValidator->isPaymentIdValid($paymentId)) {
            $this->paymentSessionDestructor->becauseInvalidPaymentId();
            wp_safe_redirect(wc_get_cart_url());
            exit;
        }

        $paymentPatcher = $this->paymentPatchFactory->create(
            $order,
            $paymentId,
            $this->settingRepository->invoicePrefix(),
            ApiContextFactory::getFromConfiguration()
        );

        try {
            $paymentPatcher->execute();
        } catch (PayPalConnectionException $exc) {
            $apiError = $this->apiErrorDataExtractor->extractByException($exc);
            $this->checkoutDropper->abortSessionBecauseOfApiError($apiError);
        }

        wp_enqueue_script('paypalplus-woocommerce-plus-paypal-redirect');
    }

    /**
     * @param $paymentId
     */
    private function abortPatchingBecausePaymentId($paymentId)
    {
        $this->logger->error("Impossible to update the order, payment id {$paymentId} is not valid.");
        $this->checkoutDropper->abortSessionWithReason(sprintf(
            esc_html__(
                'Impossible to update the order, payment id %s is not valid.',
                'woo-paypalplus'
            ),
            $paymentId
        ));
    }
}
