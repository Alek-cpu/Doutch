<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Api\CredentialValidator;
use WCPayPalPlus\Api\ErrorData\Codes;
use WCPayPalPlus\Api\ErrorData\ApiErrorExtractor;
use WCPayPalPlus\Gateway\MethodsTrait;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Payment\PaymentIdValidator;
use WCPayPalPlus\Payment\PaymentPatcher;
use WCPayPalPlus\Payment\PaymentPatchFactory;
use WCPayPalPlus\Payment\PaymentProcessException;
use WCPayPalPlus\Payment\PaymentSessionDestructor;
use WCPayPalPlus\Session\SessionCleaner;
use WCPayPalPlus\Setting\ExpressCheckoutRepositoryTrait;
use WCPayPalPlus\Setting\ExpressCheckoutStorable;
use WCPayPalPlus\Setting\GatewaySharedSettingsTrait;
use WCPayPalPlus\Payment\PaymentExecutionFactory;
use WCPayPalPlus\Payment\PaymentCreatorFactory;
use WCPayPalPlus\Session\Session;
use WCPayPalPlus\Refund\RefundFactory;
use WCPayPalPlus\Setting\SettingsGatewayModel;
use WCPayPalPlus\Setting\SharedRepositoryTrait;
use WCPayPalPlus\WC\CheckoutDropper;
use WooCommerce;
use WC_Payment_Gateway;
use RuntimeException;

/**
 * Class Gateway
 * @package WCPayPalPlus\ExpressCheckoutGateway
 */
final class Gateway extends WC_Payment_Gateway implements ExpressCheckoutStorable
{
    use SharedRepositoryTrait;
    use ExpressCheckoutRepositoryTrait;
    use GatewaySharedSettingsTrait;
    use MethodsTrait;
    use PaymentExecutionTrait;

    const GATEWAY_ID = 'paypal_express';
    const GATEWAY_TITLE_METHOD = 'PayPal Express Checkout';
    const ACTION_AFTER_PAYMENT_EXECUTION = 'woopaypalplus.after_express_checkout_payment_execution';

    /**
     * @var CredentialValidator
     */
    private $credentialValidator;

    /**
     * @var GatewaySettingsModel
     */
    private $settingsModel;

    /**
     * @var RefundFactory
     */
    private $refundFactory;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var PaymentExecutionFactory
     */
    private $paymentExecutionFactory;

    /**
     * @var PaymentCreatorFactory
     */
    private $paymentCreatorFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * @var CheckoutDropper
     */
    private $checkoutDropper;

    /**
     * @var PaymentPatchFactory
     */
    private $paymentPatchFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ApiErrorExtractor
     */
    private $apiErrorDataExtractor;

    /**
     * @var SessionCleaner
     */
    private $sessionCleaner;

    /**
     * @var PaymentIdValidator
     */
    private $paymentIdValidator;

    /**
     * @var PaymentSessionDestructor
     */
    private $paymentSessionDestructor;

    /**
     * Gateway constructor.
     * @param WooCommerce $wooCommerce
     * @param CredentialValidator $credentialValidator
     * @param SettingsGatewayModel $settingsModel
     * @param RefundFactory $refundFactory
     * @param OrderFactory $orderFactory
     * @param PaymentExecutionFactory $paymentExecutionFactory
     * @param Session $session
     * @param CheckoutDropper $checkoutDropper
     * @param PaymentPatchFactory $paymentPatchFactory
     * @param Logger $logger
     * @param ApiErrorExtractor $apiErrorDataExtractor
     * @param SessionCleaner $sessionCleaner
     * @param PaymentIdValidator $paymentIdValidator
     * @param PaymentSessionDestructor $paymentSessionDestructor
     */
    public function __construct(
        WooCommerce $wooCommerce,
        CredentialValidator $credentialValidator,
        SettingsGatewayModel $settingsModel,
        RefundFactory $refundFactory,
        OrderFactory $orderFactory,
        PaymentExecutionFactory $paymentExecutionFactory,
        Session $session,
        CheckoutDropper $checkoutDropper,
        PaymentPatchFactory $paymentPatchFactory,
        Logger $logger,
        ApiErrorExtractor $apiErrorDataExtractor,
        SessionCleaner $sessionCleaner,
        PaymentIdValidator $paymentIdValidator,
        PaymentSessionDestructor $paymentSessionDestructor
    ) {

        $this->wooCommerce = $wooCommerce;
        $this->credentialValidator = $credentialValidator;
        $this->settingsModel = $settingsModel;
        $this->refundFactory = $refundFactory;
        $this->orderFactory = $orderFactory;
        $this->paymentExecutionFactory = $paymentExecutionFactory;
        $this->session = $session;
        $this->checkoutDropper = $checkoutDropper;
        $this->paymentPatchFactory = $paymentPatchFactory;
        $this->logger = $logger;
        $this->id = self::GATEWAY_ID;
        $this->apiErrorDataExtractor = $apiErrorDataExtractor;
        $this->sessionCleaner = $sessionCleaner;

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->method_title = self::GATEWAY_TITLE_METHOD;
        $this->description = $this->get_option('description');
        $this->method_description = esc_html_x(
            'Enable faster payments with the Express Checkout button, directly from the single product page or the shopping cart.',
            'gateway-settings',
            'woo-paypalplus'
        );

        $this->has_fields = true;
        $this->supports = [
            'products',
            'refunds',
        ];
        $this->paymentIdValidator = $paymentIdValidator;
        $this->paymentSessionDestructor = $paymentSessionDestructor;
    }

