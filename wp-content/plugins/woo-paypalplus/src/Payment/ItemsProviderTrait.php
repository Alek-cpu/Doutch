<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Payment;

use Inpsyde\Lib\PayPal\Api\Item;
use Inpsyde\Lib\PayPal\Api\ItemList;
use InvalidArgumentException;

/**
 * Trait ItemsProviderTrait
 * @property OrderDataProvider $orderDataProvider
 * @package WCPayPalPlus\Payment
 */
trait ItemsProviderTrait
{
    /**
     * Retrieve the Order Items List
     *
     * Address Rounding problems
     *
     * The main problem here is how WooCommerce tackle the taxes applied to prices and how
     * PayPal needs to have those prices.
     *
     * Because of intrinsic rounding problems WooCommerce deal with taxes as pow10 values, means
     * the numbers used to calculate taxes contains a lot of information.
     *
     * PayPal want to have prices 2 decimal rounded, if not the Item::setPrice will format in that way.
     *
     * What we did in previous versions was to get the price of the product (total) divided by
     * the quantity see $data->get_price(), this could produce rounding problems because
     * the division result could contains more than 2 decimal points, then we pass that value
     * to Item::setPrice that will round it, practically adding some more cents to the price.
     *
     * So the price calculated by WooCommerce will not fit the price calculated by our implementation.
     * The good solution would work as WooCommerce (using pow10 values) but actually the logic
     * is too complicated that need a complete separated implementation.
     *
     * As workaround for now we'll send one single product where the name contains all of the product
     * names + quantities and the amount is the subtotal.
     *
     * @return ItemList
     * @throws InvalidArgumentException
     */
    private function itemsList()
    {
        $orderItemsList = $this->orderDataProvider->itemsList();

        if (!wc_prices_include_tax()) {
            return $orderItemsList;
        }

        $itemList = new ItemList;
        $item = new Item;
        $itemNamesList = $this->extractItemsNames($orderItemsList);

        $item
            ->setName($itemNamesList)
            ->setCurrency(get_woocommerce_currency())
            ->setQuantity(1)
            ->setPrice($this->orderDataProvider->subTotal());

        $itemList->addItem($item);

        return $itemList;
    }

    /**
     * Extract the Item Names x Quantity from ItemList Items
     *
     * @param ItemList $itemsList
     * @return string
     */
    private function extractItemsNames(ItemList $itemsList)
    {
        $names = [];

        /** @var Item $item */
        foreach ($itemsList->getItems() as $item) {
            $names[] = $item->getName() . 'x' . $item->getQuantity();
        }

        return implode(',', $names);
    }
}
