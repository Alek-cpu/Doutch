<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly
define('XMLRPC_REQUEST', true);
ob_start(null);

require_once dirname(__FILE__) . '/../../data/feedcore.php';
require_once dirname(__FILE__) . '/../../data/productcategories.php';

ob_clean();

$categoryList = new AMWSCP_PProductCategories();
$result = new stdClass();
$result->children = array();

foreach ($categoryList->categories as $this_category)
    if (!isset($this_category->parent_category))
        process_category($result->children, $this_category);

echo json_encode($result);

function process_category(&$target_list, $this_category)
{
    $new_category = new stdClass();
    $new_category->id = $this_category->id;
    $new_category->title = $this_category->title;
    $new_category->tally = $this_category->tally;
    $new_category->children = array();
    $target_list[] = $new_category;
    foreach ($this_category->children as $child)
        process_category($new_category->children, $child);
}

?>