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
 * Class RemoteResourcesStorer
 * @package WCPayPalPlus\Http\PayPalAssetsCache
 */
class NullRemoteResourcesStorer implements RemoteResourcesStorerInterface
{

    /**
     * NullResourcesDownloader constructor.
     *
     */
    public function __construct()
    {
    }

    /**
     * Update Resources
     *
     * @param ResourceDictionary $resourceDictionary
     * @return void
     */
    public function update(ResourceDictionary $resourceDictionary)
    {
    }
}
