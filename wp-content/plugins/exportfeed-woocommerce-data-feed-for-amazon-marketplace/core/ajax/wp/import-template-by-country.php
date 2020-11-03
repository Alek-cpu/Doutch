<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly
// require_once dirname(__FILE__) . '/../../data/feedcore.php';

$service_name = strtolower($_POST['service_name']);
$code = $_POST['country_code'];

if(class_exists('CategoryFetch')){
    $obj = new CategoryFetch();

    $html_optins = $obj->categoryList($service_name,$code);
    echo $html_optins;
    exit;
  }
  else{
  	print_r("Class doesn't exists");exit;
  }

Class CategoryFetch {
   
   function __construct(){

   }
  

  function categoryList($service_name,$code)
  {
  	    $url = 'URL|'.admin_url().'admin.php?page=amwscpf-feed-template';
		$tpl_name = '';
		
		$templates = $this->templateList();
		
		$html = "<label class='label' for='categoryDisplayText'>Template : </label>";
		$html .= "<span>";
		$html .= '<select name="categoryDisplayText" class="text_big" id="categoryDisplayText"  onchange="amwscp_doSelectCategory(\'' . $service_name . '\',  this.value);" value="' . $initial_remote_category . '">';
		$html .= "<option></option>";
		
		if (count($templates) > 0){
			foreach ($templates as $key => $tpl){

				$selected = "";
				$opt_value = $tpl->tpl_name.'_'.$tpl->country;
				$selected = $opt_value == $initial_remote_category ? 'selected="selected"' : '';
				
				if(strtoupper($tpl->country)==strtoupper($code)){
                             $html .= "<option value='".$opt_value."' $selected>".$tpl->tpl_name. '('. $tpl->country . ')' . "</option>";
                            }
			}
		}

		$html .= "<option value='listingloader'>Listing Loader</option>
                              <option value ='".$url."' >Cloud not find Template? Click here to import</option>";
		$html .= "</select>";
		$html .= "</span>";
		$html .= "<input type='hidden' id='remote_category' name='remote_category' value='{$tpl_name}'/>";
		return $html;
  }


  function templateList()
  {
		global $wpdb;
		$table = $wpdb->prefix."amwscp_amazon_templates";
		$sql = "SELECT id,tpl_name,country FROM $table";
		$templates = $wpdb->get_results($sql);
		return $templates;
  }



 }


?>