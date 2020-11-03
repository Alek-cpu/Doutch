<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 06.02.17
 * Time: 16:02
 */

namespace WCPayPalPlus\Payment;

use WCPayPalPlus\Utils\PriceFormatterTrait;

/**
 * Class OrderFeeData
 *
 * @package WCPayPalPlus\Payment
 */
class OrderFeeData implements OrderItemDataProvider
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
     * @param array $data Item data.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns the item price.
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
     * Returns no product SKU.
     *
     * @return string|null
     */
    public function get_sku()
    {
        return null;
    }
}
