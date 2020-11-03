<?php
/**
 * WPL_API_Hooks
 *
 * implements public action hooks for 3rd party developers
 *
 * TODO: document other filter hooks intended for 3rd party developers, mainly:
 *  - wplister_get_product_main_image
 *  - wplister_product_images
 *  - wplister_ebay_price
 *  - wplister_before_process_template_html
 *  - wplister_process_template_html
 *  - wplister_listing_columns
 */

class WPL_API_Hooks extends WPL_Core {

	public function __construct() {
		parent::__construct();

		// revise inventory status on eBay
		add_action( 'wplister_revise_inventory_status', array( &$this, 'wplister_revise_inventory_status' ), 10, 2 );

		// revise item on eBay
		add_action( 'wplister_revise_item', array( &$this, 'wplister_revise_item' ), 10, 1 );
		add_action( 'wplister_relist_item', array( &$this, 'wplister_relist_item' ), 10, 1 );
		add_action( 'wplister_end_item',    array( &$this, 'wplister_end_item'    ), 10, 2 );

		// listing actions - used by rest api and grid editor
		add_filter( 'wple_verify_item',  array( &$this, 'wple_verify_item'  ), 10, 2 );
		add_filter( 'wple_publish_item', array( &$this, 'wple_publish_item' ), 10, 2 );
		add_filter( 'wple_revise_item',  array( &$this, 'wple_revise_item'  ), 10, 2 );
		add_filter( 'wple_end_item',     array( &$this, 'wple_end_item'     ), 10, 2 );

		// re-apply profile and mark listing item as changed
		add_action( 'wplister_product_has_changed', array( &$this, 'wplister_product_has_changed' ), 10, 1 );

		// create new prepared listing from product and profile
		add_action( 'wplister_prepare_listing', array( &$this, 'wplister_prepare_listing' ), 10, 2 );

		// process inventory changes from amazon
		add_action( 'wpla_inventory_status_changed', array( &$this, 'wpla_inventory_status_changed' ), 10, 1 );

		// process product updates triggered via the WooCommerce REST API
		// TODO: process 2nd parameter ($data) - if only stock and/or price are updated, use ReviseInventoryStatus
		add_action( 'woocommerce_api_edit_product', 			array( &$this, 'wplister_product_has_changed' ), 20, 1 ); 		// WC REST API					PUT /wc-api/v2/products/1234

		// handle ajax requests from third party CSV import plugins
		add_action( 'wp_ajax_woo-product-importer-ajax',      	array( &$this, 'handle_third_party_ajax_csv_import' ), 1, 1 );	// Woo Product Importer 		https://github.com/dgrundel/woo-product-importer
		add_action( 'wp_ajax_woocommerce_csv_import_request', 	array( &$this, 'handle_third_party_ajax_csv_import' ), 1, 1 );	// Product CSV Import Suite 	http://www.woothemes.com/products/product-csv-import-suite/
		add_action( 'wp_ajax_runImport',      					array( &$this, 'handle_third_party_ajax_csv_import' ), 1, 1 );	// WooCommerce CSV importer 2.x	http://wordpress.org/plugins/woocommerce-csvimport/
		add_action( 'wp_ajax_run_import',      					array( &$this, 'handle_third_party_ajax_csv_import' ), 1, 1 );	// WooCommerce CSV importer 3.x	http://wordpress.org/plugins/woocommerce-csvimport/
		// add_action( 'load-all-import_page_pmxi-admin-import', array( &$this, 'handle_third_party_ajax_csv_import' ), 1, 1 );	// WP All Import
		add_action( 'pmxi_saved_post', 							array( &$this, 'handle_object_has_changed'         ), 20, 1 ); 	// WP All Import				http://www.wpallimport.com/documentation/advanced/action-reference/

		// trigger 3rd party import mode if called from custom cron implementation
		// example: /wp-content/plugins/wwc-amz-aff/do-cron.php for WooCommerce Amazon Affiliates plugin
		// deactivated as it seems to cause problems with wwc-amz-aff
		// if ( 'do-cron.php' == basename( $_SERVER['SCRIPT_NAME'] ) )
		// 	$this->handle_third_party_ajax_csv_import();

		// example of using wplister_custom_attributes filter to add SKU as a virtual attribute
		add_filter( 'wplister_custom_attributes', array( &$this, 'wplister_custom_attributes' ), 10, 1 );
		add_filter( 'wplister_custom_attributes', array( &$this, 'wplister_custom_brand_attribute' ), 10, 1 );

		// add support for Store Exporter plugin (http://www.visser.com.au/documentation/store-exporter/usage/)
		add_filter( 'woo_ce_product_fields', array( &$this, 'woo_ce_product_fields' ) );
		add_filter( 'woo_ce_product_item',   array( &$this, 'woo_ce_product_item' ), 10, 2 );

		// process CompleteSale requests from other plugins
		add_action( 'wple_complete_sale_on_ebay', array( &$this, 'wple_complete_sale_on_ebay' ), 10, 2 );

	}

