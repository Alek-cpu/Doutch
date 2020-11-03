<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use Exception;
use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Api\Transaction;
use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Gateway\CurrentPaymentMethod;
use WCPayPalPlus\Session\Session;
use WooCommerce;

/**
 * Class CheckoutAddressOverride
 * @package WCPayPalPlus\ExpressCheckoutGateway
 */
class CheckoutAddressOverride
{

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * @var CurrentPaymentMethod
     */
    private $currentPaymentMethod;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array Addresses
     */
    private $addresses = [];

    /**
     * CheckoutAddressOverride constructor.
     *
     * @param WooCommerce $wooCommerce
     * @param CurrentPaymentMethod $currentPaymentMethod
     * @param Logger $logger
     */
    public function __construct(
        WooCommerce $wooCommerce,
        CurrentPaymentMethod $currentPaymentMethod,
        Logger $logger
    ) {

        $this->wooCommerce = $wooCommerce;
        $this->currentPaymentMethod = $currentPaymentMethod;
        $this->logger = $logger;
    }

    /**
     * Don't save customer address from paypal
     *
     * @wp-hook woocommerce_checkout_update_customer_data
     *
     * @param bool $default
     *
     * @return bool
     */
    public function filterSaveCustomerData($default)
    {
        if (! $this->isExpressCheckout()) {
            return $default;
        }

        return false;
    }

    /**
     * Overwrite real customer data with session customer data
     *
     * @wp-hook woocommerce_checkout_get_value
     *
     * @param string $default
     * @param string $input
     *
     * @return string
     */
    public function filterCheckoutValues($default, $input)
    {
        if (! $this->isExpressCheckout()) {
            return $default;
        }

        if (0 !== strpos($input, 'billing_') && 0 !== strpos($input, 'shipping_')) {
            return $default;
        }

        $addresses = $this->getPaymentAddresses();
        if (isset($addresses[$input])) {
            return $addresses[$input];
        }

        return $default;
    }

    /**
     * Are we currently in a Express checkout
     *
     * @return bool
     */
    private function isExpressCheckout()
    {
        $currentPaymentMethod = $this->currentPaymentMethod->payment();

        return Gateway::GATEWAY_ID === $currentPaymentMethod;
    }

    /**
     * Get shipping and billing address
     *
     * @return array
     */
    private function getPaymentAddresses()
    {
        if ($this->addresses) {
             return $this->addresses;
        }

        $paymentId = $this->wooCommerce->session->get(Session::PAYMENT_ID);
        $apiContext = ApiContextFactory::getFromConfiguration();
        $payment = null;
        try {
            $payment = Payment::get($paymentId, $apiContext);
        } catch (PayPalConnectionException $exc) {
            $this->logger->error($exc->getData());
        } catch (Exception $exc) {
            $this->logger->error($exc->getMessage());
        }

        if (!$payment) {
            wc_add_notice(
                __('Can not retrieve address from PayPal, try to checkout again.', 'woo-paypalplus'),
                'error'
            );
            return $this->addresses;
        }

        $payer = $payment->getPayer();
        $payerInfo = $payer->getPayerInfo();

        $shippingAddress = null;
        $transactions = $payment->getTransactions();
        if ($transactions && $transactions[0] instanceof Transaction) {
            $itemList = $transactions[0]->getItemList();
            $shippingAddress = $itemList->getShippingAddress();
        }

        $this->addresses['billing_first_name'] = $payerInfo->getFirstName();
        $this->addresses['billing_last_name'] = $payerInfo->getLastName();
        $this->addresses['billing_company'] = '';
        $this->addresses['billing_email'] = $payerInfo->getEmail();
        $this->addresses['billing_phone'] = $payerInfo->getPhone();
        $this->addresses['billing_country'] = $payerInfo->getCountryCode();

        $apiBillingAddress = $payerInfo->getBillingAddress();
        $sessionCustomer = $this->wooCommerce->customer;
        if ($apiBillingAddress) {
            $this->addresses['billing_address_1'] = $apiBillingAddress->getLine1();
            $this->addresses['billing_address_2'] = $apiBillingAddress->getLine2();
            $this->addresses['billing_city'] = $apiBillingAddress->getCity();
            $this->addresses['billing_country'] = $apiBillingAddress->getCountryCode();
            $this->addresses['billing_postcode'] = $apiBillingAddress->getPostalCode();
            $this->addresses['billing_state'] = $apiBillingAddress->getState();
        }
        if (!$apiBillingAddress && $sessionCustomer->get_billing_address_1()) {
            $this->addresses['billing_company'] = $sessionCustomer->get_billing_company();
            $this->addresses['billing_address_1'] = $sessionCustomer->get_billing_address_1();
            $this->addresses['billing_address_2'] = $sessionCustomer->get_billing_address_2();
            $this->addresses['billing_city'] = $sessionCustomer->get_billing_city();
            $this->addresses['billing_country'] = $sessionCustomer->get_billing_country();
            $this->addresses['billing_postcode'] = $sessionCustomer->get_billing_postcode();
            $this->addresses['billing_state'] = $sessionCustomer->get_billing_state();
        }
        if (!$apiBillingAddress && !isset($this->addresses['billing_address_1'])) {
            $this->addresses['billing_address_1'] = $shippingAddress->getLine1();
            $this->addresses['billing_address_2'] = $shippingAddress->getLine2();
            $this->addresses['billing_city'] = $shippingAddress->getCity();
            $this->addresses['billing_country'] = $shippingAddress->getCountryCode();
            $this->addresses['billing_postcode'] = $shippingAddress->getPostalCode();
            $this->addresses['billing_state'] = $shippingAddress->getState();
        }

        if ($shippingAddress) {
            list($this->addresses['shipping_first_name'], $this->addresses['shipping_last_name']) =
                explode(' ', $shippingAddress->getRecipientName(), 2);
            $this->addresses['shipping_address_1'] = $shippingAddress->getLine1();
            $this->addresses['shipping_address_2'] = $shippingAddress->getLine2();
            $this->addresses['shipping_city'] = $shippingAddress->getCity();
            $this->addresses['shipping_country'] = $shippingAddress->getCountryCode();
            $this->addresses['shipping_postcode'] = $shippingAddress->getPostalCode();
            $this->addresses['shipping_state'] = $shippingAddress->getState();
        }

        return $this->addresses;
    }
}
