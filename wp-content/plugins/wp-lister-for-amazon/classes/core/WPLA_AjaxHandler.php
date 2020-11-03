<?php

class WPLA_AjaxHandler extends WPLA_Core {

	public function config() {

		// called from jobs window
		add_action('wp_ajax_wpla_jobs_load_tasks', 					array( &$this, 'jobs_load_tasks' ) );
		add_action('wp_ajax_wpla_jobs_run_task', 					array( &$this, 'jobs_run_task' ) );
		add_action('wp_ajax_wpla_jobs_complete_job', 				array( &$this, 'jobs_complete_job' ) );

		// called from category tree
		add_action('wp_ajax_wpla_get_amazon_categories_tree',  		array( &$this, 'ajax_get_amazon_categories_tree' ) );

		// logfile viewer
		add_action('wp_ajax_wpla_tail_log', 						array( &$this, 'ajax_wpla_tail_log' ) );

		// product matcher
		add_action('wp_ajax_wpla_match_product', 					array( &$this, 'ajax_wpla_match_product' ) );
		add_action('wp_ajax_wpla_show_product_matches', 			array( &$this, 'ajax_wpla_show_product_matches' ) );

		// profile selector
		add_action('wp_ajax_wpla_select_profile', 					array( &$this, 'ajax_wpla_select_profile' ) );
		add_action('wp_ajax_wpla_show_profile_selection', 			array( &$this, 'ajax_wpla_show_profile_selection' ) );

		// load market details
		add_action('wp_ajax_wpla_load_market_details', 				array( &$this, 'ajax_wpla_load_market_details' ) );

		// load feed template data
		add_action('wp_ajax_wpla_load_template_data_for_profile', 	array( &$this, 'ajax_wpla_load_template_data_for_profile' ) );
		add_action('wp_ajax_wpla_load_template_data_for_product', 	array( &$this, 'ajax_wpla_load_template_data_for_product' ) );

		// import preview
		add_action('wp_ajax_wpla_get_import_preview_table',       	array( &$this, 'ajax_wpla_get_import_preview_table' ) );

		// apply lowest price
		add_action('wp_ajax_wpla_use_lowest_price',   				array( &$this, 'ajax_wpla_use_lowest_price' ) );
		add_action('wp_ajax_wpla_apply_lowest_price', 				array( &$this, 'ajax_wpla_apply_lowest_price' ) );

		// repricing tool
		add_action('wp_ajax_wpla_update_price_column', 				array( &$this, 'ajax_wpla_update_price_column' ) );

		// pricing changelog / details info
		add_action('wp_ajax_wpla_view_pnq_log',      				array( &$this, 'ajax_wpla_view_pnq_log' ) );
		add_action('wp_ajax_wpla_view_pricing_info', 				array( &$this, 'ajax_wpla_view_pricing_info' ) );

	}


	// load import preview table
	public function ajax_wpla_get_import_preview_table() {

        // check nonce and permissions
        check_admin_referer( 'wpla_ajax_nonce' );
		if ( ! current_user_can('manage_amazon_listings') ) return;

		$query = wpla_clean($_REQUEST['query']);
		$page  = wpla_clean($_REQUEST['pagenum']);

		// analyse report content
		$report    = new WPLA_AmazonReport( wpla_clean($_REQUEST['report_id']) );
		$account   = new WPLA_AmazonAccount( $report->account_id );
		$summary   = WPLA_ImportHelper::analyzeReportForPreview( $report );

		WPLA_ImportHelper::render_import_preview_table( $report->get_data_rows( $query ), $summary, $query, $page );

		exit();
	} // ajax_wpla_get_import_preview_table()


	// show pricing details for listing
	public function ajax_wpla_view_pricing_info() {
		if ( ! current_user_can('manage_amazon_listings') ) return;

		$listing_id = wpla_clean($_REQUEST['id']);
		if ( ! $listing_id ) return;

		// get all feed IDs:
		$lm      = new WPLA_ListingsModel();
		$listing = $lm->getItem( $listing_id );
		// echo "<pre>";print_r($listing);echo"</pre>";#die();

		// load template
		$tpldata = array(
			'listing_id'		=> $listing_id,
			'item'				=> $listing,
		);

		@WPLA_Page::display( 'ajax/pricing_details', $tpldata );
		exit();
	} // ajax_wpla_view_pricing_info()



	// show pricing changelog for SKU
	public function ajax_wpla_view_pnq_log() {
		if ( ! current_user_can('manage_amazon_listings') ) return;

		$sku = wpla_clean($_REQUEST['sku']);
		if ( ! $sku ) return;

		// get all feed IDs:
		$feed_ids = WPLA_AmazonFeed::getAllPnqFeedsForSKU( $sku );
		$log_rows = array();
		$feed_currency_format = get_option( 'wpla_feed_currency_format', 'auto' );

		// fetch all data rows for this SKU
		foreach ( $feed_ids as $feed_id ) {
			$feed     = new WPLA_AmazonFeed( $feed_id );
			$data_row = $feed->getDataRowForSKU( $sku );

			// add details for template view
			$data_row['feed_id']                 = $feed->id;
			$data_row['FeedSubmissionId']        = $feed->FeedSubmissionId;
			$data_row['SubmittedDate']           = $feed->SubmittedDate;
			$data_row['CompletedProcessingDate'] = $feed->CompletedProcessingDate;
			$data_row['FeedProcessingStatus']    = $feed->FeedProcessingStatus;

			// maybe convert decimal comma to decimal point
			if ( $feed_currency_format == 'auto' ) {
				$data_row['price']                        = str_replace( ',', '.', $data_row['price'] );
				$data_row['minimum-seller-allowed-price'] = str_replace( ',', '.', $data_row['minimum-seller-allowed-price'] );
				$data_row['maximum-seller-allowed-price'] = str_replace( ',', '.', $data_row['maximum-seller-allowed-price'] );
			}

			$log_rows[] = $data_row;
		}
		// echo "<pre>";print_r($log_rows);echo"</pre>";#die();

		// load template
		$tpldata = array(
			'sku'						=> $sku,
			'log_rows'					=> $log_rows
		);

		@WPLA_Page::display( 'ajax/pnq_log', $tpldata );
		exit();
	} // ajax_wpla_view_pnq_log()