	/**
     * revise inventory status for given product_id - or array of product_ids (deprecated)
     *
     * @param $post_id
     * @param bool $force Set to TRUE to force revision regardles if cached quantity is current
     *
     * @return bool
     */
	function wplister_revise_inventory_status( $post_id, $force = false ) {
	    $this->dblogger = new WPL_EbatNs_Logger();

        // log to db - before request
        $this->dblogger->updateLog( array(
            'callname'    => 'wplister_revise_inventory_status',
            'request_url' => 'internal action hook',
            'request'     => maybe_serialize( $post_id ),
            'success'     => 'pending'
        ));

        // make sure we process only a single $post_id
        if ( is_array( $post_id ) ) $post_id = $post_id[0];

        // get all listing items for $post_id
		$listings = WPLE_ListingQueryHelper::getAllListingsFromPostOrParentID( $post_id );
		$revised_listing_ids = array();

		// process all listing items
		foreach ( $listings as $listing ) {

	        // use right account_id for listing
	        // $listing_id = WPLE_ListingQueryHelper::getListingIDFromPostID( $post_id );
	        // $account_id = WPLE_ListingQueryHelper::getAccountID( $listing_id );
	        $listing_id = $listing->id;
	        $account_id = $listing->account_id;

			// call EbayController
			$this->initEC( $account_id );
			$this->EC->reviseInventoryForListing( $listing_id, $force );
			$this->EC->closeEbay();

			WPLE()->logger->info('revised inventory status for item: ' . print_r($listing_id,1) . '');
			$revised_listing_ids[] = $listing_id;
		}

		if ( !empty( $revised_listing_ids ) ) {
            // log to db
            $this->dblogger->updateLog( array(
                'response'  => json_encode( $revised_listing_ids ),
                'success'   => $this->EC->isSuccess ? 'Success' : 'Error'
            ));

            return isset( $this->EC->lastResults ) ? $this->EC->lastResults : false;
        }

        return true;
	}

	// verify ebay item for given listing id
	function wple_verify_item( $listing_id ) {
        $account_id = WPLE_ListingQueryHelper::getAccountID( $listing_id );
		WPLE()->logger->info('verifying listing '.$listing_id.' - account '.$account_id );

		// call EbayController
		$this->initEC( $account_id );
		$results = $this->EC->verifyItems( $listing_id );
		$this->EC->closeEbay();

		WPLE()->logger->info('verified listing '.$listing_id );
		return isset( $this->EC->lastResults ) ? $this->EC->lastResults : false;
	}

	// publish ebay item for given listing id
	function wple_publish_item( $listing_id ) {
        $account_id = WPLE_ListingQueryHelper::getAccountID( $listing_id );
		WPLE()->logger->info('publishing listing '.$listing_id.' - account '.$account_id );

		// call EbayController
		$this->initEC( $account_id );
		$results = $this->EC->sendItemsToEbay( $listing_id );
		$this->EC->closeEbay();

		WPLE()->logger->info('published listing '.$listing_id );
		return isset( $this->EC->lastResults ) ? $this->EC->lastResults : false;
	}

