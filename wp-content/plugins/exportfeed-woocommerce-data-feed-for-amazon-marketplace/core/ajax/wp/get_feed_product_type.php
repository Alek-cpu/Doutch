<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
global $wpdb;
if (!class_exists('AMWSCP_PFeedCore')) {
    
    $include = realpath(__DIR__ . '/../../data/feedcore.php');
    if (file_exists($include)) {
        include_once $include;
    } else {
        print_r("File " . $include . " doesn't Exists");
        exit;
    }
}

global $amwcore;

$strLicenseKey = 'amwscpf_licensekey';
$strRapidcartToken = 'cp_rapidcarttoken';

$rapidToken = $amwcore->settingGet($strRapidcartToken);
//When loading license key, we must be careful to check for valid licenses from prior versions
$licensekey = $amwcore->settingGet($strLicenseKey);
$country = isset($_POST['country_code']) ? $_POST['country_code'] : null;

$nodata = false;
$final = false;
$html='';
$hidelabel = false;
$feed_type_data_html = '';
$level = false;

if (array_key_exists('tpl_id', $_POST) && isset($_POST['tpl_id'])) {
    $remote_tpl_id_for_feed_product_type = array_key_exists('tmpl_id', $_POST) ? $_POST['tmpl_id'] : '';
    $saved_feed_product_type = sanitize_text_field($_REQUEST['feed_product_type']);
    
    $table = $wpdb->prefix . "amwscp_amazon_template_values";
    $tpl = sanitize_text_field($_REQUEST['tpl_id']);
    
    $tpl_tbl = $wpdb->prefix . "amwscp_amazon_services_templates";
    
    /***********************************************************************/
    #Perform Curl action to get feed_product_type#
    /***********************************************************************/
    
    $url = 'https://services.exportfeed.com/init.php';
    $postfields = array(
        'fetch' => 'feed_product_type',
        'flat_tmpl_id' => $remote_tpl_id_for_feed_product_type,
        'country' => $country,
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_TIMEOUT, 200);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    $data = json_decode($data);
    if (is_array($data->results) && array_key_exists('0', $data->results)) {
        $feed_product_type = maybe_unserialize(utf8_decode($data->results[0]->valid_values)) OR json_decode(utf8_decode($data->results[0]->valid_values));
        if(gettype($feed_product_type)==='string')
            $feed_product_type = json_decode($feed_product_type);
    } else {
        $feed_product_type = array();
    }
    $html = 'empty';
    $hidelabel = false;
    if (isset($feed_product_type) && is_array($feed_product_type) && count($feed_product_type) > 0 ) {
        if (count($feed_product_type) == 1) {
            $hidelabel = true;
            $html = '<input type="hidden" name="feed_product_type" class="text_big" id="feed_product_type" value="' . $feed_product_type[0] . '" autocomplete="off">';
        } else {
            $html = "<select class='text_big' name = 'feed_product_type' id='feed_product_type' value=''>";
            $html .= '<option value="">Select Feed Type</option>';
            foreach ($feed_product_type as $index => $opt) {
                $selected = "";
                if (strlen($saved_feed_product_type) > 0) {
                    $selected = 'selected="selected"';
                }
                $html .= "<option $selected value='" . $opt . "'>$opt</option>";
            }
            $html .= "</select>";
        }
    }
    
    /*else {
$html = '<input type="text" name="feed_product_type" class="text_big" id="feed_product_type" value="'.$saved_feed_product_type.'" autocomplete="off" placeholder="Start typing feed Product Type">';
}*/
}

if (array_key_exists('id', $_POST)) {
    $id = isset($_POST['id']) ? $_POST['id'] : 1;
    $level = isset($_POST['level']) ? $_POST['level'] : 2;
    $flat_tmpl_id = isset($_POST['tmpl_id']) ? $_POST['tmpl_id'] : 1;
    $node = isset($_POST['node']) ? $_POST['node'] : '';
} else {
    $id = 1;
    $level = 2;
    $flat_tmpl_id = isset($_POST['tmpl_id']) ? $_POST['tmpl_id'] : 1;
}

/*echo "<pre>";
print_r($id);exit;*/
$category_name = explode('_', $_REQUEST['tpl_id']);
$category_name = strtolower($category_name[0]);

$reg = new AMWSCPF_License();
$url = 'https://services.exportfeed.com/init.php';
$usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
$valid_domain = $_SERVER['SERVER_NAME'];
$path_to_extract = realpath(dirname(__FILE__) . '/../../');
if ($level <= 2) {
    $postfields = array(
        'license_key' => $licensekey,
        'ips' => $usersip,
        'valid_domain' => $valid_domain,
        'path_to_extract' => $path_to_extract,
        'id' => 1,
        'flat_tmpl_id' => $flat_tmpl_id,
        'level' => 2,
    );
} else {
    $postfields = array(
        'license_key' => $licensekey,
        'ips' => $usersip,
        'valid_domain' => $valid_domain,
        'path_to_extract' => $path_to_extract,
        'parent_id' => $id,
        'flat_tmpl_id' => $flat_tmpl_id,
        'level' => $level,
        'node' => $node
    );
}
if ($reg->results['status'] == 'Active') {
    
    if (function_exists('curl_exec')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ApiData = curl_exec($ch);
        if (curl_errno($ch)) {
            $this->error_message = 'Curl error: ' . curl_error($ch);
        }
        $feed_type_data = json_decode($ApiData);
        curl_close($ch);
        
    }
} else {
    $ApiData = null;
    $feed_type_data = new stdClass();
    $feed_type_data->results = array();
}

