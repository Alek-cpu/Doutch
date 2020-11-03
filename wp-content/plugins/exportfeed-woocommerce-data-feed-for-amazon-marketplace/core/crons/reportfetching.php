<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once dirname(__FILE__) . '/../classes/amazon_main.php';

Class AMWSCP_GETREPORTS
{

    private $db;
    private $productRecordTable;
    private $feed_ID;

    function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->productRecordTable = $this->db->prefix . 'amwscp_feed_product_record';
    }

    public function getReports()
    {
        set_include_path(dirname(__FILE__) . '/../classes/Amazon/');
        global $wpdb, $amwcore;
        $amazon = new CPF_Amazon_Main();
        $table = $wpdb->prefix . "amwscp_amazon_feeds";
        $feed_tbl = $wpdb->prefix . "amwscp_feeds";
        //$wpdb->query("DELETE FROM $table WHERE id NOT IN ( SELECT * FROM ( SELECT MAX(id) FROM $table GROUP BY feed_title ) temp )");
        //$sql = $wpdb->prepare("SELECT FeedSubmissionId,account_id,feed_title,type_id FROM $table WHERE FeedProcessingStatus != %s ORDER BY updated_at asc LIMIT 5", array('_DONE_'));
        $sql = "SELECT FeedSubmissionId,account_id,feed_title,type_id FROM $table WHERE FeedProcessingStatus !='_DONE_'  ORDER BY updated_at asc LIMIT 5";
        $FeedSubmissionIds = $wpdb->get_results($sql);
        require_once 'MarketplaceWebService/Model/GetFeedSubmissionResultRequest.php';
        $count = 0;
        $no_of_errors = null;
        if (is_array($FeedSubmissionIds) && count($FeedSubmissionIds) > 0) {
            foreach ($FeedSubmissionIds as $i => $feed) {
                $id = $feed->FeedSubmissionId;
                $this->feed_ID = $feed->type_id;
                //if($id!=74359017916) continue;
                $credential = $feed->account_id;
                $amazon->initialize($credential);
                $service = $amazon->submitService();
                if ($service == false) {
                    error_log('Service could not be initialted for ID:' . $id);
                    continue;
                }
                $handle = @fopen('php://memory', 'rw+');
                $request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
                $request->setMerchant($amazon->seller_key);
                $request->setFeedSubmissionId($id);
                if ($amazon->mws_auth_token) {
                    $request->setMWSAuthToken($amazon->mws_auth_token);
                }
                $request->setFeedSubmissionResult($handle);
                $result = new stdClass();

                try {
                    $response = $service->getFeedSubmissionResult($request);
                    if ($response->isSetGetFeedSubmissionResultResult()) {
                        rewind($handle);
                        $result->content = stream_get_contents($handle);
                        $result->success = true;
                    }
                } catch (MarketplaceWebService_Exception $ex) {
                    $result->ErrorMessage = $ex->getMessage();
                    $result->ErrorCode = $ex->getErrorCode();
                    $result->StatusCode = $ex->getStatusCode();
                    $result->success = false;
                }
                /*if($id==74359017916){
                    var_dump($amazon->serviceUrl);
                    echo "<pre>";
                    print_r($result);
                    echo "</pre>";
                    exit();
                }*/
                $data = array();
                if ($result->success) {
                    $c = $result->content;
                    $suceess = strpos($result->content, 'successful') + 10;
                    $processed = strpos($result->content, 'processed') + 9;
                    $no_of_success = (int)substr($c, $suceess, 5);
                    $no_of_process = (int)substr($c, $processed, 5);

                    $sucess_feed = false;
                    $status = '_COMPLETED_WITH_ERRORS_';
                    if ($no_of_process > 0 && ($no_of_success == $no_of_process)) {
                        $no_of_errors = $no_of_process - $no_of_success;
                        if ($no_of_errors == 0) {
                            $sucess_feed = true;
                            $status = '_DONE_';
                            $update = $wpdb->update($feed_tbl, array('remote_category' => 'listingloader'), array('id' => $feed->type_id));
                        }
                    } else {
                        $date = date('Ymdhis');
                        $dir = AMWSCP_PFeedFolder::uploadRoot() . '/amazon_mws_feeds/' . $feed->feed_title . $date . '.txt';
                        $local_url = wp_upload_dir();
                        $handle = fopen($dir, 'w');
                        fwrite($handle, $result->content);
                        fclose($handle);
                        $parseAndinsert = $this->parseFileAndmakereadyForPartialUpload($result->content, $dir);
                        if ($sucess_feed == false) {
                            $update = $wpdb->update($feed_tbl, array('submitted' => 0,), array('id' => $feed->type_id));
                            $status = '_DONE_';
                        }
                        if ($no_of_errors !== 0) {
                            $status = '_COMPLETED_WITH_ERRORS_';
                        }
                        if (isset($result->ErrorMessage)) {
                            $data = array(
                                'result' => "There was some problem fetching reports",
                                'FeedProcessingStatus' => $status,
                                'message' => $result->ErrorMessage,
                                'updated_at' => date('Y-m-d H:i:s')
                            );
                        } else {
                            $data = array(
                                'result' => $local_url['baseurl'] . '/amazon_mws_feeds/' . $feed->feed_title . $date . '.txt',
                                'FeedProcessingStatus' => $status,
                                'message' => 'Reports Fetched from amazon successfully. Please view reports for more detail.',
                                'updated_at' => date('Y-m-d H:i:s')
                            );
                        }
                        $update = $wpdb->update($table, $data, array('FeedSubmissionId' => $id));
                        $count++;

                    }
                } elseif ($result->ErrorMessage == 'Request is throttled') {
                    $data = array(
                        'message' => $result->ErrorMessage . ' by AWS. Please wait till the throttling is removed. Thanks.',
                        'updated_at' => date('Y-m-d H:i:s')
                    );
                    $update = $wpdb->update($table, $data, array('FeedSubmissionId' => $id));
                } elseif ($result) {
                    $data = array(
                        'result' => 'Result is not ready yet',
                        'updated_at' => date('Y-m-d H:i:s'),
                        'message' => $result->ErrorMessage
                    );
                    $update = $wpdb->update($table, $data, array('FeedSubmissionId' => $id));
                } else {
                    $data = array(
                        'result' => 'Result is not ready yet',
                        'updated_at' => date('Y-m-d H:i:s'),
                        'message' => 'Reports are not prepared by amazon yet. Please wait some more time. Thanks.'
                    );
                    $update = $wpdb->update($table, $data, array('FeedSubmissionId' => $id));
                }
            }
        }
        if ($count) {
            echo $count . ' feeds updated! Reloading the page, please wait...';
        } else {
            echo 'All feeds are submitted. Download the reports to see progress of your product listing.';
        }
    }

    public function parseFileAndmakereadyForPartialUpload($content, $file)
    {
        $rows = array_map(function ($v) {
            return str_getcsv($v, "\t");
        }, file($file));

        $allproductOfthisFeed = $this->getAllSkus($this->feed_ID);
        $header = array_shift($rows);
        $failed_product_skus = [];
        $csv = [];
        for ($i = 4; $i < count($rows); $i++) {
            $sku = $rows[$i][1];
            $error = $rows[$i][4];
            $failed_product_skus[] = $sku;
            // $this->db->update($this->productRecordTable, array('uploaded' => 0, 'upload_result' => $error), array('sku' => $sku));
        }

        $successFullUploads = array_diff($allproductOfthisFeed, $failed_product_skus);
        if (count($successFullUploads) > 0) {
            foreach ($successFullUploads as $successFullUpload) {
                $this->db->update($this->productRecordTable, array('uploaded' => 1, 'upload_result' => $error), array('sku' => $sku));
            }
        }
        return true;
    }

    public function getAllSkus($feedid)
    {
        $skus = [];
        $result = $this->db->get_results($this->db->prepare("SELECT sku FROM $this->productRecordTable WHERE feed_id=%d", array($this->feed_ID)));
        foreach ($result as $key => $item) {
            $skus[] = $item->sku;
        }
        return $skus;
    }

    public function createNewFileforFailedOnes()
    {

    }

}
