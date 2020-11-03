<?php
/**
 * WPL_Page
 *
 * This class provides methods for single admin pages
 * 
 */

// helper class for custom screen options - will be removed in future versions
require_once( WPLE_PLUGIN_PATH . '/includes/screen-options/screen-options.php' );

class WPL_Page extends WPL_Core {
	
	public function __construct() {
		parent::__construct();

		self::$PLUGIN_URL = WPLE_PLUGIN_URL;
		self::$PLUGIN_DIR = WPLE_PLUGIN_PATH;

		$this->main_admin_menu_label = trim( get_option( 'wplister_admin_menu_label', $this->app_name ) );
		$this->main_admin_menu_label = $this->main_admin_menu_label ? $this->main_admin_menu_label : $this->app_name;
		$this->main_admin_menu_slug  = sanitize_key( str_replace(' ', '-', trim($this->main_admin_menu_label) ) );

		add_action( 'admin_menu', 			array( &$this, 'onWpAdminMenu' ), 20 );

		if ( is_admin() ) {
			add_action( 'plugins_loaded', 	array( &$this, 'handleSubmit' ) );
		}

	}
	
	// these methods can be overriden
	public function onWpAdminMenu() {
	}	
	public function handleSubmit() {
	}	


	// display view
	public function display( $insView, $inaData = array(), $echo = true ) {

		$sFile = WPLE_PLUGIN_PATH . '/views/' . $insView . '.php';
		
		if ( !is_file( $sFile ) ) {
			$this->showMessage("View not found: ".$sFile,1,1);
			return false;
		}
		
		if ( count( $inaData ) > 0 ) {
			extract( $inaData, EXTR_PREFIX_ALL, 'wpl' );
		}
		
		ob_start();
			include( $sFile );
			$sContents = ob_get_contents();
		ob_end_clean();

		// change admin footer on wplister pages
		if ( apply_filters( 'wplister_enable_admin_footer', true ) ) {
			add_filter('admin_footer_text', array( &$this, 'change_admin_footer_text') ); 
			add_filter('update_footer', array( &$this, 'change_admin_footer_version') ); 
		}

		// MOVED to wp-lister.php
		// fix thickbox display problems caused by other plugins 
		// like woocommerce-pip which enqueues media-upload on every admin page
		// if ( did_action( 'init' ) ) wp_dequeue_script( 'media-upload' );

		// filter content before output
		$sContents = apply_filters( 'wplister_admin_page_content', $sContents );

		if ($echo) {
			echo $sContents;
			return true;
		} else {
			return $sContents;
		}
	
	}

	function change_admin_footer_text() {  
		// $plugin_name = WPLE_IS_LITE_VERSION ? $this->app_name : $this->app_name . ' Pro';  
		$plugin_name = WPLE_IS_LITE_VERSION ? 'WP-Lister for eBay' : 'WP-Lister Pro for eBay';  
	    echo '<span id="footer-thankyou">';
	    echo sprintf( __( 'Thank you for listing with %s', 'wp-lister-for-ebay' ), '<a href="https://www.wplab.com/plugins/wp-lister/" target="_blank">'.$plugin_name.'</a>' );
	    echo '</span>';
	}  
	function change_admin_footer_version( $version ) {
		// $plugin_name  = WPLE_IS_LITE_VERSION ? $this->app_name : $this->app_name . ' Pro';  
		$plugin_name  = WPLE_IS_LITE_VERSION ? 'WP-Lister Lite for eBay' : 'WP-Lister Pro';  
		$plugin_name .= ' ' . WPLE_PLUGIN_VERSION;
		$network_activated = get_option('wplister_is_network_activated') == 1 ? true : false;
		if ( $network_activated ) $plugin_name .= 'n';
	    return $version . ' / ' . $plugin_name;
	}  

	// deprecated
	function get_plugin_version() {

		return WPLE_PLUGIN_VERSION;

	    // if ( ! function_exists( 'get_plugins' ) )
	    // 	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	    
	    // // $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
	    // // $plugin_file = basename( ( __FILE__ ) );
	    // $plugin_folder = get_plugins( '/' . plugin_basename( WPLE_PLUGIN_PATH ) );
	    // $plugin_file = 'wp-lister.php';

	    // return $plugin_folder[$plugin_file]['Version'];
	}

	function get_i8n_html( $basename )	{
		if ( empty( $basename ) ) return false;
		if ( ! defined( 'WPLANG' ) ) define( 'WPLANG', 'en_US' );

		$lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : substr( WPLANG, 0, 2 ); // WPML COMPATIBILITY
		$lang_folder = trailingslashit(WPLE_PLUGIN_PATH) . 'views/lang/'; 

		$default_file    = $lang_folder . $basename . '_en.html';
		$translated_file = $lang_folder . $basename . '_' . $lang . '.html';

		$file = file_exists( $translated_file ) ? $translated_file : $default_file;
		if ( is_readable( $file ) ) return file_get_contents( $file );

		$this->showMessage('file not found: '.$file,1,1);
		
		return false;
	}

	function check_wplister_setup( $page = false )	{
		$Setup = new WPL_Setup();
		$Setup->checkSetup( $page );
	}


}

