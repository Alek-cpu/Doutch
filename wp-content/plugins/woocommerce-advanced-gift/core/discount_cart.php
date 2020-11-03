<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class pw_class_woocommerce_gift_discunt_cart {
	public function __construct() {
		$this->rule_check = array();

		$this->product_qty_in_cart = array();

		$this->product_qty_in_cart_gift = array();

		$this->show_gift_item_for_cart = array();

		$this->gift_item_variable = array();

		$this->arr_cart = array();

		$this->item_cart = array();

		$this->cart = array();

		$this->message_gift_cart = '';

		$this->setting = get_option( "pw_gift_options" );

		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'check_session_gift' ) );

		//add_action('woocommerce_before_cart_table', array($this, 'render_redeem_points_message'));

		add_action( 'wp_head', array( $this, 'adjust_cart_rule' ) );
		add_action( 'wp_head', array( $this, 'remove_gift_from_cart' ) );

		add_action( 'wp_ajax_handel_pw_gift_add_adv', [ $this, 'pw_gift_add_adv_function' ] );
		add_action( 'wp_ajax_nopriv_handel_pw_gift_add_adv', [ $this, 'pw_gift_add_adv_function' ] );

		//For Popup Gifts Variable
		add_action( 'wp_head', array( $this, 'show_variation_html_gift' ) );
		add_action( 'wp_ajax_handel_pw_gift_show_variation', [ $this, 'pw_gift_show_variation_function' ] );
		add_action( 'wp_ajax_nopriv_handel_pw_gift_show_variation', [ $this, 'pw_gift_show_variation_function' ] );

		//For display in cart
		add_action( 'woocommerce_cart_contents', array( $this, 'woocommerce_cart_contents_function' ) );
		//add_action( 'woocommerce_mini_cart_contents', array( $this, 'woocommerce_mini_cart_contents_function' ) );
		//add_filter( 'woocommerce_cart_contents_count', array( $this, 'woocommerce_cart_contents_count_function' ),1); 

		add_action( 'woocommerce_review_order_after_cart_contents', array(
			$this,
			'review_order_after_cart_contents_function'
		) );
		add_action( 'woocommerce_new_order', array( $this, 'add_gift_to_order' ), 10, 1 );

		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'wc_add_surcharge' ) );  //Add For Adon fee


		//add_action( 'woocommerce_after_checkout_validation', array($this,'validation_gift_selected'), 10, 2);
	}


	public function woocommerce_mini_cart_contents_function() {
		global $woocommerce, $product;
		$cart_page_id = get_permalink( wc_get_page_id( 'cart' ) );
		if ( substr( $cart_page_id, - 1 ) == "/" ) {
			$cart_page_id = substr( $cart_page_id, 0, - 1 );
		}
		if ( strpos( $cart_page_id, '?' ) !== false ) {
			$cart_page_id = $cart_page_id . '&';
		} else {
			$cart_page_id = $cart_page_id . '?';
		}

		$retrived_group_input_value = WC()->session->get( 'group_order_data' );

		if ( $retrived_group_input_value != '' && is_array( $retrived_group_input_value ) && count
		                                                                                     ( $retrived_group_input_value ) > 0 && count( $this->show_gift_item_for_cart ) > 0 ) {
			foreach ( $retrived_group_input_value as $key => $index ) {
				$gift_index = "";
				$product    = wc_get_product( $index['id_product'] );
				$title      = $product->get_title();
				if ( $product->post_type == 'product_variation' ) {
					$title = $product->get_name();
					//$title = wc_get_formatted_variation( $product->get_variation_attributes(), true );

				}

				$img_url = wp_get_attachment_image_src( $product->get_image_id(), 'thumbnail' );
				$img_url = $img_url[0];
				if ( $img_url == "" ) {
					$img_url = wc_placeholder_img_src();
				}
				$img_html = '<img src="' . $img_url . '" alt="Gift Image" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" width="247" height="296"/>';

				$count = isset( $index['q'] ) ? $index['q'] : 1;

				echo
					'
				<li class="woocommerce-mini-cart-item mini_cart_item">
					<a href="' . $cart_page_id . 'pw_gift_remove=' . $index['id'] . '" class="remove remove_from_cart_button" aria-label="Remove this item">×</a>
					<a href="">' . $img_html . apply_filters( 'woocommerce_checkout_product_title', $title, $product ) . '
					</a>
					<span class="quantity">' . $count . ' × <span class="woocommerce-Price-amount amount">' . $this->setting['free'] . '</span>
					</span>
				</li>';
			}
		}
	}

	public function woocommerce_cart_contents_count_function( $array_sum ) {
		$retrived_group_input_value = WC()->session->get( 'group_order_data' );
		$count                      = 0;
		if ( $retrived_group_input_value != ''
		     && is_array( $retrived_group_input_value )
		     && count( $retrived_group_input_value ) > 0
		     && count( $this->show_gift_item_for_cart ) > 0 ) {
			$count = count( $retrived_group_input_value );
		}

		return $array_sum + $count;

	}

	public function validation_gift_selected( $fields, $errors ) {

		$retrived_group_input_value = WC()->session->get( 'group_order_data' );

		if ( ! is_array( $retrived_group_input_value ) || count( $retrived_group_input_value ) <= 0 ) {
			wc_add_notice( __( 'Please select a Gift For Checkout!!!' ), 'error' );
		}
	}

	public function render_redeem_points_message() {
		if ( $this->message_gift_cart != '' ) {
			$message = '<div class="woocommerce-info wc_points_redeem_earn_points">' . $this->message_gift_cart . '</div>';
			echo sprintf( '%s', $message );
		}
	}

	public function woocommerce_loaded_function() {
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'on_cart_loaded_from_session' ), 99, 1 );
	}

	public function check_session_gift() {
//		echo WC()->cart->cart_contents_total.'-'.time().'<br/>';
		global $woocommerce;
		if ( ! is_array( WC()->cart->cart_contents ) ) {
			return;
		}
		$this->item_cart = WC()->cart->cart_contents;


		$this->product_qty_in_cart = $this->get_cart_item_quantities();

		$retrived_group_input_value = WC()->session->get( 'group_order_data' );
		if ( is_array( $retrived_group_input_value ) && count( $retrived_group_input_value ) > 0 ) {
			$product_qty_in_cart_gift = $this->get_cart_item_quantities_gift();
			foreach ( $retrived_group_input_value as $index => $set ) {

				$product_get = wc_get_product( $set['id_product'] );
				//if($product instanceof WC_Product)
				//{

				//}
				if ( ! is_array( $product_get ) ) {
					continue;
				}

				$get_stock_quantity = $product_get->get_stock_quantity();

				if ( $product_get->is_in_stock() && $get_stock_quantity >= 1 ) {

					$x                           = 0;
					$required_stock_in_cart_gift = isset( $product_qty_in_cart_gift[ $set['id_product'] ] ) ? $product_qty_in_cart_gift[ $set['id_product'] ] : 0;
					$required_stock_in_cart      = isset( $this->product_qty_in_cart[ $product_get->get_stock_managed_by_id() ] ) ? $this->product_qty_in_cart[ $product_get->get_stock_managed_by_id() ] : 0;

					$x = $get_stock_quantity - $required_stock_in_cart;
					if ( $x < $required_stock_in_cart_gift ) {
						if ( $x <= 0 ) {
							unset( $retrived_group_input_value[ $index ] );
						} else {
							$retrived_group_input_value[ $index ]['q'] = $x;
						}
					}
				}
			}
			WC()->session->set( 'group_order_data', $retrived_group_input_value );
		}
		if ( ! $this->check_rule() ) {
			WC()->session->set( 'group_order_data', '' );

			return;
		}

		//add_action( 'woocommerce_after_checkout_validation', array($this,'validation_gift_selected'), 10, 2);

		//if (!$this->check_rule()) {
		//   return;
		// }

		$retrived_group_input_value = WC()->session->get( 'group_order_data' );

		$count_gift      = 0;
		$count_rule_gift = array();
		$gifts_set       = array();

		if ( is_array( $retrived_group_input_value ) && count( $retrived_group_input_value ) > 0 ) {
			foreach ( $retrived_group_input_value as $index => $set ) {
				$count_gift  += $set['q'];
				$gifts_set[] = $set['id'];
				if ( array_key_exists( $set['rule_id'], $count_rule_gift ) ) {
					$count_rule_gift[ $set['rule_id'] ]['q'] += $set['q'];
				} else {
					$count_rule_gift[ $set['rule_id'] ]['q'] = $set['q'];
				}
			}
		}

		//Check if multiselect_gift_count setting is update
		$multiselect_gift_count = $this->setting['multiselect_gift_count'];
		if ( $multiselect_gift_count < $count_gift && $this->setting['multiselect'] == "yes" ) {
			WC()->session->set( 'group_order_data', '' );

			return;
		}

		if ( is_array( $retrived_group_input_value ) && count( $retrived_group_input_value ) > 0 ) {

			foreach ( $retrived_group_input_value as $index => $set ) {

				if ( $retrived_group_input_value[ $index ]['time_add'] != $this->rule_check[ $set['rule_id'] ]['time_rule'] ) {

					unset( $retrived_group_input_value[ $index ] );
					WC()->session->set( 'group_order_data', $retrived_group_input_value );
					continue;
				}

				if ( ! array_key_exists( $index, $this->gift_item_variable['all_gifts'] ) ) {
					unset( $retrived_group_input_value[ $index ] );
					continue;
				}

				for ( $i = 1; $i < $count_gift; $i ++ ) {
					if ( isset( $count_rule_gift[ $set['rule_id'] ]['q'] ) && $count_rule_gift[ $set['rule_id'] ]['q'] >
					                                                          $this->gift_item_variable[ $set['rule_id'] ]['pw_number_gift_allowed'] ) {

						if ( $retrived_group_input_value[ $index ]['q'] <= 1 ) {
							unset( $retrived_group_input_value[ $index ] );

						} else {
							$retrived_group_input_value[ $index ]['q'] --;
						}
						$count_rule_gift[ $set['rule_id'] ]['q'] --;
					}
				}

				if ( array_key_exists( $set['rule_id'], $count_rule_gift ) && $retrived_group_input_value != '' && count
				                                                                                                   ( $retrived_group_input_value ) > 0 &&
				     $set['q'] > $this->gift_item_variable[ $set['rule_id'] ]['pw_number_gift_allowed'] ) {
					if ( isset( $retrived_group_input_value[ $index ]['q'] ) && $retrived_group_input_value[ $index ]['q'] <=
					                                                            1 ) {
						unset( $retrived_group_input_value[ $index ] );
					} else {
						$retrived_group_input_value[ $index ]['q'] = $this->gift_item_variable[ $set['rule_id'] ]['pw_number_gift_allowed'];
					}
					$count_rule_gift[ $set['rule_id'] ]['q'] --;
				}
			}
		}
		WC()->session->set( 'group_order_data', $retrived_group_input_value );
		if ( is_array( $this->show_gift_item_for_cart ) && count( $this->show_gift_item_for_cart ) > 0 ) {
			add_action( 'woocommerce_after_cart_table', array( $this, 'pw_woocommerce_after_cart_table_function' ), 1 );
		}
	}

	public function check_rule() {
		global $woocommerce, $wpdb, $product;
		$this->cart['amount']     = 0;
		$this->cart['quantities'] = "";

		if ( ! is_array( WC()->cart->cart_contents ) ) {
			return false;
		}
		$this->item_cart = WC()->cart->cart_contents;

		$this->rule_check = $this->get_rule_db();
		$quantity         = 0;

		foreach ( $this->item_cart as $cart_item_key => $cart_item ) {
			$quantity = ( isset( $cart_item['quantity'] ) && $cart_item['quantity'] ) ? $cart_item['quantity'] : 1;
		}
		$this->cart['amount']     = $this->calculate_cart_subtotal();
		$this->cart['quantities'] = $woocommerce->cart->cart_contents_count;

		$this->show_gift_item_for_cart = array();

		$this->gift_item_variable = array();

		$rules = array();
		foreach ( $this->rule_check as $rules_item => $rule ) {
			$check_and_go_per_rule = true;
			if ( has_filter( 'custom_gift_field_per_rule_check' ) ) {
				$check_and_go_per_rule = apply_filters( 'custom_gift_field_per_rule_check', $rule );
			}
			if ( ! $check_and_go_per_rule ) {
				continue;
			}
			if ( $rule['repeat'] != "" && $rule['repeat'] != "none" ) {
				$repeat = 1;
				if ( $rule['repeat'] == 'repeat_qty' ) {
					$repeat = $this->check_for_repeat_qty( $rule );
					if ( $repeat == 0 ) {
						continue;
					}
				} elseif ( $rule['repeat'] == 'repeat_sub' ) {
					$repeat = $this->check_for_repeat_sum( $rule );
					if ( $repeat == 0 ) {
						continue;
					}
				}
				$rule['pw_number_gift_allowed'] = $repeat * $rule['pw_number_gift_allowed'];
			}

			if ( ! $this->check_candition_rules_basic( $rule ) ) {
				continue;
			}

			if ( ! $this->check_candition_rules_product( $rule ) ) {
				continue;
			}
			if ( ! $this->check_candition_rules_exclude_product( $rule ) ) {
				continue;
			}
			if ( ! $this->check_candition_rules_category_product( $rule ) ) {
				continue;
			}
			if ( ! $this->check_candition_rules_exclude_category_product( $rule ) ) {
				continue;
			}
			if ( ! $this->check_candition_rules_brand_product( $rule ) ) {
				continue;
			}
			//print_r(WC()->session->get('shipping_for_package_0'));
			//die;
			//if (!$this->check_candition_shipping($rule)) {
			//    continue;
			//}
			if ( $rule['pw_number_gift_allowed'] == '' || $rule['pw_number_gift_allowed'] == 0 ) {
				$rule['pw_number_gift_allowed'] = 1;
			}

			if ( ! is_array( $rule['all_gifts_items'] ) || count( $rule['all_gifts_items'] ) <= 0 ) {
				continue;
			}

			$this->gift_item_variable[ $rule['pw_id'] ] = array(
				"rule_id"                => $rule['pw_id'],
				"disable_if"             => $rule['disable_if'],
				"pw_number_gift_allowed" => $rule['pw_number_gift_allowed'],
				"can_several_gift"       => $rule['can_several_gift'],
				"time_rule"              => $rule['time_rule'],
				"auto"                   => $rule['gift_auto_to_cart'],
				"gifts"                  => $rule['all_gifts_items'],
			);

			$count_auto_add = 0;
			//For Auto Add
			//Create all gifts with varation
			foreach ( $rule['all_gifts_items'] as $item ) {
				$id                                            = "";
				$id                                            = $rule['pw_id'] . '-' . $item;
				$this->gift_item_variable ['all_gifts'][ $id ] = array(
					'rule_id'    => $rule['pw_id'],
					'id_product' => $item,
				);

				//For Auto Add
				//$count_auto_add = 0;

				if ( $rule['gift_auto_to_cart'] == "yes" ) {
					for ( $i = 1; $i <= $rule['pw_number_gift_allowed']; $i ++ ) {

						if ( $count_auto_add < $rule['pw_number_gift_allowed'] ) {

							if ( $this->pw_insert_gift_cart( $id ) ) {
								$count_auto_add ++;
							} else {
								break;
							}
						}
					}
				}
			}


			if ( $rule['pw_gifts'] != "" ) {
				//$current_session_order_id = isset( WC()->session->order_awaiting_payment ) ? absint( WC()->session->order_awaiting_payment ) : 0;
				if ( is_array( $rule['pw_gifts'] ) && count( $rule['pw_gifts'] ) > 0 ) {
					//Create gift with variable
					foreach ( $rule['pw_gifts'] as $gift ) {
						$id                                   = "";
						$id                                   = $rule['pw_id'] . '-' . $gift;
						$this->show_gift_item_for_cart[ $id ] = array(
							"item"                   => $gift,
							"rule_id"                => $rule['pw_id'],
							"key"                    => $id,
							"disable_if"             => $rule['disable_if'],
							"pw_number_gift_allowed" => $rule['pw_number_gift_allowed'],
							"can_several_gift"       => $rule['can_several_gift'],
							"auto"                   => $rule['gift_auto_to_cart'],
						);
					}
				}
			}
		}

		/*echo '<br/>';
        echo '<br/>';
        echo '<pre>';
        print_r($this->gift_item_variable);
        echo '<br/>';
        echo '<br/>';
        print_r($this->show_gift_item_for_cart);
        echo '</pre>';
        
		*/
		if ( is_array( $this->show_gift_item_for_cart ) && count( $this->show_gift_item_for_cart ) > 0 ) {
			return true;
		}

		return false;
	}

	public function get_rule_db() {
		$query_meta_query   = array( 'relation' => 'AND' );
		$query_meta_query[] = array(
			'key'     => 'status',
			'value'   => "active",
			'compare' => '=',
		);
		$matched_products   = get_posts(
			array(
				'post_type'     => 'pw_gift_rule',
				'numberposts'   => - 1,
				'post_status'   => 'publish',
				'fields'        => 'ids',
				'no_found_rows' => true,
				'orderby'       => 'modified',
				'meta_query'    => $query_meta_query,
			)
		);
		foreach ( $matched_products as $pr ) {
			$is_ok             = true;
			$product_depends   = $category_depend = $pw_cart_amount = $criteria_nb_products_min = $criteria_nb_products_max = $pw_cart_amount_min = $pw_cart_amount_max = $brand_depends = $pw_brand_depends = $gift_auto_to_cart = $pw_id = $disable_if = "";
			$gift_auto_to_cart = get_post_meta( $pr, 'gift_auto_to_cart', true );
			$brand_depends     = $pw_brand_depends = $pw_brand_depends_method = '';
			if ( defined( 'plugin_dir_url_pw_woo_brand' ) ) {
				$brand_depends           = get_post_meta( $pr, 'brand_depends', true );
				$pw_brand_depends        = get_post_meta( $pr, 'pw_brand_depends', true );
				$pw_brand_depends_method = get_post_meta( $pr, 'pw_brand_depends_method', true );
			}
			$pw_cart_amount           = get_post_meta( $pr, 'pw_cart_amount', true );
			$pw_cart_amount_min       = get_post_meta( $pr, 'pw_cart_amount_min', true );
			$pw_cart_amount_max       = get_post_meta( $pr, 'pw_cart_amount_max', true );
			$criteria_nb_products_min = get_post_meta( $pr, 'criteria_nb_products_min', true );
			$criteria_nb_products_max = get_post_meta( $pr, 'criteria_nb_products_max', true );
			$criteria_nb_products_op  = get_post_meta( $pr, 'criteria_nb_products_op', true );
			$criteria_nb_products     = get_post_meta( $pr, 'criteria_nb_products', true );
			$criteria_nb_products     = ( $criteria_nb_products != "" ? $criteria_nb_products : 0 );
			$cart_amount_op           = get_post_meta( $pr, 'cart_amount_op', true );
			$disable_if               = get_post_meta( $pr, 'disable_if', true );
			$pw_number_gift_allowed   = get_post_meta( $pr, 'pw_number_gift_allowed', true );
			$pw_cart_amount           = ( $pw_cart_amount != "" ? $pw_cart_amount : 0 );
			$r                        = "";
			$pw_to                    = strtotime( get_post_meta( $pr, 'pw_to', true ) );
			$pw_from                  = strtotime( get_post_meta( $pr, 'pw_from', true ) );

			$pw_gifts          = get_post_meta( $pr, 'pw_gifts', true );
			$pw_gifts_metod    = get_post_meta( $pr, 'pw_gifts_metod', true );
			$pw_gifts_category = get_post_meta( $pr, 'pw_gifts_category', true );

			$users_depends = get_post_meta( $pr, 'users_depends', true );
			$pw_users      = get_post_meta( $pr, 'pw_users', true );

			$pw_roles      = get_post_meta( $pr, 'pw_roles', true );
			$roles_depends = get_post_meta( $pr, 'roles_depends', true );

			$pw_exclude_roles      = get_post_meta( $pr, 'pw_exclude_roles', true );
			$exclude_roles_depends = get_post_meta( $pr, 'exclude_roles_depends', true );

			$is_coupons     = get_post_meta( $pr, 'is_coupons', true );
			$order_op_count = get_post_meta( $pr, 'order_op_count', true );
			$order_count    = get_post_meta( $pr, 'order_count', true );

			$pw_product_depends         = get_post_meta( $pr, 'pw_product_depends', true );
			$pw_product_depends_temp    = get_post_meta( $pr, 'pw_product_depends_temp', true );
			$all_gifts_items            = get_post_meta( $pr, 'all_gifts_items', true );
			$product_depends            = get_post_meta( $pr, 'product_depends', true );
			$pw_product_depends_method  = get_post_meta( $pr, 'pw_product_depends_method', true );
			$pw_category_depends_method = get_post_meta( $pr, 'pw_category_depends_method', true );
			$exclude_product_depends    = get_post_meta( $pr, 'exclude_product_depends', true );
			$pw_exclude_product_depends = get_post_meta( $pr, 'pw_exclude_product_depends', true );

			$pw_category_depends         = get_post_meta( $pr, 'pw_category_depends', true );
			$exclude_pw_category_depends = get_post_meta( $pr, 'exclude_pw_category_depends', true );
			$category_depends            = get_post_meta( $pr, 'category_depends', true );
			$exclude_category_depends    = get_post_meta( $pr, 'exclude_category_depends', true );
			$gift_auto_to_cart           = get_post_meta( $pr, 'gift_auto_to_cart', true );
			$pw_limit_per_rule           = get_post_meta( $pr, 'pw_limit_per_rule', true );
			$pw_limit_cunter             = get_post_meta( $pr, 'pw_limit_cunter', true );
			$pw_limit_per_user           = get_post_meta( $pr, 'pw_limit_per_user', true );
			$pw_register_user            = get_post_meta( $pr, 'pw_register_user', true );
			$schedule_type               = get_post_meta( $pr, 'schedule_type', true );
			$pw_weekly                   = get_post_meta( $pr, 'pw_weekly', true );
			$pw_daily                    = get_post_meta( $pr, 'pw_daily', true );
			$pw_monthly                  = get_post_meta( $pr, 'pw_monthly', true );
			$can_several_gift            = get_post_meta( $pr, 'can_several_gift', true );
			$repeat                      = get_post_meta( $pr, 'repeat', true );
			$pfx_date                    = get_the_modified_date( 'Y/m/d g:i:s', $pr );

			$this->rule_check[ $pr ] = array(
				"pw_id"                       => $pr,
				"disable_if"                  => $disable_if,
				"pw_number_gift_allowed"      => ( $pw_number_gift_allowed <= 0 ? 1 : $pw_number_gift_allowed ),
				"pw_gifts"                    => $pw_gifts,
				"pw_gifts_metod"              => $pw_gifts_metod,
				"is_coupons"                  => $is_coupons,
				"order_count"                 => $order_count,
				"order_op_count"              => $order_op_count,
				"pw_gifts_category"           => $pw_gifts_category,
				"pw_roles"                    => $pw_roles,
				"roles_depends"               => $roles_depends,
				"pw_exclude_roles"            => $pw_exclude_roles,
				"exclude_roles_depends"       => $exclude_roles_depends,
				"users_depends"               => $users_depends,
				"pw_users"                    => $pw_users,
				"pw_to"                       => $pw_to,
				"pw_from"                     => $pw_from,
				"criteria_nb_products"        => $criteria_nb_products,
				"pw_cart_amount"              => $pw_cart_amount,
				"pw_cart_amount_min"          => $pw_cart_amount_min,
				"pw_cart_amount_max"          => $pw_cart_amount_max,
				"criteria_nb_products_min"    => $criteria_nb_products_min,
				"criteria_nb_products_max"    => $criteria_nb_products_max,
				"criteria_nb_products_op"     => $criteria_nb_products_op,
				"cart_amount_op"              => $cart_amount_op,
				"product_depends"             => $product_depends,
				"pw_product_depends"          => $pw_product_depends,
				"pw_product_depends_temp"     => $pw_product_depends_temp,
				"all_gifts_items"             => $all_gifts_items,
				"pw_product_depends_method"   => $pw_product_depends_method,
				"exclude_product_depends"     => $exclude_product_depends,
				"pw_exclude_product_depends"  => $pw_exclude_product_depends,
				"category_depends"            => $category_depends,
				"exclude_category_depends"    => $exclude_category_depends,
				"pw_category_depends"         => $pw_category_depends,
				"exclude_pw_category_depends" => $exclude_pw_category_depends,
				"pw_category_depends_method"  => $pw_category_depends_method,
				"brand_depends"               => $brand_depends,
				"pw_brand_depends"            => $pw_brand_depends,
				"pw_brand_depends_method"     => $pw_brand_depends_method,
				"gift_auto_to_cart"           => $gift_auto_to_cart,
				"pw_limit_per_rule"           => $pw_limit_per_rule,
				"pw_limit_per_user"           => $pw_limit_per_user,
				"pw_limit_cunter"             => $pw_limit_cunter,
				"pw_register_user"            => $pw_register_user,
				"schedule_type"               => $schedule_type,
				"pw_weekly"                   => $pw_weekly,
				"pw_daily"                    => $pw_daily,
				"pw_monthly"                  => $pw_monthly,
				"time_rule"                   => $pfx_date,
				"repeat"                      => ( $repeat == '' ? 'none' : $repeat ),
				"can_several_gift"            => ( $can_several_gift == 'yes' ? 'yes' : 'no' ),
			);
//            echo '<br/>';
//            echo '<br/>a';
//            print_r($all_gifts_items);
		}

		//print_r($this->rule_check);

		return $this->rule_check;
	}

	public function get_cart_item_quantities() {
		$quantities = array();
		foreach ( $this->item_cart as $cart_item_key => $values ) {
			$product                                           = $values['data'];
			$quantities[ $product->get_stock_managed_by_id() ] = isset( $quantities[ $product->get_stock_managed_by_id() ] ) ? $quantities[ $product->get_stock_managed_by_id() ] + $values['quantity'] : $values['quantity'];
		}

		return $quantities;
	}

	public function get_cart_item_quantities_gift() {
		$quantities                 = array();
		$retrived_group_input_value = WC()->session->get( 'group_order_data' );

		if ( is_array( $retrived_group_input_value ) && count( $retrived_group_input_value ) > 0 ) {

			foreach ( $retrived_group_input_value as $index => $set ) {
				if ( isset( $set['id_product'] ) ) {
					$quantities[ $set['id_product'] ] = isset( $quantities[ $set['id_product'] ] ) ? $quantities[ $set['id_product'] ] + $set['q'] : $set['q'];
				}
			}
		}

		return $quantities;
	}

	public function check_for_repeat_qty( $rule ) {
		$count_in_rule    = $rule['criteria_nb_products'];
		$quantity_in_cart = $this->check_nb_product( $rule );
		if ( count( $quantity_in_cart ) <= 0 ) {
			return false;
		}
		$count_in_cart = 0;
		foreach ( $quantity_in_cart as $cunter ) {
			$count_in_cart += $cunter;
		}
		if ( $count_in_cart < $count_in_rule ) {
			return false;
		}
		if ( $count_in_rule == 0 || $count_in_rule == '' || $count_in_cart <= 0 ) {
			return false;
		}

		return ( floor( $count_in_cart / $count_in_rule ) );
	}

	public function check_for_repeat_sum( $rule ) {
		$pw_cart_amount = $rule['pw_cart_amount'];
		$amount_in_cart = $this->cart['amount'];

		if ( $amount_in_cart <= 0 ) {
			return false;
		}
		if ( $amount_in_cart <= $pw_cart_amount ) {
			return false;
		}
		if ( $pw_cart_amount == 0 || $pw_cart_amount == '' || $amount_in_cart <= 0 ) {
			return false;
		}

		return ( floor( $amount_in_cart / $pw_cart_amount ) );
	}

	public function review_order_after_cart_contents_function() {
		global $woocommerce;

		if ( ! $this->check_rule() ) {
			return;
		}
		$retrived_group_input_value = WC()->session->get( 'group_order_data' );
		if ( ! is_array( $this->gift_item_variable ) || count( $this->gift_item_variable ) <= 0 || $this->gift_item_variable['all_gifts'] <= 0 ) {
			return;
		}

		if ( $retrived_group_input_value != '' && is_array( $retrived_group_input_value ) && count
		                                                                                     ( $retrived_group_input_value ) > 0 ) {
			foreach ( $retrived_group_input_value as $key => $index ) {
				$img_url = $img_html = $title = '';
				$product = wc_get_product( $index['id_product'] );
				$title   = $product->get_title();
				if ( $product->post_type == 'product_variation' ) {
					$title = $product->get_name();
				}
				$price_p = $this->setting['free'];
				$count   = isset( $retrived_group_input_value[ $key ]['q'] ) ? $retrived_group_input_value[ $key ]['q'] : 1;
				echo '<tr>
                          <td class="product-name">' .
				     apply_filters( 'woocommerce_checkout_product_title', $title, $product ) . ' ' .
				     '<strong class="product-quantity"> × ' . sprintf( '%s', $count ) . '</strong>' .
				     '</td>
                          <td class="product-total" style="color: #00aa00;">' . sprintf( '%s', $price_p ) . '</td>
                      </tr>';
			}
		}
	}

	public function check_candition_rules_product( $rule ) {
		if ( $rule['product_depends'] == 'yes' ) {

			if ( $rule['pw_product_depends_method'] == 'all' ) {
				$id_product = array();
				foreach ( $this->item_cart as $cart_item_key => $cart_item ) {
					if ( $cart_item['data']->post_type == 'product_variation' ) {
						$id_product [] = $cart_item['variation_id'];
					} else {
						$id_product [] = $cart_item['product_id'];
					}
				}
				foreach ( $rule['pw_product_depends_temp'] as $pw_product_depends_id ) {
					if ( ! in_array( $pw_product_depends_id, $id_product ) ) {
						return false;
					}
				}
			} else {
				foreach ( $this->item_cart as $cart_item_key => $cart_item ) {
					$id_product = "";
					$id_product = $cart_item['product_id'];
					if ( $cart_item['data']->post_type == 'product_variation' ) {
						$id_product = $cart_item['variation_id'];
					}
					if ( in_array( $id_product, $rule['pw_product_depends_temp'] ) ) {
						return true;
					}
				}

				return false;
			}
		}

		if ( $rule['exclude_product_depends'] == 'yes' ) {
			foreach ( $this->item_cart as $cart_item_key => $cart_item ) {
				$id_product = "";
				$id_product = $cart_item['product_id'];
				if ( $cart_item['data']->post_type == 'product_variation' ) {
					$id_product = $cart_item['variation_id'];
				}
				if ( in_array( $id_product, $rule['pw_exclude_product_depends'] ) ) {
					return false;
				}
			}

			return true;
		}

		return true;
	}

	public function check_candition_rules_exclude_product( $rule ) {
		if ( $rule['exclude_product_depends'] == 'yes' ) {
			foreach ( $this->item_cart as $cart_item_key => $cart_item ) {
				$id_product = "";
				$id_product = $cart_item['product_id'];
				if ( $cart_item['data']->post_type == 'product_variation' ) {
					$id_product = $cart_item['variation_id'];
				}

				if ( in_array( $id_product, $rule['pw_exclude_product_depends'] ) ) {

					return false;
				}
			}

			return true;
		}

		return true;
	}

	public function check_candition_rules_brand_product( $rule ) {
		if ( $rule['brand_depends'] == 'yes' ) {
			if ( $rule['pw_brand_depends_method'] == 'all' ) {
				foreach ( $rule['pw_brand_depends'] as $pw_brand_depends ) {
					$flag = false;
					foreach ( $this->item_cart as $cart_item_key => $cart_item ) {
						if ( ! in_array( $pw_brand_depends, $this->get_cart_item_brands( $cart_item ) ) ) {
							$flag = false;

						} else {
							$flag = true;
							break;
						}
					}
					if ( ! $flag ) {
						return false;
					}
				}
			} else {
				foreach ( $this->item_cart as $cart_item_key => $cart_item ) {
					if ( count( array_intersect( $this->get_cart_item_brands( $cart_item ), $rule['pw_brand_depends'] ) ) != 0 ) {
						return true;
					}
				}

				return false;
			}
		}

		return true;
	}

	public function check_candition_shipping( $rule ) {
		foreach ( WC()->session->get( 'shipping_for_package_0' )['rates'] as $method_id => $rate ) {
			if ( WC()->session->get( 'chosen_shipping_methods' )[0] == $method_id ) {
				$rate_label         = $rate->label; // The shipping method label name
				$rate_cost_excl_tax = floatval( $rate->cost ); // The cost excluding tax
				// The taxes cost
				$rate_taxes = 0;
				foreach ( $rate->taxes as $rate_tax ) {
					$rate_taxes += floatval( $rate_tax );
				}

				// The cost including tax
				$rate_cost_incl_tax = $rate_cost_excl_tax + $rate_taxes;

				echo '<p class="shipping-total">
					<strong class="label">' . $rate_label . ': </strong>
					<span class="totals">' . WC()->cart->get_cart_shipping_total() . '</span>
				</p>';
				break;
			}
		}

		return true;
	}

	public function check_candition_rules_category_product( $rule ) {
		if ( $rule['category_depends'] == 'yes' ) {

			if ( $rule['pw_category_depends_method'] == 'all' ) {
				foreach ( $rule['pw_category_depends'] as $pw_category_depends ) {
					$flag = false;
					foreach ( $this->item_cart as $cart_item_key => $cart_item ) {
						if ( ! in_array( $pw_category_depends, $this->get_cart_item_categories( $cart_item ) ) ) {
							$flag = false;

						} else {
							$flag = true;
							break;
						}
					}
					if ( ! $flag ) {
						return false;
					}
				}
			} else {

				foreach ( $this->item_cart as $cart_item_key => $cart_item ) {

					if ( count( array_intersect( $this->get_cart_item_categories( $cart_item ), $rule['pw_category_depends'] ) ) != 0 ) {
						return true;
					}
				}

				return false;
			}
		}

		return true;
	}

	public function check_candition_rules_exclude_category_product( $rule ) {
		/* Cart Check */
		if ( $rule['exclude_category_depends'] == 'yes' ) {

			foreach ( $this->item_cart as $cart_item_key => $cart_item ) {
				if ( count( array_intersect( $this->get_cart_item_categories( $cart_item ), $rule['exclude_pw_category_depends'] ) ) > 0 ) {
					return false;
				}
			}

			return true;
		}

		return true;

		/* Line to Line  check
		if ($rule['exclude_category_depends'] == 'yes') {
			$flag = true;
            foreach ($this->item_cart as $cart_item_key => $cart_item) {
                if (count(array_intersect($this->get_cart_item_categories($cart_item), $rule['exclude_pw_category_depends'])) > 0) {
					$flag = true;
					break;
                    //return false;
                }
				else{
					$flag = false;
				}
            }
            return true;
        }

        return true;	*/

	}

	public function check_candition_rules_basic( $rule ) {
		global $woocommerce;
		$multiselect_cart_amount = $this->setting['multiselect_cart_amount'];
		$multiselect_gift_count  = $this->setting['multiselect_gift_count'];

		if ( $rule['pw_register_user'] == "yes" && ! is_user_logged_in() ) {
			return false;
		} elseif ( $rule['pw_register_user'] == "yes" && is_user_logged_in() && is_array( $rule['pw_limit_cunter'] ) && $rule['pw_limit_per_user'] != '' ) {
			$user_id = get_current_user_id();
			$number  = 0;
			foreach ( $rule['pw_limit_cunter']['user_info'] as $user_info ) {
				if ( $user_info['id'] == $user_id ) {
					$number = $user_info['number'];
					break;
				}
			}

			if ( $number >= $rule['pw_limit_per_user'] ) {
				return false;
			}
		}

		if ( $rule['pw_limit_per_rule'] != 0 && $rule['pw_limit_per_rule'] != "" && is_array( $rule['pw_limit_cunter'] ) && $rule['pw_limit_cunter']['count'] >= $rule['pw_limit_per_rule'] ) {
			return false;
		}
		if ( $rule['roles_depends'] == "yes" ) {
			if ( count( array_intersect( $this->pw_current_user_roles(), $rule['pw_roles'] ) ) < 1 ) {
				return false;
			}
		}
		if ( $rule['exclude_roles_depends'] == "yes" ) {
			if ( count( array_intersect( $this->pw_current_user_roles(), $rule['pw_exclude_roles'] ) ) > 0 ) {
				return false;
			}
		}

		if ( $rule['users_depends'] == 'yes' ) {
			$current_user = wp_get_current_user();
			if ( isset( $rule['pw_users'] ) && ! in_array( $current_user->ID, $rule['pw_users'] ) ) {

				return false;
			}
		}

		if ( isset( $rule['pw_to'] ) && ! empty( $rule['pw_to'] ) && ( $rule['pw_to'] ) < time() ) {
			return false;
		}
		if ( isset( $rule['pw_from'] ) && ! empty( $rule['pw_from'] ) && ( $rule['pw_from'] > time() ) ) {
			return false;
		}

		if ( isset( $rule['schedule_type'] ) && $rule['schedule_type'] != 'unlimited' ) {

			if ( $rule['schedule_type'] == 'daily' ) {
				$ret       = true;
				$t         = date( "d", time() );
				$month_end = date( 'd', strtotime( 'last day of this month', time() ) );
				if ( in_array( 'last', $rule['pw_daily'] ) && $month_end == $t ) {
					$ret = false;
				}

				if ( in_array( $t, $rule['pw_daily'] ) ) {
					$ret = false;
				}
				if ( $ret ) {
					return false;
				}
			} elseif ( $rule['schedule_type'] == 'weekly' ) {
				if ( ! is_array( $rule['pw_weekly'] ) ) {
					return false;
				}
				$t = date( "l", time() );
				if ( ! in_array( $t, $rule['pw_weekly'] ) ) {
					return false;
				}

			} elseif ( $rule['schedule_type'] == 'monthly' ) {
				$each          = $rule['pw_monthly']['each'];
				$day           = $rule['pw_monthly']['day'];
				$time_for_gift = date( 'd', strtotime( $each . ' ' . $day . ' of ' . date( 'M Y' ) ) );
				$t             = date( "d", time() );
				if ( $time_for_gift != $t ) {
					return false;
				}
			}
		}

		if ( isset( $rule['is_coupons'] ) && $rule['is_coupons'] == 'yes' ) {

			if ( WC()->cart->get_coupons() ) {
				return false;
			}
		}

		if ( isset( $rule['order_count'] ) && ! empty( $rule['order_count'] ) ) {
			global $wpdb;
			if ( ! is_user_logged_in() ) {
				return false;
			}
			$user_id = get_current_user_id();
			$count   = $wpdb->get_var( "SELECT COUNT(*)
				FROM $wpdb->posts as posts

				LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id

				WHERE   meta.meta_key       = '_customer_user'
				AND     posts.post_type     IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "')
				AND     posts.post_status   = 'wc-completed'
				AND     meta_value          = $user_id
			" );

			$count = absint( $count );
			switch ( $rule['order_op_count'] ) {
				case '>':
					if ( $count < $rule['order_count'] ) {
						return false;
					}
					break;
				case '<':
					if ( $count > $rule['order_count'] ) {
						return false;
					}
					break;
				case '==':
					if ( $count != $rule['order_count'] ) {
						return false;
					}
					break;
			}
		}

		if ( isset( $rule['criteria_nb_products'] ) && ( $rule['repeat'] != 'repeat_qty' || $rule['repeat'] == 'none' ) ) {
			$count    = 0;
			$quantity = $this->check_nb_product( $rule );
			if ( count( $quantity ) <= 0 ) {
				return false;
			}
			foreach ( $quantity as $cunter ) {
				$count += $cunter;
			}

			switch ( $rule['criteria_nb_products_op'] ) {
				case '>':
					if ( $count <= $rule['criteria_nb_products'] ) {

						return false;
					}
					break;
				case '<':
					if ( $count >= $rule['criteria_nb_products'] ) {
						return false;
					}
					break;
				case '==':
					if ( $count != $rule['criteria_nb_products'] ) {
						return false;
					}
					break;
				case 'min_max':

					if ( $count < $rule['criteria_nb_products_min'] || $count > $rule['criteria_nb_products_max'] ) {
						return false;
					}
					break;

			}
		}

		if ( isset( $rule['pw_cart_amount'] ) && ( $rule['repeat'] != 'repeat_sub' || $rule['repeat'] == 'none' ) ) {
			$amount_product = $this->check_amount_product( $rule );

			if ( $amount_product < 0 ) {
				return false;
			}
			$cart_balance = 0;
			switch ( $rule['cart_amount_op'] ) {
				case '>':
					if ( ! empty( $rule['pw_cart_amount'] ) && $amount_product < $rule['pw_cart_amount'] ) {
						$cart_balance            = $rule['pw_cart_amount'] - $amount_product;
						$this->message_gift_cart = 'Spend up to  ' . wc_price( $cart_balance ) . '  and select your free gift ';

						return false;
					}
					break;
				case '<':
					if ( ! empty( $rule['pw_cart_amount'] ) && $amount_product > $rule['pw_cart_amount'] ) {
						return false;
					}
					break;
				case '==':
					if ( ! empty( $rule['pw_cart_amount'] ) && $amount_product != $rule['pw_cart_amount'] ) {
						return false;
					}
					break;
				case 'min_max':
					// list($min_value, $max_value) = explode(':', $rule['pw_cart_amount']);
					if ( $amount_product < $rule['pw_cart_amount_min'] || $amount_product > $rule['pw_cart_amount_max'] ) {
						return false;
					}
					break;
			}
		}

		return true;
	}

	public function check_nb_product( $rule ) {
		$cart_items = array();
		foreach ( $this->item_cart as $cart_item_key => $cart_item ) {
			//$cart_items[$cart_item_key] = $cart_item;
			$inter = false;
			if ( $rule['exclude_category_depends'] == 'yes' ) {
				$categories = $this->cart_categories( $cart_item );
				if ( isset( $rule['exclude_pw_category_depends'] ) && count( array_intersect( $categories, $rule['exclude_pw_category_depends'] ) ) > 0 ) {
					//$cart_items[$cart_item_key] = $cart_item;
					$inter = true;
				}
			}
			if ( $rule['category_depends'] == 'yes' && $inter == false ) {
				$categories = $this->cart_categories( $cart_item );
				if ( isset( $rule['pw_category_depends'] ) && count( array_intersect( $categories, $rule['pw_category_depends'] ) ) > 0 ) {
					$cart_items[ $cart_item_key ] = $cart_item;
					$inter                        = true;
				}
			}
			if ( $rule['brand_depends'] == 'yes' && $inter == false ) {
				$categories = $this->get_cart_item_brands( $cart_item );
				if ( count( array_intersect( $categories, $rule['pw_brand_depends'] ) ) > 0 ) {
					$cart_items[ $cart_item_key ] = $cart_item;
					$inter                        = true;
				}
			}
			if ( $rule['product_depends'] == 'yes' && $inter == false ) {
				$id_product = "";
				$id_product = $cart_item['product_id'];
				if ( $cart_item['data']->post_type == 'product_variation' ) {
					$id_product = $cart_item['variation_id'];
				}
				if ( isset( $rule['pw_product_depends_temp'] ) && is_array( $rule['pw_product_depends_temp'] ) && in_array( $id_product, $rule['pw_product_depends_temp'] ) ) {
					$cart_items[ $cart_item_key ] = $cart_item;
				}
				$inter = true;
			}
			if ( $inter == false && $rule['product_depends'] != 'yes' && $rule['category_depends'] != 'yes' && $rule['brand_depends'] != 'yes' ) {

				$cart_items[ $cart_item_key ] = $cart_item;
			}
		}
		$quantity = array();
		foreach ( $cart_items as $item_key => $item ) {
			if ( isset( $quantity[ $item['product_id'] ] ) ) {
				$quantity[ $item['product_id'] ] += $item['quantity'];
			} else {
				$quantity[ $item['product_id'] ] = $item['quantity'];
			}
		}

		return $quantity;

	}

	public function check_amount_product( $rule ) {
		$cart_items = array();
		foreach ( $this->item_cart as $cart_item_key => $cart_item ) {
			//$cart_items[$cart_item_key] = $cart_item;
			$inter = false;
			if ( $rule['category_depends'] == 'yes' ) {
				$categories = $this->cart_categories( $cart_item );
				if ( isset( $rule['pw_category_depends'] ) && count( array_intersect( $categories, $rule['pw_category_depends'] ) ) > 0 ) {
					$cart_items[ $cart_item_key ] = $cart_item;
					$inter                        = true;
				}
			}
			if ( $rule['brand_depends'] == 'yes' && $inter == false ) {
				$categories = $this->get_cart_item_brands( $cart_item );
				if ( count( array_intersect( $categories, $rule['pw_brand_depends'] ) ) > 0 ) {
					$cart_items[ $cart_item_key ] = $cart_item;
					$inter                        = true;
				}
			}
			if ( $rule['product_depends'] == 'yes' && $inter == false ) {
				$id_product = "";
				$id_product = $cart_item['product_id'];
				if ( $cart_item['data']->post_type == 'product_variation' ) {
					$id_product = $cart_item['variation_id'];
				}
				if ( isset( $rule['pw_product_depends_temp'] ) && in_array( $id_product, $rule['pw_product_depends_temp'] ) ) {
					$cart_items[ $cart_item_key ] = $cart_item;
				}
				$inter = true;
			}
			if ( $inter == false && $rule['product_depends'] != 'yes' && $rule['category_depends'] != 'yes' && $rule['brand_depends'] != 'yes' ) {

				$cart_items[ $cart_item_key ] = $cart_item;
			}
		}

		$cart_subtotal = 0;
		/*   foreach ($cart_items as $item_key => $item) {
            $quantitye = (isset($item['quantity']) && $item['quantity']) ? $item['quantity'] : 1;
            $cart_subtotal += $item['data']->get_price() * $quantitye;
        }

		*/
		$include_tax = wc_tax_enabled();
		foreach ( $cart_items as $item_key => $item ) {
			// Add line subtotal
			$cart_subtotal += $item['line_subtotal'];
			// Add line subtotal tax
			if ( isset( $item['line_subtotal_tax'] ) && $include_tax ) {
				$cart_subtotal += $item['line_subtotal_tax'];
			}
		}
//		echo WC()->cart->cart_contents_total.'-'.time().'<br/>';
//		return WC()->cart->cart_contents_total;
		return $cart_subtotal;
	}

	public function cart_categories( $cart_item ) {
		$categories = array();
		$current    = wp_get_post_terms( $cart_item['product_id'], 'product_cat' );
		foreach ( $current as $category ) {
			$categories[] = $category->term_id;
		}

		return $categories;
	}

	public function pw_current_user_roles() {
		global $current_user;
		wp_get_current_user();

		return $current_user->roles;
	}

	public function pw_woocommerce_after_cart_table_function() {
		global $woocommerce;

		$product_item = '';
		// $setting = get_option("pw_gift_options");
		$multiselect_cart_amount = $this->setting['multiselect_cart_amount'];
		$multiselect_gift_count  = $this->setting['multiselect_gift_count'];
		$add_gift                = $this->setting['add_gift'];
		$select_gift             = isset( $this->setting['select_gift'] ) ? $this->setting['select_gift'] : 'select Gift';
		$select_gift             = ( $select_gift != '' ) ? $select_gift : 'select Gift';

		$i    = 0;
		$page = 1;

		$retrived_group_input_value = WC()->session->get( 'group_order_data' );
		$count_gift                 = 0;
		$count_rule_gift            = array();
		$gifts_set                  = array();

		if ( is_array( $retrived_group_input_value ) && count( $retrived_group_input_value ) > 0 ) {
			$product_qty_in_cart_gift = $this->get_cart_item_quantities_gift();
			foreach ( $retrived_group_input_value as $index => $set ) {
				$count_gift  += $set['q'];
				$gifts_set[] = $set['id'];
				if ( array_key_exists( $set['rule_id'], $count_rule_gift ) ) {
					$count_rule_gift[ $set['rule_id'] ]['q'] += $set['q'];
				} else {
					$count_rule_gift[ $set['rule_id'] ]['q'] = $set['q'];
				}
			}
		}

		$t           = 1;
		$innsert_div = false;

		foreach ( $this->show_gift_item_for_cart as $gift_item_key => $gift ) {
			if ( isset( $gift['auto'] ) && $gift['auto'] == 'yes' ) {
				continue;
			}
			$product = wc_get_product( $gift['item'] );

			$img_url               = wp_get_attachment_image_src( $product->get_image_id(), 'large' );
			$img_url               = $img_url[0];
			$img_html              = $title_html = $text_stock_qty = '';
			$item_hover            = '';
			$disable               = false;
			$check_and_go_per_rule = true;
			if ( has_filter( 'custom_gift_field_per_rule_check_session' ) ) {
				$check_and_go_per_rule = apply_filters( 'custom_gift_field_per_rule_check_session', $gift['rule_id'] );

				if ( ! $check_and_go_per_rule ) {

					if ( $gift['disable_if'] == 'hide' ) {
						continue;
					} else {
						$disable = true;
					}
				}
			}

			if ( array_key_exists( $gift['rule_id'], $count_rule_gift ) && $count_rule_gift[ $gift['rule_id'] ]['q'] >= $gift['pw_number_gift_allowed'] ) {

				if ( $gift['disable_if'] == 'hide' ) {
					continue;

				} else {
					$disable = true;
				}

			} elseif ( in_array( $gift['key'], $gifts_set ) && $gift['can_several_gift'] == 'no' ) {
				if ( $gift['disable_if'] == 'hide' ) {
					continue;
				} else {
					$disable = true;
				}
			} elseif ( $gift['disable_if'] != 'show' && $count_gift >= 1 ) {
				if ( $gift['disable_if'] == 'hide' ) {
					continue;
				} else {
					$disable = true;
				}
			} else if ( $this->setting['multiselect'] == "yes" && $count_gift >= 1 ) {
				if ( $this->calculate_cart_subtotal() < $multiselect_cart_amount || $count_gift >= $multiselect_gift_count ) {
					if ( $gift['disable_if'] == 'hide' ) {
						break;
					}

					$disable = true;
				}
			}

			$title = $product->get_title();
			if ( $product->post_type == 'product_variation' ) {

				$title = $product->get_name();
				// $title = $product->get_attribute_summary();
			}
			$product_type     = $product->get_type();
			$price            = '';
			$price            = '<div style="text-align: center;">' . sprintf( '%s', $product->get_price_html() ) . '</div>';
			$product_variable = false;
			if ( $product_type == 'variable' ) {
				$product_variable = true;
			}


			// if (is_array($retrived_group_input_value) && count($retrived_group_input_value) > 0) {
			$get_stock_quantity = $product->get_stock_quantity();

			if ( ( ! $product->is_in_stock() || $product->managing_stock() ) && $get_stock_quantity <= 0 ) {
				$disable = true;
			} else if ( $product->is_in_stock() && $get_stock_quantity >= 1 && $product->managing_stock() ) {
				$x                           = 0;
				$required_stock_in_cart_gift = isset( $product_qty_in_cart_gift[ $gift['item'] ] ) ? $product_qty_in_cart_gift[ $gift['item'] ] : 0;
				$required_stock_in_cart      = isset( $this->product_qty_in_cart[ $product->get_stock_managed_by_id() ] ) ? $this->product_qty_in_cart[ $product->get_stock_managed_by_id() ] : 0;

				$x = $get_stock_quantity - ( $required_stock_in_cart + $required_stock_in_cart_gift );
				if ( $x < $required_stock_in_cart_gift || $x == 0 ) {
					$disable = true;
					//$text_stock_qty='<div class="gift-product-stock">'.$get_stock_quantity.' '. __('in stock','pw_wc_advanced_gift').'</div>';
				} else if ( ! $product_variable && isset( $this->setting['show_gift_stock_qty'] ) && $this->setting['show_gift_stock_qty'] == 'yes' ) {
					$text_stock_qty = '<div class="gift-product-stock">' . sprintf( '%s', $x ) . ' ' . __( 'in stock', 'pw_wc_advanced_gift' ) . '</div>';
				} else {
					$text_stock_qty = '';
				}
			}

			//   }


			//For Show Stock Qty
			/*

			*/
			if ( $disable == true ) {
				$img_html   = '<img src="' . esc_url( $img_url ) . '" />';
				$title_html = '<div class="gift-product-title">' . sprintf( '%s', $title ) . '</div>';
				$item_hover = 'disable-hover';
			} else {
				$img_html   = '<img src="' . esc_url( $img_url ) . '" />';
				$title_html = '<div class="gift-product-title">' . sprintf( '%s', $title ) . '</div>';
				$item_hover = 'hovering';
			}

			if ( $i == 0 && $this->setting["view_cart_gift"] == "grid" ) {
				$active = '';
				if ( $page == 1 ) {
					$active = ' pw-gift-active ';
				}
				$product_item .= '<div class="page_' . esc_attr( $page ) . ' pw_gift_pagination_div ' . esc_attr( $active ) . '" style="display: none;">';
				$page ++;
				$innsert_div = true;
			}

			$item     = ( $this->setting["view_cart_gift"] == "grid" ) ? '<div class="' . esc_attr( $this->setting["mobile_columns"] ) . ' ' . esc_attr( $this->setting["tablet_columns"] ) . ' ' . esc_attr( $this->setting["desktop_columns"] ) . ' ' . esc_attr( $i ) . '" >' : '';
			$end_item = ( $this->setting["view_cart_gift"] == "grid" ) ? '</div>' : '';
			$i ++;

			$product_item .= $item . '<div class="gift-product-item ' . esc_attr( $item_hover ) . '">
										<div class="gift-product-hover"  >
											<div>';
			if ( $product_variable ) {
				$product_item .= '<a class="btn-select-gift-button"  href="" data-rule-id="' . esc_attr( $gift['rule_id'] ) . '" data-id="' . esc_attr( $gift['item'] ) . '">' . sprintf( '%s', $select_gift ) . '</a>';
			} else {
				$product_item .= '<a class="btn-add-gift-button"  href=""  data-id="' . esc_attr( $gift['key'] ) . '">' . sprintf( '%s', $add_gift ) . '</a>';
			}
			$price        = '';
			$product_item .= '</div>
						</div>' . $img_html . '' . $title_html . $text_stock_qty . $price . '
					</div>' . $end_item;

			if ( ( $i == $this->setting["number_per_page"] && $this->setting["view_cart_gift"] == "grid" ) || ( count( $this->show_gift_item_for_cart ) == $t && $innsert_div == true ) ) {

				$innsert_div  = false;
				$product_item .= '</div>';
				$i            = 0;
			}
			$t ++;
		}
		$did = rand( 0, 1000 );
		// $setting = get_option("pw_gift_options");
		if ( $product_item == '' ) {
			return;
		}
		echo '<div class="gift-popup-title">' . $this->setting['cart_title'] . '</div>';

		if ( $this->setting['view_cart_gift'] == 'carousel' ) {
			echo '<div style="position: relative;" >
					<div class="gift_cart_ajax blockUI blockOverlay" style="display:none;z-index: 1000; border: medium none; margin: 0px; padding: 0px; width: 100%; height: 100%; top: 0px; left: 0px; background: rgb(255, 255, 255) none repeat scroll 0% 0%; opacity: 0.6; cursor: default; position: absolute;"></div>
					<div class="owl-carousel wb-car-car  wb-carousel-layout wb-car-cnt " id="pw_slider_gift"  >

					' . sprintf( '%s', $product_item ) . '
					
				</div></div>';
			echo "<script type='text/javascript'>
						jQuery(document).ready(function() {
                            jQuery( document.body ).on( 'updated_cart_totals', function(){
                                jQuery('.owl-carousel').owlCarousel('destroy'); 
                                jQuery('.owl-carousel').owlCarousel({
								  margin : " . esc_attr( $this->setting['pw_item_marrgin'] ) . " , 
								  loop:true,
								  dots:" . esc_attr( $this->setting['pw_show_pagination'] ) . ",
								  nav:" . esc_attr( $this->setting['pw_show_control'] ) . ",
								  slideBy: " . esc_attr( $this->setting['pw_item_per_slide'] ) . ",
								  autoplay:" . esc_attr( $this->setting['pw_auto_play'] ) . ",
								  autoplayTimeout : " . esc_attr( $this->setting['pw_slide_speed'] ) . ",
								  rtl: " . ( isset( $this->setting['pw_slide_rtl'] ) ? esc_attr( $this->setting['pw_slide_rtl'] ) : false ) . ",
								  responsive:{
							        0:{
							            items:1
							        },
							        600:{
							            items:2
							        },
							        1000:{
							            items:" . esc_attr( $this->setting['pw_item_per_view'] ) . "
							        }
							    },
							    autoplayHoverPause: true,
							    navText: [ '>', '<' ]
							 });
});						    
							  jQuery('#pw_slider_gift').owlCarousel({
								  margin : " . esc_attr( $this->setting['pw_item_marrgin'] ) . " , 
								  loop:true,
								  dots:" . esc_attr( $this->setting['pw_show_pagination'] ) . ",
								  nav:" . esc_attr( $this->setting['pw_show_control'] ) . ",
								  slideBy: " . esc_attr( $this->setting['pw_item_per_slide'] ) . ",
								  autoplay:" . esc_attr( $this->setting['pw_auto_play'] ) . ",
								  autoplayTimeout : " . esc_attr( $this->setting['pw_slide_speed'] ) . ",
								  rtl: " . ( isset( $this->setting['pw_slide_rtl'] ) ? esc_attr( $this->setting['pw_slide_rtl'] ) : false ) . ",
								  responsive:{
							        0:{
							            items:1
							        },
							        600:{
							            items:2
							        },
							        1000:{
							            items:" . esc_attr( $this->setting['pw_item_per_view'] ) . "
							        }
							    },
							    autoplayHoverPause: true,
							    navText: [ '>', '<' ]
							 });					  
                         })
                 </script>";


		} else if ( $this->setting['view_cart_gift'] == 'grid' ) {
			$btn = '';
			if ( $page > 2 ) {
				$btn = '<div class="gift-popup-title">';
				for ( $i = 1; $i < $page; $i ++ ) {
					$btn .= '<a href="" class="pw_gift_pagination_num" data-page-id="page_' . esc_attr( $i ) . '" style="border: none;">' . esc_attr( $i ) . '</a>';
					if ( $i + 1 < $page ) {
						$btn .= ' | ';
					}
				}
				$btn .= '</div>';
			}
			echo '<div class="wg-row wg-maincontainer" style="position: relative;"><div class="gift_cart_ajax blockUI blockOverlay" style="display:none;z-index: 1000; border: medium none; margin: 0px; padding: 0px; width: 100%; height: 100%; top: 0px; left: 0px; background: rgb(255, 255, 255) none repeat scroll 0% 0%; opacity: 0.6; cursor: default; position: absolute;"></div>' . sprintf( '%s', $product_item ) . '</div>' . sprintf( '%s', $btn );
		}
	}

	public function show_variation_html_gift() {
		$select_gift = isset( $this->setting['select_gift'] ) ? $this->setting['select_gift'] : 'Select Gift';
		$select_gift = ( $select_gift != '' ) ? $select_gift : 'select Gift';
		echo '
		<div class="pw-cover" style="visibility:hidden"></div>
		<div class="pw_gift_popup pw-gift-cart" style="visibility:hidden">
			<h2 class="pw-title">' . sprintf( '%s', $select_gift ) . '</h2><div class="pw_gift_popup_close"></div>
			<div class="pw-gifts">
			</div>
		</div>
		';
	}

	public function pw_gift_show_variation_function() {
		$ret = '';

		global $woocommerce;
		if ( ! isset( $_POST['pw_gift_variable'] ) ) {
			wp_die();
		}
		$add_gift       = $this->setting['add_gift'];
		$variable       = $_POST['pw_gift_variable'];
		$rule_id        = $_POST['pw_gift_rule_id'];
		$p_product      = wc_get_product( $variable );
		$product_type   = $p_product->get_type();
		$text_stock_qty = '';

		if ( $product_type == 'variable' ) {
			$retrived_group_input_value = WC()->session->get( 'group_order_data' );
			if ( is_array( $retrived_group_input_value ) && count( $retrived_group_input_value ) > 0 ) {
				$product_qty_in_cart_gift = $this->get_cart_item_quantities_gift();
			}
			$variation_ids = version_compare( WC()->version, '2.7.0', '>=' ) ? $p_product->get_visible_children() : $p_product->get_children( true );
			$ret           .= '<div class="wg-row">';

			foreach ( $variation_ids as $variation_id ) {
				$text_stock_qty     = '';
				$item_hover         = 'hovering';
				$_product_variation = wc_get_product( $variation_id );

				//For Show Stock Qty
				if ( isset( $this->setting['show_gift_stock_qty'] ) && $this->setting['show_gift_stock_qty'] == 'yes' ) {
					$get_stock_quantity = $_product_variation->get_stock_quantity();
					if ( ( ! $_product_variation->is_in_stock() || $_product_variation->managing_stock() ) && $get_stock_quantity <= 0 ) {
						$item_hover     = 'disable-hover';
						$text_stock_qty = '';
					} else if ( $_product_variation->is_in_stock() && $get_stock_quantity >= 1 && $_product_variation->managing_stock() ) {
						$x                           = 0;
						$required_stock_in_cart_gift = isset( $product_qty_in_cart_gift[ $variation_id ] ) ? $product_qty_in_cart_gift[ $variation_id ] : 0;
						$required_stock_in_cart      = isset( $this->product_qty_in_cart[ $_product_variation->get_stock_managed_by_id() ] ) ? $this->product_qty_in_cart[ $_product_variation->get_stock_managed_by_id() ] : 0;
						$x                           = $get_stock_quantity - ( $required_stock_in_cart + $required_stock_in_cart_gift );
						if ( $x < $required_stock_in_cart_gift || $x == 0 ) {
							$item_hover     = 'disable-hover';
							$text_stock_qty = '';
						} else {
							$text_stock_qty = '<div class="gift-product-stock">' . esc_attr( $x ) . ' ' . esc_html__( 'in stock', 'pw_wc_advanced_gift' ) . '</div>';
						}
						//$text_stock_qty='<div class="gift-product-stock">'.$get_stock_quantity.' '. __('in stock','pw_wc_advanced_gift').'</div>';
					}
				}

				$title_v = $_product_variation->get_name();
				$img_url = wp_get_attachment_image_src( $_product_variation->get_image_id(), 'large' );
				$img_url = $img_url[0];

				$ret .= '<div class="wg-col-xs-12 wg-col-sm-6 wg-col-md-3">
					<div class="gift-product-item ' . esc_attr( $item_hover ) . '">
						<div class="gift-product-hover" >
							<div>
							<a class="btn-add-gift-button" href=""  data-id="' . esc_attr( $rule_id ) . '-' . esc_attr( $variation_id ) . '">' . sprintf( '%s', $add_gift ) . '</a>
							</div>
						</div>
					
						<img src="' . esc_url( $img_url ) . '" class="gift-item-img" alt="' . esc_attr( $title_v ) . '" />
                        <div class="gift-product-title">' . sprintf( '%s', $title_v ) . '</div>' . sprintf( '%s', $text_stock_qty ) . '
					</div>
				</div>';
			}
			$ret .= '</div>';
		}
		print_r( $ret );
		wp_die();
	}

	public function pw_gift_add_adv_function() {
		global $woocommerce;
		if ( ! isset( $_POST['pw_add_gift'] ) ) {
			echo __( "Can't Add", 'pw_wc_advanced_gift' );
			wp_die();
		}
		if ( ! $this->check_rule() ) {
			echo __( "Can't Add", 'pw_wc_advanced_gift' );
			wp_die();
		}

		$gift            = $_POST['pw_add_gift'];
		$this->setting   = get_option( "pw_gift_options" );
		$count_gift      = 0;
		$count_rule_gift = array();
		$gifts_set       = array();

		$retrived_group_input_value = WC()->session->get( 'group_order_data' );
		if ( is_array( $retrived_group_input_value ) && count( $retrived_group_input_value ) > 0 ) {
			foreach ( $retrived_group_input_value as $index => $set ) {
				$count_gift  += $set['q'];
				$gifts_set[] = $set['id'];
				if ( array_key_exists( $set['rule_id'], $count_rule_gift ) ) {
					$count_rule_gift[ $set['rule_id'] ]['q'] += $set['q'];
				} else {
					$count_rule_gift[ $set['rule_id'] ]['q'] = $set['q'];
				}
			}
		}

		if ( ! empty( $gift ) && array_key_exists( $gift, $this->gift_item_variable['all_gifts'] ) ) {
			$rule_id                = $this->gift_item_variable['all_gifts'][ $gift ]['rule_id'];
			$pw_number_gift_allowed = $this->gift_item_variable[ $rule_id ]['pw_number_gift_allowed'];

			//Check all gift Rule added Qty
			if ( array_key_exists( $rule_id, $count_rule_gift ) && $count_rule_gift[ $rule_id ]['q'] >= $pw_number_gift_allowed ) {
				echo __( "Can't Add", 'pw_wc_advanced_gift' );
				die( 0 );
			} elseif ( in_array( $gift, $gifts_set ) && $this->gift_item_variable[ $rule_id ]['can_several_gift'] == 'no' ) {
				echo __( "Can't Add", 'pw_wc_advanced_gift' );
				die( 0 );
			} else if ( $this->setting['multiselect'] == "yes" && ( $this->cart['amount'] < $this->setting['multiselect_cart_amount'] || $count_gift
			                                                                                                                             >= $this->setting['multiselect_gift_count'] ) ) {

				echo $this->setting['multiselect_cart_amount'];
				die( 0 );
			} else if ( $retrived_group_input_value != '' && is_array( $retrived_group_input_value ) && count
			                                                                                            ( $retrived_group_input_value ) > 0 &&
			            $retrived_group_input_value[ $gift ]['q'] >=
			            $pw_number_gift_allowed ) {
				echo __( "Can't Add", 'pw_wc_advanced_gift' );
				die( 0 );
			}

			$id_product                = $this->gift_item_variable['all_gifts'][ $gift ]['id_product'];
			$product_get               = wc_get_product( $id_product );
			$flag_stock                = 0;
			$this->product_qty_in_cart = $this->get_cart_item_quantities();

			$get_stock_quantity = $product_get->get_stock_quantity();
			if ( ( ! $product_get->is_in_stock() || $product_get->managing_stock() ) && $get_stock_quantity <= 0 ) {
				$flag_stock = 1;
			} else if ( $product_get->is_in_stock() && $get_stock_quantity > 0 ) {
				$product_qty_in_cart_gift    = $this->get_cart_item_quantities_gift();
				$required_stock_in_cart      = isset( $this->product_qty_in_cart[ $product_get->get_stock_managed_by_id() ] ) ? $this->product_qty_in_cart[ $product_get->get_stock_managed_by_id() ] : 0;
				$required_stock_in_cart_gift = isset( $product_qty_in_cart_gift[ $id_product ] ) ? $product_qty_in_cart_gift[ $id_product ] : 0;
				if ( ( $get_stock_quantity - $required_stock_in_cart ) - $required_stock_in_cart_gift <= 0 ) {
					$flag_stock = 1;
				}
			}
			if ( $flag_stock == 1 ) {
				echo __( "This Product is Out of stock", 'pw_wc_advanced_gift' );
				die( 0 );
			}


			if ( $count_gift >= 1 ) {
				if ( ! array_key_exists( $rule_id, $count_rule_gift ) || ! isset( $retrived_group_input_value[ $gift ]['q'] ) ) {
					$retrived_group_input_value[ $gift ] = array(
						'id'         => $gift,
						'id_product' => $id_product,
						'q'          => 1,
						'rule_id'    => $rule_id,
						'time_add'   => $this->gift_item_variable[ $rule_id ]['time_rule']
					);
					WC()->session->set( 'group_order_data', $retrived_group_input_value );
					echo __( "Add", 'pw_wc_advanced_gift' );
					wp_die();
				} else {
					$retrived_group_input_value[ $gift ] = array(
						'id'         => $gift,
						'id_product' => $id_product,
						'q'          => $retrived_group_input_value[ $gift ]['q'] + 1,
						'rule_id'    => $rule_id,
						'time_add'   => $this->gift_item_variable[ $rule_id ]['time_rule']
					);
					WC()->session->set( 'group_order_data', $retrived_group_input_value );
					echo __( "Add", 'pw_wc_advanced_gift' );
					wp_die();
				}
			} else {
				$retrived_group_input_value          = array();
				$retrived_group_input_value[ $gift ] = array(
					'id'         => $gift,
					'id_product' => $id_product,
					'q'          => 1,
					'rule_id'    => $rule_id,
					'time_add'   => $this->gift_item_variable[ $rule_id ]['time_rule']
				);
				WC()->session->set( 'group_order_data', $retrived_group_input_value );
				echo __( "Add", 'pw_wc_advanced_gift' );
				wp_die();
			}
		}
		echo __( "Can't Add", 'pw_wc_advanced_gift' );
		wp_die();
	}

	public function pw_insert_gift_cart( $gift = null ) {
		global $woocommerce;

		$retrived_group_input_value = WC()->session->get( 'group_order_data' );

		$count_gift      = 0;
		$count_rule_gift = array();
		$gifts_set       = array();

		if ( is_array( $retrived_group_input_value ) && count( $retrived_group_input_value ) > 0 ) {
			foreach ( $retrived_group_input_value as $index => $set ) {

				$count_gift  += $set['q'];
				$gifts_set[] = $set['id'];
				if ( array_key_exists( $set['rule_id'], $count_rule_gift ) ) {
					$count_rule_gift[ $set['rule_id'] ]['q'] += $set['q'];
				} else {
					$count_rule_gift[ $set['rule_id'] ]['q'] = $set['q'];
				}
			}
		}
		$multiselect_gift_count = $this->setting['multiselect_gift_count'];
		if ( $multiselect_gift_count < $count_gift && $this->setting['multiselect'] == "yes" ) {
			WC()->session->set( 'group_order_data', '' );
		}

		if ( ! empty( $gift ) && array_key_exists( $gift, $this->gift_item_variable['all_gifts'] ) ) {
			$rule_id                = $this->gift_item_variable['all_gifts'][ $gift ]['rule_id'];
			$pw_number_gift_allowed = $this->gift_item_variable[ $rule_id ]['pw_number_gift_allowed'];

			if ( array_key_exists( $rule_id, $count_rule_gift ) && $count_rule_gift[ $rule_id ]['q'] >= $pw_number_gift_allowed ) {
				return false;
			} elseif ( in_array( $gift, $gifts_set ) && $this->gift_item_variable[ $rule_id ]['can_several_gift'] == 'no' ) {
				return false;
			} else if ( $this->setting['multiselect'] == "yes" && ( $this->cart['amount'] < $this->setting['multiselect_cart_amount'] || $count_gift
			                                                                                                                             >= $this->setting['multiselect_gift_count'] ) ) {
				return false;
			} else if ( $retrived_group_input_value != '' && is_array( $retrived_group_input_value ) && count
			                                                                                            ( $retrived_group_input_value ) > 0 &&
			            isset( $retrived_group_input_value[ $gift ]['q'] )
			            &&
			            $retrived_group_input_value[ $gift ]['q'] >=
			            $pw_number_gift_allowed ) {
				return false;
			}

			$id_product_stock   = $this->gift_item_variable['all_gifts'][ $gift ]['id_product'];
			$product_get        = wc_get_product( $id_product_stock );
			$flag_stock         = 0;
			$get_stock_quantity = $product_get->get_stock_quantity();
			//  $get_stock_quantity = $product_get->get_stock_quantity();
			if ( ( ! $product_get->is_in_stock() || $product_get->managing_stock() ) && $get_stock_quantity <= 0 ) {
				$flag_stock = 1;
			} else if ( $product_get->is_in_stock() && $get_stock_quantity >= 1 ) {
				//echo 'w';
				$product_qty_in_cart_gift    = $this->get_cart_item_quantities_gift();
				$required_stock_in_cart      = isset( $this->product_qty_in_cart[ $product_get->get_stock_managed_by_id() ] ) ? $this->product_qty_in_cart[ $product_get->get_stock_managed_by_id() ] : 0;
				$required_stock_in_cart_gift = isset( $product_qty_in_cart_gift[ $id_product_stock ] ) ? $product_qty_in_cart_gift[ $id_product_stock ] : 0;

				if ( ( $get_stock_quantity - $required_stock_in_cart ) - $required_stock_in_cart_gift <= 0 ) {
					$flag_stock = 1;
				}
			}
			if ( $flag_stock == 1 ) {
				return false;
			}
			$id_product = $this->gift_item_variable['all_gifts'][ $gift ]['id_product'];
			if ( $count_gift >= 1 ) {

				if ( ! array_key_exists( $rule_id, $count_rule_gift ) || ! isset( $retrived_group_input_value[ $gift ]['q'] ) ) {
					$retrived_group_input_value[ $gift ] = array(
						'id'         => $gift,
						'id_product' => $id_product,
						'q'          => 1,
						'rule_id'    => $rule_id,
						'time_add'   => $this->gift_item_variable[ $rule_id ]['time_rule']
					);
					WC()->session->set( 'group_order_data', $retrived_group_input_value );

					return true;
				} else {
					$q                                   = isset( $retrived_group_input_value[ $gift ]['q'] ) ? $retrived_group_input_value[ $gift ]['q'] + 1 : 1;
					$retrived_group_input_value[ $gift ] = array(
						'id'         => $gift,
						'id_product' => $id_product,
						'q'          => $q,
						'rule_id'    => $rule_id,
						'time_add'   => $this->gift_item_variable[ $rule_id ]['time_rule']
					);
					WC()->session->set( 'group_order_data', $retrived_group_input_value );

					return true;
				}
			} else {
				$retrived_group_input_value          = array();
				$retrived_group_input_value[ $gift ] = array(
					'id'         => $gift,
					'id_product' => $id_product,
					'q'          => 1,
					'rule_id'    => $rule_id,
					'time_add'   => $this->gift_item_variable[ $rule_id ]['time_rule']
				);
				WC()->session->set( 'group_order_data', $retrived_group_input_value );

				return true;
			}
		}

		return false;
	}

	public function remove_gift_from_cart() {
		global $woocommerce;
		if ( isset( $_GET['pw_gift_remove'] ) ) {
			$retrived_group_input_value = WC()->session->get( 'group_order_data' );
			$retrived_group_input_value = WC()->session->get( 'group_order_data' );
			if ( $retrived_group_input_value != '' && is_array( $retrived_group_input_value ) && count
			                                                                                     ( $retrived_group_input_value ) > 0 && array_key_exists
			     ( $_GET['pw_gift_remove'], $retrived_group_input_value ) ) {
				unset( $retrived_group_input_value[ $_GET['pw_gift_remove'] ] );
				WC()->session->set( 'group_order_data', $retrived_group_input_value );
			}
		}
	}

	public function woocommerce_cart_contents_function() {
		global $woocommerce, $product;

		$cart_page_id = get_permalink( wc_get_page_id( 'cart' ) );
		if ( substr( $cart_page_id, - 1 ) == "/" ) {
			$cart_page_id = substr( $cart_page_id, 0, - 1 );
		}
		if ( strpos( $cart_page_id, '?' ) !== false ) {
			$cart_page_id = $cart_page_id . '&';
		} else {
			$cart_page_id = $cart_page_id . '?';
		}

		$retrived_group_input_value = WC()->session->get( 'group_order_data' );

		if ( $retrived_group_input_value != '' && is_array( $retrived_group_input_value ) && count
		                                                                                     ( $retrived_group_input_value ) > 0 && count( $this->show_gift_item_for_cart ) > 0 ) {
			foreach ( $retrived_group_input_value as $key => $index ) {
				$gift_index = "";
				$product    = wc_get_product( $index['id_product'] );
				$title      = $product->get_title();
				if ( $product->post_type == 'product_variation' ) {
					$title = $product->get_name();
					//$title = wc_get_formatted_variation( $product->get_variation_attributes(), true );
					//$attributes=array();
					//$attributes = $product->get_attributes();
					//foreach ( $attributes as $key => $value ) {
					//	$title.=$value;
					//}	

				}

				$img_url = wp_get_attachment_image_src( $product->get_image_id(), 'thumbnail' );
				$img_url = $img_url[0];
				if ( $img_url == "" ) {
					$img_url = wc_placeholder_img_src();
				}
				$img_html = '<img src="' . esc_url( $img_url ) . '" alt="Gift Image"/>';
				$count    = isset( $index['q'] ) ? $index['q'] : 1;
				echo '
							<tr class="woocommerce-cart-form__cart-item cart_item">
								<td class="product-remove">
									<a class="remove" href="' . esc_url( $cart_page_id ) . 'pw_gift_remove=' . esc_attr( $index['id'] ) . '">×</a>
								</td>
								<td class="product-thumbnail">' . sprintf( '%s', $img_html ) . '</td>
								<td class="product-name" data-title="' . $this->setting['cart_title'] . '"><a href="' . esc_url( $product->get_permalink() ) . '">' . apply_filters( 'woocommerce_checkout_product_title', $title, $product ) . '</a></td>
								<td class="product-price" data-title="' . esc_html__( "Price", "woocommerce" ) . '">' . sprintf( '%s', $this->setting['free'] ) . '</td>
								<td class="product-quantity" data-title="' . esc_html__( "Quantity", "woocommerce" ) . '">' . esc_html__( $count ) . '</td>
								<td class="product-subtotal" data-title="' . esc_html__( "Total", "woocommerce" ) . '">' . sprintf( '%s', $this->setting['free'] ) . '</td>
							</tr>';
			}
		}
	}

	public function add_gift_to_order( $order_id ) {
		global $woocommerce;

		if ( ! $this->check_rule() ) {
			return;
		}
		$retrived_group_input_value = WC()->session->get( 'group_order_data' );


		if ( is_array( $retrived_group_input_value ) && is_array( $this->gift_item_variable ) && count
		                                                                                         ( $retrived_group_input_value ) > 0 && count( $this->gift_item_variable['all_gifts'] ) > 0 ) {
			$flag           = false;
			$set_rule_limit = '';
			$i              = 1;
			foreach ( $retrived_group_input_value as $key => $index ) {
				$product_id = "";
				$product_id = $index['id_product'];
				$rule_id    = $index['rule_id'];
				$_product   = wc_get_product( $product_id );
				if ( $_product->post_type == 'product_variation' ) {
					$product_id = wp_get_post_parent_id( $product_id );
				}
				$item                 = array();
				$item['variation_id'] = $this->get_variation_id( $_product );
				@$item['variation_data'] = $item['variation_id'] ? $this->get_variation_attributes( $_product ) : '';

				if ( $_product->is_in_stock() ) {
					$item_id = wc_add_order_item( $order_id, array(
						'order_item_name' =>
							$_product->get_title(),
						'order_item_type' => 'line_item'
					) );
					if ( $item_id ) {

						wc_add_order_item_meta( $item_id, '_qty', $retrived_group_input_value[ $key ]['q'] );
						wc_add_order_item_meta( $item_id, '_tax_class', $_product->get_tax_class() );
						wc_add_order_item_meta( $item_id, '_product_id', $product_id );
						wc_add_order_item_meta( $item_id, '_variation_id', $this->get_variation_id( $_product ) );
						wc_add_order_item_meta( $item_id, '_line_subtotal', wc_format_decimal( 0, 4 ) );
						wc_add_order_item_meta( $item_id, '_line_total', wc_format_decimal( 0, 4 ) );
						wc_add_order_item_meta( $item_id, '_line_tax', wc_format_decimal( 0, 4 ) );
						wc_add_order_item_meta( $item_id, '_line_subtotal_tax', wc_format_decimal( 0, 4 ) );
						wc_add_order_item_meta( $item_id, '_free_gift', 'yes' );
						wc_add_order_item_meta( $item_id, '_rule_id_free_gift', $rule_id );

						if ( @$item['variation_data'] && is_array( $item['variation_data'] ) ) {
							foreach ( $item['variation_data'] as $key => $value ) {
								wc_add_order_item_meta( $item_id, esc_attr( str_replace( 'attribute_', '', $key ) ), $value );
							}
						}

						//For Limit
						if ( $rule_id != $set_rule_limit ) {
							$pw_limit_cunter = get_post_meta( $rule_id, 'pw_limit_cunter', true );
							if ( is_array( $pw_limit_cunter ) ) {
								$pw_limit_cunter['count'] ++;
								$order   = new WC_Order( $order_id );
								$user_id = $order->get_customer_id( 'view' );
								if ( $user_id > 0 ) {
									$i = 0;
									foreach ( $pw_limit_cunter['user_info'] as $user_info ) {
										if ( $user_info['id'] == $user_id ) {
											$nubmer = $user_info['number'];
											$pw_limit_cunter['user_info'][ $i ]['number'] ++;
											$flag = true;
											break;
										}
										$i ++;
									}
									if ( ! $flag ) {
										$pw_limit_cunter['user_info'][] = array(
											'id'     => $user_id,
											'number' => 1,
										);
										$flag                           = true;
									}
								}
								update_post_meta( $rule_id, 'pw_limit_cunter', $pw_limit_cunter );
							}
							$set_rule_limit = $rule_id;
						}
					}
				}
			}
		}
		WC()->session->set( 'group_order_data', '' );
	}

	protected function get_variation_id( $_product ) {
		if ( version_compare( WC()->version, "2.7.0" ) >= 0 ) {
			return $_product->get_id();
		} else {
			return $_product->variation_id;
		}
	}

	protected function get_variation_attributes( $_product ) {
		if ( version_compare( WC()->version, "2.7.0" ) >= 0 ) {
			return wc_get_product_variation_attributes( $_product->get_id() );
		} else {
			return $_product->get_variation_attributes();
		}
	}

	public function get_cart_item_categories( $cart_item ) {
		$categories = array();
		$current    = wp_get_post_terms( $cart_item['product_id'], 'product_cat' );
		foreach ( $current as $category ) {
			$categories[] = $category->term_id;
		}

		return $categories;
	}

	public function get_cart_item_brands( $cart_item ) {
		$brands  = array();
		$current = wp_get_post_terms( $cart_item['product_id'], 'product_brand' );
		foreach ( $current as $brand ) {
			$brands[] = $brand->term_id;
		}

		return $brands;
	}

	public function get_cart_item_tags( $cart_item ) {
		$tags    = array();
		$current = wp_get_post_terms( $cart_item['product_id'], 'product_tag' );
		foreach ( $current as $tag ) {
			$tags[] = $tag->term_id;
		}

		return $tags;
	}

	public function calculate_cart_subtotal() {
		global $woocommerce;
		$include_tax = wc_tax_enabled();
		$subtotal    = 0;
		foreach ( $woocommerce->cart->get_cart() as $cart_item ) {
			if ( isset( $cart_item['line_subtotal'] ) ) {
				// Add line subtotal
				$subtotal += $cart_item['line_subtotal'];
				// Add line subtotal tax
				if ( isset( $cart_item['line_subtotal_tax'] ) && $include_tax ) {
					$subtotal += $cart_item['line_subtotal_tax'];
				}
			}
		}
//		return WC()->cart->cart_contents_total;
		//echo WC()->cart->cart_contents_total;
		return $subtotal;

	}

	public function adjust_cart_rule() {
		$setting = get_option( "pw_gift_options" );
		if ( $setting['view_cart_gift'] == 'carousel' ) {
			echo "<script type='text/javascript'>
					jQuery(document).ready(function() {
						jQuery( document.body ).on( 'updated_cart_totals', function(){
							if(jQuery('html').find('.owl-carousel').length){
								jQuery('.owl-carousel').owlCarousel('destroy'); 
								jQuery('.owl-carousel').owlCarousel({
									  margin : " . esc_attr( $setting['pw_item_marrgin'] ) . " , 
									  loop:true,
									  dots:" . esc_attr( $setting['pw_show_pagination'] ) . ",
									  nav:" . esc_attr( $setting['pw_show_control'] ) . ",
									  slideBy: " . esc_attr( $setting['pw_item_per_slide'] ) . ",
									  autoplay:" . esc_attr( $setting['pw_auto_play'] ) . ",
									  autoplayTimeout : " . esc_attr( $setting['pw_slide_speed'] ) . ",
									  rtl: " . ( isset( $setting['pw_slide_rtl'] ) ? esc_attr( $setting['pw_slide_rtl'] ) : false ) . ",
									  responsive:{
										0:{
											items:1
										},
										600:{
											items:2
										},
										1000:{
											items:" . esc_attr( $setting['pw_item_per_view'] ) . "
										}
									},
									autoplayHoverPause: true,
									navText: [ '>', '<' ]
								});
						}
					});	
                })
        </script>";
		}
	}

	function wc_add_surcharge() {
		if ( ! $this->check_rule() ) {
			WC()->session->set( 'group_order_data', '' );
		}
		if ( ! defined( 'plugin_dir_path_gift_fee' ) ) {
			return;
		}

		global $woocommerce;
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( ! $this->check_rule() ) {
			return;
		}
		$flag_check = false;
		foreach ( $this->show_gift_item_for_cart as $gift_item_key => $cart_item ) {
			if ( is_array( $woocommerce->session->wc_free_select_gift ) &&
			     in_array( $cart_item['key'], $woocommerce->session->wc_free_select_gift )
			) {
				$flag_check = true;
				break;
			} else {
				$flag_check = false;
			}
		}
		if ( $flag_check == false ) {
			unset( $woocommerce->session->wc_free_select_gift );
			$woocommerce->session->wc_free_select_gift = "";
			$woocommerce->session->wc_free_select_gift = array();

			//return;
		}
		$retrived_group_input_value = WC()->session->get( 'group_order_data' );
		if ( is_array( $woocommerce->session->wc_free_select_gift ) && count( $woocommerce->session->wc_free_select_gift ) > 0 && is_array( $this->show_gift_item_for_cart ) && count( $this->show_gift_item_for_cart ) > 0 ) {
			$sum_value = 0;
			foreach ( $woocommerce->session->wc_free_select_gift as $session_gift => $index ) {
				$gift_index = $this->show_gift_item_for_cart[ $index ];
				$count      = 1;
				$count      = $retrived_group_input_value[ $gift_index['key'] ]['q'];
				if ( has_filter( 'add_fee_for_addon' ) ) {
					$value     = apply_filters( 'add_fee_for_addon', $gift_index['item'] );
					$sum_value += $count * $value;
				}
			}
			if ( $sum_value > 0 ) {
			}
			$woocommerce->cart->add_fee( 'Fee', '5', true, 'standard' );
		}
		$woocommerce->cart->add_fee( 'Fee', '5', true, 'standard' );
	}

}

new pw_class_woocommerce_gift_discunt_cart();
?>