	// revise ebay item for given listing id
	function wple_revise_item( $listing_id ) {
        $account_id = WPLE_ListingQueryHelper::getAccountID( $listing_id );
		WPLE()->logger->info('revising listing '.$listing_id.' - account '.$account_id );

		// call EbayController
		$this->initEC( $account_id );
		$results = $this->EC->reviseItems( $listing_id );
		$this->EC->closeEbay();

		WPLE()->logger->info('revised listing '.$listing_id );
		return isset( $this->EC->lastResults ) ? $this->EC->lastResults : false;
	}

	// end ebay item for given listing id
	function wple_end_item( $listing_id ) {
        $account_id = WPLE_ListingQueryHelper::getAccountID( $listing_id );
		WPLE()->logger->info('ending listing '.$listing_id.' - account '.$account_id );

		// call EbayController
		$this->initEC( $account_id );
		$results = $this->EC->endItemsOnEbay( $listing_id );
		$this->EC->closeEbay();

		WPLE()->logger->info('ended listing '.$listing_id );
		return isset( $this->EC->lastResults ) ? $this->EC->lastResults : false;
	}


	// revise ebay item for given product_id
	function wplister_revise_item( $post_id ) {
        // call markItemAsModified() to re-apply the listing profile
		$lm = new ListingsModel();
		$lm->markItemAsModified( $post_id );

		$listing_id = WPLE_ListingQueryHelper::getListingIDFromPostID( $post_id );
        $account_id = WPLE_ListingQueryHelper::getAccountID( $listing_id );
		WPLE()->logger->info('revising listing '.$listing_id.' - account '.$account_id );

		// call EbayController
		$this->initEC( $account_id );
		$results = $this->EC->reviseItems( $listing_id );
		$this->EC->closeEbay();

		WPLE()->logger->info('revised listing '.$listing_id );
		return isset( $this->EC->lastResults ) ? $this->EC->lastResults : false;

	}

	// relist ebay item for given product_id
	function wplister_relist_item( $post_id ) {

		// call markItemAsModified() to re-apply the listing profile
		$lm = new ListingsModel();
		$lm->markItemAsModified( $post_id );

		$listing_id = WPLE_ListingQueryHelper::getListingIDFromPostID( $post_id );
        $account_id = WPLE_ListingQueryHelper::getAccountID( $listing_id );
		WPLE()->logger->info('relisting listing '.$listing_id.' - account '.$account_id );

		// call EbayController
		$this->initEC( $account_id );
		$results = $this->EC->relistItems( $listing_id );
		$this->EC->closeEbay();

		WPLE()->logger->info('relisted item '.$listing_id );
		return isset( $this->EC->lastResults ) ? $this->EC->lastResults : false;

	}

	// end ebay item for given listing_id
	function wplister_end_item( $listing_id, $account_id = null ) {
		WPLE()->logger->info('ending item '.$listing_id );

		// if listing_id is an eBay Item ID, find the listing_id automatically
		if ( ! is_array( $listing_id ) && strlen( $listing_id ) > 10 ) {
			$listing = WPLE_ListingQueryHelper::findItemByEbayID( $listing_id, false );
			if ( $listing ) $listing_id = $listing->id;
		}

		// call EbayController
		$this->initEC( $account_id );
		$results = $this->EC->endItemsOnEbay( $listing_id );
		$this->EC->closeEbay();

		WPLE()->logger->info('ended item '.$listing_id );
		return isset( $this->EC->lastResults ) ? $this->EC->lastResults : false;

	}

	// re-apply profile and mark listing item as changed
	function wplister_product_has_changed( $post_id ) {
	    if ( empty( $post_id ) ) {
	        return;
        }

		$lm = new ListingsModel();
		$listing_id = $lm->markItemAsModified( $post_id );

		// handle locked items
		$listing = ListingsModel::getItem( $listing_id );
		if ( $listing && $listing['locked'] )
			do_action( 'wplister_revise_inventory_status', $post_id );

		wple_async_revise_listing( $listing_id );

	}