/*echo "<pre>";
print_r($feed_type_data);exit;*/
$nodata = false;
$final = false;
$feed_type_data_html = '<ul>';
if (property_exists($feed_type_data, 'results') && count($feed_type_data->results) > 0) {
    foreach ($feed_type_data->results as $key => $value) {
        
        $output = utf8_decode($value->category);
        
        if (preg_match('/[?]/', $output)) {
            $output = utf8_encode($output);
        }
        $output = str_replace('&', 'and', $output);
        $output = str_replace('Or', 'or', $output);
        
        /* if (preg_match('/[Schokolade & SÃ¼Ã?igkeiten]/', $output)) $output = "Schokolade and  Süßigkeiten";
        str_replace(\"Ã?l, Essig & Salatdressing", "Öl,  Essig and  Salatdressing", $)
        if (preg_match('/[Ã?l, Essig & Salatdressing]/', $output)) $output = "Öl,  Essig and  Salatdressing";*/
        
        if (stripos($value->item_type, "(") !== false || stripos($value->item_type, ")") !== false) {
            $item_type = str_replace(array('(', ')'), '', $value->item_type);
            $item_type_array = explode('OR', $item_type);
            $clean_item_type = $item_type_array[0];
        } else {
            $clean_item_type = $value->item_type;
        }
        if ($value->level == $level && $country != 'AU') {
            $uniqueid = $value->node;
            $feed_type_data_html .= '<div id="' . $uniqueid . '" class="btg-node-category" onclick="return browseSubCat(\'' . $value->id . '\',\'' . $value->level . '\',\'' . $value->node . '\');"><li class ="fetch-item-type">' . $output . '<span class="list-icon-arrow-right"></span></li>
        <input id="item_type_' . $value->node . '" type="hidden" name="item_type" value="' . $clean_item_type . '"></input>
        </div>';
        } else {
            $uniqueid = $value->node;
            $feed_type_data_html .= '<div id="' . $uniqueid . '" class="btg-node-category" onclick="return browseSubCat(\'' . $value->id . '\',\'' . $value->level . '\',\'' . $value->node . '\');"><li class ="fetch-item-type">' . $output . '<span class="list-icon-arrow-right"></span></li>
        <input id="item_type_' . $value->node . '" type="hidden" name="item_type" value="' . $clean_item_type . '"></input>
        </div>';
        }
    }
    // $feed_type_data_html = utf8_encode($feed_type_data_html);
    
} else {
    $final = true;
    $node = isset($_POST['node']) ? $_POST['node'] : null;
    if ($node) {
        $feed_type_data_html .= '<li id="final-category"></li><li id="select-button-' . $node . '" onclick="return assignItemtypeandNode(\'' . $node . '\');"><button class="select-button button-primary">Select</button></li>';
    } elseif ($reg->results['status'] !== 'Active') {
        $nodata = true;
        $feed_type_data_html .= '<li>This section is automatically updated for users who have a valid license key. "\n" However, it seems that you have not registered a license key OR the existing one has expired. Please input browse node and item type manually.</li>
                                   <li><input style="width: 74%;" type="text" id="manual-recommended-node" name="recommended_browse_pop" placeholder="Enter browse node here..." value="">
                                   </li>
                                   <li><input style="width: 74%;" type="text" id="manual-item-type" name="item_type_keyword_pop" placeholder="Enter item-type here..." value=""></li>
                                   <li id="no-category-data"></li><li id="select-button" onclick="return assignItemtypeandNode(\'nodata\');"><button class="button-primary">Select</button></li>
                                   ';
    } else {
        $nodata = true;
        $feed_type_data_html .= '<li>Recommended browse node and Item type keyword could not be found. You can manually search for the selected category and enter it here.</li>
                                 <li><input style="width: 74%;" type="text" id="manual-recommended-node" name="recommended_browse_pop" placeholder="Enter browse node here..." value="">
                                 </li>
                                 <li><input style="width: 74%;" type="text" id="manual-item-type" name="item_type_keyword_pop" placeholder="Enter item-type here..." value=""></li>
                                 <li id="no-category-data"></li><li id="select-button" onclick="return assignItemtypeandNode(\'nodata\');"><button class="button-primary">Select</button></li>
                                 ';
    }
}

$feed_type_data_html .= '</ul>';

$response = array(
    'nodata' => $nodata,
    'final' => $final,
    'status' => 'success',
    'html' => $html,
    'hide' => $hidelabel,
    'feed_type_data_html' => $feed_type_data_html,
    'level' => $level
);

/*if (is_array($feed_product_type) && count($feed_product_type) > 0) {
    $response = array('nodata' => $nodata, 'final' => $final, 'status' => 'success', 'html' => $html, 'hide' => $hidelabel, 'feed_type_data_html' => $feed_type_data_html, 'level' => $level);
} elseif (is_array($data) && count($data) > 0) {
    $response = array('nodata' => $nodata, 'final' => $final, 'status' => 'success', 'html' => $html, 'hide' => $hidelabel, 'feed_type_data_html' => $feed_type_data_html, 'level' => $level);
} else {
    $response = array('nodata' => $nodata, 'final' => $final, 'status' => 'failed', 'html' => $html, 'hide' => $hidelabel, 'feed_type_data_html' => $feed_type_data_html, 'level' => $level);
}*/

if ($final == true) {
    $response = array('nodata' => $nodata, 'final' => $final, 'status' => 'success', 'html' => $html, 'hide' => $hidelabel, 'feed_type_data_html' => $feed_type_data_html, 'level' => $level, 'node_id' => $node);
}
echo json_encode($response);
die();
