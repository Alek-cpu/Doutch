<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Service\Exception;

/**
 * Exception to be thrown when a not shared value or factory callback is to be accessed on a
 * bootstrapped container.
 */
class LateAccessToNotSharedService extends InvalidValueReadAccess
{
    /**
     * @param string $name
     * @param string $action
     * @return self
     */
    public static function forService($name, $action)
    {
        assert(is_string($name));
        assert(is_string($action));

        return new static(
            sprintf(
                'Cannot %2$s not shared "%1$s". The container has already been bootstrapped.',
                $name,
                $action
            )
        );
    }
}
