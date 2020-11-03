<?php
class AMWSCPF_Amazon
{
    public $setup   = false;
    public $message = array(); // two of the type one is 'system' error and another if 'feed' error
    public $countryfullname;
    public function __construct()
    {
        $this->view('load_script');
    }
    public function sites()
    {
        $sites = [
            [
                'title' => 'Canada',
                'code'  => 'CA',
                'url'   => 'amazon.ca',
            ],
            [
                'title' => 'France',
                'code'  => 'FR',
                'url'   => 'amazon.fr',
            ],
            [
                'title' => 'Germany',
                'code'  => 'DE',
                'url'   => 'amazon.de',
            ],
            [
                'title' => 'Italy',
                'code'  => 'IT',
                'url'   => 'amazon.it',
            ],
            [
                'title' => 'Spain',
                'code'  => 'ES',
                'url'   => 'amazon.es',
            ],
            [
                'title' => 'United Kingdom',
                'code'  => 'UK',
                'url'   => 'amazon.co.uk',
            ],
            [
                'title' => 'United States',
                'code'  => 'US',
                'url'   => 'amazon.com',
            ],
            [
                'title' => 'Mexico',
                'code'  => 'MX',
                'url'   => 'amazon.com',
            ],
            [
                'title' => 'Australia',
                'code'  => 'AU',
                'url'   => 'amazon.com.au',
            ],
            [
                'title' => 'India',
                'code'  => 'IN',
                'url'   => 'amazon.in',
            ],

        ];
        return $sites;
    }
    public function display($id = null, $action = "", $feed_id)
    {
        global $wpdb;
        $feedTable     = $wpdb->prefix . "amwscp_feeds";
        $feedsql       = $wpdb->prepare("SELECT remote_category FROM $feedTable WHERE id=%d", [$feed_id]);
        $marketcodeRaw = $wpdb->get_row($feedsql);
        if($marketcodeRaw->remote_category=='listingloader'){
            $table         = $wpdb->prefix . "amwscp_amazon_accounts";
            $sql           = $wpdb->prepare("SELECT * FROM $table WHERE is_valid = %d", [1]);
            $credentials   = $wpdb->get_results($sql);
        }else{
            $marketcode    = explode('_', $marketcodeRaw->remote_category);
            $marketcode    = isset($marketcode[1]) ? $marketcode[1] : '';
            $table         = $wpdb->prefix . "amwscp_amazon_accounts";
            $sql           = $wpdb->prepare("SELECT * FROM $table WHERE market_code = %s AND is_valid = %d", [$marketcode, 1]);
            $credentials   = $wpdb->get_results($sql);
            if(is_array($credentials) && count($credentials)<=0){
                $sql           = $wpdb->prepare("SELECT * FROM $table WHERE is_valid = %d", [1]);
                $credentials   = $wpdb->get_results($sql);
            }
        }
        $saved_feed    = '';
        // update feed

        if ($id) {
            $saved_feed = new AMWSCPF_SavedFeed($id);
        }
        $this->view('upload-tab', [
            'action'      => $action,
            'id'          => $id,
            'credentials' => $credentials,
            'saved_feed'  => $saved_feed,
        ]);
        echo '<script type="text/javascript">feed_id = ' . $id . ';</script>';
    }

    public function account_page($id = "", $action = "")
    {
        require_once AMWSCPF_PATH . "/core/classes/tables/Account_Table.php";
        $class = new AMWS_Account_Table();
        $msg   = '';
        if (strlen($action) > 0) {
            switch ($action) {
                case 'delete':
                    $msg = $class->delete_account($id);
                    break;
                case 'default':
                    $msg = $class->default_mws_account($id);
                    break;
            }
        }
        $sites = $this->sites();
        //amwscpf_print_info();
        $this->view('account_main_page', [
            'class'       => $class,
            'marketplace' => $sites,
            'msg'         => $msg,
        ]);
    }

    public function getCountryFullname($code)
    {
        $code = strtoupper($code);
        switch ($code) {
            case 'CA':
                $result = "Canada";
                break;
            case 'AU':
                $result = "Australia";
                break;
            case 'US':
                $result = "United States";
                break;
            case 'UK':
                $result = "United Kingdom";
                break;
            case 'FR':
                $result = "France";
                break;
            case 'MX':
                $result = "Mexico";
                break;
            case 'DE':
                $result = "Denmark";
                break;
            case 'ES':
                $result = "Spain";
                break;
            case 'IT':
                $result = "Italy";
                break;
            case 'IN':
                $result = "India";
                break;
            default:
                $result = "United States";
        }
        return $result;
    }

    public function template_page($id = "", $action = "", $response = "")
    {
        require_once 'core/classes/tables/category-amazon-list.php';
        $lists                 = new AMWSCPF_Categories;
        $this->countryfullname = $this->getCountryFullname($lists->code);
        if ((strlen($action) > 0) && ($action == 'remove_template')) {
            $lists->delete_template($id);
        }

        $templates = $lists->getImportedTemplates($this->countryfullname);
        //amwscpf_print_info();
        $this->view('template_page', [
            'class'     => $lists,
            'templates' => $templates,
            'response'  => $response,
        ]);
    }

    public function report_page()
    {
        require_once 'core/classes/tables/FeedReport_Table.php';
        $feed = new AMWS_FeedReport_Table();
        //amwscpf_print_info();
        $this->view('feed_report_table', [
            'class' => $feed,
        ]);
    }

