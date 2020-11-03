<?php

class WPLA_InventoryCheck extends WPLA_Model  {

    protected $last_product_id         = 0;
    protected $last_product_object     = array();
    protected $last_profile_id         = 0;
    protected $last_profile_object     = array();

    protected $batch_size;
    private $data_key;
    private $oos_products = array(); // out-of-stock products
    private $published_count = 0;
    private $mode;
    private $compare_prices;

    public function __construct( $data_key = 'wpla_inventory_check_queue_data' ) {
        $this->data_key         = $data_key;

        $data = $this->getTemporaryData();

        $this->mode             = $data['mode'];
        $this->oos_products     = $data['out_of_sync_products'];
        $this->compare_prices   = $data['compare_prices'];
        $this->published_count  = $data['published_count'];
        $this->batch_size       = get_option( 'wpla_inventory_check_batch_size', 200 );
    }

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
        update_option( $this->data_key, $data );
    }

    protected function resetData() {
        $this->mode             = 'published';
        $this->compare_prices   = false;
        $this->oos_products     = array();
        $this->published_count  = 0;

        $this->saveTemporaryData();
    }

    protected function checkSync( $item, $compare_prices = false ) {
        // check wc product
        $item = (array) $item;
        $post_id = $item['post_id'];
        $_product = $this->getProduct( $post_id );

        // checking parent variations makes no sense in WPLA, so skip them
        if ( wpla_get_product_meta( $_product, 'product_type' ) == 'variable' ) {
            return true;
        }

        // get stock level and price
        $stock = WPLA_ProductWrapper::getStock( $item['post_id'] );
        $price = WPLA_ProductWrapper::getPrice( $item['post_id'] );

        // check for sale price on amazon side
        $sale_price       = $this->getSalePriceForItem( $item );
        $amazon_price     = $sale_price ? $sale_price : $item['price'];

        //
        // check if product and amazon listing are in sync
        //

        // check stock level making sure "" is equal to 0 so they dont show up in the report
        if ( empty( $item['quantity'] ) ) {
            $item['quantity'] = 0;
        }

        if ( $stock != $item['quantity'] ) {
            return false;
        }

        // check price
        if ( $compare_prices ) {
            if ( round( $price, 2 ) != round( $amazon_price, 2 ) ) {
                return false;
            }
        }

        return true;
    }

    public function addToReport( $item ) {
        // get stock level and price
        $item       = (array)$item;
        $stock      = WPLA_ProductWrapper::getStock( $item['post_id'] );
        $price      = WPLA_ProductWrapper::getPrice( $item['post_id'] );
        $product    = $this->getProduct( $item['post_id'] );

        // count products which have not yet been marked as changed
        if ( $item['status'] == 'online' ) $this->published_count += 1;

        // add to list of out of sync products
        $item['price_woo']      = $price;
        $item['price_woo_max']  = isset( $price_max ) ? $price_max : false;
        $item['stock']          = $stock;
        $item['exists']         = $product ? true : false;
        $item['type']           = $product ? wpla_get_product_meta( $product, 'product_type' ) : 'missing';
        $item['parent_id']      = WPLA_ProductWrapper::getVariationParent( $item['post_id'] );

        array_push( $this->oos_products, $item );

        WPLA()->logger->info( 'Added item #'. $item['post_id'] .' to the OOS list' );
        WPLA()->logger->info( 'List now contains '. count( $this->oos_products ) .' items' );
    }

    /**
     * Compare local stocks agains the last Merchant Inventory report
     * @param WPLA_AmazonReport $report
     */
    public function checkInventorySyncFromReport( $report ) {
         if ( is_numeric( $report ) ) {
             $report = new WPLA_AmazonReport( $report);
         }

        $lm = new WPLA_ListingsModel();
        $rows = $report->get_data_rows();
        $page = get_option( 'wpla_bg_inventory_check_step', 1 );
        $offset = ( $page * $this->batch_size ) - $this->batch_size;
        $total_pages = ceil( count( $rows ) / $this->batch_size );

        WPLA()->logger->info( 'checkInventorySyncFromReport step #'. $page .' (batch size: '. $this->batch_size .')' );
        WPLA()->logger->info( 'total pages: '. $total_pages );

        $rows = array_slice( $rows, $offset, $this->batch_size );

        foreach ($rows as $csv) {

            $row_asin = false;
            $row_asin = isset( $csv['asin1'] ) ? $csv['asin1'] : $row_asin;
            $row_asin = isset( $csv['asin']  ) ? $csv['asin']  : $row_asin;
            if ( ! $row_asin && isset($csv['product-id']) ) {
                if ( $csv['product-id-type'] == 1 ) {
                    $row_asin = $csv['product-id'];
                }
            }

            // skip if $csv is not the right format - seller-sku is required,
            // localized report headers would insert empty rows in listings table
            if ( ! isset( $csv['seller-sku'] ) || empty( $csv['seller-sku'] ) ) {
                WPLA()->logger->error('Could not parse report row (ASIN: '. $row_asin .'). Make sure to disable localized column headers in seller central.');
                continue;
            }

            $existing_item = $lm->getItemBySkuAndAccount( $csv['seller-sku'], $report->account_id, false );

            if ( $existing_item ) {
                // checkSync expects as $item array with quantity, price and post_id and since we're
                // not updating the listings here, just copy the $existing_item array and use the quantity from the report
                $item = $existing_item;
                $item->quantity = $csv['quantity'];

                if ( ! $this->checkSync( $item, false ) ) {
                    $this->addToReport( $item );
                }
            }
        }
        $this->saveTemporaryData();

        if ( $page < $total_pages ) {
            $page++;
            update_option( 'wpla_bg_inventory_check_step', $page );
            as_schedule_single_action( time() + 1, 'wpla_bg_inventory_check_run', array( 'report' => $report->id ) );
        } else {
            // Done processing. Reset the data then send the notification email
            delete_option( 'wpla_bg_inventory_check_step' );

            if ( count( $this->oos_products ) ) {
                // out-of-sync products found!
                $this->sendSyncNotificationEmail();
                $this->resetData();
            }
        }

    }

    private function sendSyncNotificationEmail() {
        $admin_email = get_option( 'wpla_inventory_check_notification_email', get_bloginfo( 'admin_email' ) );
        $mailer = WC()->mailer();
        $subject = 'Your WP-Lister products are out of sync!';
        $message = '<p>Warning: '. sprintf( _n( '%d listing is', '%d listings are', sizeof( $this->oos_products ), 'wp-lister-for-amazon' ), sizeof( $this->oos_products ) ) .' out of sync or missing in WooCommerce.</p>';
        $message .= $this->generateResultsTable();
        $message = $mailer->wrap_message( $subject, $message );
        $mailer->send( $admin_email, $subject, $message );
    }

	// check_wc_out_of_sync
	public function checkProductInventory( $mode = 'published', $compare_prices = false, $step = 0 ) {
        $limit      = $this->batch_size;
		$offset     = $this->batch_size * $step;

		// get all published listings
		$lm = new WPLA_ListingsModel();
		// $listings = $mode == 'published' ? $lm->getWhere( 'status', 'online' ) : $lm->getWhere( 'status', 'sold' );
		$listings = $mode == 'published' ? WPLA_ListingQueryHelper::getAllPublished( $limit, $offset ) : WPLA_ListingQueryHelper::getAllSold( $limit, $offset );
		if ( empty($listings) ) return false;


		// process published listings
		foreach ( $listings as $item ) {
		    if ( !$this->checkSync( $item, $compare_prices ) ) {
                // mark listing as changed
                if ( isset( $_REQUEST['mark_as_changed'] ) && $_REQUEST['mark_as_changed'] == 'yes' ) {
                    // mark as modified, but skip updating feeds
                    $lm->markItemAsModified( $item['post_id'], true );

                    // in case the product is missing, force the listing to be changed (?)
                    $lm->updateListing( $item['id'], array( 'status' => 'changed' ) );

                    //$item['status'] = 'changed';
                }

                // add to list of out of sync products
                $this->addToReport( $item );
            }
		}

		// store result so far
        $this->mode = $mode;
		$this->compare_prices = $compare_prices;
		$this->saveTemporaryData();

		// true means we processed more items
		return true;

	} // checkProductInventory()


	public function showProductInventoryCheckResult( $mode = 'published' ) {

		// return if empty
		if ( empty( $this->oos_products ) ) {
			WPLA()->showMessage('All '.$mode.' listings seem to be in sync.', 0, 1);
			return;
		}

		$msg = '<p>';
		$msg .= 'Warning: '.sizeof($this->oos_products).' '.$mode.' listings are out of sync or missing in WooCommerce.';
		$msg .= '</p>';

		$msg .= $this->generateResultsTable();

		// buttons
		$msg .= '<p>';

		// show 'check again' button
		$url  = 'admin.php?page=wpla-tools&tab=inventory&action=check_wc_out_of_sync&mode='.$mode.'&prices='.$this->compare_prices.'&_wpnonce='.wp_create_nonce('wpla_tools_page');
		$msg .= '<a href="'.$url.'" class="button">'.__( 'Check again', 'wp-lister-for-amazon' ).'</a> &nbsp; ';

		// show 'mark all as changed' button
		if ( $mode == 'published' )
		if ( $this->published_count ) {
			$url = 'admin.php?page=wpla-tools&tab=inventory&action=check_wc_out_of_sync&mark_as_changed=yes&mode='.$mode.'&prices='.$this->compare_prices.'&_wpnonce='.wp_create_nonce('wpla_tools_page');
			$msg .= '<a href="'.$url.'" class="button">'.__( 'Mark all as changed', 'wp-lister-for-amazon' ).'</a> &nbsp; ';
			$msg .= 'Click this button to mark all found listings as changed in WP-Lister.';
		} else {
			// $msg .= '<a id="btn_revise_all_changed_items_reminder" class="btn_revise_all_changed_items_reminder button wpl_job_button">' . __( 'Revise all changed items', 'wp-lister-for-amazon' ) . '</a>';
			// $msg .= ' &nbsp; ';
			// $msg .= 'Click to revise all changed items.';
		}
		$msg .= '</p>';

		WPLA()->showMessage( $msg, 1, 1 );
		$this->resetData();

	} // showProductInventoryCheckResult()

    private function generateResultsTable() {
        $msg = '';

        // table header
        $msg .= '<table style="width:100%">';
        $msg .= "<tr>";
        $msg .= "<th style='text-align:left'>SKU</th>";
        $msg .= "<th style='text-align:left'>Product</th>";
        $msg .= "<th style='text-align:left'>Local Qty</th>";
        $msg .= "<th style='text-align:left'>Amazon Qty</th>";
        $msg .= "<th style='text-align:left'>Local Price</th>";
        $msg .= "<th style='text-align:left'>Amazon Price</th>";
        $msg .= "<th style='text-align:left'>ASIN</th>";
        $msg .= "<th style='text-align:left'>Status</th>";
        $msg .= "</tr>";

        // table rows
        foreach ( $this->oos_products as $item ) {
            // echo "<pre>";print_r($item['asin']);echo"</pre>";#die();

            // get column data
            $sku          = $item['sku'];
            $qty          = $item['quantity'];
            $stock        = $item['stock'];
            $title        = $item['listing_title'];
            $post_id      = $item['post_id'];
            $asin         = $item['asin'];
            $status       = $item['status'];
            $exists       = $item['exists'];
            $price        = wc_price( $item['price'] );
            $price_woo    = wc_price( $item['price_woo'] );
            $product_type = $item['type'] == 'simple' ? '' : $item['type'];

            // highlight changed values
            $changed_stock     =   intval( $item['quantity']   )     ==   intval( $item['stock']     )     ? false : true;
            $changed_price     = floatval( $item['price'] )     == floatval( $item['price_woo'] )     ? false : true;
            $changed_price_max = floatval(@$item['price_max'] ) == floatval( $item['price_woo_max'] ) ? false : true;
            $stock_css         = $changed_stock                       ? 'color:darkred;' : '';
            $price_css         = $changed_price || $changed_price_max ? 'color:darkred;' : '';
//            if ( ! $compare_prices ) $price_css = '';

            // build links
            // $amazon_url  = $item['ViewItemURL'] ? $item['ViewItemURL'] : $amazon_url = 'http://www.amazon.com/itm/'.$asin;
            $product_id  = $item['parent_id'] ? $item['parent_id'] : $post_id;
            $amazon_url  = admin_url( 'admin.php?page=wpla&s='.$asin );
            $product_url = admin_url( 'post.php?action=edit&post='. $product_id );
            $amazon_link = '<a href="'.$amazon_url.'" target="_blank">'.$asin.'</a>';
            $edit_link   = '<a href="'. $product_url .'" target="_blank">'.$title.'</a>';

            // mark non existent products
            if ( ! $exists ) {
                $stock    = 'N/A';
                $post_id .= ' missing!';
            }

            // show price range for variations
            // if ( $item['price_woo_max'] )
            // 	$price_woo .= ' - '.wc_price( $item['price_woo_max'] );
            // if ( @$item['price_max'] )
            // 	$price .= ' - '.wc_price( $item['price_max'] );

            // build table row
            $msg .= "<tr>";
            $msg .= "<td>$sku</td>";
            $msg .= "<td>$edit_link <span style='color:silver'>$product_type (#$post_id)</span></td>";
            $msg .= "<td style='$stock_css'>$stock</td>";
            $msg .= "<td style='$stock_css'>$qty</td>";
            $msg .= "<td style='$price_css'>$price_woo</td>";
            $msg .= "<td style='$price_css'>$price</td>";
            $msg .= "<td>$amazon_link</td>";
            $msg .= "<td>$status</td>";
            $msg .= "</tr>";
        }
        $msg .= '</table>';

        return $msg;
    }

	// check_wc_out_of_stock
	public function checkProductStock( $step = 0 ) {

		$limit      = $this->batch_size;
		$offset     = $this->batch_size * $step;

		// get all published listings
		$lm = new WPLA_ListingsModel();
		$listings = WPLA_ListingQueryHelper::getAllPublished( $limit, $offset );

		if ( empty($listings) ) return false;

		// restore previous data
		$tmp_result = $this->getTemporaryData();
        $out_of_stock_products = $tmp_result['out_of_stock_products'];

		// process published listings
		foreach ( $listings as $item ) {

			// get wc product
			$item = (array) $item;
			$_product = $this->getProduct( $item['post_id'] );

			// checking parent variations makes no sense in WPLA, so skip them
			if ( wpla_get_product_meta( $_product, 'product_type' ) == 'variable' ) continue;

			// check stock level
			$stock = WPLA_ProductWrapper::getStock( $item['post_id'] );
			// $stock = $_product ? $_product->get_total_stock() : 0;
			if ( $stock > 0 )
				continue;

			if ( $item['quantity'] == 0 )
				continue;

			// mark listing as changed
			if ( isset( $_REQUEST['mark_as_changed'] ) && $_REQUEST['mark_as_changed'] == 'yes' ) {
				$lm->updateListing( $item['id'], array( 'status' => 'changed' ) );
				$item['status'] = 'changed';
			}

			// add to list of out of stock products
			$item['stock']     = $stock;
			$item['exists']    = $_product ? true : false;
			$item['parent_id'] = WPLA_ProductWrapper::getVariationParent( $item['post_id'] );
			$out_of_stock_products[] = $item;

		}

		// store result so far
		$tmp_result = array(
			'out_of_stock_products' => $out_of_stock_products,
		);
		update_option('wpla_inventory_check_queue_data', $tmp_result, 'no');

		// true means we processed more items
		return true;

	} // checkProductStock()



	public function showProductStockCheckResult( $mode = 'out_of_stock' ) {

		// restore previous data
		$tmp_result = get_option('wpla_inventory_check_queue_data', false);
		$out_of_stock_products = $tmp_result['out_of_stock_products'];
		// $published_count      = $tmp_result['published_count'];
		// $compare_prices       = $tmp_result['compare_prices'];
		// $mode                 = $tmp_result['mode'];


		// return if empty
		if ( empty( $out_of_stock_products ) ) {
			WPLA()->showMessage('No out of stock products found.', 0, 1);
			return;
		}

		$msg = '<p>';
		$msg .= sprintf( 'Warning: %s published listings are out of stock or missing in WooCommerce.', sizeof($out_of_stock_products) );
		$msg .= '</p>';

		// table header
		$msg .= '<table style="width:100%">';
		$msg .= "<tr>";
		$msg .= "<th style='text-align:left'>Stock</th>";
		$msg .= "<th style='text-align:left'>SKU</th>";
		$msg .= "<th style='text-align:left'>Product</th>";
		$msg .= "<th style='text-align:left'>Qty</th>";
		$msg .= "<th style='text-align:left'>ASIN</th>";
		$msg .= "<th style='text-align:left'>Status</th>";
		$msg .= "</tr>";

		// table rows
		foreach ( $out_of_stock_products as $item ) {

			// get column data
			$sku     = $item['sku'];
			$qty     = $item['quantity'];
			$stock   = $item['stock'] . ' x ';
			$title   = $item['listing_title'];
			$post_id = $item['post_id'];
			$asin    = $item['asin'];
			$status  = $item['status'];
			$exists  = $item['exists'];

			// build links
			// $amazon_url  = $item['ViewItemURL'] ? $item['ViewItemURL'] : $amazon_url = 'http://www.amazon.com/itm/'.$asin;
			$amazon_url  = 'admin.php?page=wpla&s='.$asin;
			$amazon_link = '<a href="'.$amazon_url.'" target="_blank">'.$asin.'</a>';
			$edit_link   = '<a href="post.php?action=edit&post='. ( $item['parent_id'] ? $item['parent_id'] : $post_id ) .'" target="_blank">'.$title.'</a>';

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
			$msg .= "<td>$amazon_link</td>";
			$msg .= "<td>$status</td>";
			$msg .= "</tr>";
		}
		$msg .= '</table>';


		$msg .= '<p>';
		$url = 'admin.php?page=wpla-tools&action=check_wc_out_of_stock&mark_as_changed=yes&_wpnonce='.wp_create_nonce('wpla_tools_page');
		$msg .= '<a href="'.$url.'" class="button">'.__( 'Mark all as changed', 'wp-lister-for-amazon' ).'</a> &nbsp; ';
		$msg .= 'Click this button to mark all found listings as changed in WP-Lister.';
		$msg .= '</p>';

		WPLA()->showMessage( $msg, 1, 1 );

	} // showProductStockCheckResult()





	// check_wc_fba_stock
	public function checkFBAStock( $mode = 'in_stock_only', $step = 0 ) {

		$limit      = $this->batch_size;
		$offset     = $this->batch_size * $step;

		// get all published listings
		$lm = new WPLA_ListingsModel();
		$out_of_sync_products = array();

		if ( $mode == 'all_stock' ) {
			$listings = $lm->getAllItemsUsingFBA( $limit, $offset );
		} else {
			$listings = $lm->getAllItemsWithStockInFBA( $limit, $offset );
		}
		if ( empty($listings) ) return false;

		// restore previous data
		$tmp_result = get_option('wpla_inventory_check_queue_data', false);
		if ( $tmp_result ) {
			$out_of_sync_products = $tmp_result['out_of_sync_products'];
		} else {
			$out_of_sync_products = array();
		}

		// process FBA listings
		foreach ( $listings as $item ) {

			// get wc product
			$item = (array) $item;
			$_product = $this->getProduct( $item['post_id'] );
			if ( ! $_product ) continue;

			// checking parent variations makes no sense in WPLA, so skip them
			if ( wpla_get_product_meta( $_product, 'product_type' ) == 'variable' ) continue;

			// check stock level
			$stock = WPLA_ProductWrapper::getStock( $item['post_id'] );
			if ( $stock == $item['fba_quantity'] )
				continue;

			// copy FBA qty to Woo
            if ( isset( $_REQUEST['wpla_copy_fba_qty_to_woo'] ) && $_REQUEST['wpla_copy_fba_qty_to_woo'] == 'yes' ) {
				update_post_meta( $item['post_id'], '_stock', $item['fba_quantity'] );
				continue;
			}

			// add to list of out of stock products
			$item['stock']     = $stock;
			$item['parent_id'] = WPLA_ProductWrapper::getVariationParent( $item['post_id'] );
			$out_of_sync_products[] = $item;

		}

		// store result so far
		$tmp_result = array(
			'out_of_sync_products' => $out_of_sync_products,
		);
		update_option('wpla_inventory_check_queue_data', $tmp_result, 'no');

		// true means we processed more items
		return true;

	} // checkFBAStock()



	public function showFBAStockCheckResult( $mode = 'in_stock_only' ) {

		// restore previous data
		$tmp_result = get_option('wpla_inventory_check_queue_data', false);
		$out_of_sync_products = isset( $tmp_result['out_of_sync_products'] ) ? $tmp_result['out_of_sync_products'] : array();
		// $mode                 = $tmp_result['mode'];

		// return if empty
		if ( empty( $out_of_sync_products ) ) {
			WPLA()->showMessage('All FBA products are in sync with WooCommerce.', 0, 1);
			return;
		}

		$msg = '<p>';
		$msg .= sprintf( 'There are %s FBA products have a different stock level in WooCommerce.', sizeof($out_of_sync_products) );
		$msg .= '</p>';

		// table header
		$msg .= '<table style="width:100%">';
		$msg .= "<tr>";
		$msg .= "<th style='text-align:left'>SKU</th>";
		$msg .= "<th style='text-align:left'>Product</th>";
		$msg .= "<th style='text-align:left'>FBA</th>";
		$msg .= "<th style='text-align:left'>WooCommerce</th>";
		$msg .= "<th style='text-align:left'>ASIN</th>";
		$msg .= "<th style='text-align:left'>Status</th>";
		$msg .= "</tr>";

		// table rows
		foreach ( $out_of_sync_products as $item ) {

			// get column data
			$sku     = $item['sku'];
			$qty     = $item['quantity'];
			$fba_qty = $item['fba_quantity'];
			$stock   = $item['stock'];
			$title   = $item['listing_title'];
			$post_id = $item['post_id'];
			$asin    = $item['asin'];
			$status  = $item['status'];

			// build links
			// $amazon_url  = $item['ViewItemURL'] ? $item['ViewItemURL'] : $amazon_url = 'http://www.amazon.com/itm/'.$asin;
			$amazon_url  = 'admin.php?page=wpla&s='.$asin;
			$amazon_link = '<a href="'.$amazon_url.'" target="_blank">'.$asin.'</a>';
			$edit_link   = '<a href="post.php?action=edit&post='. ( $item['parent_id'] ? $item['parent_id'] : $post_id ) .'" target="_blank">'.$title.'</a>';

			// build table row
			$msg .= "<tr>";
			$msg .= "<td>$sku</td>";
			$msg .= "<td>$edit_link</td>";
			$msg .= "<td>$fba_qty</td>";
			$msg .= "<td>$stock</td>";
			$msg .= "<td>$amazon_link</td>";
			$msg .= "<td>$status</td>";
			$msg .= "</tr>";
		}
		$msg .= '</table>';


		$msg .= '<p>';
		$url = 'admin.php?page=wpla-tools&tab=inventory&action=check_wc_fba_stock&wpla_copy_fba_qty_to_woo=yes&mode='.$mode.'&_wpnonce='.wp_create_nonce('wpla_tools_page');
		$msg .= '<a href="'.$url.'" class="button">'.__( 'Copy FBA quantity to WooCommerce', 'wp-lister-for-amazon' ).'</a> &nbsp; ';
		$msg .= 'Click this button set the stock level in WooCommerce to the current FBA quantity for each found product.';
		$msg .= '</p>';

		WPLA()->showMessage( $msg, 1, 1 );


	} // checkFBAStock()





	// check_wc_sold_stock
	public function checkSoldStock() {

		// get all published listings
		$lm = new WPLA_ListingsModel();
		$listings = $lm->getWhere( 'status', 'sold' );
		$out_of_stock_products = array();

		// process published listings
		foreach ( $listings as $item ) {

			// get wc product
			$_product = $this->getProduct( $item['post_id'] );

			// checking parent variations makes no sense in WPLA, so skip them
			if ( wpla_get_product_meta( $_product, 'product_type' ) == 'variable' ) continue;

			// check stock level
			// $stock = WPLA_ProductWrapper::getStock( $item['post_id'] );
            $stock = 0;

            if ( $_product ) {
                $stock = WPLA_ProductWrapper::getStock( $_product );
            }

			if ( $stock == 0 )
				continue;

			// add to list of out of stock products
			$item['stock']     = $stock;
			$item['exists']    = $_product ? true : false;
			$item['parent_id'] = WPLA_ProductWrapper::getVariationParent( $item['post_id'] );
			$out_of_stock_products[] = $item;

		}

		// return if empty
		if ( empty( $out_of_stock_products ) ) {
			WPLA()->showMessage('No sold products have stock in WooCommerce.', 0, 1);
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
		$msg .= "<th style='text-align:left'>ASIN</th>";
		$msg .= "<th style='text-align:left'>Ended at</th>";
		$msg .= "<th style='text-align:left'>Status</th>";
		$msg .= "</tr>";

		// table rows
		foreach ( $out_of_stock_products as $item ) {

			// get column data
			// $qty     = $item['quantity'] - $item['quantity_sold'];
			$sku     = $item['sku'];
			$qty     = $item['quantity'];
			$stock   = $item['stock'] . ' x ';
			$title   = $item['listing_title'];
			$post_id = $item['post_id'];
			$asin    = $item['asin'];
			$status  = $item['status'];
			$exists  = $item['exists'];
			$date_ended = $item['date_finished'] ? $item['date_finished'] : $item['end_date'];

			// build links
			$amazon_url = $item['ViewItemURL'] ? $item['ViewItemURL'] : $amazon_url = 'http://www.amazon.com/itm/'.$asin;
			$amazon_url  = 'admin.php?page=wpla&s='.$asin;
			$amazon_link = '<a href="'.$amazon_url.'" target="_blank">'.$asin.'</a>';
			$edit_link   = '<a href="post.php?action=edit&post='. ( $item['parent_id'] ? $item['parent_id'] : $post_id ) .'" target="_blank">'.$title.'</a>';

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
			$msg .= "<td>$amazon_link</td>";
			$msg .= "<td>$date_ended</td>";
			$msg .= "<td>$status</td>";
			$msg .= "</tr>";
		}
		$msg .= '</table>';

		// show 'check again' button
		$msg .= '<p>';
		$url  = 'admin.php?page=wpla-tools&action=check_wc_sold_stock&_wpnonce='.wp_create_nonce('wpla_tools_page');
		$msg .= '<a href="'.$url.'" class="button">'.__( 'Check again', 'wp-lister-for-amazon' ).'</a> &nbsp; ';
		$msg .= '</p>';

		// $msg .= '<p>';
		// $url = 'admin.php?page=wpla-tools&action=check_wc_out_of_stock&mark_as_changed=yes&_wpnonce='.wp_create_nonce('wpla_tools_page');
		// $msg .= '<a href="'.$url.'" class="button">'.__( 'Mark all as changed', 'wp-lister-for-amazon' ).'</a> &nbsp; ';
		// $msg .= 'Click this button to mark all found listings as changed in WP-Lister.';
		// $msg .= '</p>';

		WPLA()->showMessage( $msg, 1, 1 );


	} // checkSoldStock()

    function getSalePriceForItem($item) {
        if ( ! $item['post_id'] ) return false;
        $post_id = $item['post_id'];

        $product = $this->getProduct( $post_id );
        $profile = $this->getProfile( $item['profile_id'] );

        $value   = wpla_get_product_meta( $product, 'sale_price' );          // WC2.0 compat
        $value   = $profile ? $profile->processProfilePrice( $value ) : $value;
        $value   = apply_filters( 'wpla_filter_sale_price', $value, $post_id, $product, $item, $profile );

        return $value;
    }

    // get profile object - if possible from cache
    function getProfile( $profile_id ) {

        // update cache if required
        if ( $this->last_profile_id != $profile_id ) {
            $this->last_profile_object = new WPLA_AmazonProfile( $profile_id );
            $this->last_profile_id     = $profile_id;
        }

        return $this->last_profile_object;
    }

    // get product object - if possible from cache
    function getProduct( $post_id ) {

        // update cache if required
        if ( $this->last_product_id != $post_id ) {
            $this->last_product_object = WPLA_ProductWrapper::getProduct( $post_id );
            $this->last_product_id     = $post_id;
        }

        return $this->last_product_object;
    }


} // class WPLA_InventoryCheck
