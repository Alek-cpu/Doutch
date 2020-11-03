<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Payment;

use WC_Order;

use WCPayPalPlus\Utils\PriceFormatterTrait;

/**
 * Class OrderData
 *
 * @package WCPayPalPlus\Payment
 */
final class OrderData implements OrderDataProvider
{
    use PriceFormatterTrait;
    use OrderDataTrait;

    /**
     * WooCommerce Order object.
     *
     * @var \WC_Order
     */
    private $order;

    /**
     * OrderData constructor.
     *
     * @param WC_Order $order WooCommerce order object.
     */
    public function __construct(WC_Order $order)
    {
        $this->order = $order;
    }

    /**
     * Returns the total discount on the order.
     *
     * @return float
     */
    public function totalDiscount()
    {
        return $this->order->get_total_discount();
    }

    /**
     * Returns the total tax amount of the order.
     *
     * @return float
     */
    public function totalTaxes()
    {
        $tax = $this->order->get_total_tax();

        return $this->format($this->round($tax));
    }

    /**
     * Returns the total shipping cost of the order.
     *
     * @return float
     */
    public function shippingTotal()
    {
        $shippingTotal = $this->order->get_shipping_total();
        $shippingTax = $this->order->get_shipping_tax();

        // If shipping tax exists, and shipping has more than 2 decimals
        // Then calculate rounded shipping amount to prevent rounding errors
        if ($shippingTax && preg_match('/\.\d{3,}/', $shippingTotal)) {
            $shippingTotal = $this->round($shippingTotal + $shippingTax);
            $shippingTotal = $shippingTotal - $this->round($shippingTax);
        }

        return $this->format($this->round($shippingTotal));
    }

    /**
     * @inheritDoc
     */
    public function shippingTax()
    {
        return $this->format($this->round($this->order->get_shipping_tax()));
    }

    /**
     * @inheritdoc
     */
    protected function items()
    {
        $cart = $this->order->get_items();
        $items = [];

        foreach ($cart as $item) {
            $items[] = new OrderItemData($item);
        }

        foreach ($this->order->get_fees() as $fee) {
            $items[] = new OrderFeeData([
                'name' => $fee['name'],
                'qty' => 1,
                'line_subtotal' => $fee['line_total'],
            ]);
        }

        $discount = $this->totalDiscount();
        if ($discount > 0) {
            $items[] = new OrderDiscountData([
                'name' => 'Total Discount',
                'qty' => 1,
                'line_subtotal' => -$this->format($discount),
            ]);
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
        $shipping_diff = $this->round(
            $this->order->get_shipping_total() + $this->order->get_shipping_tax()
        );
        return $this->format($this->order->get_total() - $shipping_diff);
    }
}
