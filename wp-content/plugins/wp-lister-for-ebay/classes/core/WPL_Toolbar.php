<?php

class WPLister_Toolbar  {
	
	public function __construct() {

		// custom toolbar
		add_action( 'admin_bar_menu', array( &$this, 'customize_toolbar' ), 999 );

	}


	// custom toolbar bar
	function customize_toolbar( $wp_admin_bar ) {

		// check if current user can manage listings
		if ( ! current_user_can('manage_ebay_listings') ) return;

		// top level 'eBay'
		$args = array(
			'id'    => 'wplister_top',
			'title' => __( 'eBay', 'wp-lister-for-ebay' ),
			'href'  => admin_url( 'admin.php?page=wplister' ),
			'meta'  => array('class' => 'wplister-toolbar-top')
		);
		$wp_admin_bar->add_node($args);
		
		// Listings page	
		$args = array(
			'id'    => 'wplister_listings',
			'title' => __( 'Listings', 'wp-lister-for-ebay' ),
			'href'  => admin_url( 'admin.php?page=wplister' ),
			'parent'  => 'wplister_top',
			'meta'  => array('class' => 'wplister-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Profiles page
		$args = array(
			'id'    => 'wplister_profiles',
			'title' => __( 'Profiles', 'wp-lister-for-ebay' ),
			'href'  => admin_url( 'admin.php?page=wplister-profiles' ),
			'parent'  => 'wplister_top',
			'meta'  => array('class' => 'wplister-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Templates page
		$args = array(
			'id'    => 'wplister_templates',
			'title' => __( 'Templates', 'wp-lister-for-ebay' ),
			'href'  => admin_url( 'admin.php?page=wplister-templates' ),
			'parent'  => 'wplister_top',
			'meta'  => array('class' => 'wplister-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Orders page
		$args = array(
			'id'    => 'wplister_orders',
			'title' => __( 'Orders', 'wp-lister-for-ebay' ),
			'href'  => admin_url( 'admin.php?page=wplister-orders' ),
			'parent'  => 'wplister_top',
			'meta'  => array('class' => 'wplister-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		if ( get_option( 'wplister_enable_messages_page' ) ) {

			// Messages page
			$args = array(
				'id'    => 'wplister_messages',
				'title' => __( 'Messages', 'wp-lister-for-ebay' ),
				'href'  => admin_url( 'admin.php?page=wplister-messages' ),
				'parent'  => 'wplister_top',
				'meta'  => array('class' => 'wplister-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

		}

		// Tools page
		$args = array(
			'id'    => 'wplister_tools',
			'title' => __( 'Tools', 'wp-lister-for-ebay' ),
			'href'  => admin_url( 'admin.php?page=wplister-tools' ),
			'parent'  => 'wplister_top',
			'meta'  => array('class' => 'wplister-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Inventory Check
		$args = array(
			'id'    => 'wplister_tools_inventory',
			'title' => __( 'Inventory Check', 'wp-lister-for-ebay' ),
			'href'  => admin_url( 'admin.php?page=wplister-tools&tab=inventory' ),
			'parent'  => 'wplister_tools',
			'meta'  => array('class' => 'wplister-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Stock Log
		$args = array(
			'id'    => 'wplister_tools_stock_log',
			'title' => __( 'Stock Log', 'wp-lister-for-ebay' ),
			'href'  => admin_url( 'admin.php?page=wplister-tools&tab=stock_log' ),
			'parent'  => 'wplister_tools',
			'meta'  => array('class' => 'wplister-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Developer Tools
		$args = array(
			'id'    => 'wplister_tools_developer',
			'title' => __( 'Developer', 'wp-lister-for-ebay' ),
			'href'  => admin_url( 'admin.php?page=wplister-tools&tab=developer' ),
			'parent'  => 'wplister_tools',
			'meta'  => array('class' => 'wplister-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		if ( current_user_can('manage_ebay_options') ) {

			// Settings page
			$args = array(
				'id'    => 'wplister_settings',
				'title' => __( 'Settings', 'wp-lister-for-ebay' ),
				'href'  => admin_url( 'admin.php?page=wplister-settings' ),
				'parent'  => 'wplister_top',
				'meta'  => array('class' => 'wplister-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

			// Settings - General tab
			$args = array(
				'id'    => 'wplister_settings_general',
				'title' => __( 'General Settings', 'wp-lister-for-ebay' ),
				'href'  => admin_url( 'admin.php?page=wplister-settings' ),
				'parent'  => 'wplister_settings',
				'meta'  => array('class' => 'wplister-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

			// Settings - Accounts tab
			$args = array(
				'id'    => 'wplister_settings_accounts',
				'title' => __( 'Accounts', 'wp-lister-for-ebay' ),
				'href'  => admin_url( 'admin.php?page=wplister-settings&tab=accounts' ),
				'parent'  => 'wplister_settings',
				'meta'  => array('class' => 'wplister-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

			// Settings - Categories tab
			$args = array(
				'id'    => 'wplister_settings_categories',
				'title' => __( 'Categories', 'wp-lister-for-ebay' ),
				'href'  => admin_url( 'admin.php?page=wplister-settings&tab=categories' ),
				'parent'  => 'wplister_settings',
				'meta'  => array('class' => 'wplister-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

			// Settings - Advanced tab
			$args = array(
				'id'    => 'wplister_settings_advanced',
				'title' => __( 'Advanced', 'wp-lister-for-ebay' ),
				'href'  => admin_url( 'admin.php?page=wplister-settings&tab=advanced' ),
				'parent'  => 'wplister_settings',
				'meta'  => array('class' => 'wplister-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

			// Settings - Developer tab
			$args = array(
				'id'    => 'wplister_settings_developer',
				'title' => __( 'Developer', 'wp-lister-for-ebay' ),
				'href'  => admin_url( 'admin.php?page=wplister-settings&tab=developer' ),
				'parent'  => 'wplister_settings',
				'meta'  => array('class' => 'wplister-toolbar-page')
			);
			$wp_admin_bar->add_node($args);


		} // if current_user_can('manage_ebay_options')

		if ( current_user_can('manage_ebay_options') && ( get_option( 'wplister_log_to_db' ) == '1' ) ) {
		
			// Logs page
			$args = array(
				'id'    => 'wplister_log',
				'title' => __( 'Logs', 'wp-lister-for-ebay' ),
				'href'  => admin_url( 'admin.php?page=wplister-log' ),
				'parent'  => 'wplister_top',
				'meta'  => array('class' => 'wplister-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

		}

		// product page
		global $post;
		global $wp_query;
		global $pagenow;
		$post_id = false;

		if ( $wp_query->in_the_loop && isset( $wp_query->post->post_type ) && ( $wp_query->post->post_type == 'product' ) ) {
			$post_id = $wp_query->post->ID;
		} elseif ( is_object( $post ) && isset( $post->post_type ) && ( $post->post_type == 'product' ) ) {
			$post_id = $post->ID;
		}

		// skip product links on the main products page
		if ( $pagenow == 'edit.php' ) return;

		// do we have a single product page?
		if ( empty($post_id) ) return;


		// get all items
		$listings = WPLE_ListingQueryHelper::getAllListingsFromPostID( $post_id );

		if ( sizeof($listings) > 0 ) {

			$ebay_id = WPLE_ListingQueryHelper::getEbayIDFromPostID( $post_id );
			$url     = WPLE_ListingQueryHelper::getViewItemURLFromPostID( $post_id );

			// View on eBay link
			$args = array(
				'id'    => 'wplister_view_on_ebay',
				'title' => __( 'View item on eBay', 'wp-lister-for-ebay' ), # ." ($ebay_id)",
				'href'  => $url,
				'parent'  => 'wplister_top',
				'meta'  => array('target' => '_blank', 'class' => 'wplister-toolbar-link')
			);
			if ( $url ) $wp_admin_bar->add_node($args);

			foreach ($listings as $listing) {

				$args = array(
					'id'    => 'wplister_view_on_ebay_'.$listing->id,
					'title' => '#'.$listing->ebay_id . ': ' . $listing->auction_title,
					'href'  => $listing->ViewItemURL,
					'parent'  => 'wplister_view_on_ebay',
					'meta'  => array('target' => '_blank', 'class' => 'wplister-toolbar-link')
				);
				if ( $listing->ViewItemURL ) $wp_admin_bar->add_node($args);

			}

			// View in WP-Lister
			$url = admin_url( 'admin.php?page=wplister&s='.$post_id );
			$args = array(
				'id'    => 'wplister_view_on_listings_page',
				'title' => __( 'View item in WP-Lister', 'wp-lister-for-ebay' ),
				'href'  => $url,
				'parent'  => 'wplister_top',
				'meta'  => array('target' => '_blank', 'class' => 'wplister-toolbar-link')
			);
			$wp_admin_bar->add_node($args);

		} else {

			// $args = $this->addPrepareActions( $args );

		}

		if ( current_user_can('prepare_ebay_listings') )
			$this->addPrepareActions( $wp_admin_bar, $post_id );


	} // customize_toolbar()

	function addPrepareActions( $wp_admin_bar, $post_id ) {

		// Prepare listing link
		$url = '#';
		$args = array(
			'id'    => 'wplister_tb_prepare_listing',
			'title' => __( 'List on eBay', 'wp-lister-for-ebay' ),
			'href'  => $url,
			'parent'  => 'wplister_top',
			'meta'  => array('class' => 'wplister-toolbar-page')
		);
		$wp_admin_bar->add_node( $args );

		$pm = new ProfilesModel();
		$profiles = $pm->getAll();

		foreach ($profiles as $profile) {

			// echo "<pre>";print_r($profile);echo"</pre>";#die();
			$profile_id = $profile['profile_id'];
			$url = admin_url( 'admin.php?page=wplister&action=wpl_prepare_single_listing&product_id='.$post_id.'&profile_id='.$profile_id .'&_wpnonce='. wp_create_nonce( 'wplister_prepare_single_listing' ) );
			$args = array(
				'id'    => 'wplister_list_on_ebay_'.$profile['profile_id'],
				'title' => $profile['profile_name'],
				'href'  => $url,
				'parent'  => 'wplister_tb_prepare_listing',
				'meta'  => array('class' => 'wplister-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

		}

		return $args;
	} // addPrepareActions()
	

} // class WPLister_Toolbar

// instantiate object
$oWPLister_Toolbar = new WPLister_Toolbar();

