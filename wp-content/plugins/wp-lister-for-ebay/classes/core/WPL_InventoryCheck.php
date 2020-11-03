<?php

class WPL_InventoryCheck extends WPL_Model  {

    protected $batch_size;
    protected $data_key;
    protected $oos_products = array(); // out-of-stock products
    protected $published_count = 0;
    protected $mode;
    protected $compare_prices;

    public function __construct( $data_key = 'wple_inventory_check_queue_data' ) {
        $this->data_key         = $data_key;

        $data = $this->getTemporaryData();

        $this->mode             = $data['mode'];
        $this->oos_products     = $data['out_of_sync_products'];
        $this->compare_prices   = $data['compare_prices'];
        $this->published_count  = $data['published_count'];
        $this->batch_size       = get_option( 'wplister_inventory_check_batch_size', 200 );
    }

    public function addToReport( $item ) {
        // get stock level and price
        $item       = (array)$item;
        $stock      = ProductWrapper::getStock( $item['post_id'] );
        $price      = ProductWrapper::getPrice( $item['post_id'] );
        $product    = ProductWrapper::getProduct( $item['post_id'] );

        // apply price modified from profile
        $profile_start_price = ( isset( $profile_details['start_price'] ) && ! empty( $profile_details['start_price'] ) ) ? $profile_details['start_price'] : false ;
        if ( $profile_start_price ) {
            // echo "<pre>price: ";print_r($profile_start_price);echo"</pre>";#die();
        }

        // count products which have not yet been marked as changed
        if ( $item['status'] == 'published' ) $this->published_count += 1;

        // add to list of out of sync products
        $item['price_woo']           = $price;
        $item['price_woo_max']       = isset( $price_max ) ? $price_max : false;
        $item['stock']               = $stock;
        $item['exists']              = $product ? true : false;
        $item['type']                = $product ? wple_get_product_meta( $product, 'product_type' ) : 'missing';
        $item['profile_start_price'] = $profile_start_price;
        array_push( $this->oos_products, $item );

        WPLE()->logger->info( 'Added item #'. $item['post_id'] .' to the OOS list' );
        WPLE()->logger->info( 'List now contains '. count( $this->oos_products ) .' items' );
    }

	// var $batch_size = 200;

	// check_wc_out_of_sync
	public function checkProductInventory( $mode = 'published', $compare_prices = false, $step = 0 ) {

		$batch_size = get_option( 'wplister_inventory_check_batch_size', 200 );
		$limit      = $batch_size;
		$offset     = $batch_size * $step;

		// get listings - or return false
		$lm = new ListingsModel();
		$listings = $mode == 'published' ? WPLE_ListingQueryHelper::getAllPublished( $limit, $offset ) : WPLE_ListingQueryHelper::getAllEnded( $limit, $offset );
		if ( empty($listings) ) return false;

		// delete the queue_data option
		if ( 0 == $step ) {
			$this->reset();
		}

		// process published listings
		foreach ( $listings as $item ) {
		    if ( !$this->checkSync( $item, $compare_prices ) ) {
		        $this->addToReport( $item );

                // mark listing as changed
                if ( isset( $_REQUEST['mark_as_changed'] ) && $_REQUEST['mark_as_changed'] == 'yes' ) {

                    // only existing products can have a profile re-applied
                    $lm->markItemAsModified( $item['post_id'] );

                    // in case the product is locked or missing, force the listing to be changed
                    ListingsModel::updateListing( $item['id'], array( 'status' => 'changed' ) );

                    $item['status'] = 'changed';
                }
            }
		}

		// true means we processed more items
		return true;

	} // checkProductInventory()