	// create new prepared listing from product and apply profile
	function wplister_prepare_listing( $post_id, $profile_id ) {

		// prepare product
		$listingsModel = new ListingsModel();
        $listingsModel->prepareProductForListing( $post_id );
        if ( ! $profile_id ) return;

        // get profile
		$profilesModel = new ProfilesModel();
        $profile = $profilesModel->getItem( $profile_id );
        if ( ! $profile ) return;

        $listingsModel->applyProfileToNewListings( $profile );
	}

	// process inventory changes from amazon
	function wpla_inventory_status_changed( $post_id ) {

		// re-apply profile to update ebay_auctions table
		$lm = new ListingsModel();
		$listing_id = $lm->markItemAsModified( $post_id );

		// $this->wplister_revise_inventory_status( $post_id );
		do_action( 'wplister_revise_inventory_status', $post_id );
	}


	// call CompleteSale for given order post_id and data
	// example for $data array:
	// $data['TrackingNumber']  = '123456789';
	// $data['TrackingCarrier'] = 'UPS';
	// $data['ShippedTime']     = '2015-12-24';
	// $data['FeedbackText']    = 'Thank You...';
	function wple_complete_sale_on_ebay( $post_id, $data ) {

        // log to db - before request
        $this->dblogger = new WPL_EbatNs_Logger();
        $this->dblogger->updateLog( array(
            'callname'    => 'wple_complete_sale_on_ebay',
            'request_url' => 'internal action hook - post_id: '.$post_id,
            'request'     => maybe_serialize( $data ),
            'success'     => 'skipped'
        ));

    	// check if this order came in from eBay
        $ebay_order_id = get_post_meta( $post_id, '_ebay_order_id', true );
    	if ( ! $ebay_order_id ) return false; // die('This is not an eBay order.');

    	// check if this order was marked as shipped already
        if ( 'yes' == get_post_meta( $post_id, '_ebay_marked_as_shipped', true ) ) return false;


		// make sure ShippedTime is a timestamp
		if ( isset($data['ShippedTime']) && ! is_numeric($data['ShippedTime']) ) {
			$data['ShippedTime'] = strtotime( $data['ShippedTime'] );
		}

		// fuzzy match tracking provider
		if ( isset($data['TrackingCarrier']) ) {
			$data['TrackingCarrier'] = WpLister_Order_MetaBox::findMatchingTrackingProvider( $data['TrackingCarrier'] );
		}

		// use default feedback text unless FeedbackText parameter is set
		if ( ! isset($data['FeedbackText']) ) {
			$data['FeedbackText'] = get_option( 'wplister_default_feedback_text', '' );
		}


    	// complete sale on eBay
		$response = WpLister_Order_MetaBox::callCompleteOrder( $post_id, $data, true );

		// Update order data if request was successful
		if ( $response->success ) {
			if ( isset( $data['TrackingCarrier'] ) ) update_post_meta( $post_id, '_tracking_provider', 	$data['TrackingCarrier'] );
			if ( isset( $data['TrackingNumber']  ) ) update_post_meta( $post_id, '_tracking_number', 	$data['TrackingNumber'] );
			if ( isset( $data['ShippedTime'] 	 ) ) update_post_meta( $post_id, '_date_shipped', 		$data['ShippedTime'] );
			if ( isset( $data['FeedbackText'] 	 ) ) update_post_meta( $post_id, '_feedback_text', 		$data['FeedbackText'] );
		}

        // log to db
        $this->dblogger->updateLog( array(
            'response'  => json_encode( $response ),
            'success'   => 'Success'
        ));

		return $response;
	} // wple_complete_sale_on_ebay()



