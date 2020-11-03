<?php
if (!defined('ABSPATH')) {
    exit;
}
/*ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);*/
// Exit if accessed directly
require_once dirname(__FILE__) . '/../../classes/amazon_main.php';
require_once dirname(__FILE__) . '/../../data/feedfolders.php';
include_once dirname(__FILE__) . '/../../classes/invoker.php'; // i like this
require_once AMWSCPF_PATH . '/amazon.php';
set_include_path(dirname(__FILE__) . '/../../classes/Amazon/');
global $wpdb;
$display = new AMWSCPF_Amazon();
$amazon = new CPF_Amazon_Main();
$cmd = sanitize_text_field($_REQUEST['cmd']);

$feedType = '_POST_FLAT_FILE_LISTINGS_DATA_';
$type = 'feed';
require_once 'MarketplaceWebService/Model/SubmitFeedRequest.php';
$id = intval($_REQUEST['feed_id']);
$credentials = intval($_REQUEST['credentials']);
/*=== Getting Account Detail ===*/

$table = $wpdb->prefix . "amwscp_feeds";
$sql = $wpdb->prepare("SELECT * FROM $table WHERE id = %d", [$id]);
$savedFeed = $wpdb->get_row($sql);
$current_productcount = $savedFeed->product_count;
$filename = basename($savedFeed->url);
$dir = AMWSCP_PFeedFolder::uploadFolder() . '/AmazonSC/';
$feed = file_get_contents($dir . $filename);
$amazon->initialize($credentials);
$service = $amazon->submitService();
$feedhandle = @fopen('php://temp', 'rw+');
fwrite($feedhandle, $feed);
rewind($feedhandle);

/*======================================================================================================================
                                                      Depricated Code
========================================================================================================================
    $parameters = array(
    'Merchant'          => $amazon->seller_key,
    'MarketplaceIdList' => $amazon->marketplace_key,
    'FeedType'          => $feedType,
    'FeedContent'       => $feedhandle,
    'PurgeAndReplace'   => false,
    'MWSAuthToken' => 'amzn.mws.daaaabff-9b76-ac68-a496-4c611ab7ffab',
    'ContentMd5'        => base64_encode(md5(stream_get_contents($feedhandle), true)),
);
rewind($feedhandle);

=====================================================================================================================*/
$request = new MarketplaceWebService_Model_SubmitFeedRequest(NULL);
$request->setMerchant($amazon->seller_key);
$request->setMarketplaceIdList($amazon->marketplace_key);
if ($amazon->mws_auth_token) {
    $request->setMWSAuthToken($amazon->mws_auth_token);
}
$request->setFeedType($feedType);
$request->setFeedContent($feedhandle);
$request->setPurgeAndReplace(false);
$request->setContentMd5(base64_encode(md5(stream_get_contents($feedhandle), true)));

/*======================================================================================================================
 * change this with below code
       $request = new MarketplaceWebService_Model_SubmitFeedRequest(NULL);
       $request->setMerchant($amazon->seller_key);
       $request->setMarketplaceIdList($amazon->marketplace_key);
       $request->setMWSAuthToken($amazon->secret_key);
       $request->setFeedType($feedType);
       $request->setFeedContent($feedhandle);
       $request->setPurgeAndReplace(true);
       $request->setContentMd5(base64_encode(md5(stream_get_contents($feedhandle), true)));
======================================================================================================================*/

$invoker = new CPF_Invoker(); // wow
$submit = $invoker->invokeSubmitFeed($service, $request);

// saving the feed report
$table = $wpdb->prefix . "amwscp_amazon_feeds";
if ($submit->success) {
    $data = array(
        'FeedSubmissionId' => $submit->FeedSubmissionId,
        'FeedProcessingStatus' => $submit->FeedProcessingStatus,
        'FeedType' => $submit->FeedType,
        'SubmittedDate' => $submit->SubmittedDate,
        'Status' => $submit->success,
        'type_id' => $id,
        'type' => $type,
        'account_id' => $credentials,
        'feed_title' => $savedFeed->filename,
        'message'               => 'Reports will be fetched after sometime, please wait.',
        'updated_at'=> date('Y-m-d H:i:s'),
    );

    $qry = $wpdb->prepare("SELECT * FROM `$table` WHERE feed_title = %s", array($savedFeed->filename));
    $result = $wpdb->get_results($qry);

    if (is_array($result) && count($result) > 1) {
        $wpdb->query("DELETE FROM $table WHERE id NOT IN ( SELECT * FROM ( SELECT MAX(id) FROM $table GROUP BY feed_title ) temp )");
        $result = $wpdb->get_row($qry);
    } else {
        $result = $wpdb->get_row($qry);
    }
    $update = null;
    $insert = null;
    if (is_object($result) && !empty($result)) {
        $update = $wpdb->update($table, $data, array('feed_title' => $savedFeed->filename));
    } else {
        $insert = $wpdb->insert($table, $data);
    }

    if ($insert || $update) {
        $table = $wpdb->prefix . "amwscp_feeds";
        $update = $wpdb->update($table, array('previous_product_count' => $current_productcount, 'submitted' => 1), array('id' => $id));
    }
} else {
    $data = array();
}
$display->view('submit-result-table', array(
    'data' => $data,
    'result' => $submit,
));
