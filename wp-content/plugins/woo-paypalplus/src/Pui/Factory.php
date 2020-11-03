<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Pui;

/**
 * Class PaymentInstructionDataFactory
 * @package WCPayPalPlus\Pui
 */
class Factory
{
    public static function createData(\WC_Order $order, $legalNote)
    {
        assert(is_string($legalNote));

        return new Data($order, $legalNote);
    }

    public static function createViewFromData(Data $data)
    {
        return new View($data);
    }
}
