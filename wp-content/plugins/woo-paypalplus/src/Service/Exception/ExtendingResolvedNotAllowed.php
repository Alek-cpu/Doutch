<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Service\Exception;

/**
 * Exception to be thrown when a value that has already been set is to be manipulated.
 */
class ExtendingResolvedNotAllowed extends InvalidValueAccess
{
    /**
     * @param string $name
     * @return ExtendingResolvedNotAllowed
     */
    public static function forName($name)
    {
        assert(is_string($name));

        return new static("Cannot extend service {$name}. It has already been resolved.");
    }
}
