<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Setting;

use WCPayPalPlus\ExpressCheckoutGateway\GatewaySettingsModel;

/**
 * Trait ExpressCheckoutRepositoryTrait
 * @package WCPayPalPlus\Setting
 */
trait ExpressCheckoutRepositoryTrait
{
    /**
     * @inheritDoc
     */
    public function showOnProductPage()
    {
        $option = $this->get_option(
            ExpressCheckoutStorable::OPTION_SHOW_ON_PRODUCT_PAGE,
            Storable::OPTION_ON
        );

        return $option === Storable::OPTION_ON;
    }

    /**
     * @inheritDoc
     */
    public function showOnMiniCart()
    {
        $option = $this->get_option(
            ExpressCheckoutStorable::OPTION_SHOW_ON_MINI_CART,
            Storable::OPTION_ON
        );

        return $option === Storable::OPTION_ON;
    }

    /**
     * @inheritDoc
     */
    public function showOnCart()
    {
        $option = $this->get_option(
            ExpressCheckoutStorable::OPTION_SHOW_ON_CART,
            Storable::OPTION_ON
        );

        return $option === Storable::OPTION_ON;
    }

    /**
     * @inheritDoc
     */
    public function buttonColor()
    {
        $option = $this->get_option(
            ExpressCheckoutStorable::OPTION_BUTTON_COLOR,
            GatewaySettingsModel::DEFAULT_BUTTON_COLOR
        );

        return $option;
    }

    /**
     * @inheritDoc
     */
    public function buttonShape()
    {
        $option = $this->get_option(
            ExpressCheckoutStorable::OPTION_BUTTON_SHAPE,
            GatewaySettingsModel::DEFAULT_BUTTON_SHAPE
        );

        return $option;
    }

    /**
     * @inheritDoc
     */
    public function buttonSize()
    {
        $option = $this->get_option(
            ExpressCheckoutStorable::OPTION_BUTTON_SIZE,
            GatewaySettingsModel::DEFAULT_BUTTON_SIZE
        );

        return $option;
    }

    /**
     * @inheritDoc
     */
    public function buttonLabel()
    {
        $option = $this->get_option(
            ExpressCheckoutStorable::OPTION_BUTTON_LABEL,
            GatewaySettingsModel::DEFAULT_BUTTON_LABEL
        );

        return $option;
    }
}
