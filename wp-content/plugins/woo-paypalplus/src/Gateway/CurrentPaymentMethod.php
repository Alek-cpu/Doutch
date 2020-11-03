<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Gateway;

use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Session\Session;

/**
 * Class CurrentPaymentMethod
 * @package WCPayPalPlus\Gateway
 */
class CurrentPaymentMethod
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var Request
     */
    private $request;

    /**
     * CurrentPaymentMethod constructor.
     * @param Session $session
     * @param Request $request
     */
    public function __construct(Session $session, Request $request)
    {
        $this->session = $session;
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function payment()
    {
        $paymentMethod = $this->session->get(Session::CHOSEN_PAYMENT_METHOD);
        $paymentMethod or $paymentMethod = $this->request->get(
            Request::KEY_PAYMENT_METHOD,
            FILTER_SANITIZE_STRING
        );

        return (string)$paymentMethod;
    }
}
