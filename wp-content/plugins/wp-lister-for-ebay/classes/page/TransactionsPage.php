<?php
/**
 * TransactionsPage class
 * 
 */

class TransactionsPage extends WPL_Page {

	const slug = 'transactions';

	public function onWpInit() {
		// parent::onWpInit();

		// Add custom screen options
		$load_action = "load-".$this->main_admin_menu_slug."_page_wplister-".self::slug;
		add_action( $load_action, array( &$this, 'addScreenOptions' ) );

		// handle actions
		$this->handleActionsOnInit();
	}

	public function onWpAdminMenu() {
		parent::onWpAdminMenu();

		$page = isset( $_GET['page'] ) ? sanitize_key($_GET['page']) : '';
		if ( ( $page != 'wplister-transactions') ) return;

		add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( 'Transactions' ), __( 'Transactions', 'wp-lister-for-ebay' ),
						  'manage_ebay_listings', $this->getSubmenuId( 'transactions' ), array( &$this, 'onDisplayTransactionsPage' ) );
	}

	public function handleActionsOnInit() {
		if ( ! current_user_can('manage_ebay_listings') ) return;

		// these actions have to wait until 'init'
		if ( $this->requestAction() == 'view_trx_details' ) {
		    //check_admin_referer( 'wplister_view_trx_details' );
			$this->showTransactionDetails( wple_clean($_REQUEST['transaction']) );
			exit();
		}


	}

	function addScreenOptions() {
		$option = 'per_page';
		$args = array(
	    	'label' => 'Transactions',
	        'default' => 20,
	        'option' => 'transactions_per_page'
	        );
		add_screen_option( $option, $args );
		$this->transactionsTable = new TransactionsTable();
	
	    // add_thickbox();
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

	}
	


	public function onDisplayTransactionsPage() {
		$this->check_wplister_setup();

		// handle update ALL from eBay action
		if ( $this->requestAction() == 'wple_update_transactions' ) {
		    check_admin_referer( 'wplister_update_transactions' );
			$this->initEC();
			$tm = $this->EC->loadTransactions();
			$this->EC->updateListings();
			$this->EC->closeEbay();

			// show transaction report
			$msg  = $tm->count_total .' '. __( 'Transactions were loaded from eBay.', 'wp-lister-for-ebay' ) . '<br>';
			$msg .= __( 'Timespan', 'wp-lister-for-ebay' ) .': '. $tm->getHtmlTimespan();
			$msg .= '&nbsp;&nbsp;';
			$msg .= '<a href="#" onclick="jQuery(\'#transaction_report\').toggle();return false;">'.__( 'show details', 'wp-lister-for-ebay' ).'</a>';
			$msg .= $tm->getHtmlReport();
			$this->showMessage( $msg );
		}
		// handle update from eBay action
		if ( $this->requestAction() == 'wple_update_transactions' ) {
		    check_admin_referer( 'bulk-transactions' );

			if ( isset( $_REQUEST['transaction'] ) ) {
				$this->initEC();
				$this->EC->updateTransactionsFromEbay( wple_clean($_REQUEST['transaction']) );
				$this->EC->closeEbay();
				$this->showMessage( __( 'Selected transactions were updated from eBay.', 'wp-lister-for-ebay' ) );
			} else {
				$this->showMessage( __( 'You need to select at least one item from the list below in order to use bulk actions.', 'wp-lister-for-ebay' ),1 );
			}
		}
		// handle delete action
		if ( $this->requestAction() == 'wple_delete_transactions' ) {
		    check_admin_referer( 'bulk-transactions' );

			if ( isset( $_REQUEST['transaction'] ) ) {
				$this->initEC();
				$this->EC->deleteTransactions( wple_clean($_REQUEST['transaction']) );
				$this->EC->closeEbay();
				$this->showMessage( __( 'Selected items were removed.', 'wp-lister-for-ebay' ) );
			} else {
				$this->showMessage( __( 'You need to select at least one item from the list below in order to use bulk actions.', 'wp-lister-for-ebay' ),1 );
			}
		}
		// handle wpl_revert_transaction action
		if ( $this->requestAction() == 'wpl_revert_transaction' ) {
		    check_admin_referer( 'wplister_revert_transaction' );

			if ( isset( $_REQUEST['id'] ) ) {
				$tm = new TransactionsModel();
				$tm->revertTransaction( intval($_REQUEST['id']) );
				$this->showMessage( __( 'Selected transaction was reverted.', 'wp-lister-for-ebay' ) );
			} else {
				$this->showMessage( __( 'You need to select at least one item from the list below in order to use bulk actions.', 'wp-lister-for-ebay' ),1 );
			}
		}

		// show warning if duplicate transactions found
		$this->checkForDuplicates();

	    //Create an instance of our package class...
	    $transactionsTable = new TransactionsTable();
    	//Fetch, prepare, sort, and filter our data...
	    $transactionsTable->prepare_items();
		
		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'transactionsTable'			=> $transactionsTable,
			'preview_html'				=> isset($preview_html) ? $preview_html : '',
		
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-transactions'
		);
		$this->display( 'transactions_page', $aData );
		

	}

	public function checkForDuplicates() {

		// show warning if duplicate products found
		$tm = new TransactionsModel();
		$duplicateTransactions = $tm->getAllDuplicateTransactions();
		if ( ! empty($duplicateTransactions) ) {

			// built message
			$msg  = '<p><b>Warning: '.__( 'There are duplicate transactions which should be removed.', 'wp-lister-for-ebay' ).'</b>';
			$msg .= '<br>';

			// table header
			$msg .= '<table style="width:95%">';
			$msg .= "<tr>";
			$msg .= "<th style='text-align:left'>Date</th>";
			$msg .= "<th style='text-align:left'>Transaction ID</th>";
			$msg .= "<th style='text-align:left'>Order ID</th>";
			$msg .= "<th style='text-align:left'>Last modified</th>";
			$msg .= "<th style='text-align:left'>Qty</th>";
			$msg .= "<th style='text-align:left'>eBay ID</th>";
			$msg .= "<th style='text-align:left'>Stock red.</th>";
			$msg .= "<th style='text-align:left'>New Stock</th>";
			$msg .= "<th style='text-align:left'>Status</th>";
			$msg .= "<th style='text-align:left'>&nbsp;</th>";
			$msg .= "</tr>";

			// table rows
			foreach ($duplicateTransactions as $transaction_id) {

				$transactions = $tm->getAllTransactionsByTransactionID( $transaction_id );
				$last_transaction_id = false;
				foreach ($transactions as $txn) {

					// get column data
					$qty     = $txn['quantity'];

					// check if stock was reduced
					list( $reduced_product_id, $new_stock_value ) = $tm->checkIfStockWasReducedForItemID( $txn, $txn['item_id'] );

					// color results
					$color_id = 'silver';
					if ( $transaction_id != $last_transaction_id ) {
						$color_id = 'black';
						$last_transaction_id = $transaction_id;						
					}

					$color_status = 'auto';
					if ( $txn['CompleteStatus'] == 'Completed' ) {
						$color_status = 'darkgreen';
					}
					if ( $txn['CompleteStatus'] == 'Cancelled' ) {
						$color_status = 'silver';
					}

					// built buttons
					$actions = '';
					if ( $txn['status'] != 'reverted' && $txn['CompleteStatus'] != 'Completed' ) {
						$button_label = $reduced_product_id ? 'Restore stock' : 'Remove';
						$url = 'admin.php?page=wplister-transactions&action=wpl_revert_transaction&id='.$txn['id'].'&_wpnonce='. wp_create_nonce( 'wplister_revert_transaction' );
						$actions = '<a href="'.$url.'" class="button button-small">'.$button_label.'</a>';
					}
					if ( $txn['status'] != 'reverted' && $txn['CompleteStatus'] == 'Completed' ) {
						$button_label = 'Remove';
						$url = 'admin.php?page=wplister-transactions&action=wple_delete_transactions&transaction='.$txn['id'].'&_wpnonce='. wp_create_nonce( 'bulk-transactions' );
						$actions = '<a href="'.$url.'" class="button button-small">'.$button_label.'</a>';
					}

					// build table row
					$msg .= "<tr>";
					$msg .= "<td>".$txn['date_created']."</td>";
					$msg .= "<td style='color:$color_id'>".$txn['transaction_id']."</td>";
					$msg .= "<td>".$txn['order_id']."</td>";
					$msg .= "<td>".$txn['LastTimeModified']."</td>";
					$msg .= "<td>".$txn['quantity']."</td>";
					$msg .= "<td>".$txn['item_id']."</td>";
					$msg .= "<td>".$reduced_product_id."</td>";
					$msg .= "<td>".$new_stock_value."</td>";
					$msg .= "<td style='color:$color_status'>".$txn['CompleteStatus']."</td>";
					$msg .= "<td>".$actions."</td>";
					$msg .= "</tr>";

				} // foreach ($transactions as $txn)
				
			} // foreach ($duplicateTransactions as $transaction_id)

			$msg .= '</table>';
			$msg .= '</p>';

			$this->showMessage( $msg, 1 );				
		}
	}


	public function showTransactionDetails( $id ) {
	
		// init model
		$transactionsModel = new TransactionsModel();		

		// get transaction record
		$transaction = $transactionsModel->getItem( $id );
		
		// get auction item record
		$auction_item = ListingsModel::getItemByEbayID( $transaction['item_id'] );
		
		$aData = array(
			'transaction'				=> $transaction,
			'auction_item'				=> $auction_item
		);
		$this->display( 'transaction_details', $aData );
		
	}


} // class TransactionsPage
