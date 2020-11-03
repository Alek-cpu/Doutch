<?php
/**
 * WPLA_AccountsPage class
 * 
 */

class WPLA_AccountsPage extends WPLA_Page {

	const slug = 'accounts';

	public function onWpInit() {

		// Add custom screen options
		if ( ! isset($_GET['tab']) || $_GET['tab'] == 'accounts' ) {
			// $load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".self::slug;
			$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".'settings';
			add_action( $load_action, array( &$this, 'addScreenOptions' ) );
		}

		if ( get_option( 'wpla_enable_accounts_page' ) ) {
			$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".'settings';
			add_action( $load_action.'-accounts', array( &$this, 'addScreenOptions' ) );
		}

	}

	
	public function handleActions() {
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// save accounts
		if ( $this->requestAction() == 'wpla_add_account' ) {
		    check_admin_referer( 'wpla_add_account' );
			$this->newAccount();
		}

		// update account details from amazon
		if ( $this->requestAction() == 'wpla_update_account' ) {
		    check_admin_referer( 'wpla_update_account' );
			$this->updateAccount( wpla_clean($_REQUEST['amazon_account']) );
		}

		// delete account
		if ( $this->requestAction() == 'wpla_delete_account' ) {
		    check_admin_referer( 'wpla_delete_account' );
			$account = new WPLA_AmazonAccount( wpla_clean($_REQUEST['amazon_account']) );
			$account->delete();
			$this->showMessage( __( 'Account has been deleted.', 'wp-lister-for-amazon' ) );
		}

		// enable account
		if ( $this->requestAction() == 'wpla_enable_account' ) {
		    check_admin_referer( 'wpla_enable_account' );
			$account = new WPLA_AmazonAccount( wpla_clean($_REQUEST['amazon_account']) );
			$account->active = 1;
			$account->update();
			$this->showMessage( __( 'Account has been enabled.', 'wp-lister-for-amazon' ) );
		}

		// disable account
		if ( $this->requestAction() == 'wpla_disable_account' ) {
		    check_admin_referer( 'wpla_disable_account' );
			$account = new WPLA_AmazonAccount( wpla_clean($_REQUEST['amazon_account']) );
			$account->active = 0;
			$account->update();
			$this->showMessage( __( 'Account has been disabled.', 'wp-lister-for-amazon' ) );
		}

		// set default account
		if ( $this->requestAction() == 'wpla_make_default' ) {
		    check_admin_referer( 'wpla_make_default_account' );
			update_option( 'wpla_default_account_id', wpla_clean($_REQUEST['amazon_account']) );
		}

		// assign invalid data to default account
		if ( $this->requestAction() == 'wpla_assign_invalid_data_to_default_account' ) {
		    check_admin_referer( 'wpla_assign_invalid_data_to_default_account' );
			WPLA_Setup::fixItemsUsingInvalidAccounts();
		}

	} // handleActions()
	

	public function updateAccount( $id ) {

		$account = new WPLA_AmazonAccount( $id );
		if ( ! $account ) return;

		// update allowed markets
		$account->updateMarketplaceParticipations();

		$this->showMessage( __( 'Account details have been updated.', 'wp-lister-for-amazon' ) );
	}

	function addScreenOptions() {
		
		// render table options
		$option = 'per_page';
		$args = array(
	    	'label' => 'Accounts',
	        'default' => 20,
	        'option' => 'accounts_per_page'
	        );
		add_screen_option( $option, $args );
		$this->accountsTable = new WPLA_AccountsTable();
	
	    // add_thickbox();
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

	}
	

