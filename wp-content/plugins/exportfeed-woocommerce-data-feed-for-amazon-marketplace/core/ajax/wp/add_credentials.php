<?php
if (!defined('ABSPATH')) exit;
require_once AMWSCPF_PATH . '/core/classes/amazon_main.php';
require_once AMWSCPF_PATH . '/core/classes/invoker.php';
set_include_path(dirname(__FILE__) . '/../../classes/Amazon/');
require_once 'MarketplaceWebServiceSellers/Client.php';
require_once 'MarketplaceWebServiceSellers/Model/ListMarketplaceParticipationsRequest.php';

Class Account_manager
{

    private $accountDetails = array();
    private $fillables = array('seller_id' => 'merchant_id', 'account_title' => 'title', 'mws_auth_token' => 'mws_auth_token', 'marketplace_id' => 'marketplace_id', 'site' => 'market_code', 'type' => 'type', 'account_id' => 'id', 'marketplace' => 'marketplace');
    private $response = array();
    private $table;
    private $aws;
    private $serviceUrl;

    public function __construct($params)
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $wpdb->prefix . 'amwscp_amazon_accounts';
        $this->setter($params);
    }

    public function getter($key)
    {
        if (is_array($this->accountDetails) && count($this->accountDetails) > 0)
            if (array_key_exists($key, $this->accountDetails))
                return $this->accountDetails[$key];
            else return false;
        else return false;
    }

    public function setter($params)
    {
        foreach ($params as $key => $param) {
            if (array_key_exists($key, $this->fillables)) {
                $this->accountDetails[$this->fillables[$key]] = sanitize_text_field($param);
            }
        }
        return $this->accountDetails;
    }

    public function add_account()
    {
        if (is_array($this->accountDetails) && count($this->accountDetails) > 0) {
            if ($this->getter('id') && $this->getter('id') !== 'false') {
                $this->save('update');
                if (!$this->db->last_error) {
                    $this->response['success'] = true;
                    $this->response['message'] = "Account updated successfully";
                }
                return true;
            } elseif ($this->getter('type')) {
                if ($this->performEscapeAccount()) {
                    $this->response['success'] = true;
                    $this->response['account_escape'] = true;
                }
            } else {
                delete_option('amwscp_escape_accountsetting');
                delete_option('amwscp_country_without_account_set');
                $this->aws = new CPF_Amazon_Main($this->accountDetails['market_code']);
                $this->accountDetails['access_key_id'] = $this->aws->aws_key;
                $this->accountDetails['secret_key'] = $this->aws->secret_key;
                $this->accountDetails['is_valid'] = 0;
                $awsResponse = $this->getCredentialsFromAmazon();
                if ($awsResponse === true) {
                    $this->response['success'] = false;
                    $check = $this->checkExistingAccount();
                    /*if ($check) {
                        echo "shit";
                        $currentAccount = $this->verifyAccount();
                        if ($currentAccount) {
                            $this->response['success'] = true;
                            $this->response['message'] = "The account with same credentials already exists. Thanks.";
                            return true;
                        }
                        $this->response['link'] = '<span style="color:red">' . $this->accountDetails['title'] . ' is almost saved. Would you like to make it default? <a href="?page=amwscpf-feed-account&action=default&id=' . $this->accountDetails['account_id'] . '">Yes</a> <a href="?page=amwscpf-feed-account">No</a></span>';
                        return true;
                    }*/
                    $this->accountDetails['active'] = $check ? 0 : 1;
                    if (empty($check)) {
                        $this->accountDetails['active'] = 1;
                        unset($this->accountDetails['id']);
                        $this->response['message'] = "Account saved, now you are ready for uplading the products.";
                        if ($this->save('insert')) {
                            $this->response['success'] = true;
                            return true;
                        } else {
                            $this->response['success'] = true;
                            $this->response['message'] = "Some problem occured, please try again later.";
                            return true;
                        }
                    } else {
                        $account_id = $this->accountDetails['id'];
                        //unset($this->accountDetails['id']);
                        $currentAccount = $this->verifyAccount();
                        if ($currentAccount) {
                            $this->response['success'] = true;
                            $this->response['message'] = "The account with same credentials already exists. Thanks.";
                            return true;
                        }
                        $this->response['message'] = "Account saved, now you are ready for uplading the products.";
                        if (!$this->db->last_error) {
                            $this->save('insert');
                            $this->response['link'] = '<span style="color:red">' . $this->accountDetails['title'] . ' is almost saved. Would you like to make it default? <a href="?page=amwscpf-feed-account&action=default&id=' . $this->db->insert_id . '">Yes</a> <a href="?page=amwscpf-feed-account">No</a></span>';
                            $this->response['success'] = true;
                            return true;
                        }
                    }
                } else {
                    $this->response['invalid'] = (strlen($awsResponse['message']) > 5) ? $awsResponse['message'] : "Something went wrong while varifying the account, please try again later.";
                    return true;
                }
            }
        } else {
            $this->response['message'] = "Account Details was not provided";
            return true;
        }
    }


    protected function verifyAccount()
    {
        $qry = $this->db->get_row($this->db->prepare("SELECT id FROM {$this->table} WHERE `merchant_id`=%s AND `mws_auth_token`=%s AND `marketplace_id`=%s", array($this->accountDetails['merchant_id'], $this->accountDetails['mws_auth_token'], $this->accountDetails['marketplace_id'])));
        if ($qry)
            return $qry->id;
        else {
            return false;
        }
    }

    public function getCredentialsFromAmazon()
    {
        $this->serviceUrl = $this->aws->serviceUrl($this->accountDetails['market_code']);
        $config = array(
            'ServiceURL' => $this->serviceUrl . "/Sellers/2011-07-01",
            'ProxyHost' => null,
            'ProxyPort' => -1,
            'ProxyUsername' => null,
            'ProxyPassword' => null,
            'MaxErrorRetry' => 3,
        );
        $service = new MarketplaceWebServiceSellers_Client(
            $this->accountDetails['access_key_id'],
            $this->accountDetails['secret_key'],
            $this->aws->application_name,
            $this->aws->application_version,
            $config
        );
        $request = new MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsRequest();
        $request->setSellerId($this->accountDetails['merchant_id']);
        $request->setMWSAuthToken($this->accountDetails['mws_auth_token']);

        $invoker = new CPF_Invoker();
        $submit = $invoker->invokeListMarketplaceParticipations($service, $request);
        if ($submit->success) {
            $allowed_markets = maybe_serialize($submit->allowed_markets);
            $this->accountDetails['allowed_markets'] = $allowed_markets;
            $this->accountDetails['is_valid'] = 1;
            return true;
        }
        return array('StatusCode' => $submit->StatusCode, 'message' => $submit->ErrorMessage);
    }

    private function save($option)
    {
        if ($option == 'insert') {
            return $this->db->insert($this->table, $this->accountDetails);
        } else {
            return $this->db->update($this->table, $this->accountDetails, array('id' => $this->accountDetails['id']));
        }
    }

    public function performEscapeAccount()
    {
        if ($a = get_option('amwscp_escape_accountsetting') && $b = get_option('amwscp_country_without_account_set')) {
            if ($a == '' && $b == '') {
                return true;
            } else {
                update_option('amwscp_escape_accountsetting', 'yes');
                update_option('amwscp_country_without_account_set', $this->accountDetails['marketplace']);
            }
        } else {
            add_option('amwscp_country_without_account_set', $this->accountDetails['marketplace']);
            add_option('amwscp_escape_accountsetting', 'yes');
        }
        return true;
    }

    public function checkExistingAccount()
    {
        $qry = $this->db->get_var("SELECT id FROM $this->table");
        return $qry;
    }

    public function responder()
    {
        wp_send_json_success($this->response);
        exit;
    }
}

$object = new Account_manager($_POST);
if ($object->add_account())
    $object->responder();