    public function tutorials_page()
    {
        $path = AMWSCPF_PATH . "/amazon-views/tutorial_page.php";
        require_once $path;

        //amwscpf_print_info();
        $view_obj = new View();
        $view_obj->tutorial_page_view();
        // $embed_code = wp_oembed_get('https://www.youtube.com/watch?v=QEHoUtlDN54&feature=youtu.be');
        // echo '<div class="cpf_tutorials_page" style="margin-top: 59px;">
        //         <div class="cpf_google_merchant_tutorials">
        //             <h2> ExportFeed : Amazon Marketplace Feed Creation Tutorials</h2>
        //         </div>'.$embed_code.'</div>';
    }

    public function orders_page()
    {
        require_once AMWSCPF_PATH . "/core/classes/amazon_main.php";
        $amazon = new CPF_Amazon_Main();
        $days   = false;
        $amazon->importOrders($days);

        //amwscpf_print_info();

        $this->view('order_main_page');
    }
    // view function to display the format
    public function view($insView, $inaData = array(), $response = "", $echo = true)
    {
        $sFile = dirname(__FILE__) . '/amazon-views/' . $insView . '.php';

        if (!is_file($sFile)) {
            echo "View not found: " . $sFile, 1, 1;
            return false;
        }

        if (count($inaData) > 0) {
            extract($inaData, EXTR_PREFIX_ALL, 'cpf');
        }

        ob_start();
        include $sFile;
        $sContents = ob_get_contents();
        ob_end_clean();

        if ($echo) {
            echo $sContents;

            return true;
        } else {
            return $sContents;
        }
    }

    public function verify_this_feed($id = null)
    {
        global $wpdb;
        if ($id === null) {
            $this->message['system'][] = 'Feed is not selected.';
        }
        $table           = $wpdb->prefix . "amwscp_feeds";
        $sql             = $wpdb->prepare("SELECT remote_category FROM $table WHERE id = %d", [$id]);
        $remote_category = $wpdb->get_var($sql);
        $this->feed_verification($id, $remote_category);
        if (count($this->message) == 0) {
            $verified = false;
        } else {
            $verified = $this->view('amwscpf-verify-feed-report', [
                'id' => $id,
            ], false);
        }
        return $verified;

    }

    public function feed_verification($id, $remote_category)
    {
        global $wpdb;
        $sql      = $wpdb->prepare("SELECT post_id,meta_value FROM $wpdb->postmeta WHERE meta_key = %s", ['_amwscpf_feed_data_' . $id]);
        $products = $wpdb->get_results($sql);

        if (count($products) == 0) {
            $this->message['system'][] = 'Feed contains no product lists. Open the feed and check the listing. If there is items on the feed then go back to manage feed section and update the feeds.';
            return false;
        }

        $tpl = $this->get_tpl_data($remote_category);

        foreach ($products as $prod) {
            $this_product = json_decode($prod->meta_value, true);
            $this->check_the_attributes($this_product, $tpl, $prod->post_id);
        }
    }

    public function get_tpl_data($remote_category)
    {
        global $wpdb;
        $table_template = $wpdb->prefix . "amwscp_amazon_templates";
        $table_values   = $wpdb->prefix . "amwscp_template_values";

        //first get tpl id
        $tpl_details = explode("_", $remote_category);
        $sql         = $wpdb->prepare("SELECT id FROM $table_template WHERE tpl_name = %s AND country = %s", $tpl_details);
        $tpl_id      = $wpdb->get_var($sql);
        if (!$tpl_id) {
            $this->message['feed'] = 'Feed contains no amazon template. It occurs if your feeds are not properly updated. Please try updating feeds once and then try to verify again.';
            return false;
        }

        // preparing the value for WHERE condition required in query below
        unset($tpl_details[0]); // unsetting first value since it contains templates name
        $tpl_details[] = $tpl_id; //tmpl_id
        $tpl_details[] = 1; // required or not

        // retreive required fields of template
        $sql        = $wpdb->prepare("SELECT * FROM $table_values WHERE country = %s AND tmpl_id=%d AND required =%d", $tpl_details);
        $tpl_values = $wpdb->get_results($sql);
        if (count($tpl_values) == 0) {
            $this->message['system'] = 'Template not selected in feed. Goto Manage Feed section update the feed once again. Make sure your Template is on the import list.';
            return false;
        }
        $new_tpl_format = [];

        foreach ($tpl_values as $key => $vals) {
            $new_tpl_format[$vals->fields] = [
                'definition'   => $vals->definition,
                'valid_values' => $vals->valid_values,
                'examples'     => $vals->examples,
            ];
        }
        return $new_tpl_format;
    }

    public function check_the_attributes($product, $tpl, $prod_id)
    {
        #echo '<pre>';print_r($tpl);die;
        foreach ($tpl as $field => $define) {
            if (!isset($product[$field])) {
                $this->message['feed'][$prod_id][] = [
                    'missing'    => $field,
                    'sugget'     => strlen($define['valid_values']) == 0 ? $define['examples'] : maybe_unserialize($define['valid_values']),
                    'definition' => $define['definition'],
                ];
            }
        }
    }

    public function get_feed_verification_response($verification, $id)
    {
        global $wpdb;
        $table = $wpdb->prefix . "amwscp_feeds";
        $data  = ['verified' => 1];
        if (!$verification) {
            $wpdb->update($table, $data, ['id' => $id]);
            return false;
        }
        $dir_path = AMWSCP_PFeedFolder::uploadFolder() . 'verification_report/';
        if (!is_dir($dir_path)) {
            mkdir($dir_path);
        }

        $filename = $dir_path . 'feed-id-' . $id . '-verification-report.txt';
        $handle   = fopen($filename, 'w');
        fwrite($handle, $verification);
        fclose($handle);
        return AMWSCP_PFeedFolder::uploadURL() . 'verification_report/' . 'feed-id-' . $id . '-verification-report.txt';

    }
}
