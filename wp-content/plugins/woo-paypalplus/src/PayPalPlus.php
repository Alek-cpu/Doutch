<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus;

use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\IntegrationServiceProvider;
use WCPayPalPlus\Service\ServiceProvider;
use WCPayPalPlus\Service\ServiceProvidersCollection;

/**
 * Class PayPalPlus
 * @package WCPayPalPlus
 */
class PayPalPlus
{
    const ACTION_BOOTSTRAPPED = 'wcpaypalplus.bootstrapped';
    const ACTION_REGISTER_MODULES = 'wcpaypalplus.register_modules';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var ServiceProvidersCollection
     */
    private $serviceProviders;

    /**
     * @param Container $container
     * @param ServiceProvidersCollection $serviceProviders
     */
    public function __construct(Container $container, ServiceProvidersCollection $serviceProviders)
    {
        $this->container = $container;
        $this->serviceProviders = $serviceProviders;
    }

    /**
     * Bootstraps MultilingualPress.
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function bootstrap()
    {
        if (did_action(self::ACTION_BOOTSTRAPPED)) {
            throw new \RuntimeException(
                'Cannot bootstrap an instance that has already been bootstrapped.'
            );
        }

        $this->serviceProviders->applyMethod('register', $this->container);

        // Lock the container. Nothing can be registered after that.
        $this->container->lock();

        $integrations = $this->serviceProviders->filter(
            function (ServiceProvider $provider) {
                return $provider instanceof IntegrationServiceProvider;
            }
        );
        $integrations->applyMethod('integrate', $this->container);

        $bootstrappable = $this->serviceProviders->filter(
            function (ServiceProvider $provider) {
                return $provider instanceof BootstrappableServiceProvider;
            }
        );

        $bootstrappable->applyMethod('bootstrap', $this->container);

        $this->container->bootstrap();

        /**
         * Fires right after MultilingualPress was bootstrapped.
         */
        do_action(static::ACTION_BOOTSTRAPPED);

        return true;
    }
}
