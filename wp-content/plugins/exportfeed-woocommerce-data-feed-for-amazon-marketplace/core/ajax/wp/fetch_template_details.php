<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once dirname(__FILE__) . '/../../data/feedcore.php';
require_once dirname(__FILE__) . '/../../classes/dialogbasefeed.php';
require_once dirname(__FILE__) . '/../../classes/providerlist.php';

$feedType = $_POST['provider']; // 'Amazonsc';
$category = $_POST['template'];
if (strlen($category) <= 0)
    $category = 'listingloader';
//$template = $_POST['template'];
if (strlen($feedType) == 0)
    return;

$inc = dirname(__FILE__) . '/../../feeds/' . strtolower($feedType) . '/dialognew.php';
$feedObjectName = 'AMWSCP_'.$feedType . 'Dlg';
if(file_exists($inc)){
    include_once $inc;
}
$f = new $feedObjectName();
$f->initializeProvider();
$f->provider->initializeFeed($category, $category);
//$f->provider->loadTemplate($template, $template);
$remote_tpl_id = get_option('current_remote_tpl_id');
$mappings = $f->attributeMappings();
$response = array("template_id"=>$remote_tpl_id,'mappings'=>$mappings);
echo json_encode($response); die;