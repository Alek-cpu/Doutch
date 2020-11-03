<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Payment;

use WCPayPalPlus\Utils\PriceFormatterTrait;

/**
 * Class CartData
 *
 * @package WCPayPalPlus\Payment
 */
final class CartData implements OrderDataProvider
{
    use PriceFormatterTrait;
    use OrderDataTrait;

    /**
     * WooCommerce Cart.
     *
     * @var \WC_Cart
     */
    private $cart;

    /**
     * CartData constructor.
     *
     * @param \WC_Cart $cart WooCommerce cart.
     */
    public function __construct(\WC_Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Return the total taxes amount of the cart.
     *
     * @return float
     */
    public function totalTaxes()
    {
        // Include shipping and fee taxes
        $tax = $this->cart->get_taxes_total(true, false);
        $tax = $this->format($this->round($tax));

        return $tax;
    }

    /**
     * Returns the total shipping cost.
     *
     * @return float
     */
    public function shippingTotal()
    {
        $shippingTotal = $this->cart->get_shipping_total();
        $shippingTax = $this->cart->get_shipping_tax();

        // If shipping tax exists, and shipping has more than 2 decimals
        // Then calculate rounded shipping amount to prevent rounding errors
        if ($shippingTax && preg_match('/\.\d{3,}/', $shippingTotal)) {
            $shippingTotal = $this->round($shippingTotal + $shippingTax);
            $shippingTotal = $shippingTotal - $this->round($shippingTax);
        }

        return $this->format($this->round($shippingTotal));
    }

    /**
     * Returns the total discount in the cart.
     *
     * @return float
     */
    public function totalDiscount()
    {
        return $this->cart->get_discount_total();
    }

    /**
     * @inheritDoc
     */
    public function shippingTax()
    {
        return $this->format($this->round($this->cart->get_shipping_tax()));
    }

    /**
     * @inheritdoc
     */
    protected function items()
    {
        $items = [];
        $discount = $this->totalDiscount();

        foreach ($this->cart->get_cart() as $item) {
            $items[] = new CartItemData($item);
        }

        foreach ($this->cart->get_fees() as $fee) {
            $items[] = new FeeData($fee);
        }

        if ($discount > 0) {
            foreach ($this->cart->get_coupons('cart') as $code => $coupon) {
                $couponAmount = $this->cart->get_coupon_discount_amount($code);
                $items[] = new OrderDiscountData([
                    'name' => 'Cart Discount',
                    'qty' => '1',
                    'line_subtotal' => '-' . $this->format($couponAmount),
                ]);
            }
        }

        return $items;
    }

    /**
     * Get the subtotal including any additional taxes.
     *
     * This is used when the prices are given already including tax.
     *
     * @return string
     */
    protected function subTotalTaxIncluded()
    {
        return $this->format($this->round(
            $this->cart->get_cart_contents_total()
            + $this->cart->get_fee_total()
            + $this->cart->get_fee_tax()
            + $this->cart->get_cart_contents_tax()
        ));
    }
}
