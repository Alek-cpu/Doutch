<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus;

use Brain\Nonces\NonceContextInterface;
use Brain\Nonces\NonceInterface;
use WooCommerce;

/**
 * Class Nonce
 * @package WCPayPalPlus
 */
final class Nonce implements NonceInterface
{
    /**
     * @var NonceInterface
     */
    private $nonce;

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * Nonce constructor.
     * @param NonceInterface $nonce
     * @param WooCommerce $wooCommerce
     */
    public function __construct(NonceInterface $nonce, WooCommerce $wooCommerce)
    {
        $this->nonce = $nonce;
        $this->wooCommerce = $wooCommerce;
    }

    /**
     * @inheritDoc
     */
    public function action()
    {
        return $this->nonce->action();
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $this->disableWoCommerceFilters();
        $string = $this->nonce->__toString();
        $this->enableWooCommerceFilters();

        return $string;
    }

    /**
     * @inheritDoc
     */
    public function validate(NonceContextInterface $context = null)
    {
        $this->disableWoCommerceFilters();
        $isValid = $this->nonce->validate($context);
        $this->enableWooCommerceFilters();

        return $isValid;
    }

    /**
     * Disable WooCommerce Filters
     */
    private function disableWoCommerceFilters()
    {
        remove_filter(
            'nonce_user_logged_out',
            [$this->wooCommerce->session, 'nonce_user_logged_out']
        );
    }

    /**
     * Enable WooCommerce Filters
     */
    private function enableWooCommerceFilters()
    {
        add_filter(
            'nonce_user_logged_out',
            [$this->wooCommerce->session, 'nonce_user_logged_out']
        );
    }
}
