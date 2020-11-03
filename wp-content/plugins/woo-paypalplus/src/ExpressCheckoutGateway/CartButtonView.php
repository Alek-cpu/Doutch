<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use Brain\Nonces\NonceInterface;
use WCPayPalPlus\Renderable;

/**
 * Class CartButtonView
 * @package WCPayPalPlus\ExpressCheckout
 */
final class CartButtonView implements Renderable
{
    const SELECTOR_ID = 'paypalplus_ecs_cart_button';

    /**
     * @var NonceInterface
     */
    private $nonce;

    /**
     * @var \WooCommerce
     */
    private $woocommerce;

    /**
     * CartButtonView constructor.
     * @param NonceInterface $nonce
     * @param \WooCommerce $wooCommerce
     */
    public function __construct(NonceInterface $nonce, \WooCommerce $wooCommerce)
    {
        $this->nonce = $nonce;
        $this->woocommerce = $wooCommerce;
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        if ($this->woocommerce->cart->is_empty()) {
            return;
        }

        ?>
        <div class="woo-paypalplus-checkout-button">
            <div id="<?= sanitize_html_class(self::SELECTOR_ID) ?>" data-context="cart"></div>
            <span
                class="woo-paypalplus-checkout-nonce"
                data-noncename="<?= esc_attr($this->nonce->action()) ?>"
                data-noncevalue="<?= esc_attr($this->nonce) ?>"
            >
            </span>
        </div>
        <?php
    }
}
