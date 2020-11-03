<?php
/*
 * Plugin Name: WooCommerce Table Rate Shipping
 * Plugin URI: http://bolderelements.net/plugins/table-rate-shipping-woocommerce/
 * Description: WooCommerce custom plugin designed to calculate shipping costs and add one or more rates based on a table of rules
 * Author: Bolder Elements
 * Author URI: http://www.bolderelements.net/
 * Text Domain: be-table-ship
 * Version: 4.2.1
 * WC requires at least: 3.2.0
 * WC tested up to: 4.1.0

	Copyright: © 2014-2019 Bolder Elements (email : info@bolderelements.net)
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) )
	exit;

add_action('plugins_loaded', 'woocommerce_table_rate_shipping_init', 0);

function woocommerce_table_rate_shipping_init() {

	//Check if WooCommerce is active
	if ( ! class_exists( 'WooCommerce' ) ) return;

	// Ensure there are not duplicate classes
	if ( class_exists( 'BE_Table_Rate_WC' ) ) return;

	// setup internationalization support
	load_plugin_textdomain('be-table-ship', false, 'woocommerce-table-rate-shipping/languages');

	// include Envato plugin updater file
	include_once( plugin_dir_path( __FILE__ ) . 'inc/envato-market-installer.php' );

	// included deprecated method for prior users
	$betrs_legacy_options = get_option( 'woocommerce_table_rate_shipping_settings' );
	if ( $betrs_legacy_options && isset( $betrs_legacy_options['enabled'] ) && 'yes' === $betrs_legacy_options['enabled'] ) {
		include_once( plugin_dir_path( __FILE__ ) . 'deprecated/woocommerce-table-rate-shipping.php' );
	}

	class BE_Table_Rate_WC {

		/*
		 * Table Rates Class
		 */
		public $table_rates;

		/*
		 * Allowed HTML tags for descriptions
		 */
		public $allowedtags;

		/**
		 * Cloning is forbidden. Will deactivate prior 'instances' users are running
		 *
		 * @since 4.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cloning this class could cause catastrophic disasters!', 'be-table-ship' ), '4.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 4.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Unserializing is forbidden!', 'be-table-ship' ), '4.0' );
		}

		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		function __construct() {
			// Include required files
			if( is_admin() ) {
				// Admin only includes
				add_action( 'admin_enqueue_scripts', array( $this, 'register_plugin_admin' ) );
				add_action( 'admin_footer', array( $this, 'add_script_admin' ) );
			}

			add_action( 'woocommerce_init', array( $this, 'includes' ), 0 );
			add_action( 'woocommerce_after_shipping_rate', array( $this, 'display_option_description' ), 10, 2 );
			add_action( 'woocommerce_order_shipping_to_display', array( $this, 'display_option_description_review' ), 10, 2 );
			add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );
			add_filter( 'woocommerce_screen_ids', array( $this, 'add_settings_screen' ) );

		}


		/**
		 * setup included files
		 *
		 * @access public
		 * @return void
		 */
		function includes() {
			/**
			 * Description allowed HTML elements.
			 */
			$this->allowedtags = apply_filters( 'betrs_desc_allowed_tags', array(
					'a' => array(
						'href' => true,
						'title' => true,
					),
					'abbr' => array(
						'title' => true,
					),
					'acronym' => array(
						'title' => true,
					),
					'b' => array(),
					'blockquote' => array(
						'cite' => true,
					),
					'br' => array(),
					'cite' => array(),
					'code' => array(),
					'del' => array(
						'datetime' => true,
					),
					'em' => array(),
					'i' => array(),
					'p' => array(
						'align' => true,
						'dir' => true,
						'lang' => true,
						'xml:lang' => true,
					),
					'q' => array(
						'cite' => true,
					),
					's' => array(),
					'strike' => array(),
					'strong' => array(),
				)
			);

			// Setup compatibility functions
			include_once( 'compatibility/comp.wpml.php' );
			include_once( 'compatibility/comp.currency_switcher.php' );
			include_once( 'compatibility/comp.wmc.php' );
			include_once( 'compatibility/comp.polylang.php' );
			include_once( 'compatibility/comp.dokan.php' );

			// Setup shipping method
			include_once( 'inc/class.shipping-method.php' );
			include_once( 'inc/class.table-rate_options.php' );
			include_once( 'inc/class.calculate-rates.php' );

			// Setup additional settings requirements
			include_once( 'inc/class.settings-shipping-classes.php' );
			include_once( 'inc/class.settings-table-rates.php' );
			$this->table_rates = new BETRS_Table_Rates();

			include_once( 'inc/class.settings-method-conditions.php' );
			$this->method_conditions = new BE_Table_Rate_Method_Conditions();

			// Inclued dashboard only files
			if( is_admin() ) {
				include_once( 'inc/admin/class.user-management.php' );
			}

		}

		/**
		 * add_cart_rate_method function.
		 *
		 * @package		WooCommerce/Classes/Shipping
		 * @access public
		 * @param array $methods
		 * @return array
		 */
		function add_shipping_method( $methods ) {
			$methods['betrs_shipping'] = 'BE_Table_Rate_Method';
			//$methods['table_rate_shipping'] = 'BE_Table_Rate_Method';
			return $methods;
		}


	    /**
	     * display description if applicable.
	     *
	     * @access public
	     * @param mixed $method
	     * @return void
	     */
	    function display_option_description( $method, $index ) {
	    	global $betrs_shipping;

	    	$meta_data = $method->get_meta_data();
	    	if( isset( $meta_data['description'] ) && is_object( $meta_data['description'] ) )
	    		echo '<div class="betrs_option_desc">' . stripslashes( wp_kses( $meta_data['description']->scalar, $betrs_shipping->allowedtags ) ) . '</div>';
	    }


	    /**
	     * display description if applicable.
	     *
	     * @access public
	     * @param mixed $method
	     * @return void
	     */
	    function display_option_description_review( $shipping, $order ) {
		    global $betrs_shipping;
		    
		    foreach( $order->get_shipping_methods() as $method ) {
		    	if( ! is_object( $method ) || ! method_exists( $method, 'get_meta_data' ) )
		    		continue;

		        $meta_data = $method->get_meta_data();
		        foreach( $meta_data as $meta ) {
		        	if( method_exists( $meta, 'get_data' ) ) {
			            $data = $meta->get_data();
			            if( $data['key'] == 'description' && ! empty( $data['value'] ) )
			            $shipping .= '<div class="betrs_option_desc betrs_order_review">' . stripslashes( wp_kses( $data['value']->scalar, $betrs_shipping->allowedtags ) ) . '</div>';
			    	}
		        }
		    }
		    return $shipping;
		}


        /**
         * add settings page to list of WC settings
		 *
		 * @access public
		 * @return void
         */
		public function add_settings_screen( $current_screens ) {

			$current_screens[] = 'toplevel_page_betrs-manage-shipping';
			return $current_screens;
		}



		/**
		 * Modify Scripts in Dashboard
		 *
		 * @access public
		 * @return void
		 */
		public function register_plugin_admin( $hook_suffix ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'jquery-ui-widget' );
			wp_enqueue_script( 'jquery-ui-position' );
			wp_enqueue_script( 'jquery-ui-button' );
			wp_enqueue_script( 'jquery-ui-menu' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'betrs_settings_js', plugins_url( 'assets/js/settings' . $suffix . '.js', __FILE__ ), array( 'jquery' ), null, true );
			wp_enqueue_script( 'betrs_settings_table_rates_js', plugins_url( 'assets/js/settings.table-rates' . $suffix . '.js', __FILE__ ), array( 'jquery' ), null, true );
			wp_enqueue_script( 'comiseo.daterangepicker', plugins_url( 'assets/js/jquery.comiseo.daterangepicker.js', __FILE__ ), array( 'jquery', 'jquery-ui-widget', 'jquery-ui-position', 'jquery-ui-button', 'jquery-ui-menu', 'jquery-ui-datepicker' ), false, true );
			wp_enqueue_script( 'moment.js', plugins_url( 'assets/js/moment.min.js', __FILE__ ), array( 'jquery' ), false, true );

			wp_enqueue_style( 'betrs_dashboard_css', plugins_url( 'assets/css/dashboard.css', __FILE__ ), false, null );
			wp_enqueue_style( 'comiseo.daterangepicker', plugins_url( 'assets/css/jquery.comiseo.daterangepicker.css', __FILE__ ), false, true );

			// user management page only
			if( get_current_screen() == 'toplevel_page_betrs-manage-shipping' ) {
				wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
				wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.0' );
				wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
				wp_localize_script( 'wc-enhanced-select', 'wc_enhanced_select_params', array(
					'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
					'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
					'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
					'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
					'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
					'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
					'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
					'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
					'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
					'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
					'ajax_url'                  => admin_url( 'admin-ajax.php' ),
					'search_products_nonce'     => wp_create_nonce( 'search-products' ),
					'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
				) );
			}
		}


		/**
		 * Add Script Directly to Dashboard Foot
		 */
		public function add_script_admin() {
			$betrs_data = array();

			// Setup translated strings
			$betrs_data = array(
				'ajax_url'					=> addcslashes( admin_url( 'admin-ajax.php', 'relative' ), '/' ),
				'ajax_loader_url'			=> plugins_url( 'assets/img/loader.gif', __FILE__ ),
				'text_ok'					=> __( 'OK' ),
				'text_edit'					=> __( 'Edit' ),
				'text_error'				=> __( 'Error' ),
				'text_upload'				=> __( 'Upload' ),
				'text_cancel'				=> __( 'Cancel' ),
				'text_delete_confirmation'	=> __( 'Are you sure you want to do this? Delete actions cannot be undone.', 'be-table-ship' ),
				'text_importing_table'		=> __( 'Import Table of Rates', 'be-table-ship' ),
				'text_importing_csv'		=> __( 'Select a CSV file', 'be-table-ship' ),
				'text_importing_del'		=> __( 'Delete existing rows before importing', 'be-table-ship' ),
				'text_exporting'			=> __( 'Exporting', 'be-table-ship' ),
				'text_no_selection'			=> __( 'Please select which rows you wish to export', 'be-table-ship' ),
				'text_error_server'			=> __( 'Please review the documentation and enable the required server settings', 'be-table-ship' ),
				);
?>
<script type='text/javascript'>
/* <![CDATA[ */
var betrs_data = <?php echo json_encode( $betrs_data ) . "\n"; ?>
/* ]]> */
</script>
<?php
		}

	} /* End Class BE_Table_Rate_WC */

	$GLOBALS['betrs_shipping'] = new BE_Table_Rate_WC();

} // End woocommerce_table_rate_shipping_init.
 

