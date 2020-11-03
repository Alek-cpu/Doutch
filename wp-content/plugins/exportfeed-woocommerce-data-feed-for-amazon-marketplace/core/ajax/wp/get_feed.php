<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
define('XMLRPC_REQUEST', true);
//ob_start(null, 0, PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_CLEANABLE);
// Force a short-init since we just need core WP, not the entire framework stack
if (!defined('SHORTINIT')) {
    define('SHORTINIT', true);
}

ob_start(null);

function safeGetPostData($index) {
    if (isset($_POST[$index])) {
        return $_POST[$index];
    } else {
        return '';
    }

}

function doOutput($output) {
    ob_clean();
    echo json_encode($output);
}

require_once dirname(__FILE__) . '/../../../amwscpf-wpincludes.php';

do_action('amwscpf_load_feed_modifier');
global $amwcore;
$amwcore->trigger('amwscpf_init_feeds');

add_action('amwscpf_feed_main_hook', 'get_feed_main');
do_action('amwscpf_feed_main_hook');

function get_feed_main() {
    $requestCode = safeGetPostData('provider');
    $local_category = safeGetPostData('local_category');
    $remote_category = safeGetPostData('remote_category');
    $amazon_category = safeGetPostData('amazon_category');
    $file_name = safeGetPostData('file_name');
    $feedIdentifier = safeGetPostData('feed_identifier');
    $saved_feed_id = safeGetPostData('feed_id');
    $feed_list = safeGetPostData('feed_ids'); //For Aggregate Feed Provider
    $feed_product_type = safeGetPostData('feed_product_type');
    $recommended_browse_nodes = safeGetPostData('recommended_browse_nodes');
    $item_type_keyword = safeGetPostData('item_type_keyword');
    $output = new stdClass();
    $output->url = '';
    $marketCode = safeGetPostData('selectedMArket');
    $variationtheme = safeGetPostData('variationTheme');
    $Mcode = explode('_', $remote_category);
    if (isset($Mcode[1]) && $Mcode[1] !== $marketCode && $remote_category != 'listingloader') {
        $assignedCategory = $Mcode[0] . '_' . $marketCode;
    } else {
        $assignedCategory = $remote_category;
    }

    if (strlen($requestCode) * strlen($local_category) == 0) {
        $output->errors = 'Error: error in AJAX request. Insufficient data or categories supplied.';
        doOutput($output);
        return;
    }

    if (strlen($remote_category) == 0) {
        $output->errors = 'Error: Insufficient data. Please fill in "' . $requestCode . ' category"';
        doOutput($output);
        return;
    }

    // Check if form was posted and select task accordingly
    $dir = AMWSCP_PFeedFolder::uploadRoot();
    if (!is_writable($dir)) {
        $output->errors = "Error: $dir should be writeable";
        doOutput($output);
        return;
    }
    $dir = AMWSCP_PFeedFolder::uploadFolder();
    if (!is_dir($dir)) {
        mkdir($dir);
    }
    if (!is_writable($dir)) {
        $output->errors = "Error: $dir should be writeable";
        doOutput($output);
        return;
    }

    $providerFile = 'feeds/' . strtolower($requestCode) . '/feed.php';

    if (!file_exists(dirname(__FILE__) . '/../../' . $providerFile)) {
        if (!class_exists('AMWSCP_P' . $requestCode . 'Feed')) {
            $output->errors = 'Error: Provider file not found.';
            doOutput($output);
            return;
        }
    }

    $providerFileFull = dirname(__FILE__) . '/../../' . $providerFile;
    if (file_exists($providerFileFull)) {
        require_once $providerFileFull;
    }

    //Load form data
    $file_name = sanitize_title_with_dashes($file_name);
    if ($file_name == '') {
        $file_name = 'feed' . rand(10, 1000);
    }

    $saved_feed = null;
    if ((strlen($saved_feed_id) > 0) && ($saved_feed_id > -1)) {
        require_once dirname(__FILE__) . '/../../data/savedfeed.php';
        $saved_feed = new AMWSCPF_SavedFeed($saved_feed_id);
    }
   
    $providerClass = 'AMWSCP_P' . $requestCode . 'Feed';
    $x = new $providerClass;
    $x->feed_list = $feed_list; //For Aggregate Provider only
    if (strlen($feedIdentifier) > 0) {
        $x->activityLogger = new AMWSCP_PFeedActivityLog($feedIdentifier);
    }

    $x->getFeedData($local_category, $remote_category, $file_name, $saved_feed, $amazon_category, $assignedCategory,$variationtheme,$feed_product_type,$recommended_browse_nodes,$item_type_keyword);
    if ($x->success) {
        $output->url = AMWSCP_PFeedFolder::uploadURL() . $x->providerName . '/' . $file_name . '.' . $x->fileformat;
        if (is_object($x) && property_exists($x, 'feed_data')) {
            foreach ($x->feed_data as $product_id => $prd_data) {
                update_post_meta($product_id, '_amwscpf_feed_data_' . $saved_feed_id, maybe_serialize($prd_data));
            }
        }

    }
    if ($requestCode == 'Amazonsc') {
        $url = admin_url('admin.php?page=exportfeed-amazon-amwscpf-manage-page');
        $btn = '<a href="' . $url . '" class="button button-primary">Goto Manage Feed</a>';
        $output->submit = $url;
    } else {
        $url = admin_url('admin.php?page=exportfeed-amazon-amwscpf-manage-page');
        $btn = '<a href="' . $url . '" class="button button-primary">Goto Manage Feed</a>';
        $output->submit = $url;
    }
    $output->errors = $x->getErrorMessages();

    doOutput($output);
}
