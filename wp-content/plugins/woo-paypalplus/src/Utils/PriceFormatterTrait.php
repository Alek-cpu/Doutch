<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Utils;

/**
 * Trait PriceFormatterTrait
 * @package WCPayPalPlus\Utils
 */
trait PriceFormatterTrait
{
    /**
     * @param float $price The un-formatted price.
     * @return float
     */
    private function format($price)
    {
        $decimals = 2;

        if ($this->currencyNotSupportDecimals()) {
            $decimals = 0;
        }

        return wc_format_decimal($price, $decimals);
    }

    /**
     * Rounds a price to 2 decimals.
     *
     * @param float $price The item price.
     * @return float
     */
    private function round($price)
    {
        $precision = 2;

        if ($this->currencyNotSupportDecimals()) {
            $precision = 0;
        }

        return round($price, $precision);
    }

    /**
     * Checks if the currency supports decimals.
     *
     * @return bool
     */
    private function currencyNotSupportDecimals()
    {
        return in_array(get_woocommerce_currency(), ['HUF', 'JPY', 'TWD'], true);
    }
}
