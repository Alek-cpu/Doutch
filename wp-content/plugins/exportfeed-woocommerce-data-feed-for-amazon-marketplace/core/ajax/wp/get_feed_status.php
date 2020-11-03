<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
define('XMLRPC_REQUEST', true);
ob_start(null);

function safeGetPostData($index)
{
    if (isset($_POST[$index]))
        return $_POST[$index];
    else
        return '';
}

$feedIdentifier = safeGetPostData('feed_identifier');

ob_clean();
echo get_option('cp_feedActivity_' . $feedIdentifier);

?>