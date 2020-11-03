<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use WCPayPalPlus\Api\ErrorData\ApiErrorExtractor;
use function WCPayPalPlus\areAllExpressCheckoutButtonsDisabled;
use function WCPayPalPlus\isGatewayDisabled;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Gateway\CurrentPaymentMethod;
use Brain\Nonces\NonceContextInterface;
use Brain\Nonces\WpNonce;
use WCPayPalPlus\Nonce;
use WCPayPalPlus\Payment\PaymentCreatorFactory;
use WCPayPalPlus\Payment\PaymentIdValidator;
use WCPayPalPlus\Payment\PaymentPatchFactory;
use WCPayPalPlus\Payment\PaymentSessionDestructor;
use WCPayPalPlus\Session\Session;
use WCPayPalPlus\Session\SessionCleaner;
use WCPayPalPlus\Setting\ExpressCheckoutStorable;
use WCPayPalPlus\Setting\SharedSettingsModel;
use WCPayPalPlus\Utils\AjaxJsonRequest;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Api\CredentialValidator;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Payment\PaymentExecutionFactory;
use WCPayPalPlus\Refund\RefundFactory;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\WC\CheckoutDropper;
use WooCommerce;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\ExpressCheckout
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $ajaxNonce = new Nonce(
            new WpNonce(AjaxHandler::ACTION . '_nonce'),
            $container[WooCommerce::class]
        );

        $container[GatewaySettingsModel::class] = function (Container $container) {
            return new GatewaySettingsModel(
                $container[SharedSettingsModel::class]
            );
        };
        $container[Gateway::class] = function (Container $container) {
            return new Gateway(
                $container[WooCommerce::class],
                $container[CredentialValidator::class],
                $container[GatewaySettingsModel::class],
                $container[RefundFactory::class],
                $container[OrderFactory::class],
                $container[PaymentExecutionFactory::class],
                $container[Session::class],
                $container[CheckoutDropper::class],
                $container[PaymentPatchFactory::class],
                $container[Logger::class],
                $container[ApiErrorExtractor::class],
                $container[SessionCleaner::class],
                $container[PaymentIdValidator::class],
                $container[PaymentSessionDestructor::class]
            );
        };
        $container[CheckoutGatewayOverride::class] = function (Container $container) {
            return new CheckoutGatewayOverride(
                $container[CurrentPaymentMethod::class]
            );
        };
        $container[CheckoutAddressOverride::class] = function (Container $container) {
            return new CheckoutAddressOverride(
                $container[WooCommerce::class],
                $container[CurrentPaymentMethod::class],
                $container[Logger::class]
            );
        };

        $container[SingleProductButtonView::class] = function () use ($ajaxNonce) {
            return new SingleProductButtonView($ajaxNonce);
        };
        $container[CartButtonView::class] = function (Container $container) use ($ajaxNonce) {
            return new CartButtonView(
                $ajaxNonce,
                $container[WooCommerce::class]
            );
        };
        $container[Dispatcher::class] = function () {
            return new Dispatcher();
        };
        $container[AjaxHandler::class] = function (Container $container) use ($ajaxNonce) {
            return new AjaxHandler(
                $ajaxNonce,
                $container[NonceContextInterface::class],
                $container[Dispatcher::class],
                $container[Request::class],
                $container[AjaxJsonRequest::class]
            );
        };
        $container[CartCheckout::class] = function (Container $container) {
            return new CartCheckout(
                $container[ExpressCheckoutStorable::class],
                $container[PaymentCreatorFactory::class],
                $container[AjaxJsonRequest::class],
                $container[WooCommerce::class],
                $container[Logger::class],
                $container[Request::class],
                $container[Session::class]
            );
        };
        $container[SingleProductCheckout::class] = function (Container $container) {
            return new SingleProductCheckout(
                $container[WooCommerce::class],
                $container[AjaxJsonRequest::class],
                $container[CartCheckout::class],
                $container[Request::class],
                $container[Logger::class]
            );
        };

        $container[PayPalPaymentExecution::class] = function (Container $container) {
            return new PayPalPaymentExecution(
                $container[OrderFactory::class],
                $container[PaymentExecutionFactory::class],
                $container[Session::class],
                $container[Logger::class],
                $container[ExpressCheckoutStorable::class],
                $container[Request::class],
                $container[ApiErrorExtractor::class]
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        $gateway = $container[Gateway::class];

        add_filter('woocommerce_payment_gateways', function ($methods) use ($gateway) {
            $methods[Gateway::class] = $gateway;

            return $methods;
        });

        /** @noinspection PhpUnhandledExceptionInspection */
        if (!is_admin() && (isGatewayDisabled($gateway) || areAllExpressCheckoutButtonsDisabled())) {
            return;
        }

        $this->bootstrapAjaxRequests($container);

        is_admin() and $this->bootstrapBackend($container);
        !is_admin() and $this->bootstrapFrontend($container);
    }

    /**
     * Bootstrap Backend
     *
     * @param Container $container
     */
    private function bootstrapBackend(Container $container)
    {
        $gatewayId = Gateway::GATEWAY_ID;
        $gateway = $container[Gateway::class];

        add_action(
            "woocommerce_update_options_payment_gateways_{$gatewayId}",
            [$gateway, 'process_admin_options'],
            10
        );
    }

    /**
     * Bootstrap Frontend
     *
     * @param Container $container
     */
    private function bootstrapFrontend(Container $container)
    {
        $payPalPaymentExecution = $container[PayPalPaymentExecution::class];

        add_filter(
            'woocommerce_checkout_get_value',
            [$container[CheckoutAddressOverride::class], 'filterCheckoutValues'],
            20,
            2
        );
        add_filter(
            'woocommerce_checkout_update_customer_data',
            [$container[CheckoutAddressOverride::class], 'filterSaveCustomerData']
        );

        add_filter(
            'woocommerce_available_payment_gateways',
            [$container[CheckoutGatewayOverride::class], 'maybeOverridden'],
            9999
        );

        add_action('wp', function () use ($payPalPaymentExecution) {
            !is_admin() and $payPalPaymentExecution->execute();
        });

        $this->bootstrapButtons($container);
    }

    /**
     * Bootstrap Express Checkout Buttons
     *
     * @param Container $container
     */
    private function bootstrapButtons(Container $container)
    {
        $settingsRepository = $container[ExpressCheckoutStorable::class];

        if (is_admin()) {
            return;
        }

        $settingsRepository->showOnProductPage() and add_action(
            'woocommerce_after_add_to_cart_button',
            [$container[SingleProductButtonView::class], 'render']
        );
        $settingsRepository->showOnMiniCart() and add_action(
            'woocommerce_after_mini_cart',
            [$container[CartButtonView::class], 'render']
        );
        // After WooCommerce woocommerce_button_proceed_to_checkout
        $settingsRepository->showOnCart() and add_action(
            'woocommerce_proceed_to_checkout',
            [$container[CartButtonView::class], 'render'],
            25
        );
    }

    /**
     * Bootstrap Ajax Requests for Express Checkout
     *
     * @param Container $container
     */
    private function bootstrapAjaxRequests(Container $container)
    {
        add_action(
            'wp_ajax_' . AjaxHandler::ACTION,
            [$container[AjaxHandler::class], 'handle']
        );
        add_action(
            'wp_ajax_nopriv_' . AjaxHandler::ACTION,
            [$container[AjaxHandler::class], 'handle']
        );

        add_action(
            Dispatcher::ACTION_DISPATCH_CONTEXT . '/cart/' . CartCheckout::TASK_CREATE_ORDER,
            [$container[CartCheckout::class], CartCheckout::TASK_CREATE_ORDER],
            10,
            2
        );
        add_action(
            Dispatcher::ACTION_DISPATCH_CONTEXT . '/cart/' . CartCheckout::TASK_STORE_PAYMENT_DATA,
            [$container[CartCheckout::class], CartCheckout::TASK_STORE_PAYMENT_DATA],
            10,
            2
        );
        add_action(
            Dispatcher::ACTION_DISPATCH_CONTEXT . '/product/' . SingleProductCheckout::TASK_CREATE_ORDER,
            [$container[SingleProductCheckout::class], SingleProductCheckout::TASK_CREATE_ORDER],
            10,
            2
        );
        add_action(
            Dispatcher::ACTION_DISPATCH_CONTEXT . '/product/' . CartCheckout::TASK_STORE_PAYMENT_DATA,
            [$container[CartCheckout::class], CartCheckout::TASK_STORE_PAYMENT_DATA],
            10,
            2
        );
    }
}
