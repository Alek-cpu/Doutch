<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 27.01.17
 * Time: 15:30
 */

namespace WCPayPalPlus\Payment;

use WCPayPalPlus\Utils\PriceFormatterTrait;

/**
 * Class OrderDiscountData
 *
 * @package WCPayPalPlus\Payment
 */
class OrderDiscountData implements OrderItemDataProvider
{
    use PriceFormatterTrait;
    /**
     * Item data.
     *
     * @var array
     */
    private $data;

    /**
     * OrderDiscountData constructor.
     *
     * @param array $data Item data.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns the discount amount.
     *
     * @return string
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
        return (int)$this->data['qty'];
    }

    /**
     * Returns the item name.
     *
     * @return string
     */
    public function get_name()
    {
        return $this->data['name'];
    }

    /**
     * Returns no SKU.
     *
     * @return string|null
     */
    public function get_sku()
    {
        return null;
    }
}
