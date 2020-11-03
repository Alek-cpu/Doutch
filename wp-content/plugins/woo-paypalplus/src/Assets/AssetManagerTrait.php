<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Assets;

use WCPayPalPlus\PluginProperties;

/**
 * Interface AssetManagerTrait
 * @property PluginProperties $pluginProperties
 * @package WCPayPalPlus\Assets
 */
trait AssetManagerTrait
{
    /**
     * Localize Scripts
     * @param $handle
     * @param $objName
     * @param array $data
     */
    private function loadScriptsData($handle, $objName, array $data)
    {
        assert(is_string($handle));
        assert(is_string($objName));

        wp_localize_script($handle, $objName, $data);
    }

    /**
     * Retrieve the assets and url path for scripts
     *
     * @return array
     */
    private function assetUrlPath()
    {
        $assetPath = untrailingslashit($this->pluginProperties->dirPath());
        $assetUrl = untrailingslashit($this->pluginProperties->dirUrl());

        return [
            $assetPath,
            $assetUrl,
        ];
    }
}
