<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once dirname(__FILE__) . '/../../data/feedcore.php';
require_once dirname(__FILE__) . '/../../classes/dialogbasefeed.php';
require_once dirname(__FILE__) . '/../../classes/providerlist.php';
do_action('amwscpf_load_feed_modifier');

global $amwcore;
$amwcore->trigger('amwscpf_init_feeds');

add_action('amwscpf_select_feed_main_hook', 'amwscp_select_feed_main');
do_action('amwscpf_select_feed_main_hook');

function amwscp_select_feed_main()
{   
    $feedType = array_key_exists('feedType', $_REQUEST) ? $_REQUEST['feedType'] : null ;
    if ($feedType == null || strlen($feedType) === 0) {
        return;
    }
    
    $inc = dirname(__FILE__) . '/../../feeds/' . strtolower($feedType) . '/dialognew.php';
    $feedObjectName = 'AMWSCP_'.$feedType . 'Dlg';
    if (file_exists($inc))
        include_once $inc;
    $f = new $feedObjectName();
    echo $f->mainDialog();
}