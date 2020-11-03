<?php

namespace Vendidero\Germanized\Shipments;

use Exception;
use WC_Shipping;
use WC_Shipping_Method;

defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 */
class Package {
    /**
     * Version.
     *
     * @var string
     */
    const VERSION = '1.2.10';

    public static $upload_dir_suffix = '';

    protected static $method_settings = null;

    /**
     * Init the package - load the REST API Server class.
     */
    public static function init() {
	    if ( ! self::has_dependencies() ) {
		    return;
	    }

	    self::define_tables();
	    self::maybe_set_upload_dir();
	    self::init_hooks();
        self::includes();
    }

    protected static function init_hooks() {
	    add_filter( 'woocommerce_data_stores', array( __CLASS__, 'register_data_stores' ), 10, 1 );
	    add_action( 'after_setup_theme', array( __CLASS__, 'include_template_functions' ), 11 );

	    // Filter email templates
	    add_filter( 'woocommerce_gzd_default_plugin_template', array( __CLASS__, 'filter_templates' ), 10, 3 );

	    add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'register_endpoints' ), 10, 1 );

	    add_action( 'woocommerce_load_shipping_methods', array( __CLASS__, 'load_shipping_methods' ), 5, 1 );
	    add_filter( 'woocommerce_shipping_methods', array( __CLASS__, 'set_method_filters' ), 200, 1 );

	    // Guest returns
	    add_filter( 'wc_get_template', array( __CLASS__, 'add_return_shipment_guest_endpoints' ), 10, 2 );
	    add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );

	    add_action( 'woocommerce_gzd_wpml_compatibility_loaded', array( __CLASS__, 'load_wpml_compatibility' ), 10 );
    }

    public static function add_return_shipment_guest_endpoints( $template, $template_name ) {
    	global $wp;

    	if ( 'myaccount/form-login.php' === $template_name ) {

    		try {
			    $key          = ( isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '' );
			    $order_id     = false;
			    $callback     = false;

			    if ( isset( $wp->query_vars['add-return-shipment'] ) ) {
				    $callback = 'woocommerce_gzd_shipments_template_add_return_shipment';
				    $order_id = absint( $wp->query_vars['add-return-shipment'] );
			    }

			    if ( $callback && $order_id && ( $order_shipment = wc_gzd_get_shipment_order( $order_id ) ) && ! empty( $key ) ) {

				    // Order return key is invalid.
				    if ( ! wc_gzd_customer_can_add_return_shipment( $order_id ) ) {
					    throw new Exception( _x( 'Sorry, this order is invalid and cannot be returned.', 'shipments', 'woocommerce-germanized' ) );
				    } else {
				    	call_user_func_array( $callback, array( 'order_id' => $order_id ) );
				    	$template = self::get_path() . '/templates/global/empty.php';
				    }
			    }
		    } catch ( Exception $e ) {
    			wc_add_notice( $e->getMessage(), 'error' );
		    }
	    }

    	return $template;
    }

    public static function register_shortcodes() {
	    add_shortcode( 'gzd_return_request_form', array( __CLASS__, 'return_request_form' ) );
    }

    public static function return_request_form( $args = array() ) {

    	$defaults = array(
		    'message'  => '',
		    'hidden'   => false,
	    );

	    $args    = wp_parse_args( $args, $defaults );
	    $notices = function_exists( 'wc_print_notices' ) ? wc_print_notices( true ) : '';
	    $html    = '';

	    // Output notices in case notices have not been outputted yet.
	    if ( ! empty( $notices ) ) {
		    $html .= '<div class="woocommerce">' . $notices . '</div>';
	    }

    	$html .= wc_get_template_html( 'global/form-return-request.php', $args );

    	return $html;
    }

	public static function load_wpml_compatibility( $compatibility ) {
		WPMLHelper::init( $compatibility );
	}

	public static function set_method_filters( $methods ) {

		foreach ( $methods as $method => $class ) {
			add_filter( 'woocommerce_shipping_instance_form_fields_' . $method, array( __CLASS__, 'add_method_settings' ), 10, 1 );
			/**
			 * Use this filter as a backup to support plugins like Flexible Shipping which may override methods
			 */
			add_filter( 'woocommerce_settings_api_form_fields_' . $method, array( __CLASS__, 'add_method_settings' ), 10, 1 );
			add_filter( 'woocommerce_shipping_' . $method . '_instance_settings_values', array( __CLASS__, 'filter_method_settings' ), 10, 2 );
		}

		return $methods;
	}

	protected static function get_method_settings() {

		if ( is_null( self::$method_settings ) ) {
			self::$method_settings = ShippingProviderMethod::get_admin_settings();
		}

		return self::$method_settings;
	}

	public static function filter_method_settings( $p_settings, $method ) {
		$shipping_provider_settings = self::get_method_settings();

		foreach( $p_settings as $setting => $value ) {

			if ( array_key_exists( $setting, $shipping_provider_settings ) ) {
				if ( self::get_setting( $setting ) === $value ) {
					unset( $p_settings[ $setting ] );
				}
			}
		}

		/**
		 * Filter that returns shipping method settings cleaned from global shipping provider method settings.
		 * This filter might be useful to remove some default setting values from
		 * shipping provider method settings e.g. DHL settings.
		 *
		 * @param array               $p_settings The settings
		 * @param WC_Shipping_Method $method The shipping method instance
		 *
		 * @since 3.0.6
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipping_provider_method_clean_settings', $p_settings, $method );
	}

	public static function add_method_settings( $p_settings ) {
		$shipping_provider_settings = self::get_method_settings();

		return array_merge( $p_settings, $shipping_provider_settings );
	}

	public static function load_shipping_methods( $package ) {
		$shipping = WC_Shipping::instance();

		foreach( $shipping->shipping_methods as $key => $method ) {
			$shipping_provider_method = new ShippingProviderMethod( $method );
		}
	}

    public static function register_endpoints( $query_vars ) {
    	$query_vars['view-shipment']       = get_option( 'woocommerce_gzd_shipments_view_shipment_endpoint', 'view-shipment' );
	    $query_vars['add-return-shipment'] = get_option( 'woocommerce_gzd_shipments_add_return_shipment_endpoint', 'add-return-shipment' );
	    $query_vars['view-shipments']      = get_option( 'woocommerce_gzd_shipments_view_shipments_endpoint', 'view-shipments' );

    	return $query_vars;
    }

	public static function install() {
    	self::includes();
		Install::install();
	}

	public static function install_integration() {
    	self::init();
		self::install();
	}

	public static function maybe_set_upload_dir() {
		// Create a dir suffix
		if ( ! get_option( 'woocommerce_gzd_shipments_upload_dir_suffix', false ) ) {
			self::$upload_dir_suffix = substr( self::generate_key(), 0, 10 );
			update_option( 'woocommerce_gzd_shipments_upload_dir_suffix', self::$upload_dir_suffix );
		} else {
			self::$upload_dir_suffix = get_option( 'woocommerce_gzd_shipments_upload_dir_suffix' );
		}
	}

	/**
	 * Generate a unique key.
	 *
	 * @return string
	 */
	protected static function generate_key() {
		$key       = array( ABSPATH, time() );
		$constants = array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT', 'SECRET_KEY' );

		foreach ( $constants as $constant ) {
			if ( defined( $constant ) ) {
				$key[] = constant( $constant );
			}
		}

		shuffle( $key );

		return md5( serialize( $key ) );
	}

	public static function get_upload_dir_suffix() {
		return self::$upload_dir_suffix;
	}

	public static function get_upload_dir() {

		self::set_upload_dir_filter();
		$upload_dir = wp_upload_dir();
		self::unset_upload_dir_filter();

		/**
		 * Filter to adjust the upload directory used to store shipment related files. By default
		 * files are stored in a custom directory under wp-content/uploads.
		 *
		 * @param array $upload_dir Array containing `wp_upload_dir` data.
		 *
		 * @since 3.0.1
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipments_upload_dir', $upload_dir );
	}

	public static function get_relative_upload_dir( $path ) {

		self::set_upload_dir_filter();
		$path = _wp_relative_upload_path( $path );
		self::unset_upload_dir_filter();

		/**
		 * Filter to retrieve the relative upload path used for storing shipment related files.
		 *
		 * @param array $path Relative path.
		 *
		 * @since 3.0.1
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipments_relative_upload_dir', $path );
	}

	public static function set_upload_dir_filter() {
		add_filter( 'upload_dir', array( __CLASS__, "filter_upload_dir" ), 150, 1 );
	}

	public static function unset_upload_dir_filter() {
		remove_filter( 'upload_dir', array( __CLASS__, "filter_upload_dir" ), 150 );
	}

	public static function filter_upload_dir( $args ) {
		$upload_base = trailingslashit( $args['basedir'] );
		$upload_url  = trailingslashit( $args['baseurl'] );

		/**
		 * Filter to adjust the upload path used to store shipment related files. By default
		 * files are stored in a custom directory under wp-content/uploads.
		 *
		 * @param string $path Path to the upload directory.
		 *
		 * @since 3.0.6
		 * @package Vendidero/Germanized/Shipments
		 */
		$args['basedir'] = apply_filters( 'woocommerce_gzd_shipments_upload_path', $upload_base . 'wc-gzd-shipments-' . self::get_upload_dir_suffix() );
		/**
		 * Filter to adjust the upload URL used to retrieve shipment related files. By default
		 * files are stored in a custom directory under wp-content/uploads.
		 *
		 * @param string $url URL to the upload directory.
		 *
		 * @since 3.0.6
		 * @package Vendidero/Germanized/Shipments
		 */
		$args['baseurl'] = apply_filters( 'woocommerce_gzd_shipments_upload_url', $upload_url . 'wc-gzd-shipments-' . self::get_upload_dir_suffix() );

		$args['path'] = $args['basedir'] . $args['subdir'];
		$args['url']  = $args['baseurl'] . $args['subdir'];

		return $args;
	}

	public static function has_dependencies() {
		return class_exists( 'WooCommerce' ) && apply_filters( 'woocommerce_gzd_shipments_enabled', true );
	}

    private static function includes() {

        if ( is_admin() ) {
	        Admin\Admin::init();
        }

        Ajax::init();
        Automation::init();
        Emails::init();
        Validation::init();
        Api::init();
	    FormHandler::init();

        if ( self::is_frontend_request() ) {
        	include_once self::get_path() . '/includes/wc-gzd-shipment-template-hooks.php';
        }

        include_once self::get_path() . '/includes/wc-gzd-shipment-functions.php';
    }

    private static function is_frontend_request() {
	    return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
    }

    /**
     * Function used to Init WooCommerce Template Functions - This makes them pluggable by plugins and themes.
     */
    public static function include_template_functions() {
        include_once self::get_path() . '/includes/wc-gzd-shipments-template-functions.php';
    }

    public static function filter_templates( $path, $template_name ) {

        if ( file_exists( self::get_path() . '/templates/' . $template_name ) ) {
            $path = self::get_path() . '/templates/' . $template_name;
        }

        return $path;
    }

    /**
     * Register custom tables within $wpdb object.
     */
    private static function define_tables() {
        global $wpdb;

        // List of tables without prefixes.
        $tables = array(
            'gzd_shipment_itemmeta'     => 'woocommerce_gzd_shipment_itemmeta',
            'gzd_shipmentmeta'          => 'woocommerce_gzd_shipmentmeta',
            'gzd_shipments'             => 'woocommerce_gzd_shipments',
            'gzd_shipment_items'        => 'woocommerce_gzd_shipment_items',
            'gzd_shipping_provider'     => 'woocommerce_gzd_shipping_provider',
            'gzd_shipping_providermeta' => 'woocommerce_gzd_shipping_providermeta',
        );

        foreach ( $tables as $name => $table ) {
            $wpdb->$name    = $wpdb->prefix . $table;
            $wpdb->tables[] = $table;
        }
    }

    public static function register_data_stores( $stores ) {
        $stores['shipment']          = 'Vendidero\Germanized\Shipments\DataStores\Shipment';
        $stores['shipment-item']     = 'Vendidero\Germanized\Shipments\DataStores\ShipmentItem';
	    $stores['shipping-provider'] = 'Vendidero\Germanized\Shipments\DataStores\ShippingProvider';

        return $stores;
    }

    /**
     * Return the version of the package.
     *
     * @return string
     */
    public static function get_version() {
        return self::VERSION;
    }

    /**
     * Return the path to the package.
     *
     * @return string
     */
    public static function get_path() {
        return dirname( __DIR__ );
    }

    /**
     * Return the path to the package.
     *
     * @return string
     */
    public static function get_url() {
        return plugins_url( '', __DIR__ );
    }

    public static function get_assets_url() {
        return self::get_url() . '/assets';
    }

	public static function get_setting( $name ) {
		$option_name = "woocommerce_gzd_shipments_{$name}";

		return get_option( $option_name );
	}
}
