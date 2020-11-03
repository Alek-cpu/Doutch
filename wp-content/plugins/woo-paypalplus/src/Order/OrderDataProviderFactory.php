<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Order;

use Exception;
use OutOfBoundsException;
use RuntimeException;
use UnexpectedValueException;
use WC_Order;
use WC_Order_Refund;
use WCPayPalPlus\Payment\CartData;
use WCPayPalPlus\Payment\OrderData;
use WCPayPalPlus\Session\Session;
use WooCommerce;

/**
 * Class OrderDataProviderFactory
 * @package WCPayPalPlus\Order
 */
class OrderDataProviderFactory
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * OrderDataFactory constructor.
     * @param OrderFactory $orderFactory
     * @param Session $session
     * @param WooCommerce $wooCommerce
     */
    public function __construct(
        OrderFactory $orderFactory,
        Session $session,
        WooCommerce $wooCommerce
    ) {

        $this->orderFactory = $orderFactory;
        $this->session = $session;
        $this->wooCommerce = $wooCommerce;
    }

    /**
     * @return OrderData|CartData
     */
    public function create()
    {
        try {
            $orderData = $this->retrieveOrderByRequest();
            $orderData = new OrderData($orderData);
        } catch (Exception $exc) {
            $orderData = new CartData($this->wooCommerce->cart);
        }

        return $orderData;
    }

    /**
     * @return WC_Order|WC_Order_Refund
     * @throws OrderFactoryException
     * @throws RuntimeException
     * @throws OutOfBoundsException
     * @throws UnexpectedValueException
     */
    private function retrieveOrderByRequest()
    {
        $key = filter_input(INPUT_GET, 'key');

        if (!$key) {
            throw new RuntimeException('Key for order not provided by the current request.');
        }

        $order = $this->orderFactory->createByOrderKey($key);

        // TODO Understand why the ppp_order_id is set twice.
        $this->session->set(Session::ORDER_ID, $order->get_id());

        return $order;
    }
}
