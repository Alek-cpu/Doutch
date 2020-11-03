<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Payment;

use Inpsyde\Lib\PayPal\Api\Item;
use Inpsyde\Lib\PayPal\Api\ItemList;

/**
 * Interface OrderDataProvider
 *
 * @package WCPayPalPlus\Payment
 */
interface OrderDataProvider
{
    /**
     * Array of item data providers.
     *
     * @return ItemList
     */
    public function itemsList();

    /**
     * Order subtotal.
     *
     * @return float
     */
    public function subTotal();

    /**
     * Order total.
     *
     * @return float
     */
    public function total();

    /**
     * Tax total amount.
     *
     * @return float
     */
    public function totalTaxes();

    /**
     * Total shipping cost.
     *
     * @return float
     */
    public function shippingTotal();

    /**
     * Retrieve the Total Shipping Taxes
     *
     * @return float
     */
    public function shippingTax();

    /**
     * Total discount amount.
     *
     * @return float
     */
    public function totalDiscount();
}
