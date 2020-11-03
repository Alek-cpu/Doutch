<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 27.01.17
 * Time: 11:47
 */

namespace WCPayPalPlus\Payment;

use WCPayPalPlus\Utils\PriceFormatterTrait;

/**
 * Class OrderItemData
 *
 * @package WCPayPalPlus\Payment
 */
class OrderItemData implements OrderItemDataProvider
{
    use PriceFormatterTrait;

    /**
     * Item data.
     *
     * @var array
     */
    private $data;

    /**
     * OrderItemData constructor.
     *
     * @param \WC_Order_Item $data Item data.
     */
    public function __construct(\WC_Order_Item $data)
    {
        $this->data = $data->get_data();
    }

    /**
     * Returns the item price.
     *
     * @return string
     */
    public function get_price()
    {
        return $this->format($this->data['subtotal'] / $this->get_quantity());
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
        $product = $this->get_product();

        return $product->get_title();
    }

    /**
     * Returns the WC_Product associated with the order item.
     *
     * @return \WC_Product
     */
    protected function get_product()
    {
        return wc_get_product($this->data['product_id']);
    }

    /**
     * Returns the product SKU.
     *
     * @return string|null
     */
    public function get_sku()
    {
        $product = $this->get_product();
        $sku = $product->get_sku();
        if ($product instanceof \WC_Product_Variation) {
            $sku = $product->parent->get_sku();
        }

        return $sku;
    }
}
