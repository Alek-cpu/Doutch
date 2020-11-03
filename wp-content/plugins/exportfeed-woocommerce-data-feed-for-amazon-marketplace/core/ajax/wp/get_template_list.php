<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once AMWSCPF_PATH . '/core/classes/tables/category-amazon-list.php';

global $amwcore;
$obj = new AMWSCPF_Categories();
$list = $obj->get_list();

$nation = sanitize_text_field($_REQUEST['marketplace']);
$tpls = $list[$nation];
$site = $tpls['site'];
$code = $tpls['code'];
$html = "";

foreach ($tpls['categories'] as $key => $tpl) {
    $html .= "<dl class='template'>";
    $html .= "<dt>" . $tpl['title'] . "</dt>";
    $sign = $code . '_' . $tpl['meta_name'];
    $html .= "<dd><button type='button' onclick='importSomeTemplate(this)' data-tpl= '".$code.'_'.$tpl['meta_name']."' >Import</button> </dd>";
//    $html .= "<dd><a href='?page=amwscpf-feed-template&action=import&tpl=" . $sign . "&need_help=1'>Import</a></dd>";
    $html .= "</dl>";
}
$html .= "<div class='clear'></div>";
echo $html;
die;