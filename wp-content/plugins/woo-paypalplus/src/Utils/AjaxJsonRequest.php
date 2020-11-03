<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Utils;

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;

/**
 * Class JsonParser
 * @package WCPayPalPlus
 */
class AjaxJsonRequest
{
    const DEFAULT_LOG_MESSAGE = 'Unknown error message.';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * AjaxJsonRequest constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Send a JSON response back to an Ajax request, indicating success.
     *
     * @param array $data
     * @param int $status
     */
    public function sendJsonSuccess(array $data, $status = null)
    {
        wp_send_json_success($data, $status);
    }

    /**
     * Send a JSON response back to an Ajax request, indicating failure.
     *
     * @param array $data
     * @param int $status
     */
    public function sendJsonError(array $data, $status = null)
    {
        $message = isset($data['exception']) ? $data['exception'] : '';
        $message or $message = isset($data['message']) ? $data['message'] : self::DEFAULT_LOG_MESSAGE;

        unset($data['exception']);

        $this->logger->error($message, [$data, $status]);

        wp_send_json_error($data, $status);
    }
}