	public function showProductInventoryCheckResult( $mode = 'published' ) {

		// restore previous data
		$out_of_sync_products = $this->oos_products;
		$published_count      = $this->published_count;
		$compare_prices       = $this->compare_prices;
		$mode                 = $this->mode;

		// return if empty
		if ( empty( $out_of_sync_products ) ) {
			$this->showMessage('All '.$mode.' listings seem to be in sync.', 0, 1);
			return;
		}

		$msg = '<p>';
		$msg .= 'Warning: '.sizeof($out_of_sync_products).' '.$mode.' listings are out of sync or missing in WooCommerce.';
		$msg .= '</p>';

		// table header
		$msg .= '<table style="width:100%">';
		$msg .= "<tr>";
		$msg .= "<th style='text-align:left'>SKU</th>";
		$msg .= "<th style='text-align:left'>Product</th>";
		$msg .= "<th style='text-align:left'>Local Qty</th>";
		$msg .= "<th style='text-align:left'>eBay Qty</th>";
		$msg .= "<th style='text-align:left'>Local Price</th>";
		$msg .= "<th style='text-align:left'>eBay Price</th>";
		$msg .= "<th style='text-align:left'>eBay ID</th>";
		$msg .= "<th style='text-align:left'>Status</th>";
		$msg .= "</tr>";

		// table rows
		foreach ( $out_of_sync_products as $item ) {
			// echo "<pre>";print_r($item['ebay_id']);echo"</pre>";#die();

			// get column data
			$qty          = $item['qty'];
			$sku          = get_post_meta( $item['post_id'], '_sku', true );
			$stock        = $item['stock'];
			$title        = $item['auction_title'];
			$post_id      = $item['post_id'];
			$ebay_id      = $item['ebay_id'];
			$status       = $item['status'];
			$exists       = $item['exists'];
			$locked       = $item['locked'] ? 'locked' : '';
			$price        = wc_price( $item['price'] );
			$price_woo    = wc_price( $item['price_woo'] );
			$product_type = $item['type'] == 'simple' ? '' : $item['type'];

			// highlight changed values
			$changed_stock     =   intval( $item['qty']   )     ==   intval( $item['stock']     )     ? false : true;
			$changed_price     = floatval( $item['price'] )     == floatval( $item['price_woo'] )     ? false : true;
			$changed_price_max = floatval(@$item['price_max'] ) == floatval( $item['price_woo_max'] ) ? false : true;
			$stock_css         = $changed_stock                       ? 'color:darkred; font-weight:bold;' : '';
			$price_css         = $changed_price || $changed_price_max ? 'color:darkred;'                   : '';
			if ( ! $compare_prices ) $price_css = '';

			// build links
			$ebay_url = $item['ViewItemURL'] ? $item['ViewItemURL'] : $ebay_url = 'http://www.ebay.com/itm/'.$ebay_id;
			$ebay_link = '<a href="'.$ebay_url.'" target="_blank">'.$ebay_id.'</a>';
			$edit_link = '<a href="post.php?action=edit&post='.$post_id.'" target="_blank">'.$title.'</a>';

			// mark non existent products
			if ( ! $exists ) {
				$stock    = 'N/A';
				$post_id .= ' missing!';
			}

			// show price range for variations
			if ( $item['price_woo_max'] )
				$price_woo .= ' - '.wc_price( $item['price_woo_max'] );
			if ( @$item['price_max'] )
				$price .= ' - '.wc_price( $item['price_max'] );

			if ( $item['profile_start_price'] )
				$price .= ' ('. $item['profile_start_price'] .')';

			// build table row
			$msg .= "<tr>";
			$msg .= "<td>$sku</td>";
			$msg .= "<td>$edit_link <span style='color:silver'>$locked $product_type (#$post_id)</span></td>";
			$msg .= "<td style='$stock_css'>$stock</td>";
			$msg .= "<td style='$stock_css'>$qty</td>";
			$msg .= "<td style='$price_css'>$price_woo</td>";
			$msg .= "<td style='$price_css'>$price</td>";
			$msg .= "<td>$ebay_link</td>";
			$msg .= "<td>$status</td>";
			$msg .= "</tr>";
		}
		$msg .= '</table>';

		// buttons
		$msg .= '<p>';

		// show 'check again' button
		$url  = 'admin.php?page=wplister-tools&action=check_wc_out_of_sync&mode='.$mode.'&prices='.$compare_prices.'&_wpnonce='.wp_create_nonce('e2e_tools_page');
		$msg .= '<a href="'.$url.'" class="button">'.__( 'Check again', 'wp-lister-for-ebay' ).'</a> &nbsp; ';

		// show 'mark all as changed' button
		if ( $mode == 'published' )
		if ( $published_count ) {
			$url = 'admin.php?page=wplister-tools&action=check_wc_out_of_sync&mode='.$mode.'&prices='.$compare_prices.'&mark_as_changed=yes&_wpnonce='.wp_create_nonce('e2e_tools_page');
			$msg .= '<a href="'.$url.'" class="button">'.__( 'Mark all as changed', 'wp-lister-for-ebay' ).'</a> &nbsp; ';
			$msg .= 'Click this button to mark all found listings as changed in WP-Lister, then revise all changed listings.';
		} else {
			$msg .= '<a id="btn_revise_all_changed_items_reminder" class="btn_revise_all_changed_items_reminder button wpl_job_button">' . __( 'Revise all changed items', 'wp-lister-for-ebay' ) . '</a>';
			$msg .= ' &nbsp; ';
			// $msg .= 'Click to revise all changed items. If there are still unsynced items after revising, you might have to reapply the listing profile.';
			$msg .= 'Click to revise all changed items.';
		}
		$msg .= '</p>';

		wple_show_message( $msg, 'warn' );
		$this->reset();

	} // showProductInventoryCheckResult()

