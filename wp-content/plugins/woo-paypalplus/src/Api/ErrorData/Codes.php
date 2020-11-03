<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Api\ErrorData;

/**
 * Class Code
 * @package WCPayPalPlus\Api\ErrorData
 */
class Codes
{
    const ERROR_INSTRUMENT_DECLINED = 'INSTRUMENT_DECLINED';
    const ERROR_INSUFFICIENT_FUNDS = 'INSUFFICIENT_FUNDS';
    const ERROR_INVALID_RESOURCE_ID = 'INVALID_RESOURCE_ID';
    const ERROR_VALIDATION_ERROR = 'VALIDATION_ERROR';

    const REDIRECTABLE_ERROR_CODES = [
        self::ERROR_INSTRUMENT_DECLINED,
        self::ERROR_INSUFFICIENT_FUNDS,
    ];
}
