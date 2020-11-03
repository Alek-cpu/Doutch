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
 * Class AssetsStoreUpdater
 * @package WCPayPalPlus\Http\PayPalAssetsCache
 */
class AssetsStoreUpdater
{
    /**
     * @var ResourceDictionary
     */
    private $resourceDictionary;

    /**
     * @var RemoteResourcesStorerInterface
     */
    private $remoteResourcesStorer;

    /**
     * StorerCron constructor.
     * @param RemoteResourcesStorerInterface $remoteResourcesStorer
     * @param ResourceDictionary $resourceDictionary
     */
    public function __construct(
        RemoteResourcesStorerInterface $remoteResourcesStorer,
        ResourceDictionary $resourceDictionary
    ) {

        $this->remoteResourcesStorer = $remoteResourcesStorer;
        $this->resourceDictionary = $resourceDictionary;
    }

    /**
     * Execute Cron Event
     */
    public function update()
    {
        $this->remoteResourcesStorer->update($this->resourceDictionary);
    }
}
