<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\PlusGateway;

use \WCPayPalPlus\PlusGateway\Gateway as PlusGateway;
use WCPayPalPlus\ExpressCheckoutGateway\Gateway as ExpressCheckoutGateway;
use WCPayPalPlus\Gateway\CurrentPaymentMethod;
use WCPayPalPlus\Session\Session;
use WCPayPalPlus\Setting;
use OutOfBoundsException;

/**
 * Class DefaultGatewayOverride
 *
 * Overrides the default Payment Gateway ONCE per user session.
 *
 * Hence, it should never override user input.
 */
class DefaultGatewayOverride
{
    const INPUT_PAYMENT_METHOD = 'payment_method';

    /**
     * @var Setting\PlusStorable
     */
    private $repository;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CurrentPaymentMethod
     */
    private $currentPaymentMethod;

    /**
     * DefaultGatewayOverride constructor.
     * @param Setting\PlusStorable $repository
     * @param Session $session
     * @param CurrentPaymentMethod $currentPaymentMethod
     */
    public function __construct(
        Setting\PlusStorable $repository,
        Session $session,
        CurrentPaymentMethod $currentPaymentMethod
    ) {

        $this->repository = $repository;
        $this->session = $session;
        $this->currentPaymentMethod = $currentPaymentMethod;
    }

    /**
     *
     * @throws OutOfBoundsException
     */
    public function maybeOverride()
    {
        if (!$this->isValidRequest()
            || $this->repository->isDisableGatewayOverrideEnabled()
            || $this->currentPaymentMethod->payment() === ExpressCheckoutGateway::GATEWAY_ID
        ) {
            return;
        }

        $this->setChosenPaymentMethod(PlusGateway::GATEWAY_ID);
        $this->setSessionFlag();
    }

    /**
     * Check the current request
     *
     * @return bool
     */
    public function isValidRequest()
    {
        $paymentMethod = (string)filter_input(
            INPUT_POST,
            self::INPUT_PAYMENT_METHOD,
            FILTER_SANITIZE_STRING
        );

        if ($paymentMethod
            || !is_checkout()
            || $this->getSessionFlag()
            || is_ajax()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve our private session flag
     *
     * @return array|string
     */
    private function getSessionFlag()
    {
        return $this->session->get(Session::SESSION_CHECK_KEY);
    }

    /**
     * Set the gateway override
     *
     * @param string $paymentMethod
     * @throws OutOfBoundsException
     */
    private function setChosenPaymentMethod($paymentMethod)
    {
        assert(is_string($paymentMethod));

        $this->session->set(Session::CHOSEN_PAYMENT_METHOD, $paymentMethod);
    }

    /**
     * Set our private session flag
     *
     * @throws OutOfBoundsException
     */
    private function setSessionFlag()
    {
        $this->session->set(Session::SESSION_CHECK_KEY, Session::SESSION_CHECK_ACTIVATE);
    }
}
