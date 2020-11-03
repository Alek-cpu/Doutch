<?php

class WPLA_ImportHelper {

	var $account;
	public $result;
	public $message = '';
	public $lastError;
	public $lastPostID;
	public $updated_count = 0;
	public $imported_count = 0;
	public $request_count = 0;

	const TABLENAME = 'amazon_listings';


	public static function analyzeReportForPreview( $report ) {
		$summary = new stdClass();

        $data_rows    = $report->get_data_rows();
        $report_asins = self::getAllASINsInReport( $data_rows );
        $report_skus  = self::getAllSKUsInReport( $data_rows );

        $wpla_asins   = self::getAllASINsInWPLA();
        $woocom_skus  = self::getAllSKUsInWooCom();

		// compare ASINs
		$summary->listings_to_update = array_intersect( $report_asins, $wpla_asins );
		$summary->listings_to_import = array_diff     ( $report_asins, $wpla_asins );

		// compare SKUs
		$summary->products_to_update = array_intersect( $report_skus, $woocom_skus );
		$summary->products_to_import = array_diff     ( $report_skus, $woocom_skus );

		// include raw data as well
		$summary->report_asins = $report_asins;
		$summary->report_skus  = $report_skus;
		// $summary->woocom_skus  = $woocom_skus;
		// $summary->wpla_asins   = $wpla_asins;

		// echo "<pre>";print_r($summary);echo"</pre>";die();
		return $summary;
	}


