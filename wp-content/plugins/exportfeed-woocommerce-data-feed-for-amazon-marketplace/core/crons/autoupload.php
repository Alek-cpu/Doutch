<?php
require_once AMWSCPF_PATH . '/amwscpf-wpincludes.php';
require_once AMWSCPF_PATH . '/core/feeds/amazonsc/feed.php';
require_once AMWSCPF_PATH . '/core/data/productlistw.php';
require_once AMWSCPF_PATH . '/core/classes/Amazon/MarketplaceWebService/Model/SubmitFeedRequest.php';

Class Autouploader
{
    private $feedType = '_POST_FLAT_FILE_LISTINGS_DATA_';
    private $uploadType = 'auto_update';
    private $product_record_table;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->product_record_table = $this->db->prefix . 'amwscp_feed_product_record';
        $this->fileformat = 'txt';
        $this->providerName = 'AmazonSC';
        $this->providerNameL = 'amazonsc';
    }

    public function feedCreater()
    {
        $products = $this->db->get_results($this->db->prepare("SELECT * FROM $this->product_record_table WHERE uploaded=%d", array(1)));
        $invoker = new AMWSCP_PAmazonSCFeed();
        $invoker->getListingLoaderFeed(null, 'listingloader', 'universal-autoloader-feed', $saved_feed = null, $products);
        return true;
    }

    public function submit()
    {
        $amazon = new CPF_Amazon_Main();
        $credential = $amazon->get_default_account();
        $savedFeed = AMWSCP_PFeedFolder::uploadURL() . $this->providerName . '/universal-autoloader-feed.' . $this->fileformat;
        $feed_content = file_get_contents($savedFeed);
        $amazon->initialize($credential->id);
        $service = $amazon->submitService();
        $feedhandle = @fopen('php://temp', 'rw+');
        fwrite($feedhandle, $feed_content);
        rewind($feedhandle);
        $request = new MarketplaceWebService_Model_SubmitFeedRequest(NULL);
        $request->setMerchant($amazon->seller_key);
        $request->setMarketplaceIdList($amazon->marketplace_key);
        if ($amazon->mws_auth_token) {
            $request->setMWSAuthToken($amazon->mws_auth_token);
        }
        $request->setFeedType($this->feedType);
        $request->setFeedContent($feedhandle);
        $request->setPurgeAndReplace(false);
        $request->setContentMd5(base64_encode(md5(stream_get_contents($feedhandle), true)));
        $invoker = new CPF_Invoker();
        $submit = $invoker->invokeSubmitFeed($service, $request);
        return $submit;
    }
}
