<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
/**
 * Required admin files
 *
 */
require_once 'amwscpf-setup.php';
require_once 'amwscpf-order-cron.php';
//require_once 'amazon-product-metabox.php';
//require_once 'amwscpf-product-listing.php';amwscpf-feed-reports
/**
 * Hooks for adding admin specific styles and scripts
 *
 */
function amwscpf_register_styles_and_scripts($hook)
{
    if (!strchr($hook, 'amwscpf')) {
        return;
    }

    wp_register_style('amws-style', plugins_url('css/exportfeed-amazon-mws.css', __FILE__));
    wp_enqueue_style('amws-style');

    /*wp_register_style('amws-datatable-css', plugins_url('css/DataTables/datatables.css', __FILE__));
    wp_enqueue_style('amws-datatable-css');*/

    wp_register_style('amws-colorstyle', plugins_url('css/colorbox.css', __FILE__));
    wp_enqueue_style('amws-colorstyle');

    wp_register_style('amws-progress', plugins_url('css/style.css', __FILE__));
    wp_enqueue_style('amws-progress');

    wp_register_style('amws-reset', plugins_url('css/reset.css', __FILE__));
    wp_enqueue_style('amws-reset');

    wp_enqueue_script('jquery');

    wp_register_script('amws-scripts-modernizer', plugins_url('js/modernizr.js', __FILE__), array('jquery'), true);
    wp_enqueue_script('amws-scripts-modernizer');

    wp_register_script('amws-scripts-tipTip', plugins_url('js/jquery.tipTip.min.js', __FILE__), array('jquery'), true);
    wp_enqueue_script('amws-scripts-tipTip');

    wp_register_script('amws-scripts-colorbox', plugins_url('js/jquery.colorbox-min.js', __FILE__), array('jquery'));
    wp_enqueue_script('amws-scripts-colorbox');

    /*wp_register_script('amws-datatable-script', plugins_url('css/DataTables/datatables.js', __FILE__), array('jquery'));
    wp_enqueue_script('amws-datatable-script');*/

    wp_register_script('amws-scripts', plugins_url('js/amwscpf_scripts.js', __FILE__), array('jquery'));
    wp_enqueue_script('amws-scripts');

    wp_localize_script('amws-scripts', 'amwscpf_object', [
        'action'                       => 'amazon_seller_ajax_handle',
        'security'                     => wp_create_nonce('amazon-exportfeed-nonce'),
        'ajaxhost'                     => plugins_url('/', __FILE__),
        'cmdFetchCategory'             => 'core/ajax/wp/fetch_category.php',
        'cmdFetchLocalCategories'      => 'core/ajax/wp/fetch_local_categories.php',
        'cmdFetchTemplateDetails'      => 'core/ajax/wp/fetch_template_details.php',
        'cmdGetFeed'                   => 'core/ajax/wp/get_feed.php',
        'cmdGetFeedStatus'             => 'core/ajax/wp/get_feed_status.php',
        'cmdMappingsErase'             => 'core/ajax/wp/attribute_mappings_erase.php',
        'cmdRemember'                  => 'core/ajax/wp/update_remember.php',
        'cmdSearsPostByRestAPI'        => 'core/ajax/wp/sears_post.php',
        'cmdSaveAggregateFeedSetting'  => 'core/ajax/wp/save_aggregate_feed_setting.php',
        'cmdSelectFeed'                => 'core/ajax/wp/select_feed.php',
        'cmdSetAttributeOption'        => 'core/ajax/wp/attribute_mappings_update.php',
        'cmdSetAttributeUserMap'       => 'core/ajax/wp/attribute_user_map.php',
        'cmdUpdateAllFeeds'            => 'core/ajax/wp/update_all_feeds.php',
        'cmdUpdateSetting'             => 'core/ajax/wp/update_setting.php',
        'cmdUploadFeed'                => 'core/ajax/wp/upload_feed.php',
        'cmdUploadFeedStatus'          => 'core/ajax/wp/upload_feed_status.php',
        'cmdUpdateFeedConfig'          => 'core/ajax/wp/update_feed_config.php',
        'cmdAddCredentials'            => 'core/ajax/wp/add_credentials.php',
        'cmdGetCredentials'            => 'core/ajax/wp/get_credentials.php',
        'cmdSubmitFeed'                => 'core/ajax/wp/submit_feed.php',
        'cmdSubmissionFeedResult'      => 'core/ajax/wp/submission_feed_result.php',
        'cmdGetFeedProductType'        => 'core/ajax/wp/get_feed_product_type.php',
        'cmdUpdateSwitchInterval'      => 'core/ajax/wp/update_feed_interval_switch.php',
        'cmdSaveFeedCredential'        => 'core/ajax/wp/save_feed_credential.php',
        'feed_product_type'            => '',
        'create_by'                    => '',
        'disabled'                     => false,
        'cmdOrderUpdate'               => 'core/ajax/wp/order_import.php',
        'step'                         => '0',
        'cmdTemplatesList'             => 'core/ajax/wp/get_template_list.php',
        'cmdImportTemplate'            => 'core/ajax/wp/import_template.php',
        'cmdImportTemplatesofPcountry' => 'core/ajax/wp/import-template-by-country.php',
        'cmdSearchOrder'               => 'core/ajax/wp/order-search.php',
        'cmdgetAllTemplate'            => 'core/ajax/wp/import-all-template.php',
        'cmdUpdatecategoryMapping'     => 'core/ajax/wp/update-category-mapping.php',
        'cmdUpdatefeedProductType'     => 'core/ajax/wp/update_feed_product_type.php'
    ]);

}

