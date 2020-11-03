<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use WCPayPalPlus\Setting\ExpressCheckoutStorable;
use WCPayPalPlus\Setting\SettingsGatewayModel;
use WCPayPalPlus\Setting\SharedFieldsOptionsTrait;
use WCPayPalPlus\Setting\SharedSettingsModel;
use WC_Payment_Gateway;
use WCPayPalPlus\Setting\Storable;

/**
 * Class GatewaySettingsModel
 *
 * @package WCPayPalPlus\ExpressCheckoutGateway
 */
final class GatewaySettingsModel implements SettingsGatewayModel
{
    use SharedFieldsOptionsTrait;

    const DEFAULT_BUTTON_COLOR = 'gold';
    const DEFAULT_BUTTON_SHAPE = 'rect';
    const DEFAULT_BUTTON_SIZE = 'responsive';
    const DEFAULT_BUTTON_LABEL = 'paypal';

    /**
     * @var SharedSettingsModel
     */
    private $sharedSettingsModel;

    /**
     * GatewaySettingsModel constructor.
     * @param SharedSettingsModel $sharedSettingsModel
     */
    public function __construct(SharedSettingsModel $sharedSettingsModel)
    {
        $this->sharedSettingsModel = $sharedSettingsModel;
    }

    /**
     * @param WC_Payment_Gateway $gateway
     * @return array
     */
    public function settings(WC_Payment_Gateway $gateway)
    {
        /** @noinspection AdditionOperationOnArraysInspection */
        $settings =
            $this->general()
            + $this->sharedSettingsModel->credentials()
            + $this->sharedSettingsModel->webProfile($gateway)
            + $this->gateway()
            + $this->buttonsPosition()
            + $this->buttonsLayout()
            + $this->sharedSettingsModel->debugLog();

        return $settings;
    }