	/**
	 *  support for Woo Product Importer plugin
	 *  https://github.com/dgrundel/woo-product-importer
	 *
	 *  support for WooCommerce Product CSV Import Suite
	 *  http://www.woothemes.com/products/product-csv-import-suite/
	 *
	 *  Third party CSV import plugins usually call wp_update_post() before update_post_meta() so WP will trigger the save_post action before price and stock have been updated.
	 *  We need to disable the original save_post hook and collect post IDs to mark them as modified at shutdown (including further processing for locked items)
	 */

	function handle_third_party_ajax_csv_import() {
		WPLE()->logger->info("CSV import mode ENABLED");

		// disable default action for save_post
		global $WPL_WooBackendIntegration;
		remove_action( 'save_post', array( &$WPL_WooBackendIntegration, 'wplister_on_woocommerce_product_save'           ), 20, 2 );
		remove_action( 'save_post', array( &$WPL_WooBackendIntegration, 'wplister_on_woocommerce_product_bulk_edit_save' ), 20, 2 );

		// add new save_post action to collect changed post IDs
		add_action( 'save_post', array( &$this, 'collect_updated_products' ), 10, 2 );

		// collect changed post IDs from WooCommerce Product CSV Import Suite (PCSVIS)
		add_action( 'import_end', array( &$this, 'collect_updated_products_from_pcsvis' ) );

		// add shutdown handler
		register_shutdown_function( array( &$this, 'update_products_on_shutdown' ) );

	}

	// collect changed product IDs
	function collect_updated_products_from_pcsvis() {
		WPLE()->logger->info("CSV: collect_updated_products_from_pcsvis()");

		if ( ! isset( $GLOBALS['WC_CSV_Product_Import']                  ) ) return;
		if ( ! isset( $GLOBALS['WC_CSV_Product_Import']->processed_posts ) ) return;
		if (   isset( $_GET['step'] ) && $_GET['step'] == 4                ) return; // step 4 is cleaning up after the actual import
		WPLE()->logger->info("CSV: processed_posts: ".print_r($GLOBALS['WC_CSV_Product_Import']->processed_posts,1));

		// get queue
		$collected_products = get_option( 'wplister_updated_products_queue', array() );
		if ( ! is_array( $collected_products ) ) $collected_products = array();

		// add processed posts to queue - and remove duplicates
		$collected_products = array_unique( array_merge( $collected_products, $GLOBALS['WC_CSV_Product_Import']->processed_posts ) );

		// update queue
		update_option( 'wplister_updated_products_queue', $collected_products );
	}

	// collect changed product IDs
	function collect_updated_products( $post_id, $post ) {
		WPLE()->logger->info("CSV: collect_updated_products( $post_id )");

		if ( !$_POST ) return $post_id;
		// if ( is_int( wp_is_post_revision( $post_id ) ) ) return;
		// if( is_int( wp_is_post_autosave( $post_id ) ) ) return;
		// if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
		if ( ! current_user_can( 'edit_post', $post_id )) return $post_id;
		if ( ! in_array( $post->post_type, array( 'product', 'product_variation' ) ) ) return $post_id;

		// if this is a single variation use parent_id
		// if ( $parent_id = ProductWrapper::getVariationParent( $post_id ) ) {
		if ( $post->post_type == 'product_variation' ) {
			$parent_id = ProductWrapper::getVariationParent( $post_id );
			// WPLE()->logger->info("single variation found - use parent $parent_id for $post_id");
			$post_id = $parent_id;
		}

		// get queue
		$collected_products = get_option( 'wplister_updated_products_queue', array() );
		if ( ! is_array( $collected_products ) ) $collected_products = array();

		// add product_id to queue - if it doesn't exist
		if ( ! in_array( $post_id, $collected_products ) )
			$collected_products[] = $post_id;

		// WPLE()->logger->info("collected products: ".print_r($collected_products,1));

		// update queue
		update_option( 'wplister_updated_products_queue', $collected_products );
	}

