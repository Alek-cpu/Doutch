<?php
/**
 * WPLE_eBayAccount class
 *
 */

// class WPLE_eBayAccount extends WPLE_NewModel {
class WPLE_eBayAccount extends WPL_Core {

	const TABLENAME = 'ebay_accounts';

	var $id;
	var $title;
	var $site_id;
	var $site_code;

	function __construct( $id = null ) {
		
		$this->init();

		if ( $id ) {
			$this->id = $id;
			
			$account = $this->getAccount( $id );
			if ( ! $account ) return false; // this doesn't actually return an empty object - why?

			// load data into object		
			foreach( $account AS $key => $value ){
			    $this->$key = $value;
			}

			return $this;
		}

	}

	function init()	{

		$this->fieldnames = array(
			'title',
			'site_id',
			'site_code',
			'active',
			'sandbox_mode',
			'token',
			'user_name',
			'user_details',
			'valid_until',
			'ebay_motors',
			'oosc_mode',
			'seller_profiles',
			'shipping_profiles',
			'payment_profiles',
			'return_profiles',
			'shipping_discount_profiles',
			'categories_map_ebay',
			'categories_map_store',
			'default_ebay_category_id',
			'paypal_email',
			'sync_orders',
			'sync_products',
			'last_orders_sync',
		);

	}

