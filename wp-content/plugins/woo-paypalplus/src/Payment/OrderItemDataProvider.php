<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 26.01.17
 * Time: 14:06
 */

namespace WCPayPalPlus\Payment;

interface OrderItemDataProvider
{
    /**
     * Returns the item price.
     *
     * @return float
     */
    public function get_price();

    /**
     * Returns the item quantity.
     *
     * @return int
     */
    public function get_quantity();

    /**
     * Returns the item name.
     *
     * @return string
     */
    public function get_name();

    /**
     * Returns the item SKU.
     *
     * @return string|null
     */
    public function get_sku();
}
