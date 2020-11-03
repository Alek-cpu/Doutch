<?php
/**
 * WPLE_GridEditorPage class
 * 
 */

define( 'WPLE_AGGRID_THEME', 'alpine' ); 

class WPLE_GridEditorPage extends WPL_Page {

	// const slug = 'editor';
	var $debug = false;
	var $plugin_domain = 'wplege';

	public function onWpInit() {
		// parent::onWpInit();

		// // custom (raw) screen options for grid page
		// add_screen_options_panel('wplister_grid_options', '', array( &$this, 'renderSettingsOptions'), $this->main_admin_menu_slug.'_page_wplister-grid' );

		// // load scripts for this page only
		// add_action( 'admin_enqueue_scripts', array( &$this, 'onWpEnqueueScripts' ) );		
		// add_thickbox();
	}

	public function onWpAdminMenu() {
		parent::onWpAdminMenu();
		if ( ! get_option('wplister_enable_grid_editor') ) return;

		$title = __( 'Grid Editor', 'wp-lister-for-ebay');
		$hook_suffix = add_submenu_page( self::ParentMenuId, $title, $title, 'manage_ebay_options', $this->getSubmenuId( 'grid' ), array( &$this, 'onDisplayGridEditorPage' ) );

		add_action( 'load-' . $hook_suffix, array( $this, 'load_assets' ) );
	}

	public function handleSubmit() {
		if ( ! current_user_can('manage_ebay_listings') ) return;
	}
	
	public function handleActions() {
		if ( ! current_user_can('manage_ebay_listings') ) return;
		if ( ! get_option('wplister_enable_grid_editor') ) return;
	} // handleActions()
	

	public function onDisplayGridEditorPage() {

		$this->handleActions();

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,		
			// 'results'					=> isset($this->results) ? $this->results : '',
			'debug'						=> isset($debug) ? $debug : '',
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-grid'
		);

		$this->display( 'grid_page', $aData );
	}


	public function get_wple_profiles() {
		$profilesModel = new ProfilesModel();
		$profiles      = $profilesModel->getAll();
		$profile_list  = array();

		foreach ($profiles as $p) {
			$profile_list[ $p['profile_id'] ] = array(
				'id'    => $p['profile_id'],
				'value' => $p['profile_id'],
				'text'  => $p['profile_name'],
			);
		}

		return $profile_list;
	}

	public function get_wple_accounts() {
		$account_list  = array();

		foreach ( WPLE()->accounts as $a ) {
			$account_list[ $a->id ] = array(
				'id'        => $a->id,
				'value'     => $a->id,
				'text'      => $a->title,
				'site_id'   => $a->site_id,
				'site_code' => $a->site_code,
			);
		}

		return $account_list;
	}

	public function load_assets() {

		// Vue
		// wp_register_script( $this->plugin_domain . '-vue', 'https://unpkg.com/browse/vue@2.6.11/dist/vue.js', array(), '', 'all' );	// production
		// wp_register_script( $this->plugin_domain . '-vue', 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js', array(), '', 'all' );		// development
		$vueUrl = (get_option('wplister_log_level') < 6) ? 'https://cdn.jsdelivr.net/npm/vue/dist/vue.min.js' : 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js'; 
		wp_register_script( $this->plugin_domain . '-vue', $vueUrl, array(), '', 'all' );
		wp_enqueue_script(  $this->plugin_domain . '-vue' );

		// ag-grid
		wp_register_script( $this->plugin_domain . '-ag-grid', 'https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.noStyle.js', array(), '', 'all' );
	
		wp_enqueue_style( $this->plugin_domain . '-ag-grid-styles', 'https://unpkg.com/ag-grid-community/dist/styles/ag-grid.css', array(), '', 'all' );
		wp_enqueue_style( $this->plugin_domain . '-ag-grid-theme', 'https://unpkg.com/ag-grid-community/dist/styles/ag-theme-'.WPLE_AGGRID_THEME.'.css', array(), '', 'all' );

		
		// main.js
		wp_register_script( $this->plugin_domain . '-main', WPLE_PLUGIN_URL . 'js/classes/GridEditor.js', array(), filemtime( WPLE_PLUGIN_PATH . '/js/classes/GridEditor.js' ), 'all' );

		wp_localize_script( $this->plugin_domain . '-main', 'wpleApiSettings', array(
			'root'                  => esc_url_raw( rest_url() ),
			'rest_nonce'            => wp_create_nonce( 'wp_rest' ),
			'view_nonce'            => wp_create_nonce( 'wplister_preview_auction' ),
			'wple_ajax_base'        => esc_url_raw( rest_url().'wple/v1' ),
			'wple_listing_profiles' => self::get_wple_profiles(),
			'wple_ebay_accounts'    => self::get_wple_accounts(),
			'wple_grid_theme'       => WPLE_AGGRID_THEME,
		) );

		wp_enqueue_script( $this->plugin_domain . '-ag-grid' );
		wp_enqueue_script( $this->plugin_domain . '-main' );
		// wp_add_inline_script( $this->plugin_domain . '-main', '', 'before' );

		wp_enqueue_style( $this->plugin_domain . '-stylesheet', WPLE_PLUGIN_URL . 'css/grid.css', array(), filemtime( WPLE_PLUGIN_PATH . '/css/grid.css' ), 'all' );

		// other scripts
		add_thickbox();

		// Lobibox (for notifications and modal boxes)
		wp_register_script( $this->plugin_domain . '-lobibox', WPLE_PLUGIN_URL . 'js/lobibox/dist/js/lobibox.js', array(), filemtime( WPLE_PLUGIN_PATH . '/js/lobibox/dist/js/lobibox.js' ), 'all' );
		wp_enqueue_script( $this->plugin_domain . '-lobibox' );
		wp_add_inline_script( $this->plugin_domain . '-lobibox', 'var $ = jQuery.noConflict();', 'before' );

		wp_enqueue_style( $this->plugin_domain . '-lobibox', WPLE_PLUGIN_URL . 'js/lobibox/dist/css/lobibox.css', array(), filemtime( WPLE_PLUGIN_PATH . '/js/lobibox/dist/css/lobibox.css' ), 'all' );
		// wp_enqueue_style( $this->plugin_domain . '-bootstrap', WPLE_PLUGIN_URL . 'js/lobibox/bootstrap/dist/css/bootstrap.min.css', array(), filemtime( WPLE_PLUGIN_PATH . '/js/lobibox/bootstrap/dist/css/bootstrap.min.css' ), 'all' );

	}

} // class WPLE_GridEditorPage
