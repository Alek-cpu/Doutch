<?php

class ImportTemplate
{
    
    function __construct()
    {
    
    }
    
    public function getTemplates($country)
    {
        // global $wpdb;
        // $table = $wpdb->prefix.'amwscp_amazon_services_templates';
        // $sql = "SELECT * FROM $table WHERE country = '$country' ";
        //       $data = $wpdb->get_results($sql);
        $url = 'https://services.exportfeed.com/init.php';
        $postfields = array(
            'fetch' => '1',
            'country' => $country,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $data = json_decode($data);
        
        if (curl_errno($ch))
            $this->error_message = 'Curl error: ' . curl_error($ch);
        
        curl_close($ch);
        if (isset($data->results))
            return $this->buildhtml($data->results->results);
        return null;
    }
    
    public function buildhtml($data = array())
    {
        if (count($data) > 0) {
            $html = '<ul>';
            foreach ($data as $option) {
                $output = preg_replace(array('/(?<=[^A-Z])([A-Z])/', '/(?<=[^0-9])([0-9])/'), ' $0', ($option->title));
                $output = ucwords($output);
                $output = str_replace('&', 'and', $output);
                $output = str_replace('Or', 'or', $output);
                $html .= '<div id="amazon_product_category_' . $option->tmpl_id . '" class="btg-node-category selected" onclick=\'return amwscp_doSelectCategory("Amazonsc","' . $option->title . '",' . $option->tmpl_id . ',"' . $option->country . '");\'>
                     <li class ="fetch-item-type">' . $output . '<span class="list-icon-arrow-right"></span></li>
				     <input id="item_type_' . $option->title . "_" . $option->tmpl_id . '" type="hidden" name="amazon_category" value="' . $option->tmpl_id . '">
				</div>';
            }
            $html .= '</ul>';
            
            return $html;
        }
        return null;
    }
    
    
}

$country = isset($_POST['country']) ? $_POST['country'] : 'US';
$templateObject = new ImportTemplate();
$allTemplates = $templateObject->getTemplates($country);
$response = array('status' => 'success', 'html' => $allTemplates);
echo json_encode($response);
die;