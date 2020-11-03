<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use function Brain\Nonces\formField;
use Brain\Nonces\NonceInterface;
use WCPayPalPlus\Renderable;

/**
 * Class SingleProductButtonView
 * @package WCPayPalPlus\ExpressCheckout
 */
final class SingleProductButtonView implements Renderable
{
    const SELECTOR_ID = 'paypalplus_ecs_single_product_button';
    const NOT_ALLOWED_PRODUCTS = [
        'external',
    ];

    /**
     * @var NonceInterface
     */
    private $nonce;

    /**
     * SingleProductButtonView constructor.
     * @param NonceInterface $nonce
     */
    public function __construct(NonceInterface $nonce)
    {
        $this->nonce = $nonce;
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        if (!is_product()) {
            return;
        }

        $product = wc_get_product();
        if (!$this->isValidProduct($product)) {
            return;
        }
        ?>
        <div class="woo-paypalplus-checkout-button">
            <div id="<?= sanitize_html_class(self::SELECTOR_ID) ?>" data-context="product"></div>
            <?=
            // phpcs:ignore
            formField($this->nonce) ?>
            <input type="hidden" name="product_id" value="<?= esc_attr($product->get_id()) ?>"/>
        </div>
        <?php
    }

    /**
     * Check if the Given Product is a WooCommerce Product of the allowed type
     *
     * @param $product
     * @return bool
     */
    private function isValidProduct($product)
    {
        return (
            $product instanceof \WC_Product
            && $product->get_price()
            && !\in_array($product->get_type(), self::NOT_ALLOWED_PRODUCTS, true)
        );
    }
}
