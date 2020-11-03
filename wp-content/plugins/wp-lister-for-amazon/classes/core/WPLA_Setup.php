<?php

class WPLA_Setup extends WPLA_Core {
	
	// check if setup is incomplete and display next step
	public function checkSetup( $page = false ) {
		global $pagenow;

		// check if incomatible plugins are active
		$this->checkPlugins();

		// check if cURL is loaded
		if ( ! self::isCurlLoaded() ) return false;

		// check for windows server
		// if ( self::isWindowsServer() ) return false;
		self::isWindowsServer( $page );

		// create folders if neccessary
		// if ( self::checkFolders() ) return false;

		// check for updates
		self::checkForUpdates();

		// check if cron is working properly
		self::checkCron();

		// check if PHP, WooCommerce and WP are up to date
		self::checkVersions();

		// check for multisite installation
		// if ( self::checkMultisite() ) return false;

		// setup wizard
		// if ( self::getOption('amazon_token') == '' ) {
		if ( ( '1' == self::getOption('setup_next_step') ) && ( $page != 'settings') ) {
		
			$msg1 = __( 'You have not linked WP-Lister to your Amazon account yet.', 'wp-lister-for-amazon' );
			$msg2 = __( 'To complete the setup procedure go to %s and follow the instructions.', 'wp-lister-for-amazon' );
			$link = '<a href="admin.php?page=wpla-settings">'.__( 'Settings', 'wp-lister-for-amazon' ).'</a>';
			$msg2 = sprintf($msg2, $link);
			$msg = "<p><b>$msg1</b></p><p>$msg2</p>";
			wpla_show_message($msg);
		
			// update_option('wpla_setup_next_step', '0');
		
		}

		
		// db upgrade
		WPLA_UpgradeHelper::upgradeDB();

		// check if all db tables exist
		self::checkDatabaseTables( $page );

		// check for outdated accounts
		self::checkForAccountsWithoutMwsAuthToken( $page );

	} // checkSetup()