    /**
     * @inheritdoc
     * @throws PaymentProcessException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     * @throws \WCPayPalPlus\Order\OrderFactoryException
     */
    public function process_payment($orderId)
    {
        assert(is_int($orderId));

        $order = null;
        $paymentId = $this->session->get(Session::PAYMENT_ID);
        $payerId = $this->session->get(Session::PAYER_ID);

        // Set orderId so we can retrieve it later if needed.
        $this->session->set(Session::ORDER_ID, $orderId);

        if (!$payerId || !$orderId) {
            throw PaymentProcessException::forInsufficientData();
        }

        /*
         * Cannot use paymentSessionDestructor->becauseInvalidPaymentId() here because
         * we are in ajax context and WooCommerce doesn't allow us to do custom redirect at this
         * point, we can only thrown an exception but that's not enough because if we do not
         * throw any exception the `\WC_Checkout::send_ajax_failure_response` will be called that
         * not do any redirection plus throw an exception will add a notice as feedback to the user
         * and then will call `\WC_Checkout::send_ajax_failure_response`.
         *
         * So Since we need to redirect the user to a different page to ask to start the
         * payment process again in order to create a new payment id the only solution is to mark
         * the payment as success and pass the url where we want to redirect the user.
         */
        if (!$this->paymentIdValidator->isPaymentIdValid($paymentId)) {
            $this->paymentSessionDestructor->becauseInvalidPaymentId();
            return [
                'result' => 'success',
                'redirect' => wc_get_cart_url(),
            ];
        }

        try {
            $order = $this->orderFactory->createById($orderId);
        } catch (RuntimeException $exc) {
            throw PaymentProcessException::becauseInvalidOrderId($orderId);
        }

        $paymentPatcher = $this->paymentPatchFactory->create(
            $order,
            $paymentId,
            $this->invoicePrefix(),
            ApiContextFactory::getFromConfiguration()
        );

        try {
            $paymentPatcher->execute();
        } catch (PayPalConnectionException $exc) {
            $apiError = $this->apiErrorDataExtractor->extractByException($exc);
            throw PaymentProcessException::byApiError($apiError);
        }

        /**
         * Allow to execute more patching
         *
         * @oparam bool $isSuccessPatched
         */
        do_action(PaymentPatcher::ACTION_AFTER_PAYMENT_PATCH);

        try {
            $this->execute($order, $payerId, $paymentId);
        } catch (PayPalConnectionException $exc) {
            $apiError = $this->apiErrorDataExtractor->extractByException($exc);

            if (in_array($apiError->code(), Codes::REDIRECTABLE_ERROR_CODES, true)) {
                $this->sessionCleaner->cleanChosenPaymentMethod();
                return [
                    'result' => 'success',
                    'redirect' => $this->redirectPayPalUrl(),
                ];
            }

            throw PaymentProcessException::byApiError($apiError);
        }

        return [
            'result' => 'success',
            'redirect' => $order->get_checkout_order_received_url(),
        ];
    }

    /**
     * Retrieve the url to paypal site
     *
     * @return string
     */
    private function redirectPayPalUrl()
    {
        $paymentToken = urlencode($this->session->get(Session::PAYMENT_TOKEN));
        $environment = $this->isSandboxed() ? 'sandbox' : 'live';

        return "https://www.{$environment}.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token={$paymentToken}&useraction=commit";
    }
}
