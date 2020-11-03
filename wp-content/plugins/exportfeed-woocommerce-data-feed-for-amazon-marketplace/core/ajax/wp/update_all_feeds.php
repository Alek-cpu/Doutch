<?php

if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
$interval_switch = get_option('amwscpf_interval_switch');
if ($interval_switch == true) {
    amwscpf_update_all_feeds(false);
    echo 'Update successful';
} else {
    echo 'Feed  update is disabled. Please enable from above toggle switch.';
}