	// check_wc_out_of_stock
	public function checkProductStock( $step = 0 ) {

		$batch_size = get_option( 'wplister_inventory_check_batch_size', 200 );
		$limit      = $batch_size;
		$offset     = $batch_size * $step;

		// get listings - or return false
		$listings = WPLE_ListingQueryHelper::getAllPublished( $limit, $offset );
		if ( empty($listings) ) return false;

		// process published listings
		foreach ( $listings as $item ) {

			// get wc product
			$_product = ProductWrapper::getProduct( $item['post_id'] );

			// check stock level
			// $stock = ProductWrapper::getStock( $item['post_id'] );
            $stock = 0;

            if ( $_product ) {
                $stock = ProductWrapper::getStock( $_product, true );
            }

			if ( $stock > 0 )
				continue;

			// mark listing as changed
			if ( isset( $_REQUEST['mark_as_changed'] ) && $_REQUEST['mark_as_changed'] == 'yes' ) {
				ListingsModel::updateListing( $item['id'], array( 'status' => 'changed' ) );
				$item['status'] = 'changed';
			}

			// add to list of out of stock products
			$item['stock']  = $stock;
			$item['exists'] = $_product ? true : false;
			$this->addToReport( $item );

		}

		// true means we processed more items
		return true;

	} // checkProductStock()



