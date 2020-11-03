<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use Brain\Nonces\NonceContextInterface;
use Brain\Nonces\NonceInterface;
use WCPayPalPlus\Utils\AjaxJsonRequest;
use WCPayPalPlus\Request\Request;

/**
 * Class AjaxHandler
 * @package WCPayPalPlus\ExpressCheckout
 */
class AjaxHandler
{
    const ACTION = 'paypal_express_checkout_request';
    const VALID_CONTEXTS = [
        'cart',
        'product',
    ];

    /**
     * @var NonceInterface
     */
    private $nonce;

    /**
     * @var NonceContextInterface
     */
    private $nonceContext;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var AjaxJsonRequest
     */
    private $ajaxJsonRequest;

    /**
     * AjaxHandler constructor.
     * @param NonceInterface $nonce
     * @param NonceContextInterface $nonceContext
     * @param Dispatcher $dispatcher
     * @param Request $request
     * @param AjaxJsonRequest $ajaxJsonRequest
     */
    public function __construct(
        NonceInterface $nonce,
        NonceContextInterface $nonceContext,
        Dispatcher $dispatcher,
        Request $request,
        AjaxJsonRequest $ajaxJsonRequest
    ) {

        $this->nonce = $nonce;
        $this->nonceContext = $nonceContext;
        $this->dispatcher = $dispatcher;
        $this->request = $request;
        $this->ajaxJsonRequest = $ajaxJsonRequest;
    }

    /**
     * Handle the request and dispatch the action based on the `context`
     *
     * @return void
     */
    public function handle()
    {
        $this->validateRequest() or $this->sendErrorResponse();

        $context = $this->context();
        $task = $this->task();
        $requestData = $this->request->all();

        if (!$context) {
            $this->ajaxJsonRequest->sendJsonError([
                'message' => $this->invalidContextMessage(),
            ]);
        }

        $this->dispatcher->dispatch($context, $task, $requestData);
    }

    /**
     * Send an Error Response Back to Customer
     *
     * @return void
     */
    private function sendErrorResponse()
    {
        wc_add_notice(
            esc_html__(
                'Sorry, there was an error processing the request, please try to checkout again.',
                'woo-paypalplus'
            ),
            'error'
        );
        wp_send_json_error([
            'redirectUrl' => wc_get_cart_url(),
        ]);
    }

    /**
     * Validate Request
     *
     * WooCommerce `nonce_user_logged_out` conflict with our request because we create the nonce
     * before the WooCommerce session has been set and when we execute tasks at different time we
     * cannot be sure the customer Id will reflect the correct value.
     *
     * So deactivate temporary the WooCommerce filter do the trick.
     *
     * @return bool
     */
    private function validateRequest()
    {
        $isValid = false;

        remove_filter('nonce_user_logged_out', [wc()->session, 'nonce_user_logged_out']);
        if ($this->nonce->validate($this->nonceContext)) {
            $isValid = true;
        }
        add_filter('nonce_user_logged_out', [wc()->session, 'nonce_user_logged_out']);

        return $isValid;
    }

    /**
     * Retrieve the context from the request data
     *
     * @return mixed
     */
    private function context()
    {
        return $this->request->get('context', FILTER_SANITIZE_STRING);
    }

    /**
     * Retrieve the task name from the request data
     *
     * @return string
     */
    private function task()
    {
        return $this->request->get('task', FILTER_SANITIZE_STRING);
    }

    /**
     * The invalid Context Message
     *
     * @return string
     */
    private function invalidContextMessage()
    {
        $message = esc_html_x(
            'Invalid context for express checkout request. Allowed are: %s.',
            'express-checkout',
            'woo-paypalplus'
        );
        $validContextList = implode(',', self::VALID_CONTEXTS);

        return sprintf($message, $validContextList);
    }
}
