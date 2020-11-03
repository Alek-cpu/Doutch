<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Api\ErrorData;

/**
 * Class Message
 * @package WCPayPalPlus\Api\ErrorData
 */
class Message
{
    private $error;

    /**
     * Retrieve the Default PayPal Payment Error Message
     *
     * @param Error $error
     * @return Message
     */
    public static function fromError(Error $error)
    {
        return new self($error);
    }

    /**
     * Extract the message associated with the code
     *
     * @return string
     */
    public function __invoke()
    {
        $code = $this->error->code();

        switch ($code) {
            case Codes::ERROR_INVALID_RESOURCE_ID:
                $message = esc_html_x(
                    'Sorry, an internal problem prevent you to complete the payment, please contact our help support. Or try a different payment method.',
                    'api-error-code-message',
                    'woo-paypalplus'
                );
                break;
            case Codes::ERROR_INSTRUMENT_DECLINED:
                $message = esc_html_x(
                    'Sorry we cannot process your PayPal payment at the moment, please contact PayPal customer service.',
                    'api-error-code-message',
                    'woo-paypalplus'
                );
                break;
            case Codes::ERROR_INSUFFICIENT_FUNDS:
                $message = esc_html_x(
                    'Sorry we cannot process your PayPal payment because of insufficient funds, please contact PayPal customer service.',
                    'api-error-code-message',
                    'woo-paypalplus'
                );
                break;
            case Codes::ERROR_VALIDATION_ERROR:
                $message = esc_html_x(
                    'Sorry, there was a validation error while sending data to PayPal, please check your information.',
                    'api-error-code-message',
                    'woo-paypalplus'
                );
                break;
            default:
                $message = esc_html_x(
                    'Sorry an unexpected error occurred while processing your request, please contact PayPal customer service.',
                    'api-error-code-message',
                    'woo-paypalplus'
                );
                break;
        }

        return $message;
    }

    /**
     * Message constructor.
     * @param Error $error
     */
    private function __construct(Error $error)
    {
        $this->error = $error;
    }
}
