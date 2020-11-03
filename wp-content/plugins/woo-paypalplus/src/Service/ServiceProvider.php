<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Service;

/**
 * Interface for all service provider implementations to be used for dependency management.
 */
interface ServiceProvider
{
    /**
     * Registers the provided services on the given container.
     *
     * @param Container $container
     */
    public function register(Container $container);
}
