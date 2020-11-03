<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use PayPal\Exception\PayPalConnectionException;
use WCPayPalPlus\Ipn\Ipn;
use WCPayPalPlus\Payment\PaymentCreatorFactory;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Session\Session;
use WCPayPalPlus\Setting\ExpressCheckoutStorable;
use WCPayPalPlus\Setting\Storable;
use WCPayPalPlus\Utils\AjaxJsonRequest;
use Exception;
use WooCommerce;

/**
 * Class CartCheckout
 * @package WCPayPalPlus\ExpressCheckoutGateway
 */
class CartCheckout
{
    const TASK_CREATE_ORDER = 'createOrder';
    const TASK_STORE_PAYMENT_DATA = 'storePaymentData';

    const ACTION_STORE_PAYMENT_DATA = 'woopaypalplus.exc_store_payment_data';

    /**
     * @var PaymentCreatorFactory
     */
    private $paymentCreatorFactory;

    /**
     * @var Storable
     */
    private $settingRepository;

    /**
     * @var AjaxJsonRequest
     */
    private $ajaxJsonRequest;

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Session
     */
    private $session;

    /**
     * CartCheckout constructor.
     * @param ExpressCheckoutStorable $settingRepository
     * @param PaymentCreatorFactory $paymentCreatorFactory
     * @param AjaxJsonRequest $ajaxJsonRequest
     * @param WooCommerce $wooCommerce
     * @param Logger $logger
     * @param Request $request
     * @param Session $session
     */
    public function __construct(
        ExpressCheckoutStorable $settingRepository,
        PaymentCreatorFactory $paymentCreatorFactory,
        AjaxJsonRequest $ajaxJsonRequest,
        WooCommerce $wooCommerce,
        Logger $logger,
        Request $request,
        Session $session
    ) {

        $this->settingRepository = $settingRepository;
        $this->paymentCreatorFactory = $paymentCreatorFactory;
        $this->ajaxJsonRequest = $ajaxJsonRequest;
        $this->wooCommerce = $wooCommerce;
        $this->logger = $logger;
        $this->request = $request;
        $this->session = $session;
    }

    /**
     * @return void
     */
    public function createOrder()
    {
        if ($this->wooCommerce->cart->is_empty()) {
            $this->ajaxJsonRequest->sendJsonError([
                'message' => esc_html_x(
                    'Cannot create an order with an empty cart.',
                    'express-checkout',
                    'woo-paypalplus'
                ),
            ]);
        }

        $orderId = '';
        $returnUrl = $this->settingRepository->returnUrl();
        $notifyUrl = $this->wooCommerce->api_request_url(
            Gateway::GATEWAY_ID . Ipn::IPN_ENDPOINT_SUFFIX
        );
        $paymentCreator = $this->paymentCreatorFactory->create(
            $this->settingRepository,
            $returnUrl,
            $notifyUrl
        );

        // TODO Prevent to execute more than once?
        try {
            $payment = $paymentCreator->create();
            $orderId = $payment->getId();
        } catch (PayPalConnectionException $exc) {
            wc_add_notice($exc->getMessage(), 'error');
            $this->ajaxJsonRequest->sendJsonError([
                'message' => $exc->getMessage(),
            ]);
        } catch (Exception $exc) {
            wc_add_notice($exc->getMessage(), 'error');
            $this->logger->error($exc, [$orderId]);
            $this->ajaxJsonRequest->sendJsonError([
                'message' => $exc->getMessage(),
            ]);
        }

        $this->ajaxJsonRequest->sendJsonSuccess([
            'orderId' => $orderId,
        ]);
    }

    /**
     * Store the data needed for payment into session
     */
    public function storePaymentData()
    {
        $payerId = $this->request->get(Request::INPUT_PAYER_ID, FILTER_SANITIZE_STRING);
        $paymentId = $this->request->get(Request::INPUT_PAYMENT_ID, FILTER_SANITIZE_STRING);
        $paymentToken = $this->request->get(Request::INPUT_PAYMENT_TOKEN, FILTER_SANITIZE_STRING);

        if (!$payerId || !$paymentId) {
            wc_add_notice(
                esc_html__('Invalid Payment or Payer ID.', 'woo-paypalplus'),
                'error'
            );
            $this->logger->error('Invalid Payment or Payer ID.');
            $this->ajaxJsonRequest->sendJsonError(['success' => false]);
        }

        /**
         * Store Payment Data
         *
         * @param string $payerId
         * @param string $paymentId
         */
        do_action(self::ACTION_STORE_PAYMENT_DATA, $payerId, $paymentId, $paymentToken);

        $this->session->set(Session::PAYER_ID, $payerId);
        $this->session->set(Session::PAYMENT_ID, $paymentId);
        $this->session->set(Session::CHOSEN_PAYMENT_METHOD, Gateway::GATEWAY_ID);
        $this->session->set(Session::PAYMENT_TOKEN, $paymentToken);

        $this->ajaxJsonRequest->sendJsonSuccess(['success' => true]);
    }
}
