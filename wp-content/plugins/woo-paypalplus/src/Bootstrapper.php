<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus;

use Exception;
use WC_Log_Levels;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\ServiceProvidersCollection;

/**
 * Class Bootstrap
 * @package WCPayPalPlus
 */
class Bootstrapper
{
    const ACTION_ACTIVATION = 'wcpaypalplus.activation';
    const ACTION_ADD_SERVICE_PROVIDERS = 'wcpaypalplus.add_service_providers';
    const ACTION_LOG = 'wcpaypalplus.log';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $pluginFile;

    /**
     * Bootstrapper constructor.
     * @param Container $container
     * @param $pluginFile
     */
    public function __construct(Container $container, $pluginFile)
    {
        assert(is_string($pluginFile) && !empty($pluginFile));

        $this->container = $container;
        $this->pluginFile = $pluginFile;
    }

    /**
     * Bootstraps PayPal PLUS for WooCommerce
     *
     * @return bool
     *
     * @throws Exception
     * @wp-hook plugins_loaded
     */
    public function bootstrap()
    {
        if (!$this->versionCheck()) {
            return false;
        }
        if (!$this->wooCommerceCheck()) {
            return false;
        }
        // Plugin doesn't work well with cron because of WooCommerce Session.
        // To now spread conditional here and there since we don't actually need to do stuffs
        // during cron I have disabled the plugin here.
        if (defined('DOING_CRON') && DOING_CRON) {
            return false;
        }

        /** @noinspection BadExceptionsProcessingInspection */
        try {
            /** @var Container $container */
            $serviceProviders = $this->serviceProviders();
            $payPalPlus = new PayPalPlus($this->container, $serviceProviders);
            $bootstrapped = $payPalPlus->bootstrap();
        } catch (Exception $exc) {
            do_action(self::ACTION_LOG, WC_Log_Levels::ERROR, $exc->getMessage(), compact($exc));

            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw $exc;
            }

            $bootstrapped = false;
        }

        return $bootstrapped;
    }

    /**
     * @return ServiceProvidersCollection
     */
    protected function serviceProviders()
    {
        $this->container->shareValue(
            PluginProperties::class,
            new PluginProperties($this->pluginFile)
        );

        $providers = new ServiceProvidersCollection();
        $providers
            ->add(new Core\ServiceProvider())
            ->add(new Log\ServiceProvider())
            ->add(new Install\ServiceProvider())
            ->add(new Uninstall\ServiceProvider())
            ->add(new Deactivate\ServiceProvider())
            ->add(new Utils\ServiceProvider())
            ->add(new Notice\ServiceProvider())
            ->add(new Assets\ServiceProvider())
            ->add(new Session\ServiceProvider())
            ->add(new Setting\ServiceProvider())
            ->add(new Request\ServiceProvider())
            ->add(new Http\ServiceProvider())
            ->add(new Admin\ServiceProvider())
            ->add(new Gateway\ServiceProvider())
            ->add(new WC\ServiceProvider())
            ->add(new Ipn\ServiceProvider())
            ->add(new Pui\ServiceProvider())
            ->add(new Api\ServiceProvider())
            ->add(new Order\ServiceProvider())
            ->add(new Refund\ServiceProvider())
            ->add(new Payment\ServiceProvider())
            ->add(new ExpressCheckoutGateway\ServiceProvider())
            ->add(new Banner\ServiceProvider())
            ->add(new PlusGateway\ServiceProvider());

        /**
         * Fires right before MultilingualPress gets bootstrapped.
         *
         * Hook here to add custom service providers via
         * `ServiceProviderCollection::add_service_provider()`.
         *
         * @param ServiceProvidersCollection $providers
         */
        do_action(self::ACTION_ADD_SERVICE_PROVIDERS, $providers);

        return $providers;
    }

    /**
     * Admin Message
     * @param $message
     */
    protected function adminNotice($message)
    {
        add_action(
            'admin_notices',
            function () use ($message) {
                $class = 'notice notice-error';
                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
            }
        );
    }

    /**
     * @return bool
     */
    protected function versionCheck()
    {
        $minPhpVersion = '5.6';
        if (PHP_VERSION < $minPhpVersion) {
            $this->adminNotice(
                sprintf(
                    __(
                        'PayPal PLUS requires PHP version %1$1s or higher. You are running version %2$2s ',
                        'woo-paypalplus'
                    ),
                    $minPhpVersion,
                    PHP_VERSION
                )
            );

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function wooCommerceCheck()
    {
        if (!function_exists('WC')) {
            $this->adminNotice(
                __('PayPal PLUS requires WooCommerce to be active.', 'woo-paypalplus')
            );
            return false;
        }

        if (version_compare(wc()->version, '3.6.4', '<')) {
            $this->adminNotice(
                __(
                    'PayPal PLUS requires WooCommerce version 3.6.4 or higher.',
                    'woo-paypalplus'
                )
            );
            return false;
        }

        return true;
    }
}
