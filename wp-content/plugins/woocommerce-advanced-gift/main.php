<?php
/*
	*  Plugin Name: PW Woocommerce Advanced Gift Rules
	*  Plugin URI: http://plugin.proword.net/Plugins/Advanced_Gift/sandbox-demo/
	*  Description: Create Rules Gift For woocommerce
	*  Author: Proword
	*  Version: 5.2
	*  Author URI: http://proword.net/
	*  Text Domain: pw_wc_advanced_gift
	*  Domain Path: /languages/ 
	*  WC requires at least: 3.0
	*  WC tested up to: 3.8.0
*/

define('plugin_dir_url_wc_advanced_gift', plugin_dir_url(__FILE__));
define('PW_WC_GiIFT_URL', plugin_dir_path(__FILE__));

/**
 * Localisation
 **/
add_action('plugins_loaded', 'pw_gift_load_textdomain');
function pw_gift_load_textdomain()
{
    load_plugin_textdomain('pw_wc_advanced_gift', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

class woocommerce_advanced_gift
{
    const VERSION = '5.0';
    public function __construct()
    {
        //$this->includes();
		add_action('wp_enqueue_scripts', array($this, "front_end_woo_advanced_gift_js_css"), 10, 1);		
		add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts_function']);		
		
        //add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action('admin_menu', array($this, 'add_menu'));
        register_activation_hook(__FILE__, array($this, 'woo_advanced_gift_install'));
        //$this->includes();
		add_action( 'plugins_loaded', array( $this, 'includes' ) );
		
        //Shortcode Ui
        add_action('admin_head', array($this, 'wc_gift_shortcodes_addbuttons'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'action_links'));
        // admin
        //if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        //	$this->install();
		add_action( 'init', array($this,'create_post_type_gift'));
		
		add_filter( 'plugin_row_meta', array($this, 'plugin_row_meta' ), 10, 4 );
    }


	public function create_post_type_gift() {
	  register_post_type( 'pw_gift_rule',
		array(
		  'labels' => array(
			'name' => __( 'pw_gift_rule' ),
			'singular_name' => __( 'pw_gift_rule' )
		  ),
		'public' => true,
		'has_archive' => true,
		'show_in_menu'=>false, 
		)
	  );
	}

	public function plugin_row_meta( $links_array, $plugin_file_name, $plugin_data, $status ){
		if( $plugin_file_name === 'woocommerce-advanced-gift/main.php' )
		{
			$links_array[] = '<a href="http://plugin.proword.net/Plugins/Advanced_Gift/Documentation/" target="_blank" title="' . __( 'Documentation &#187;' ) . '"><strong>' . __( 'Documentation &#187;' ) . '</strong></a>';
			$links_array[] = '<a href="https://support.proword.net/" target="_blank" title="' . __( 'Support &#187;' ) . '"><strong>' . __( 'Support &#187;' ) . '</strong></a>';			
		}

	 
		return $links_array;
	}
	
    public function woo_advanced_gift_install()
    {
		//$this->install();
        $installed_version = get_option('pw_gift_version');
     //   if (-1 === version_compare($installed_version, self::VERSION)) {
       // }
		$this->install();
		if (-1 === version_compare($installed_version, '5.0')) {
			$setting = get_option('pw_gift_options');
			$setting ['show_gift_stock_qty']='no';
			$setting['select_gift'] = "Select Gift";
			update_option('pw_gift_options', $setting);
			update_option('pw_gift_version', '5.0');
		}
		
		if (-1 === version_compare($installed_version, '4.4')) {
			$setting = get_option('pw_gift_options');
			$setting ['number_per_page']='6';
			update_option('pw_gift_options', $setting);
			update_option('pw_gift_version', '4.4');
		}
		 update_option('pw_gift_version', self::VERSION);
    }

    public function includes()
    {
		
        $method = "1";
        require('core/discount_cart.php');
        /*if($method=="1")
            require( 'core/discount_cart_old.php' );
        else
            require( 'core/discount_cart_new.php' );
        */
        require('core/show_product_meta.php');
        require('core/shortcode.php');

        $setting = get_option("pw_gift_options");
        //if (!is_admin() && $setting['hide_popup_in_mobile'] == 'true') {
            //return;
        //}
       // require('core/popup.php');
        if (!is_admin() && !isset($_COOKIE['pw_woo_popup_gift_cookie']) && $setting['show_popup'] == "true") {
			$expire=isset($setting['expire']) ? $setting['expire'] : 60;
			$expire=time() + ($expire *60);
            require('core/popup.php');
            setcookie("pw_woo_popup_gift_cookie", 'ok',  $expire, COOKIEPATH, COOKIE_DOMAIN);

        }
        //setcookie("pw_woo_popup_gift_cookie", '', time()-3600, COOKIEPATH, COOKIE_DOMAIN);
    }

    public function action_links($links)
    {
        return array_merge(array(
            '<a href="' . admin_url('admin.php?page=rule_gift&tab=setting&pw_action_type=list') . '">' . __('Settings', 'pw_wc_advanced_gift') . '</a>',

        ), $links);
    }

    public function add_menu()
    {

        $this->page_id = add_submenu_page(
            'woocommerce',
            __('Woo Advanced Gift', 'pw_wc_advanced_gift'),
            __('Woo Advanced Gift', 'pw_wc_advanced_gift'),
            'manage_woocommerce',
            'rule_gift',
            array($this, 'show_sub_menu_page')
        );
    }

    public function show_sub_menu_page()
    {

        $current_tab = (empty($_GET['page'])) ? 'rule_gift' : urldecode($_GET['page']);
        if ('rule_gift' === $current_tab)
            $this->show_level_tab();
    }

    private function show_level_tab()
    {

        $current_tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'gift_rules';
        $arr = __('Add Rules','pw_wc_advanced_gift');
        if (@$_GET['pw_action_type'] == "edit")
            $arr = __('Edit Rules','pw_wc_advanced_gift');
        $tabs = array(
            array('name' => "<i class='fa fa-list-ul' ></i>".__('Rules List','pw_wc_advanced_gift'), 'url' => "gift_rules"),
            array('name' => "<i class='fa fa-edit' ></i>" . $arr, 'url' => "add_rule"),
            array('name' => "<i class='fa fa-cog' ></i>".__('Settings','pw_wc_advanced_gift'), 'url' => "setting"),
            array('name' => "<i class='fa fa-bar-chart' ></i>".__('Reports','pw_wc_advanced_gift'), 'url' => "report"),
            array('name' => "<i class='fa fa-history' ></i>".__('Clear Session','pw_wc_advanced_gift'), 'url' => "log_version"),
        );
        //$tabs=array_filter($tabs);

        echo '<div class="woocommerce_page_rule_gift"><h2>';
        foreach ($tabs as $name => $a) {
            echo '<a href="' . admin_url('admin.php?page=rule_gift&tab=' . $a['url'] . '&pw_action_type=list') . '" class="nav-tab ';
            if ($current_tab == $a['url'])
                echo 'nav-tab-active';
            echo '">' . $a['name'] . '</a>';
        }
        echo '</h2></div>';

        if (@$_GET['tab'] == "localization") {
            require('core/admin/localization.php');
        }
        if (@$_GET['tab'] == "setting") {
            require('core/admin/setting.php');
        }
		if (@$_GET['tab'] == "log_version") {
			require('core/admin/log_version.php');
		}		
        if (@$_GET['tab'] == "report") {
            echo '
            <div class="pw-form-cnt">
            <div class="pw-form-content pw-report-tab">
	            <div class="pw-report-subtab">
		            <a href="' . admin_url('admin.php?page=rule_gift&tab=report&subtab=main&pw_action_type=list') . '" class="' . (!isset($_GET['subtab']) || $_GET['subtab'] == "main" ? "active-subtab" : '') . '">' . __('Dashboard', 'pw_wc_advanced_gift') . '</a>';

            echo '<a href="' . admin_url('admin.php?page=rule_gift&tab=report&subtab=rules&pw_action_type=list') . '" class="' . (isset($_GET['subtab']) && $_GET['subtab'] == "rules" ? "active-subtab" : '') . '">' . __('Rules', 'pw_wc_advanced_gift') . '</a>';

            echo '<a href="' . admin_url('admin.php?page=rule_gift&tab=report&subtab=customer&pw_action_type=list') . '" class="' . (isset($_GET['subtab']) && $_GET['subtab'] == "customer" ? "active-subtab" : '') . '">' . __('Customer', 'pw_wc_advanced_gift') . '</a>';

            echo '<a href="' . admin_url('admin.php?page=rule_gift&tab=report&subtab=guest&pw_action_type=list') . '" class="' . (isset($_GET['subtab']) && $_GET['subtab'] == "guest" ? "active-subtab" : '') . '">' . __('Guest', 'pw_wc_advanced_gift') . '</a>  
                </div>';

            if (@$_GET['subtab'] == "main" || !isset($_GET['subtab'])) {
                require('core/admin/report/main.php');
            }
            if (@$_GET['subtab'] == "customer") {
                require('core/admin/report/customer.php');
            }
            if (@$_GET['subtab'] == "rules") {
                require('core/admin/report/rules.php');
            }
            if (@$_GET['subtab'] == "guest") {
                require('core/admin/report/guest.php');
            }            
            echo '
				</div>
			</div>';
        }
        if (@$_GET['tab'] == "add_rule") {
            if (@$_GET['pw_action_type'] == "add" || @$_GET['pw_action_type'] == "list" || @$_GET['pw_action_type'] == "edit" && $_GET['tab'] == "add_rule") {
                if (@$_POST['pw_action_type'] == 'add' || @$_POST['pw_action_type'] == '' && isset($_POST['pw_id'])) {
                    include_once(PW_WC_GiIFT_URL . '/core/admin/add_rule.php');
                } else if (@$_POST['pw_action_type'] == 'edit' && isset($_POST['pw_name'])) {
                    include_once(PW_WC_GiIFT_URL . '/core/admin/edit_rule.php');
                }
                global $wpdb;
                $status = $pw_name = $disable_if = $pw_rule_description = $pw_gifts = $product_depends = $pw_product_depends = $category_depends= $exclude_category_depends = $pw_category_depends = $users_depends = $roles_depends = $pw_roles = $exclude_roles_depends = $pw_exclude_roles = $pw_users = $pw_cart_amount= $criteria_nb_products_max =$criteria_nb_products_min = $pw_cart_amount_min = $pw_cart_amount_max = $pw_from = $pw_to = $criteria_nb_products = $gift_preselector_product_page = $gift_auto_to_cart = $order_count = $gift_auto_to_cart = $order_op_count = $brand_depends = $pw_number_gift_allowed = $exclude_pw_category_depends = $pw_brand_depends = $is_coupons = $pw_gifts_metod = $criteria_nb_products_op = $cart_amount_op = $pw_gifts_category = $exclude_pw_category_depends_method= $pw_category_depends_method = $pw_product_depends_method = $pw_brand_depends_method = $pw_limit_per_rule = $pw_limit_per_rule_cunter = $pw_limit_per_user =$repeat = $pw_register_user = $schedule_type = $pw_weekly = $pw_monthly = $can_several_gift = $gift_notify_add =$exclude_product_depends=$pw_exclude_product_depends = "";
                $pw_action_type = 'add';
                if (@$_GET['pw_action_type'] == "edit") {
                    $pw_action_type = 'edit';
                    if (isset($_GET['pw_id']) && get_post_status($_GET['pw_id'])) {
                        $status = get_post_meta($_GET['pw_id'], 'status', true);
                        $pw_number_gift_allowed = get_post_meta($_GET['pw_id'], 'pw_number_gift_allowed', true);
                        $pw_name = get_post_meta($_GET['pw_id'], 'pw_name', true);
                        $pw_rule_description = get_post_meta($_GET['pw_id'], 'pw_rule_description', true);
                        $pw_gifts = get_post_meta($_GET['pw_id'], 'pw_gifts', true);
                        $pw_gifts_metod = get_post_meta($_GET['pw_id'], 'pw_gifts_metod', true);
                        $pw_gifts_category = get_post_meta($_GET['pw_id'], 'pw_gifts_category', true);
                        $product_depends = get_post_meta($_GET['pw_id'], 'product_depends', true);
                        $pw_product_depends = get_post_meta($_GET['pw_id'], 'pw_product_depends', true);
                        $pw_product_depends_method = get_post_meta($_GET['pw_id'], 'pw_product_depends_method', true);
                        $category_depends = get_post_meta($_GET['pw_id'], 'category_depends', true);
                        $exclude_category_depends = get_post_meta($_GET['pw_id'], 'exclude_category_depends', true);
                        $pw_category_depends = get_post_meta($_GET['pw_id'], 'pw_category_depends', true);
                        $pw_category_depends_method = get_post_meta($_GET['pw_id'], 'pw_category_depends_method', true);
                        $exclude_pw_category_depends = get_post_meta($_GET['pw_id'], 'exclude_pw_category_depends', true);
                        $users_depends = get_post_meta($_GET['pw_id'], 'users_depends', true);
                        $pw_users = get_post_meta($_GET['pw_id'], 'pw_users', true);
                        $roles_depends = get_post_meta($_GET['pw_id'], 'roles_depends', true);
                        $pw_roles = get_post_meta($_GET['pw_id'], 'pw_roles', true);                        
						$exclude_roles_depends = get_post_meta($_GET['pw_id'], 'exclude_roles_depends', true);
                        $pw_exclude_roles = get_post_meta($_GET['pw_id'], 'pw_exclude_roles', true);
                        $order_count = get_post_meta($_GET['pw_id'], 'order_count', true);
                        $is_coupons = get_post_meta($_GET['pw_id'], 'is_coupons', true);
                        $pw_cart_amount = get_post_meta($_GET['pw_id'], 'pw_cart_amount', true);
                        $pw_cart_amount_min = get_post_meta($_GET['pw_id'], 'pw_cart_amount_min', true);
                        $pw_cart_amount_max = get_post_meta($_GET['pw_id'], 'pw_cart_amount_max', true);
                        $criteria_nb_products_max = get_post_meta($_GET['pw_id'], 'criteria_nb_products_max', true);
                        $criteria_nb_products_min = get_post_meta($_GET['pw_id'], 'criteria_nb_products_min', true);
                        $pw_from = get_post_meta($_GET['pw_id'], 'pw_from', true);
                        $order_op_count = get_post_meta($_GET['pw_id'], 'order_op_count', true);
                        $criteria_nb_products_op = get_post_meta($_GET['pw_id'], 'criteria_nb_products_op', true);
                        $cart_amount_op = get_post_meta($_GET['pw_id'], 'cart_amount_op', true);
                        $pw_to = get_post_meta($_GET['pw_id'], 'pw_to', true);
                        $criteria_nb_products = get_post_meta($_GET['pw_id'], 'criteria_nb_products', true);
                        $gift_preselector_product_page = get_post_meta($_GET['pw_id'], 'gift_preselector_product_page', true);
                        $disable_if = get_post_meta($_GET['pw_id'], 'disable_if', true);
                        $gift_auto_to_cart = get_post_meta($_GET['pw_id'], 'gift_auto_to_cart', true);
                        $pw_limit_per_rule = get_post_meta($_GET['pw_id'], 'pw_limit_per_rule', true);
                        $pw_limit_per_user = get_post_meta($_GET['pw_id'], 'pw_limit_per_user', true);
                        $pw_limit_cunter = get_post_meta($_GET['pw_id'], 'pw_limit_cunter', true);
                        $pw_register_user = get_post_meta($_GET['pw_id'], 'pw_register_user', true);
                        $schedule_type = get_post_meta($_GET['pw_id'], 'schedule_type', true);
                        $repeat = get_post_meta($_GET['pw_id'], 'repeat', true);
                        $pw_weekly = get_post_meta($_GET['pw_id'], 'pw_weekly', true);
                        $pw_daily = get_post_meta($_GET['pw_id'], 'pw_daily', true);
                        $pw_monthly = get_post_meta($_GET['pw_id'], 'pw_monthly', true);
                        $gift_notify_add = get_post_meta($_GET['pw_id'], 'gift_notify_add', true);
                        $can_several_gift = get_post_meta($_GET['pw_id'], 'can_several_gift', true);
                        $exclude_product_depends = get_post_meta($_GET['pw_id'], 'exclude_product_depends', true);
                        $pw_exclude_product_depends = get_post_meta($_GET['pw_id'], 'pw_exclude_product_depends', true);
                        if (defined('plugin_dir_url_pw_woo_brand')) {
                            $brand_depends = get_post_meta($_GET['pw_id'], 'brand_depends', true);
                            $pw_brand_depends = get_post_meta($_GET['pw_id'], 'pw_brand_depends', true);
                            $pw_brand_depends_method = get_post_meta($_GET['pw_id'], 'pw_brand_depends_method', true);
                        }
                    } else {
                        echo '<div>' . __('Rule is Deleted', 'pw_wc_advanced_gift') . '</div>';
                        exit;
                    }
                }
                include_once(PW_WC_GiIFT_URL . '/core/admin/add_edit_rule.php');

            }
        } else {
            if (@$_GET['pw_action_type'] == "list_product") {
            }
            require('core/admin/admin-core.php');
        }
        echo '<input type="hidden" name="page" value="' . esc_attr($_REQUEST['page']) . '" />';

    }

    public function get_all_product_list()
    {
        $result = array('' => '');

        $posts_raw = get_posts(array(
            'posts_per_page' => -1,
            'post_type' => 'product',
            'post_status' => array('publish', 'private', 'inherit'),
            'fields' => 'ids',
        ));
        return $posts_raw;
    }

    public function get_all_category_list()
    {
        $result = array('' => '');
        $args = array('hide_empty=0');
        $result = get_terms('product_cat', $args);
        return $result;
    }

    public function get_product($id)
    {

        if (version_compare(WOOCOMMERCE_VERSION, "2.4.0") >= 0) {
            $p = wc_get_product($id);
        } else {
            $p = get_product($id);
        }

        return $p;

    }

    public function my_custom_menu_page()
    {
        $current_tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'Pricing Rules';
        $tabs = array(
            array('name' => "Gift Rule", 'url' => "pricing_rules"),
            array('name' => "Cart Discounts", 'url' => "cart_discounts"),
        );
        echo '<h2>';
        foreach ($tabs as $name => $a) {
            echo '<a href="' . admin_url('admin.php?page=my_custom_menu_page&tab=' . $a['url'] . '&pw_action_type=list') . '" class="nav-tab ';
            if ($current_tab == $name)
                echo 'nav-tab-active';
            echo '">' . $a['name'] . '</a>';
        }
        echo '</h2>';
    }

    public function install()
    {
		
        if ('' == get_option('pw_gift_options')) {
            $setting = array();
            $setting['show_popup'] = "yes";
            $setting['multiselect_gift_count'] = "1";
            $setting['multiselect_cart_amount'] = "0";
            //$setting['predifine_active']="no";
            $setting['auto_add'] = "no";
            $setting['multiselect'] = "no";
            $setting['predifine_gift'] = "no";

            $setting['popup_pw_item_width'] = "200";
            $setting['popup_pw_item_marrgin'] = "10";
            $setting['popup_pw_show_pagination'] = "false";
            $setting['popup_pw_show_control'] = "true";
            $setting['popup_pw_item_per_view'] = "4";
            $setting['popup_pw_item_per_slide'] = "1";
            $setting['popup_pw_slide_speed'] = "4000";
            $setting['popup_pw_auto_play'] = "true";
            $setting['hide_popup_in_mobile'] = "true";

            $setting['pw_item_width'] = "200";
            $setting['pw_item_marrgin'] = "10";
            $setting['pw_show_pagination'] = "false";
            $setting['pw_show_control'] = "true";
            $setting['pw_item_per_view'] = "4";
            $setting['pw_item_per_slide'] = "1";
            $setting['pw_slide_speed'] = "4000";
            $setting['pw_auto_play'] = "true";
            $setting['pw_slide_rtl'] = "false";
            $setting['number_per_page'] = "6";
            $setting['popup_title'] = "Our gifts";
            $setting['cart_title'] = "Our gifts";
            $setting['Hour'] = "Hour";
            $setting['free'] = "free";
            $setting['Minutes'] = "Minutes";
            $setting['Seconds'] = "Seconds";
            $setting['view_cart_gift'] = "grid";
            $setting['desktop_columns'] = "wg-col-md-3";
            $setting['tablet_columns'] = "wg-col-sm-12";
            $setting['mobile_columns'] = "wg-col-xs-12";
            $setting['hide_gifts_after_select'] = "no";
            $setting['add_gift'] = "ADD GIFT";
            $setting['txt_single_product'] = "product gift(s)";
            $setting['select_gift'] = "Select Gift";
            $setting['expire'] = 60;
            update_option('pw_gift_options', $setting);
        }
        update_option('pw_wc_advanced_gift', 'install');
    }

    public function admin_enqueue_scripts_function()
	{
		//DataTables Css
		wp_register_style('pro-gift-datatables-css', plugin_dir_url_wc_advanced_gift . 'css/datatables/css/jquery.dataTables.min.css');
		//DataTables JS
		wp_register_script('pro-gift-datatables-js', plugin_dir_url_wc_advanced_gift . 'css/datatables/js/jquery.dataTables.min.js', array('jquery'));
		if(isset($_REQUEST['tab']) && $_REQUEST['tab']=='report'){
		//Chart
			wp_enqueue_script('it_amcharts', 'https://www.amcharts.com/lib/3/amcharts.js', array('jquery'));
			wp_enqueue_script('it_pie', 'https://www.amcharts.com/lib/3/pie.js', array('jquery'));
			wp_enqueue_script('it_serial', 'https://www.amcharts.com/lib/3/serial.js', array('jquery'));
			wp_enqueue_script('it_export.min', 'https://www.amcharts.com/lib/3/plugins/export/export.min.js', array('jquery'));
			wp_enqueue_script('it_light', 'https://www.amcharts.com/lib/3/themes/light.js', array('jquery'));
			wp_enqueue_style('it_export.css', 'https://www.amcharts.com/lib/3/plugins/export/export.css', true);
		}

		/*	Admin Css	*/
		wp_enqueue_style('cart_rule_gift', plugin_dir_url_wc_advanced_gift . 'css/admin-css.css');

		wp_enqueue_style('pw-gift-datepicher-style', plugin_dir_url_wc_advanced_gift . 'css/jquery.datetimepicker.css');

		wp_enqueue_style('pw-gift-chosen-style', plugin_dir_url_wc_advanced_gift . 'css/chosen/chosen.css', array(), null);

		//CountDown
		wp_enqueue_style('flipclock-master-cssss', plugin_dir_url_wc_advanced_gift . 'css/frontend/countdown/jquery.countdown.css');

		//JS
		wp_enqueue_script('pw-gift-chosen-script', plugin_dir_url_wc_advanced_gift . 'js/chosen/chosen.jquery.min.js', array('jquery'));

		wp_enqueue_script('pw-gift-datepicher-script', plugin_dir_url_wc_advanced_gift . 'js/jquery.datetimepicker.js', array('jquery'));

		//wp_enqueue_script('jquery');
		wp_enqueue_script('pw-dependsOn-gift', plugin_dir_url_wc_advanced_gift . 'js/dependsOn-1.0.1.min.js', array('jquery'));
		//CountDown
		wp_enqueue_style('flipclock-master-cssss', plugin_dir_url_wc_advanced_gift . 'css/frontend/countdown/jquery.countdown.css');
		//CountDown
		wp_enqueue_script('flipclocksdsd-master-jsaaaa', plugin_dir_url_wc_advanced_gift . 'js/frontend/countdown/jquery.countdown.min.js', array('jquery'));		

		wp_enqueue_style('font_awesome', plugin_dir_url_wc_advanced_gift . 'css/font-awesome/all.min.css');
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');			
		
	}
	
    public function front_end_woo_advanced_gift_js_css()
	{		
		//JS
		wp_enqueue_script("jquery");
		
		$this->setting = get_option("pw_gift_options");
		//grid
		wp_enqueue_style('pw-gift-grid-style', plugin_dir_url_wc_advanced_gift . 'css/frontend/grid/grid.css');
		/*if($this->setting["view_cart_gift"] == "grid")
		{

		}*/
		//else{
			wp_enqueue_style('pw-gift-slider-style', plugin_dir_url_wc_advanced_gift . 'css/frontend/slider/owl.carousel.css');
			wp_enqueue_script('pw-gift-slider-jquery', plugin_dir_url_wc_advanced_gift . 'js/frontend/slider/owl.carousel.js');
		//}
		
		
        $cart_page_id = wc_get_page_id('cart');
        $cart_page_id = get_permalink($cart_page_id);
        if (substr($cart_page_id, -1) == "/") {
            $cart_page_id = substr($cart_page_id, 0, -1);
        }
		wp_enqueue_script('pw-gift-add-jquery', plugin_dir_url_wc_advanced_gift . 'js/frontend/add_gift.js');		
        wp_localize_script('pw-gift-add-jquery', 'pw_wc_gift_adv_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('jkhKJSdd4576d'),
            'action_add_gift' => 'handel_pw_gift_add_adv',
            'action_show_variation' => 'handel_pw_gift_show_variation',
            'cart_page_id' => $cart_page_id,
        ));
		
		wp_enqueue_style('flash_sale_shortcodes', plugin_dir_url_wc_advanced_gift . 'includes/shortcodes.css');

		wp_register_script('flash_sale_shortcodes_js', plugin_dir_url_wc_advanced_gift . 'includes/shortcodes.js', 'jquery');
		wp_enqueue_script('flash_sale_shortcodes_js');	
		
		wp_enqueue_style('pw-gift-layout-style', plugin_dir_url_wc_advanced_gift . 'css/frontend/layout/layout.css', array(), null);

		//CountDown
		wp_enqueue_style('flipclock-master-cssss', plugin_dir_url_wc_advanced_gift . 'css/frontend/countdown/jquery.countdown.css');

		//lightbox
	//	wp_enqueue_style('pw-gift-lightbox-css', plugin_dir_url_wc_advanced_gift . 'css/frontend/lightbox/lightcase.css');

		//Lightbox
	//	wp_enqueue_script('pw-gift-slightbx-jquery', plugin_dir_url_wc_advanced_gift . 'js/frontend/lightbox/lightcase.js');
	}

    function wc_gift_shortcodes_addbuttons()
    {
        global $typenow;
        // check user permissions
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }
        // check if WYSIWYG is enabled
        if (get_user_option('rich_editing') == 'true') {
            add_filter("mce_external_plugins", array($this, "add_wc_gift_shortcodes_tinymce_plugin"));
            add_filter('mce_buttons', array($this, 'register_wc_gift_shortcodes_button'));
        }
    }

    function add_wc_gift_shortcodes_tinymce_plugin($plugin_array)
    {
        $plugin_array['wc_gift_shortcodes_button'] = plugins_url('/includes/tinymce_button.js', __FILE__);
        return $plugin_array;
    }

    function register_wc_gift_shortcodes_button($buttons)
    {
        array_push($buttons, "wc_gift_shortcodes_button");
        return $buttons;
    }
}