add_action('admin_enqueue_scripts', 'amwscpf_register_styles_and_scripts');

add_action('amwscpf_ajax_calls', 'amwscpf_ajax_processes');
do_action('amwscpf_ajax_calls');
/**
 * Add menu items to the admin
 *
 */
function amwscpf_admin_menu()
{

    /* add new top level */
    add_menu_page(
        __('Amazon Feed', 'amwscpf-exportfeed-strings'),
        __('Amazon Feed', 'amwscpf-exportfeed-strings'),
        'manage_options',
        'exportfeed-amazon-amwscpf-admin',
        'amwscpf_feed_admin_page',
        plugins_url('/', __FILE__) . '/images/xml-icon.png'
    );

    /* add the submenus */

    add_submenu_page(
        'exportfeed-amazon-amwscpf-admin',
        __('Create Feed', 'amwscpf-exportfeed-strings'),
        __('Create Feed', 'amwscpf-exportfeed-strings'),
        'manage_options',
        'exportfeed-amazon-amwscpf-admin',
        'amwscpf_feed_admin_page'
    );

    add_submenu_page(
        'exportfeed-amazon-amwscpf-admin',
        __('Account', 'amwscpf-exportfeed-strings'),
        __('Account', 'amwscpf-exportfeed-strings'),
        'manage_options',
        'amwscpf-feed-account',
        'amwscpf_feed_account_page'
    );

    /*add_submenu_page(
    'exportfeed-amazon-amwscpf-admin',
    __('Template', 'amwscpf-exportfeed-strings'),
    __('Template', 'amwscpf-exportfeed-strings'),
    'manage_options',
    'amwscpf-feed-template',
    'amwscpf_feed_template_page'
    );*/

    add_submenu_page(
        'exportfeed-amazon-amwscpf-admin',
        __('Manage Feeds', 'amwscpf-exportfeed-strings'),
        __('Manage Feeds', 'amwscpf-exportfeed-strings'),
        'manage_options',
        'exportfeed-amazon-amwscpf-manage-page',
        'amwscpf_manage_feed_page'
    );

    add_submenu_page(
        'exportfeed-amazon-amwscpf-admin',
        __('Reports', 'amwscpf-exportfeed-strings'),
        __('Reports', 'amwscpf-exportfeed-strings'),
        'manage_options',
        'amwscpf-feed-reports',
        'amwscpf_reports_page'
    );

    add_submenu_page(
        'exportfeed-amazon-amwscpf-admin',
        __('Amazon Orders', 'amwscpf-exportfeed-strings'),
        __('Amazon Orders', 'amwscpf-exportfeed-strings'),
        'manage_options',
        'amwscpf-feed-orders',
        'amwscpf_orders_page'
    );

    add_submenu_page(
        'exportfeed-amazon-amwscpf-admin',
        __('Settings', 'amwscpf-exportfeed-strings'),
        __('Settings', 'amwscpf-exportfeed-strings'),
        'manage_options',
        'amwscpf-feed-settings',
        'amwscpf_settings_page'
    );

    add_submenu_page(
        'exportfeed-amazon-amwscpf-admin',
        __('Tutorials', 'amwscpf-exportfeed-strings'),
        __('Tutorials', 'amwscpf-exportfeed-strings'),
        'manage_options',
        'amwscpf-feed-tutorials',
        'amwscpf_tutotials_page'
    );
    //add tutorials

}

