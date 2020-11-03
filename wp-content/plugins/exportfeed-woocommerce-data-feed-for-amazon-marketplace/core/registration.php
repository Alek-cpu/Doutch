<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!class_exists('AMWSCPF_License')) {
    class AMWSCPF_License
    {

        public $debugmode = false;
        public $error_message = '';
        public $results = array();
        public $valid = false;
        protected $strErrorMsgMain = '  You are using the free version (limited to 100 items per feed). Get the pro version or a free trial key at: <a target=\'_blank\' href = \'http://exportfeed.com/\'>exportfeed.com</a> ';

        protected $strLicenseKey = 'amwscpf_licensekey';
        protected $strLocalKey = 'amwscpf_localkey';
        protected $strLicenseKeyOld = 'purplexml_licensekey';
        protected $strLocalKeyOld = 'purplexml_localkey';
        protected $strRapidcartToken = 'cp_rapidcarttoken';

        function __construct($debug = false)
        {

            global $amwcore;

            $rapidToken = $amwcore->settingGet($this->strRapidcartToken);
            //When loading license key, we must be careful to check for valid licenses from prior versions
            $licensekey = $amwcore->settingGet($this->strLicenseKey);
            if (strlen($licensekey) == 0) {
                //Look for old version of key
                $licensekey = $amwcore->settingGet($this->strLicenseKeyOld);
                if (strlen($licensekey) > 0)
                    $amwcore->settingSet($this->strLicenseKey, $licensekey);
            }

            $localkey = $amwcore->settingGet($this->strLocalKey);
            if (strlen($localkey) == 0) {
                //Look for old version of key
                $localkey = $amwcore->settingGet($this->strLocalKeyOld);
                if (strlen($localkey) > 0)
                    $amwcore->settingSet($this->strLocalKey, $localkey);
            }

            $this->debugmode = $debug;
            if ($this->debugmode) {
                echo "License Key: $licensekey \r\n";
                echo "Local Key: $localkey \r\n";
                // echo "RapidCart Token: $rapidToken \r\n";
            }

            $this->results['status'] = 'Invalid';
            $this->error_message = '';

            //If there are keys set, remember this fact
            $hasKeys = false;
            if (strlen($localkey) > 0 || strlen($licensekey) > 0)
                $hasKeys = true;
            $this->checkLicense($licensekey, $localkey);
            $this->results['checkmd5x'] = new AMWSCP_md5x($licensekey . $localkey, $this->results);
            if ($this->results['status'] == 'Active') {
                $this->valid = true;
            } elseif ($hasKeys)
                $this->error_message = 'License Key Invalid: ' . $this->error_message;
            return true;
        }


        function checkLicense($licensekey, $localkey = '')
        {
            global $wpdb;
            $protocol = is_ssl() ? 'https://' : 'http://';
            $whmcsurl = $protocol . 'shop.exportfeed.com/';
            $licensing_secret_key = '437682532'; # Unique value, should match what is set in the product configuration for MD5 Hash Verification
            $check_token = time() . md5(mt_rand(1000000000, mt_getrandmax()) . $licensekey);
            $checkdate = date('Ymd'); # Current date
            if (!isset($_SERVER['SERVER_ADDR']) && !isset($_SERVER['LOCAL_ADDR'])) {
                $sql = "SELECT option_value FROM `" . $wpdb->options . "` where option_name like '%_transient_external_ip_address%'";
                $usersip = $wpdb->get_var($sql);
            } else
                $usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];

            $localkeydays = 1; # How long the local key is valid for in between remote checks
            $allowcheckfaildays = 0; # How many days to allow after local key expiry before blocking access if connection cannot be made
            $localkeyvalid = false;
            $originalcheckdate = '';
            // $localexpiry = 0; will use later on
            $status = '';
            $Results = $this->validateLocalKey($localkey, $localkeydays, $licensing_secret_key, $usersip);
            if ($Results) {
                if ($this->debugmode) {
                    echo "- Return After validating local key last checked on: " . $Results['checkdate'] . "\r\n";
                    echo "- Status: " . $Results['status'];
                }
                foreach ($Results as $k => $result)
                    $this->results[$k] = $result;
                return;
            }
            $domain = get_option('siteurl'); //or home
            $domain = str_replace($protocol, '', $domain);
            $domain = str_replace('www', '', $domain); //add the . after the www if you don't want it
            $domain = strstr($domain, '/', true); //PHP5 only, this is in case WP is not root
            if (isset($_SERVER['SERVER_NAME'])) {
                $domain = $_SERVER['SERVER_NAME'];
            }
            $postfields['licensekey'] = $licensekey;
            $postfields['domain'] = $domain;
            $postfields['ip'] = $usersip;
            //$postfields['dir'] = dirname(__FILE__);

            if ($check_token)
                $postfields['check_token'] = $check_token;
            if (function_exists('curl_exec')) {
                if ($this->debugmode) echo "curl_init(). \r\n";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $whmcsurl . 'modules/servers/licensing/verify.php');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                //added for users who experience 'Remote Check Failed' license issue.
                //it is not enough to disable CURLOPT_SSL_VERIFYPEER
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                $data = curl_exec($ch);
                //echo curl_error($ch); //debug
                if (curl_errno($ch)) {
                    $this->error_message = 'Curl error: ' . curl_error($ch);
                }
                curl_close($ch);

            } else {
                if ($this->debugmode) echo "fsockopen(). \r\n";
                $fp = fsockopen($whmcsurl, 80, $errno, $errstr, 5);

                if ($fp) {
                    $querystring = '';
                    foreach ($postfields as $k => $v)
                        $querystring .= '$k=' . urlencode($v) . '&';

                    $header = "POST " . $whmcsurl . "modules/servers/licensing/verify.php HTTP/1.0\r\n";
                    $header .= "Host: " . $whmcsurl . "\r\n";
                    $header .= "Content-type: application/x-www-form-urlencoded\r\n";
                    $header .= "Content-length: " . @strlen($querystring) . "\r\n";
                    $header .= "Connection: close\r\n\r\n";
                    $header .= $querystring;

                    $data = '';
                    @stream_set_timeout($fp, 20);
                    @fputs($fp, $header);
                    $status = @socket_get_status($fp);

                    while (!@feof($fp) && $status) {
                        $data .= @fgets($fp, 1024);
                        $status = @socket_get_status($fp);
                    }

                    @fclose($fp);
                }
            }

            if (!$data) {
                if ($this->debugmode) echo "Remote check failed. \r\n";
                $this->error_message = 'Remote Check Failed. ';
                return;
            }

            preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
            $inputdata = array();
            foreach ($matches[1] as $k => $v) {
                $inputdata[$v] = $matches[2][$k];
                $this->results[$v] = $matches[2][$k];
            }

            if (isset($inputdata['md5hash']) && $inputdata['md5hash']) {
                if ($inputdata['md5hash'] != md5($licensing_secret_key . $check_token)) {
                    $this->error_message = 'MD5 Checksum Verification Failed. ';
                    if ($this->debugmode) echo "MD5 Checksum Verification Failed. \r\n";
                    return;
                }
            }

            if ($inputdata["status"] == "Active") {
                if ($this->debugmode) echo "Status Active. \r\n";
                $inputdata["checkdate"] = $checkdate;
                $data_encoded = serialize($inputdata);
                $data_encoded = base64_encode($data_encoded);
                $data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
                $data_encoded = strrev($data_encoded);
                $data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
                $data_encoded = wordwrap($data_encoded, 80, "\n", true);
                $inputdata["localkey"] = $data_encoded;
                update_option('amwscpf_localkey', $data_encoded); //Set local key if license key status is Active
            } else {
                $this->error_message .= $this->strErrorMsgMain;
                if ($this->debugmode) echo "Inactive. \r\n";
            }

            $this->inputdata["remotecheck"] = true;
            unset($postfields, $data, $matches, $whmcsurl, $licensing_secret_key, $checkdate, $usersip, $localkeydays, $allowcheckfaildays, $md5hash);
        }

        function setLicenseKey($licenseKey, $localKey)
        {
            global $amwcore;
            $amwcore->settingSet($this->strLicenseKey, $licenseKey);
            $amwcore->settingSet($this->strLocalKey, $localKey);
        }

        function unregister()
        {
            global $amwcore;
            //This will remove the license key (which is likely an undesirable course of action)
            $amwcore->settingSet($this->strLicenseKey, '');
            $amwcore->settingSet($this->strLocalKey, '');
        }

        function unregisterAll()
        {
            global $amwcore;
            //Remove all stored license keys for all known products
            $amwcore->settingDelete('amwscpf_licensekey');
            $amwcore->settingDelete('amwscpf_localkey');
            $amwcore->settingDelete('cp_rapidcarttoken');
            $amwcore->settingDelete('purplexml_licensekey');
            $amwcore->settingDelete('purplexml_localkey');
            $amwcore->settingDelete('gts_licensekey');
            $amwcore->settingDelete('gts_localkey');
            $amwcore->settingDelete('fv_licensekey');
            $amwcore->settingDelete('fv_localkey');
        }

        function validateLocalKey($localkey, $localkeydays, $licensing_secret_key, $usersip)
        {
            if (!$localkey)
                return false;

            $localkey = str_replace("\n", '', $localkey); # Remove the line breaks
            $localdata = substr($localkey, 0, strlen($localkey) - 32); # Extract License Data
            $md5hash = substr($localkey, strlen($localkey) - 32); # Extract MD5 Hash

            if ($md5hash != md5($localdata . $licensing_secret_key))
                return false;

            $localdata = strrev($localdata); # Reverse the string
            $md5hash = substr($localdata, 0, 32); # Extract MD5 Hash
            $localdata = substr($localdata, 32); # Extract License Data
            $localdata = base64_decode($localdata);
            $localkeyresults = unserialize($localdata);
            $originalcheckdate = $localkeyresults['checkdate'];

            if ($md5hash != md5($originalcheckdate . $licensing_secret_key))
                return false;

            $locheck_licensecalexpiry = date('Ymd', mktime(0, 0, 0, date('m'), date('d') - $localkeydays, date('Y')));

            if ($originalcheckdate < $locheck_licensecalexpiry)
                return false;

            $validdomains = explode(',', $localkeyresults['validdomain']);
            if (!in_array($_SERVER['SERVER_NAME'], $validdomains)) {
                $this->error_message = 'Valid domain incorrect. ';
                return false;
            }

            $validips = explode(',', $localkeyresults['validip']);
            if (!in_array($usersip, $validips)) {
                $this->error_message = 'IP incorrect. ';
                return false;
            }

            return $localkeyresults;
        }
    }
}

if (!class_exists('AMWSCP_PLicenseGTS')) {
    class AMWSCP_PLicenseGTS extends AMWSCPF_License
    {

        function __construct()
        {
            $this->strErrorMsgMain = '- Register for the pro version to get full functionality: <a target=\'_blank\' href = \'http://exportfeed.com/\'>exportfeed.com</a> ';
            $this->strLicenseKey = 'gts_licensekey';
            $this->strLocalKey = 'gts_localkey';
            parent::__construct();
        }
    }
}

if (!class_exists('AMWSCP_PLicenseFV')) {
    class AMWSCP_PLicenseFV extends AMWSCPF_License
    {
        function __construct()
        {
            $this->strErrorMsgMain = '- Register for the pro version to get full functionality: <a target=\'_blank\' href = \'http://exportfeed.com/\'>exportfeed.com</a> ';
            $this->strLicenseKey = 'fv_licensekey';
            $this->strLocalKey = 'fv_localkey';
            $this->strLocalKeyOld = '';
            $this->strLocalKeyOld = '';
            parent::__construct();
        }
    }
}
