<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\PlusGateway;

use WCPayPalPlus\Setting\SettingsGatewayModel;
use WCPayPalPlus\Setting\SharedFieldsOptionsTrait;
use WCPayPalPlus\Setting\SharedSettingsModel;
use WC_Payment_Gateway;

/**
 * Class GatewaySettingsModel
 *
 * @package WCPayPalPlus\WC
 */
final class GatewaySettingsModel implements SettingsGatewayModel
{
    use SharedFieldsOptionsTrait;

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
            $this->generalSettings()
            + $this->sharedSettingsModel->credentials()
            + $this->sharedSettingsModel->webProfile($gateway)
            + $this->gatewaySettings()
            + $this->sharedSettingsModel->debugLog();

        return $settings;
    }

    /**
     * @return array
     */
    private function generalSettings()
    {
        return [
            'enabled' => [
                'title' => esc_html_x('Enable/Disable', 'gateway-settings', 'woo-paypalplus'),
                'type' => 'checkbox',
                'label' => esc_html_x('Enable PayPal PLUS', 'gateway-settings', 'woo-paypalplus'),
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
                'default' => esc_html_x(
                    'PayPal, Direct Debit, Credit Card and Invoice (if available)',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
            'description' => [
                'title' => esc_html_x('Description', 'gateway-setting', 'woo-paypalplus'),
                'type' => 'text',
                'description' => esc_html_x(
                    'This controls the payment gateway description the user sees during checkout.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'default' => esc_html_x(
                    'Please choose a payment method:',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
            ],
        ];
    }

    /**
     * @return array
     */
    private function gatewaySettings()
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
                    'Custom Cancelation URL',
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
            'legal_note' => [
                'title' => esc_html_x(
                    'Legal Note for PAY UPON INVOICE Payment',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'type' => 'textarea',
                'description' => esc_html__(
                    'legal note that will be added to the thank you page and emails.',
                    'woo-paypalplus'
                ),
                'default' => esc_html__(
                    'Dealer has ceeded the claim against you within the framework of an ongoing factoring contract to the PayPal (Europe) S.àr.l. et Cie, S.C.A.. Payments with a debt-free effect can only be paid to the PayPal (Europe) S.àr.l. et Cie, S.C.A.',
                    'woo-paypalplus'
                ),
                'desc_tip' => false,
            ],
            'pay_upon_invoice_instructions' => [
                'title' => esc_html_x(
                    'Pay upon Invoice Instructions',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'type' => 'textarea',
                'description' => esc_html_x(
                    'Pay upon Invoice Instructions that will be added to the thank you page and emails.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'default' => esc_html_x(
                    'Please transfer the complete amount to the bank account provided below.',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'desc_tip' => false,
            ],
            'disable_gateway_override' => [
                'title' => esc_html_x(
                    'Disable default gateway override',
                    'gateway-setting',
                    'woo-paypalplus'
                ),
                'type' => 'checkbox',
                'label' => esc_html_x('Disable', 'gateway-setting', 'woo-paypalplus'),
                'default' => 'no',
                'description' => esc_html_x(
                    'PayPal PLUS will be selected as default payment gateway regardless of its position in the list of enabled gateways. You can turn off this behaviour here.',
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
}