add_action('admin_menu', 'amwscpf_admin_menu');
add_action('amwscpf_init_pageview', 'amwscpf_feed_admin_page_action');
add_action('amwscpf_init_submit', 'amwscpf_submit_amazon_feed_page');
add_action('amwscpf_init_help', 'amwscpf_setup_account_page');

function amwscpf_feed_template_page()
{
    require_once 'amwscpf-wpincludes.php';

    $action   = "";
    $id       = "";
    $response = "";
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'remove_template') {
        $action = $_REQUEST['action'];
        $id     = $_REQUEST['tmp_id'];
    }
    if (array_key_exists('response', $_GET)) {
        $response = $_GET['response'];
    }
    $amazon = new AMWSCPF_Amazon();
    $amazon->template_page($id, $action, $response);
    echo '<script type="text/javascript">ajaxhost = "' . plugins_url('/', __FILE__) . '";</script>';
}

function amwscpf_settings_page(){
    include_once 'core/classes/amwscp_settings.php';
    $settingsObject = new Amwscp_settings();
    $settingsObject->init();
}

function amwscpf_feed_account_page()
{
    require_once 'amwscpf-wpincludes.php';
    $id     = "";
    $action = "";
    $feed_id = null;
    if (isset($_REQUEST['action'])) {
        $id     = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $action = $_REQUEST['action'];
    }
    $amazon = new AMWSCPF_Amazon();
    $amazon->account_page($id, $action);
    echo '<script type="text/javascript">ajaxhost = "' . plugins_url('/', __FILE__) . '";</script>';
}

function amwscpf_reports_page()
{
    require_once 'amwscpf-wpincludes.php';
    $amazon = new AMWSCPF_Amazon();
    /*echo '<script type="text/javascript">
    ajaxhost = "' . plugins_url('/', __FILE__) . '";
    amwscp_doUpdateFeedResults();
    </script>';*/

    $amazon->report_page();
}

function amwscpf_feed_admin_page()
{
    require_once 'amwscpf-wpincludes.php';
    require_once 'core/classes/dialoglicensekey.php';
    include_once 'core/classes/dialogfeedpage.php';
    require_once 'core/feeds/basicfeed.php';

    global $amwcore;
    $acore = new CPF_Amazon_Main();
    $escape = get_option('amwscp_escape_accountsetting');
    if ($acore->no_account && empty($escape)) {
        do_action('amwscpf_init_help');exit;
    }

    $amazon = new AMWSCPF_Amazon();

    $amazon->view('screen-option-help');
    $amwcore->trigger('amwscpf_init_feeds');

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'amwscpf_submit_feed') {
        do_action('amwscpf_init_submit');
    } elseif (isset($_REQUEST['help'])) {
        do_action('amwscpf_init_help');
    } else {
        do_action('amwscpf_init_pageview');
    }

}

function amwscpf_submit_amazon_feed_page()
{

    require_once 'core/data/savedfeed.php';
    $amazon  = new AMWSCPF_Amazon();
    $action  = $_REQUEST['action'];
    $feed_id = $_REQUEST['id'];
    $amazon->display($_REQUEST['id'], $action, $feed_id);
    echo '<script type="text/javascript">
             ajaxhost = "' . plugins_url('/', __FILE__) . '";
         </script>';
}

