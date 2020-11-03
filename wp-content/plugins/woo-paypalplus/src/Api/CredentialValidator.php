<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Api;

use Exception;
use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Rest\ApiContext;

/**
 * Class CredentialVerification
 *
 * @package WCPayPalPlus\WC
 */
class CredentialValidator
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * CredentialValidator constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Verify the API Credentials by making a dummy API call with them.
     *
     * @param ApiContext $context
     * @return CredentialValidationResponse
     */
    public function ensureCredential(ApiContext $context)
    {
        $responseMessage = esc_html_x('Credential are Valid', 'credential', 'woo-paypalplus');
        $isResponseValid = true;
        $credential = $context->getCredential();

        if (!$credential->getClientId() || !$credential->getClientSecret()) {
            return new CredentialValidationResponse(
                false,
                esc_html_x('Credential are Empty', 'credential', 'woo-paypalplus')
            );
        }

        try {
            $params = ['count' => 1];
            Payment::all($params, $context);
        } catch (PayPalConnectionException $exc) {
            $responseMessage = $exc->getData();
            $isResponseValid = false;
        } catch (Exception $exc) {
            $responseMessage = $exc->getMessage();
            $isResponseValid = false;
        }

        if (!$isResponseValid) {
            $this->logger->error($responseMessage);
        }

        return new CredentialValidationResponse($isResponseValid, $responseMessage);
    }
}
