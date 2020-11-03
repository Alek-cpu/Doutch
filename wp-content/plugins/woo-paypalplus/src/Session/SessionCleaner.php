<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Session;

use WCPayPalPlus\ExpressCheckoutGateway\Gateway;
use WCPayPalPlus\Gateway\CurrentPaymentMethod;

/**
 * Class SessionReset
 * @package WCPayPalPlus\Session
 */
class SessionCleaner
{
    /**
     * @var CurrentPaymentMethod
     */
    private $currentPaymentMethod;

    /**
     * @var Session
     */
    private $session;

    /**
     * SessionCleaner constructor.
     * @param Session $session
     * @param CurrentPaymentMethod $currentPaymentMethod
     */
    public function __construct(Session $session, CurrentPaymentMethod $currentPaymentMethod)
    {
        $this->currentPaymentMethod = $currentPaymentMethod;
        $this->session = $session;
    }

    /**
     * Clean Session when User get out By the Express Checkout Page
     *
     * The session is cleared only if the refer page is including the checkout url and only if
     * the current page isn't the checkout it self or the checkout pay page.
     *
     * Users will then be able to choose a different payment method if they want.
     *
     * @return void
     */
    public function cleanByReferer()
    {
        $refer = wp_get_referer();
        $checkoutPageUrl = wc_get_checkout_url();

        if (is_checkout() || is_checkout_pay_page()) {
            return;
        }

        if (strpos($refer, $checkoutPageUrl) !== false
            && Gateway::GATEWAY_ID === $this->currentPaymentMethod->payment()
        ) {
            $this->session->clean();
        }
    }

    /**
     * Clean Chosen Payment Method
     *
     * @return void
     */
    public function cleanChosenPaymentMethod()
    {
        $this->session->delete(Session::CHOSEN_PAYMENT_METHOD);
    }
}