//include_once('cart-product-version-check.php');
/**
 * Create news feed page
 */
function amwscpf_feed_admin_page_action()
{

/***********************************************************************************************************************************************/

    /****************** Commented for now. May need Later on. **********************/

    /* echo '<div class="postbox" style="width:98%;top: 12px;">
    echo '<div class="postbox info" style="width:98%;top: 12px;">
    <div style="height:auto;" class="inside-export-target">
    <div>
    <h4>Excited to sell your products through Amazon marketplace?</h4>
    <h5>Make sure you meet these basic requirements to start listing and managing your products on Amazon using ExportFeed.</h5>
    <ul>
    <li><div class="step-circle list"><span class="dashicons dashicons-arrow-right-alt2"></span></div>Professional Seller account on Amazon Seller Central. <a href=" https://www.exportfeed.com/sell-on-amazon-marketplaces-requirements/" target="_blank">Here\'s</a> why you need it. </li>
    <li><div class="step-circle list"><span class="dashicons dashicons-arrow-right-alt2"></span></div>Access details: Seller ID, MarketPlace ID, AWS key Id & Secret Key. <a href="https://www.exportfeed.com/documentation/get-amazon-mws-details-connect-exportfeed-cloud-amazon/" target="_blank">Here\'s</a> how to get it.
    </li>
    </ul>
    </div>
    </div>
    </div>';*/
/***********************************************************************************************************************************************/

    if ($_REQUEST['page'] == 'exportfeed-amazon-amwscpf-admin') {
        amwscpf_print_info();
    }
    $action         = '';
    $source_feed_id = -1;
    $message2       = null;
    $disabled       = false;
    //check action
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
    }

    if (isset($_GET['action'])) {
        $action = $_GET['action'];
    }

    switch ($action) {
        case 'update_license':
            //I think this is AJAX only now -K
            //No... it is still used (2014/08/25) -K
            if (isset($_POST['license_key'])) {
                $licence_key = $_POST['license_key'];
                if ($licence_key != '') {
                    update_option('amwscpf_licensekey', $licence_key);
                }

            }
            break;
        case 'reset_attributes':
            //I don't think this is used -K
            global $wpdb, $woocommerce;
            $attr_table = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
            $sql        = "SELECT attribute_name FROM " . $attr_table . " WHERE 1";
            $attributes = $wpdb->get_results($sql);
            foreach ($attributes as $attr) {
                delete_option($attr->attribute_name);
            }

            break;
        case 'edit':
            $action         = '';
            $source_feed_id = $_GET['id'];
            if (isset($_REQUEST['perform']) && $_REQUEST['perform'] == 'update') {
                $disabled = true;
            }
            break;
    }

    if (isset($action) && (strlen($action) > 0)) {
        echo "<script> window.location.assign( '" . admin_url() . "admin.php?page=exportfeed-amazon-amwscpf-admin' );</script>";
    }

    if (isset($_GET['debug'])) {
        $debug = $_GET['debug'];
        if ($debug == 'phpinfo') {
            phpinfo(INFO_GENERAL + INFO_CONFIGURATION + INFO_MODULES);
            return;
        }
        if ($debug == 'reg') {
            echo "<pre>\r\n";
            new AMWSCPF_License(true);
            echo "</pre>\r\n";
        }
    }

    # Get Variables from storage ( retrieve from wherever it's stored - DB, file, etc... )

    $reg = new AMWSCPF_License();

    //Main content
    echo '
        <script type="text/javascript">
            jQuery( document ).ready( function( $ ) {
                jQuery("#page_action_determiner").val("edit");
                ajaxhost = "' . plugins_url('/', __FILE__) . '";
                jQuery( "#selectFeedType" ).val( "AmazonSC" );
                amwscp_doSelectFeed(); // st
                amwscp_doFetchLocalCategories();
                feed_id = ' . $source_feed_id . ';
                perform = "' . $disabled . '";
                //if(feed_id > 0 ){
                  //var tpl_id = jQuery("#amazon-remote-category-selected").val();
                  // console.log(tpl_id);
                  //amwscp_doSelectCategory("AmazonSC",tpl_id);
                //}
          } );
        </script>';

    //WordPress Header ( May contain a message )

    global $message;
    if (strlen($message) > 0 && strlen($reg->error_message) > 0) {
        $message .= '<br>';
    }
    //insert break after local message (if present)
    $message .= $reg->error_message;
    if (strlen($message) > 0) {
        //echo '<div id="setting-error-settings_updated" class="error settings-error">'
        echo '<div id="setting-error-settings_updated" class="updated settings-error">
        <p>' . $message . '</p>
    </div>';
    }

    if ($source_feed_id == -1) {
        //Page Header
        echo AMWSCPF_FeedPageDialogs::pageHeader();
        //Page Body
        echo AMWSCPF_FeedPageDialogs::pageBody();
    } else {
        require_once dirname(__FILE__) . '/core/classes/dialogeditfeed.php';
        echo AMWSCPF_EditFeedDialog::pageBody($source_feed_id, $disabled);
        echo '
        <script type="text/javascript">
            jQuery( document ).ready( function( $ ) {
                jQuery("#page_action_determiner").val("edit");
                ajaxhost = "' . plugins_url('/', __FILE__) . '";
                jQuery( "#selectFeedType" ).val( "AmazonSC" );
                amwscp_doSelectFeed(); // st
                amwscp_doFetchLocalCategories();
                feed_id = ' . $source_feed_id . ';
                perform = "' . $disabled . '";
                if(feed_id > 0 ){
                  var tpl_id = jQuery("#amazon-remote-category-selected").val();
                  // console.log(tpl_id);
                  amwscp_doSelectCategory("AmazonSC",tpl_id);
              }
          } );
        </script>';
    }

    if (!$reg->valid) {
        //echo AMWSCPF_LicenseKeyDialog::large_registration_dialog( '' );
    }

}

