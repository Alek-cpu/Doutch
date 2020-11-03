<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Session;

use WC_Session_Handler;
use OutOfBoundsException;
use WooCommerce;

/**
 * Class Session
 * @package WCPayPalPlus\Payment
 */
class WooCommerceSession implements Session
{
    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * Session constructor.
     * @param WooCommerce $wooCommerce
     */
    public function __construct(WooCommerce $wooCommerce)
    {
        $this->wooCommerce = $wooCommerce;
    }

    /**
     * @param $name
     * @return array|string
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            return self::DEFAULT_VALUE;
        }

        return $this->session()->get($name);
    }

    /**
     * @param $name
     * @param $value
     * @throws OutOfBoundsException
     */
    public function set($name, $value)
    {
        if (!in_array($name, self::ALLOWED_PROPERTIES, true)) {
            throw new OutOfBoundsException("Cannot set unknown property {$name}");
        }

        $this->session()->set($name, $value);
    }

    /**
     * @inheritdoc
     */
    public function delete($name)
    {
        assert(is_string($name));

        $this->session()->__unset($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return $this->session()->__isset($name);
    }

    /**
     * Delete all of the properties and their values from the session storage
     */
    public function clean()
    {
        foreach (self::ALLOWED_PROPERTIES as $property) {
            $this->session()->__unset($property);
        }
    }

    /**
     * Lazy load the session because WooCommerce set the session during init hook
     *
     * @return WC_Session_Handler|NullWooCommerceSession
     *
     * phpcs:disable Generic.NamingConventions.ConstructorName.OldStyle
     */
    private function session()
    {
        // phpcs:enable

        if (!did_action('woocommerce_init')) {
            _doing_it_wrong(__METHOD__, 'Cannot be called before WordPress init.', '2.0.0');
        }

        static $nullSessionObject = null;

        $session = $this->wooCommerce->session;

        if (!$session && $nullSessionObject === null) {
            $nullSessionObject = new NullWooCommerceSession();
        }

        if (!$session) {
            return $nullSessionObject;
        }

        return $session;
    }
}
