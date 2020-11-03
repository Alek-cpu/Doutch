<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Refund;

use WCPayPalPlus\Utils\PriceFormatterTrait;
use WCPayPalPlus\WC\RequestSuccessHandler;
use WC_Order;

/**
 * Class RefundSuccess
 *
 * @package WCPayPalPlus\Refund
 */
class RefundSuccess implements RequestSuccessHandler
{
    use PriceFormatterTrait;

    /**
     * WooCommerce Order object.
     *
     * @var \WC_Order
     */
    private $order;

    /**
     * PayPal transaction ID.
     *
     * @var string
     */
    private $transaction_id;

    /**
     * @var string
     */
    private $reason;

    /**
     * RefundSuccess constructor.
     *
     * @param WC_Order $order WooCommerce Order object.
     * @param string $transaction_id PayPal transaction ID.
     * @param string $reason Refund reason.
     */
    public function __construct(WC_Order $order, $transaction_id, $reason)
    {
        $this->order = $order;
        $this->transaction_id = $transaction_id;
        $this->reason = $reason;
    }

    /**
     * Handle the successful request.
     *
     * @return void
     */
    public function execute()
    {
        $this->order->add_order_note(
            esc_html__('Refund Transaction ID:', 'woo-paypalplus') . $this->transaction_id
        );
        $this->reason and $this->order->add_order_note(
            esc_html__('Reason for Refund :', 'woo-paypalplus') . $this->reason
        );
    }
}
