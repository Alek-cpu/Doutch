<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Setting;

/**
 * Interface ExpressCheckoutStorable
 * @package WCPayPalPlus\Setting
 */
interface ExpressCheckoutStorable extends Storable
{
    const OPTION_SHOW_ON_PRODUCT_PAGE = 'show_on_product_page';
    const OPTION_SHOW_ON_MINI_CART = 'show_on_mini_cart';
    const OPTION_SHOW_ON_CART = 'show_on_cart';

    const OPTION_BUTTON_COLOR = 'button_color';
    const OPTION_BUTTON_SHAPE = 'button_shape';
    const OPTION_BUTTON_SIZE = 'button_size';
    const OPTION_BUTTON_LABEL = 'button_label';

    /**
     * @return bool
     */
    public function showOnProductPage();

    /**
     * @return bool
     */
    public function showOnMiniCart();

    /**
     * @return bool
     */
    public function showOnCart();

    /**
     * @return string
     */
    public function buttonColor();

    /**
     * @return string
     */
    public function buttonShape();

    /**
     * @return string
     */
    public function buttonSize();

    /**
     * @return string
     */
    public function buttonLabel();
}
