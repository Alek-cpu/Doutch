<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Assets;

use WC_Admin_Settings as Settings;
use WCPayPalPlus\Setting\ExpressCheckoutStorable;

/**
 * Class SmartButtonArguments
 * @package WCPayPalPlus\Assets
 */
class SmartButtonArguments
{
    const ENVIRONMENT_SANDBOX = 'sandbox';
    const ENVIRONMENT_PRODUCTION = 'production';

    const DEFAULT_CURRENCY = 'EUR';

    const FILTER_LOCALE = 'woopaypalplus.express_checkout_button_locale';
    const DISABLED_FUNDING = [
        'card',
        'credit',
    ];

    /**
     * @var ExpressCheckoutStorable
     */
    private $settingRepository;

    /**
     * SmartButtonArguments constructor.
     * @param ExpressCheckoutStorable $settingRepository
     */
    public function __construct(ExpressCheckoutStorable $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * Return the Script Arguments as an array
     *
     * @return array
     */
    public function toArray()
    {
        $currency = $this->wooCommerceSettings('currency', self::DEFAULT_CURRENCY);
        $locale = get_locale();

        /**
         * Filter locale
         *
         * Allow third parties to filter the locale if needed.
         *
         * @param string $locale
         */
        $locale = apply_filters(self::FILTER_LOCALE, $locale);

        return [
            'currency' => $currency,
            'intent' => 'authorize',
            'payment_method' => 'paypal',
            'env' => $this->environment(),
            'locale' => $locale,
            'funding' => [
                'disallowed' => self::DISABLED_FUNDING,
            ],
            'style' => [
                'color' => $this->settingRepository->buttonColor(),
                'shape' => $this->settingRepository->buttonShape(),
                'size' => $this->settingRepository->buttonSize(),
                'label' => $this->settingRepository->buttonLabel(),
                'layout' => 'horizontal',
                'branding' => true,
                'tagline' => false,
            ],
            'redirect_urls' => [
                'cancel_url' => $this->settingRepository->cancelUrl(),
                'return_url' => $this->settingRepository->returnUrl(),
            ],
        ];
    }

    /**
     * Retrieve a WooCommerce Option by the given name
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    private function wooCommerceSettings($name, $default = null)
    {
        assert(is_string($name));

        return Settings::get_option($name, $default);
    }

    /**
     * Retrieve the environment
     *
     * @return string
     */
    private function environment()
    {
        return $this->settingRepository->isSandboxed() ? 'sandbox' : 'production';
    }
}
