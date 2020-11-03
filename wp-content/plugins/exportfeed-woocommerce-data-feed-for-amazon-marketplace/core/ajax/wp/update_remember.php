<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (isset($_POST['remember'])) {
    $remember = $_POST['remember'];
    $user_id = $_POST['userid'];
    $provider = $_POST['provider'];
    update_user_meta($user_id, "cpf_remember_$provider", $remember);
    if ($remember == 'true') {
        foreach ($_POST as $key => $val) {
            if (!in_array($key, array('remember', 'provider', 'userid')))
                update_user_meta($user_id, "cpf_$key" . "_$provider", $val);
        }
    } else {
        foreach ($_POST as $key => $val) {
            if (!in_array($key, array('remember', 'provider', 'userid')))
                update_user_meta($user_id, "cpf_$key" . "_$provider", '');
        }
    }
}
