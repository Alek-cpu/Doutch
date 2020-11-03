<?php
if (!defined('ABSPATH')) {
    exit;
}

if (defined('ENV') && ENV == true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
// Exit if accessed directly
require_once dirname(__FILE__) . '/Amazon/MarketplaceWebService/Samples/.config.inc.php';
include_once AMWSCPF_PATH . '/core/classes/invoker.php';

if (!class_exists('CPF_Amazon_Main')) {
    class CPF_Amazon_Main
    {
        public $aws_key = "AKIAIQ7TW4GLHVZJLCNQ"; // AWS_ACCESS_KEY_ID
        public $secret_key = "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"; // AWS_SECRET_ACCESS_KEY
        public $marketplace_key;
        public $mws_auth_token = null;
        public $seller_key = ""; // aka MERCHANT_ID
        public $application_name = "Exportfeed"; // APPLICATION_NAME
        public $application_version = "1.1.0"; // APPLICATION_VERSION
        public $site = ""; // COUNTRY CODE
        public $serviceUrl = "";
        public $no_account = false;
        public $email = "";
        public $checkFlag;

        /**  function __autoload($className) // deprecated
         * {
         * $filePath = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
         * $includePaths = explode(PATH_SEPARATOR, get_include_path());
         * foreach ($includePaths as $includePath) {
         * if (file_exists($includePath . DIRECTORY_SEPARATOR . $filePath)) {
         * require_once $filePath;
         * return;s
         * }
         * }
         */

        public function __construct($country = null)
        {
            if (in_array($country, array('UK', 'FR', 'DE', 'ES', 'IT'))) {
                $this->aws_key = "AKIAJUMS7TLKMYOZRU4Q";
                $this->secret_key = "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";
            }
            if (!$this->get_default_account()) {
                $this->no_account = true;
            }
        }

        public function initialize($id)
        {
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_amazon_accounts";
            $sql = $wpdb->prepare("SELECT * FROM $table WHERE id = %d", [$id]);
            $credential = $wpdb->get_row($sql);
            if (!empty($credential) && is_object($credential)) {
                if (in_array($credential->market_code, array('UK', 'FR', 'DE', 'ES', 'IT'))) {
                    if (isset($credential->access_key_id) && ($credential->access_key_id == 'AKIAJUMS7TLKMYOZRU4Q')) {
                        $this->aws_key = "AKIAJUMS7TLKMYOZRU4Q";
                        $this->secret_key = "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";
                    } else {
                        $this->aws_key = $credential->access_key_id;
                        $this->secret_key = $credential->secret_key;
                    }
                } else {
                    if (isset($credential->access_key_id) && ($credential->access_key_id != $this->aws_key)) {
                        $this->aws_key = $credential->access_key_id;
                        $this->secret_key = $credential->secret_key;
                    }
                }
                $this->seller_key = $credential->merchant_id;
                $this->marketplace_key = ['Id' => [$credential->marketplace_id]];
                $this->site = $credential->market_code;
                $this->serviceUrl = $this->serviceUrl($this->site);
                $this->email = $credential->title;
                $this->mws_auth_token = $credential->mws_auth_token;
            } else {
                return null;
            }

            return $this;
        }

        public function serviceUrl($site)
        {
            if ($site) {
                switch ($site) {
                    case 'US':
                        $serviceUrl = "https://mws.amazonservices.com";
                        break;
                    case 'MX':
                        $serviceUrl = "https://mws.amazonservices.com.mx";
                        break;
                    case 'UK':
                        $serviceUrl = "https://mws.amazonservices.co.uk";
                        break;
                    case 'FR':
                        $serviceUrl = "https://mws.amazonservices.fr";
                        break;
                    case 'IT':
                        $serviceUrl = "https://mws.amazonservices.it";
                        break;
                    case 'CA':
                        $serviceUrl = "https://mws.amazonservices.ca";
                        break;
                    case 'DE':
                        $serviceUrl = "https://mws.amazonservices.de";
                        break;
                    case 'ES':
                        $serviceUrl = "https://mws.amazonservices.es";
                        break;
                    case 'AU':
                        $serviceUrl = "https://mws.amazonservices.com.au";
                        break;
                    case 'IN':
                        $serviceUrl = "https://mws.amazonservices.in";
                        break;
                }
                return $serviceUrl;
            }
        }

        public function submitService()
        {
            if ($this->serviceUrl) {
                require_once 'MarketplaceWebService/Client.php';
                $config = array(
                    'ServiceURL' => $this->serviceUrl,
                    'ProxyHost' => null,
                    'ProxyPort' => -1,
                    'MaxErrorRetry' => 3,
                );

                $service = new MarketplaceWebService_Client(
                    $this->aws_key,
                    $this->secret_key,
                    $config,
                    $this->application_name,
                    $this->application_version
                );

                return $service;
            } else {
                error_log("No Service Url for current Account");
                return false;
            }
            return false;
        }

        public function getCredentials($id)
        {
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_amazon_accounts";
            $sql = $wpdb->prepare("SELECT * FROM $table WHERE id= %d", [$id]);
            $credential = $wpdb->get_row($sql);
            return $credential;
        }

        public function upload_from_product($file)
        {
            $feedType = '_POST_FLAT_FILE_LISTINGS_DATA_';

            $acnt = $this->get_default_account();
            $this->initialize($acnt->id);
            set_include_path(AMWSCPF_PATH . '/core/classes/Amazon/');
            require_once 'MarketplaceWebService/Model/SubmitFeedRequest.php';
            $feed = file_get_contents($file);
            $service = $this->submitServive();
            $feedhandle = @fopen('php://temp', 'rw+');
            fwrite($feedhandle, $feed);
            rewind($feedhandle);
            $parameters = array(
                'Merchant' => property_exists($this, 'seller_key') ? $this->seller_key : null,
                'MarketplaceIdList' => property_exists($this, 'marketplace_key') ? $this->marketplace_key : null,
                'FeedType' => $feedType,
                'FeedContent' => $feedhandle,
                'PurgeAndReplace' => false,
                'ContentMd5' => base64_encode(md5(stream_get_contents($feedhandle), true)),
            );
            rewind($feedhandle);
            $request = new MarketplaceWebService_Model_SubmitFeedRequest($parameters);
            $invoker = new CPF_Invoker();
            $submit = $invoker->invokeSubmitFeed($service, $request);
            $submit->account = $acnt->id;
            return $submit;
        }

        public function get_default_account()
        {
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_amazon_accounts";
            $sql = $wpdb->prepare("SELECT * FROM $table WHERE active = %d", [1]);
            $account = $wpdb->get_row($sql);
            return $account;
        }

        public function default_mws_account($id)
        {
            global $wpdb;
            $result = 'Cannot Update';
            $table = $wpdb->prefix . "amwscp_amazon_accounts";
            $data = array('active' => 1);
            $wpdb->query("UPDATE $table SET active = 0");

            if ($wpdb->update($table, $data, array('id' => $id))) {
                $result = 'Default account selected. Import templates for this account now. <a href="?page=amwscpf-feed-template">Yes</a>?';
            }
            return $result;
        }

        public function setup_1()
        {
            $url = admin_url('admin.php?page=amwscpf-amazon-configure');
            $alt = 'You need to upload you feed to amazon? In order to do so, you need an account. Goto Account Page.';
            if ($this->check_if_any_account_is_created()) {
                return '<strong>IMPORTANT:</strong> Your Account Setup is not completed. Create new Account.<a alt="' . $alt . '"  style="margin-top:12px;" href="' . $url . '" class="add-new-h2">Create Account</a> and make it default.' . '<br>';
            }

            return '';
        }

        public function setup_2()
        {
            $url = admin_url('admin.php?page=amwscpf-amazon-configure&tab=categories');
            if (!$this->check_if_any_template_is_imported()) {
                return '<strong>IMPORTANT:</strong> Setup the templates for the feeds. <a style="margin-top:12px;" class="add-new-h2" href="' . $url . '">Import Templates</a>' . '<br>';
            }

            return '';
        }

        public function check_if_any_account_is_created($default = false)
        {
            $where = "";
            if ($default) {
                $where = ' WHERE active = 1';
            }
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_amazon_accounts";
            $res = $wpdb->get_var("SELECT count(id) FROM $table" . $where);
            return $res;
        }

        public function check_if_any_template_is_imported()
        {
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_amazon_templates";
            $res = $wpdb->get_var("SELECT * FROM $table");
            return $res;
        }

        public function checkifFeedisGenerated()
        {
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_feeds";
            $res = $wpdb->get_var("SELECT * FROM $table");
            return $res;
        }

        public function getMarketplace($id)
        {
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_amazon_accounts";
            $sql = $wpdb->prepare("SELECT allowed_markets FROM $table WHERE id = %d", [$id]);
            $allowed_markets = $wpdb->get_var($sql);
            return $allowed_markets;
        }

        public function importOrders($days = 0, $cron = false)
        {
            if ($cron == true) {
                if ($days <= 0) {
                    $days = 5;
                }
                $account = self::get_default_account();
                if (is_object($account) && property_exists($account, 'id')) {
                    $this->initialize($account->id);
                    $marketplace = maybe_unserialize($this->getMarketplace($account->id));
                    if (!is_array($marketplace)) {
                        if (property_exists($this, 'marketplace_key') && isset($this->marketplace_key['Id']['0'])) {
                            $marketplace = $this->marketplace_key['Id']['0'];
                        }
                    }
                    $this->updateShippingCost($account->id);
                    $this->updateIncomepleOrderStatus($account->id);
                    $from_date = $account->last_ordered;
                    $orders = $this->summonTheOrders($marketplace, $from_date, $days);
                    if ($orders) {
                        foreach ($orders as $order) {
                            $items = $this->list_items($order->AmazonOrderId);
                            $items = maybe_serialize($items);
                            $this->saveOrder($order, $items, $cron = false);
                        }
                        // update last updates
                        //$this->updateLastUpdatedOrder($account->id);
                        // create woocommerce order
                        $amazonorder = $this->create_amazon_order();

                        if ($amazonorder && count($orders) > 0) {
                            return count($orders);
                        } else {
                            return false;
                        }
                    } else {
                        $dir = wp_upload_dir();
                        $upload_die = $dir['basedir'];
                        $myfile = fopen($upload_die . "/Orderlog.txt", "w") or die("Unable to open file!");
                        $txt = "No order found when fetched on " . date("Y/m/d") . "\n";
                        fwrite($myfile, $txt);
                        fclose($myfile);
                    }

                    /*************************************************************************************/
                    #May need for future use
                    /*************************************************************************************/

                    /**$dir = wp_upload_dir();
                     * $upload_die = $dir['basedir'];
                     * $myfile = fopen($upload_die . "/newfile.txt", "w") or die("Unable to open file!");
                     * $txt = "came into file twice";
                     * fwrite($myfile, $txt);
                     * fclose($myfile);
                     * $args = array(
                     * 'category' => array('Clothing'),
                     * );
                     * $products = wc_get_products($args);
                     * $pn = $products[0]->get_name();
                     * $dir = wp_upload_dir();
                     * $upload_die = $dir['basedir'];
                     * $myfile = fopen($upload_die . "/newfile.txt", "w") or die("Unable to open file!");
                     * $txt = $pn;
                     * fwrite($myfile, $txt);
                     * fclose($myfile);
                     * $days = 30;
                     * $account = self::get_default_account();
                     * if ($account) {
                     * $marketplace = maybe_unserialize($this->getMarketplace($account->id));
                     * if (!is_array($marketplace)) {
                     * if (isset($this->maretplace_key['Id']['0'])) {
                     * $marketplace = $this->maretplace_key['Id']['0'];
                     * }
                     * }
                     * $this->initialize($account->id);
                     * $from_date = $account->last_ordered;
                     * $orders = $this->summonTheOrders($marketplace, $from_date, $days);
                     * if ($orders) {
                     * foreach ($orders as $order) {
                     * $items = $this->list_items($order->AmazonOrderId);
                     * $items = maybe_serialize($items);
                     *
                     * $this->saveOrder($order, $items, $cron);
                     * }
                     * // update last updates
                     * $this->updateLastUpdatedOrder($account->id);
                     * $result = null;
                     * if (!class_exists('AMWSCP_CustomOrder')) {
                     * include plugin_dir_path(__FILE__) . '../../custom-woo-order.php';
                     * $wobj = new AMWSCP_CustomOrder();
                     * $result = $wobj->create_amazon_orderByCron();
                     * if ($result == true) {
                     * $createorder = $wobj->create_woocommerce_orderHook();
                     * }
                     * }
                     * if ($result == true) {
                     * return true;
                     * }
                     * return false;
                     * }
                     */

                    /**************************************************************************************/

                }

            } else {
                $account = self::get_default_account();
                if (is_object($account) && property_exists($account, 'id')) {
                    $this->initialize($account->id);
                    $marketplace = maybe_unserialize($this->getMarketplace($account->id));
                    if (!is_array($marketplace)) {
                        if (isset($this->marketplace_key['Id']['0'])) {
                            $marketplace = $this->marketplace_key['Id']['0'];
                        }
                    }
                    $from_date = $account->last_ordered;
                    if ($days > 0) {
                        $this->updateShippingCost($account->id);
                        $this->updateIncomepleOrderStatus($account->id);
                        $orders = $this->summonTheOrders($marketplace, $from_date, $days);
                        if ($orders) {
                            foreach ($orders as $order) {
                                $items = $this->list_items($order->AmazonOrderId);
                                $items = maybe_serialize($items);
                                $this->saveOrder($order, $items, $cron = false);
                            }
                            // update last updates
                            $this->updateLastUpdatedOrder($account->id);

                            /*******************************************************************************/

                            /*  if($cron==true){
                                include plugin_dir_path(__FILE__).'../../custom-woo-order.php';
                                $wobj = new AMWSCP_CustomOrder();
                                $result = $wobj->create_amazon_orderByCron();
                                if($result==true){
                                return true;
                                }
                                return false;
                                exit;
                                // $product_id = $wobj->getItemBySKU('MUSICADD58');
                            */

                            /**********************************************************************************/

                            // create woocommerce order
                            $amazonorder = $this->create_amazon_order();

                            if ($amazonorder && count($orders) > 0) {
                                return count($orders);
                            } else {
                                return false;
                            }
                        }
                    }
                }
            }
        }

        public function updateShippingCost($accountID)
        {
            /*global $wpdb;
            $table = $wpdb->prefix.'amwscp_orders';
            $wpdb->delete($table,array('order_id'=>'171-7709755-1130754'));*/

            $ShippedOrderData = $this->getShippedOrderData();
            if (is_array($ShippedOrderData) && count($ShippedOrderData) > 0) {
                foreach ($ShippedOrderData as $key => $value) {
                    $shippingDetails = maybe_unserialize($value->items);

                    try {
                        $shippingDetails = $shippingDetails[0];
                    } catch (Exception $e) {
                        $shippingDetails = array();
                    }

                    if ($accountID != $value->account_id) {
                        continue;
                    } else {
                        $WooOrder = wc_get_order($value->post_id);
                        if (is_object($WooOrder)) {
                            if (count($WooOrder->get_items('shipping')) <= 0) {
                                if (class_exists('WC_Order_Item_Shipping')) {
                                    if ($shippingDetails->ShippingPrice->Amount) {
                                        $vat = get_option('amwscp_custom_vat_amount') ? get_option('amwscp_custom_vat_amount') : 20;
                                        $shipping = new WC_Order_Item_Shipping();
                                        $shipping->set_method_title("Amazon shipping rate");
                                        $shipping->set_method_id("amazon_flat_rate:77"); // set an existing Shipping method rate ID
                                        $shipping->set_total($this->getShippingValueWithoutVat($shippingDetails->ShippingPrice->Amount, $vat));
                                        $WooOrder->add_item($shipping);
                                        $WooOrder->calculate_totals();
                                        $WooOrder->save();
                                    }
                                }
                            }
                        }

                    }
                }

            }
        }

        public function updateIncomepleOrderStatus($accountID)
        {
            $IncomepleOrderData = $this->getIncompleteOrderData();
            if (is_array($IncomepleOrderData) && count($IncomepleOrderData) > 0) {
                foreach ($IncomepleOrderData as $key => $value) {
                    if (!empty($value)) {

                        $shippingDetails = maybe_unserialize($value->items);
                        try {
                            $shippingDetails = isset($shippingDetails[0])?$shippingDetails[0]:array();
                        } catch (Error $e) {
                            $shippingDetails = array();
                        }
                        global $wpdb;
                        $table = $wpdb->prefix . "amwscp_orders";
                        $data = array(
                            'updated_date' => date('Y-m-d h:i:sa'),
                        );
                        $wpdb->update($table, $data, ['order_id' => $value->order_id]);

                        if ($accountID != $value->account_id) {
                            continue;
                        } else {
                            $OrderData = $this->getRecentOrderStatus($value->order_id);

                            if (is_array($OrderData) && $OrderData['status']) {
                                $wooOrderStatus = $this->getWooOrderStatusFromAmazonStatus($OrderData['status']);
                                if ($wooOrderStatus) {
                                    $WooOrder = wc_get_order($value->post_id);
                                    if (is_object($WooOrder)) {
                                        $WooOrder->set_address($OrderData['shipping'], 'shipping');
                                        if ($WooOrder->set_address($OrderData['billing'], 'billing') !== false) {
                                            if (count($WooOrder->get_items('shipping')) <= 0) {
                                                if (class_exists('WC_Order_Item_Shipping')) {
                                                    if ($shippingDetails->ShippingPrice->Amount) {
                                                        $vat = get_option('amwscp_custom_vat_amount') ? get_option('amwscp_custom_vat_amount') : 20;
                                                        $shipping = new WC_Order_Item_Shipping();
                                                        $shipping->set_method_title("Amazon shipping rate");
                                                        $shipping->set_method_id("amazon_flat_rate:77"); // set an existing Shipping method rate ID

                                                        /* * $shipping->set_total($item->ShippingPrice->Amount); // (optional)
                                                         $shipping->set_taxes(['total' => [0]]);
                                                         $shipping->set_total_tax(0);*/

                                                        $shipping->set_total($this->getShippingValueWithoutVat($shippingDetails->ShippingPrice->Amount, $vat));
                                                        //$item->calculate_taxes($calculate_tax_for);
                                                        $WooOrder->add_item($shipping);
                                                        $WooOrder->calculate_totals();
                                                    }
                                                }
                                            }

                                            $WooOrder->save();
                                            if ($WooOrder->update_status($wooOrderStatus) == false) {
                                                error_log("Order status Of orderID {$value->order_id} with state {$value->status} could not be updated to {$wooOrderStatus} because of some internal error");
                                            } else {
                                                global $wpdb;
                                                $table = $wpdb->prefix . "amwscp_orders";
                                                $data = array(
                                                    'status' => $OrderData['status'],
                                                    'updated_date' => date('Y-m-d h:i:sa'),
                                                );
                                                $wpdb->update($table, $data, ['order_id' => $value->order_id]);
                                            }
                                        } else {
                                            if ($WooOrder->update_status($wooOrderStatus) == false) {
                                                error_log("Order status Of orderID {$value->order_id} with state {$value->status} coulod not be updated to {$wooOrderStatus} because of some internal error");
                                            } else {
                                                global $wpdb;
                                                $table = $wpdb->prefix . "amwscp_orders";
                                                $data = array(
                                                    'status' => $OrderData['status'],
                                                    'updated_date' => date('Y-m-d h:i:sa'),
                                                );
                                                $wpdb->update($table, $data, ['order_id' => $value->order_id]);
                                            }
                                            $message = "Could not Set or Update the Shipping and Billing Address of order with ID {$value->order_id}";
                                            $this->SaveLog($message);
                                        }
                                    } else {
                                        error_log("Could not fetch the status of order with ID {$value->order_id}");
                                    }

                                } else {
                                    error_log("Could not fetch the status of order with ID {$value->order_id}");
                                }
                            } else {
                                $wooOrderStatus = $this->getWooOrderStatusFromAmazonStatus($value->status);
                                $WooOrder = wc_get_order($value->post_id);
                                if ($WooOrder) {
                                    wp_update_post(['ID' => $value->post_id, 'post_status' => $wooOrderStatus]);
                                    //$WooOrder->update_status($wooOrderStatus);
                                    //print_r($OrderData);
                                }
                            }
                        }

                    }
                }
            } else {
                error_log("No orders with order state other than Shipped or Cancelled");
            }
            return true;
        }

        public
        function getWooOrderStatusFromAmazonStatus($status)
        {
            if ($assigned = get_option('amwscp_' . str_replace(' ', '_', $status))) {
                return $assigned;
            } else {
                switch ($status) {
                    case 'Unshipped':
                        $result = "processing";
                        break;
                    case 'PartiallyShipped':
                        $result = 'processing';
                        break;
                    case 'Shipped':
                        $result = 'completed';
                        break;
                    case 'Refund applied':
                        $result = 'Refunded';
                        break;
                    case 'Canceled':
                        $result = "Cancelled";
                        break;
                    default:
                        $result = "processing";
                }

                return $result;
            }
        }

        public
        function SaveLog($message)
        {
            $dir = wp_upload_dir();
            $upload_dir = $dir['basedir'];
            $logFile = $upload_dir . '/logs/Orderimportlog' . date("j.n.Y") . '.txt';
            if (!file_exists($logFile)) {
                mkdir($upload_dir . '/logs');
            }
            file_put_contents($logFile, $message . "\n", FILE_APPEND);
        }

        public function getIncompleteOrderData()
        {
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_orders";
            $sql = $wpdb->prepare("SELECT * FROM $table WHERE status <> %s AND status <> %s order by updated_date ASC LIMIT 15 ", ['Shipped', 'Canceled ']);
            $selectedOrders = $wpdb->get_results($sql);
            if (is_array($selectedOrders) && count($selectedOrders) > 0) {
                return $selectedOrders;
            }
            return null;
        }

        public function getShippedOrderData()
        {
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_orders";
            $sql = $wpdb->prepare("SELECT order_id,post_id,account_id,items,status,sync_state,date_created,updated_date FROM $table WHERE status = %s order by updated_date DESC LIMIT 15 ", ['Shipped']);
            $selectedOrders = $wpdb->get_results($sql);
            if (is_array($selectedOrders) && count($selectedOrders) > 0) {
                return $selectedOrders;
            }
            return null;
        }

        public function getRecentOrderStatus($id)
        {
            $action = 'GetOrder';
            $section = 'Orders';
            $version = '2013-09-01';
            $params = ['AmazonOrderId.Id.1' => $id];
            $result = $this->sendSignedRequest($action, $section, $params, $version);

            if (is_object($result)) {
                $BillingAddress = $this->getBillingAddress($result->GetOrderResult->Orders->Order);
                $ShippingAddress = $this->getShippingAddress($result->GetOrderResult->Orders->Order);
                $result = $result->GetOrderResult->Orders->Order->OrderStatus;
                $resultString = array((string)$result);
                return array('status' => $resultString[0], 'billing' => $BillingAddress, 'shipping' => $ShippingAddress);
            }
            return $result;

        }

        public function updateOrder($orderid, $items)
        {
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_orders";
            $data = [
                'items' => $items,
            ];
            $wpdb->update($table, $data, ['order_id' => $orderid]);
        }

        public function updateOrderData($order_id, $data)
        {
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_orders";
            $wpdb->update($table, $data, ['order_id' => $order_id]);
            if ($wpdb->last_error) {
                return false;
            }
            return true;
        }

        public function list_items($id)
        {
            $action = 'ListOrderItems';
            $section = 'Orders';
            $version = '2013-09-01';
            $params = ['AmazonOrderId' => $id];

            $result = $this->sendSignedRequest($action, $section, $params, $version);
            if ($result) {
                $result = json_decode(json_encode($result));
                if (isset($result->ListOrderItemsResult->OrderItems->OrderItem)) {
                    if (is_array($result->ListOrderItemsResult->OrderItems->OrderItem)) {
                        $items = $result->ListOrderItemsResult->OrderItems->OrderItem;
                    } else {
                        $items = [$result->ListOrderItemsResult->OrderItems->OrderItem];
                    }
                    return $items;
                } elseif (isset($result->ListOrderItemsResult->OrderItems)) {
                    return array();
                }
            }
            return $result;
        }

        public function order_id_exists($id)
        {
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_orders";
            $sql = $wpdb->prepare("SELECT id FROM $table WHERE order_id = %s", [$id]);
            $order = $wpdb->get_var($sql);
            if ($order) {
                return true;
            } else {
                return false;
            }
        }

        public function saveOrder($order, $items, $cron)
        {
            global $wpdb;
            $account = $this->get_default_account();
            $table = $wpdb->prefix . "amwscp_orders";
            $data = [
                'order_id' => $order->AmazonOrderId,
                'date_created' => $order->PurchaseDate,
                'items' => $items,
                'status' => $order->OrderStatus,
                'account_id' => property_exists($account, 'id') ? $account->id : null,
                'sync_state' => 'INCOMPLETE',
                'data' => maybe_serialize($order),
                // 'woo_order_created' => '0'
            ];
            if ($cron == true) {
                $data['fetchedby'] = '_CRON_';
            } else {
                $data['fetchedby'] = '_MANUAL_';
            }

            if ($this->order_id_exists($order->AmazonOrderId)) {
                unset($data['sync_state']);
                $wpdb->update($table, $data, ['order_id' => $order->AmazonOrderId]);
            } else {
                $data['woo_order_created'] = '0';
                $wpdb->insert($table, $data);
            }
        }

        public function create_amazon_order()
        {
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_orders";
            $sql = $wpdb->prepare("SELECT * FROM $table WHERE sync_state = %s AND woo_order_created = %s", ['INCOMPLETE', '0']);
            $orders = $wpdb->get_results($sql);

            if (count($orders) > 0) {
                foreach ($orders as $key => $order) {
                    $checkifOrderExists = $this->checkWoocommerceOrderById($order->post_id);
                    if ($order->woo_order_created == 0 && $checkifOrderExists == false) {
                        $post_id = $this->create_woocommerce_order($order);
                        if ($post_id) {
                            $data = ['sync_state' => 'COMPLETED', 'post_id' => $post_id, 'woo_order_created' => '1'];
                            $wpdb->update($table, $data, ['id' => $order->id]);
                        }
                    }
                    // Commented the code below because we don't want to update the orders that are not inserted in woocommerce order
                    /*else {
                        $data = ['sync_state'=>'COMPLETED'];
                        $wpdb->update($table,$data,['id'=>$order->id]);
                    */
                }
            }
        }

        /*
                                 * @Info : Check if order exists in woocommerce
                                 * @returns : bool(true|false)
        */
        public function checkWoocommerceOrderById($orderID)
        {
            if (function_exists('wc_get_order')) {
                $ord = wc_get_order($orderID);
                if (is_object($ord)) {
                    if ($ord->get_id()) {
                        return true;
                    }
                }
                return false;
            }
            error_log("Fucntion wc_get_order is not available at the time of execution at amazon_main.");
            return false;
        }

        /*
         * @param : woocommerce Order ID
         * @return bool|int
        */
        public
        function update_woocommerce_orderStatus($id, $status)
        {
            if (function_exists('wc_get_order')) {
                $ord = wc_get_order($id);
                if (is_object($ord)) {
                    $ord->update_status($status);
                }
                return false;
            }
            error_log("Fucntion wc_get_order is not available at the time of execution at amazon_main.");
            return "Fucntion doesn't exists";
        }

        /**
         * @param $order
         * @return bool|int
         */
        public function create_woocommerce_order($order)
        {
            global $woocommerce;
            $order_detail = maybe_unserialize($order->data);

            $billingAddress = $this->getBillingAddress($order_detail);
            $shippingAddress = $this->getShippingAddress($order_detail);

            $order_status = $order_detail->OrderStatus;

            /*=============================================================================*/

            /** @INFO : Deprecated code, later version will use mapping structure
             * if ($order_detail->OrderStatus == 'Unshipped') {
             * $order_status = "processing";
             * } elseif ($order_detail->OrderStatus == 'PartiallyShipped') {
             * $order_status = "processing";
             * } elseif ($order_detail->OrderStatus == 'Shipped') {
             * $order_status = "completed";
             * } elseif ($order_detail->OrderStatus == "Refund applied") {
             * $order_status = "refunded";
             * } elseif ($order_detail->OrderStatus == "Canceled") {
             * $order_status = "cancelled";
             * } elseif ($order_detail->OrderStatus == "Completed") {
             * $order_status = "completed";
             * } else {
             * $order_status = $order_detail->OrderStatus;
             * }
             *
             * ================================================================================*/

            if (!($order_status = get_option('amwscp_' . str_replace(' ', '_', $order_status)))) {
                if (!($order_status = get_option('amwscp_universal_status'))) {
                    $order_status = $order_detail->OrderStatus;
                }
            }

            $items = maybe_unserialize($order->items);
            if (is_array($items)) {
                $ord = null;
                $ord_id = null;

                foreach ($items as $item) {
                    $this->checkFlag = true;
                    $item_id = $this->getItemBySKU($item->SellerSKU);
                    if ($item_id == false) {
                        $this->checkFlag = false;
                    }
                }
                if ($this->checkFlag == true) {
                    $ord = wc_create_order();
                    $ord_id = $ord->get_id();
                }
                foreach ($items as $item) {
                    $item_id = $this->getItemBySKU($item->SellerSKU);
                    if ($item_id !== false) {
                        if ($woocommerce != null) {
                            // making version backward compatibilty
                            $wc_version = explode('.', $woocommerce->version);
                            if (($wc_version[0] <= 2)) {
                                $product = get_product($item_id); //WooCommerce - get product by id
                            } else {
                                $product = wc_get_product($item_id); // woocommerce new hook for getting product by id
                            }
                        }
                        if ($ord != null) {
                            $ord->add_product($product, $item->QuantityOrdered);

                            if (isset($item->ShippingPrice->Amount)) {

                                if (count($ord->get_items('shipping')) <= 0) {
                                    if (class_exists('WC_Order_Item_Shipping')) {
                                        $vat = get_option('amwscp_custom_vat_amount') ? get_option('amwscp_custom_vat_amount') : 20;
                                        $shipping = new WC_Order_Item_Shipping();
                                        $shipping->set_method_title("Amazon shipping rate");
                                        $shipping->set_method_id("amazon_flat_rate:77"); // set an existing Shipping method rate ID

                                        /* * $shipping->set_total($item->ShippingPrice->Amount); // (optional)
                                         $shipping->set_taxes(['total' => [0]]);
                                         $shipping->set_total_tax(0);*/

                                        $shipping->set_total($this->getShippingValueWithoutVat($item->ShippingPrice->Amount, $vat));
                                        //$item->calculate_taxes($calculate_tax_for);
                                        $ord->add_item($shipping);
                                        $ord->calculate_totals();
                                    }
                                }
                            }

                            /**
                             * @INFO: It seems that woocommerce order has started managing order itself
                             *  first id product second is qty
                             */
                            //$this->manage_stock($item_id, $item->QuantityOrdered, $order_status);

                        } else {
                            $this->checkFlag = false;
                        }
                    }

                    if (isset($ord) && $ord_id !== null && isset($ord_id)) {
                        $ord->set_address($shippingAddress, 'shipping');
                        $ord->set_address($billingAddress, 'billing');

                        $ord->calculate_totals();
                        $wo_order_status = wc_get_order($ord_id);
                        /*Added for changing payment status*/
                        if ($order_status == "Completed") {
                            // "completed" updated status for paid Orders with all others payment methods
                            $wo_order_status->update_status('completed');
                        } else {
                            $wo_order_status->update_status($order_status);
                        }

                        /*Removed as it was causing problem for updating the payment status. Instead of this,the above code was addded*/
                        // $ord->update_status($order_status, "Amazon Feed Order", TRUE);
                        return $ord_id;
                    }
                }
                return false;
            }
            return false;
        }

        public
        function getShippingValueWithoutVat($value, $percentage)
        {
            $actual_value = ($value * 100) / (100 + $percentage);
            if ($actual_value) return $actual_value;
            return 0;
        }

        public
        function manage_stock($id, $qty, $status)
        {
            // manage stock
            $is_stock = get_post_meta($id, '_manage_stock');
            if ($is_stock[0] == 'yes') {
                $stock = get_post_meta($id, '_stock');
                if ($status != "Refunded" || $status != "Cancelled") {
                    $qty = $stock[0] - $qty;
                    update_post_meta($id, '_stock', $qty);
                }
            }
        }

        public
        function summonTheOrders($marketplace, $from_date = false, $days = false)
        {
            $action = 'ListOrders';
            $section = 'Orders';
            $version = '2013-09-01';
            $params = [];

            if ($from_date) {
                $params['LastUpdatedAfter'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", strtotime($from_date . ' UTC') + 0);
            }
            if (!$from_date && !$days) {
                $days = 1;
            }
            $params['MarketplaceId.Id.1'] = $marketplace;
            if (is_array($marketplace)) {
                $i = 1;
                foreach ($marketplace as $key => $item) {
                    $params['MarketplaceId.Id.' . $i] = $item->MarketplaceId;
                    $i++;
                }
            } else {
                $params['MarketplaceId.Id.1'] = $marketplace;
            }
            if ($days) {
                $params['LastUpdatedAfter'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time() - $days * 24 * 3600);
            }
            $result = $this->sendSignedRequest($action, $section, $params, $version);
            if ($result) {
                $result = json_decode(json_encode($result));
                if (isset($result->ListOrdersResult->Orders->Order)) {
                    $orders = $result->ListOrdersResult->Orders->Order;
                    if (is_object($orders)) {
                        $orders = array($orders);
                    }

                    $nextToken = isset($result->ListOrderResult->NextToken) ? $result->ListOrderResult->NextToken : false;
                    while ($nextToken) {
                        $result = $this->sendSignedRequest('ListOrdersByNextToken', $section, $params, $version);
                        $result = json_decode(json_encode($result));
                        if (isset($result->ListOrdersByNextTokenResult->Orders->Order)) {
                            $next_orders = $result->ListOrdersByNextTokenResult->Orders->Order;
                            if (is_object($next_orders)) {
                                $next_orders = array($next_orders);
                            }
                            // merge orders array
                            $orders = array_merge($orders, $next_orders);
                        }
                        $nextToken = isset($result->ListOrdersByNextTokenResult->NextToken) ? $result->ListOrdersByNextTokenResult->NextToken : false;
                    }
                    return $orders;
                } elseif (isset($result->ListOrdersResult->Orders)) {
                    // empty orders
                    return false;
                }
                return false;
            }
            return $result;
        }

        // Converting amazon MWS scratchpad queries to API calls
        public
        function sendSignedRequest($action, $section = false, $params = array(), $version)
        {
            $api_section = $section ? $section . '/' . $version : '';
            $base_params = [
                'AWSAccessKeyId' => $this->aws_key,
                'Action' => $action,
                'SellerId' => $this->seller_key,
                'SignatureMethod' => "HmacSHA256",
                'SignatureVersion' => "2",
                'Timestamp' => gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time()),
                'Version' => $version,
            ];
            if ($this->mws_auth_token) {
                $base_params['MWSAuthToken'] = $this->mws_auth_token;
            }
            $params = array_merge($base_params, $params);

            // Sort the URL parameters
            $url_parts = [];
            foreach (array_keys($params) as $key) {
                $url_parts[] = $key . '=' . str_replace('%7E', '~', rawurlencode($params[$key]));
            }

            sort($url_parts);

            // Construct the string to sign
            $url_string = implode("&", $url_parts);
            $string_to_sign = "GET\n" . str_replace('https://', '', $this->serviceUrl) . "\n/$api_section\n" . $url_string;
            // sign the request
            $signature = hash_hmac("sha256", $string_to_sign, $this->secret_key, true);

            // Base64 encode the signature and make it URL safe
            $signature = urlencode(base64_encode($signature));
            $url = $this->serviceUrl . '/' . $api_section . '?' . $url_string . "&Signature=" . $signature;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            // If you are having problems, try adding this to the end of the curl-setopt block:
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $curlinfo = curl_getinfo($ch);
            curl_close($ch);

            if ($response == false) {
                return false;
            } else {
                $xml = simplexml_load_string($response);
                if (isset($xml->Error)) {
                    return false;
                } elseif (isset($xml->GetOrderResult->Error)) {
                    return false;
                } elseif (isset($xml->ListOrderItemsResult->Error)) {
                    return false;
                } else {
                    return $xml;
                }
            }
        }

        public
        function updateLastUpdatedOrder($account_id)
        {
            global $wpdb;
            $table = $wpdb->prefix . "amwscp_amazon_accounts";
            $data = [
                'last_ordered' => date('Y-m-d H:i:s'),
            ];
            $wpdb->update($table, $data, ['id' => $account_id]);
        }

        public
        function getItemBySKU($sku)
        {
            error_log("On Sku");
            global $wpdb;
            $table = $wpdb->postmeta;
            $sql = $wpdb->prepare("SELECT post_id FROM $table WHERE meta_value = %s", [$sku]);
            $id = $wpdb->get_var($sql);
            if ($id) {
                return $id;
            }
            return false;
        }

        public
        function get_mock_order()
        {
            // deprecated: no longer in use
        }

        public
        function mock_order_items()
        {
            // deprecated: no longer in use
        }

        public
        function getBillingAddress($order_detail)
        {
            $name = array();
            if (property_exists($order_detail, 'BuyerName')) {
                $name = explode(' ', $order_detail->BuyerName);
                $this->BuyerEmail = isset($order_detail->BuyerEmail) ? $order_detail->BuyerEmail : 'N/A';
            } else {
                $name[0] = "Byuer first name not available";
                $name[1] = "Buyer last name not available";
            }
            if (property_exists($order_detail, 'ShippingAddress')) {
                $shipping_details = $order_detail->ShippingAddress;
            } else {
                $shipping_details = null;
            }

            $billing_address = [
                'first_name' => isset($name[0]) ? (string)$name[0] : '',
                'last_name' => isset($name[1]) ? (string)$name[1] : '',
                'company' => 'N/A',
                'email' => isset($this->BuyerEmail) ? (string)$this->BuyerEmail : '',
                'phone' => isset($shipping_details->Phone) ? (string)$shipping_details->Phone : '',
                'address_1' => isset($shipping_details->AddressLine1) ? (string)$shipping_details->AddressLine1 : '',
                'address_2' => isset($shipping_details->AddressLine2) ? (string)$shipping_details->AddressLine2 : '',
                'city' => isset($shipping_details->City) ? (string)$shipping_details->City : '',
                'postcode' => isset($shipping_details->PostalCode) ? (string)$shipping_details->PostalCode : '',
                'country' => isset($shipping_details->CountryCode) ? (string)$shipping_details->CountryCode : '',
            ];

            return $billing_address;
        }

        public
        function getShippingAddress($order_detail)
        {
            $shipping_address = [];
            $shipping_details = null;
            if (property_exists($order_detail, 'ShippingAddress')) {
                $shipping_details = $order_detail->ShippingAddress;
                if (isset($shipping_details->Name)) {
                    $rawname = explode(' ', $shipping_details->Name);
                    $first_name = isset($rawname[0]) ? $rawname[0] : 'N/A';
                    $last_name = isset($rawname[1]) ? $rawname[1] : 'N/A';
                } else {
                    $first_name = 'First name not provided';
                    $last_name = 'Last name not provided';
                }
                $shipping_address = array(
                    'first_name' => $first_name,
                    'last_name' => isset($last_name) ? $last_name : 'N/A',
                    'company' => 'N/A',
                    'email' => isset($this->BuyerEmail) ? (string)$this->BuyerEmail : 'N/A',
                    'phone' => isset($shipping_details->Phone) ? (string)$shipping_details->Phone : 'N/A',
                    'address_1' => isset($shipping_details->AddressLine1) ? (string)$shipping_details->AddressLine1 : '',
                    'address_2' => isset($shipping_details->AddressLine2) ? (string)$shipping_details->AddressLine2 : '',
                    'city' => isset($shipping_details->City) ? (string)$shipping_details->City : 'N/A',
                    'postcode' => isset($shipping_details->PostalCode) ? (string)$shipping_details->PostalCode : '',
                    'country' => isset($shipping_details->CountryCode) ? (string)$shipping_details->CountryCode : '',
                    'state' => isset($shipping_details->StateOrRegion) ? (string)$shipping_details->StateOrRegion : '',
                );
            } else {
                $shipping_address = array(
                    'first_name' => 'value not available',
                    'last_name' => 'value not available',
                    'company' => 'N/A',
                    'email' => isset($this->BuyerEmail) ? (string)$this->BuyerEmail : '',
                    'phone' => isset($shipping_details->Phone) ? (string)$shipping_details->Phone : '',
                    'address_1' => isset($shipping_details->AddressLine1) ? (string)$shipping_details->AddressLine1 : '',
                    'address_2' => isset($shipping_details->AddressLine2) ? (string)$shipping_details->AddressLine2 : '',
                    'city' => isset($shipping_details->City) ? (string)$shipping_details->City : 'N/A',
                    'postcode' => isset($shipping_details->PostalCode) ? (string)$shipping_details->PostalCode : '',
                    'country' => isset($shipping_details->CountryCode) ? (string)$shipping_details->CountryCode : '',
                    'state' => isset($shipping_details->StateOrRegion) ? (string)$shipping_details->StateOrRegion : '',
                );
            }

            return $shipping_address;
        }
    }
}