/**
 * Display the manage feed page
 *
 */

add_action('amwscpf_preview_mange', 'amwscpf_mange_page_action');

function amwscpf_manage_feed_page()
{
    require_once 'amwscpf-wpincludes.php';
    require_once 'core/classes/dialoglicensekey.php';
    include_once 'core/classes/dialogfeedpage.php';

    global $amwcore;
    $amwcore->trigger('amwscpf_init_feeds');

    do_action('amwscpf_preview_mange');
}

function amwscpf_mange_page_action()
{

    $reg = new AMWSCPF_License();

    require_once 'amwscpf-manage-feeds.php';

    //if ( !$reg->valid )
    //echo AMWSCPF_LicenseKeyDialog::large_registration_dialog( '' );

}

function amwscpf_ajax_processes()
{
    if (isset($_POST['action']) && $_POST['action'] == 'amazon_seller_ajax_handle') {
        add_action('wp_ajax_amazon_seller_ajax_handle', 'amws_all_ajax_handles');
    }
    if (isset($_GET['action']) && $_GET['action'] == 'amazon_seller_ajax_handle') {
        add_action('wp_ajax_amazon_seller_ajax_handle', 'amws_all_ajax_handles');
    }
}

function amws_all_ajax_handles()
{
    $check = check_ajax_referer('amazon-exportfeed-nonce', 'security');
    if ($check) {
        $file = plugin_dir_path(__FILE__) . $_REQUEST['feedpath'];
        require_once $file;
    }
    die;
}

function amwscpf_tutotials_page()
{
    require_once 'amwscpf-wpincludes.php';
    $amazon = new AMWSCPF_Amazon();
    $amazon->tutorials_page();
    echo '<script type="text/javascript">
    ajaxhost = "' . plugins_url('/', __FILE__) . '";
</script>';
}