	// show profile selection
	public function ajax_wpla_show_profile_selection() {
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// fetch profiles
		$profiles = WPLA_AmazonProfile::getAll();

		// load template
		$tpldata = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,
			'profiles'					=> $profiles,
			'form_action'				=> 'admin.php?page='.self::ParentMenuId
		);

		@WPLA_Page::display( 'profile/select_profile', $tpldata );
		exit();

	} // ajax_wpla_show_profile_selection()


	// match product
	public function ajax_wpla_select_profile() {
        // check nonce and permissions
        check_admin_referer( 'wpla_ajax_nonce' );
		if ( ! current_user_can('manage_amazon_listings') ) return;


		if ( isset( $_REQUEST['profile_id'] ) && isset( $_REQUEST['product_ids'] ) ) {

			$profile_id  = wpla_clean($_REQUEST['profile_id']);
			$product_ids = wpla_clean($_REQUEST['product_ids']);
			$select_mode = wpla_clean($_REQUEST['select_mode']);
			$default_account_id = get_option( 'wpla_default_account_id', 1 );

			$lm = new WPLA_ListingsModel();
			if ( 'products' == $select_mode ) {

				// prepare new listings from products
				$response = $lm->prepareListings( $product_ids, $profile_id );

			} elseif ( 'listings' == $select_mode ) {

				// remove profile?
				if ( $profile_id == '_NONE_' ) {

					$lm->removeProfileFromListings( $product_ids );

					// build response
					$response = new stdClass();
					$response->success        = true;
					$response->msg 			  = sprintf( __( 'Profile was removed from %s items.', 'wp-lister-for-amazon' ), count($product_ids) );
					$this->returnJSON( $response );
					exit();
				}

				// change profile for existing listings
				// $profile = WPLA_AmazonProfile::getProfile( $profile_id ); // doesn't work
				$profile = new WPLA_AmazonProfile( $profile_id );
				$items = $lm->applyProfileToListings( $profile, $product_ids );

				// build response
				$response = new stdClass();
				// $response->success     = $prepared_count ? true : false;
				$response->success        = true;
				$response->msg 			  = sprintf( __( 'Profile "%s" was applied to %s items.', 'wp-lister-for-amazon' ), $profile->profile_name, count($items) );
				$this->returnJSON( $response );
				exit();
			} else {
				die('invalid select mode: '.$select_mode);
			}

			if ( $response->success ) {

				// store ASIN as product meta
				// update_post_meta( $post_id, '_wpla_asin', $asin );

				$response->msg = sprintf( __( '%s product(s) have been prepared.', 'wp-lister-for-amazon' ), $response->prepared_count );
				if ( $response->skipped_count )
					$response->msg = sprintf( __( '%s product(s) have been prepared and %s products were skipped.', 'wp-lister-for-amazon' ), $response->prepared_count, $response->skipped_count );
				if ( ! $response->prepared_count )
					$response->msg = sprintf( __( '%s products have been skipped.', 'wp-lister-for-amazon' ), $response->skipped_count );

				if ( $response->errors )
					$response->msg .= '<br>'.join('<br>',$response->errors);
				if ( $response->warnings )
					$response->msg .= '<br>'.join('<br>',$response->warnings);


				$this->returnJSON( $response );
				exit();

			} else {
				if ( isset($lm->lastError) ) echo $lm->lastError."\n";
				echo "Failed to prepare product!";
				exit();
			}

		}

	} // ajax_wpla_select_profile()



	// apply lowest price to product
	public function ajax_wpla_apply_lowest_price() {
        // check nonce and permissions
        check_admin_referer( 'wpla_ajax_nonce' );
		if ( ! current_user_can('manage_amazon_listings') ) return;

		if ( ! current_user_can( 'edit_products' ) ) {
			echo "You're not allowed to do this.";
			exit();
		}

		if ( isset( $_REQUEST['post_id'] ) && isset( $_REQUEST['new_price'] ) ) {

			$post_id           = wpla_clean($_REQUEST['post_id']);
			$listing_id        = wpla_clean($_REQUEST['listing_id']);
			$new_price         = wpla_clean($_REQUEST['new_price']);
			$price_type_select = wpla_clean($_REQUEST['price_type_select']);

			// apply new price
			if ( 'sale' == $price_type_select ) {
				update_post_meta( $post_id, '_price', $new_price );
				update_post_meta( $post_id, '_sale_price', $new_price );
			} else {
				update_post_meta( $post_id, '_price', $new_price );
				update_post_meta( $post_id, '_regular_price', $new_price );
			}

			update_option( 'wpla_default_lowest_price_selection', $price_type_select );

			// mark item as modified - and reload status
			$lm = new WPLA_ListingsModel();
			$lm->markItemAsModified( $post_id );
			$listing = $lm->getItem( $listing_id, OBJECT );

			if ( $listing ) {

				// build response
				$response = new stdClass();
				$response->post_id        = $post_id;
				$response->listing_id     = $listing_id;
				$response->listing_status = $listing->status;
				$response->error_msg      = false;
				$response->success        = true;

				$this->returnJSON( $response );
				exit();

			} else {
				if ( isset($lm->lastError) ) echo $lm->lastError."\n";
				echo "Failed to match product!";
			}

		}

	} // ajax_wpla_apply_lowest_price()


	// show UI to use lowest price for WooCommerce product
	public function ajax_wpla_use_lowest_price() {
        // check nonce and permissions
        check_admin_referer( 'wpla_ajax_nonce' );
		if ( ! current_user_can('manage_amazon_listings') ) return;

		if ( isset( $_REQUEST['id'] ) ) {

			$lm = new WPLA_ListingsModel();
			$listing      = $lm->getItem( wpla_clean($_REQUEST['id']) );
			$product      = WPLA_ProductWrapper::getProduct( $listing['post_id'] );
			$pricing_info = maybe_unserialize( $listing['pricing_info'] );
			// echo "<pre>";print_r($pricing_info);echo"</pre>";
			// echo "<pre>";print_r($listing);echo"</pre>";

			if ( $listing ) {

				if ( is_array( $pricing_info ) ) {

					// load template
					$tpldata = array(
						'plugin_url'				=> self::$PLUGIN_URL,
						'message'					=> $this->message,
						'pricing_info'			    => $pricing_info,
						'listing'				    => $listing,
						'product'				    => $product,
						'post_id'				    => $listing['post_id'],
						'lowest_price'			    => $listing['lowest_price'],
						'listing_id'				=> wpla_clean($_REQUEST['id']),
						// 'query_select'				=> isset($_REQUEST['query_select']) ? wpla_clean($_REQUEST['query_select']) : false,
						'form_action'				=> 'admin.php?page='.self::ParentMenuId
					);

					@WPLA_Page::display( 'apply_lowest_price', $tpldata );

				} else {
					$errors  = sprintf( __( 'There were no products found for query %s.', 'wp-lister-for-amazon' ), $query );
					echo $errors;
				}
				exit();

			} else {
				echo "invalid product";
			}

		}

	} // ajax_wpla_use_lowest_price()



	// update product price
	public function ajax_wpla_update_price_column() {
	    // check nonce and permissions
        check_admin_referer( 'wpla_ajax_nonce' );
		if ( ! current_user_can('manage_amazon_listings') ) return;

		if ( ! current_user_can( 'edit_products' ) ) return 'not allowed!';

		if ( isset( $_REQUEST['listing_id'] ) ) {

			$lm         = new WPLA_ListingsModel();
			$listing_id = wpla_clean($_REQUEST['listing_id']);
			$column     = wpla_clean($_REQUEST['column']);
			$value      = trim( wpla_clean($_REQUEST['value']) );
			$value      = str_replace( ',', '.', $value ); // convert decimal comma
			$value      = ( is_numeric( $value ) && $value >= 0 ) ? number_format( $value, 2, '.', '' ) : $value;

			// $value can only be of numeric type or the word "delete"
            // for min_price and max_price columns
            if ( in_array( $column, array('min_price', 'max_price') ) && !is_numeric( $value ) && strtolower($value) != 'delete' ) {
                $value = null;
            }

			// check column
			if ( ! in_array( $column, array('price','sale_price','min_price','max_price','ebay_price','msrp_price') ) ) return 'invalid column!';

			// load listing item
			$item       = $lm->getItem( $listing_id, OBJECT );

			// update listing table
			$data = array(
				$column => $value,
				'pnq_status' => 1, // mark as changed
			);
			if ( $column != 'sale_price' ) {
				$lm->updateWhere( array( 'id' => $listing_id ), $data );
			}

			// update product
			if ( $column == 'price' ) {
	        	update_post_meta( $item->post_id, '_amazon_price', $value );
			}
			if ( $column == 'sale_price' ) {
	        	update_post_meta( $item->post_id, '_sale_price', $value );
			}
			if ( $column == 'min_price' ) {
	        	update_post_meta( $item->post_id, '_amazon_minimum_price', $value );
			}
			if ( $column == 'max_price' ) {
	        	update_post_meta( $item->post_id, '_amazon_maximum_price', $value );
			}
			if ( $column == 'ebay_price' ) {
	        	update_post_meta( $item->post_id, '_ebay_start_price', $value );
	        	do_action( 'wplister_product_has_changed', $item->parent_id ? $item->parent_id : $item->post_id );
			}
			if ( $column == 'msrp_price' ) {
			    // Variations use _msrp while simple/parents use _msrp_price
                if ( WPLA_ProductWrapper::getVariationParent( $item->post_id ) ) {
                    update_post_meta( $item->post_id, '_msrp', $value );
                } else {
                    update_post_meta( $item->post_id, '_msrp_price', $value );
                }
            }

			// build response
			$response = new stdClass();
			$response->success = true;
			//$response->new_value = $this->number_format( $msrp_price, 2 );
			$this->returnJSON( $response );
			exit();
		}

	} // ajax_wpla_update_price_column()



	// show matching products
	public function ajax_wpla_show_product_matches() {
        // check nonce and permissions
        check_admin_referer( 'wpla_ajax_nonce' );
		if ( ! current_user_can('manage_amazon_listings') ) return;

		if ( isset( $_REQUEST['id'] ) ) {

			$product = WPLA_ProductWrapper::getProduct( wpla_clean($_REQUEST['id']) );

			if ( $product ) {
                $product_post = get_post( wpla_clean($_REQUEST['id']) );
				$product_attributes	= WPLA_ProductWrapper::getAttributes( wpla_get_product_meta( $product, 'parent_id' ), true );

			    $wpl_default_matcher_selection = get_option( 'wpla_default_matcher_selection', 'title' );
			    switch ($wpl_default_matcher_selection) {
			    	case 'title':
			    		# product title
						$query = $product_post->post_title;
			    		break;

			    	case 'sku':
			    		# product sku
						$query = wpla_get_product_meta( $product, 'sku' );
			    		break;

			    	default:
			    		# else check for attributes
			    		foreach ($product_attributes as $attribute_label => $attribute_value) {
			    			if ( $attribute_label == $wpl_default_matcher_selection )
			    				$query = $attribute_value;
			    		}
			    		break;
			    }

			    // fall back to title when query is empty
			    if ( empty($query) ) $query = $product_post->post_title;

			    // handle custom query
				if ( isset( $_REQUEST['query'] ) ) $query = trim( wpla_clean($_REQUEST['query']) );

                $query = apply_filters( 'wpla_product_matches_request_query', $query, $wpl_default_matcher_selection, wpla_clean($_REQUEST['id']) );

				$default_account_id = get_option( 'wpla_default_account_id', 1 );
				$account = WPLA_AmazonAccount::getAccount( $default_account_id );
				if ( ! $account ) {
					echo "<br>You need to select a default account to be used for matching products.";
					exit();
				}

				// get product attributes - if possible from cache
				$transient_key = 'wpla_product_match_results_'.sanitize_key( $query );
				$products = get_transient( $transient_key );
				if ( empty( $products ) ){
					// call API
					$api      = new WPLA_AmazonAPI( $account->id );
					$products = $api->listMatchingProducts( $query );
					if ( is_array( $products ) ) {

						// get lowest prices
						$products = $this->populateMatchesWithLowestPrices( $products, $account );

						// save cache
						set_transient( $transient_key, $products, 300 );
					}
					// echo "<pre>";print_r($transient_key);echo"</pre>";#die();
				}
				// echo "<pre>AJAX:";print_r($products);echo"</pre>";die();

				// get market / site domain - for "view" links
	            $market  = new WPLA_AmazonMarket( $account->market_id );

				if ( is_array( $products ) )  {

					// load template
					$tpldata = array(
						'plugin_url'				=> self::$PLUGIN_URL,
						'message'					=> $this->message,
						'query'						=> $query,
						'query_product'				=> $product,
						'query_product_attributes'	=> $product_attributes,
						'products'					=> $products,
						'market_url'				=> $market->url,
						'post_id'					=> wpla_clean($_REQUEST['id']),
						'query_select'				=> isset($_REQUEST['query_select']) ? wpla_clean($_REQUEST['query_select']) : false,
						'form_action'				=> 'admin.php?page='.self::ParentMenuId
					);

					@WPLA_Page::display( 'match_product', $tpldata );

				// } elseif ( $product->Error->Message ) {
				// 	$errors  = sprintf( __( 'There was a problem fetching product details for %s.', 'wp-lister-for-amazon' ), $product->post->post_title ) .'<br>Error: '. $reports->Error->Message;
				} else {
					$errors  = sprintf( __( 'There were no products found for query %s.', 'wp-lister-for-amazon' ), $query );
					echo $errors;
				}
				exit();

			} else {
				echo "invalid product";
			}

		}

	}

	// fetch lowest prices for product matches
	public function populateMatchesWithLowestPrices( $products, $account ) {

		// build array of ASINs
		$listing_ASINs = array();
    	foreach ($products as $product) {
    		if ( sizeof($listing_ASINs) == 20 ) continue;
    		$listing_ASINs[] = $product->ASIN;
    	}

    	if ( ! empty($listing_ASINs) ) {

			$api     = new WPLA_AmazonAPI( $account->id );
			$result  = $api->getCompetitivePricingForId( $listing_ASINs );

			$ASIN_to_lowest_price = array();
			foreach ( $result->products as $asin => $product ) {
				$lowest_price = PHP_INT_MAX;
				foreach ( $product->prices as $price ) {
					// $lowest_price = $price->LandedPrice;
					// $condition    = $price->condition;
					// $subcondition = $price->subcondition;
					// $shipping_fee = $price->Shipping;

					if ( $price->LandedPrice < $lowest_price ) {
						if ( $price->condition == 'New' ) {
							$lowest_price = $price->LandedPrice;
						}
					}

				} // each pricing node

				if ( $lowest_price != PHP_INT_MAX ) {
					$ASIN_to_lowest_price[ $product->ASIN ] = $lowest_price;
				}

			} // each product

		}

    	foreach ($products as & $product) {
    		if ( isset( $ASIN_to_lowest_price[ $product->ASIN ] ) ) {
    			$product->lowest_price = $ASIN_to_lowest_price[ $product->ASIN ];
    		} else {
    			$product->lowest_price = false;
    		}
    	}
    	// echo "<pre>FINAL: ";print_r($products);echo"</pre>";#die();

		return $products;
	} // populateMatchesWithLowestPrices()

	// match product
	public function ajax_wpla_match_product() {
        // check nonce and permissions
        check_admin_referer( 'wpla_ajax_nonce' );
		if ( ! current_user_can('manage_amazon_listings') ) return;

		if ( isset( $_REQUEST['post_id'] ) && isset( $_REQUEST['asin'] ) ) {

			$asin    = trim( wpla_clean($_REQUEST['asin']) );
			$post_id = wpla_clean($_REQUEST['post_id']);
			$default_account_id = get_option( 'wpla_default_account_id', 1 );

			$lm = new WPLA_ListingsModel();
			$success = $lm->insertMatchedProduct( $post_id, $asin, $default_account_id );

			if ( $success ) {

				// store ASIN as product meta
				update_post_meta( $post_id, '_wpla_asin', $asin );

				// build response
				$response = new stdClass();
				$response->post_id		= $post_id;
				$response->listing_id	= isset($lm->last_insert_id) ? $lm->last_insert_id : false;
				$response->error_msg	= isset($lm->lastError) ? $lm->lastError : false;
				$response->url   		= 'http://www.amazon.com/dp/'.$asin;
				$response->success  	= true;

				$this->returnJSON( $response );
				exit();

			} else {
				if ( isset($lm->lastError) ) echo $lm->lastError."\n";
				echo "Failed to match product!";
			}

		}

	}


	// load market details
	public function ajax_wpla_load_market_details() {
        // check nonce and permissions
        check_admin_referer( 'wpla_ajax_nonce' );
		if ( ! current_user_can('manage_amazon_listings') ) return;

		if ( isset( $_REQUEST['market_id'] ) ) {

			$market = new WPLA_AmazonMarket( wpla_clean($_REQUEST['market_id']) );

			if ( $market ) {

				$region_code = 'NA';
				if ( 'Europe'         == $market->group_title ) $region_code = 'EU';
				if ( 'Asia / Pacific' == $market->group_title ) $region_code = 'AS';

				// build response
				$response = new stdClass();
				$response->url            = $market->url;
				$response->code           = $market->code;
				$response->signin_url     = $market->getSignInUrl();
				$response->developer_id   = $market->developer_id;
				$response->marketplace_id = $market->marketplace_id;
				$response->region_title   = $market->group_title;
				$response->region_code    = $region_code;
				$response->success        = true;

				$this->returnJSON( $response );
				exit();

			} else {
				echo "invalid marketplace id";
			}

		}

	} // ajax_wpla_load_market_details()


	// load feed template data for profile
	public function ajax_wpla_load_template_data_for_profile() {
        // check nonce and permissions
        check_admin_referer( 'wpla_ajax_nonce' );
		if ( ! current_user_can('manage_amazon_listings') ) return;

		if ( isset( $_REQUEST['id'] ) ) {

			$template = new WPLA_AmazonFeedTemplate( wpla_clean($_REQUEST['id']) );
			$profile  = new WPLA_AmazonProfile( wpla_clean($_REQUEST['profile_id']) );

			if ( $template ) {

				// build settings form
				$data = array();
				$data['fields'] = $template->getFieldData();
				$data['values'] = $template->getFieldValues();
				$data['profile_field_data'] = $profile ? maybe_unserialize( $profile->fields ) : array();
				$data['product_attributes'] = WPLA_ProductWrapper::getAttributeTaxonomies();

				// check if account is registered brand
				$account = $profile && ! empty($profile->id) ? new WPLA_AmazonAccount( $profile->account_id ) : false;
				$data['is_reg_brand'] = $account ? $account->is_reg_brand : false;

                $data = apply_filters( 'wpla_profile_template_data', $data, $profile, $template );

				@WPLA_Page::display( 'profile/edit_field_data', $data );
				exit();

			} else {
				echo "invalid template id";
			}

		}

	} // ajax_wpla_load_template_data_for_profile()

	// load feed template data for product
	public function ajax_wpla_load_template_data_for_product() {
        // check nonce and permissions
        check_admin_referer( 'wpla_ajax_nonce' );
		if ( ! current_user_can('manage_amazon_listings') ) return;

		if ( isset( $_REQUEST['tpl_id'] ) ) {

			$template   = new WPLA_AmazonFeedTemplate( wpla_clean($_REQUEST['tpl_id']) );
			$post_id    = wpla_clean($_REQUEST['post_id']);
			$field_data = get_post_meta( $post_id, '_wpla_custom_feed_columns', true );

			if ( $template ) {

				// build settings form
				$data = array();
				$data['fields'] = $template->getFieldData();
				$data['values'] = $template->getFieldValues();
				$data['profile_field_data'] = is_array($field_data) ? $field_data : array();
				$data['product_attributes'] = WPLA_ProductWrapper::getAttributeTaxonomies();

				@WPLA_Page::display( 'profile/edit_field_data', $data );
				exit();

			} else {
				echo "invalid template id";
			}

		}

	} // ajax_wpla_load_template_data_for_product()


	// load browse tree data
	public function ajax_get_amazon_categories_tree() {
		if ( ! current_user_can('manage_amazon_listings') ) return;

		$path           = wpla_clean($_POST['dir']);	// example: /0/20081/37903/ - /0/ means root
		$parent_node_id = basename( $path );
		$categories     = $this->getChildrenOfCategory( $parent_node_id );
		// $categories = apply_filters( 'wpla_get_amazon_categories_node', $categories, $parent_node_id, $path );

		$show_node_ids = get_option( 'wpla_show_browse_node_ids' );

		if( count($categories) > 0 ) {
			echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">"."\n";

			// first show all folders
			foreach( $categories as $cat ) {
				if ( $cat['leaf'] == '0' ) {

					$node_id    = $cat['node_id'];
					$node_label = $cat['node_name'];
					$node_slug  = $path . $cat['node_id'];
					$keyword    = $cat['keyword'];

					if ( $path == '/0/' ) {
						$node_label .= ' ('. WPLA_AmazonMarket::getMarketCode( $cat['site_id'] ) .')';
					} elseif ( $show_node_ids ) {
						$node_label .= ' ('.$cat['node_id'].')';
						if ( $keyword ) $node_label .= ' ('.$keyword.')';
					}

					echo '<li class="directory collapsed"><a href="#" id="wpla_node_id_'.$node_id.'" rel="'
						. $node_slug . '/" data-keyword="'.$keyword.'" >'. $node_label . '</a></li>'."\n";
				}
			}

			// then show all leaf nodes
			foreach( $categories as $cat ) {
				if ( $cat['leaf'] == '1' ) {

					$node_id    = $cat['node_id'];
					$node_label = $cat['node_name'];
					$node_slug  = $cat['node_id'] ? $path . $cat['node_id'] : $path . $cat['keyword'];
					$keyword    = $cat['keyword'];

					if ( $show_node_ids ) {
						$node_label .= ' ('.$cat['node_id'].')';
						if ( $keyword ) $node_label .= ' ('.$keyword.')';
					}

					echo '<li class="file ext_txt"><a href="#" id="wpla_node_id_'.$node_id.'" rel="'
						. $node_slug . '" data-keyword="'.$keyword.'" >' . $node_label . '</a></li>'."\n";
				}
			}

			echo "</ul>";
		}
		exit();
	}

	function getChildrenOfCategory( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'amazon_btg';
		$items = $wpdb->get_results("
			SELECT DISTINCT * 
			FROM $table
			WHERE parent_id = '$id'
			ORDER BY node_name ASC
		", ARRAY_A);

		return $items;
	}


	function shutdown_handler() {
		global $wpla_shutdown_handler_enabled;
		if ( ! $wpla_shutdown_handler_enabled ) return;

		// check for fatal error
        $error = error_get_last();
        if ($error['type'] === E_ERROR) {

	        $logmsg  = "<br><br>";
	        $logmsg .= "<b>There has been a fatal PHP error - the server said:</b><br>";
	        $logmsg .= '<span style="color:darkred">'.$error['message']."</span><br>";
	        $logmsg .= "In file: <code>".$error['file']."</code> (line ".$error['line'].")<br>";

	        $logmsg .= "<br>";
	        $logmsg .= "<b>Please contact support in order to resolve this.</b><br>";
	        $logmsg .= "If this error is related to memory limits or timeouts, you need to contact your server administrator or hosting provider.<br>";
	        echo $logmsg;

		}
		// debug all errors
		// echo "<br>Last error: <pre>".print_r($error,1)."</pre>";
	}



	// run single task
	public function jobs_run_task() {

		// check nonce and permissions
	    check_admin_referer( 'wpla_ajax_nonce' );
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// quit if no job name provided
		if ( ! isset( $_REQUEST['job'] ) ) return false;
		if ( ! isset( $_REQUEST['task'] ) ) return false;

		$job  = wpla_clean($_REQUEST['job']);
		$task = wpla_clean($_REQUEST['task']);

		// register shutdown handler
		global $wpla_shutdown_handler_enabled;
		$wpla_shutdown_handler_enabled = true;
		register_shutdown_function( array( $this, 'shutdown_handler' ) );

		WPLA()->logger->info('running task: '.print_r($task,1));

		// handle job name
		switch ( $task['task'] ) {

			// update listing from Amazon (current used for new listings without ASIN)
			case 'updateProduct':

				// init
				$lm      = new WPLA_ListingsModel();
				$listing = $lm->getItem( $task['id'] );
				$account = WPLA_AmazonAccount::getAccount( $listing['account_id'] );
				$api     = new WPLA_AmazonAPI( $account->id );

				// get product attributes
				// $product = $api->getProduct( $listing['asin'] );
				$result = $api->getMatchingProductForId( $listing['sku'], 'SellerSKU' );
				// echo "<pre>";print_r($product);echo"</pre>";#die();
				// echo "<pre>";print_r($product);echo"</pre>";die();

				if ( $result->success )  {

					if ( ! empty( $result->product->ASIN ) ) {

						// update listing attributes
						$listing_id = $listing['id'];
						// $lm->updateItemAttributes( $product, $listing_id );
						// $listing = $lm->getItem( $listing_id ); // update values
						$lm->updateWhere( array( 'id' => $listing_id ), array( 'asin' => $result->product->ASIN ) );
						WPLA()->logger->info('new ASIN for listing #'.$listing['id'] . ': '.$result->product->ASIN );

						// update product
						// $woo = new WPLA_ProductBuilder();
						// $woo->updateProducts( array( $listing ) );

						$success = true;
						$errors  = '';

					} else {
						$errors  = sprintf( __( 'There was a problem fetching product details for %s.', 'wp-lister-for-amazon' ), $listing['asin'] );
						$errors  .= ' The product data received from Amazon was empty.';
						$success = false;
					}

				} elseif ( $result->Error->Message ) {
					$errors  = sprintf( __( 'There was a problem fetching product details for %s.', 'wp-lister-for-amazon' ), $listing['asin'] ) .'<br>Error: '. $result->Error->Message;
					$success = false;
				} else {
					$errors  = sprintf( __( 'There was a problem fetching product details for %s.', 'wp-lister-for-amazon' ), $listing['asin'] );
					$success = false;
				}

				// build response
				$response = new stdClass();
				$response->job  	= $job;
				$response->task 	= $task;
				$response->errors   = empty( $errors ) ? array() : array( array( 'HtmlMessage' => $errors ) );
				$response->success  = $success;

				$this->returnJSON( $response );
				exit();


			// create new WooCommerce product from imported listing
			case 'createProduct':

				// init
				$lm      = new WPLA_ListingsModel();
				// $listing = $lm->getItem( $task['id'] );
				$listing_id = $task['id'];

				// create product
				$ProductsImporter = new WPLA_ProductsImporter();
				$success = $ProductsImporter->createProductFromAmazonListing( $listing_id );
				$error   = $ProductsImporter->lastError;
				$delay   = $ProductsImporter->request_count * 1000; // ms

				// build response
				$response = new stdClass();
				$response->job  	= $job;
				$response->task 	= $task;
				$response->errors   = empty( $error ) ? array() : array( array( 'HtmlMessage' => $error ) );
				$response->success  = $success;
				$response->delay    = $delay;

				$this->returnJSON( $response );
				exit();



			// fetch full product description from Amazon and update WooCommerce product
			case 'fetchFullProductDescription':

				$webHelper = new WPLA_AmazonWebHelper();
				$webHelper->loadListingDetails( $task['id'] );
				// echo "<pre>";print_r($webHelper->images);echo"</pre>";#die();

		        $lm      = new WPLA_ListingsModel();
		        $item    = $lm->getItem( $task['id'] );

				if ( ! empty( $webHelper->description ) ) {

					// update product
                    $post_id = !empty( $item['parent_id'] ) ? $item['parent_id'] : $item['post_id'];
                    $p = wc_get_product( $post_id );

                    if ( $p ) {
                        $p->set_description( trim( $webHelper->description ) );
                        $p->save();
                    } else {
					$post_id = $item['post_id'];
					$post_data = array(
					    'ID'           => $post_id,
					    'post_content' => trim( $webHelper->description )
					);

					wp_update_post( $post_data );
                    }

					$success = true;
					$errors  = '';

				} else {
					$errors  = sprintf( __( 'There was a problem fetching product details for %s.', 'wp-lister-for-amazon' ), $item['asin'] );
					$errors  .= ' The product description received from Amazon was empty.';
					$success = false;
				}

				// build response
				$response = new stdClass();
				$response->job  	= $job;
				$response->task 	= $task;
				$response->errors   = empty( $errors ) ? array() : array( array( 'HtmlMessage' => $errors ) );
				$response->success  = $success;

				$this->returnJSON( $response );
				exit();


			// process Merchant or FBA Report and create / update listings
			case 'processReportPage':

				// process report page - both Merchant and FBA reports
				$response = WPLA_ImportHelper::ajax_processReportPage( $job, $task );

				$this->returnJSON( $response );
				exit();

			// process single row (SKU) Merchant or FBA Report - and create / update listings
			case 'processSingleSkuFromReport':

				// process report page - both Merchant and FBA reports
				$response = WPLA_ImportHelper::ajax_processReportPage( $job, $task, true );

				$this->returnJSON( $response );
				exit();

            case 'applyProfileDelayed':

                $profile_id = $task['profile_id'];
                $offset     = $task['offset'];
                $limit      = $task['limit'];
                $profile    = new WPLA_AmazonProfile( $profile_id );

                $lm     = new WPLA_ListingsModel();
                $items  = $lm->findAllListingsByColumn( $profile_id, 'profile_id' );

                $total_items = sizeof($items);

                // extract batch
                $items = array_slice( $items, $offset, $limit );

                // apply profile to items
                $lm->applyProfileToListings( $profile, $items );

                // reset reminder option when last batch is run
                if ( $offset + $limit >= $total_items ) {
                    update_option( 'wpla_job_reapply_profile_id', '' );
                }

                // build response
                $response = new stdClass();
                $response->job  	= $job;
                $response->task 	= $task;
                $response->errors   = array();
                // $response->errors   = array( array( 'HtmlMessage' => ' Profile was applied to '.sizeof($items).' items ') );
                $response->success  = true;

                $this->returnJSON( $response );
                exit();

			default:
				// echo "unknown task";
				// exit();
		}

	}

	// load task list
	public function jobs_load_tasks() {

		// check nonce and permissions
	    check_admin_referer( 'wpla_ajax_nonce' );
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// quit if no job name provided
		if ( ! isset( $_REQUEST['job'] ) ) return false;
		$jobname = wpla_clean($_REQUEST['job']);

		// check if an array of listing IDs was provided
        $lm = new WPLA_ListingsModel();
		$listing_ids = ( isset( $_REQUEST['item_ids'] ) && is_array( $_REQUEST['item_ids'] ) ) ? wpla_clean($_REQUEST['item_ids']) : false;
		if ( $listing_ids )
	        $items = $lm->getItemsByIdArray( $listing_ids );

		// register shutdown handler
		global $wpla_shutdown_handler_enabled;
		$wpla_shutdown_handler_enabled = true;
		register_shutdown_function( array( $this, 'shutdown_handler' ) );

		// handle job name
		switch ( $jobname ) {

			case 'updateProductsWithoutASIN':

				// get prepared items
		        $sm = new WPLA_ListingsModel();
		        $items = $sm->getAllOnlineWithoutASIN();

		        // create job from items and send response
		        $response = $this->_create_bulk_listing_job( 'updateProduct', $items, $jobname );
				$this->returnJSON( $response );
				exit();

			case 'createAllImportedProducts':

				// get prepared items
		        $sm = new WPLA_ListingsModel();
		        $items = $sm->getAllImported();

				// DEV: limit to 10 tasks at a time ***
		        // $items = array_slice($items, 0, 10, true);

		        // create job from items and send response
		        $response = $this->_create_bulk_listing_job( 'createProduct', $items, $jobname );
				$this->returnJSON( $response );
				exit();

			case 'processAmazonReport':

				// get report
				$id = wpla_clean($_REQUEST['item_id']);
		        $report = new WPLA_AmazonReport( $id );
		        $rows = $report->get_data_rows();
		        $rows_count = sizeof( $rows );

		        $page_size = 500;
		        $number_of_pages = intval( $rows_count / $page_size ) + 1;

		        $items = array();
		        if ( $number_of_pages > 0 )
		        for ($page=0; $page < $number_of_pages; $page++) {
		        	$from_row = ( $page * $page_size ) + 1;
		        	$to_row   = ( $page + 1 ) * $page_size;
		        	if ( $to_row > $rows_count ) $to_row = $rows_count;
		        	$items[] = array(
						'id'       => $id,
						'page'     => $page,
						'from_row' => $from_row,
						'to_row'   => $to_row,
						'title'    => 'Processing rows '.$from_row.' to '.$to_row
		        	);
		        }

		        // create job from items and send response
		        $response = $this->_create_bulk_listing_job( 'processReportPage', $items, $jobname );
				$this->returnJSON( $response );
				exit();

			case 'processRowsFromAmazonReport':

				$id   = wpla_clean($_REQUEST['report_id']);
				$skus = wpla_clean($_REQUEST['sku_list']);

				foreach ( $skus as $sku ) {
		        	$items[] = array(
						'id'       => $id,
						'sku'      => $sku,
						'title'    => 'Processing SKU '.$sku
		        	);
		        }

		        // create job from items and send response
		        $response = $this->_create_bulk_listing_job( 'processSingleSkuFromReport', $items, $jobname );
				$this->returnJSON( $response );
				exit();


			case 'fetchProductDescription':

		        // create job from items and send response
		        $response = $this->_create_bulk_listing_job( 'fetchFullProductDescription', $items, $jobname );
				$this->returnJSON( $response );
				exit();

            case 'runDelayedProfileApplication':

                // get items using given profile
                $profile_id = get_option('wpla_job_reapply_profile_id' );
                if ( ! $profile_id ) return;

                $lm = new WPLA_ListingsModel();
                $items  = $lm->findAllListingsByColumn( $profile_id, 'profile_id' );

                $total_items = sizeof($items);
                $batch_size  = get_option( 'wpla_apply_profile_batch_size', 1000 );
                $tasks       = array();

                // echo "<pre>profile_id: ";echo $profile_id;echo"</pre>";
                // echo "<pre>total: ";echo $total_items;echo"</pre>";die();

                for ( $page=0; $page < ($total_items / $batch_size); $page++ ) {

                    $from = $page * $batch_size + 1;
                    $to   = $page * $batch_size + $batch_size;
                    $to   = min( $to, $total_items );

                    // add task - load user specific details
                    $tasks[] = array(
                        'task'        => 'applyProfileDelayed',
                        'displayName' => 'Apply profile to items '.$from.' to '.$to,
                        'profile_id'  => $profile_id,
                        'offset'      => $page * $batch_size,
                        'limit'       => $batch_size,
                    );

                }

                // build response
                $response = new stdClass();
                $response->tasklist    = $tasks;
                $response->total_tasks = count( $tasks );
                $response->error       = '';
                $response->success     = true;

                // create new job
                $newJob = new stdClass();
                $newJob->jobname = $jobname;
                $newJob->tasklist = $tasks;
                $job = new WPLA_JobsModel( $newJob );
                $response->job_key = $job->key;

                $this->returnJSON( $response );
                exit();

			default:
				// echo "unknown job";
				// break;
		}
		// exit();

	} // jobs_load_tasks()

	// create bulk listing job
	public function _create_bulk_listing_job( $taskname, $items, $jobname ) {

		// create tasklist
        $tasks = array();
        foreach( $items as $item ) {
			WPLA()->logger->info('adding task for item #'.$item['id'] . ' - '.@$item['listing_title']);
			// $tasks = $this->_prepare_sub_tasks( $item, $taskname, $tasks );

			$task = array(
				'task'        => $taskname,
				'displayName' => isset( $item['listing_title'] ) ? $item['listing_title'] : $item['title'],
				'id'          => $item['id']
			);
			if ( isset( $item['sku']      ) ) $task['sku']      = $item['sku'];
			if ( isset( $item['page']     ) ) $task['page']     = $item['page'];
			if ( isset( $item['to_row']   ) ) $task['to_row']   = $item['to_row'];
			if ( isset( $item['from_row'] ) ) $task['from_row'] = $item['from_row'];
			$tasks[] = $task;
        }

		// build response
		$response = new stdClass();
		$response->tasklist = $tasks;
		$response->total_tasks = count( $tasks );
		$response->error    = '';
		$response->success  = true;

		// create new job
		$newJob = new stdClass();
		$newJob->jobname = $jobname;
		$newJob->tasklist = $tasks;
		$job = new WPLA_JobsModel( $newJob );
		$response->job_key = $job->key;

		return $response;
	}




	// complete job
	public function jobs_complete_job() {

		// check nonce and permissions
	    check_admin_referer( 'wpla_ajax_nonce' );
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// quit if no job name provided
		if ( ! isset( $_REQUEST['job'] ) ) return false;

		// mark job as completed
		$job = new WPLA_JobsModel( wpla_clean($_REQUEST['job']) );
		$job->completeJob();

		// build response
		$response = new stdClass();
		$response->msg    = $job->item['job_name'].' comleted';
		$response->error    = '';
		$response->success  = true;
		$response->job_key = $job->key;

		$this->returnJSON( $response );
		exit();

	}


	public function addAdminMessagesToResult( $data ) {
		if ( ! is_object($data) ) return $data;
		if ( ! isset($data->errors) ) return $data;
		if ( ! is_array($data->errors) ) $data->errors = array();

		// merge admin notices with result errors
		$admin_errors = WPLA()->messages->get_admin_notices_for_json_result();
		$data->errors = array_merge( $data->errors, $admin_errors );

		return $data;
	}

	public function returnJSON( $data ) {

		// add WPLE admin messages to result errors
		$data = $this->addAdminMessagesToResult( $data );

		header('content-type: application/json; charset=utf-8');
		echo json_encode( $data );
	}



	// handle calls to logfile viewer based on php-tail
	// http://code.google.com/p/php-tail
	// https://github.com/taktos/php-tail
	public function ajax_wpla_tail_log() {

		// check nonce and permissions
	    check_admin_referer( 'wpla_tail_log' );
		if ( ! current_user_can('manage_amazon_listings') ) return;

		if ( WPLA_LIGHT ) {
			echo '<pre>';
			echo file_get_contents( WPLA()->logger->file );
			die();
		}

	}


}

// instantiate object
// $oWPLA_AjaxHandler = new WPLA_AjaxHandler();

