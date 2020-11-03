<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Setting;

use WooCommerce;

/**
 * Class SharedRepository
 * @package WCPayPalPlus\Setting
 */
class SharedRepository implements Storable
{
    use SharedRepositoryTrait;

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * SharedRepository constructor.
     * @param WooCommerce $wooCommerce
     */
    public function __construct(WooCommerce $wooCommerce)
    {
        $this->wooCommerce = $wooCommerce;
    }

    /**
     * Retrieve a Shared Option by the Given Name
     *
     * @param $name
     * @param null $default
     * @return mixed
     */
    private function get_option($name, $default = null)
    {
        assert(is_string($name));

        $option = get_option(SharedPersistor::OPTION_NAME, $default) ?: $default;

        return isset($option[$name]) ? $option[$name] : $default;
    }
}