/**
 * Add links to dashboard Plugins page
 *
 * @access public
 * @return void
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'be_table_rate_wc_action_links' );
function be_table_rate_wc_action_links( $links ) {
	return array_merge(
		array(
			'settings' => '<a href="' . get_admin_url() . '/admin.php?page=wc-settings&tab=shipping">' . __( 'Settings', 'be-table-ship' ) . '</a>',
			'support' => '<a href="http://bolderelements.net/support/" target="_blank">' . __( 'Support', 'be-table-ship' ) . '</a>'
		),
		$links
	);
 
}


/**
 * API check.
 *
 * @since 4.2.0
 *
 * @param bool   $api Always false.
 * @param string $action The API action being performed.
 * @param object $args Plugin arguments.
 * @return mixed $api The plugin info or false.
 */
function betrs_override_plugins_api_result( $res, $action, $args ) {
	
	if ( isset( $args->slug ) && 'woocommerce-table-rate-shipping' === $args->slug ) {
		$api_check = betrs_override_api_check();
		if ( is_object( $api_check ) ) {
			$res = $api_check;
			$res->external = true;
		}
	}

	return $res;
}
add_filter( 'plugins_api_result', 'betrs_override_plugins_api_result', 10, 3 );


/**
 * Check Github for an update.
 *
 * @since 4.2.0
 *
 * @return false|object
 */
function betrs_override_api_check() {
	$raw_response = wp_remote_get( 'https://bolderelements.github.io/docs/woocommerce-table-rate-shipping/update-check.json' );

	if ( is_wp_error( $raw_response ) ) {
		return false;
	}
	if ( ! empty( $raw_response['body'] ) ) {
		$raw_body = json_decode( trim( $raw_response['body'] ), true );
		if ( $raw_body ) {
			return (object) $raw_body;
		}
	}
	return false;
}

?>