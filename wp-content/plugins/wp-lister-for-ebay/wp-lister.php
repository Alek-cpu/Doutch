<?php
/* 
Plugin Name: WP-Lister Lite for eBay
Plugin URI: https://www.wplab.com/plugins/wp-lister/
Description: List your products on eBay the easy way.
Version: 2.6.2
Author: WP Lab
Author URI: https://www.wplab.com/ 
Max WP Version: 5.4.2
WC requires at least: 3.0.0
WC tested up to: 4.3.1
Text Domain: wp-lister-for-ebay
Domain Path: /languages
License: GPL2+
*/

if ( class_exists('WPL_WPLister') ) die(sprintf( 'WP-Lister for eBay %s is already installed and activated. Please deactivate any other version before you activate this one.', WPLE_PLUGIN_VERSION ));

define('WPLE_PLUGIN_VERSION', '2.6.2' );
define('WPLE_PLUGIN_PATH', realpath( dirname(__FILE__) ) );
define('WPLE_PLUGIN_URL', plugins_url() . '/' . basename(dirname(__FILE__)) . '/' );

// backwards compatibility for add-ons - will be removed in next major release (2.4)
define('WPLISTER_VERSION', WPLE_PLUGIN_VERSION );
define('WPLISTER_PATH', WPLE_PLUGIN_PATH );
define('WPLISTER_URL', WPLE_PLUGIN_URL );

// force production error reporting level
if ( get_option('wplister_php_error_handling') == '9' ) error_reporting( E_ERROR );

// include base classes
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_Autoloader.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_Functions.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_Core.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_BasePlugin.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_Logger.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_StocksLogger.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_EbatNs_Logger.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_Page.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_Model.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_CronActions.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_AjaxHandler.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_Setup.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_Install_Uninstall.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_Toolbar.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/WPL_API_Hooks.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/core/EbayController.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/integration/WooBackendIntegration.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/integration/WooFrontendIntegration.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/integration/WooOrderBuilder.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/integration/WooOrderMetaBox.php' );
require_once( WPLE_PLUGIN_PATH . '/classes/integration/OrderWrapper_woo.php' ); // Added to fix "Class 'OrderWrapper' not found in wp-lister-ebay.php:173"

// set up autoloader
spl_autoload_register('WPL_Autoloader::autoload');

if ( ! defined('WPLE_IS_LITE_VERSION')) define('WPLE_IS_LITE_VERSION', true );


