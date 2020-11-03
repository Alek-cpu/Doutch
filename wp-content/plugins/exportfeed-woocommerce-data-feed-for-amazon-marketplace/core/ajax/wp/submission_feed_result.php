<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once dirname(__FILE__) . '/../../crons/reportfetching.php';
$obj = new AMWSCP_GETREPORTS();
$obj->getReports();
exit();

/* We are keeping this to revert if the new way doesn't works */
require_once dirname(__FILE__) . '/../../classes/amazon_main.php';
set_include_path(dirname(__FILE__) . '/../../classes/Amazon/');
global $wpdb,$amwcore;
$amazon = new CPF_Amazon_Main();
$table = $wpdb->prefix . "amwscp_amazon_feeds";
$feed_tbl = $wpdb->prefix."amwscp_feeds";

//$wpdb->query("DELETE FROM $table WHERE id NOT IN ( SELECT * FROM ( SELECT MAX(id) FROM $table GROUP BY feed_title ) temp )");
//$sql = $wpdb->prepare("SELECT FeedSubmissionId,account_id,feed_title,type_id FROM $table WHERE FeedProcessingStatus != %s ORDER BY updated_at asc LIMIT 5", ['_DONE_']);
$sql = "SELECT FeedSubmissionId,account_id,feed_title,type_id FROM $table WHERE FeedProcessingStatus !='_DONE_'  ORDER BY updated_at asc LIMIT 5";
$FeedSubmissionIds = $wpdb->get_results($sql);
require_once 'MarketplaceWebService/Model/GetFeedSubmissionResultRequest.php';
$count = 0;
$no_of_errors = null;
if (is_array($FeedSubmissionIds) && count($FeedSubmissionIds) > 0) {
    foreach ($FeedSubmissionIds as $i => $feed){
        $id = $feed->FeedSubmissionId;
        $credential = $feed->account_id;
        if($credential!=12) continue;
        $amazon->initialize($credential);
        $service = $amazon->submitService();

        $handle = @fopen('php://memory','rw+');
        $request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
        $request->setMerchant($amazon->seller_key);
        $request->setFeedSubmissionId($id);
        if($amazon->mws_auth_token){
            $request->setMWSAuthToken($amazon->mws_auth_token);
        }
        $request->setFeedSubmissionResult($handle);
        $result = new stdClass();

        try {
            $response = $service->getFeedSubmissionResult($request);
            if ($response->isSetGetFeedSubmissionResultResult()){
                rewind($handle);
                $result->content = stream_get_contents($handle);
                $result->success = true;
            }
        } catch ( MarketplaceWebService_Exception $ex) {
            $result->ErrorMessage =  $ex->getMessage();
            $result->ErrorCode = $ex->getErrorCode();
            $result->StatusCode = $ex->getStatusCode();
            $result->success      = false;
        }

        $data = array();
        if ($result->success){
            $c = $result->content;
            $suceess = strpos($result->content,'successful')+10;
            $processed = strpos($result->content,'processed')+9;
            $no_of_success = (int)substr($c,$suceess,5);
            $no_of_process = (int)substr($c,$processed,5);

            $sucess_feed = false;
            $status = '_COMPLETED_WITH_ERRORS_';
            if ($no_of_process > 0){
                $no_of_errors = $no_of_process - $no_of_success;
                if ($no_of_errors == 0){
                    $sucess_feed = true;
                    $status = '_DONE_';
                    $update = $wpdb->update($feed_tbl,['remote_category' => 'listingloader'],['id'=>$feed->type_id]);
                }
            }
            if ($sucess_feed==false){
                //$update = $wpdb->update($feed_tbl,['submitted' => 0],['id'=>$feed->type_id]);
                $status = '_DONE_';
            }
            if($no_of_errors!==0){
                $status = '_COMPLETED_WITH_ERRORS_';
            }
            $date = date('Ymdhis');
            $dir = AMWSCP_PFeedFolder::uploadRoot().'/amazon_mws_feeds/'.$feed->feed_title.$date.'.txt';
            $local_url = wp_upload_dir();
            $handle = fopen($dir,'w');
            fwrite($handle,$result->content);
            fclose($handle);
            $data = [
                'result'                => $local_url['baseurl'].'/amazon_mws_feeds/'.$feed->feed_title.$date.'.txt',
                'FeedProcessingStatus'  => $status,
                'message'               => 'Reports Fetched from amazon successfully. Please view reports for more detail.',
                'updated_at'            => date('Y-m-d H:i:s')
            ];
            //$update = $wpdb->update($table,$data,['FeedSubmissionId' => $id]);
            $count++;
        } elseif($result->ErrorMessage == 'Request is throttled'){
            $data = [
                'message'    => $result->ErrorMessage.' by AWS. Please wait till the throttling is removed. Thanks.',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            error_log("Id:".json_encode($id));
            error_log("Just before update");
            //$update = $wpdb->update($table,$data,['FeedSubmissionId' => $id]);
        } else {
            $data = [
                'result'       => 'Result is not ready yet',
                'updated_at'   => date('Y-m-d H:i:s'),
                'message'      => 'Reports are not prepared by amazon yet. Please wait some more time. Thanks.'
            ];
            //$update = $wpdb->update($table,$data,['FeedSubmissionId' => $id]);
        }
    }
}
if ($count){
    echo $count .' feeds updated! Reloading the page, please wait...';
} else {
    echo 'All feeds are submitted. Download the reports to see progress of your product listing.';
}
