<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Payment;

use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Order\OrderDataProviderFactory;
use WCPayPalPlus\Setting\Storable;

/**
 * Class PaymentCreatorFactory
 * @package WCPayPalPlus\Payment
 */
class PaymentCreatorFactory
{
    /**
     * @var OrderDataProviderFactory
     */
    private $orderDataFactory;

    /**
     * PaymentCreatorFactory constructor.
     * @param OrderDataProviderFactory $orderDataFactory
     */
    public function __construct(OrderDataProviderFactory $orderDataFactory)
    {
        $this->orderDataFactory = $orderDataFactory;
    }

    /**
     * @param Storable $settings
     * @param $returnUrl
     * @param $notifyUrl
     * @return PaymentCreator
     */
    public function create(Storable $settings, $returnUrl, $notifyUrl)
    {
        assert(is_string($returnUrl));
        assert(is_string($notifyUrl));

        $orderData = $this->orderDataFactory->create();
        $cancelUrl = $settings->cancelUrl();
        $experienceProfile = $settings->experienceProfileId();

        $paymentData = new PaymentData(
            $returnUrl,
            $cancelUrl,
            $notifyUrl,
            $experienceProfile,
            ApiContextFactory::getFromConfiguration()
        );

        return new PaymentCreator($paymentData, $orderData);
    }
}