	public static function getAllSKUsInWooCom() {
		global $wpdb;
		$table = $wpdb->postmeta;

		$result = $wpdb->get_col("
			SELECT meta_value FROM $table
			WHERE meta_key = '_sku'
			  AND NOT meta_value = ''
		");
		return $result;
	}

	public static function getAllASINsInWPLA() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$result = $wpdb->get_col("
			SELECT asin FROM $table
			WHERE NOT asin IS NULL
		");
		return $result;
	}

	public static function getAllASINsInReport( $rows ) {
		$ASINs = array();
		foreach ($rows as $row) {
			$row_asin = false;
			$row_asin = isset( $row['asin1'] ) ? $row['asin1'] : $row_asin;
			$row_asin = isset( $row['asin']  ) ? $row['asin']  : $row_asin;

			// special treatment for amazon.ca
			if ( ! $row_asin && isset($row['product-id']) ) {
				if ( $row['product-id-type'] == 1 ) {
					$row_asin = $row['product-id'];
				}
			}
			if ( ! $row_asin ) continue;

			// if ( ! in_array($row_asin, $ASINs) ) // poor performance on big arrays
			if ( ! isset( $ASINs[ $row_asin ] ) )
				$ASINs[ $row_asin ] = 1;
		}
		return array_keys( $ASINs );
	}

	public static function getAllSKUsInReport( $rows ) {
		$SKUs = array();
		foreach ($rows as $row) {
			$row_sku = $row['seller-sku'];
			// if ( ! in_array($row_sku, $SKUs) )  // poor performance on big arrays
			if ( ! isset( $SKUs[ $row_sku ] ) )
				$SKUs[ $row_sku ] = 1;
		}
		return array_keys( $SKUs );
	}


	// process single report page - called from WPLA_AjaxHandler
	public static function ajax_processReportPage( $job, $task, $single_sku_mode = false ) {

		// init
		$report = new WPLA_AmazonReport( $task['id'] );
		// $account = WPLA_AmazonAccount::getAccount( $report->account_id );
		// $api     = new WPLA_AmazonAPI( $account->id );

		// get CSV data
        $rows = $report->get_data_rows();

        if ( $single_sku_mode ) {

			// slice single row with matching SKU
			$selected_rows = array();
			foreach ( $rows as $row ) {
				if ( $row['seller-sku'] == $task['sku'] ) {
					$selected_rows[] = $row;
				}
			}
			$rows = $selected_rows;

        } else {

			// slice rows array according to limits
			$from_row = $task['from_row'];
			$to_row   = $task['to_row'];
			$rows     = array_slice( $rows, $from_row - 1, $to_row - $from_row + 1, true );

        }


		// _GET_AFN_INVENTORY_DATA_
       	if ( $report->ReportType == '_GET_AFN_INVENTORY_DATA_' ) {
			return self::processFBAReportPage( $report, $rows, $job, $task );
			die();
       	}

        // _GET_FBA_MYI_UNSUPPRESSED_INVENTORY_DATA_
        if ( $report->ReportType == '_GET_FBA_MYI_UNSUPPRESSED_INVENTORY_DATA_' ) {
            return self::processManageFBAReportPage( $report, $rows, $job, $task );
            die();
        }

		// _GET_FBA_FULFILLMENT_INVENTORY_HEALTH_DATA_
       	if ( $report->ReportType == '_GET_FBA_FULFILLMENT_INVENTORY_HEALTH_DATA_' ) {
			return self::processFBAInventoryHealthReportPage( $report, $rows, $job, $task );
			die();
       	}

		// _GET_MERCHANT_LISTINGS_DEFECT_DATA_
       	if ( $report->ReportType == '_GET_MERCHANT_LISTINGS_DEFECT_DATA_' ) {
			return self::processQualityReportPage( $report, $rows, $job, $task );
			die();
       	}

		// _GET_MERCHANT_LISTINGS_DATA_
       	if ( $report->ReportType == '_GET_MERCHANT_LISTINGS_DATA_' ) {
			return self::processInventoryReportPage( $report, $rows, $job, $task );
			die();
       	}

		echo "Unknown report type: ".$report->ReportType;
		die();
	} // ajax_processReportPage()



	// process single merchant inventory report page
	public static function processInventoryReportPage( $report, $rows, $job, $task ) {

        // process rows
		$lm             = new WPLA_ListingsModel();
		$ProductBuilder = new WPLA_ProductBuilder();

		// $update_woo_products_from_reports = get_option( 'wpla_update_woo_products_from_reports' ) == '1' ? true : false;
		$reports_update_woo_stock         = get_option( 'wpla_reports_update_woo_stock'    , 1 ) == 1 ? true : false;
		$reports_update_woo_price         = get_option( 'wpla_reports_update_woo_price'    , 1 ) == 1 ? true : false;
		$reports_update_woo_condition     = get_option( 'wpla_reports_update_woo_condition', 1 ) == 1 ? true : false;
		$update_woo_products_from_reports = $reports_update_woo_stock || $reports_update_woo_price || $reports_update_woo_condition;

		foreach ($rows as $report_row) {
		    // Allow plugins to skip rows #34591
		    $process_row = apply_filters( 'wpla_process_inventory_report_listing', true, $report_row, $report );

		    if ( $process_row ) {
                $existing_item = $lm->updateItemFromReportCSV( $report_row, $report->account_id );
                if ( $existing_item && $update_woo_products_from_reports ) {
                    $ProductBuilder->updateProductFromItem( $existing_item, $report_row );
                }
            }

		}

		//
		// debug
		//
		$msg  = ''.$lm->imported_count.' items were added to the import queue and '.$lm->updated_count.' existing listings were updated.<br>';
		$msg  = "<div class='updated'><p>$msg</p></div>";

		// send debug data as error...
		$error = new stdClass();
		$error->code  		= 10001;
		$error->HtmlMessage	= $msg;
		$errors  = array( $error );

		$success = true;
		// $errors  = '';


		// build response
		$response = new stdClass();
		$response->job  	= $job;
		$response->task 	= $task;
		$response->errors   = $errors;
		$response->success  = $success;

		$response->imported_count = $lm->imported_count;
		$response->updated_count  = $lm->updated_count;

		return $response;
	} // processInventoryReportPage()



	// process single FBA report page
	public static function processFBAReportPage( $report, $rows, $job, $task ) {
	    global $wpdb;

		$listingsModel = new WPLA_ListingsModel();
		$errors = array();

		// get default fulfillment center ID
		$fba_default_fcid = get_option( 'wpla_fba_fulfillment_center_id', 'AMAZON_NA' );


		// if fallback is enabled, clear FBA data before processing first page
		$account_id          = $report->account_id;
		$fba_enable_fallback = get_option( 'wpla_fba_enable_fallback', 0 );
		$fba_only_mode       = get_option( 'wpla_fba_only_mode', 0 );
		$fba_stock_sync      = get_option( 'wpla_fba_stock_sync', 0 );
		$is_first_page       = $task === null || $task['from_row'] == 1 ? true : false;

		if ( $fba_enable_fallback && $is_first_page ) {

			// reset FBA info for all items using this account
			$update_data = array(
				'fba_quantity' => null,
				'fba_fcid'     => null,
			);
			$listingsModel->updateWhere( array( 'account_id' => $account_id ), $update_data );
		}


        // process rows
		if ( is_array($rows) )
		foreach ($rows as $row) {

			// skip error rows (single element array)
			if ( sizeof($row) <= 1 ) {
				$error = new stdClass();
				$error->HtmlMessage = strip_tags( reset($row) );
				$errors[] = $error;
				continue;
			}

			$asin          = $row['asin'];
			$sku           = html_entity_decode( $row['seller-sku'] ); // yes, a & char will become &amp; in an FBA report
			$fba_quantity  = $row['Quantity Available'];
			$fba_condition = $row['Warehouse-Condition-code'];
			$fba_fnsku     = $row['fulfillment-channel-sku'];
			$stock_updated = false;

			// skip rows if condition is UNSELLABLE
			if ( $fba_condition == 'UNSELLABLE' ) continue;

			// if fallback enabled, skip rows with zero quantity
			if ( $fba_quantity == 0 && $fba_enable_fallback && !$fba_only_mode && !$fba_stock_sync ) continue;


			// update quantity in WooCommerce - only if current stock level is less than FBA quantity
			if ( $listing_item = $listingsModel->getItemBySKU( $sku ) ) {


				// update listings table
				$update_data = array(
					'fba_quantity' => $fba_quantity,
					'fba_fcid'     => $fba_default_fcid,
				);
				// mark item as changed - if FBA fallback is enabled and Fulfillment Center ID has been changed
				if ( $fba_enable_fallback && $listing_item->fba_fcid != $fba_default_fcid ) {
					$update_data['status'] = 'changed';
				}

				if ( ! get_option( 'wpla_case_sensitive_sku_matching', 0 ) ) {
                    // update listings table - by SKU and account ID
                    $listingsModel->updateWhere( array( 'sku' => $sku, 'account_id' => $account_id ), $update_data );
                } else {
                    /*
                     * The WHERE clause in MySQL is case-insensitive when used with a *_ci collation (https://stackoverflow.com/questions/4558707/case-sensitive-collation-in-mysql)
                     * This can cause an issue when a merchant uses the same SKU but in different cases #31798. We need to
                     * specify a non-CI collation in the WHERE clause of the query to force MySQL to do a case-sensitive comparison
                     *
                     */
                    // Update listings table by SKU and account ID
                    $data = '';
                    foreach ( $update_data as $key => $value ) {
                        $data .= "`$key` = '". esc_sql( $value ) ."',";
                    }
                    $data = rtrim($data, ',');
                    $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}amazon_listings SET {$data} WHERE sku collate utf8_bin = %s AND account_id = %d", $sku, $account_id ) );
                }

				// store FNSKU in details column
				if ( ! is_object($listing_item->details) ) $listing_item->details = new stdClass();
				$listing_item->details->fnsku = $fba_fnsku;
				$update_data = array(
					'details' => json_encode( $listing_item->details )
				);
				$listingsModel->updateWhere( array( 'sku' => $sku, 'account_id' => $account_id ), $update_data );


				// update product in WooCommerce
				$post_id = $listing_item->post_id;
		        WPLA()->logger->info("updating SKU $sku / ASIN $asin / post_id $post_id - new FBA stock: $fba_quantity " );
				if ( $post_id ) {

					// disable stock sync if FBA is disabled for this SKU
					$disable_stock_sync = false;
					$fba_overwrite      = get_post_meta( $post_id, '_amazon_fba_overwrite', true );
					if ( $fba_overwrite == 'FBM' ) $disable_stock_sync = true;

				    // allow 3rd-party code to disable the synchronization of stocks from FBA to WC
				    $disable_stock_sync = apply_filters( 'wpla_disable_fba_to_wc_stock_sync', $disable_stock_sync, $listing_item );

                    if ( ! $disable_stock_sync ) {
                        // update stock level - if lower than FBA, or FBA only mode enabled

                        $woo_stock = WPLA_ProductWrapper::getStock( $post_id );

                        if ( $woo_stock < $fba_quantity || $fba_only_mode == 1 || $fba_stock_sync == 1 ) {

                        	// make sure we only update if there is a change
                            if ( $woo_stock != $fba_quantity ) {
	                            update_post_meta( $post_id, '_stock', $fba_quantity );
	                            $woo_stock = $fba_quantity;
	                            $stock_updated = true;
                            }

                        }

                        // update stock status based on actual stock - if required
                        $woo_stock_status   = get_post_meta( $post_id, '_stock_status', true );
                        $new_stock_status   = $woo_stock > 0 ? 'instock' : 'outofstock';
                        if ( $new_stock_status != $woo_stock_status ) {
                            if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
                                $wc_product = wc_get_product( $post_id );

                                if ( $wc_product ) {
                                    $wc_product->set_stock_status( $new_stock_status );
                                    $wc_product->save();
                                }
                            } else {
                                update_post_meta( $post_id, '_stock_status', $new_stock_status );
                            }

                            WPLA()->logger->info("updated stock status from $woo_stock_status to $new_stock_status");
                            $stock_updated = true;
                        }
                    }

					// if there was any change...
					if ( $stock_updated ) {

						// notify WP-Lister for eBay (and other plugins)
						do_action( 'wpla_inventory_status_changed', $post_id );

						// Save variations
						if ( 'variation' == $listing_item->product_type && method_exists('WC_Product_Variable','sync_stock_status') ) {
							$parent_id = $listing_item->parent_id;
							// Update parent if variable so price sorting works and stays in sync with the cheapest child
							if ( $_product = wc_get_product( $parent_id ) ) {
								WC_Product_Variable::sync( $parent_id );
								WC_Product_Variable::sync_stock_status( $parent_id );
						        WPLA()->logger->info("Synced stock / stock status for variation $post_id / $parent_id");
							} else {
						        WPLA()->logger->info("Skipped non-existing variation $post_id / $parent_id");
							}

							// notify WP-Lister for eBay (and other plugins)
							if ( $parent_id ) do_action( 'wpla_inventory_status_changed', $parent_id ); // trigger stock update for parent variation as well

							// Clear cache/transients
							// wc_delete_product_transients( $parent_id );
						}

					}

				} // if $post_id

			} // if $listing_item

		} // foreach report row

