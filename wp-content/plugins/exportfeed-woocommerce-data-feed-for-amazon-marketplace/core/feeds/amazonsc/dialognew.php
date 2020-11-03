<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if (!class_exists('AMWSCP_AmazonSCDlg')){
    class AMWSCP_AmazonSCDlg extends AMWSCP_PBaseFeedDialog {
        
        public $active_code;

        function __construct() {
            parent::__construct();
            $this->service_name = 'Amazonsc';
            $this->service_name_long = 'Amazon Seller Central';
        }

        function templateList(){
            global $wpdb;
            $table = $wpdb->prefix."amwscp_amazon_templates";
            $sql = "SELECT id,tmpl_id,tpl_name,country FROM $table";
            $templates = $wpdb->get_results($sql);
            return $templates;
        }

        function changeCcodetoCountry($code){
            $code = strtoupper($code);
            switch($code){

                case 'CA':
                    $result = "Canada";
                    break;

                case 'AU':
                    $result = "Australia";
                    break;

                case 'US':
                    $result = "United States";
                    break;

                case 'MX':
                    $result = "Mexico";
                    break;

                case 'UK':
                    $result = "United Kingdom";
                    break;

                case 'FR':
                    $result = "France";
                    break;

                case 'DE':
                    $result = "Germany";
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

        public function getMarketplaceArray(){
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

         function getmwsAccount(){
            global $wpdb;
            $table = $wpdb->prefix."amwscp_amazon_accounts";
            $sql = "SELECT id as ID, title, marketplace_id, market_id,market_code,active  FROM {$table}";
            $data = $wpdb->get_results($sql,ARRAY_A);
            return $data;
        }

        function amazonAccounts($iniitial_country=null){
            $data = $this->getmwsAccount();
            if(is_array($data) && count($data)<=0){
                $countryArray = $this->getMarketplaceArray();
                $countrycodeForNoAccount = !empty(get_option('amwscp_country_without_account_set')) ? get_option('amwscp_country_without_account_set') : 'US';
                $html  = '<select class="text_big" id="categoryDisplayTextbyC"  name="amazon_account" onchange=" return amwscp_doFetchTemplatesByCountry(this.value,\'amazonsc\')">';
                $html .= '<option>Select Marketplace</option>';
                foreach ($countryArray as $key => $value) {
                    $selected = '';
                    if(strtoupper($value['code'])==strtoupper($countrycodeForNoAccount)){
                        $selected = "selected";
                        $this->active_code = $value['code'];
                    }
                    $html .= '<option '. $selected .' value="'.$value['code'].'" >'.$this->changeCcodetoCountry($value['code']).' ( '.$value['title'].' ) '.'</option>';
                }
                $html .= '</select>';
            }else{
                $html  = '<select class="text_big" id="categoryDisplayTextbyC"  name="amazon_account" onchange=" return amwscp_doFetchTemplatesByCountry(this.value,\'amazonsc\')">';
                $html .= '<option>Select Marketplace</option>';

                if($iniitial_country){
                    $array = explode('_', $iniitial_country);
                    $iniitial_country_code = $array[1];
                    // print_r($iniitial_country_code);exit;
                    foreach ($data as $key => $value) {
                        $selected = '';
                        if(strtoupper($value['market_code'])==strtoupper($iniitial_country_code)){
                            $selected = "selected";
                            $this->active_code = $value['market_code'];
                        }
                        /*$html .= '<option '. $selected .' value="'.$value['market_code'].'" >' . $this->changeCcodetoCountry($value['market_code']) .'</option>';*/
                        $html .= '<option '. $selected .' value="'.$value['market_code'].'" >'.$this->changeCcodetoCountry($value['market_code']).' ( '.$value['title'].' ) '.'</option>';
                    }

                }
                else{
                    foreach ($data as $key => $value) {
                        $selected = '';
                        if($value['active']==true){
                            $selected = "selected";
                            $this->active_code = $value['market_code'];
                        }
                        $html .= '<option '. $selected .' value="'.$value['market_code'].'" >'.$this->changeCcodetoCountry($value['market_code']).' ( '.$value['title'].' ) '.'</option>';
                    }

                }
                $html .= '</select>';
            }
            return $html;
        }

        function categoryList($initial_remote_category,$code=null)
        {

            if($code==null){

                if ($this->blockCategoryList)
                return '';
                else{
                    $tpl_name = $initial_remote_category;
                    if (isset($this->disable) && $this->disable == 'disabled'){
                        $tpl_name = 'listingloader';
                    }
                    $templates = $this->templateList();
                    // <option value="{'foo':'bar','one':'two'}">Option two</option>
                    //            return json_encode($templates);die;
                    $html = "<label class='label' for='categoryDisplayText'>Select Amazon Category : </label>";
                    $html .= "<span>";
                    $html .= '<select name="categoryDisplayText" '.$this->disable.' class="text_big" id="categoryDisplayText"  onchange="amwscp_doSelectCategory(\'' . $this->service_name . '\',  this.value);" value="' . $initial_remote_category . '">';
                    $html .= "<option></option>";
                    if (count($templates) > 0){
                       
                        foreach ($templates as $key => $tpl){
                            $selected = "";
                            $opt_value = $tpl->tpl_name.'_'.$tpl->country;

                            $selected = $opt_value == $initial_remote_category ? 'selected="selected"' : '';
                            
                             if(strtoupper($tpl->country)==strtoupper($this->active_code)){
                             $html .= "<option value='".$opt_value."' $selected>".$tpl->tpl_name. '('. $tpl->country . ')' . "</option>";
                            }
                        }
                    }
                    $url = 'URL|'.admin_url().'admin.php?page=amwscpf-feed-template';
                    $html .= "<option value='listingloader'>Listing Loader</option>
                              <option value ='".$url."' >Cloud not find Template? Click here to import</option>";
                    $html .= "</select>";
                    $html .= "</span>";
                    $html .= "<input type='hidden' id='remote_category' name='remote_category' value='{$tpl_name}'/>";
                    return $html;
                }
            }
            else{
                if ($this->blockCategoryList)
                return '';
                else{
                    $tpl_name = $initial_remote_category;
                    if (isset($this->disable) && $this->disable == 'disabled'){
                        $tpl_name = 'listingloader';
                    }
                    $templates = $this->templateList();
    //            return json_encode($templates);die;
                    $html = "<label class='label' for='categoryDisplayText'>Amazon Template : </label>";
                    $html .= "<span>";
                    $html .= '<select name="categoryDisplayText" '.$this->disable.' class="text_big" id="categoryDisplayText"  onchange="amwscp_doSelectCategory(\'' . $this->service_name . '\',  this.value);" value="' . $initial_remote_category . '">';
                    $html .= "<option></option>";
                    if (count($templates) > 0){
                        foreach ($templates as $key => $tpl){
                            $selected = "";
                            $opt_value = $tpl->tpl_name.'_'.$tpl->country;
                            $selected = $opt_value == $initial_remote_category ? 'selected="selected"' : '';
                           if(strtoupper($tpl->country)==strtoupper($this->active_code)){
                             $html .= "<option value='{ \"name\":\"".$opt_value."\",\"tmpl_id\":\"".$tpl->tmpl_id."\"}' $selected>".$tpl->tpl_name. '('. $tpl->country . ')' . "</option>";
                            }
                        }
                    }
                    $url = 'URL|'.admin_url().'admin.php?page=amwscpf-feed-template';
                    $html .= "<option value='listingloader'>Listing Loader</option>
                              <option value ='".$url."' >Cloud not find Template? Click here to import</option>";
                    $html .= "</select>";
                    $html .= "</select>";
                    $html .= "</span>";
                    $html .= "<input type='hidden' id='remote_category' name='remote_category' value='{$tpl_name}'/>";
                    return $html;
                }
            }
        }

        function feed_product_type($initial_product_type){
            return '

                      <span id = "amwscp_feed_list">
                        <input type="text" '.$this->disable.' name="feed_product_type" class="text_big" id="feed_product_type" value="' . $initial_product_type . '" autocomplete="off" placeholder="Please refer to Template\'s BTG." />
                      </span>';
        }

        function recommended_browse_nodes($recommended_browse_nodes){
            return '<label class="label" for="recommended_browse_node" >Recommended Browse Node: </label>
                      <span>
                        <input type="text" '.$this->disable.' name="recommended_browse_node" class="text_big" id="recommended_browse_node" value="'. $recommended_browse_nodes .' "  placeholder="Please refer to Template\'s BTG." />
                      </span>';
        }

        function item_type_keyword($item_type_keyword){
            return '<label class="label" for="item_type_keyword" >Item Type: </label>
                      <span>
                        <input type="text" '.$this->disable.' name="item_type_keyword" class="text_big" id="item_type_keyword" value="'. $item_type_keyword .' " autocomplete="off" placeholder="Please refer to Template\'s BTG." />
                      </span>';
        }

        function feed_type($amazon_category){
            return '
                      <span id = "select-feed-type">
                          <input id="feed-type-value-input" type="text" readonly name="select-feed-type" class="text_big" onclick="return selectFeedType(this);" value="'.$amazon_category.'">
                      </span>';
        }

    }
}
