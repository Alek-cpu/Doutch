<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Session;

use OutOfBoundsException;

/**
 * Interface Session
 * @package WCPayPalPlus\Session
 */
interface Session
{
    const ORDER_ID = 'ppp_order_id';
    const PAYMENT_ID = 'ppp_payment_id';
    const PAYER_ID = 'ppp_payer_id';
    const APPROVAL_URL = 'ppp_approval_url';
    const PAYMENT_TOKEN = 'ppp_payment_token';

    const CHOSEN_PAYMENT_METHOD = 'chosen_payment_method';

    const SESSION_CHECK_KEY = '_ppp_default_override_flag';
    const SESSION_CHECK_ACTIVATE = '1';

    const DEFAULT_VALUE = null;

    const ALLOWED_PROPERTIES = [
        self::ORDER_ID,
        self::PAYMENT_ID,
        self::PAYER_ID,
        self::APPROVAL_URL,
        self::CHOSEN_PAYMENT_METHOD,
        self::SESSION_CHECK_KEY,
        self::SESSION_CHECK_ACTIVATE,
        self::PAYMENT_TOKEN,
    ];

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name);

    /**
     * @param string $name
     * @param mixed $value
     * @throws OutOfBoundsException
     */
    public function set($name, $value);

    /**
     * @param string $name
     * @return void
     */
    public function delete($name);

    /**
     * @param $name
     * @return bool
     */
    public function has($name);

    /**
     * Delete all of the properties and their values from the session storage
     */
    public function clean();
}
