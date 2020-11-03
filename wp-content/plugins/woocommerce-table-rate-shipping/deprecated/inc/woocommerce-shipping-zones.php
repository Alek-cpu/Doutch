<?php
/*
For Plugin: WooCommerce Table Rate Shipping
Description: Creates new settings page for zone shipping.
	Additionally functions are defined to compare data for other functions
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	/**
	 * Check if WooCommerce is active
	 */
	if ( class_exists( 'Woocommerce' ) || class_exists( 'WooCommerce' ) ) {

		if (!class_exists('WC_Shipping_Method')) return;

		include(plugin_dir_path(__FILE__).'zone-list-table.php');
		$SUCCESS = false;

		function create_new_tab() {
	    	$current_tab = ( empty( $_GET['tab'] ) ) ? 'general' : sanitize_text_field( urldecode( $_GET['tab'] ) );

		    if( WOOCOMMERCE_VERSION >= 2.1 ) 
				echo '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping_zones' ) . '" class="nav-tab ';
			else
				echo '<a href="' . admin_url( 'admin.php?page=woocommerce_settings&tab=shipping_zones' ) . '" class="nav-tab ';
			if( $current_tab == 'shipping_zones' ) echo 'nav-tab-active';
			echo '">Shipping Zones</a>';
		}
		add_action('woocommerce_settings_tabs','create_new_tab');

		function jquery_admin_init() {
	        /* Register our script. */
	        wp_enqueue_script( 'jquery-ui-sortable' );
	    }
	    add_action( 'admin_init', 'jquery_admin_init' );

		function be_table_rate_shipping_zones() {
			global $woocommerce;

			if(isset($_GET['upgrade']) && $_GET['upgrade'] == 'zones') :
				$GLOBALS['hide_save_button'] = true;
				remove_action('woocommerce_update_options_shipping_zones','be_save_new_zone');
				BE_Table_Rate_Shipping::install_plugin_button();
			elseif(isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'new' || $_GET['action'] == 'delete')) :
				if($_GET['action'] == 'delete' ) {
					be_save_new_zone();
					$GLOBALS['hide_save_button'] = true;
					Zone_List_Table::tt_render_list_page();
				}elseif ( isset( $_POST['save'] )) {
					Zone_List_Table::tt_render_edit_page($_POST['zone_id']);
				} else Zone_List_Table::tt_render_edit_page();
			elseif(isset($_POST['action'])) :
				be_save_new_zone();
				$GLOBALS['hide_save_button'] = true;
				Zone_List_Table::tt_render_list_page();
			else :
				$GLOBALS['hide_save_button'] = true;
				Zone_List_Table::tt_render_list_page();
			endif;
		}
		add_action('woocommerce_settings_tabs_shipping_zones','be_table_rate_shipping_zones');

		function be_save_new_zone() {
			global $woocommerce, $SUCCESS;

	        $shipping_zones = array_filter( (array) get_option( 'be_woocommerce_shipping_zones' ) );
			if(isset($_POST['action'])) {
				if( 'delete' === Zone_List_Table::current_action() ) {
					if(is_array($_POST['zone']) && count($_POST['zone']) > 0) {
						foreach ($_POST['zone'] as $value) {
			                if( isset( $shipping_zones[$value] ) && is_array( $shipping_zones[$value] ) )  {
			                    unset($shipping_zones[$value]);
								$SUCCESS = true;
			                }
						}
			            update_option('be_woocommerce_shipping_zones', $shipping_zones);
					}
				} else {
					$i = 1;
					if(count($_POST['zone_id']) > 0) {
						$new_order = array();
						foreach ($_POST['zone_id'] as $value) {
							$zone_id = (int) $value;
			                if( isset( $shipping_zones[$zone_id] ) && is_array( $shipping_zones[$zone_id] ) )  {
			                    $new_order[$zone_id] = $shipping_zones[$zone_id];
			                    $new_order[$zone_id]['zone_order'] = $i;
			                    $i++;
			                }
						}
			            update_option('be_woocommerce_shipping_zones', $new_order);

						// reorder table of rates to reflect new zone order
						$currentRates = array_filter( (array) get_option( 'woocommerce_table_rates' ) );
						foreach ($currentRates as $key => $value) {
							$currentRates[$key]['zone_order'] = $new_order[$value['zone']]['zone_order'];
						}
						$newRates = BE_Table_Rate_Shipping::sort_table_rates( $currentRates );
						update_option('woocommerce_table_rates', $newRates);
					}
					$SUCCESS = true;
				}
			} elseif(isset($_GET['action']) && $_GET['action'] == 'delete') {
				if(isset($_GET['zone'])) {
					$zone_id = (int) $_GET['zone'];
			        if( isset( $shipping_zones[$zone_id] ) && is_array( $shipping_zones[$zone_id] ) )  {
	                    unset($shipping_zones[$zone_id]);
		            	update_option('be_woocommerce_shipping_zones', $shipping_zones);
						$SUCCESS = true;
	                }
				}
			} elseif (isset($_GET['action']) && ( $_GET['action'] == 'new' || $_GET['action'] == 'edit') ) {
	            $totalitems = count($shipping_zones);
	            $max_keys = array();

				$zone_id_posted = (int) $_POST['zone_id'];
				$zone_enabled = ( isset( $_POST['zone_enabled'] ) ) ? '1' : '0';
				$zone_title = sanitize_text_field($_POST['zone_title']);
				$zone_description = sanitize_text_field($_POST['zone_description']);
				$zone_type = sanitize_text_field($_POST['zone_type']);

				if( !isset( $shipping_zones[$zone_id_posted] ) || !isset( $shipping_zones[$zone_id_posted]['zone_order'] ) ) {
					if(count($shipping_zones) > 0) {
						foreach ($shipping_zones as $value)
							$max_keys[] = $value['zone_order'];
						$zone_order_max = max($max_keys);
					} else {
						$zone_order_max = 0;
					}
				} else $zone_order_max = $shipping_zones[$zone_id_posted]['zone_order'];

				if($zone_type == 'countries') {
					$zone_country = ( isset( $_POST[ 'location_countries' ] ) ) ? (array) $_POST[ 'location_countries' ] : array();
					$zone_country = implode( ',', $zone_country );
					$zone_country_except = ( isset( $_POST[ 'location_countries_exceptS' ] ) ) ? (array) $_POST[ 'location_countries_exceptS' ] : array();
					$zone_country_except = implode( ',', $zone_country_except );
					$zone_postal_except = sanitize_text_field( $_POST['location_countries_except'] );
					//$zone_postal_except = preg_replace( '/\s+/', '', $zone_postal_except );
					$zone_except = array('states' => $zone_country_except, 'postals' => $zone_postal_except);
					$zone_postal = '';
				} elseif($zone_type == 'postal') {
					$zone_country = sanitize_text_field( $_POST['location_country'] );
					$zone_postal = sanitize_text_field( $_POST['location_codes'] );
					//$zone_postal = preg_replace( '/\s+/', '', $zone_postal );
					$zone_except = ( isset( $_POST[ 'location_postal_except' ] ) ) ? sanitize_text_field( $_POST[ 'location_postal_except' ] ) : '';
					//$zone_except = preg_replace( '/\s+/', '', $zone_except );
				} else {
					$zone_country = $zone_postal = "";
					$zone_except = ( isset( $_POST[ 'location_everywhere_except' ] ) ) ? (array) $_POST[ 'location_everywhere_except' ] : array();
					$zone_except = implode( ',', $zone_except );
				}

				$shipping_zones[$zone_id_posted] = array(
					'zone_id' => $zone_id_posted,
					'zone_enabled' => $zone_enabled,
					'zone_title' => $zone_title,
					'zone_description' => $zone_description,
					'zone_type' => $zone_type,
					'zone_country' => $zone_country,
					'zone_postal' => $zone_postal,
					'zone_except' => $zone_except,
					'zone_order' => ( $_GET['action'] == 'edit' ) ? $shipping_zones[$zone_id_posted]['zone_order'] : $zone_order_max + 1,
					);
				update_option('be_woocommerce_shipping_zones', $shipping_zones);

				// Clear any unwanted data
				wc_delete_product_transients();
				if( !isset( $zone_id ) || $zone_id == 0) $zone_id = $zone_id_posted;

				delete_transient( 'woocommerce_cache_excluded_uris' );

				$zone_id = ($zone_id_posted == 0) ? $zone_id_max : $zone_id_posted;

				$SUCCESS = true;
			}

		}
		add_action('woocommerce_update_options_shipping_zones','be_save_new_zone');

		function be_get_zones() {
			$zoneList = new Zone_List_Table();
			$zones = $zoneList->shipping_zones;
			return $zones;
		}

		function be_in_zone($zone_id, $country, $state, $zipcode) {
			$zones = get_option( 'be_woocommerce_shipping_zones' );
			if(isset($zones[$zone_id]) && count($zones[$zone_id]) > 0) :
				$zone = $zones[$zone_id];
				if($zone['zone_enabled'] == 0) return false;

				$zipcode = str_replace( '-', '', $zipcode );

				switch ($zone['zone_type']) {
		            case 'everywhere':
                		$countries_abbr = explode(',', $zone['zone_except']);
		    			if(in_array($country, $countries_abbr) || in_array($country.":".$state, $countries_abbr))
		    				return false;
		    			else return true;
		            case 'countries':
                		$countries_abbr = explode(',', $zone['zone_country']);
		    			if(in_array($country, $countries_abbr) || in_array($country.":".$state, $countries_abbr)) {
		    				if( isset( $zone['zone_except']['states'] ) && count( $zone['zone_except']['states'] ) ) {
		    					$states_excluded = explode( ',', $zone['zone_except']['states'] );
		    					if(in_array($country, $states_excluded) || in_array($country.":".$state, $states_excluded))
		    						return false;
		    				}
		    				if( isset( $zone['zone_except']['postals'] ) && $zone['zone_except']['postals'] != '' ) {
		    					$postals_excluded = str_replace( ', ', ',', $zone['zone_except']['postals'] );
								foreach( explode( ',', $postals_excluded ) as $code ) {
									$code_clean = str_replace('^', '', $code);
			    					if($code_clean == $zipcode) {
										return false;
			    					} elseif(strstr( $code, '-' )) {
			    						$code_clean = str_replace( ' - ', '-', $code_clean );
			    						list($code_1,$code_2) = explode('-', $code_clean);
			    						if( $zipcode >= $code_1 && $zipcode <= $code_2 )
											return false;
			    					} elseif(strstr( $code, '*' )) {
										$code_length = strlen( $code_clean ) - 1;
										if (strtolower(substr($code_clean, 0, -1)) == strtolower(substr($zipcode, 0, $code_length)))
											return false;
									}
								}
		    				}
		    				return true;
		    			} else return false;
		            case 'postal':
		    			if($country == $zone['zone_country'] || $country.":".$state == $zone['zone_country']) {
		    				$zone['zone_postal'] = str_replace( ', ', ',', $zone['zone_postal'] );
		    				if ( $zone['zone_postal'] != '' ) {
		    					$in_range = false;
								foreach( explode( ',', $zone['zone_postal'] ) as $code ) {
									$code_clean = str_replace('^', '', $code);
			    					if($code_clean == $zipcode) {
										if(!strstr( $code, '^' )) $in_range = true; 
											else return false;
			    					} elseif(strstr( $code, '-' )) {
			    						$code_clean = str_replace( ' - ', '-', $code_clean );
			    						list($code_1,$code_2) = explode('-', $code_clean);
			    						if( $zipcode >= $code_1 && $zipcode <= $code_2 )
											if(!strstr( $code, '^' )) $in_range = true; 
												else return false;
			    					} elseif(strstr( $code, '*' )) {
										$code_length = strlen( $code_clean ) - 1;
										if (strtolower(substr($code_clean, 0, -1)) == strtolower(substr($zipcode, 0, $code_length)))
											if(!strstr( $code, '^' )) $in_range = true; 
												else return false;
									}
								}
								if($in_range) {
				    				if( isset( $zone['zone_except'] ) && $zone['zone_except'] != '' ) {
				    					$postals_excluded = str_replace( ', ', ',', $zone['zone_except'] );
										foreach( explode( ',', $postals_excluded ) as $code ) {
											$code_clean = str_replace('^', '', $code);
					    					if($code_clean == $zipcode) {
												return false;
					    					} elseif(strstr( $code, '-' )) {
					    						$code_clean = str_replace( ' - ', '-', $code_clean );
					    						list($code_1,$code_2) = explode('-', $code_clean);
					    						if( $zipcode >= $code_1 && $zipcode <= $code_2 )
													return false;
					    					} elseif(strstr( $code, '*' )) {
												$code_length = strlen( $code_clean ) - 1;
												if (strtolower(substr($code_clean, 0, -1)) == strtolower(substr($zipcode, 0, $code_length)))
													return false;
											}
										}
				    				}
				    				return true;
								} else return false;
							}
		    			}
					default:
						return false;
				}
			else :
				return false;
			endif;
		}
	}