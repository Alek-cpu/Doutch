<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Admin;

use WCPayPalPlus\Admin\Notice\AjaxDismisser;
use WCPayPalPlus\Admin\Notice\Controller;
use WCPayPalPlus\Admin\Notice\Notice;
use WCPayPalPlus\Admin\Notice\Noticeable;
use WCPayPalPlus\Admin\Notice\NoticeRender;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WC_Payment_Gateways;
use WC_Payment_Gateway;
use WCPayPalPlus\ExpressCheckoutGateway\Gateway as ExpressCheckoutGateway;
use WCPayPalPlus\PlusGateway\Gateway as PlusGateway;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Admin
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register(Container $container)
    {
        $container[NoticeRender::class] = function () {
            return new NoticeRender();
        };
        $container['gateway_notice'] = function () {
            return new Notice(
                Noticeable::WARNING,
                esc_html_x(
                    'Seems you have more than one PayPal gateway active. We recommend to deactivate all of them except PayPal PLUS to avoid duplicated payment options at checkout.',
                    'admin-notice',
                    'woo-paypalplus'
                ),
                true,
                'WCPayPalPlus\Admin\Notice\GatewayNotice'
            );
        };
        $container[AjaxDismisser::class] = function (Container $container) {
            return new AjaxDismisser(
                $container[Controller::class],
                $container[Request::class]
            );
        };
        $container[Controller::class] = function (Container $container) {
            return new Controller(
                $container[NoticeRender::class]
            );
        };
    }

    /**
     * @inheritDoc
     */
    public function bootstrap(Container $container)
    {
        $gatewayNotice = $container['gateway_notice'];
        $controller = $container[Controller::class];
        $gatewaySubString = 'paypal';
        $gatewaysToNotCheckAgainst = [
            ExpressCheckoutGateway::class,
            PlusGateway::class,
        ];

        add_action(
            'admin_notices',
            function () use (
                $controller,
                $gatewayNotice,
                $gatewaySubString,
                $gatewaysToNotCheckAgainst
            ) {
                $availableGateways = WC_Payment_Gateways::instance()->payment_gateways();
                /** @var WC_Payment_Gateway $method */
                foreach ($availableGateways as $method) {
                    $name = is_string($method) ? $method : get_class($method);
                    if (in_array($name, $gatewaysToNotCheckAgainst, true)) {
                        continue;
                    }
                    if (stripos($name, $gatewaySubString) !== false) {
                        wc_string_to_bool($method->enabled) and $controller->maybeRender($gatewayNotice);
                        break;
                    }
                }
            }
        );
        add_action(
            'wp_ajax_' . AjaxDismisser::AJAX_NONCE_ACTION,
            [$container[AjaxDismisser::class], 'handle']
        );
    }
}
