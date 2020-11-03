<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Order;

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Ipn\PaymentValidator;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Setting\Storable;
use WooCommerce;
use RuntimeException;
use UnexpectedValueException;

/**
 * Class OrderUpdaterFactory
 * @package WCPayPalPlus\Ipn
 */
class OrderUpdaterFactory
{
    /**
     * @var OrderStatuses
     */
    private $orderStatuses;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Request
     */
    private $request;

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
     * OrderUpdaterFactory constructor.
     * @param WooCommerce $wooCommerce
     * @param OrderStatuses $orderStatuses
     * @param OrderFactory $orderFactory
     * @param Request $request
     * @param Storable $settingRepository
     * @param Logger $logger
     */
    public function __construct(
        WooCommerce $wooCommerce,
        OrderStatuses $orderStatuses,
        OrderFactory $orderFactory,
        Request $request,
        Storable $settingRepository,
        Logger $logger
    ) {

        $this->wooCommerce = $wooCommerce;
        $this->orderStatuses = $orderStatuses;
        $this->orderFactory = $orderFactory;
        $this->request = $request;
        $this->settingRepository = $settingRepository;
        $this->logger = $logger;
    }

    /**
     * @return OrderUpdater
     * @throws RuntimeException
     * @throws UnexpectedValueException
     */
    public function create()
    {
        $orderKey = $this->request->get(Request::KEY_CUSTOM, FILTER_SANITIZE_STRING);
        $order = $this->orderFactory->createByOrderKey($orderKey);
        $paymentValidator = new PaymentValidator($this->request, $order);

        return new OrderUpdater(
            $this->wooCommerce,
            $order,
            $this->settingRepository,
            $this->request,
            $paymentValidator,
            $this->orderStatuses,
            $this->logger
        );
    }
}
