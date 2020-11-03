<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\WC;

use WCPayPalPlus\Api\ErrorData\Error;
use WCPayPalPlus\Api\ErrorData\Message;
use WCPayPalPlus\Session\Session;
use WCPayPalPlus\Setting\Storable;

/**
 * Class CheckoutDropper
 * @package WCPayPalPlus\WC
 */
class CheckoutDropper
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var Storable
     */
    private $settingRepository;

    /**
     * CheckoutDropper constructor.
     * @param Session $session
     * @param Storable $settingRepository
     */
    public function __construct(Session $session, Storable $settingRepository)
    {
        $this->session = $session;
        $this->settingRepository = $settingRepository;
    }

    /**
     * Abort Checkout because of PayPal Error
     *
     * @param Error $apiError
     */
    public function abortSessionBecauseOfApiError(Error $apiError)
    {
        $apiErrorMessage = Message::fromError($apiError);
        $this->abortSessionWithReason($apiErrorMessage());
    }

    /**
     * Abort Checkout with a message and Redirect User
     *
     * @param $message
     */
    public function abortSessionWithReason($message)
    {
        assert(is_string($message));

        wc_add_notice($message, 'error');
        $this->abortSession();
    }

    /**
     * Abort Checkout and Redirect User
     */
    public function abortSession()
    {
        $this->abort();
        wp_safe_redirect($this->url());
        exit;
    }

    /**
     * Abort Checkout
     */
    public function abort()
    {
        $this->session->clean();
    }

    /**
     * @return string
     */
    private function url()
    {
        return $this->settingRepository->cancelUrl();
    }
}
