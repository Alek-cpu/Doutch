<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Pui;

/**
 * Class PaymentInstructionView
 */
class View
{
    /**
     * PUI data
     *
     * @var Data
     */
    private $data;

    /**
     * PaymentInstructionView constructor.
     *
     * @param Data $data PUI Data provider.
     */
    public function __construct(Data $data)
    {
        $this->data = $data;
    }

    /**
     * Render the instructions table on the thank you page
     *
     * phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
     */
    public function thankyouPage()
    {
        esc_html_e(
            'Please transfer the complete amount to the bank account provided below.',
            'woo-paypalplus'
        );
        // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
        echo PHP_EOL;
        ?>
        <h2><?php esc_html_e('PayPal Bank Details', 'woo-paypalplus'); ?></h2>
        <table class="shop_table order_details">
            <tbody>
            <tr>
                <th scope="row"><?php esc_html_e('Bank name:', 'woo-paypalplus'); ?></th>
                <td><span><?php echo esc_html($this->data->bankName()); ?></span></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Account holder name:', 'woo-paypalplus'); ?></th>
                <td><span><?php echo esc_html($this->data->accountHolderName()); ?></span>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('IBAN:', 'woo-paypalplus'); ?></th>
                <td><span><?php echo esc_html($this->data->iban()); ?></span></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('BIC:', 'woo-paypalplus'); ?></th>
                <td><span><?php echo esc_html($this->data->bic()); ?></span></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Payment due date:', 'woo-paypalplus'); ?></th>
                <td><span><?php echo esc_html($this->data->paymentDueDate()) ?></span></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Reference:', 'woo-paypalplus'); ?></th>
                <td><span><?php echo esc_html($this->data->referenceNumber()); ?></span></td>
            </tr>
            </tbody>
        </table>
        <?php
        echo $this->data->legalNote();
    }

    /**
     * Renders the instructions table.
     *
     * @param bool $plainText
     */
    public function emailInstructions($plainText = false)
    {
        if (!$plainText) {
            esc_html_e(
                'Please transfer the complete amount to the bank account provided below.',
                'woo-paypalplus'
            );
            // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
            echo PHP_EOL;

            printf(
                '<h2 class="wc-bacs-bank-details-heading">%s</h2>',
                esc_html__(
                    'PayPal Bank Details',
                    'woo-paypalplus'
                )
            );
            // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
            echo PHP_EOL;

            // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
            echo '<ul class="wc-bacs-bank-details order_details bacs_details">' . PHP_EOL;
            foreach ($this->getAccountFields() as $field_key => $field) {
                if (!empty($field['value'])) {
                    echo '<li class="' . esc_attr($field_key) . '">'
                        . esc_attr($field['label']) . ': <strong>' . wptexturize($field['value'])
                        // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
                        . '</strong></li>' . PHP_EOL;
                }
            }
            echo '</ul>';

            // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
            echo $this->data->legalNote() . PHP_EOL;
        } else {
            esc_html_e(
                'Please transfer the complete amount to the bank account provided below.',
                'woo-paypalplus'
            );
            // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
            echo PHP_EOL;
            esc_html_e('PayPal Bank Details', 'woo-paypalplus');
            // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
            echo PHP_EOL;

            foreach ($this->getAccountFields() as $field_key => $field) {
                if (!empty($field['value'])) {
                    echo ' - ' . esc_attr($field['label'])
                        // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
                        . ': ' . wptexturize($field['value']) . PHP_EOL;
                }
            }

            // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
            echo PHP_EOL . esc_html(strip_tags($this->data->legalNote())) . PHP_EOL;
        }
    }

    /**
     * Returns the account fields.
     *
     * @return array
     */
    private function getAccountFields()
    {
        return [
            'bank_name' => [
                'label' => __('Bank name', 'woo-paypalplus'),
                'value' => $this->data->bankName(),
            ],
            'account_holder_name' => [
                'label' => __('Account holder name', 'woo-paypalplus'),
                'value' => $this->data->accountHolderName(),
            ],
            'iban' => [
                'label' => __('IBAN', 'woo-paypalplus'),
                'value' => $this->data->iban(),
            ],
            'bic' => [
                'label' => __('BIC', 'woo-paypalplus'),
                'value' => $this->data->bic(),
            ],
            'payment_due_date' => [
                'label' => __('Payment due date', 'woo-paypalplus'),
                'value' => $this->data->paymentDueDate(),
            ],
            'reference_number' => [
                'label' => __('Reference', 'woo-paypalplus'),
                'value' => $this->data->referenceNumber(),
            ],
        ];
    }
}
