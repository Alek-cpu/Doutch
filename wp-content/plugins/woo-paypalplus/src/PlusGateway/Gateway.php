<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\PlusGateway;

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\CredentialValidator;
use WCPayPalPlus\Ipn\Ipn;
use WCPayPalPlus\Gateway\MethodsTrait;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Setting\GatewaySharedSettingsTrait;
use WCPayPalPlus\Setting\PlusRepositoryTrait;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\Payment\PaymentExecutionFactory;
use WCPayPalPlus\Payment\PaymentCreatorFactory;
use WCPayPalPlus\Session\Session;
use WCPayPalPlus\Refund\RefundFactory;
use WCPayPalPlus\Setting\SettingsGatewayModel;
use WCPayPalPlus\Setting\SharedRepositoryTrait;
use WCPayPalPlus\WC\CheckoutDropper;
use WooCommerce;
use WC_Payment_Gateway;
use OutOfBoundsException;
use WC_Order;
use Exception;

/**
 * Class Gateway
 * @package WCPayPalPlus\WC
 */
final class Gateway extends WC_Payment_Gateway implements PlusStorable
{
    use SharedRepositoryTrait;
    use PlusRepositoryTrait;
    use GatewaySharedSettingsTrait;
    use MethodsTrait;

    const GATEWAY_ID = 'paypal_plus';
    const GATEWAY_TITLE_METHOD = 'PayPal PLUS';

    const ACTION_AFTER_PAYMENT_EXECUTION = 'woopaypalplus.after_plus_checkout_payment_execution';
    const ACTION_AFTER_PAYMENT_PATCH = 'woopaypalplus.after_plus_checkout_payment_patch';

    /**
     * @var FrameRenderer
     */
    private $frameView;

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
     * @var Logger
     */
    private $logger;

    /**
     * @var CheckoutDropper
     */
    private $checkoutDropper;

    /**
     * Gateway constructor.
     * @param WooCommerce $wooCommerce
     * @param FrameRenderer $frameView
     * @param CredentialValidator $credentialValidator
     * @param SettingsGatewayModel $settingsModel
     * @param RefundFactory $refundFactory
     * @param OrderFactory $orderFactory
     * @param PaymentExecutionFactory $paymentExecutionFactory
     * @param PaymentCreatorFactory $paymentCreatorFactory
     * @param CheckoutDropper $checkoutDropper
     * @param Session $session
     * @param Logger $logger
     */
    public function __construct(
        WooCommerce $wooCommerce,
        FrameRenderer $frameView,
        CredentialValidator $credentialValidator,
        SettingsGatewayModel $settingsModel,
        RefundFactory $refundFactory,
        OrderFactory $orderFactory,
        PaymentExecutionFactory $paymentExecutionFactory,
        PaymentCreatorFactory $paymentCreatorFactory,
        CheckoutDropper $checkoutDropper,
        Session $session,
        Logger $logger
    ) {

        $this->wooCommerce = $wooCommerce;
        $this->frameView = $frameView;
        $this->credentialValidator = $credentialValidator;
        $this->settingsModel = $settingsModel;
        $this->refundFactory = $refundFactory;
        $this->orderFactory = $orderFactory;
        $this->paymentExecutionFactory = $paymentExecutionFactory;
        $this->paymentCreatorFactory = $paymentCreatorFactory;
        $this->checkoutDropper = $checkoutDropper;
        $this->session = $session;
        $this->logger = $logger;

        $this->id = self::GATEWAY_ID;

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->method_title = self::GATEWAY_TITLE_METHOD;
        $this->description = $this->get_option('description');
        $this->method_description = esc_html_x(
            'Allow customers to conveniently checkout with different payment options like PayPal, Direct Debit, Credit Card and Invoice (if available).',
            'gateway-settings',
            'woo-paypalplus'
        );

        $this->has_fields = true;
        $this->supports = [
            'products',
            'refunds',
        ];
    }

    /**
     * @param int $orderId
     * @return array
     */
    public function process_payment($orderId)
    {
        $order = new WC_Order($orderId);

        return [
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true),
        ];
    }

    /**
     * @throws OutOfBoundsException
     */
    public function payment_fields()
    {
        parent::payment_fields();

        if ($this->supports('tokenization') && is_checkout()) {
            $this->tokenization_script();
            $this->saved_payment_methods();
            $this->form();
            $this->save_payment_method_checkbox();
            return;
        }

        $this->form();
    }

    /**
     * @throws OutOfBoundsException
     */
    private function form()
    {
        $paymentUrl = $this->createPayment();

        $data = [
            'useraction' => 'commit',
            'showLoadingIndicator' => true,
            'approvalUrl' => $paymentUrl,
            'placeholder' => 'ppplus',
            'mode' => $this->isSandboxed() ? 'sandbox' : 'live',
            'country' => $this->wooCommerce->customer->get_billing_country(),
            'language' => $this->locale(),
            'buttonLocation' => 'outside',
            'showPuiOnSandbox' => true,
        ];

        $this->frameView->render($data);
    }

    /**
     * @return string
     * @throws OutOfBoundsException
     */
    private function createPayment()
    {
        $url = (string)$this->session->get(Session::APPROVAL_URL);

        if (!$url) {
            try {
                $returnUrl = $this->wooCommerce->api_request_url($this->id);
                $notifyUrl = $this->wooCommerce->api_request_url(
                    self::GATEWAY_ID . Ipn::IPN_ENDPOINT_SUFFIX
                );
                $paymentCreator = $this->paymentCreatorFactory->create(
                    $this,
                    $returnUrl,
                    $notifyUrl
                );
                $paymentCreator = $paymentCreator->create();
            } catch (Exception $exc) {
                return '';
            }

            $this->session->set(Session::PAYMENT_ID, $paymentCreator->getId());

            $url = htmlspecialchars_decode($paymentCreator->getApprovalLink());
            $this->session->set(Session::APPROVAL_URL, $url);
        }

        return $url;
    }

    /**
     * @return bool|string
     */
    private function locale()
    {
        $locale = false;
        if (get_locale() !== '') {
            $locale = substr(get_locale(), 0, 5);
        }

        return $locale;
    }
}