	public function displayAccountsPage() {

		// handle actions and show notes
		$this->handleActions();

		if ( $this->requestAction() == 'wpla_save_account' ) {
		    check_admin_referer( 'wpla_save_account' );
			$this->saveAccount();
		}
		if ( $this->requestAction() == 'edit_account' ) {
			return $this->displayEditAccountsPage();
		}

		if ( $default_account_id = get_option( 'wpla_default_account_id' ) ) {
			$default_account = WPLA_AmazonAccount::getAccount( $default_account_id );
			if ( ! $default_account ) {
				$this->showMessage( __( 'Your default account does not exist anymore. Please select a new default account.', 'wp-lister-for-amazon' ),1);
			}
		}

		// check for data linked to deleted accounts
		WPLA_Setup::checkDbForInvalidAccounts();


	    // create table and fetch items to show
	    $this->accountsTable = new WPLA_AccountsTable();
	    $this->accountsTable->prepare_items();

		$active_tab = 'accounts';
		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'accountsTable'				=> $this->accountsTable,
			'amazon_markets'			=> WPLA_AmazonMarket::getAll(),
			'amazon_accounts'			=> WPLA_AmazonAccount::getAll( true ),
			'default_account'			=> get_option( 'wpla_default_account_id' ),

			'settings_url'				=> 'admin.php?page='.self::ParentMenuId.'-settings',
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-settings'.'&tab='.$active_tab
		);
		$this->display( 'settings_accounts', $aData );
	}


	public function displayEditAccountsPage() {

	    // get account
	    $account_id = wpla_clean($_REQUEST['amazon_account']);
	    $account = new WPLA_AmazonAccount( $account_id );
	    if ( ! $account ) die('wrong account');

	    $account->allowed_markets = maybe_unserialize( $account->allowed_markets );

		$active_tab = 'accounts';
		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'account'					=> $account,
			'amazon_markets'			=> WPLA_AmazonMarket::getAll(),
			'default_account'			=> get_option( 'wpla_default_account_id' ),

			'settings_url'				=> 'admin.php?page='.self::ParentMenuId.'-settings',
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-settings'.'&tab='.$active_tab
		);
		$this->display( 'account_edit_page', $aData );
	}



	protected function saveAccount() {
		if ( ! current_user_can('manage_amazon_options') ) return;

		// save account
		$account = new WPLA_AmazonAccount( absint($_POST['wpla_account_id']) );
		$account->title          = trim( wpla_clean( $_POST['wpla_title'] ) );
		$account->market_id      = trim( wpla_clean( $_POST['wpla_market_id'] ) );
		$account->merchant_id    = trim( wpla_clean( $_POST['wpla_merchant_id'] ) );
		$account->marketplace_id = trim( wpla_clean( $_POST['wpla_marketplace_id'] ) );
		$account->mws_auth_token = trim( wpla_clean( $_POST['wpla_mws_auth_token'] ) );
		$account->active         = trim( wpla_clean( $_POST['wpla_account_is_active'] ) );
		$account->is_reg_brand   = trim( wpla_clean( $_POST['wpla_account_is_reg_brand'] ) );

		// allow to save secret key, but only once
		if ( $account->secret_key == '' && isset($_POST['wpla_secret_key']) ) {
			$account->secret_key = trim( wpla_clean( $_POST['wpla_secret_key'] ) );
		}

		// remove access key and secret key when auth token is set
		if ( ! empty($account->mws_auth_token) && substr( $account->mws_auth_token, 0, 8 ) == 'amzn.mws' ) {
			$account->access_key_id = '';
			$account->secret_key    = '';
		}

		$account->update();

		$this->showMessage( __( 'Account was updated.', 'wp-lister-for-amazon' ) );
	} // saveAccount()


	protected function newAccount() {

		// make sure all required fields are populated
		if ( empty( $_POST['wpla_merchant_id'] ) ) {
			$this->showMessage( __( 'No Seller ID was provided.', 'wp-lister-for-amazon' ), 1 );
			return;
		}
		if ( empty( $_POST['wpla_marketplace_id'] ) ) {
			$this->showMessage( __( 'No Marketplace ID was provided.', 'wp-lister-for-amazon' ), 1 );
			return;
		}
		if ( empty( $_POST['wpla_access_key_id'] ) && empty( $_POST['wpla_mws_auth_token'] ) ) {
			$this->showMessage( __( 'You need to provide either an MWS Auth Token or an AWS Access Key ID.', 'wp-lister-for-amazon' ), 1 );
			return;
		}

		// create new account
		$account = new WPLA_AmazonAccount();
		$account->title          = trim( wpla_clean( $_POST['wpla_account_title'] ) );
		$account->market_id      = trim( wpla_clean( $_POST['wpla_amazon_market_id'] ) );
		$account->market_code    = trim( wpla_clean( $_POST['wpla_amazon_market_code'] ) );
		$account->merchant_id    = trim( wpla_clean( $_POST['wpla_merchant_id'] ) );
		$account->marketplace_id = trim( wpla_clean( $_POST['wpla_marketplace_id'] ) );
		$account->access_key_id  = trim( wpla_clean( $_POST['wpla_access_key_id'] ) );
		$account->mws_auth_token = trim( wpla_clean( $_POST['wpla_mws_auth_token'] ) );
		$account->secret_key     = wpla_clean( str_replace( ' ', '', $_POST['wpla_secret_key'] ) ); // avoid problems caused by spaces in secret key #23288
		$account->active         = 1;
		$account->add();

		// update allowed markets
		$account->updateMarketplaceParticipations();
		
		$this->showMessage( __( 'New account was added.', 'wp-lister-for-amazon' ) );
	} // newAccount()


} // class WPLA_AccountsPage