	// get single account
	static function getAccount( $id )	{
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		
        $item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE id = %d
		", $id
		), OBJECT);

		return $item;
	}

	// get all accounts
	static function getAll( $include_inactive = false, $sort_by_id = false ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		// return if DB has not been initialized yet
		if ( get_option('wplister_db_version') < 37 ) return array();

		$where_sql = $include_inactive ? '' : 'WHERE active = 1';
		$order_sql = $sort_by_id       ? '' : 'ORDER BY title ASC';
		$items = $wpdb->get_results("
			SELECT *
			FROM $table
			$where_sql
			$order_sql
		", OBJECT_K);

		return $items;
	}

	// get account title
	static function getAccountTitle( $id )	{
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		
		$account_title = $wpdb->get_var( $wpdb->prepare("
			SELECT title
			FROM $table
			WHERE id = %d
		", $id ) );
		return $account_title;
	}

	static function getSiteCode( $id ) {
	    global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;

        $code = $wpdb->get_var( $wpdb->prepare("
			SELECT site_code
			FROM $table
			WHERE id = %d
		", $id ) );
        return $code;
    }

    static function getAccountLocale( $id ) {
	    $site_code = self::getSiteCode( $id );

	    switch ( strtolower( $site_code ) ) {
            case 'germany':
                $lang = 'de';
                break;

            case 'france':
                $lang = 'fr';
                break;

            case 'italy':
                $lang = 'it';
                break;

            case 'spain':
                $lang = 'es';
                break;

            case 'netherlands':
                $lang = 'nl';
                break;

            default:
                $lang = 'en';
                break;
        }

        return apply_filters( 'wple_account_locale', $lang, $id );
    }

	// get this account's site
	function getSite()	{
		// return WPLA_AmazonSite::getSite( $this->site_id );
	}

	// save account
	function add() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$data = array();
		$data['user_details']               = ''; // fix rare "Field 'user_details' doesn't have a default value" error on some MySQL servers
		$data['shipping_profiles']          = '';
		$data['payment_profiles']           = '';
		$data['return_profiles']            = '';
		$data['shipping_discount_profiles'] = '';
		$data['categories_map_ebay']        = '';
		$data['categories_map_store']       = '';
		
		foreach ( $this->fieldnames as $key ) {
			if ( isset( $this->$key ) ) {
				$data[ $key ] = $this->$key;
			} 
		}

		if ( sizeof( $data ) > 0 ) {
			$result = $wpdb->insert( $table, $data );

			if ( ! $wpdb->insert_id ) {
				wple_show_message( 'There was a problem adding your account. MySQL said: '.$wpdb->last_error, 'error' );
			}

			$this->id = $wpdb->insert_id;
			return $wpdb->insert_id;		
		}

	}

	// update feed
	function update() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		if ( ! $this->id ) return;

		$data = array();
		foreach ( $this->fieldnames as $key ) {
			if ( isset( $this->$key ) ) {
				$data[ $key ] = $this->$key;
			} 
		}

		if ( sizeof( $data ) > 0 ) {
			$result = $wpdb->update( $table, $data, array( 'id' => $this->id ) );
			echo $wpdb->last_error;
			// echo "<pre>";print_r($wpdb->last_query);echo"</pre>";#die();
			// return $wpdb->insert_id;		
		}

	}

    function updateUserDetails() {
        // update token expiration date
        $this->initEC( $this->id );

	    $this->getUserToken( $this->EC );
	    $this->getUserDetails( $this->EC );
	    $this->getUserPreferences( $this->EC );
    }

	function getUserToken( $EC = null ) {
		if ( ! $this->id ) return;

		// update token expiration date
        if ( is_null( $EC ) ) {
            $this->initEC( $this->id );
            $EC = $this->EC;
        }

        $EC->initLogger();
		$expdate = $EC->GetTokenStatus( true );
		$EC->closeEbay();
		if ( $expdate ) {
			$this->valid_until = $expdate;
			$this->update();
			update_option( 'wplister_ebay_token_is_invalid', false );
		}

	} // updateUserDetails()

    function getUserDetails( $EC = null ) {
        if ( ! $this->id ) return;

        // update user details
        if ( is_null( $EC ) ) {
            $this->initEC( $this->id );
            $EC = $this->EC;
        }
        $EC->initLogger();
        $user_details = $EC->GetUser( true );
        $EC->closeEbay();
        if ( $user_details ) {
            $this->user_name 	= $user_details->UserID;
            $this->user_details = maybe_serialize( $user_details );
            if ( $this->title == 'My Account' ) {
                $this->title    = $user_details->UserID; // use UserID as default title for new accounts
            }
            $this->update();
        }
    }

    function getUserPreferences( $EC = null ) {
        if ( ! $this->id ) return;

        // update seller profiles
        if ( is_null( $EC ) ) {
            $this->initEC( $this->id );
            $EC = $this->EC;
        }
        $EC->initLogger();
        $result = $EC->GetUserPreferences( true );
        $EC->closeEbay();
        if ( $result ) {
            $this->oosc_mode         = $result->OutOfStockControl    ? 1 : 0;
            $this->seller_profiles   = $result->SellerProfileOptedIn ? 1 : 0;
            $this->shipping_profiles = maybe_serialize( $result->seller_shipping_profiles );
            $this->payment_profiles  = maybe_serialize( $result->seller_payment_profiles );
            $this->return_profiles   = maybe_serialize( $result->seller_return_profiles );
            $this->update();
        }
    }

	// delete account
	function delete() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		if ( ! $this->id ) return;

		$result = $wpdb->delete( $table, array( 'id' => $this->id ) );
		echo $wpdb->last_error;
	}

	function getPageItems( $current_page, $per_page ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

        $orderby  = (!empty($_REQUEST['orderby'])) ? esc_sql( $_REQUEST['orderby'] ) : 'title';
        $order    = (!empty($_REQUEST['order']))   ? esc_sql( $_REQUEST['order']   ) : 'asc';
        $offset   = ( $current_page - 1 ) * $per_page;
        $per_page = esc_sql( $per_page );

        // get items
		$items = $wpdb->get_results("
			SELECT *
			FROM $table
            ORDER BY active desc, $orderby $order
            LIMIT $offset, $per_page
		", ARRAY_A);

		// get total items count - if needed
		if ( ( $current_page == 1 ) && ( count( $items ) < $per_page ) ) {
			$this->total_items = count( $items );
		} else {
			$this->total_items = $wpdb->get_var("
				SELECT COUNT(*)
				FROM $table
				ORDER BY $orderby $order
			");			
		}

		foreach( $items as &$account ) {
			// $account['ReportTypeName'] = $this->getRecordTypeName( $account['ReportType'] );
		}

		return $items;
	} // getPageItems()


} // WPLE_eBayAccount()