	public function showProductStockCheckResult( $mode = 'out_of_stock' ) {

		// restore previous data
		$out_of_stock_products = $this->oos_products;

		// return if empty
		if ( empty( $out_of_stock_products ) ) {
			$this->showMessage('No out of stock products found.', 0, 1);
			return;
		}

		$msg = '<p>';
		$msg .= 'Warning: Some published listings are out of stock or missing in WooCommerce.';
		$msg .= '</p>';

		// table header
		$msg .= '<table style="width:100%">';
		$msg .= "<tr>";
		$msg .= "<th style='text-align:left'>Stock</th>";
		$msg .= "<th style='text-align:left'>SKU</th>";
		$msg .= "<th style='text-align:left'>Product</th>";
		$msg .= "<th style='text-align:left'>Qty</th>";
		$msg .= "<th style='text-align:left'>eBay ID</th>";
		$msg .= "<th style='text-align:left'>Status</th>";
		$msg .= "</tr>";

		// table rows
		foreach ( $out_of_stock_products as $item ) {

			// get column data
			$sku     = get_post_meta( $item['post_id'], '_sku', true );
			$qty     = $item['quantity'];
			$stock   = $item['stock'] . ' x ';
			$title   = $item['auction_title'];
			$post_id = $item['post_id'];
			$ebay_id = $item['ebay_id'];
			$status  = $item['status'];
			$exists  = $item['exists'];

			// build links
			$ebay_url = $item['ViewItemURL'] ? $item['ViewItemURL'] : $ebay_url = 'http://www.ebay.com/itm/'.$ebay_id;
			$ebay_link = '<a href="'.$ebay_url.'" target="_blank">'.$ebay_id.'</a>';
			$edit_link = '<a href="post.php?action=edit&post='.$post_id.'" target="_blank">'.$title.'</a>';

			// mark non existent products
			if ( ! $exists ) {
				$stock    = 'N/A';
				$post_id .= ' missing!';
			}

			// build table row
			$msg .= "<tr>";
			$msg .= "<td>$stock</td>";
			$msg .= "<td>$sku</td>";
			$msg .= "<td>$edit_link (ID $post_id)</td>";
			$msg .= "<td>$qty x </td>";
			$msg .= "<td>$ebay_link</td>";
			$msg .= "<td>$status</td>";
			$msg .= "</tr>";
		}
		$msg .= '</table>';


		$msg .= '<p>';
		$url = 'admin.php?page=wplister-tools&action=check_wc_out_of_stock&mark_as_changed=yes&_wpnonce='.wp_create_nonce('e2e_tools_page');
		$msg .= '<a href="'.$url.'" class="button">'.__( 'Mark all as changed', 'wp-lister-for-ebay' ).'</a> &nbsp; ';
		$msg .= 'Click this button to mark all found listings as changed in WP-Lister, then revise all changed listings.';
		$msg .= '</p>';

		wple_show_message( $msg, 'warn' );
        $this->reset();
	} // showProductStockCheckResult()

	// check_wc_sold_stock
	public function checkSoldStock() {

		// get all sold listings
		$listings = WPLE_ListingQueryHelper::getAllWithStatus('sold');
		$out_of_stock_products = array();

		// process published listings
		foreach ( $listings as $item ) {

			// get wc product
			$_product = ProductWrapper::getProduct( $item['post_id'] );

			// check stock level
			// $stock = ProductWrapper::getStock( $item['post_id'] );
            $stock = 0;

            if ( $_product ) {
                $stock = ProductWrapper::getStock( $_product, true );
            }

			if ( $stock == 0 )
				continue;

			// mark listing as changed
			// if ( isset( $_REQUEST['mark_as_changed'] ) && $_REQUEST['mark_as_changed'] == 'yes' ) {
			// 	ListingsModel::updateListing( $item['id'], array( 'status' => 'changed' ) );
			// 	$item['status'] = 'changed';
			// }

			// add to list of out of stock products
			$item['stock']  = $stock;
			$item['exists'] = $_product ? true : false;
			$out_of_stock_products[] = $item;

		}

		// return if empty
		if ( empty( $out_of_stock_products ) ) {
			$this->showMessage('No sold products have stock in WooCommerce.', 0, 1);
			return;
		}

		$msg = '<p>';
		$msg .= 'Warning: Some sold listings are still in stock in WooCommerce.';
		$msg .= '</p>';

		// table header
		$msg .= '<table style="width:100%">';
		$msg .= "<tr>";
		$msg .= "<th style='text-align:left'>Stock</th>";
		$msg .= "<th style='text-align:left'>SKU</th>";
		$msg .= "<th style='text-align:left'>Product</th>";
		$msg .= "<th style='text-align:left'>Qty</th>";
		$msg .= "<th style='text-align:left'>eBay ID</th>";
		$msg .= "<th style='text-align:left'>Ended at</th>";
		$msg .= "<th style='text-align:left'>Status</th>";
		$msg .= "</tr>";

		// table rows
		foreach ( $out_of_stock_products as $item ) {

			// get column data
			$qty     = $item['quantity'] - $item['quantity_sold'];
			$sku     = get_post_meta( $item['post_id'], '_sku', true );
			$stock   = $item['stock'] . ' x ';
			$title   = $item['auction_title'];
			$post_id = $item['post_id'];
			$ebay_id = $item['ebay_id'];
			$status  = $item['status'];
			$exists  = $item['exists'];
			$date_ended = $item['date_finished'] ? $item['date_finished'] : $item['end_date'];

			// build links
			$ebay_url = $item['ViewItemURL'] ? $item['ViewItemURL'] : $ebay_url = 'http://www.ebay.com/itm/'.$ebay_id;
			$ebay_link = '<a href="'.$ebay_url.'" target="_blank">'.$ebay_id.'</a>';
			$edit_link = '<a href="post.php?action=edit&post='.$post_id.'" target="_blank">'.$title.'</a>';

			// mark non existent products
			if ( ! $exists ) {
				$stock    = 'N/A';
				$post_id .= ' missing!';
			}

			// build table row
			$msg .= "<tr>";
			$msg .= "<td>$stock</td>";
			$msg .= "<td>$sku</td>";
			$msg .= "<td>$edit_link (ID $post_id)</td>";
			$msg .= "<td>$qty x </td>";
			$msg .= "<td>$ebay_link</td>";
			$msg .= "<td>$date_ended</td>";
			$msg .= "<td>$status</td>";
			$msg .= "</tr>";
		}
		$msg .= '</table>';

		// show 'check again' button
		$msg .= '<p>';
		$url  = 'admin.php?page=wplister-tools&action=check_wc_sold_stock&_wpnonce='.wp_create_nonce('e2e_tools_page');
		$msg .= '<a href="'.$url.'" class="button">'.__( 'Check again', 'wp-lister-for-ebay' ).'</a> &nbsp; ';
		$msg .= '</p>';

		// $msg .= '<p>';
		// $url = 'admin.php?page=wplister-tools&action=check_wc_out_of_stock&mark_as_changed=yes&_wpnonce='.wp_create_nonce('e2e_tools_page');
		// $msg .= '<a href="'.$url.'" class="button">'.__( 'Mark all as changed', 'wp-lister-for-ebay' ).'</a> &nbsp; ';
		// $msg .= 'Click this button to mark all found listings as changed in WP-Lister, then revise all changed listings.';
		// $msg .= '</p>';

		wple_show_message( $msg, 'warn' );

	} // checkSoldStock()



