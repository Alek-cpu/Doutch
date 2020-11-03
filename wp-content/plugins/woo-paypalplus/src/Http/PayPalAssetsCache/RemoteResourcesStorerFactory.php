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

use WP_Filesystem_Base;

/**
 * Class RemoteResourcesStorerFactory
 * @package WCPayPalPlus\Http\PayPalAssetsCache
 */
class RemoteResourcesStorerFactory
{
    /**
     * @param WP_Filesystem_Base|null $fileSystem
     * @param Boolean $cachedPayPalJsFiles
     *
     * @return NullRemoteResourcesStorer|RemoteResourcesStorer
     */
    public static function create($fileSystem, $cachedPayPalJsFiles)
    {
        if (!$cachedPayPalJsFiles || !$fileSystem) {
            return new NullRemoteResourcesStorer();
        }
        return new RemoteResourcesStorer($fileSystem);
    }

}
