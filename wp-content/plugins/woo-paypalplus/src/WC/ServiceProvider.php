<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\WC;

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\ErrorData\ApiErrorExtractor;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Payment\PaymentIdValidator;
use WCPayPalPlus\Payment\PaymentSessionDestructor;
use WCPayPalPlus\PlusGateway\Gateway;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\Payment\PaymentPatchFactory;
use WCPayPalPlus\Session\Session;
use WCPayPalPlus\Service;
use WCPayPalPlus\Setting\Storable;
use WooCommerce;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\WC
 */
class ServiceProvider implements Service\BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[WooCommerce::class] = function () {
            return wc();
        };
        $container[CheckoutDropper::class] = function (Container $container) {
            return new CheckoutDropper(
                $container[Session::class],
                $container[Storable::class]
            );
        };
        $container[RedirectablePatcher::class] = function (Container $container) {
            return new RedirectablePatcher(
                $container[OrderFactory::class],
                $container[PaymentPatchFactory::class],
                $container[PlusStorable::class],
                $container[Session::class],
                $container[CheckoutDropper::class],
                $container[Logger::class],
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
        $gatewayId = $container[Gateway::class]->id;

        add_action(
            "woocommerce_receipt_{$gatewayId}",
            [$container[RedirectablePatcher::class], 'patchOrder']
        );
    }
}