	// check_wc_stock_reduction_mismatch (single)
	static function checkThisOrderForStockReductionsMismatch( $order_id, $include_line_items_in_check = false ) {

		// get wpl order
		$wpl_order = EbayOrdersModel::getItem( $order_id );

		// skip Active orders
		if ( 'Active' == $wpl_order['CompleteStatus'] ) return false;

        // count purchased items
        $items      = maybe_unserialize( $wpl_order['items'] );
        $item_count = 0;
        $line_items = 0;
        $qty_index  = array();
        if ( is_array($items) ) {
            foreach ($items as $line_item) {
				$ebay_id     = $line_item['item_id'];
				$item_count += $line_item['quantity'];
				$line_items += 1;
				// accumulate qty for mutliple line items of the same eBay ID (variations)
                $qty_index[ $ebay_id ] = isset( $qty_index[ $ebay_id ] ) ? $qty_index[ $ebay_id ] + $line_item['quantity'] : $line_item['quantity'];
            }
        }

        // check if WP-Lister did reduce stock for purchased items
        $history = maybe_unserialize( $wpl_order['history'] );
        $history = is_array( $history ) ? $history : array();

		$units_purchased      = $item_count;
		$stock_units_reduced  = 0;
		$skipped_items        = 0;
		$skipped_transactions = 0;
		$recorded_line_items  = 0;
		$line_items_mismatch  = false;

        // loop over history records
        foreach ( $history as $record ) {

        	// count actually reduced stock units
            if ( $record->action == 'reduce_stock' ) {
                if ( isset( $record->details['product_id'] ) && isset( $record->details['quantity_purchased'] ) ) {
                    $stock_units_reduced += $record->details['quantity_purchased'];
                }
            }

            // count skipped items using look up table ($qty_index array)
            if ( $record->action == 'skipped_item' ) {
                if ( isset( $record->details['ebay_id'] ) && isset( $qty_index[ $record->details['ebay_id'] ] ) ) {
                    //$stock_units_reduced += $qty_index[ $record->details['ebay_id'] ]; // Do no to increase stock_units_reduced on skipped items #37869
                    $qty_index[ $record->details['ebay_id'] ] = 0; // reset qty index for variations (same eBay ID)
                    $skipped_items++;
                }
            }

            // count skipped transactions by fetching their QuantityPurchased
            if ( $record->action == 'skipped_transaction' ) {
				$transaction_id = str_replace('Skipped already processed transaction ', '', $record->msg );
				$transaction = TransactionsModel::getTransactionByTransactionID( $transaction_id );
				if ( $transaction ) {
					$quantity             = $transaction['quantity'];
					$stock_units_reduced += $quantity;
				}
				$skipped_transactions++;
            }

        	// compare actual number of line items with recorded line items
            if ( $record->action == 'new_order' ) {
                if ( isset( $record->details['item_count'] ) ) {
                    $recorded_line_items = $record->details['item_count'];
                }
                if ( isset( $record->details['line_item_count'] ) ) {
                    $recorded_line_items = $record->details['line_item_count'];
                }
            }

        } // each history record

        // check for line items mismatch
        if ( $recorded_line_items > 0 && $recorded_line_items != $line_items ) {
        	$line_items_mismatch = true;
        }
        // if we are not supposed to check line items, then ignore check result
        if ( ! $include_line_items_in_check ) $line_items_mismatch = false;

        // skip if units are equal
        if ( $units_purchased == $stock_units_reduced  &&  ! $line_items_mismatch ) {
        	return false;
        }

        // skip orders with skipped items (todo: count skipped items)
        // if ( $skipped_items != 0 ) {
        // if ( $units_purchased == $stock_units_reduced + $skipped_items ) {
        // 	return false;
        // }

        // // skip empty orders (?)
        // if ( $units_purchased == 0 ) {
        // 	return false;
        // }

		// return mismatching order - with calculated meta data
		$wpl_order['units_purchased']      = $units_purchased;
		$wpl_order['stock_units_reduced']  = $stock_units_reduced;
		$wpl_order['skipped_items']        = $skipped_items;
		$wpl_order['skipped_transactions'] = $skipped_transactions;
		$wpl_order['actual_line_items']    = $line_items;
		$wpl_order['recorded_line_items']  = $recorded_line_items;
		$wpl_order['line_items_mismatch']  = $line_items_mismatch;

		return $wpl_order;

	} // checkThisOrderForStockReductionsMismatch()



