<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus;

use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\Exception\NameNotFound;
use WCPayPalPlus\Setting\ExpressCheckoutStorable;
use WCPayPalPlus\Setting\Storable;

/**
 *  * Resolves the value with the given name from the container.
 *
 *
 * @param string $name
 * @return mixed
 * @throws NameNotFound
 * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
 */
function resolve($name = '')
{
    // phpcs:enable

    assert(is_string($name));

    static $container;
    $container or $container = new Container();

    if ($name && !isset($container[$name])) {
        throw NameNotFound::forName($name);
    }

    return $name ? $container[$name] : $container;
}

/**
 * Check if Given Gateway is Available or not
 *
 * @param $gateway
 * @return bool
 */
function isGatewayDisabled($gateway)
{
    return ($gateway->enabled !== Storable::OPTION_ON);
}

/**
 * Check if no ExpressCheckout Button have to be Displayed
 *
 * @return bool
 * @throws NameNotFound
 */
function areAllExpressCheckoutButtonsDisabled()
{
    $settingRepository = resolve(ExpressCheckoutStorable::class);

    $buttons = [
        $settingRepository->showOnProductPage(),
        $settingRepository->showOnMiniCart(),
        $settingRepository->showOnCart(),
    ];

    return count(array_filter($buttons)) === 0;
}
