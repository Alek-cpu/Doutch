<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once AMWSCPF_PATH . '/core/classes/tables/category-amazon-list.php';

$obj = new AMWSCPF_Categories();
$templates = sanitize_text_field($_REQUEST['tpl']);
$data = new stdClass();
$data = '<button type="button" onclick="importSomeTemplate(this)" data-tpl="'.$templates.'">Import</button>';
if($obj->importCategory($templates)){
    $data = '<button type="button">Imported</button>';
}
echo $data;
die;