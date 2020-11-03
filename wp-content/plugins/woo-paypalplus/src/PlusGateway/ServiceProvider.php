<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\PlusGateway;

use WCPayPalPlus\Api\ErrorData\ApiErrorExtractor;
use WCPayPalPlus\Gateway\CurrentPaymentMethod;
use function WCPayPalPlus\isGatewayDisabled;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\CredentialValidator;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Payment\PaymentIdValidator;
use WCPayPalPlus\Payment\PaymentSessionDestructor;
use WCPayPalPlus\Refund\RefundFactory;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\Payment\PaymentExecutionFactory;
use WCPayPalPlus\Payment\PaymentCreatorFactory;
use WCPayPalPlus\Session\Session;
use WCPayPalPlus\Setting\SharedSettingsModel;
use WCPayPalPlus\WC\CheckoutDropper;
use WooCommerce;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\PlusGateway
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[FrameRenderer::class] = function () {
            return new FrameRenderer();
        };
        $container[GatewaySettingsModel::class] = function (Container $container) {
            return new GatewaySettingsModel(
                $container[SharedSettingsModel::class]
            );
        };
        $container[DefaultGatewayOverride::class] = function (Container $container) {
            return new DefaultGatewayOverride(
                $container[PlusStorable::class],
                $container[Session::class],
                $container[CurrentPaymentMethod::class]
            );
        };
        $container[Gateway::class] = function (Container $container) {
            return new Gateway(
                $container[WooCommerce::class],
                $container[FrameRenderer::class],
                $container[CredentialValidator::class],
                $container[GatewaySettingsModel::class],
                $container[RefundFactory::class],
                $container[OrderFactory::class],
                $container[PaymentExecutionFactory::class],
                $container[PaymentCreatorFactory::class],
                $container[CheckoutDropper::class],
                $container[Session::class],
                $container[Logger::class]
            );
        };
        $container[PaymentExecution::class] = function (Container $container) {
            return new PaymentExecution(
                $container[OrderFactory::class],
                $container[Session::class],
                $container[PaymentExecutionFactory::class],
                $container[Logger::class],
                $container[CheckoutDropper::class],
                $container[ApiErrorExtractor::class],
                $container[PaymentIdValidator::class],
                $container[PaymentSessionDestructor::class]
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        $gateway = $container[Gateway::class];

        if (!is_admin() && isGatewayDisabled($gateway)) {
            return;
        }

        add_filter('woocommerce_payment_gateways', function ($methods) use ($gateway) {
            $methods[Gateway::class] = $gateway;
            return $methods;
        });

        add_action(
            'woocommerce_api_' . Gateway::GATEWAY_ID,
            [$container[PaymentExecution::class], 'execute'],
            12
        );

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
        add_action(
            'wp',
            [$container[DefaultGatewayOverride::class], 'maybeOverride']
        );
    }
}
