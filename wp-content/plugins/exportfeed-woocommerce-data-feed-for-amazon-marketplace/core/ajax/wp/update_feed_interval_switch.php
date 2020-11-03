<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
$switch = $_REQUEST['switch_value'];
$switch = ($switch == 'true') ? 1 : 0;
update_option('amwscpf_interval_switch',$switch);
echo 'switch value updated';
die;