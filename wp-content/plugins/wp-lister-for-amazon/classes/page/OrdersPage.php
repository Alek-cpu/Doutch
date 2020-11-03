<?php
/**
 * WPLA_OrdersPage class
 * 
 */

class WPLA_OrdersPage extends WPLA_Page {

	const slug = 'orders';

	public function onWpInit() {

		// Add custom screen options
		$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".self::slug;
		add_action( $load_action, array( &$this, 'addScreenOptions' ) );

		$this->handleSubmitOnInit();
	}

	public function onWpAdminMenu() {
		parent::onWpAdminMenu();

		add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( 'Orders' ), __( 'Orders', 'wp-lister-for-amazon' ),
						  self::ParentPermissions, $this->getSubmenuId( 'orders' ), array( &$this, 'displayOrdersPage' ) );
	}

	function addScreenOptions() {
		
		// render table options
		$option = 'per_page';
		$args = array(
	    	'label' => 'Orders',
	        'default' => 20,
	        'option' => 'orders_per_page'
	        );
		add_screen_option( $option, $args );
		$this->ordersTable = new WPLA_OrdersTable();
	
	    // add_thickbox();
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

	}
	

	public function handleSubmitOnInit() {
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// handle preview action
		if ( $this->requestAction() == 'view_amazon_order_details' ) {
		    check_admin_referer( 'wpla_view_order_details' );
			$this->showOrderDetails( wpla_clean($_REQUEST['amazon_order']) );
			exit();
		}

	}

	public function handleActions() {
		if ( ! current_user_can('manage_amazon_listings') ) return;
	
		// trigger orders update
		if ( $this->requestAction() == 'update_amazon_orders' ) {
		    check_admin_referer( 'wpla_update_orders' );
			do_action( 'wpla_update_orders' );
		}

		// load order items
		if ( $this->requestAction() == 'load_order_items' ) {
		    check_admin_referer( 'wpla_load_order_items' );

			$lm = new WPLA_OrdersModel();
			$order = $lm->getItem( wpla_clean($_REQUEST['amazon_order']) );
			if ( ! $order ) return;

			$account = WPLA_AmazonAccount::getAccount( $order['account_id'] );
			if ( ! $account ) return;

			$api = new WPLA_AmazonAPI( $account->id );

			// get report requests
			$items = $api->getOrderLineItems( $order['order_id'] );
			// echo "<pre>";print_r($items);echo"</pre>";die();

			if ( is_array( $items ) )  {

				// run the import
				$this->importOrderItems( $items, $order['order_id'] );

				$this->showMessage( sprintf( __( '%s item(s) were processed for account %s.', 'wp-lister-for-amazon' ), sizeof($items), $account->title ) );

			} elseif ( $items->Error->Message ) {
				$this->showMessage( sprintf( __( 'There was a problem downloading items for account %s.', 'wp-lister-for-amazon' ), $account->title ) .'<br>Error: '. $items->Error->Message, 1 );
			} else {
				$this->showMessage( sprintf( __( 'There was a problem downloading items for account %s.', 'wp-lister-for-amazon' ), $account->title ), 1 );
			}

		}

		// handle update from Amazon action
		if ( $this->requestAction() == 'wpla_update_orders' ) {
		    check_admin_referer( 'bulk-orders' );
			$this->updateOrdersfromAmazon( wpla_clean($_REQUEST['amazon_order']) );
			// $this->showMessage( __( 'Not implemented yet.', 'wp-lister-for-amazon' ) );
		}


		// handle delete action
		if ( $this->requestAction() == 'wpla_delete_orders' ) {
		    check_admin_referer( 'bulk-orders' );
			$this->deleteOrders( wpla_clean($_REQUEST['amazon_order']) );
			$this->showMessage( __( 'Selected items were removed.', 'wp-lister-for-amazon' ) );
		}

	}
	

	public function updateOrdersfromAmazon( $orders ) {
		$there_were_errors = false;
		
		$om = new WPLA_OrdersModel();
		foreach ($orders as $id) {
			$success = $om->updateFromAmazon( $id );
			if ( $success == 'RequestThrottled' ) {
				$this->showMessage( sprintf( __( 'Order %s could not be updated because you are sending too many requests per minute to Amazon.<br>Please wait a minute and then try to update a smaller number of orders at the same time.', 'wp-lister-for-amazon' ), $om->lastOrderID ), 1, 1 );
				$there_were_errors = true;
			}
		}

		if ( $there_were_errors ) {
			$this->showMessage( __( 'Some orders could not be updated from Amazon.', 'wp-lister-for-amazon' ), 2 );
		} else {
			$this->showMessage( __( 'Selected orders were updated from Amazon.', 'wp-lister-for-amazon' ) );
		}

	}

	public function deleteOrders( $orders ) {
		
		$om = new WPLA_OrdersModel();
		foreach ($orders as $id) {
			$om->deleteItem( $id );
		}

	}

	
	public function importOrderItems( $items, $order_id ) {
	
		$importer = new WPLA_OrdersImporter();
		$success  = $importer->importOrderItems( $items, $order_id );

	}
	

	public function displayOrdersPage() {
		$this->check_wplister_setup();
	
		// handle actions and show notes
		$this->handleActions();

	    // create table and fetch items to show
	    // $this->ordersTable = new WPLA_OrdersTable();
	    $this->ordersTable->prepare_items();

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'ordersTable'				=> $this->ordersTable,
		
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-orders'
		);
		$this->display( 'orders_page', $aData );

	}

	public function showOrderDetails( $id ) {
	
		// init model
		$ordersModel = new WPLA_OrdersModel();		

		// get amazon_order record
		$amazon_order = $ordersModel->getItem( $id );
		
		// get WooCommerce order
		$wc_order_notes = $amazon_order['post_id'] ? $this->get_order_notes( $amazon_order['post_id'] ) : false;

		$aData = array(
			'amazon_order'				=> $amazon_order,
			'wc_order_notes'			=> $wc_order_notes,
		);
		$this->display( 'order_details', $aData );
		
	}

	public function get_order_notes( $id ) {

		$notes = array();

		$args = array(
			'post_id' => $id,
			'approve' => 'approve',
			'type' => ''
		);

		remove_filter('comments_clauses', 'woocommerce_exclude_order_comments');

		// fix blank details page if WooCommerce Product Reviews Pro plugin is active (Call to undefined function get_current_screen())
		// since we only render the details page and then exit(), it's safe to remove all problematic filters
		remove_all_filters('comments_clauses');
		remove_all_filters('parse_comment_query');

		$comments = get_comments( $args );

		foreach ($comments as $comment) :
			// $is_customer_note = get_comment_meta($comment->comment_ID, 'is_customer_note', true);
			// $comment->comment_content = make_clickable($comment->comment_content);
			$notes[] = $comment;
		endforeach;

		add_filter('comments_clauses', 'woocommerce_exclude_order_comments');

		return (array) $notes;

	}

}
