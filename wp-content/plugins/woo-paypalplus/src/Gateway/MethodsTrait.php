<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Gateway;

use WC_Order;
use WC_Order_Refund;
use WCPayPalPlus\Api\ApiContextFactory;
use RuntimeException;
use WCPayPalPlus\Api\CredentialValidator;
use WCPayPalPlus\Notice\Admin as AdminNotice;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Refund\RefundFactory;

/**
 * Trait GatewayMethodsTrait
 * @property OrderFactory $orderFactory
 * @property CredentialValidator $credentialValidator
 * @property RefundFactory $refundFactory
 * @package WCPayPalPlus
 */
trait MethodsTrait
{
    /**
     * @inheritdoc
     */
    public function init_form_fields()
    {
        $this->form_fields = $this->settingsModel->settings($this);
    }

    /**
     * @param int $orderId
     * @param null $amount
     * @param string $reason
     * @return bool
     * @throws RuntimeException
     */
    public function process_refund($orderId, $amount = null, $reason = '')
    {
        $order = $this->orderFactory->createById($orderId);

        if (!$order instanceof WC_Order) {
            return false;
        }

        if (!$this->can_refund_order($order)) {
            return false;
        }

        $apiContext = ApiContextFactory::getFromConfiguration();
        $refund = $this->refundFactory->create($order, $amount, $reason, $apiContext);

        return $refund->execute();
    }

    /**
     * @param WC_Order $order
     * @return bool
     */
    public function can_refund_order($order)
    {
        return $order && $order->get_transaction_id();
    }

    /**
     * @param array $formFields
     * @param bool $echo
     * @return false|string
     */
    public function generate_settings_html($formFields = [], $echo = true)
    {
        ob_start();
        $this->display_errors();
        do_action(AdminNotice::ACTION_ADMIN_MESSAGES);
        $output = ob_get_clean();

        $credentialValidationResponse = $this->credentialValidator->ensureCredential(
            ApiContextFactory::getFromConfiguration()
        );
        $isValidStatus = $credentialValidationResponse->isValidStatus();

        $isValidStatus and $this->sandboxMessage($output);
        !$isValidStatus and $this->invalidPaymentMessage($output);

        $output .= parent::generate_settings_html($formFields, $echo);

        if ($echo) {
            echo wp_kses_post($output);
        }

        return $output;
    }

    /**
     * @param $output
     * @param $message
     */
    private function credentialInformation(&$output, $message)
    {
        $output .= sprintf(
            '<div><p>%s</p></div>',
            esc_html__(
                'Below you can see if your account is successfully hooked up to use PayPal.',
                'woo-paypalplus'
            ) . "<br />{$message}"
        );
    }

    /**
     * @param $output
     */
    private function invalidPaymentMessage(&$output)
    {
        $this->credentialInformation(
            $output,
            sprintf(
                '<strong class="error-text">%s</strong>',
                esc_html__(
                    'Error connecting to the API. Check that the credentials are correct.',
                    'woo-paypalplus'
                )
            )
        );
    }

    /**
     * @param $output
     */
    private function sandboxMessage(&$output)
    {
        $msgSandbox = $this->isSandboxed()
            ? esc_html__(
                'Note: This is connected to your sandbox account.',
                'woo-paypalplus'
            )
            : esc_html__(
                'Note: This is connected to your live PayPal account.',
                'woo-paypalplus'
            );

        $this->credentialInformation(
            $output,
            sprintf('<strong>%s</strong>', $msgSandbox)
        );
    }
}
