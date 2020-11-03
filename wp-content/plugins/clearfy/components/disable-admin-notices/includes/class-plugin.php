<?php
/**
 * Disable admin notices core class
 *
 * @author        Alexander Kovalev <alex.kovalevv@gmail.com>
 *                Github: https://github.com/alexkovalevv
 * @copyright (c) 2018 Webraftic Ltd
 * @version       1.0
 */

// Exit if accessed directly
//use WBCR\Factory_Adverts_109\Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDN_Plugin extends Wbcr_Factory429_Plugin {

	/**
	 * @var Wbcr_Factory429_Plugin
	 */
	private static $app;
	private $plugin_data;


	/**
	 * @param string $plugin_path
	 * @param array $data
	 *
	 * @throws Exception
	 */
	public function __construct( $plugin_path, $data ) {
		parent::__construct( $plugin_path, $data );

		self::$app         = $this;
		$this->plugin_data = $data;

		$this->global_scripts();

		if ( is_admin() ) {
			$this->admin_scripts();
		}
	}

	/**
	 * @return Wbcr_Factory429_Plugin
	 */
	public static function app() {
		return self::$app;
	}

	private function registerPages() {
		self::app()->registerPage( 'WDN_Settings_Page', WDN_PLUGIN_DIR . '/admin/pages/class-pages-settings.php' );

		if ( ! ( $this->premium->is_activate() && $this->premium->is_install_package() ) ) {
			self::app()->registerPage( 'WDAN_Block_Ad_Redirects', WDN_PLUGIN_DIR . '/admin/pages/class-pages-edit-redirects.php' );
			self::app()->registerPage( 'WDAN_Edit_Admin_Bar', WDN_PLUGIN_DIR . '/admin/pages/class-pages-edit-admin-bar.php' );
		}

		self::app()->registerPage( 'WDN_LicensePage', WDN_PLUGIN_DIR . '/admin/pages/class-pages-license.php' );
	}

	private function admin_scripts() {
		require( WDN_PLUGIN_DIR . '/admin/options.php' );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			require_once( WDN_PLUGIN_DIR . '/admin/ajax/hide-notice.php' );
			require_once( WDN_PLUGIN_DIR . '/admin/ajax/restore-notice.php' );
		}

		require_once( WDN_PLUGIN_DIR . '/admin/boot.php' );
		require_once( WDN_PLUGIN_DIR . '/admin/pages/class-pages-edit-admin-bar.php' );
		require_once( WDN_PLUGIN_DIR . '/admin/pages/class-pages-edit-redirects.php' );

		add_action( 'plugins_loaded', function () {
			$this->registerPages();
		}, 30 );
	}

	private function global_scripts() {
		require_once( WDN_PLUGIN_DIR . '/includes/function.php' );
		require_once( WDN_PLUGIN_DIR . '/includes/classes/class-configurate-notices.php' );
		new WDN_ConfigHideNotices( self::$app );
	}
}
