<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Refund;

use Inpsyde\Lib\PayPal\Rest\ApiContext;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Order\OrderStatuses;
use WC_Order_Refund;

/**
 * Class RefundFactory
 * @package WCPayPalPlus\Refund
 */
class RefundFactory
{
    /**
     * @var OrderStatuses
     */
    private $orderStatuses;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * RefundFactory constructor.
     * @param OrderStatuses $orderStatuses
     * @param Logger $logger
     */
    public function __construct(
        OrderStatuses $orderStatuses,
        Logger $logger
    ) {

        $this->orderStatuses = $orderStatuses;
        $this->logger = $logger;
    }

    /**
     * Create a new Refund Order
     *
     * @param WC_Order_Refund $order
     * @param string $amount
     * @param string $reason
     * @param ApiContext $apiContext
     * @return Refunder
     */
    public function create($order, $amount, $reason, ApiContext $apiContext)
    {
        assert(is_string($amount));
        assert(is_string($reason));

        $refundData = new RefundData(
            $order,
            $amount,
            $reason,
            $apiContext
        );

        return new Refunder(
            $refundData,
            $apiContext,
            $this->orderStatuses,
            $this->logger
        );
    }
}
