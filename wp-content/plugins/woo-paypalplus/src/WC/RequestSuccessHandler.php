<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\WC;

/**
 * Interface RequestSuccessHandler
 * @package WCPayPalPlus\WC
 */
interface RequestSuccessHandler
{
    /**
     * Handles a successful REST call
     *
     * @return void
     */
    public function execute();
}