	// check_wc_stock_reduction_mismatch (loop)
	static function checkOrdersForStockReductionsMismatch() {

		// get all completed orders
		$order_ids    = EbayOrdersModel::getAllIdsWithStatus('Completed');
		$found_orders = array();

		// process published orders
		foreach ( $order_ids as $order_id ) {

			$wpl_order = self::checkThisOrderForStockReductionsMismatch( $order_id, true );
			if ( ! $wpl_order ) continue;

			// add to list of mismatching orders
			$found_orders[] = $wpl_order;

		} // foreach $order_ids

		// return if empty
		if ( empty( $found_orders ) ) {
		    wple_show_message( 'No matching orders were found, that is good.', 'info', false );
			return;
		}

		$msg = '<p>';
		$msg .= 'Warning: '.sizeof($found_orders).' order(s) were found where the total number of purchased units does not match the total number of processed stock units:';
		$msg .= '</p>';

		// table header
		$msg .= '<table style="width:100%">';
		$msg .= "<tr>";
		$msg .= "<th style='text-align:left' >Date</th>";
		$msg .= "<th style='text-align:right;padding-right:1em;'>WooCommerce Order</th>";
		$msg .= "<th style='text-align:left' >eBay Order</th>";
		$msg .= "<th style='text-align:right'>Purchased units</th>";
		$msg .= "<th style='text-align:right'>Processed units</th>";
		$msg .= "<th style='text-align:right'>Skipped items / transactions</th>";
		$msg .= "<th style='text-align:right'>Line items count</th>";
		$msg .= "</tr>";

		// table rows
		foreach ( $found_orders as $item ) {

			// get column data
			$date_created         = $item['date_created'];
			$total                = $item['total'];
			$units_purchased      = $item['units_purchased'];
			$stock_units_reduced  = $item['stock_units_reduced'];
			$skipped_items        = $item['skipped_items'];
			$skipped_transactions = $item['skipped_transactions'];
			$actual_line_items    = $item['actual_line_items'];
			$recorded_line_items  = $item['recorded_line_items'];
			$line_items_mismatch  = $item['line_items_mismatch'];
			$ebay_order_id        = $item['order_id'];
			$wc_order_id          = $item['post_id'];
			$total                = $item['total'];
			$buyer_name           = $item['buyer_name'];
			$buyer_userid         = $item['buyer_userid'];

			// build links
			$ebay_order_url  = 'admin.php?page=wplister-orders&s='.$ebay_order_id;
			$ebay_order_link = '<a href="'.$ebay_order_url.'" target="_blank">'.$ebay_order_id.'</a>';
			$edit_order_link = $wc_order_id ? '<a href="post.php?action=edit&post='.$wc_order_id.'" target="_blank">#'.$wc_order_id.'</a>' : '&mdash;';

			// empty values are gray
			$skipped_items        = $skipped_items        ? $skipped_items        : '<span style="color:silver">0</span>';
			$skipped_transactions = $skipped_transactions ? $skipped_transactions : '<span style="color:silver">0</span>';
			$recorded_line_items  = $recorded_line_items  ? $recorded_line_items  : '<span style="color:silver">-</span>';
			$actual_line_items    = !$line_items_mismatch ? $actual_line_items    : '<span style="color:darkred">'.$actual_line_items.'</span>';

			// build table row
			$msg .= "<tr>";
			$msg .= "<td style='text-align:left' >$date_created</td>";
			$msg .= "<td style='text-align:right;padding-right:1em;'>$edit_order_link <br> <span style='color:silver'>$buyer_name</span></td>";
			$msg .= "<td style='text-align:left' >$ebay_order_link <br> <span style='color:silver'>$buyer_userid</span></td>";
			$msg .= "<td style='text-align:right'>$units_purchased</td>";
			$msg .= "<td style='text-align:right'>$stock_units_reduced</td>";
			$msg .= "<td style='text-align:right'>$skipped_items <span style='color:silver'>/</span> $skipped_transactions</td>";
			$msg .= "<td style='text-align:right'>$actual_line_items <span style='color:silver'>/</span> $recorded_line_items</td>";
			$msg .= "</tr>";
		}
		$msg .= '</table>';

		// show 'check again' button
		$msg .= '<p>';
		$url  = 'admin.php?page=wplister-tools&action=check_wc_stock_reduction_mismatch&_wpnonce='.wp_create_nonce('e2e_tools_page');
		$msg .= '<a href="'.$url.'" class="button">'.__( 'Check again', 'wp-lister-for-ebay' ).'</a> &nbsp; ';
		$msg .= '</p>';

		wple_show_message( $msg, 'warn' );

	} // checkOrdersForStockReductionsMismatch()