if ( ! class_exists('WPL_WPLister') ) {
class WPL_WPLister extends WPL_BasePlugin {
	
	var $pages         = array();
	var $accounts      = array();
	var $multi_account = false;
	var $db_version    = 0;
	var $logger;

	/**
	 * @var WPLE_MemCache
	 */
	var $memcache;

	protected static $_instance = null;
	
	// get singleton instance
    public static function get_instance() {

        if ( is_null( self::$_instance ) ) {
        	self::$_instance = new self();
        }

        return self::$_instance;
    }

	public function __construct() {
		parent::__construct();

		// load current DB version
		$this->db_version = get_option('wplister_db_version');

		$this->initLogger();
		$this->initClasses();
		$this->loadAccounts();

		if ( is_admin() ) {
			require_once( WPLE_PLUGIN_PATH . '/classes/integration/WooProductMetaBox.php' );
			// require_once( WPLE_PLUGIN_PATH . '/classes/integration/WooOrderMetaBox.php' );
			// require_once( WPLE_PLUGIN_PATH . '/classes/integration/WooEbayProduct.php' );
			$oInstall 	= new WPLister_Install( __FILE__ );
			$oUninstall = new WPLister_Uninstall( __FILE__ );
			$this->loadPages();
		}

	}
		
	// initialize logger
	public function initLogger() {
		// global $wpl_logger;

        // Check if this constant has already been previously defined
        // Somehow, the notice "Constant WPLISTER_DEBUG already defined" always shows up in #33893
        if ( ! defined( 'WPLISTER_DEBUG' ) ) {
            define( 'WPLISTER_DEBUG', get_option('wplister_log_level') );
        }

		$this->logger = new WPL_Logger();

        // Only listen to stock changes if WPLA is not available
        if ( !class_exists( 'WPLA_Core' ) ) {
            new WPL_StocksLogger();
        }
	}
		
	// initialize core classes
	public function initClasses() {

		// $this->api_hooks      = new WPLE_API_Hooks();	
		// $this->ajax_hactions  = new WPLE_AjaxHandler();
		// $this->cron_actions   = new WPLE_CronActions();
		// $this->toolbar        = new WPLE_Toolbar();
		$this->memcache       = new WPLE_MemCache();
		$this->messages       = new WPLE_AdminMessages();
		$this->restapi        = new WPLE_Rest_Controller();
		
	}

	public function loadAccounts() {
		// $accounts = $this->db_version > 37 ? WPLE_eBayAccount::getAll( true ) : array();
		$accounts = get_option('wplister_db_version') > 37 ? WPLE_eBayAccount::getAll( true ) : array();
		foreach ($accounts as $account) {
			$this->accounts[ $account->id ] = $account;
		}
		$this->multi_account = count( $this->accounts ) > 1 ? true : false;
	}
		
	public function loadPages() {

		if ( is_network_admin() ) {
	
			$this->pages['sites']    	 = new NetworkAdminPage();
			$this->pages['settings']     = new SettingsPage();
	
		} else {

			if ( ( is_multisite() ) && ( self::getOption('is_enabled') == 'N' ) ) return;

			$this->pages['listings']     = new ListingsPage();
			$this->pages['grid']         = new WPLE_GridEditorPage();
			$this->pages['profiles']     = new ProfilesPage();
			$this->pages['templates']    = new TemplatesPage();
			$this->pages['transactions'] = new TransactionsPage();
			$this->pages['orders']       = new EbayOrdersPage();
			$this->pages['messages']     = new EbayMessagesPage();
			$this->pages['tools']        = new ToolsPage();
			$this->pages['settings']     = new SettingsPage();
			$this->pages['accounts']     = new WPLE_AccountsPage();
			$this->pages['tutorial']     = new HelpPage();
			$this->pages['log']          = new LogPage();

		}

	}
		
	public function onWpInit() {

		// load language
		load_plugin_textdomain( 'wp-lister-for-ebay', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// add cron handler
		// add_action('wplister_update_auctions', array( &$this, 'cron_update_auctions' ) );

	}

	public function onWpAdminInit() {

		add_action( 'admin_print_styles', array( &$this, 'onWpPrintStyles' ) );

    	// add / fix enqueued scripts - only on wplister pages
    	if  ( ( isset( $_GET['page'] ) ) && ( substr( $_GET['page'], 0, 8 ) == 'wplister') ) {
		    add_action( 'wp_print_scripts', array( &$this, 'onWpPrintScripts' ), 99 );
    	}

    	// modify bulk actions menu - only on products list page
		if ( ProductWrapper::isProductsPage() ) {
			add_action( 'admin_enqueue_scripts', array( &$this, 'modifyProductsBulkActionMenu'  ),  9 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'modifyProductsBulkActionMenu2' ), 99 );
			add_action( 'admin_print_styles',    array( &$this, 'printProductsPageStyles'       ) );
			add_action( 'wp_print_scripts',      array( &$this, 'printProductsPageScripts'      ) );
		}
		add_action( 'admin_print_styles', array( &$this, 'printOrdersPageStyles' ) );

		$this->checkPermissions();
	}
	
	public function onWpPrintStyles() {
		if  ( ( isset( $_GET['page'] ) ) && ( substr( $_GET['page'], 0, 8 ) == 'wplister') ) {
			wp_register_style( 'wplister_style', self::$PLUGIN_URL.'css/style.css', array(), WPLE_PLUGIN_VERSION );
			wp_enqueue_style( 'wplister_style' );
		}
	}

	// add custom bulk action 'prepare_auction' for cpt products
	// should be called by 'admin_footer' action
	public function modifyProductsBulkActionMenu() {	
		if ( ! current_user_can( 'prepare_ebay_listings' ) ) return;

		wc_enqueue_js('
    	    jQuery(document).ready(function() {
        	    jQuery("<option>").val("wple_prepare_auction").text("' . __( 'List on eBay', 'wp-lister-for-ebay' ) . '").appendTo("select[name=\'action\']");
            	jQuery("<option>").val("wple_prepare_auction").text("' . __( 'List on eBay', 'wp-lister-for-ebay' ) . '").appendTo("select[name=\'action2\']");
	        });
		');

	}

	// add custom bulk action 'remove_from_ebay' for cpt products
	public function modifyProductsBulkActionMenu2() {
		if ( ! current_user_can( 'manage_ebay_listings' ) ) return;

		wc_enqueue_js('
    	    jQuery(document).ready(function() {
        	    jQuery("<option>").val("wple_remove_from_ebay").text("' . __( 'End listings on eBay', 'wp-lister-for-ebay' ) . '").appendTo("select[name=\'action\']");
            	jQuery("<option>").val("wple_remove_from_ebay").text("' . __( 'End listings on eBay', 'wp-lister-for-ebay' ) . '").appendTo("select[name=\'action2\']");
	        });

		    jQuery(".tablenav .actions input[type=\'submit\'].action").on("click", function() {

		        if ( "doaction"  == this.id ) var selected_action = jQuery("select[name=\'action\']").first().val();
		        if ( "doaction2" == this.id ) var selected_action = jQuery("select[name=\'action2\']").first().val();

				if ( selected_action == "wple_remove_from_ebay" ) {
					var confirmed = confirm("' . __( 'Are you sure you want to do this?', 'wp-lister-for-ebay' ) .' '.  __('Ending the listing also removes the sales history for the item. If you were to relist these listings later you would then start out with a lower sales rank.', 'wp-lister-for-ebay' ) . '");
					if ( ! confirmed ) return false;
				}

		    });
		');

	}

	public function printProductsPageScripts() {

		// ProfileSelector
		wp_register_script( 'wple_profile_selector', self::$PLUGIN_URL.'js/classes/ProfileSelector.js', array( 'jquery' ), WPLE_PLUGIN_VERSION );
		wp_enqueue_script ( 'wple_profile_selector' );
		wp_localize_script( 'wple_profile_selector', 'wple_ProfileSelector_i18n', array(
				'WPLE_URL' 	      => WPLE_PLUGIN_URL,
				'wple_ajax_nonce' => wp_create_nonce('wple_ajax_nonce')
			)
		);

	}
	
	public function printProductsPageStyles() {	
		?>
    	<style type="text/css">
			table.wp-list-table .column-listed_on_ebay { width: 25px; }    	
    	</style>
    	<?php
	}
	public function printOrdersPageStyles() {	
		?>
    	<style type="text/css">
			.post-type-shop_order table.wp-list-table .column-wpl_order_src { width: 56px; text-align: center; padding-left: 1px; padding-right: 1px; }

			@media screen and (max-width: 782px) {
				.post-type-shop_order table.wp-list-table .column-wpl_order_src { display: none !important; }
			}
    	</style>
    	<?php
	}

	public function onWpPrintScripts() {
		global $wp_scripts;

    	// fix thickbox display problems caused by other plugins 
        wp_dequeue_script( 'media-upload' );
        
        // if any registered script depends on media-upload, dequeue that too
        foreach ( $wp_scripts->registered as $script ) {
            if ( in_array( 'media-upload', $script->deps ) ) {
                wp_dequeue_script( $script->handle );
            }
        }

        // enqueue tipTip.js 
        wp_register_script( 'jquery-tiptip', WPLE_PLUGIN_URL . 'js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WPLE_PLUGIN_VERSION, true );
        wp_enqueue_script( 'jquery-tiptip' );

	}
	
} // class WPL_WPLister
} // if class does not exists

// instantiate plugin
global $oWPL_WPLister; // keep backward compatibility for importer add-on
$oWPL_WPLister = WPL_WPLister::get_instance();
