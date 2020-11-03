<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Service;

/**
 * Interface for all bootstrappable service provider implementations.
 */
interface BootstrappableServiceProvider extends ServiceProvider
{
    /**
     * Bootstraps the registered services.
     *
     * @param Container $container
     */
    public function bootstrap(Container $container);
}
