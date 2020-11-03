<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Admin\Notice;

use WCPayPalPlus\Request\Request;

/**
 * Class AjaxDismisser
 * @package WCPayPalPlus\Admin\Notice
 */
class AjaxDismisser
{
    const AJAX_NONCE_ACTION = 'dismissable_admin_notice';
    const CAPABILITY = 'manage_options';
    const NOTICE_ID_NAME = 'noticeId';

    /**
     * @var Controller
     */
    private $controller;

    /**
     * @var Request
     */
    private $request;

    /**
     * AjaxDismisser constructor.
     * @param Controller $controller
     * @param Request $request
     */
    public function __construct(Controller $controller, Request $request)
    {
        $this->controller = $controller;
        $this->request = $request;
    }

    /**
     * Handle the Dismiss Request
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->isValidRequest()) {
            return;
        }

        $noticeId = (string)$this->request->get(self::NOTICE_ID_NAME, FILTER_SANITIZE_STRING);
        $noticeId and $this->controller->dismiss($noticeId);
    }

    /**
     * Check if Request is Valid
     *
     * @return bool
     */
    private function isValidRequest()
    {
        $isValid = check_ajax_referer(self::AJAX_NONCE_ACTION, false, false)
            && current_user_can(self::CAPABILITY);

        return $isValid;
    }
}
