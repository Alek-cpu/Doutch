<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}


if (isset($_POST['yes'])) {
    update_option('pw_gift_yes', $_POST['yes']);
    update_option('pw_gift_free', $_POST['gift_free']);
}

?>