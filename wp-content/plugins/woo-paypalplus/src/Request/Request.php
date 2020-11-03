<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Request;

/**
 * Class Request
 *
 * @package WCPayPalPlus\Request
 */
class Request
{
    const KEY_MC_FEE = 'mc_fee';
    const KEY_TXN_ID = 'txn_id';
    const KEY_CUSTOM = 'custom';
    const KEY_PENDING_REASON = 'pending_reason';
    const KEY_PAYMENT_STATUS = 'payment_status';
    const KEY_PAYMENT_METHOD = 'payment_method';

    const INPUT_PAYER_ID = 'PayerID';
    const INPUT_PAYMENT_ID = 'paymentId';
    const INPUT_PAYMENT_TOKEN = 'token';

    /**
     * Request data
     *
     * @var array
     */
    private $request;

    /**
     * Data constructor.
     * @param array $request
     */
    public function __construct(array $request)
    {
        $this->request = $request;
    }

    /**
     * Returns all request data
     *
     * @return array
     */
    public function all()
    {
        return $this->request;
    }

    /**
     * @param $name
     * @param $filter
     * @param null $options
     * @return mixed
     */
    public function get($name, $filter, $options = null)
    {
        if (!$this->has($name)) {
            return null;
        }

        $value = $this->request[$name];

        if ($name === self::KEY_PAYMENT_STATUS) {
            $value = strtolower($value);
        }

        return filter_var($value, $filter, $options);
    }

    /**
     * Checks if a specific value exists
     *
     * @param string $offset The key to search.
     *
     * @return bool
     */
    public function has($offset)
    {
        return isset($this->request[$offset]);
    }
}
