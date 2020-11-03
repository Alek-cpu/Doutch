<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Payment;

use WCPayPalPlus\Session\Session;

/**
 * Class PaymentSessionDestructor
 * @package WCPayPalPlus\Payment
 */
class PaymentSessionDestructor
{
    /**
     * @var Session
     */
    private $session;

    /**
     * PaymentSessionDestructor constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Destroy Payment Session Because Payment Id is Invalid
     *
     * @return void
     */
    public function becauseInvalidPaymentId()
    {
        $this->session->clean();
        wc_add_notice(
            __(
                'Invalid Payment Id. This could be due to an expired session. Please start a new payment process. If the problem persist please contact us.',
                'woo-paypalplus'
            ),
            'error'
        );
    }
}
