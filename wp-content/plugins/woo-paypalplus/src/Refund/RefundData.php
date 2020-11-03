<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Refund;

use Inpsyde\Lib\PayPal\Api\Amount;
use Inpsyde\Lib\PayPal\Api\RefundRequest;
use Inpsyde\Lib\PayPal\Api\Sale;
use Inpsyde\Lib\PayPal\Rest\ApiContext;
use WC_Order;
use WCPayPalPlus\Utils\PriceFormatterTrait;

/**
 * Class RefundData
 *
 * Bridge between WooCommerce and PayPal objects.
 * Provides WooCommerce with the objects needed to perform a refund
 *
 * @package WCPayPalPlus\WC
 */
class RefundData
{
    use PriceFormatterTrait;

    /**
     * WooComcerce Order object.
     *
     * @var \WC_Order
     */
    private $order;

    /**
     * Refund amount.
     *
     * @var float
     */
    private $amount;
    /**
     * PayPal API Context object.
     *
     * @var ApiContext
     */
    private $context;
    /**
     * Refund reason.
     *
     * @var string
     */
    private $reason;

    /**
     * RefundData constructor.
     *
     * @param WC_Order $order WooCommerce Order object.
     * @param float $amount Refund amount.
     * @param string $reason Refund reason.
     * @param ApiContext $context PayPal API Context object.
     */
    public function __construct(WC_Order $order, $amount, $reason, ApiContext $context)
    {
        $this->order = $order;
        $this->amount = (float)$amount;
        $this->context = $context;
        $this->reason = $reason;
    }

    /**
     * Returns the refund amount.
     *
     * @return float
     */
    public function get_amount()
    {
        return $this->amount;
    }

    /**
     * Returns the refund reason.
     *
     * @return string
     */
    public function get_reason()
    {
        return $this->reason;
    }

    /**
     * Returns the Sale object.
     *
     * @return Sale
     */
    public function get_sale()
    {
        return Sale::get($this->order->get_transaction_id(), $this->context);
    }

    /**
     * Returns a configured RefundRequest object.
     *
     * @return RefundRequest
     * @throws \InvalidArgumentException
     */
    public function get_refund()
    {
        $total = $this->format($this->amount);

        $amount = new Amount();
        $amount
            ->setCurrency($this->order->get_currency())
            ->setTotal($total);

        $refund = new RefundRequest();
        $refund->setAmount($amount);

        return $refund;
    }

    /**
     * Returns the success handler object
     *
     * @param string $transaction_id PayPal transaction ID.
     *
     * @return RefundSuccess
     */
    public function get_success_handler($transaction_id)
    {
        return new RefundSuccess($this->order, $transaction_id, $this->reason);
    }
}
