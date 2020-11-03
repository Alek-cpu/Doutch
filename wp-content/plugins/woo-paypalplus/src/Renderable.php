<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus;

/**
 * Interface Renderable
 * @package WCPayPalPlus\ExpressCheckout
 */
interface Renderable
{
    /**
     * Render the button view
     *
     * @return void
     */
    public function render();
}
