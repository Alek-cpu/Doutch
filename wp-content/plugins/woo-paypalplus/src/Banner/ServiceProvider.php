<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Banner;

use Brain\Nonces\NonceContextInterface;
use Brain\Nonces\WpNonce;
use WCPayPalPlus\Admin\Notice\AjaxDismisser;
use WCPayPalPlus\Admin\Notice\Controller;
use WCPayPalPlus\Admin\Notice\Notice;
use WCPayPalPlus\Admin\Notice\Noticeable;
use WCPayPalPlus\Admin\Notice\NoticeRender;
use WCPayPalPlus\Nonce;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Setting\SharedRepository;
use WooCommerce;

/**
 * Class ServiceProvider
 *
 * @package WCPayPalPlus\PlusGateway
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $urlBannerSettings = admin_url(
            'admin.php?page=wc-settings&tab=paypalplus-banner'
        );
        $ajaxNonce = new Nonce(
            new WpNonce(BannerAjaxHandler::ACTION . '_nonce'),
            $container[WooCommerce::class]
        );
        $container[NoticeRender::class] = function () {
            return new NoticeRender();
        };
        $container->share(
            'banner_notice',
            function (Container $container) use (
                $urlBannerSettings,
                $ajaxNonce
            ) {
                return new Notice(
                    Noticeable::WARNING,
                    sprintf(
                        '<p>%1$s <a data-nonce="%2$s" id="enable_pp_banner_feature" href="%3$s">%4$s</a></p>',
                        _x(
                            'Activate the PayPal banner to offer your buyers payment by instalments. With PayPal Installment Payment customers like to buy higher priced products. This gives them a good chance to fill their shopping baskets.',
                            'Admin Notice Banner',
                            'woo-paypalplus'
                        ),
                        (string)$ajaxNonce,
                        esc_url($urlBannerSettings),
                        _x(
                            'To enable it click here.',
                            'Admin Notice Banner',
                            'woo-paypalplus'
                        )
                    ),
                    true,
                    'WCPayPalPlus\Admin\Notice\BannerNotice'
                );
            }
        );
        $container->share(
            Controller::class,
            function (Container $container) {
                return new Controller(
                    $container[NoticeRender::class]
                );
            }
        );
        $container[BannerAjaxHandler::class] = function (
            Container $container
        ) use ($ajaxNonce) {
            return new BannerAjaxHandler(
                $ajaxNonce,
                $container[NonceContextInterface::class],
                $container[Controller::class]
            );
        };
        $container[AjaxDismisser::class] = function (Container $container) {
            return new AjaxDismisser(
                $container[Controller::class],
                $container[Request::class]
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        add_action(
            'admin_notices',
            function () use (
                $container
            ) {
                global $pagenow;
                $allowedPages = ['plugins.php', 'index.php', 'admin.php'];
                if (in_array($pagenow, $allowedPages, true)) {
                    $container[Controller::class]->maybeRender(
                        $container['banner_notice']
                    );
                }
            }
        );

        add_action(
            'wp_ajax_' . BannerAjaxHandler::ACTION,
            [$container[BannerAjaxHandler::class], 'handle']
        );

        $sharedRepository = $container->get(SharedRepository::class);
        $clientId = $sharedRepository->clientIdProduction();

        add_filter(
            'woocommerce_get_settings_pages',
            function ($settings) use ($clientId) {
                $settings[] = new BannerSettingsPage(
                    'paypalplus-banner',
                    __('PayPal Banner', 'woo-paypalplus'),
                    $clientId
                );

                return $settings;
            }
        );
    }
}
