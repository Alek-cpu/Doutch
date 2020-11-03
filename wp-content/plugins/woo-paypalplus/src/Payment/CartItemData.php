<?php

namespace WCPayPalPlus\Payment;

use WCPayPalPlus\Utils\PriceFormatterTrait;
use Exception;

/**
 * Class CartItemData
 *
 * @package WCPayPalPlus\Payment
 */

/**
 * Class CartItemData
 *
 * @package WCPayPalPlus\Payment
 */
class CartItemData implements OrderItemDataProvider
{
    use PriceFormatterTrait;

    /**
     * Item data.
     *
     * @var array
     */
    private $data;

    /**
     * CartItemData constructor.
     *
     * @param array $data Item data.
     *
     * @throws \Exception
     */
    public function __construct(array $data)
    {
        if (!isset($data['product_id'])) {
            throw new Exception('Missing Data');
        }

        $this->data = $data;
    }

    /**
     * Returns the item price.
     *
     * @return float
     */
    public function get_price()
    {
        return $this->format($this->data['line_subtotal'] / $this->get_quantity());
    }

    /**
     * Returns the item quantity.
     *
     * @return int
     */
    public function get_quantity()
    {
        return (int)$this->data['quantity'];
    }

    /**
     * Returns the item name.
     *
     * @return string
     */
    public function get_name()
    {
        return $this->data['data']->get_name();
    }

    /**
     * Returns the Product SKU.
     *
     * @return string|null
     */
    public function get_sku()
    {
        $product = $this->data['data'];
        $sku = $product->get_sku();
        if ($product instanceof \WC_Product_Variation) {
            $sku = $product->parent->get_sku();
        }

        return $sku;
    }
}