function amwscpf_orders_page()
{
    require_once 'amwscpf-wpincludes.php';
    if (isset($_GET['action']) && $_GET['action'] == "createorder") {
            $sendback = admin_url() . 'post.php?post=' . $_GET['post'] . '&action=edit';
            wp_redirect($sendback);
    } else {
        $amazon = new AMWSCPF_Amazon();
        $amazon->orders_page();
    }

}

function amwscpf_setup_account_page()
{
    global $wpdb;
    $initialcheck = true;
    $table        = $wpdb->prefix . "amwscp_amazon_accounts";
    $sql          = "SELECT COUNT(id) as count FROM $table";
    $result       = $wpdb->get_results($sql);
    if ($result[0]->count > 0) {
        $initialcheck = false;
    }
    require_once 'core/classes/tables/category-amazon-list.php';
    $lists         = new AMWSCPF_Categories;
    $templates     = $lists->get_list();
    $amazon        = new AMWSCPF_Amazon();
    $amazon->setup = true;
    if ($initialcheck == true) {
        $amazon->view('screen-option-setup');
    }
}
if (is_admin()) {
    add_filter('manage_edit-shop_order_columns', 'amwscp_column_of_wp_order');
    add_action('manage_shop_order_posts_custom_column', 'amwscpf_wp_order_column_process', 2);
}
function amwscp_column_of_wp_order($columns)
{
    $new_columns = (is_array($columns)) ? $columns : array();
    unset($new_columns['order_actions']);
    $new_columns['amazon_order_id'] = 'Amazon Order Id';
    if($columns!=null){
        $new_columns['order_actions']   = (array_key_exists('order_actions', $columns) && isset($columns['order_actions']) ) ? $columns['order_actions'] : null ;
    }else{
        $new_columns['order_actions'] =null;
    }
    return $new_columns;
}

function amwscpf_wp_order_column_process($column)
{
    global $post, $wpdb;
    $table = $wpdb->prefix . "amwscp_orders";
    $sql   = $wpdb->prepare("SELECT order_id as amazon_order_id FROM $table WHERE post_id = %d", [$post->ID]);
    $data  = $wpdb->get_row($sql, ARRAY_A);
    if ($column == 'amazon_order_id') {
        echo (isset($data['amazon_order_id']) ? $data['amazon_order_id'] : '');
    }
}

/*
 * @Info : Commented out now, May need for future use
 * function createWooOrder()
{
    include_once plugin_dir_path(__FILE__) . 'custom-woo-order.php';
    $obj    = new AMWSCP_CustomOrder();
    $result = $obj->create_woocommerce_orderHook();
    $check  = false;
    if (stripos($_SERVER['REQUEST_URI'], 'amwscpf') == true) {
        if ($_REQUEST['page'] == 'amwscpf-feed-orders') {
            // $check = stripos($_SERVER['REQUEST_URI'], 'amwscpf');
            $check = true;
        }
    }

    $admin = false;
    if ($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] == admin_url()) {
        $admin = true;
    }

    if ($check && ($check !== false || $admin == true)) {
        if ($result == 'ORDER_SYNCED' && $check !== false || $admin == true) {
            echo '<div style=" margin :10px 1px 2px;" class="notice notice-info is-dismissible">'
                . '<p>'
                . '<span style="vertical-align:bottom; margin-left:12px" >'
                . "Auto Order Synced Successfully."
                . '</span></p>'
                . '</div>';
        } elseif ($result == 'NO_NEW_ORDER' && $check != false) {

            echo '<div style=" margin :10px 1px 2px;" class="notice notice-info is-dismissible">'
                . '<p>'
                . '<span style="vertical-align:bottom; margin-left:02px;" >'
                . "All Amazon orders are synced. No new Orders found."
                . '</span></p>'
                . '</div>';

        } else {
            echo '<div style=" margin :10px 1px 2px;" class="notice notice-info is-dismissible">'
                . '<p>'
                . '<span style="vertical-align:bottom; margin-left:02px;" >'
                . "There were some problem in auto order sync process. This can occur if item sku missmatches most of the time. Make sure Item Skus are unique to all the products."
                . '</span></p>'
                . '</div>';
        }
    }
}
 add_action('admin_notices', 'createWooOrder');

*/
