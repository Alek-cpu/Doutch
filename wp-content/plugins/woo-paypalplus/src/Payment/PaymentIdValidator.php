<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Payment;

use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\ApiContextFactory;

/**
 * Class PaymentIdValidator
 * @package WCPayPalPlus\Payment
 */
class PaymentIdValidator
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * PaymentIdValidator constructor.
     * @param Logger $log
     */
    public function __construct(Logger $log)
    {
        $this->logger = $log;
    }

    /**
     * Check if Payment Id exists in PayPal
     *
     * @param string $paymentId
     * @return bool
     */
    public function isPaymentIdValid($paymentId)
    {
        assert(is_string($paymentId));

        if (strncmp($paymentId, 'PAYID-', 6) !== 0) {
            return false;
        }

        try {
            Payment::get($paymentId, ApiContextFactory::getFromConfiguration());
        } catch (PayPalConnectionException $exc) {
            $this->logger->error("Validate Payment ID: {$paymentId}: Failed" . $exc->getData());
            return false;
        }

        return true;
    }
}
