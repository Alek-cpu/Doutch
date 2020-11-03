<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Payment;

use Inpsyde\Lib\PayPal\Api\Item;
use Inpsyde\Lib\PayPal\Api\ItemList;

/**
 * Trait OrderDataTrait
 *
 * @package WCPayPalPlus\Payment
 */
trait OrderDataTrait
{
    /**
     * Calculate the order total
     *
     * @return float|int|string
     * @throws \InvalidArgumentException
     */
    public function total()
    {
        $total = $this->subTotal() + $this->shippingTotal();
        $total += !wc_prices_include_tax()
            ? $this->totalTaxes()
            : $this->shippingTax();

        $total = $this->format($this->round($total));

        return $total;
    }

    /**
     * Calculate the order subtotal
     *
     * @return float|int
     * @throws \InvalidArgumentException
     */
    public function subTotal()
    {
        $subtotal = 0;
        $items = $this->itemsList()->getItems();

        if (wc_prices_include_tax()) {
            return $this->subTotalTaxIncluded();
        }

        foreach ($items as $item) {
            $product_price = $item->getPrice();
            $item_price = (float)$product_price * $item->getQuantity();
            $subtotal += $item_price;
        }

        return $this->format($this->round($subtotal));
    }

    /**
     * Retrieve the Items
     *
     * @return ItemList
     * @throws \InvalidArgumentException
     */
    public function itemsList()
    {
        $item_list = new ItemList();
        foreach ($this->items() as $order_item) {
            $item_list->addItem($this->item($order_item));
        }

        return $item_list;
    }

    /**
     * Creates a single Order Item for the Paypal API
     *
     * @param OrderItemDataProvider $data
     * @return Item
     * @throws \InvalidArgumentException
     */
    private function item(OrderItemDataProvider $data)
    {
        $name = html_entity_decode($data->get_name(), ENT_NOQUOTES, 'UTF-8');
        $currency = get_woocommerce_currency();
        $sku = $data->get_sku();
        $price = $data->get_price();

        $item = new Item();
        $item
            ->setName($name)
            ->setCurrency($currency)
            ->setQuantity($data->get_quantity())
            ->setPrice($price);

        if (!empty($sku)) {
            $item->setSku($sku);// Similar to `item_number` in Classic API.
        }

        return $item;
    }

    /**
     * Returns an array of item data providers.
     *
     * @return OrderItemDataProvider[]
     */
    abstract protected function items();

    /**
     * Retrieve the Sub Total Taxes included
     *
     * @return float
     */
    abstract protected function subTotalTaxIncluded();
}
