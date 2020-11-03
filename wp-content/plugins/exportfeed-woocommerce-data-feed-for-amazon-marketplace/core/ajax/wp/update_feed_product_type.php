<?php
if (get_option($_POST['_template_identifier'] . '_feed_type') == $_POST['feed_product_type']) {
    wp_send_json_success(array('success' => true));
    exit();
} else {
    wp_send_json_success(array('success' => update_option($_POST['_template_identifier'] . '_feed_type', $_POST['feed_product_type'])));
    exit;
}
