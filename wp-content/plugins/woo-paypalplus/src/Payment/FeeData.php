<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.02.17
 * Time: 10:58
 */

namespace WCPayPalPlus\Payment;

use WCPayPalPlus\Utils\PriceFormatterTrait;

/**
 * Class FeeData
 *
 * @package WCPayPalPlus\Payment
 */
class FeeData implements OrderItemDataProvider
{
    use PriceFormatterTrait;

    /**
     * Fee data.
     *
     * @var array
     */
    private $fee;

    /**
     * FeeData constructor.
     *
     * @param object $fee Item data.
     */
    public function __construct($fee)
    {
        $this->fee = $fee;
    }

    /**
     * Returns the item price.
     *
     * @return float
     */
    public function get_price()
    {
        return $this->fee->amount;
    }

    /**
     * Returns the item quantity.
     *
     * @return int
     */
    public function get_quantity()
    {
        return 1;
    }

    /**
     * Returns the item name.
     *
     * @return string
     */
    public function get_name()
    {
        return $this->fee->name;
    }

    /**
     * Returns the item SKU.
     *
     * @return string|null
     */
    public function get_sku()
    {
        return null;
    }
}
