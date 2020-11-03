<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Admin\Notice;

/**
 * Interface Noticeable
 * @package WCPayPalPlus\Admin
 */
interface Noticeable
{
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';
    const SUCCESS = 'success';

    /**
     * The Admin Notice Type
     *
     * @return string
     */
    public function type();

    /**
     * The Admin Notice Message
     *
     * @return string
     */
    public function message();

    /**
     * Check if the Notice is Dismissable or not
     *
     * @return bool
     */
    public function isDismissable();

    /**
     * Identifier of the Notice
     *
     * @return string
     */
    public function id();
}
