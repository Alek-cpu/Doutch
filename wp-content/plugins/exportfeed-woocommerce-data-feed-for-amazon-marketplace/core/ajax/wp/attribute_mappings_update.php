<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

update_option($_POST['service_name'] . '_cp_' . $_POST['attribute'], $_POST['mapto']);


?>