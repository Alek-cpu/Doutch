<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Api\ErrorData;

/**
 * Class NullErrorData
 * @package WCPayPalPlus\Api
 */
final class NullError implements Error
{
    /**
     * @inheritdoc
     */
    public function code()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function details()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function message()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function debugId()
    {
        return '';
    }
}
