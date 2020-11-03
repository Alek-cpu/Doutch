<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Setting;

use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Api\Credential;
use WCPayPalPlus\Api\CredentialValidator;
use WCPayPalPlus\WC\WCWebExperienceProfile;

/**
 * Trait GatewaySharedSettingsTrait
 * @property CredentialValidator $credentialValidator
 * @package WCPayPalPlus\Setting
 */
trait GatewaySharedSettingsTrait
{
    /**
     * Initialize Settings and Include the Shared ones
     *
     * The method will directly set the property `settings` of the current Gateway.
     * The method override the existing settings for the current gateway.
     *
     * @return void
     */
    public function init_settings()
    {
        /** @noinspection PhpUndefinedClassInspection */
        parent::init_settings();

        $sharedOptions = get_option(SharedPersistor::OPTION_NAME, []) ?: [];
        $this->settings = array_merge($this->settings, $sharedOptions);
    }

    /**
     * @return void
     */
    public function process_admin_options()
    {
        $postData = $this->get_post_data();
        $credentials = $this->credentialByRequest();
        $apiContext = ApiContextFactory::getFromCredentials($credentials);
        $credentialValidationResponse = $this->credentialValidator->ensureCredential($apiContext);

        switch ($credentialValidationResponse->isValidStatus()) {
            case true:
                $config = [
                    'checkout_logo' => $this->get_option('checkout_logo'),
                    'local_id' => $this->experienceProfileId(),
                    'brand_name' => $this->get_option('brand_name'),
                    'country' => $this->get_option('country'),
                ];
                $webProfile = new WCWebExperienceProfile($config, $apiContext, $this->logger);
                $optionKey = $this->experienceProfileKey();
                $postData[$this->get_field_key($optionKey)] = $webProfile->save_profile();
                break;
            case false:
                unset($postData[$this->get_field_key('enabled')]);
                $this->enabled = Storable::OPTION_OFF;
                $this->add_error(sprintf(
                    esc_html_x(
                        'Your API credentials are either missing or invalid: %s',
                        'shared-settings',
                        'woo-paypalplus'
                    ),
                    $credentialValidationResponse->message()
                ));
                break;
        }

        $this->data = $postData;

        /** @noinspection PhpUndefinedClassInspection */
        parent::process_admin_options();

        /**
         * After Gateway Settings have been Updated
         *
         * @param array $settings
         */
        do_action(self::ACTION_AFTER_SETTINGS_UPDATE, $this->settings);
    }

    /**
     * Prefix key for settings.
     *
     * @param  string $key Field key.
     * @return string
     */
    public function get_field_key($key)
    {
        $sharedKeys = array_keys(SharedSettingsModel::SHARED_OPTIONS);
        $prefixed = $this->plugin_id . $this->id . '_' . $key;
        $isSharedKey = in_array($key, $sharedKeys, true);

        return $isSharedKey ? Storable::OPTION_PREFIX . $key : $prefixed;
    }

    /**
     * @param string $key
     * @param string|array|object $data
     * @return false|string
     */
    public function generate_html_html($key, $data)
    {
        $defaults = [
            'title' => '',
            'class' => '',
            'html' => '',
        ];

        $data = wp_parse_args($data, $defaults);

        ob_start();
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php echo wp_kses_post($data['title']); ?>
            </th>
            <td class="forminp <?= sanitize_html_class($data['class']) ?>">
                <?= wp_kses_post($data['html']) ?>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }

    /**
     * @return string
     */
    private function experienceProfileKey()
    {
        return $this->isSandboxed()
            ? Storable::OPTION_PROFILE_ID_SANDBOX_NAME
            : Storable::OPTION_PROFILE_ID_PRODUCTION_NAME;
    }

    /**
     * Retrieve a Credential Instance by the Current Request
     *
     * @return Credential
     */
    private function credentialByRequest()
    {
        $isSandboxed = $this->isSandboxed();

        $clientIdKey = $isSandboxed ? Storable::OPTION_CLIENT_ID_SANDBOX : Storable::OPTION_CLIENT_ID;
        $clientSecretKey = $isSandboxed ? Storable::OPTION_SECRET_ID_SANDBOX : Storable::OPTION_SECRET_ID;

        $clientIdKey = Storable::OPTION_PREFIX . $clientIdKey;
        $clientSecretKey = Storable::OPTION_PREFIX . $clientSecretKey;

        $clientId = (string)filter_input(INPUT_POST, $clientIdKey, FILTER_SANITIZE_STRING);
        $clientSecret = (string)filter_input(INPUT_POST, $clientSecretKey, FILTER_SANITIZE_STRING);

        return new Credential($clientId, $clientSecret);
    }
}