    protected function getTemporaryData() {
        $data = get_option( $this->data_key, false );

        if ( ! $data ) {
            // ensure we return an array with these default keys
            $data = array(
                'mode'                  => 'published',
                'compare_prices'        => false,
                'out_of_sync_products'  => array(),
                'published_count'       => 0,
            );
        }

        return $data;
    }

    protected function saveTemporaryData() {
        $data = array(
            'mode'                  => $this->mode,
            'compare_prices'        => $this->compare_prices,
            'out_of_sync_products'  => $this->oos_products,
            'published_count'       => $this->published_count,
        );
        update_option( $this->data_key, $data, false );
    }

    public function reset() {
        $this->mode             = 'published';
        $this->compare_prices   = false;
        $this->oos_products     = array();
        $this->published_count  = 0;

        $this->saveTemporaryData();
    }

    protected function checkSync( &$item, $compare_prices = false ) {
        $out_of_stock_threshold = false;
        if ( get_option( 'wplister_enable_out_of_stock_threshold' ) ) {
            $out_of_stock_threshold = get_option( 'woocommerce_notify_no_stock_amount' );
        }

        // check wc product
        $post_id = $item['post_id'];
        $_product = ProductWrapper::getProduct( $post_id );
        // echo "<pre>";print_r($_product);echo"</pre>";die();


        // get stock level and price
        $stock = ProductWrapper::getStock( $item['post_id'], true );
        $price = ProductWrapper::getPrice( $item['post_id'] );
        // $item['price_max'] = $price;
        // echo "<pre>";print_r($price);echo"</pre>";#die();
        // echo "<pre>";print_r($item);echo"</pre>";die();

        // apply profile settings to stock level
        $profile_data    = ListingsModel::decodeObject( $item['profile_data'], true );
        $profile_details = $profile_data['details'];
        $item['qty']     = $item['quantity'] - $item['quantity_sold'];
        // echo "<pre>";print_r($profile_details);echo"</pre>";#die();

        // If the out-of-stock threshold is enabled, add it to the current ebay qty to get the real value
        if ( $out_of_stock_threshold ) {
            $item['qty'] += $out_of_stock_threshold;
        }

        // apply max_quantity from profile
        $max_quantity = ( isset( $profile_details['max_quantity'] ) && intval( $profile_details['max_quantity'] )  > 0 ) ? $profile_details['max_quantity'] : false ;
        if ( $max_quantity )
            $stock = min( $max_quantity, intval( $stock ) );

        // apply price modified from profile
        $profile_start_price = ( isset( $profile_details['start_price'] ) && ! empty( $profile_details['start_price'] ) ) ? $profile_details['start_price'] : false ;
        if ( $profile_start_price ) {
            // echo "<pre>price: ";print_r($profile_start_price);echo"</pre>";#die();
        }


        // check if product has variations
        if ( $_product ) {
            $variations = wple_get_product_meta( $_product, 'product_type' ) == 'variable' ? ProductWrapper::getVariations( $item['post_id'] ) : array();
        } else {
            $variations = array();
        }

        // get total stock for all variations
        if ( ! empty( $variations ) ) {

            // reset prices and stock
            $stock          = 0;
            $price_min      = PHP_INT_MAX;
            $price_max      = 0;
            $ebay_stock     = 0;
            $ebay_price_min = PHP_INT_MAX;
            $ebay_price_max = 0;

            // check WooCommerce variations
            foreach ($variations as $var) {

                // total stock
                if ( $max_quantity )
                    $stock += min( $max_quantity, intval( $var['stock'] ) );
                else
                    $stock += $var['stock'];

                // min / max prices
                $price_min = min( $price_min, $var['price'] );
                $price_max = max( $price_max, $var['price'] );

            }

            // check eBay variations
            $cached_variations = maybe_unserialize( $item['variations'] );
            if ( is_array($cached_variations) )
                foreach ($cached_variations as $var) {
                    $ebay_stock    += $var['stock'];
                    $ebay_price_min = min( $ebay_price_min, $var['price'] );
                    $ebay_price_max = max( $ebay_price_max, $var['price'] );
                }

            // set default values
            $item['qty']       = $ebay_stock;
            $item['price']     = $ebay_price_min != PHP_INT_MAX ? $ebay_price_min : 0;
            $item['price_max'] = $ebay_price_max;
            // echo "<pre>";print_r($cached_variations);echo"</pre>";die();

        } else {

            $price_min      = false;
            $price_max      = false;
            $ebay_price_min = false;
            $ebay_price_max = false;

        }

        // check stock level
        if ( $stock != $item['qty'] )
            return false;

        // check price
        if ( $compare_prices ) {

            $price_to_compare = $price;
            if ( $profile_start_price ) {
                $price_to_compare = ListingsModel::applyProfilePrice( $price, $profile_start_price );
            }
            if ( round( $price_to_compare, 2 ) != round( $item['price'], 2 ) )
                return false;

            // check max price
            if ( isset( $price_max ) && isset( $item['price_max'] ) && ( round( $price_max, 2 ) != round ( $item['price_max'], 2 ) ) )
                return false;

        }

        // Item is in sync
        return true;
    }



} // class WPL_InventoryCheck
