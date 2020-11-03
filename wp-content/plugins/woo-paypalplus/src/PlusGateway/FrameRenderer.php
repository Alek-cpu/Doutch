<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\PlusGateway;

/**
 * Class FrameRenderer
 *
 * @package WCPayPalPlus\WC
 */
class FrameRenderer
{
    /**
     * Render the Paywall iframe
     *
     * @param array $data
     */
    public function render(array $data)
    {
        $id = $data['placeholder'];
        $config = wp_json_encode($data);
        ?>
        <div
            id="<?php echo esc_attr($id) ?>"
            class="paypalplus-paywall"
            data-config="<?= esc_attr($config) ?>"
        ></div>
        <?php
    }
}
