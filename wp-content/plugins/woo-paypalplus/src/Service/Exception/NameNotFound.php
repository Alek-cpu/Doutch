<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Service\Exception;

/**
 * Exception to be thrown when a value or factory callback could not be found in the container.
 */
class NameNotFound extends InvalidValueReadAccess
{
    /**
     * Returns a new exception object.
     *
     * @param string $name
     * @return self
     */
    public static function forName($name)
    {
        assert(is_string($name));

        return new static("There is neither a value or service named '{$name}'.");
    }
}