		// build response
		$response = new stdClass();
		$response->job  	= $job;
		$response->task 	= $task;
		$response->errors   = $errors;
		$response->success  = true;

		return $response;
	} // processFBAReportPage()

    // process single FBA report page
    public static function processManageFBAReportPage( $report, $rows, $job, $task ) {
        global $wpdb;

        $listingsModel = new WPLA_ListingsModel();
        $errors = array();

        // get default fulfillment center ID
        $fba_default_fcid = get_option( 'wpla_fba_fulfillment_center_id', 'AMAZON_NA' );


        // if fallback is enabled, clear FBA data before processing first page
        $account_id          = $report->account_id;
        $fba_enable_fallback = get_option( 'wpla_fba_enable_fallback', 0 );
        $fba_only_mode       = get_option( 'wpla_fba_only_mode', 0 );
        $fba_stock_sync      = get_option( 'wpla_fba_stock_sync', 0 );
        $is_first_page       = $task === null || $task['from_row'] == 1 ? true : false;

        if ( $fba_enable_fallback && $is_first_page ) {

            // reset FBA info for all items using this account
            $update_data = array(
                'fba_quantity' => null,
                'fba_fcid'     => null,
            );
            $listingsModel->updateWhere( array( 'account_id' => $account_id ), $update_data );
        }


        // process rows
        if ( is_array($rows) )
            foreach ($rows as $row) {

                // skip error rows (single element array)
                if ( sizeof($row) <= 1 ) {
                    $error = new stdClass();
                    $error->HtmlMessage = strip_tags( reset($row) );
                    $errors[] = $error;
                    continue;
                }

                $asin          = $row['asin'];
                $sku           = html_entity_decode( $row['sku'] ); // yes, a & char will become &amp; in an FBA report
                $fba_quantity  = $row['afn-fulfillable-quantity'];
                $fba_fnsku     = $row['fnsku'];
                $stock_updated = false;

                /**
                 * Apparently, this report includes non-FBA listings too so we have to check the
                 * afn-listing-exists value before proceeding #35204
                 * https://sellercentral.amazon.de/gp/help/external/help.html?itemID=200740930&language=en_US
                 *
                 * UPDATE: Check both afn-listing-exists and afn-fulfillable-quantitiy fields and ignore
                 * afn-listing-exists if afn-fulfillable-quantity is not zero #35732
                 */
                if ( ( empty( $row['afn-listing-exists'] ) || strtolower( $row['afn-listing-exists'] ) != 'yes' ) && $fba_quantity == 0 ) {
                    continue;
                }

                // update quantity in WooCommerce - only if current stock level is less than FBA quantity
                if ( $listing_item = $listingsModel->getItemBySKU( $sku ) ) {
                    $prev_fba_quantity = $listing_item->fba_quantity;

                    // Since we are now recording the FBA quantity separately from the FBM stocks, we should always keep the
                    // fba_quantity field updated no matter what the outcome is from the rules below
                    WPLA_ListingsModel::updateBySkuAndAccount( $sku, $account_id, array( 'fba_quantity'  => $fba_quantity ) );


                    // if fallback enabled, skip rows with zero quantity
                    if ( $fba_quantity == 0 && $fba_enable_fallback && !$fba_only_mode && !$fba_stock_sync ) {
                        if ( !is_null( $prev_fba_quantity ) && $prev_fba_quantity > 0 && $listing_item->quantity == 0 ) {
                            // FBA just ran out of stock. We need to update the WC product to make it go out of stock as well #39385
                            $wc_product = wc_get_product( $listing_item->post_id );

                            if ( $wc_product ) {
                                $wc_product->set_stock_quantity( 0 );
                                $wc_product->save();
                            }
                        }
                        continue;
                    }

                    // update listings table
                    $update_data = array(
                        'fba_quantity' => $fba_quantity,
                        'fba_fcid'     => $fba_default_fcid,
                    );
                    // mark item as changed - if FBA fallback is enabled and Fulfillment Center ID has been changed
                    if ( $fba_enable_fallback && $listing_item->fba_fcid != $fba_default_fcid ) {
                        $update_data['status'] = 'changed';
                    }

                    // Update listing
                    WPLA_ListingsModel::updateBySkuAndAccount( $sku, $account_id, $update_data );

                    // store FNSKU in details column
                    if ( ! is_object($listing_item->details) ) $listing_item->details = new stdClass();
                    $listing_item->details->fnsku = $fba_fnsku;
                    $update_data = array(
                        'details' => json_encode( $listing_item->details )
                    );
                    $listingsModel->updateWhere( array( 'sku' => $sku, 'account_id' => $account_id ), $update_data );


                    // update product in WooCommerce
                    $post_id = $listing_item->post_id;
                    WPLA()->logger->info("updating SKU $sku / ASIN $asin / post_id $post_id - new FBA stock: $fba_quantity " );
                    if ( $post_id ) {

                        // disable stock sync if FBA is disabled for this SKU
                        $disable_stock_sync = false;
                        $fba_overwrite      = get_post_meta( $post_id, '_amazon_fba_overwrite', true );
                        if ( $fba_overwrite == 'FBM' ) $disable_stock_sync = true;

                        // allow 3rd-party code to disable the synchronization of stocks from FBA to WC
                        $disable_stock_sync = apply_filters( 'wpla_disable_fba_to_wc_stock_sync', $disable_stock_sync, $listing_item );

                        if ( ! $disable_stock_sync ) {
                            // update stock level - if lower than FBA, or FBA only mode enabled

                            $woo_stock = WPLA_ProductWrapper::getStock( $post_id );

                            if ( $woo_stock < $fba_quantity || $fba_only_mode == 1 || $fba_stock_sync == 1 ) {

                                // make sure we only update if there is a change
                                if ( $woo_stock != $fba_quantity ) {
                                    update_post_meta( $post_id, '_stock', $fba_quantity );
                                    $woo_stock = $fba_quantity;
                                    $stock_updated = true;
                                }

                            }

                            // update stock status based on actual stock - if required
                            $woo_stock_status   = get_post_meta( $post_id, '_stock_status', true );
                            $new_stock_status   = $woo_stock > 0 ? 'instock' : 'outofstock';
                            if ( $new_stock_status != $woo_stock_status ) {
                                if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
                                    $wc_product = wc_get_product( $post_id );

                                    if ( $wc_product ) {
                                        $wc_product->set_stock_status( $new_stock_status );
                                        $wc_product->save();
                                    }
                                } else {
                                    update_post_meta( $post_id, '_stock_status', $new_stock_status );
                                }

                                WPLA()->logger->info("updated stock status from $woo_stock_status to $new_stock_status");
                                $stock_updated = true;
                            }
                        }

                        // if there was any change...
                        if ( $stock_updated ) {

                            // notify WP-Lister for eBay (and other plugins)
                            do_action( 'wpla_inventory_status_changed', $post_id );

                            // Save variations
                            if ( 'variation' == $listing_item->product_type && method_exists('WC_Product_Variable','sync_stock_status') ) {
                                $parent_id = $listing_item->parent_id;
                                // Update parent if variable so price sorting works and stays in sync with the cheapest child
                                if ( $_product = wc_get_product( $parent_id ) ) {
                                    WC_Product_Variable::sync( $parent_id );
                                    WC_Product_Variable::sync_stock_status( $parent_id );
                                    WPLA()->logger->info("Synced stock / stock status for variation $post_id / $parent_id");
                                } else {
                                    WPLA()->logger->info("Skipped non-existing variation $post_id / $parent_id");
                                }

                                // notify WP-Lister for eBay (and other plugins)
                                if ( $parent_id ) do_action( 'wpla_inventory_status_changed', $parent_id ); // trigger stock update for parent variation as well

                                // Clear cache/transients
                                // wc_delete_product_transients( $parent_id );
                            }

                        }

                    } // if $post_id

                } // if $listing_item

            } // foreach report row

        // build response
        $response = new stdClass();
        $response->job  	= $job;
        $response->task 	= $task;
        $response->errors   = $errors;
        $response->success  = true;

        return $response;
    } // processManageFBAReportPage()

	// process single FBA Inventory Health report page
	public static function processFBAInventoryHealthReportPage( $report, $rows, $job, $task ) {
		$listingsModel = new WPLA_ListingsModel();

		if ( $task['from_row'] == 1 ) {

			// reset quality info for all products using this account
			$account_id = $report->account_id;
			$update_data = array(
				'fba_inv_age_90'       => null,
				'fba_inv_age_180'      => null,
				'fba_inv_age_270'      => null,
				'fba_inv_age_365'      => null,
				'fba_inv_age_365_plus' => null,
				'fba_qty_ltsf_12'      => null,
				'fba_fee_ltsf_12'      => null,
			);
			$listingsModel->updateWhere( array( 'account_id' => $account_id ), $update_data );

		}

        // process rows
		foreach ($rows as $row) {

			$sku = $row['sku'];

			$update_data = array(
				'fba_inv_age_90' 		=> $row['inv-age-0-to-90-days'],
				'fba_inv_age_180' 		=> $row['inv-age-91-to-180-days'],
				'fba_inv_age_270' 		=> $row['inv-age-181-to-270-days'],
				'fba_inv_age_365' 		=> $row['inv-age-271-to-365-days'],
				'fba_inv_age_365_plus' 	=> $row['inv-age-365-plus-days'],
				'fba_qty_ltsf_12' 		=> $row['qty-to-be-charged-ltsf-12-mo'],
				'fba_fee_ltsf_12' 		=> $row['projected-ltsf-12-mo'],
				// 'fba_inv_age'   => serialize( $inv_age_info ),
			);

			if ( $sku ) $listingsModel->updateWhere( array( 'sku'  => $sku ), $update_data );
		}

		// build response
		$response = new stdClass();
		$response->job  	= $job;
		$response->task 	= $task;
		$response->errors   = '';
		$response->success  = true;

		return $response;
	} // processFBAInventoryHealthReportPage()

	// process single Quality report page
	public static function processQualityReportPage( $report, $rows, $job, $task ) {
		$listingsModel = new WPLA_ListingsModel();

		// reset quality info for all products using this account
		$account_id = $report->account_id;
		$update_data = array(
			'quality_status' => null,
			'quality_info'   => null,
		);
		$listingsModel->updateWhere( array( 'account_id' => $account_id ), $update_data );


        // process rows
		foreach ($rows as $row) {

			$asin         = $row['asin'];
			$sku          = $row['sku'];

			$quality_info = array(
				'sku'           => $row['sku'],
				'product-name'  => $row['product-name'],
				'asin'          => $row['asin'],
				'field-name'    => $row['field-name'],
				'alert-type'    => $row['alert-type'],
				'current-value' => $row['current-value'],
				'last-updated'  => $row['last-updated'],
				'alert-name'    => isset($row['alert-name']) ? $row['alert-name'] : '',
				'status'        => isset($row['status']) ? $row['status'] : '',
				'explanation'   => $row['explanation'],
				'ts'            => time(),
			);

			$update_data = array(
				// 'quality_status' => $row['status'],
				'quality_status' => $quality_info['alert-name'],
				'quality_info'   => serialize( $quality_info ),
			);

			if ( $asin ) $listingsModel->updateWhere( array( 'asin' => $asin ), $update_data );
			if ( $sku  ) $listingsModel->updateWhere( array( 'sku'  => $sku  ), $update_data );
		}

		// build response
		$response = new stdClass();
		$response->job  	= $job;
		$response->task 	= $task;
		$response->errors   = '';
		$response->success  = true;

		return $response;
	} // processQualityReportPage()


	// convert item-condition to condition_type: 11 => New
	public static function convertNumericConditionIdToType( $condition_id ) {

		$map = array(
			1  => 'UsedLikeNew'           ,
			2  => 'UsedVeryGood'          ,
			3  => 'UsedGood'              ,
			4  => 'UsedAcceptable'        ,
			5  => 'CollectibleLikeNew'    ,
			6  => 'CollectibleVeryGood'   ,
			7  => 'CollectibleGood'       ,
			8  => 'CollectibleAcceptable' ,
			10 => 'Refurbished'           ,
			11 => 'New'					  ,
		);
		$amazon_condition_type = isset( $map[ $condition_id ] ) ? $map[ $condition_id ] : '';

		return $amazon_condition_type;
	}


	// render import preview table
	public static function render_import_preview_table( $wpl_rows, $wpl_report_summary, $wpl_query = false, $wpl_pagenum = false ) {
	    if ( ! is_array($wpl_rows) || ( ! sizeof($wpl_rows) ) ) return;
	    $row_count = 0;

	    include( WPLA_PATH . '/views/import/preview_import_table.php');

	} // render_import_preview_table()


} // class WPLA_ImportHelper