	// check if cURL is loaded
	public function isCurlLoaded() {

		if( ! extension_loaded('curl') ) {
			wpla_show_message("
				<b>Required PHP extension missing</b><br>
				<br>
				Your server doesn't seem to have the <a href='http://www.php.net/curl' target='_blank'>cURL</a> php extension installed.<br>
				cURL ist required by WP-Lister to be able to talk with Amazon.<br>
				<br>
				On a recent debian based linux server running PHP 5 this should do the trick:<br>
				<br>
				<code>
					apt-get install php5-curl <br>
					/etc/init.d/apache2 restart
				</code>
				<br>
				<br>
				You'll require root access on your server to install additional php extensions!<br>
				If you are on a shared host, you need to ask your hoster to enable the cURL php extension for you.<br>
				<br>
				For more information on how to install the cURL php extension on other servers check <a href='http://stackoverflow.com/questions/1347146/how-to-enable-curl-in-php' target='_blank'>this page on stackoverflow</a>.
			",'error');
			return false;
		}

		return true;
	}

	// check server is running windows - or Solaris
	public function isWindowsServer( $page ) {

		if ( $page != 'settings' ) return;

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

			wpla_show_message("
				<b>Warning: Server requirements not met - this server runs on windows.</b><br>
				<br>
				WP-Lister currently only supports unixoid operating systems like Linux, FreeBSD and OS X.<br>
				Support for windows servers is still experimental and should not be used on production sites!
			",'warn');
			return true;
		}

		if (strtoupper(substr(PHP_OS, 0, 5)) === 'SUNOS') {

			wpla_show_message("
				<b>Warning: Server requirements not met - this server runs on Solaris (SunOS).</b><br>
				<br>
				WP-Lister for Amazon currently only supports Linux, FreeBSD and OS X.<br>
				Running WP-Lister on a Solaris server makes it impossible to communicate with the Amazon API.
			",'error');
			return true;
		}

		return false;
	}

	// check if WP_Cron is working properly
	public function checkCron() {

		$cron_interval  = get_option( 'wpla_cron_schedule' );
		$next_scheduled = wp_next_scheduled( 'wpla_update_schedule' ) ;
		if ( 'external' == $cron_interval ) $cron_interval = false;

		if ( $cron_interval && ! $next_scheduled ) {

			wpla_show_message( 
				'<p>'
				. '<b>Warning: WordPress Cron Job has been disabled - scheduled WP-Lister tasks are not executed!</b>'
				. '<br><br>'
				. 'The task schedule has been reset just now in order to automatically fix this.'
				. '<br><br>'
				. 'If this message does not disappear, please visit the <a href="admin.php?page=wpla-settings&tab=settings">Settings</a> page and click <i>Save Settings</i> or contact support.'
				. '</p>'
			,'warn');

			// this should fix it:
			wp_schedule_event( time(), $cron_interval, 'wpla_update_schedule' );

		}

		// schedule daily event if not set yet
		if ( ! wp_next_scheduled( 'wpla_daily_schedule' ) ) {
			wp_schedule_event( time(), 'daily', 'wpla_daily_schedule' );
		}

		// schedule FBA Shipment report request - if not set yet
		if ( ! wp_next_scheduled( 'wpla_fba_report_schedule' ) && ! WPLA_Setup::isStagingSite() ) {
			$schedule = get_option( 'wpla_fba_report_schedule', 'daily' );
			wp_schedule_event( time(), $schedule, 'wpla_fba_report_schedule' );
		}

	}

	// check versions
	public function checkVersions() {

		// WP-Lister for eBay 1.6+
		if ( defined('WPLISTER_VERSION') && version_compare( WPLISTER_VERSION, '1.6', '<') ) {
			wpla_show_message( 
				'<p>'
				. '<b>Warning: Your version of WP-Lister for eBay '.WPLISTER_VERSION.' is not fully compatible with WP-Lister for Amazon.</b>'
				. '<br><br>'
				. 'To prevent any issues, please update to WP-Lister for eBay 1.6 or better.'
				. '</p>'
			,'warn');
		}

		// check if WooCommerce is up to date
		$required_version    = '2.2.4';
		$woocommerce_version = defined('WC_VERSION') ? WC_VERSION : WOOCOMMERCE_VERSION;
		if ( version_compare( $woocommerce_version, $required_version ) < 0 ) {
			wpla_show_message("
				<b>Warning: Your WooCommerce version is outdated.</b><br>
				<br>
				WP-Lister requires WooCommerce $required_version to be installed. You are using WooCommerce $woocommerce_version.<br>
				You should always keep your site and plugins updated.<br>
			",'warn');
		}

		// PHP 5.3+
		if ( version_compare(phpversion(), '5.3', '<')) {
			wpla_show_message( 
				'<p>'
				. '<b>Warning: Your PHP version '.phpversion().' is outdated.</b>'
				. '<br><br>'
				. 'Your server should have PHP 5.3 or better installed.'
				. ' '
				. 'Please contact your hosting support and ask them to update your PHP version.'
				. '</p>'
			,'warn');
		}

		// OpenSSL 0.9.8o or later is required by Amazon (as of late 2015)
		// https://sellercentral.amazon.com/forums/ann.jspa?annID=284
		if ( defined('OPENSSL_VERSION_NUMBER') && ( OPENSSL_VERSION_NUMBER < 0x009080ff ) ) {
			wpla_show_message( 
				'<p>'
				. '<b>Warning: Your version of '.OPENSSL_VERSION_TEXT.' is outdated and not supported by Amazon anymore.</b>'
				. '<br><br>'
				. 'To prevent any issues communicating with the Amazon API, please ask your hosting provider to update OpenSSL to version 0.9.8o or better.'
				. '</p>'
			,'warn');
		}

	}


	// checks for incompatible plugins
	public function checkPlugins() {

		// // Plugin Name: SEO by SQUIRRLY
		// // Plugin URI: http://www.squirrly.co
		// // Plugin URI: https://wordpress.org/plugins/squirrly-seo/
		// // Version: 6.0.8
		// if ( defined('SQ_VERSION') && class_exists('SQ_ObjController') ) {

		// 	wpla_show_message("
		// 		<b>Warning: An incompatible plugin was found.</b><br>
		// 		<br>
		// 		You seem to have the <i>SEO by SQUIRRLY</i> plugin installed, which is known to cause issues with WP-Lister.<br>
		// 		Version 6.0.8 of this plugin prevents WP-Lister from being notified when a product is updated on the edit product page.<br>
		// 		It does so by calling <i>remove_action()</i> to remove the action hook for 'save_post' from within the method <i>hookSavePost()</i> which is triggered by executing the 'save_post' action in the first place.<br>
		// 		<br>
		// 		In order to use WP-Lister, you need to deactivate this plugin and use another SEO plugin - like the <i>Yoast SEO</i> plugin by Yoast.
		// 	",'warn');
		// 	return false;

		// }

	} // checkPlugins()


	// checks for multisite network
	public function checkMultisite() {

		if ( is_multisite() ) {

			// check for network activation
			if ( ! function_exists( 'is_plugin_active_for_network' ) )
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

			if ( function_exists('is_network_admin') && is_plugin_active_for_network( plugin_basename( WPLA_PATH.'/wp-lister-amazon.php' ) ) )
				wpla_show_message("network activated!");
			else
				wpla_show_message("not network activated!");


			// $this->showMessage("
			// 	<b>Multisite installation detected</b><br>
			// 	<br>
			// 	This is a site network...<br>
			// ");
			return true;
		}

		return false;
	}


	// check for updates
	public function checkForUpdates() {
	}

	// check if all database tables exist
	static function checkDatabaseTables( $page ) {
		global $wpdb;

		if ( $page != 'settings' ) return;
		if ( 0 == get_option('wpla_db_version', 0) ) return;

		$required_tables = array(
			'amazon_accounts',
			'amazon_btg',
			'amazon_categories',
			'amazon_feed_templates',
			'amazon_feed_tpl_data',
			'amazon_feed_tpl_values',
			'amazon_feeds',
			'amazon_jobs',
			'amazon_listings',
			'amazon_log',
			'amazon_markets',
			'amazon_orders',
			'amazon_payment',
			'amazon_profiles',
			'amazon_reports',
			'amazon_shipping',
			'amazon_stock_log',
		);

		$tables  = $wpdb->get_col('show tables like "'.$wpdb->prefix.'amazon%" ');
		$missing = array();

		foreach ($required_tables as $tablename ) {
			if ( ! in_array( $wpdb->prefix.$tablename, $tables ) ) {
				$missing[] = $tablename;
			}
		}

		if ( ! empty($missing) ) {
			wpla_show_message( '<b>Error: The following table(s) are missing in your database: ' . join(', ', $missing) . '</b><br>Please contact support or reinstall WP-Lister from scratch, using the "Uninstall on removal" option.', 'error' );
		}

	} // checkDatabaseTables()


	// check if there are any accounts without MWSAuthToken - and show upgrade message
	static function checkForAccountsWithoutMwsAuthToken( $page ) {

		// show different message on edit account page
		if ( $page == 'settings' && isset($_GET['action']) && $_GET['action'] == 'edit_account' ) {
			return self::displayAccountUpgradeInformationMessage();
		}

		$accounts       = WPLA()->accounts;
		$found_accounts = array();

        foreach ( $accounts as $account ) {

	        // check if this account is active
	        if ( ! $account->active ) continue;

	        // check if this account has an MWSAuthToken set
	        if ( ! empty($account->mws_auth_token) ) continue;

        	// add account to list
        	$found_accounts[] = $account;
        }
		if ( empty( $found_accounts ) ) return;

		$accounts_page_link = sprintf('<a href="%s" class="">account settings page</a>', 'admin.php?page=wpla-settings&tab=accounts' );

		// show message
		$msg = '<b>Your action is required: Authenticate your account via MWS Auth Token</b>' . '<br><br>';

		$msg .= count( $found_accounts ) > 1 ? 'There are ' . count( $found_accounts ) . ' accounts still using ' : 'Your account still uses ';
		$msg .= 'your developer <i>Access Key ID</i> and <i>Secret Key</i> for authentication, which sellers are not allowed to do anymore.'. '<br><br>';

		$msg .= 'To make sure WP-Lister will continue working with your Amazon account(s), you need to reauthenticate your account(s) using an <i>MWS Auth Token</i> by following these steps:';

		$msg .= '<ol class="ol-decimal">';
		$msg .= '<li>'."Open the $accounts_page_link where you see a warning message on each account without Auth Token.".'</li>';
		$msg .= '<li>'.'Click the <i>Reauthenticate</i> button next to each message to open the account settings.'.'</li>';
		$msg .= '<li>'.'Follow the instructions on that page to authorise our Developer ID and get your Auth Token.'.'</li>';
		$msg .= '<li>'.'Enter your token in the field for <i>MWS Auth Token</i> and save your account settings.'.'</li>';
		$msg .= '<li>'.'If your have more than one account, repeat steps 2-5 for each account where you see the message.'.'</li>';
		$msg .= '</ol>';

		wpla_show_message($msg,'warn');

	} // checkForAccountsWithoutMwsAuthToken()


	// show instructions on how to update / reauthenticate account and get MWS Token
	static function displayAccountUpgradeInformationMessage() {

		$account_id = intval($_REQUEST['amazon_account']);
		$account = WPLA_AmazonAccount::getAccount( $account_id );
		if ( ! $account ) return;

		// check if Auth Token is already present
		if ( ! empty($account->mws_auth_token) ) return;

		// get Developer ID for this marketplace
		$developer_id = WPLA_AmazonHelper::getDevIdForMarketId( $account->market_id );

		// get correct URL to Manage Apps page
		$market = new WPLA_AmazonMarket( $account->market_id );
		$apps_page_url  = $market->getSignInUrl();
		$apps_page_link = '<a href="'.$apps_page_url.'" target="_blank">Manage your apps</a>';

		// show message
		$msg = '<h2>Authenticate your account via MWS Auth Token now!</h2>';

		$msg .= 'To make sure WP-Lister will continue working with this account, you need to follow these steps, which will only take a few minutes:';

		$msg .= '<ol class="ol-decimal">';
		$msg .= '<li>'.'Sign into Seller Central using this account as the primary user.'.'</li>';
		$msg .= '<li>'."Open the $apps_page_link page in Seller Central and click <strong>Authorize new developer</strong>.".'</li>';
		$msg .= '<li>'."Enter the name <strong>WP-Lister</strong> and the Developer ID <strong>$developer_id</strong>.".'</li>';
		$msg .= '<li>'.'Follow the authorization workflow until you see your seller account identifiers on the final page.'.'</li>';
		$msg .= '<li>'.'Copy your <strong>MWS Auth Token</strong> in the field below, and click <strong>Update</strong> on the right.'.'</li>';
		$msg .= '</ol>';
		$msg .= 'To make sure the token you entered is correct, you might want to perform a simple action like manually checking for new orders on the Orders page.'.' <!br>';
		$msg .= 'If you do not see any error messages, you can be sure that everything is set up correctly now.';

		wpla_show_message($msg,'warn');

	} // displayAccountUpgradeInformationMessage()


	// check if there are any outdated listing templates that need to be replaced
	static function checkForOudatedListingTemplates( $verbose = false ) {

		$templates       = WPLA_AmazonFeedTemplate::getAll();
		$found_templates = array();

        foreach ( $templates as $tpl ) {
	
	        // check if this is a deprecated category specific template
	        if ( substr($tpl->name,0,9) != 'fptcustom' && $tpl->name != 'Offer' && $tpl->name != 'InventoryLoader' && $tpl->name != 'bookloader' ) {
	        	$found_templates[] = $tpl;
        	}

        }
		if ( empty( $found_templates ) ) return;

		// build links
		$categories_page_link = sprintf('<a href="%s" class="">category settings page</a>', 'admin.php?page=wpla-settings&tab=categories' );
		$profiles_page_link   = sprintf('<a href="%s" class="">profiles page</a>', 'admin.php?page=wpla-profiles' );

		// show message
		$msg = '<b>Your action is required: Replace outdated feed templates with updated versions</b>' . '<br><br>';

		$msg .= 'As you might know, Amazon has retired their older category specific feed templates and wants every seller to use updated versions now. You have still some old feed templates installed, so please check your profiles and categories and remove any outdated feed templates. This will avoid error messages from Amazon as well as confusion from having different template versions for the same category installed.'. '<br><br>';

		$msg .= 'Follow these steps to make sure you are using only the latest feed templates:';
		$msg .= '<ol class="ol-decimal">';
		$msg .= '<li>'."Open the $categories_page_link and click the <i>Update</i> button on the top right.".'</li>';
		$msg .= '<li>'."Open the $profiles_page_link and check if there are any warnings about outdated templates.".'</li>';
		$msg .= '<li>'.'If there are, open each profile and select the latest feed template version available.<br>Before you save the profile, check if there are any new fields that require your attention.'.'</li>';
		$msg .= '<li>'."Back on the $categories_page_link, click the <i>Remove</i> button next to each outdated template.".'</li>';
		$msg .= '<li>'.'You are done, this message will be gone.'.'</li>';
		$msg .= '</ol>';

		// show only short version by default (used on settings page, long version is shown on listings page)
		if ( ! $verbose ) {
			wpla_show_message($msg,'warn');
			return;
		}

		$msg .= 'The following templates are outdated and have to be replaced and removed:'. '<br>';
		$msg .= '<ul class="ul-disc">';

		foreach ( $found_templates as $tpl ) {
			$market  = WPLA()->memcache->getMarket( $tpl->site_id );
			$msg .= '<li>' . $tpl->title . ' (' . $market->code . ') - ' . $tpl->version . '</li>';
		}

		$msg .= '</ul>';

		$msg .= 'If you followed the steps above and there are still outdated templates left, please contact support and let us know the name, version and marketplace of the templates in question.'. '<br><br>';

		$msg .= sprintf('<a href="%s" class="button button-secondary">Open category settings page</a>', 'admin.php?page=wpla-settings&tab=categories' );
		wpla_show_message($msg,'warn');

	} // checkForOudatedListingTemplates()


	// check if there are active accounts using the same MerchantID
	static function checkForAccountsWithSameMerchantID() {

		$found_accounts = WPLA_AmazonAccount::getDuplicateMerchantIDs();
		if ( empty( $found_accounts ) ) return;
		if ( get_option( 'wpla_fetch_orders_filter', 0 ) == 1 ) return;

		// show message
		$msg = '<b>Important: You are using the same Seller ID on multiple accounts.</b>' . '<br><br>';
		$msg .= 'This is not a problem, but you need to enable the "Filter orders" setting option to make sure that orders are imported separately for each account.'. '<br>';
		$msg .= 'Currently that option is disabled, which can lead to problems where orders could get assigned to the wrong account or marketplace.'. '<br><br>';
		$msg .= 'Please note that when you enable that option, you need to add an account for every marketplace you are selling on, or WP-Lister will not be able to fetch all orders.'. '<br><br>';
		$msg .= sprintf('<a href="%s" class="button button-secondary">Open general settings page</a>', 'admin.php?page=wpla-settings' );
		wpla_show_message($msg,'warn');

	}


	// check for listings, profiles and orders using an invalid / nonexisting account
	static function checkDbForInvalidAccounts() {
		global $wpdb;
		$accounts              = WPLA()->accounts;
		$default_account_id    = get_option( 'wpla_default_account_id' );
		$default_account       = isset( $accounts[ $default_account_id ] ) ? $accounts[ $default_account_id ] : false;
		$default_account_title = $default_account ? $default_account->title : 'MISSING DEFAULT ACCOUNT';
		if ( empty($accounts) ) return;

		// get list of all active account IDs
		$active_account_ids = array();
		foreach ($accounts as $account) {
			$active_account_ids[] = $account->id;
		}
		$active_account_ids_sql = join(', ', $active_account_ids);

		// find data with invalid account IDs
		$listings_count = $wpdb->get_var("
			SELECT count(account_id)
			  FROM ".$wpdb->prefix."amazon_listings
			 WHERE NOT account_id IN ( $active_account_ids_sql )
		");
		$profiles_count = $wpdb->get_var("
			SELECT count(account_id)
			  FROM ".$wpdb->prefix."amazon_profiles
			 WHERE NOT account_id IN ( $active_account_ids_sql )
		");
		$orders_count = $wpdb->get_var("
			SELECT count(account_id)
			  FROM ".$wpdb->prefix."amazon_orders
			 WHERE NOT account_id IN ( $active_account_ids_sql )
		");

		// return if no problems found
		if ( ! $listings_count && ! $profiles_count && ! $orders_count ) return;

		// compile summary
		$what_exactly = array();
		if ( $listings_count ) $what_exactly[] = $listings_count . ' listings';
		if ( $profiles_count ) $what_exactly[] = $profiles_count . ' profiles';
		if ( $orders_count   ) $what_exactly[] = $orders_count   . ' orders';
		$what_exactly = join(' and ',$what_exactly);

		$btn_url = wp_nonce_url( 'admin.php?page=wpla-settings&tab=accounts&action=wpla_assign_invalid_data_to_default_account', 'wpla_assign_invalid_data_to_default_account' );

		// show message
		$msg = sprintf('<b>Warning: There are %s using an account which does not exist anymore.</b>',$what_exactly) . '<br><br>';
		$msg .= 'This can happen when you delete an account from WP-Lister without removing all listings, profiles and orders first.'. '<br>';
		$msg .= sprintf('Please click the button below to assign all found items to your default account <b>%s</b> (ID %s).', $default_account_title, $default_account_id ) . '<br><br>';
		$msg .= sprintf('<a href="%s" class="button button-secondary">Assign found items to default account</a>', $btn_url );
		wpla_show_message($msg,'warn');

	} // checkDbForInvalidAccounts()

	// fix listings, profiles and orders using an invalid / nonexisting account
	static function fixItemsUsingInvalidAccounts() {
		global $wpdb;
		$accounts           = WPLA()->accounts;
		$default_account_id = get_option( 'wpla_default_account_id' );
		$default_account    = isset( $accounts[ $default_account_id ] ) ? $accounts[ $default_account_id ] : false;
		if ( ! $default_account ) die('Invalid default account set!');

		// get list of all active account IDs
		$active_account_ids = array();
		foreach ($accounts as $account) {
			$active_account_ids[] = $account->id;
		}
		$active_account_ids_sql = join(', ', $active_account_ids);

		// find data with invalid account IDs
		$listings_count = $wpdb->get_var("
			UPDATE ".$wpdb->prefix."amazon_listings
			   SET account_id = '$default_account_id'
			 WHERE NOT account_id IN ( $active_account_ids_sql )
		");
		$profiles_count = $wpdb->get_var("
			UPDATE ".$wpdb->prefix."amazon_profiles
			   SET account_id = '$default_account_id'
			 WHERE NOT account_id IN ( $active_account_ids_sql )
		");
		$orders_count = $wpdb->get_var("
			UPDATE ".$wpdb->prefix."amazon_orders
			   SET account_id = '$default_account_id'
			 WHERE NOT account_id IN ( $active_account_ids_sql )
		");
		// echo $wpdb->last_query;
		echo $wpdb->last_error;

		// show message
		$msg = 'All found items have been assigned to your default account.';
		wpla_show_message($msg);

	} // fixItemsUsingInvalidAccounts()


	static public function isStagingSite() {
		$staging_site_pattern = get_option('wpla_staging_site_pattern');
		if ( ! $staging_site_pattern ) {
			update_option('wpla_staging_site_pattern','staging'); // if no pattern set, use default 'staging'
			return false;
		}

		$domain = $_SERVER["SERVER_NAME"];
		
		if ( preg_match( "/$staging_site_pattern/", $domain ) ) {
			return true;
		}
		if ( preg_match( "/wpstagecoach.com/", $domain ) ) {
			return true;
		}

		return false;
	}


} // class WPLA_Setup

