<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Session;

/**
 * Class NullWooCommerceSession
 * @package WCPayPalPlus\Session
 */
class NullWooCommerceSession
{
    public function get($name)
    {
    }

    public function set($name, $value)
    {
    }

    public function __unset($name)
    {
    }

    public function __isset($name)
    {
    }
}
