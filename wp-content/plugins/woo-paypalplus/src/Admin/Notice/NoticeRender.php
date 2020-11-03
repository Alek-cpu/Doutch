<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Admin\Notice;

/**
 * Class NoticeRender
 * @package WCPayPalPlus\Admin
 */
class NoticeRender
{
    const DISMISSABLE_SCRIPT_HANDLE = 'dismissable_notice';

    /**
     * Render the Admin Notice
     *
     * @param Noticeable $notice
     */
    public function render(Noticeable $notice)
    {
        $type = $notice->type();
        $message = $notice->message();
        $id = $notice->id();
        $isDismissible = $notice->isDismissable();
        $class = $this->classAttribute($type, $isDismissible);
        ?>
        <div class="<?= esc_attr($class) ?>" data-id="<?= esc_attr($id) ?>">
            <p>
                <?= wp_kses_post($message) ?>
            </p>
        </div>
        <?php
    }

    /**
     * Build the Class Attributes for the Notice
     *
     * @param $type
     * @param $isDismissable
     * @return string
     */
    private function classAttribute($type, $isDismissable)
    {
        $attribute = "inpsyde-notice notice notice-{$type}";
        $attribute .= $isDismissable ? ' is-dismissible' : '';

        return $attribute;
    }
}