new woocommerce_advanced_gift();


add_action('wp_ajax_pw_fetch_rule_gift', 'pw_fetch_rule_gift');
add_action('wp_ajax_nopriv_pw_fetch_rule_gift', 'pw_fetch_rule_gift');
function pw_fetch_rule_gift()
{
    $query_meta_query = array('relation' => 'AND');
    $query_meta_query[] = array(
        'key' => 'status',
        'value' => "active",
        'compare' => '=',
    );
    $args = array(
        'post_type' => 'pw_gift_rule',
        'numberposts' => -1,
        'post_status' => 'publish',
        'meta_query' => $query_meta_query,
    );
    $loop = new WP_Query($args);

    while ($loop->have_posts()) :
        $loop->the_post();
        echo '<option value="' . get_the_ID() . '">
					' . get_post_meta(get_the_ID(), 'pw_name', true) . '
				</option>';
    endwhile;

    exit(0);
}

add_action('wp_ajax_pw_rest_usage_rule_gift', 'pw_rest_usage_rule_gift');
add_action('wp_ajax_nopriv_pw_rest_usage_rule_gift', 'pw_rest_usage_rule_gift');
function pw_rest_usage_rule_gift()
{
    $pw_limit_per_usera = get_post_meta($_POST['pw_id'], 'pw_limit_cunter', true);
    $array_user_info = array(
        'count' => 0,
        'user_info' => $pw_limit_per_usera['user_info'],
    );
    update_post_meta(@$_POST['pw_id'], 'pw_limit_cunter', @$array_user_info);
    echo '1';
    die;
}

?>