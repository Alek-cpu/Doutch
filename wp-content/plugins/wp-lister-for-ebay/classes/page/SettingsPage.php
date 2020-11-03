<?php
/**
 * SettingsPage class
 *
 */

class SettingsPage extends WPL_Page {

	const slug = 'settings';

	public function onWpInit() {
		// parent::onWpInit();

		// custom (raw) screen options for settings page
		add_screen_options_panel('wplister_setting_options', '', array( &$this, 'renderSettingsOptions'), $this->main_admin_menu_slug.'_page_wplister-settings' );

		// Add custom screen options
		$load_action = "load-".$this->main_admin_menu_slug."_page_wplister-".self::slug;
		add_action( $load_action, array( &$this, 'addScreenOptions' ) );

		// add screen option on categories page if enabled
		if ( get_option( 'wplister_enable_categories_page' ) )
			add_action( $load_action.'-categories', array( &$this, 'addScreenOptions' ) );

		// network admin page
		add_action( 'network_admin_menu', array( &$this, 'onWpAdminMenu' ) );

	}

	public function onWpAdminMenu() {
		parent::onWpAdminMenu();

		add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( 'Settings' ), __( 'Settings', 'wp-lister-for-ebay' ),
						  'manage_ebay_options', $this->getSubmenuId( 'settings' ), array( &$this, 'onDisplaySettingsPage' ) );

