<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Http\PayPalAssetsCache;

/**
 * Class ResourceDictionary
 * @package WCPayPalPlus\Http\PayPalAssetsCache
 */
class ResourceDictionary
{
    /**
     * @var array
     */
    private $list;

    /**
     * ResourceDictionary constructor.
     * @param array $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }

    /**
     * @return array
     */
    public function resourcesList()
    {
        return $this->list;
    }
}
