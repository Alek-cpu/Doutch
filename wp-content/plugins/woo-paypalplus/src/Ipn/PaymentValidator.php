<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Ipn;

use WC_Order;
use WCPayPalPlus\Utils\PriceFormatterTrait;
use WCPayPalPlus\Request\Request;

/**
 * Class PaymentValidator
 *
 * @package WCPayPalPlus\Ipn
 */
class PaymentValidator
{
    use PriceFormatterTrait;

    const TRANSACTION_TYPE_DATA_KEY = 'txn_type';
    const CURRENCY_DATA_KEY = 'mc_currency';
    const AMOUNT_DATA_KEY = 'mc_gross';

    const ACCEPTED_TRANSACTIONS_TYPES = [
        'cart',
        'instant',
        'express_checkout',
        'web_accept',
        'masspay',
        'send_money',
    ];

    /**
     * The last error that occurred during validation
     *
     * @var string
     */
    private $last_error;

    /**
     * WooCommerce Order object
     *
     * @var \WC_Order
     */
    private $order;

    /**
     * @var Request
     */
    private $request;

    /**
     * PaymentValidator constructor.
     * @param Request $request
     * @param WC_Order $order
     */
    public function __construct(Request $request, WC_Order $order)
    {
        $this->request = $request;
        $this->order = $order;
    }

    /**
     * Runs all validation method
     *
     * @return bool
     */
    public function is_valid_payment()
    {
        $transactionType = $this->request->get(
            self::TRANSACTION_TYPE_DATA_KEY,
            FILTER_SANITIZE_STRING
        );
        $currency = $this->request->get(
            self::CURRENCY_DATA_KEY,
            FILTER_SANITIZE_STRING
        );
        // Amount is better to be considered as a string because of decimal, thousand separators.
        $amount = $this->request->get(
            self::AMOUNT_DATA_KEY,
            FILTER_SANITIZE_STRING
        );

        return ($this->validate_transaction_type($transactionType)
            && $this->validate_currency($currency)
            && $this->validate_payment_amount($amount));
    }

    /**
     * Check for a valid transaction type.
     *
     * @param string $transaction_type The transaction type to test against.
     *
     * @return bool
     */
    private function validate_transaction_type($transaction_type)
    {
        if (!in_array(strtolower($transaction_type), self::ACCEPTED_TRANSACTIONS_TYPES, true)) {
            $this->last_error = sprintf(
                __(
                    'Validation error: Invalid transaction type "%s".',
                    'woo-paypalplus'
                ),
                $transaction_type
            );

            return false;
        }

        return true;
    }

    /**
     * Check currency from IPN matches the order.
     *
     * @param string $currency The currency to test against.
     *
     * @return bool
     */
    private function validate_currency($currency)
    {
        $wc_currency = $this->order->get_currency();
        if ($wc_currency !== $currency) {
            $this->last_error = sprintf(
                __(
                    'Validation error: PayPal currencies do not match (PayPal: %1$1s, WooCommerce: %2$2s).',
                    'woo-paypalplus'
                ),
                $currency,
                $wc_currency
            );

            return false;
        }

        return true;
    }

    /**
     * Check payment amount from IPN matches the order.
     *
     * @param int $amount The payment amount.
     *
     * @return bool
     */
    private function validate_payment_amount($amount)
    {
        $wc_total = $this->format($this->order->get_total());
        $pp_total = $this->format($amount);
        if ($pp_total !== $wc_total) {
            $this->last_error = sprintf(
                __(
                    'Validation error: PayPal payment amounts do not match (gross %1$1s, should be %2$2s).',
                    'woo-paypalplus'
                ),
                $amount,
                $wc_total
            );

            return false;
        }

        return true;
    }

    /**
     * Checks if we're dealing with a valid refund request.
     *
     * @return bool
     */
    public function is_valid_refund()
    {
        $amount = (string)$this->request->get(self::AMOUNT_DATA_KEY, FILTER_SANITIZE_STRING);
        $total = $this->sanitize_string_amount((string)$this->order->get_total());

        $paypalTotal = $amount * -1;
        $paypalTotal = $this->sanitize_string_amount((string)$paypalTotal);

        $total = $this->format($total);
        $paypalTotal = $this->format($paypalTotal);

        return ($paypalTotal === $total);
    }

    /**
     * @param string $amt
     * @return mixed
     */
    private function sanitize_string_amount($amt)
    {
        assert(is_string($amt));

        return str_replace(',', '.', $amt);
    }

    /**
     * Returns the last validation error
     *
     * @return string
     */
    public function get_last_error()
    {
        return $this->last_error;
    }
}
