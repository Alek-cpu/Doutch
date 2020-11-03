<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Order;

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Ipn\PaymentValidator;
use WCPayPalPlus\Request\Request;
use WC_Order;
use WCPayPalPlus\Setting\Storable;
use WooCommerce;

/**
 * Class OrderUpdater
 *
 * @package WCPayPalPlus\Ipn
 */
class OrderUpdater
{
    /**
     * WooCommerce Order object
     *
     * @var WC_Order
     */
    private $order;

    /**
     * Payment Validation handler
     *
     * @var PaymentValidator
     */
    private $validator;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var OrderStatuses
     */
    private $orderStatuses;

    /**
     * @var Storable
     */
    private $settingRepository;

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * OrderUpdater constructor.
     * @param WooCommerce $wooCommerce
     * @param WC_Order $order
     * @param Storable $settingRepository
     * @param Request $request
     * @param PaymentValidator $validator
     * @param OrderStatuses $orderStatuses
     * @param Logger $logger
     */
    public function __construct(
        WooCommerce $wooCommerce,
        WC_Order $order,
        Storable $settingRepository,
        Request $request,
        PaymentValidator $validator,
        OrderStatuses $orderStatuses,
        Logger $logger
    ) {

        $this->wooCommerce = $wooCommerce;
        $this->order = $order;
        $this->settingRepository = $settingRepository;
        $this->request = $request;
        $this->validator = $validator;
        $this->orderStatuses = $orderStatuses;
        $this->logger = $logger;
    }

    /**
     * Handle a pending payment.
     *
     * @return bool
     */
    public function payment_status_pending()
    {
        return $this->payment_status_completed();
    }

    /**
     * Handle a completed payment.
     *
     * @return bool
     */
    public function payment_status_completed()
    {
        if ($this->order->has_status(OrderStatuses::ORDER_STATUS_COMPLETED)) {
            $this->logger->error('IPN Error. Payment already completed.');
            return true;
        }

        if (!$this->validator->is_valid_payment()) {
            $lastError = $this->validator->get_last_error();
            $this->order->update_status(OrderStatuses::ORDER_STATUS_ON_HOLD, $lastError);
            $this->logger->error("IPN Error. Payment validation failed: {$lastError}");
            return false;
        }

        $this->save_paypal_meta_data();

        $paymentStatus = $this->request->get(Request::KEY_PAYMENT_STATUS, FILTER_SANITIZE_STRING);
        $isOrderStatusCompleted = $this->orderStatuses->orderStatusIs(
            $paymentStatus,
            OrderStatuses::ORDER_STATUS_COMPLETED
        );

        if ($isOrderStatusCompleted) {
            $transaction_id = $this->request->get(
                Request::KEY_TXN_ID,
                FILTER_SANITIZE_STRING
            );
            $note = __('IPN payment completed', 'woo-paypalplus');
            $fee = $this->request->get(Request::KEY_MC_FEE, FILTER_SANITIZE_STRING);

            $this->payment_complete($transaction_id, $note);

            if (!empty($fee)) {
                update_post_meta($this->order->get_id(), 'PayPal Transaction Fee', $fee);
            }

            $this->logger->info('Payment completed successfully');
            return true;
        }

        $this->payment_on_hold(
            sprintf(
                __('Payment pending: %s', 'woo-paypalplus'),
                $this->request->get(Request::KEY_PENDING_REASON, FILTER_SANITIZE_STRING)
            )
        );

        $this->logger->info('Payment put on hold');
        return true;
    }

    /**
     * Save relevant data from the IPN to the order.
     */
    private function save_paypal_meta_data()
    {
        $postMeta = [
            'payer_email' => 'Payer PayPal address',
            'first_name' => 'Payer first name',
            'last_name' => 'Payer last name',
            'payment_type' => 'Payment type',
        ];

        foreach ($postMeta as $key => $name) {
            $value = wc_clean($this->request->get($key, FILTER_DEFAULT));
            $value and update_post_meta($this->order->get_id(), $name, $value);
        }
    }

    /**
     * Complete order, add transaction ID and note.
     *
     * @param string $transaction_id The Transaction ID.
     * @param string $note Payment note.
     */
    private function payment_complete($transaction_id, $note)
    {
        $this->order->add_order_note($note);
        $this->order->payment_complete($transaction_id);
    }

    /**
     * Hold order and add note.
     *
     * @param string $reason Reason for refunding.
     */
    private function payment_on_hold($reason)
    {
        $this->order->update_status(OrderStatuses::ORDER_STATUS_ON_HOLD, $reason);
        wc_reduce_stock_levels($this->order->get_id());
        $this->wooCommerce->cart->empty_cart();
    }

    /**
     * Handle a denied payment.
     *
     * @return bool
     */
    public function payment_status_denied()
    {
        return $this->payment_status_failed();
    }

    /**
     * Handle a failed payment.
     *
     * @return bool
     */
    public function payment_status_failed()
    {
        return $this->order->update_status(
            'failed',
            sprintf(
                esc_html__('Payment %s via IPN.', 'woo-paypalplus'),
                $this->request->get(Request::KEY_PAYMENT_STATUS, FILTER_SANITIZE_STRING)
            )
        );
    }

    /**
     * Handle an expired payment.
     *
     * @return bool
     */
    public function payment_status_expired()
    {
        return $this->payment_status_failed();
    }

    /**
     * Handle a voided payment.
     *
     * @return bool
     */
    public function payment_status_voided()
    {
        return $this->payment_status_failed();
    }

    /**
     * Handle a refunded order.
     */
    public function payment_status_refunded()
    {
        if ($this->validator->is_valid_refund()) {
            $this->order->add_order_note(
                sprintf(
                    esc_html__('Payment %s via IPN.', 'woo-paypalplus'),
                    $this->request->get(Request::KEY_PAYMENT_STATUS, FILTER_SANITIZE_STRING)
                )
            );
            do_action(
                'wc_paypal_plus__ipn_payment_update',
                OrderStatuses::ORDER_STATUS_REFUNDED,
                $this->settingRepository
            );
        }
    }

    /**
     * Handle a payment reversal.
     */
    public function payment_status_reversed()
    {
        $this->order->update_status(
            OrderStatuses::ORDER_STATUS_ON_HOLD,
            sprintf(
                esc_html__('Payment %s via IPN.', 'woo-paypalplus'),
                $this->request->get(Request::KEY_PAYMENT_STATUS, FILTER_SANITIZE_STRING)
            )
        );

        do_action('wc_paypal_plus__ipn_payment_update', 'reversed', $this->settingRepository);
    }

    /**
     * Handle a cancelled reversal.
     */
    public function payment_status_canceled_reversal()
    {
        do_action(
            'wc_paypal_plus__ipn_payment_update',
            'canceled_reversal',
            $this->settingRepository
        );
    }
}
