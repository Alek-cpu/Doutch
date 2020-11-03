<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 04.11.16
 * Time: 14:09
 */

namespace WCPayPalPlus\WC;

use Inpsyde\Lib\PayPal\Api\InputFields;
use Inpsyde\Lib\PayPal\Api\Presentation;
use Inpsyde\Lib\PayPal\Api\WebProfile;
use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\PayPal\Rest\ApiContext;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;

/**
 * Class WCWebExperienceProfile
 *
 * @package WCPayPalPlus\WC
 */
class WCWebExperienceProfile
{
    /**
     * PayPal SDK Api Context object.
     *
     * @var ApiContext
     */
    private $api_context;

    /**
     * Profile configuration.
     *
     * @var array
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * WCWebExperienceProfile constructor.
     *
     * @param array $config Profile configuration.
     * @param ApiContext $api_context PayPal SDK Api Context object.
     * @param Logger $logger
     */
    public function __construct(array $config, ApiContext $api_context, Logger $logger)
    {
        $this->api_context = $api_context;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Save profile data
     *
     * @return string
     */
    public function save_profile()
    {
        return $this->update_profile($this->get_local_id());
    }

    /**
     * Updates a web profile. Creates a new one if none is specified or found
     *
     * @param bool $local_id The profile id.
     *
     * @return string
     */
    private function update_profile($local_id = false)
    {
        if ($local_id) {
            $web_profile = $this->get_existing_profile($local_id);

            /**
             * Something went wrong fetching the existing profile.
             * It could have been deleted externally or the Credentials have changed,
             */
            if (is_null($web_profile)) {
                $web_profile = new WebProfile();
                $local_id = false;
            }
        } else {
            $web_profile = new WebProfile();
        }
        $brand_name = '';
        if (!empty($this->config['brand_name'])) {
            $brand_name = $this->config['brand_name'];
        }

        $web_profile
            ->setName(substr($brand_name . uniqid(), 0, 50))
            ->setInputFields($this->get_input_fields())
            ->setPresentation($this->get_presentation());

        $new_id = null;
        try {
            if ($local_id && $web_profile->update($this->api_context)) {
                $new_id = $local_id;
            } else {
                $response = $web_profile->create($this->api_context);
                $new_id = $response->getId();
            }
        } catch (PayPalConnectionException $exc) {
            $this->logger->error($exc->getData());
        }

        return $new_id;
    }

    /**
     * Fetches an existing profile
     *
     * @param string $profile_id The profile ID.
     *
     * @return null|WebProfile
     */
    public function get_existing_profile($profile_id)
    {
        $web_profile = null;
        try {
            $web_profile = WebProfile::get($profile_id, $this->api_context);
        } catch (PayPalConnectionException $exc) {
            $this->logger->error($exc->getData());
        }

        return $web_profile;
    }

    /**
     * Returns a configured InputFields object
     *
     * @return InputFields
     */
    private function get_input_fields()
    {
        $input_fields = new InputFields();

        $no_shipping = (isset($this->config['no_shipping'])) ? intval($this->config['no_shipping']) : 0;

        $input_fields->setNoShipping($no_shipping)
            ->setAddressOverride(1);

        return $input_fields;
    }

    /**
     * Creates and returns a Presentation object
     *
     * @return Presentation
     */
    private function get_presentation()
    {
        $presentation = new Presentation();

        if (!empty($this->config['checkout_logo'])) {
            $presentation->setLogoImage($this->config['checkout_logo']);
        }
        if (!empty($this->config['brand_name'])) {
            $presentation->setBrandName($this->config['brand_name']);
        }
        if (!empty($this->config['country'])) {
            $presentation->setLocaleCode($this->config['country']);
        }

        return $presentation;
    }

    /**
     * Returns the local profile ID
     *
     * @return string
     */
    private function get_local_id()
    {
        return isset($this->config['local_id']) ? $this->config['local_id'] : null;
    }
}