		if ( get_option( 'wplister_enable_categories_page' ) ) {

			add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( 'Categories' ), __( 'Categories', 'wp-lister-for-ebay' ),
						  'manage_ebay_listings', $this->getSubmenuId( 'settings-categories' ), array( &$this, 'displayCategoriesPage' ) );

		}

		if ( get_option( 'wplister_enable_accounts_page' ) ) {

			add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( 'Accounts' ), __( 'Account', 'wp-lister-for-ebay' ),
						  'manage_ebay_listings', $this->getSubmenuId( 'settings-accounts' ), array( &$this, 'displayAccountsPage' ) );

		}

	}

	function addScreenOptions() {
		// load styles and scripts for this page only
		add_action( 'admin_print_styles', array( &$this, 'onWpPrintStyles' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'onWpEnqueueScripts' ) );
		$this->categoriesMapTable = new CategoriesMapTable();
		add_thickbox();
	}

	public function handleSubmit() {
		if ( ! current_user_can('manage_ebay_listings') ) return;

		// handle redirect to ebay auth url
		if ( $this->requestAction() == 'wplRedirectToAuthURL') {
		    check_admin_referer( 'wplister_redirect_to_auth_url' );

			WPLE()->logger->info( "Request Action: wplRedirectToAuthURL() - METHOD: " . $_SERVER['REQUEST_METHOD'] );
			if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) return; // avoid issue caused by calling wplRedirectToAuthURL action twice

			// get auth url
			$this->initEC();
			$auth_url = $this->EC->getAuthUrl();
			$this->EC->closeEbay();

			WPLE()->logger->info( "wplRedirectToAuthURL() to: " . $auth_url );
			wp_redirect( $auth_url );
		}

		// save settings
		if ( $this->requestAction() == 'save_wplister_settings' ) {
		    check_admin_referer( 'wplister_save_settings' );

			$this->saveSettings();
		}

		// save advanced settings
		if ( $this->requestAction() == 'save_wplister_advanced_settings' ) {
		    check_admin_referer( 'wplister_save_advanced_settings' );

			$this->saveAdvancedSettings();
		}

		// save category map
		if ( $this->requestAction() == 'save_wplister_categories_map' ) {
		    check_admin_referer( 'wplister_save_categories_map' );
			$this->saveCategoriesSettings();
		}

		// import category map
		if ( $this->requestAction() == 'wplister_import_categories_map' ) {
		    check_admin_referer( 'wplister_import_categories_map' );
			$this->handleImportCategoriesMap();
		}

		// export category map
		if ( $this->requestAction() == 'wplister_export_categories_map' ) {
		    check_admin_referer( 'wplister_export_categories_map' );
			$this->handleExportCategoriesMap();
		}


		// save developer settings
		if ( $this->requestAction() == 'save_wplister_devsettings' ) {
		    check_admin_referer( 'wplister_save_devsettings' );
			$this->saveDeveloperSettings();
		}

        if ( $this->requestAction() == 'wple_clear_stocks_log' ) {
            check_admin_referer( 'wple_clear_stocks_log' );

            WPL_StocksLogger::clearLog();
            $this->showMessage( __( 'Stocks log has been cleared.', 'wp-lister-for-ebay' ) );
        }
        if ( $this->requestAction() == 'wple_optimize_stocks_log' ) {
            check_admin_referer( 'wple_optimize_stocks_log' );
            $count = WPL_StocksLogger::optimizeLog();
            $this->showMessage( $count . ' ' . __( 'expired records have been removed and the database table has been optimized.', 'wp-lister-for-ebay' ) );
        }

	}


	public function onDisplaySettingsPage() {
		$this->check_wplister_setup('settings');

        $default_tab = is_network_admin() ? 'license' : 'settings';
        $active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_key($_GET[ 'tab' ]) : $default_tab;
        if ( 'categories' == $active_tab ) return $this->displayCategoriesPage();
        if ( 'developer'  == $active_tab ) {
            $subtab = empty( $_GET['subtab'] ) ? 'settings' : sanitize_key( $_GET['subtab'] );

            if ( $subtab == 'stockslog' ) {
                return $this->displayStocksLogPage();
            } else {
                return $this->displayDeveloperPage();
            }
        }
        if ( 'advanced'   == $active_tab ) return $this->displayAdvancedSettingsPage();
        if ( 'accounts'   == $active_tab ) return $this->displayAccountsPage();

        $license_activated = get_option( 'wple_api_key', false ) ? 1 : 0;

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			// deprecated parameters
			'ebay_sites'				=> EbayController::getEbaySites(),
			'ebay_token_userid'			=> self::getOption( 'ebay_token_userid' ),
			'ebay_user'					=> self::getOption( 'ebay_user' ),

			'option_cron_auctions'		=> self::getOption( 'cron_auctions' ),
			'background_revisions'      => self::getOption( 'background_revisions', 0 ),
			'enable_grid_editor'        => self::getOption( 'enable_grid_editor', 0 ),
			'local_auction_display'     => self::getOption( 'local_auction_display', 'off' ),
			'is_staging_site'     		=> $this->isStagingSite(),
			'option_handle_stock'       => self::getOption( 'handle_stock' ), // required in Lite for sidebar info

			'settings_url'				=> 'admin.php?page='.self::ParentMenuId.'-settings',
			'auth_url'					=> 'admin.php?page='.self::ParentMenuId.'-settings'.'&tab='.$active_tab.'&action=wplRedirectToAuthURL&_wpnonce='. wp_create_nonce( 'wplister_redirect_to_auth_url' ),
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-settings'.'&tab='.$active_tab
		);


		$this->display( 'settings_page', $aData );
	}

	public function displayCategoriesPage() {

		$this->account_id = isset($_REQUEST['account_id']) ? intval($_REQUEST['account_id']) : get_option('wplister_default_account_id');
		$this->site_id    = isset( WPLE()->accounts[ $this->account_id ] ) ? WPLE()->accounts[ $this->account_id ]->site_id : false;
		$shop_categories  = $this->loadProductCategories();

	    //Create an instance of our package class...
	    $categoriesMapTable = new CategoriesMapTable();
    	//Fetch, prepare, sort, and filter our data...
	    $categoriesMapTable->items = $shop_categories;
	    $categoriesMapTable->prepare_items();

	    // get default category - from selected account, but fall back to default
	    $default_category_id   = $this->account_id ? WPLE()->accounts[ $this->account_id ]->default_ebay_category_id : self::getOption('default_ebay_category_id');
	    $default_category_name = EbayCategoriesModel::getFullEbayCategoryName( $default_category_id, $this->site_id );
	    if ( ! $default_category_name ) $default_category_name = 'None';

	    $form_action = 'admin.php?page='.self::ParentMenuId.'-settings'.'&tab=categories';
	    if ( @$_REQUEST['page'] == 'wplister-settings-categories' )
		    $form_action = 'admin.php?page=wplister-settings-categories';

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'shop_categories'			=> $shop_categories,
			'categoriesMapTable'		=> $categoriesMapTable,
			'default_category_id'		=> $default_category_id,
			'default_category_name'		=> $default_category_name,
			'account_id'				=> $this->account_id,
			'site_id'					=> $this->site_id,

			'settings_url'				=> 'admin.php?page='.self::ParentMenuId.'-settings',
			'form_action'				=> $form_action
		);
		$this->display( 'settings_categories', $aData );
	}


	public function displayAdvancedSettingsPage() {

        $wp_roles = new WP_Roles();
        // echo "<pre>";print_r($wp_roles);echo"</pre>";#die();

		$aData = array(
			'plugin_url'                    => self::$PLUGIN_URL,
			'message'                       => $this->message,

			'process_shortcodes'            => self::getOption( 'process_shortcodes', 'content' ),
			'do_template_autop'             => self::getOption( 'do_template_autop', 'content' ),
			'remove_links'                  => self::getOption( 'remove_links', 'default' ),
			'template_ssl_mode'             => self::getOption( 'template_ssl_mode', '' ),
			'default_image_size'            => self::getOption( 'default_image_size', 'full' ),
			'wc2_gallery_fallback'          => self::getOption( 'wc2_gallery_fallback', 'none' ),
			'gallery_items_limit'        	=> self::getOption( 'gallery_items_limit', 12 ),
			'hide_dupe_msg'                 => self::getOption( 'hide_dupe_msg' ),
            'display_product_counts'        => self::getOption( 'display_product_counts', 0 ),
			'option_uninstall'              => self::getOption( 'uninstall' ),
            'enable_out_of_stock_threshold' => self::getOption( 'enable_out_of_stock_threshold', 0 ),
			'option_allow_backorders'       => self::getOption( 'allow_backorders', 0 ),
			'disable_sale_price'            => self::getOption( 'disable_sale_price', 0 ),
			'apply_profile_to_ebay_price'   => self::getOption( 'apply_profile_to_ebay_price', 0 ),
			'auto_update_ended_items'       => self::getOption( 'auto_update_ended_items', 0 ),
			'option_preview_in_new_tab'     => self::getOption( 'preview_in_new_tab', 0 ),
			'enable_categories_page'        => self::getOption( 'enable_categories_page', 0 ),
            'store_categories_sorting'      => self::getOption( 'store_categories_sorting', 'default' ),
			'enable_accounts_page'			=> self::getOption( 'enable_accounts_page', 0 ),
			'enable_custom_product_prices'  => self::getOption( 'enable_custom_product_prices', 1 ),
			'enable_mpn_and_isbn_fields'    => self::getOption( 'enable_mpn_and_isbn_fields', 2 ),
			'option_disable_wysiwyg_editor' => self::getOption( 'disable_wysiwyg_editor', 0 ),
			'enable_item_compat_tab'        => self::getOption( 'enable_item_compat_tab', 1 ),
			'thumbs_display_size'           => self::getOption( 'thumbs_display_size', 1 ),
			'listing_sku_sorting'           => self::getOption( 'listing_sku_sorting', 0 ),
			'send_weight_and_size'          => self::getOption( 'send_weight_and_size', 'default' ),
			'convert_dimensions'        	=> self::getOption( 'convert_dimensions' ),
			'convert_attributes_mode'      	=> self::getOption( 'convert_attributes_mode', 'all' ),
			'exclude_attributes'        	=> self::getOption( 'exclude_attributes' ),
			'exclude_variation_values'      => self::getOption( 'exclude_variation_values' ),
			'autofill_missing_gtin'         => self::getOption( 'autofill_missing_gtin', '' ),
			'option_local_timezone'         => self::getOption( 'local_timezone', '' ),
			'text_admin_menu_label'         => self::getOption( 'admin_menu_label', $this->app_name ),
			'timezones'                     => self::get_timezones(),
			'available_roles'               => $wp_roles->role_names,
			'wp_roles'                      => $wp_roles->roles,
            'run_background_inventory_check'	=> self::getOption( 'run_background_inventory_check', 1 ),
            'inventory_check_frequency'	        => self::getOption( 'inventory_check_frequency', 24 ),
            'inventory_check_notification_email'=> self::getOption( 'inventory_check_notification_email', '' ),

			// removed options:
			// 'option_foreign_transactions'   => self::getOption( 'foreign_transactions' ),
			// 'api_enable_auto_relist'        => self::getOption( 'api_enable_auto_relist', 0 ),
			// 'enable_thumbs_column'          => self::getOption( 'enable_thumbs_column', 0 ),

			'settings_url'				=> 'admin.php?page='.self::ParentMenuId.'-settings',
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-settings'.'&tab=advanced'
		);
		$this->display( 'settings_advanced', $aData );
	}

	public function displayDeveloperPage() {

		$aData = array(
			'plugin_url'                   => self::$PLUGIN_URL,
			'message'                      => $this->message,

			'ajax_error_handling'          => self::getOption( 'ajax_error_handling', 'halt' ),
			'php_error_handling'           => self::getOption( 'php_error_handling', 0 ),
			'disable_variations'           => self::getOption( 'disable_variations', 0 ),
			'disable_compat_list'          => self::getOption( 'disable_compat_list', 0 ),
			'log_include_authinfo'         => self::getOption( 'log_include_authinfo', 0 ),
			'enable_item_edit_link'        => self::getOption( 'enable_item_edit_link', 0 ),
			'log_record_limit'             => self::getOption( 'log_record_limit', 4096 ),
			'log_days_limit'               => self::getOption( 'log_days_limit', 30 ),
			'orders_days_limit'            => self::getOption( 'orders_days_limit', '' ),
			'archive_days_limit'           => self::getOption( 'archive_days_limit', 90 ),
			'xml_formatter'                => self::getOption( 'xml_formatter', 'custom' ),
			'eps_xfer_mode'                => self::getOption( 'eps_xfer_mode', 'passive' ),
			'force_table_items_limit'      => self::getOption( 'force_table_items_limit' ),
			'apply_profile_batch_size'     => self::getOption( 'apply_profile_batch_size', 1000 ),
			'inventory_check_batch_size'   => self::getOption( 'inventory_check_batch_size', 200 ),
			'grid_page_size'               => self::getOption( 'grid_page_size', 10000 ),
			'fetch_orders_page_size'       => self::getOption( 'fetch_orders_page_size', 50 ),
			'staging_site_pattern'         => self::getOption( 'staging_site_pattern', '' ),
			'notify_custom_email'          => self::getOption( 'notify_custom_email', '' ),
			'enable_order_notify'          => self::getOption( 'enable_order_notify', 0 ),
			'disable_profile_popup_errors' => self::getOption( 'disable_profile_popup_errors', 0 ),
			'multi_threading_limit'        => self::getOption( 'multi_threading_limit', 1 ),
			'disable_item_specifics_cache' => self::getOption( 'disable_item_specifics_cache', 0 ),
			'item_specifics_limit'         => self::getOption( 'item_specifics_limit', 100 ),
			'revise_all_listings_limit'    => self::getOption( 'revise_all_listings_limit', '' ),
			'text_log_level'               => self::getOption( 'log_level' ),
			'option_log_to_db'             => self::getOption( 'log_to_db' ),

			'update_channel'               => get_option( 'wple_update_channel', 'stable' ),
			'ignore_orders_before_ts'      => get_option( 'ignore_orders_before_ts' ),

			'settings_url'                 => 'admin.php?page='.self::ParentMenuId.'-settings',
			'form_action'                  => 'admin.php?page='.self::ParentMenuId.'-settings'.'&tab=developer'
		);
		$this->display( 'settings_dev', $aData );
	}

	/* moved to ToolsPage.php
    public function displayStocksLogPage() {
        // If WPLA is available, stock logging will be disabled in WPLE so display a notification about that
        if ( class_exists( 'WPLA_Core' ) ) {
            $stocks_url = admin_url( 'admin.php?page=wpla-tools&tab=stock_log' );
            $msg = sprintf( __( 'WP-Lister Amazon is active so stock logging in eBay is disabled. View the stocks log <a href="%s">here</a> instead.', 'wp-lister-for-ebay' ), $stocks_url );
            wple_show_message( $msg );
        }

        // create table and fetch items to show
        $stockslogTable = new WPLE_StocksLogTable();
        $stockslogTable->prepare_items();

        $form_action = 'admin.php?page='.self::ParentMenuId.'-settings'.'&tab=developer&subtab=stockslog';

        $aData = array(
            'plugin_url'				=> self::$PLUGIN_URL,
            'message'					=> $this->message,

            'listingsTable'				=> $stockslogTable,
            //'default_account'			=> get_option( 'wpla_default_account_id' ),
            'tableSize'					=> $stockslogTable->get_table_size(),

            //'tools_url'				    => 'admin.php?page='.self::ParentMenuId.'-tools',
            'form_action'				=> $form_action,
            'settings_url'				=> 'admin.php?page='.self::ParentMenuId.'-settings',
        );
        $this->display( 'settings_dev_stockslog', $aData );
    }
	*/

	public function displayAccountsPage() {
    	return WPLE()->pages['accounts']->displayAccountsPage();
	}


	protected function saveSettings() {
		if ( ! current_user_can('manage_ebay_options') ) return;

		// self::updateOption( 'ebay_site_id',		$this->getValueFromPost( 'text_ebay_site_id' ) );
		// self::updateOption( 'paypal_email',		trim( $this->getValueFromPost( 'text_paypal_email' ) ) );
		// self::updateOption( 'ebay_update_mode', 	$this->getValueFromPost( 'option_ebay_update_mode' ) );

		self::updateOption( 'cron_auctions',		$this->getValueFromPost( 'option_cron_auctions' ) );
		self::updateOption( 'local_auction_display',$this->getValueFromPost( 'local_auction_display' ) );
		self::updateOption( 'background_revisions', $this->getValueFromPost( 'background_revisions' ) );
		self::updateOption( 'enable_grid_editor',  	$this->getValueFromPost( 'enable_grid_editor' ) );

		do_action('wple_save_settings');

		$this->handleCronSettings( $this->getValueFromPost( 'option_cron_auctions' ) );
		$this->showMessage( __( 'Settings saved.', 'wp-lister-for-ebay' ) );
	}

	protected function saveAdvancedSettings() {
		if ( ! current_user_can('manage_ebay_options') ) return;

		$this->savePermissions();

		self::updateOption( 'process_shortcodes', 				$this->getValueFromPost( 'process_shortcodes' ) );
		self::updateOption( 'do_template_autop', 				$this->getValueFromPost( 'do_template_autop' ) );
		self::updateOption( 'remove_links',     				$this->getValueFromPost( 'remove_links' ) );
		self::updateOption( 'template_ssl_mode',   				$this->getValueFromPost( 'template_ssl_mode' ) );
		self::updateOption( 'default_image_size',   			$this->getValueFromPost( 'default_image_size' ) );
		self::updateOption( 'wc2_gallery_fallback', 			$this->getValueFromPost( 'wc2_gallery_fallback' ) );
		self::updateOption( 'hide_dupe_msg',    				$this->getValueFromPost( 'hide_dupe_msg' ) );
        self::updateOption( 'display_product_counts',        $this->getValueFromPost( 'display_product_counts' ) );
		self::updateOption( 'gallery_items_limit',  			$this->getValueFromPost( 'gallery_items_limit' ) );
		self::updateOption( 'uninstall',						$this->getValueFromPost( 'option_uninstall' ) );
		self::updateOption( 'preview_in_new_tab',				$this->getValueFromPost( 'option_preview_in_new_tab' ) );
		self::updateOption( 'enable_categories_page',			$this->getValueFromPost( 'enable_categories_page' ) );
		self::updateOption( 'store_categories_sorting',         $this->getValueFromPost( 'store_categories_sorting' ) );
		self::updateOption( 'enable_accounts_page',				$this->getValueFromPost( 'enable_accounts_page' ) );
		self::updateOption( 'enable_custom_product_prices', 	$this->getValueFromPost( 'enable_custom_product_prices' ) );
		self::updateOption( 'enable_mpn_and_isbn_fields', 		$this->getValueFromPost( 'enable_mpn_and_isbn_fields' ) );
		self::updateOption( 'disable_wysiwyg_editor',			$this->getValueFromPost( 'option_disable_wysiwyg_editor' ) );
		self::updateOption( 'enable_item_compat_tab', 			$this->getValueFromPost( 'enable_item_compat_tab' ) );
		self::updateOption( 'thumbs_display_size', 				$this->getValueFromPost( 'thumbs_display_size' ) );
		self::updateOption( 'listing_sku_sorting', 				$this->getValueFromPost( 'listing_sku_sorting' ) );
		self::updateOption( 'send_weight_and_size', 			$this->getValueFromPost( 'send_weight_and_size' ) );
		self::updateOption( 'convert_dimensions', 				$this->getValueFromPost( 'convert_dimensions' ) );
		self::updateOption( 'convert_attributes_mode', 			$this->getValueFromPost( 'convert_attributes_mode' ) );
		self::updateOption( 'exclude_attributes', 				$this->getValueFromPost( 'exclude_attributes' ) );
		self::updateOption( 'local_timezone',					$this->getValueFromPost( 'option_local_timezone' ) );
		self::updateOption( 'enable_out_of_stock_threshold',    $this->getValueFromPost( 'enable_out_of_stock_threshold' ) );
		self::updateOption( 'allow_backorders',					$this->getValueFromPost( 'option_allow_backorders' ) );
		self::updateOption( 'disable_sale_price',				$this->getValueFromPost( 'disable_sale_price' ) );
		self::updateOption( 'apply_profile_to_ebay_price',		$this->getValueFromPost( 'apply_profile_to_ebay_price' ) );
		self::updateOption( 'autofill_missing_gtin',			$this->getValueFromPost( 'autofill_missing_gtin' ) );
		self::updateOption( 'auto_update_ended_items',			$this->getValueFromPost( 'auto_update_ended_items' ) );

		self::updateOption( 'exclude_variation_values', 		str_replace( ', ', ',', $this->getValueFromPost( 'exclude_variation_values' ) ) );

		if ( ! defined('WPLISTER_RESELLER_VERSION') )
			self::updateOption( 'admin_menu_label',				$this->getValueFromPost( 'text_admin_menu_label' ) );


		// removed options:
		// self::updateOption( 'foreign_transactions',			$this->getValueFromPost( 'option_foreign_transactions' ) );
		// self::updateOption( 'api_enable_auto_relist',		$this->getValueFromPost( 'api_enable_auto_relist' ) );
		// self::updateOption( 'enable_thumbs_column',			$this->getValueFromPost( 'enable_thumbs_column' ) );

        $this->saveBackgroundInventoryCheck();

		do_action('wple_save_settings');

		$this->showMessage( __( 'Settings saved.', 'wp-lister-for-ebay' ) );
	} // saveAdvancedSettings()

    protected function saveBackgroundInventoryCheck() {
	    $enabled    = $this->getValueFromPost( 'run_background_inventory_check' );
        $frequency  = $this->getValueFromPost( 'inventory_check_frequency' );
        $email      = $this->getValueFromPost( 'inventory_check_notification_email' );

        // Only once per day for Light users
        if ( !in_array( $frequency, array( 1, 3, 6, 12, 24 ) ) || WPLE_IS_LITE_VERSION ) {
            $frequency = 24;
        }

        if ( !is_email( $email ) ) {
            // do not save invalid email address so it defaults to the admin email
            $email = '';
        }

        self::updateOption( 'run_background_inventory_check', $enabled );
        self::updateOption( 'inventory_check_frequency', $frequency );
        self::updateOption( 'inventory_check_notification_email', $email );

        if ( $enabled ) {
            // Turn it on
            if ( ! as_next_scheduled_action( 'wple_bg_inventory_check' ) ) {
//                as_schedule_recurring_action( time(), $frequency * 3600, 'wple_bg_inventory_check' );
            }
        } else {
            ###
            # This doesn't work probably because it is being called too early in the stack. This has been moved to
            # WPLA_CronActions::maybe_unschedule_inventory_check() instead which is getting triggered by admin_init
            ###
            // Disabled - remove the scheduled task
            //as_unschedule_all_actions( 'wpla_update_reports', array('inventory_sync' => 1) );
            //as_unschedule_all_actions( 'wpla_bg_inventory_check' );
        }
    }


	protected function savePermissions() {

		// don't update capabilities when options are disabled
		if ( ! apply_filters( 'wpl_enable_capabilities_options', true ) ) return;

    	$wp_roles = new WP_Roles();
    	$available_roles = $wp_roles->role_names;

		$wpl_caps = array(
			'manage_ebay_listings'  => 'Manage Listings',
			'manage_ebay_options'   => 'Manage Settings',
			'prepare_ebay_listings' => 'Prepare Listings',
			'publish_ebay_listings' => 'Publish Listings',
		);

		$permissions = wple_clean($_POST['wpl_permissions']);

		foreach ( $available_roles as $role => $role_name ) {

			// admin permissions can't be modified
			if ( $role == 'administrator' ) continue;

			// get the the role object
			$role_object = get_role( $role );

			foreach ( $wpl_caps as $capability_name => $capability_title ) {

				if ( isset( $permissions[ $role ][ $capability_name ] ) ) {

					// add capability to this role
					$role_object->add_cap( $capability_name );

				} else {

					// remove capability from this role
					$role_object->remove_cap( $capability_name );

				}

			}

		}

	} // savePermissions()

	static function check_max_post_vars() {

		// count total number of post parameters - to show warning when running into max_input_vars limit ( or close: limit - 100 )
		$max_input_vars = ini_get('max_input_vars');
        $post_var_count = 0;
        foreach ( $_POST as $parameter ) {
            $post_var_count += is_array( $parameter ) ? sizeof( $parameter ) : 1;
        }
    	// show warning warning message if post count is close to limit
        if ( $post_var_count > $max_input_vars - 100 ) {

	    	$estimate = intval( $post_var_count / 100 ) * 100;
	    	$msg  = '<b>Warning: Your server has a limit of '.$max_input_vars.' input fields set for PHP</b> (max_input_vars)';
	    	$msg .= '<br><br>';
	    	$msg .= 'This page submitted more than '.$estimate.' fields, which means that either some data is already discarded by your server when this page is updated - or it will be when you add a few more product categories to your site. ';
	    	$msg .= '<br><br>';
	    	$msg .= 'Please contact your hosting provider and have them increase the <code>max_input_vars</code> PHP setting to at least '.($max_input_vars*2).' to prevent any issues saving your category mappings.';
	    	wple_show_message( $msg, 'warn' );

        }

	} // check_max_post_vars()

	protected function saveCategoriesSettings() {
		if ( ! current_user_can('manage_ebay_listings') ) return;

		self::check_max_post_vars();

        $account_id = ( isset($_REQUEST['account_id']) ? intval($_REQUEST['account_id']) : get_option('wplister_default_account_id') );
	    $site_id    = WPLE()->accounts[ $account_id ]->site_id;

	    if ( $account_id ) {
	    	$account = new WPLE_eBayAccount( $account_id );
			$account->default_ebay_category_id = $this->getValueFromPost( 'default_ebay_category_id' );
			$categories_map_ebay      = maybe_unserialize( $account->categories_map_ebay );
			$categories_map_store     = maybe_unserialize( $account->categories_map_store );

            if ( !$categories_map_ebay ) $categories_map_ebay = array();
            if ( !$categories_map_store ) $categories_map_store = array();

            // cast these two as arrays explicitly to get rid of warnings
            $categories_map_ebay = (array)$categories_map_ebay;
            $categories_map_store = (array)$categories_map_store;

            if ( !empty( $_POST['wpl_e2e_ebay_category_id'] ) ) {
                foreach ( wple_clean($_POST['wpl_e2e_ebay_category_id']) as $wc_category_id => $ebay_category_id ) {
                    $categories_map_ebay[ $wc_category_id ] = $ebay_category_id;
                }
            }

            if ( !empty( $_POST['wpl_e2e_store_category_id'] ) ) {
                foreach ( wple_clean($_POST['wpl_e2e_store_category_id']) as $wc_category_id => $store_category_id ) {
                    $categories_map_store[ $wc_category_id ] = $store_category_id;
                }
            }

            $account->categories_map_ebay = maybe_serialize( $categories_map_ebay );
            $account->categories_map_store = maybe_serialize( $categories_map_store );
			$account->update();
			WPLE()->loadAccounts();
	    }

	    // update current default account (legacy)
	    if ( $account_id == get_option('wplister_default_account_id') ) {

			// save ebay categories mapping
            $ebay_categories = (array)self::getOption( 'categories_map_ebay', array() );
            $store_categories = (array)self::getOption( 'categories_map_store', array() );

            if ( !empty( $_POST['wpl_e2e_ebay_category_id'] ) ) {
                foreach ( wple_clean($_POST['wpl_e2e_ebay_category_id']) as $wc_category_id => $ebay_category_id ) {
                    $ebay_categories[ $wc_category_id ] = $ebay_category_id;
                }
            }

            if ( !empty( $_POST['wpl_e2e_store_category_id'] ) ) {
                foreach ( wple_clean($_POST['wpl_e2e_store_category_id']) as $wc_category_id => $store_category_id ) {
                    $store_categories[ $wc_category_id ] = $store_category_id;
                }
            }

			self::updateOption( 'categories_map_ebay',	$ebay_categories );
			self::updateOption( 'categories_map_store',	$store_categories );

			// save default ebay category
			self::updateOption( 'default_ebay_category_id', $this->getValueFromPost( 'default_ebay_category_id' ) );

	    }

		$this->showMessage( __( 'Categories mapping updated.', 'wp-lister-for-ebay' ) );

	} // saveCategoriesSettings()


	protected function saveDeveloperSettings() {
		if ( ! current_user_can('manage_ebay_options') ) return;

		self::updateOption( 'log_level',					$this->getValueFromPost( 'text_log_level' ) );
		self::updateOption( 'log_to_db',					$this->getValueFromPost( 'option_log_to_db' ) );
		self::updateOption( 'ajax_error_handling',			$this->getValueFromPost( 'ajax_error_handling' ) );
		self::updateOption( 'php_error_handling',			$this->getValueFromPost( 'php_error_handling' ) );
		self::updateOption( 'disable_variations',			$this->getValueFromPost( 'disable_variations' ) );
		self::updateOption( 'disable_compat_list',			$this->getValueFromPost( 'disable_compat_list' ) );
		self::updateOption( 'log_include_authinfo',			$this->getValueFromPost( 'log_include_authinfo' ) );
		self::updateOption( 'enable_item_edit_link',		$this->getValueFromPost( 'enable_item_edit_link' ) );
		self::updateOption( 'log_record_limit',				$this->getValueFromPost( 'log_record_limit' ) );
		self::updateOption( 'log_days_limit',				$this->getValueFromPost( 'log_days_limit' ) );
		self::updateOption( 'orders_days_limit',			$this->getValueFromPost( 'orders_days_limit' ) );
		self::updateOption( 'archive_days_limit',			$this->getValueFromPost( 'archive_days_limit' ) );
		self::updateOption( 'xml_formatter',				$this->getValueFromPost( 'xml_formatter' ) );
		self::updateOption( 'eps_xfer_mode',				$this->getValueFromPost( 'eps_xfer_mode' ) );
		self::updateOption( 'force_table_items_limit',		$this->getValueFromPost( 'force_table_items_limit' ) );
		self::updateOption( 'apply_profile_batch_size',		$this->getValueFromPost( 'apply_profile_batch_size' ) );
		self::updateOption( 'inventory_check_batch_size',	$this->getValueFromPost( 'inventory_check_batch_size' ) );
		self::updateOption( 'grid_page_size',	 			$this->getValueFromPost( 'grid_page_size' ) );
		self::updateOption( 'fetch_orders_page_size',		$this->getValueFromPost( 'fetch_orders_page_size' ) );
		self::updateOption( 'staging_site_pattern',	  trim( $this->getValueFromPost( 'staging_site_pattern' ) ) );
		self::updateOption( 'notify_custom_email',	  trim( $this->getValueFromPost( 'notify_custom_email' ) ) );
        self::updateOption( 'enable_order_notify',          $this->getValueFromPost( 'enable_order_notify' ) );
        self::updateOption( 'revise_all_listings_limit',    $this->getValueFromPost( 'revise_all_listings_limit' ) );
        self::updateOption( 'disable_item_specifics_cache',  $this->getValueFromPost( 'disable_item_specifics_cache' ) );
        self::updateOption( 'item_specifics_limit',  $this->getValueFromPost( 'item_specifics_limit' ) );
        self::updateOption( 'disable_profile_popup_errors',  $this->getValueFromPost( 'disable_profile_popup_errors' ) );

		// updater instance
		update_option( 'wple_instance',	    			trim( $this->getValueFromPost( 'wple_instance' ) ) );

		// ignore_orders_before_ts
		$ignore_orders_before_ts = trim( $this->getValueFromPost( 'ignore_orders_before_ts' ) );
		update_option( 'ignore_orders_before_ts',	$ignore_orders_before_ts ? strtotime($ignore_orders_before_ts) : '' );

		$this->handleChangedUpdateChannel();

		do_action('wple_save_settings');

		$this->showMessage( __( 'Settings updated.', 'wp-lister-for-ebay' ) );

	} // saveDeveloperSettings()

	protected function handleChangedUpdateChannel() {


	}

	protected function loadProductCategories() {

		$flatlist = array();
		//$tree = get_terms( ProductWrapper::getTaxonomy(), 'orderby=name&hide_empty=0' );
		$tree = get_terms( array(
            'taxonomy'      => ProductWrapper::getTaxonomy(),
            'orderby'       => 'name',
            'hide_empty'    => 0
        ) );

		if ( ! is_wp_error($tree) ) {
			$result = $this->parseTree( $tree );
			$flatlist = $this->printTree( $result );
		}

		return $flatlist;
	}

	// parses wp terms array into a hierarchical tree structure
	function parseTree( $tree, $root = 0 ) {
		$return = array();

		// Traverse the tree and search for direct children of the root
		foreach ( $tree as $key => $item ) {

			// A direct child item is found
			if ( $item->parent == $root ) {

				// Remove item from tree (we don't need to traverse this again)
				unset( $tree[ $key ] );

				// Append the item into result array and parse its children
				$item->children = $this->parseTree( $tree, $item->term_id );
				$return[] = $item;

			}
		}
		return empty( $return ) ? null : $return;
	}

	function printTree( $tree, $depth = 0, $result = array() ) {
		$categories_map_ebay  = self::getOption( 'categories_map_ebay'  );
		$categories_map_store = self::getOption( 'categories_map_store' );

		if ( $this->account_id ) {
			$categories_map_ebay  = maybe_unserialize( WPLE()->accounts[ $this->account_id ]->categories_map_ebay );
			$categories_map_store = maybe_unserialize( WPLE()->accounts[ $this->account_id ]->categories_map_store );
		}

	    if( ($tree != 0) && (count($tree) > 0) ) {
	        foreach($tree as $node) {
	        	// indent category name accourding to depth
	            $node->name = str_repeat('&ndash; ' , $depth) . $node->name;

	            // get ebay category and (full) name
	            $ebay_category_id  = @$categories_map_ebay[$node->term_id];
	            $store_category_id = @$categories_map_store[$node->term_id];

	            // add item to results - excluding children
	            $tmpnode = array(
					'term_id'             => $node->term_id,
					'parent'              => $node->parent,
					'category'            => $node->name,
					'ebay_category_id'    => $ebay_category_id,
					'ebay_category_name'  => EbayCategoriesModel::getFullEbayCategoryName( $ebay_category_id, $this->site_id ),
					'store_category_id'   => $store_category_id,
					'store_category_name' => EbayCategoriesModel::getFullStoreCategoryName( $store_category_id, $this->account_id ),
					'description'         => $node->description
	            );

	            $result[] = $tmpnode;
	            $result = $this->printTree( $node->children, $depth+1, $result );
	        }
	    }
	    return $result;
	}




    // export rulesets as csv
    protected function handleImportCategoriesMap() {

        $uploaded_file = $this->process_upload();
        if (!$uploaded_file) return;

        // handle JSON export
        $json = file_get_contents($uploaded_file);
        $data = json_decode($json, true);

        if ( is_array($data) && ( sizeof($data) == 3 ) ) {
            $account_id = ( isset($_REQUEST['account_id']) ? intval($_REQUEST['account_id']) : get_option('wplister_default_account_id') );
            WPLE()->logger->info( 'Importing into account #'. $account_id );
            if ( $account_id ) {
                $account = new WPLE_eBayAccount( $account_id );

                $account->default_ebay_category_id = $data['default_ebay_category_id'];
                $account->categories_map_ebay = maybe_serialize( $data['categories_map_ebay'] );
                $account->categories_map_store = maybe_serialize( $data['categories_map_store'] );
                WPLE()->logger->info( 'New account: '. print_r( $account, 1 ) );
                $account->update();
                WPLE()->loadAccounts();
            } else {
                // save categories mapping
                self::updateOption( 'categories_map_ebay',		$data['categories_map_ebay'] );
                self::updateOption( 'categories_map_store',		$data['categories_map_store'] );
                self::updateOption( 'default_ebay_category_id', $data['default_ebay_category_id'] );
            }

			// show result
            $count_ebay  = sizeof($data['categories_map_ebay']);
            $count_store = sizeof($data['categories_map_store']);
            $this->showMessage( $count_ebay . ' ebay categories and '.$count_store.' store categories were imported.');

        } else {
            $this->showMessage( 'The uploaded file could not be imported. Please make sure you use a JSON backup file exported from this plugin.');
        }

    }

    // export rulesets as csv
    protected function handleExportCategoriesMap() {
        $account_id = ( isset($_REQUEST['account_id']) ? intval($_REQUEST['account_id']) : get_option('wplister_default_account_id') );
        WPLE()->logger->info( 'Exporting categories from account #'. $account_id );

        if ( $account_id ) {
            $account = new WPLE_eBayAccount( $account_id );
            $default_ebay_category_id = $account->default_ebay_category_id;
            $categories_map_ebay      = maybe_unserialize( $account->categories_map_ebay );
            $categories_map_store     = maybe_unserialize( $account->categories_map_store );

            if ( !$categories_map_ebay ) $categories_map_ebay = array();
            if ( !$categories_map_store ) $categories_map_store = array();

            // cast these two as arrays explicitly to get rid of warnings
            $categories_map_ebay = (array)$categories_map_ebay;
            $categories_map_store = (array)$categories_map_store;

            $data = array(
                'categories_map_ebay'       => $categories_map_ebay,
                'categories_map_store'      => $categories_map_store,
                'default_ebay_category_id'  => $default_ebay_category_id
            );
        } else {
            // get data
            $data = array();
            $data['categories_map_ebay']  		= self::getOption( 'categories_map_ebay'  );
            $data['categories_map_store'] 		= self::getOption( 'categories_map_store' );
            $data['default_ebay_category_id'] 	= self::getOption( 'default_ebay_category_id' );
        }

        // send JSON file
        header("Content-Disposition: attachment; filename=wplister_categories.json");
        echo json_encode( $data );
        exit;

    }


    /**
     * process file upload
     **/
    public function process_upload() {

        $this->target_path = WP_CONTENT_DIR.'/uploads/wplister_categories.json';

        if(isset($_FILES['wpl_file_upload'])) {

            $target_path = $this->target_path;

            // delete last import
            if ( file_exists($target_path) ) unlink($target_path);

            // echo '<div id="message" class="X-updated X-fade"><p>';
            // TODO: use wp_handle_upload() instead
            if(move_uploaded_file($_FILES['wpl_file_upload']['tmp_name'], $target_path))
            {
                // echo "The file ".  basename( $_FILES['wpl_file_upload']['name'])." has been uploaded";
                // $file_name = WP_CSV_TO_DB_URL.'/uploads/'.basename( $_FILES['wpl_file_upload']['name']);
                // update_option('wp_csvtodb_input_file_url', $file_name);
                // return true;
                return $target_path;
            }
            else
            {
                echo "There was an error uploading the file, please try again!";
            }
            // echo '</p></div>';
            return false;
        }
        echo "no file_upload set";
        return false;
    }


    function get_tax_rates() {
    	global $wpdb;

		$rates = $wpdb->get_results( "SELECT tax_rate_id, tax_rate_country, tax_rate_state, tax_rate_name, tax_rate_priority, tax_rate_class FROM {$wpdb->prefix}woocommerce_tax_rates ORDER BY tax_rate_name" );

		return $rates;
    }


    function get_timezones() {

		// create an array listing the time zones
		// http://www.ultramegatech.com/2009/04/working-with-time-zones-in-php/
		$zonelist = array(
			'America/Anchorage'              => '(GMT-09:00) Alaska',
			'America/Los_Angeles'            => '(GMT-08:00) Pacific Time (US &amp; Canada)',
			'America/Tijuana'                => '(GMT-08:00) Tijuana, Baja California',
			'America/Denver'                 => '(GMT-07:00) Mountain Time (US &amp; Canada)',
			'America/Chihuahua'              => '(GMT-07:00) Chihuahua',
			'America/Mazatlan'               => '(GMT-07:00) Mazatlan',
			'America/Phoenix'                => '(GMT-07:00) Arizona',
			'America/Regina'                 => '(GMT-06:00) Saskatchewan',
			'America/Tegucigalpa'            => '(GMT-06:00) Central America',
			'America/Chicago'                => '(GMT-06:00) Central Time (US &amp; Canada)',
			'America/Mexico_City'            => '(GMT-06:00) Mexico City',
			'America/Monterrey'              => '(GMT-06:00) Monterrey',
			'America/New_York'               => '(GMT-05:00) Eastern Time (US &amp; Canada)',
			'America/Bogota'                 => '(GMT-05:00) Bogota',
			'America/Lima'                   => '(GMT-05:00) Lima',
			'America/Rio_Branco'             => '(GMT-05:00) Rio Branco',
			'America/Indiana/Indianapolis'   => '(GMT-05:00) Indiana (East)',
			'America/Caracas'                => '(GMT-04:30) Caracas',
			'America/Halifax'                => '(GMT-04:00) Atlantic Time (Canada)',
			'America/Manaus'                 => '(GMT-04:00) Manaus',
			'America/Santiago'               => '(GMT-04:00) Santiago',
			'America/La_Paz'                 => '(GMT-04:00) La Paz',
			'America/St_Johns'               => '(GMT-03:30) Newfoundland',
			'America/Argentina/Buenos_Aires' => '(GMT-03:00) Georgetown',
			'America/Sao_Paulo'              => '(GMT-03:00) Brasilia',
			'America/Godthab'                => '(GMT-03:00) Greenland',
			'America/Montevideo'             => '(GMT-03:00) Montevideo',
			'Atlantic/South_Georgia'         => '(GMT-02:00) Mid-Atlantic',
			'Atlantic/Azores'                => '(GMT-01:00) Azores',
			'Atlantic/Cape_Verde'            => '(GMT-01:00) Cape Verde Is.',
			'Europe/Dublin'                  => '(GMT) Dublin',
			'Europe/Lisbon'                  => '(GMT) Lisbon',
			'Europe/London'                  => '(GMT) London',
			'Africa/Monrovia'                => '(GMT) Monrovia',
			'Atlantic/Reykjavik'             => '(GMT) Reykjavik',
			'Africa/Casablanca'              => '(GMT) Casablanca',
			'Europe/Belgrade'                => '(GMT+01:00) Belgrade',
			'Europe/Bratislava'              => '(GMT+01:00) Bratislava',
			'Europe/Budapest'                => '(GMT+01:00) Budapest',
			'Europe/Ljubljana'               => '(GMT+01:00) Ljubljana',
			'Europe/Prague'                  => '(GMT+01:00) Prague',
			'Europe/Sarajevo'                => '(GMT+01:00) Sarajevo',
			'Europe/Skopje'                  => '(GMT+01:00) Skopje',
			'Europe/Warsaw'                  => '(GMT+01:00) Warsaw',
			'Europe/Zagreb'                  => '(GMT+01:00) Zagreb',
			'Europe/Brussels'                => '(GMT+01:00) Brussels',
			'Europe/Copenhagen'              => '(GMT+01:00) Copenhagen',
			'Europe/Madrid'                  => '(GMT+01:00) Madrid',
			'Europe/Paris'                   => '(GMT+01:00) Paris',
			'Africa/Algiers'                 => '(GMT+01:00) West Central Africa',
			'Europe/Amsterdam'               => '(GMT+01:00) Amsterdam',
			'Europe/Berlin'                  => '(GMT+01:00) Berlin',
			'Europe/Rome'                    => '(GMT+01:00) Rome',
			'Europe/Stockholm'               => '(GMT+01:00) Stockholm',
			'Europe/Vienna'                  => '(GMT+01:00) Vienna',
			'Europe/Minsk'                   => '(GMT+02:00) Minsk',
			'Africa/Cairo'                   => '(GMT+02:00) Cairo',
			'Europe/Helsinki'                => '(GMT+02:00) Helsinki',
			'Europe/Riga'                    => '(GMT+02:00) Riga',
			'Europe/Sofia'                   => '(GMT+02:00) Sofia',
			'Europe/Tallinn'                 => '(GMT+02:00) Tallinn',
			'Europe/Vilnius'                 => '(GMT+02:00) Vilnius',
			'Europe/Athens'                  => '(GMT+02:00) Athens',
			'Europe/Bucharest'               => '(GMT+02:00) Bucharest',
			'Europe/Istanbul'                => '(GMT+02:00) Istanbul',
			'Asia/Jerusalem'                 => '(GMT+02:00) Jerusalem',
			'Asia/Amman'                     => '(GMT+02:00) Amman',
			'Asia/Beirut'                    => '(GMT+02:00) Beirut',
			'Africa/Windhoek'                => '(GMT+02:00) Windhoek',
			'Africa/Harare'                  => '(GMT+02:00) Harare',
			'Asia/Kuwait'                    => '(GMT+03:00) Kuwait',
			'Asia/Riyadh'                    => '(GMT+03:00) Riyadh',
			'Asia/Baghdad'                   => '(GMT+03:00) Baghdad',
			'Africa/Nairobi'                 => '(GMT+03:00) Nairobi',
			'Asia/Tbilisi'                   => '(GMT+03:00) Tbilisi',
			'Europe/Moscow'                  => '(GMT+03:00) Moscow',
			'Europe/Volgograd'               => '(GMT+03:00) Volgograd',
			'Asia/Tehran'                    => '(GMT+03:30) Tehran',
			'Asia/Muscat'                    => '(GMT+04:00) Muscat',
			'Asia/Baku'                      => '(GMT+04:00) Baku',
			'Asia/Yerevan'                   => '(GMT+04:00) Yerevan',
			'Asia/Yekaterinburg'             => '(GMT+05:00) Ekaterinburg',
			'Asia/Karachi'                   => '(GMT+05:00) Karachi',
			'Asia/Tashkent'                  => '(GMT+05:00) Tashkent',
			'Asia/Kolkata'                   => '(GMT+05:30) Calcutta',
			'Asia/Colombo'                   => '(GMT+05:30) Sri Jayawardenepura',
			'Asia/Katmandu'                  => '(GMT+05:45) Kathmandu',
			'Asia/Dhaka'                     => '(GMT+06:00) Dhaka',
			'Asia/Almaty'                    => '(GMT+06:00) Almaty',
			'Asia/Novosibirsk'               => '(GMT+06:00) Novosibirsk',
			'Asia/Rangoon'                   => '(GMT+06:30) Yangon (Rangoon)',
			'Asia/Krasnoyarsk'               => '(GMT+07:00) Krasnoyarsk',
			'Asia/Bangkok'                   => '(GMT+07:00) Bangkok',
			'Asia/Jakarta'                   => '(GMT+07:00) Jakarta',
			'Asia/Brunei'                    => '(GMT+08:00) Beijing',
			'Asia/Chongqing'                 => '(GMT+08:00) Chongqing',
			'Asia/Hong_Kong'                 => '(GMT+08:00) Hong Kong',
			'Asia/Urumqi'                    => '(GMT+08:00) Urumqi',
			'Asia/Irkutsk'                   => '(GMT+08:00) Irkutsk',
			'Asia/Ulaanbaatar'               => '(GMT+08:00) Ulaan Bataar',
			'Asia/Kuala_Lumpur'              => '(GMT+08:00) Kuala Lumpur',
			'Asia/Singapore'                 => '(GMT+08:00) Singapore',
			'Asia/Taipei'                    => '(GMT+08:00) Taipei',
			'Australia/Perth'                => '(GMT+08:00) Perth',
			'Asia/Seoul'                     => '(GMT+09:00) Seoul',
			'Asia/Tokyo'                     => '(GMT+09:00) Tokyo',
			'Asia/Yakutsk'                   => '(GMT+09:00) Yakutsk',
			'Australia/Darwin'               => '(GMT+09:30) Darwin',
			'Australia/Adelaide'             => '(GMT+09:30) Adelaide',
			'Australia/Canberra'             => '(GMT+10:00) Canberra',
			'Australia/Melbourne'            => '(GMT+10:00) Melbourne',
			'Australia/Sydney'               => '(GMT+10:00) Sydney',
			'Australia/Brisbane'             => '(GMT+10:00) Brisbane',
			'Australia/Hobart'               => '(GMT+10:00) Hobart',
			'Asia/Vladivostok'               => '(GMT+10:00) Vladivostok',
			'Pacific/Guam'                   => '(GMT+10:00) Guam',
			'Pacific/Port_Moresby'           => '(GMT+10:00) Port Moresby',
			'Asia/Magadan'                   => '(GMT+11:00) Magadan',
			'Pacific/Fiji'                   => '(GMT+12:00) Fiji',
			'Asia/Kamchatka'                 => '(GMT+12:00) Kamchatka',
			'Pacific/Auckland'               => '(GMT+12:00) Auckland',
			'Pacific/Tongatapu'              => '(GMT+13:00) Nukualofa',
			'Kwajalein'                      => '(GMT-12:00) International Date Line West',
			'Pacific/Midway'                 => '(GMT-11:00) Midway Island',
			'Pacific/Samoa'                  => '(GMT-11:00) Samoa',
			'Pacific/Honolulu'               => '(GMT-10:00) Hawaii'
		);

		return $zonelist;
	}










	protected function handleCronSettings( $schedule ) {
        WPLE()->logger->info("handleCronSettings( $schedule )");

        // remove scheduled event
	    $timestamp = wp_next_scheduled(  'wplister_update_auctions' );
    	wp_unschedule_event( $timestamp, 'wplister_update_auctions' );

    	if ( $schedule == 'external' ) return;

		if ( !wp_next_scheduled( 'wplister_update_auctions' ) ) {
			wp_schedule_event( time(), $schedule, 'wplister_update_auctions' );
		}

	}


	public function onWpPrintStyles() {

		// jqueryFileTree
		wp_register_style('jqueryFileTree_style', self::$PLUGIN_URL.'js/jqueryFileTree/jqueryFileTree.css' );
		wp_enqueue_style('jqueryFileTree_style');

	}

	public function onWpEnqueueScripts() {

		// jqueryFileTree
		wp_register_script( 'jqueryFileTree', self::$PLUGIN_URL.'js/jqueryFileTree/jqueryFileTree.js', array( 'jquery' ) );
		wp_enqueue_script( 'jqueryFileTree' );

	}

	public function renderSettingsOptions() {
		?>
		<div class="hidden" id="screen-options-wrap" style="display: block;">
			<form method="post" action="" id="dev-settings">
				<h5>Show on screen</h5>
				<div class="metabox-prefs">
						<label for="dev-hide">
							<input type="checkbox" onclick="jQuery('.dev_box').toggle();" value="dev" id="dev-hide" name="dev-hide" class="hide-column-tog">
							Developer options
						</label>
					<br class="clear">
				</div>
			</form>
		</div>
		<?php
	}

}