    /**
     * @return array
     */
    private function general()
    {
        return [
            'enabled' => [
                'title' => esc_html_x('Enable/Disable', 'gateway-settings', 'woo-paypalplus'),
                'type' => 'checkbox',
                'label' => esc_html_x(
                    'Enable PayPal Express Checkout',
                    'gateway-settings',
                    'woo-paypalplus'
                ),
                'default' => 'no',
            ],
            'title' => [
                'title' => esc_html_x('Title', 'gateway-settings', 'woo-paypalplus'),
                'type' => 'text',
                'description' => esc_html_x(
                    'This controls the name of the payment gateway the user sees during checkout.',
                    'gateway-settings',
                    'woo-paypalplus'
                ),
                'default' => esc_html_x('Paypal Checkout', 'gateway-setting', 'woo-paypalplus'),
            ],
            'description' => [
                'title' => esc_html_x('Description', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'text',
                'description' => esc_html_x(
                    'This controls the payment gateway description the user sees during checkout.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
        ];
    }

    /**
     * @return array
     */
    private function gateway()
    {
        $invoicePrefix = $this->sharedSettingsModel->invoicePrefix();
        $cachePaypalJs = $this->sharedSettingsModel->cachePaypalJsFiles();
        $options = [
            'cancel_url' => [
                'title' => esc_html_x('Cancel Page', 'gateway-setting', 'woo-paypalplus'),
                'description' => esc_html_x(
                    'Sets the page users will be returned to if they click the Cancel link on the PayPal checkout pages.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'type' => 'select',
                'options' => $this->cancelPageOptions(),
                'default' => wc_get_page_id('checkout'),
            ],
            'cancel_custom_url' => [
                'title' => esc_html_x(
                    'Custom Cancellation URL',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'type' => 'text',
                'description' => esc_html_x(
                    'URL to a custom page to be used for cancelation. Please select "custom" above first.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
        ];

        return array_merge(
            [
                'settings_section' => [
                    'title' => esc_html_x('Settings', 'gateway-setting', 'woo-paypalplus'),
                    'type' => 'title',
                    'desc' => '',
                ],
            ],
            $invoicePrefix,
            $cachePaypalJs,
            $options
        );
    }

    /**
     * @return array
     */
    private function buttonsPosition()
    {
        return [
            'buttons_position_section' => [
                'title' => esc_html_x('Buttons Position', 'gateway-settings', 'woo-paypalplus'),
                'type' => 'title',
                'desc' => '',
            ],
            ExpressCheckoutStorable::OPTION_SHOW_ON_PRODUCT_PAGE => [
                'title' => esc_html_x('Single product pages', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'checkbox',
                'default' => Storable::OPTION_ON,
                'description' => esc_html_x(
                    'Allows you to show or hide the Express Checkout button within single product pages.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
            ExpressCheckoutStorable::OPTION_SHOW_ON_MINI_CART => [
                'title' => esc_html_x('Mini Cart', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'checkbox',
                'default' => Storable::OPTION_ON,
                'description' => esc_html_x(
                    'Allows you to show or hide the Express Checkout button within the mini cart.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
            ExpressCheckoutStorable::OPTION_SHOW_ON_CART => [
                'title' => esc_html_x('Cart Page', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'checkbox',
                'default' => Storable::OPTION_ON,
                'description' => esc_html_x(
                    'Allows you to show or hide the Express Checkout button within the cart page.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
        ];
    }

    /**
     * @return array
     */
    private function buttonsLayout()
    {
        return [
            'buttons_layout_section' => [
                'title' => esc_html_x('Buttons Layout', 'gateway-settings', 'woo-paypalplus'),
                'type' => 'title',
                'desc' => '',
            ],
            ExpressCheckoutStorable::OPTION_BUTTON_COLOR => [
                'title' => esc_html_x('Button Color', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'select',
                'options' => [
                    'gold' => esc_html_x('Gold', 'gateway-settings', 'woo-paypalplus'),
                    'blue' => esc_html_x('Blue', 'gateway-settings', 'woo-paypalplus'),
                    'silver' => esc_html_x('Silver', 'gateway-settings', 'woo-paypalplus'),
                    'white' => esc_html_x('White', 'gateway-settings', 'woo-paypalplus'),
                    'black' => esc_html_x('Black', 'gateway-settings', 'woo-paypalplus'),
                ],
                'default' => self::DEFAULT_BUTTON_COLOR,
                'description' => esc_html_x(
                    'Choose the color of the Express Checkout button.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
            ExpressCheckoutStorable::OPTION_BUTTON_SHAPE => [
                'title' => esc_html_x('Button Shape', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'select',
                'options' => [
                    'rect' => esc_html_x('Rect', 'gateway-settings', 'woo-paypalplus'),
                    'pill' => esc_html_x('Pill', 'gateway-settings', 'woo-paypalplus'),
                ],
                'default' => self::DEFAULT_BUTTON_SHAPE,
                'description' => esc_html_x(
                    'Choose the shape of the Express Checkout button.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
            ExpressCheckoutStorable::OPTION_BUTTON_SIZE => [
                'title' => esc_html_x('Button Size', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'select',
                'options' => [
                    'small' => esc_html_x('Small', 'gateway-setting', 'woo-paypalplus'),
                    'medium' => esc_html_x('Medium', 'gateway-setting', 'woo-paypalplus'),
                    'large' => esc_html_x('Large', 'gateway-setting', 'woo-paypalplus'),
                    'responsive' => esc_html_x('Responsive', 'gateway-setting', 'woo-paypalplus'),
                ],
                'default' => self::DEFAULT_BUTTON_SIZE,
                'description' => esc_html_x(
                    'Choose the size of the Express Checkout button, we suggest to use always responsive for best fit the UI of themes.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
            ExpressCheckoutStorable::OPTION_BUTTON_LABEL => [
                'title' => esc_html_x('Button Label', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'select',
                'options' => [
                    'pay' => esc_html_x('Pay', 'gateway-setting', 'woo-paypalplus'),
                    'paypal' => esc_html_x('PayPal', 'gateway-setting', 'woo-paypalplus'),
                ],
                'default' => self::DEFAULT_BUTTON_LABEL,
                'description' => esc_html_x(
                    'Choose the label to associate to the Express Checkout button.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
        ];
    }
}