	function update_products_on_shutdown() {

		// get queue
		$collected_products = get_option( 'wplister_updated_products_queue', array() );
		if ( ! is_array( $collected_products ) ) $collected_products = array();

		// DEBUG
		WPLE()->logger->info("CSV: update_products_on_shutdown() - collected_products: ".print_r($collected_products,1));

		// mark each queued product as modified
		foreach ($collected_products as $post_id ) {
			do_action( 'wplister_product_has_changed', $post_id );
		}

		// clear queue
		delete_option( 'wplister_updated_products_queue' );

	}

	// handle single CSV import action - product or order
	// products are marked as changed / revised
	// orders are updated on eBay (if completed)
	function handle_object_has_changed( $post_id ) {

		$post_type = get_post_type( $post_id );

		// Handle product. Run wplister_product_has_changed on the parent product when dealing with variations
		if ( $post_type == 'product' ) {
			do_action( 'wplister_product_has_changed', $post_id );
			return;
		} elseif ( $post_type == 'product_variation' ) {
		    $product = wc_get_product( $post_id );
            do_action( 'wplister_product_has_changed', $product->get_parent_id() );
            return;
        }

		// handle order
		if ( $post_type == 'shop_order' ) {

			// get order and check status
			$order = wc_get_order( $post_id );
			if ( $order->get_status() != 'completed' ) return;

			// get tracking data
			$data = array();
			$data['TrackingNumber']  = get_post_meta( $post_id, '_tracking_number', true );
			$data['TrackingCarrier'] = get_post_meta( $post_id, '_tracking_provider', true );

			// complete sale
			do_action( 'wple_complete_sale_on_ebay', $post_id, $data );
			return;
		}

	} // handle_object_has_changed()



	// example of using wplister_custom_attributes filter to add SKU as a virtual attribute
	function wplister_custom_attributes( $attributes ) {

		$attributes[] = array(
			'label'    => 'SKU',
			'id'       => '_sku',
			'meta_key' => '_sku'
		);

		$attributes[] = array(
			'label'    => 'MPN',
			'id'       => '_ebay_mpn',
			'meta_key' => '_ebay_mpn'
		);

		$attributes[] = array(
			'label'    => 'Brand',
			'id'       => '_ebay_brand',
			'meta_key' => '_ebay_brand'
		);

		return $attributes;
	}

	function wplister_custom_brand_attribute( $attributes ) {
	    // WC Brands
        if ( class_exists( 'WC_Brands' ) ) {
            $attributes[] = array(
                'label'    => 'Brand (WC Brands Addon)',
                'id'       => '_ebay_brand',
                'callback' => array( $this, 'get_brand_name' )
            );
        }

        // Perfect WooCommerce Brands
        if ( class_exists( '\Perfect_Woocommerce_Brands\Perfect_Woocommerce_Brands' ) ) {
            $attributes[] = array(
                'label'    => 'Brand (Perfect WooCommerce Brands Addon)',
                'id'       => '_ebay_pwb_brand',
                'callback' => array( $this, 'get_brand_name' )
            );
        }


		return $attributes;
	}

	function get_brand_name( $post_id ) {
	    return ProductWrapper::getBrand( $post_id );
    }

	// add support for WooCommerce Brands extension
	function wc_brands_get_brand_name( $post_id, $listing_id ) {
		return $this->get_brand_name( $post_id );
	}

	// add support for Store Exporter plugin (http://www.visser.com.au/documentation/store-exporter/usage/)
	function woo_ce_product_fields( $fields ) {
		$fields[] = array (
			'name'    => 'ebay_item_id',
			'label'   => 'eBay Item ID',
			'default' => 0
		);
		$fields[] = array (
			'name'    => 'ebay_status',
			'label'   => 'eBay Status',
			'default' => 0
		);
		return $fields;
	}

	function woo_ce_product_item( $product, $product_id ) {
		$product->ebay_item_id = WPLE_ListingQueryHelper::getEbayIDFromPostID( $product_id );
		$product->ebay_status  = WPLE_ListingQueryHelper::getStatusFromPostID( $product_id );
		return $product;
	}


}

// global $wplister_api;
$wplister_api = new WPL_API_Hooks();
