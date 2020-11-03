<?php
global $wpdb;
$status = $pw_name = $disable_if = $pw_rule_description = $pw_gifts = $product_depends = $pw_product_depends = $category_depends = $exclude_category_depends = $pw_category_depends = $users_depends = $roles_depends = $pw_roles = $exclude_roles_depends = $pw_exclude_roles = $pw_users = $pw_cart_amount = $criteria_nb_products_max = $criteria_nb_products_min = $pw_cart_amount_min = $pw_cart_amount_max = $pw_from = $pw_to = $criteria_nb_products = $gift_preselector_product_page = $gift_auto_to_cart = $order_count = $gift_auto_to_cart = $order_op_count = $brand_depends = $pw_number_gift_allowed = $exclude_pw_category_depends = $pw_brand_depends = $is_coupons = $pw_gifts_metod = $criteria_nb_products_op = $cart_amount_op = $pw_gifts_category = $exclude_pw_category_depends_method = $pw_category_depends_method = $pw_product_depends_method = $pw_brand_depends_method = $pw_limit_per_rule = $pw_limit_per_rule_cunter = $pw_limit_per_user = $repeat = $pw_register_user = $schedule_type = $pw_weekly = $pw_monthly = $can_several_gift = $gift_notify_add = $exclude_product_depends = $pw_exclude_product_depends = "";

$status                        = get_post_meta( $_GET['pw_id'], 'status', true );
$pw_number_gift_allowed        = get_post_meta( $_GET['pw_id'], 'pw_number_gift_allowed', true );
$pw_name                       = get_post_meta( $_GET['pw_id'], 'pw_name', true );
$pw_rule_description           = get_post_meta( $_GET['pw_id'], 'pw_rule_description', true );
$pw_gifts                      = get_post_meta( $_GET['pw_id'], 'pw_gifts', true );
$pw_gifts_metod                = get_post_meta( $_GET['pw_id'], 'pw_gifts_metod', true );
$pw_gifts_category             = get_post_meta( $_GET['pw_id'], 'pw_gifts_category', true );
$product_depends               = get_post_meta( $_GET['pw_id'], 'product_depends', true );
$pw_product_depends            = get_post_meta( $_GET['pw_id'], 'pw_product_depends', true );
$pw_product_depends_method     = get_post_meta( $_GET['pw_id'], 'pw_product_depends_method', true );
$category_depends              = get_post_meta( $_GET['pw_id'], 'category_depends', true );
$exclude_category_depends      = get_post_meta( $_GET['pw_id'], 'exclude_category_depends', true );
$pw_category_depends           = get_post_meta( $_GET['pw_id'], 'pw_category_depends', true );
$pw_category_depends_method    = get_post_meta( $_GET['pw_id'], 'pw_category_depends_method', true );
$exclude_pw_category_depends   = get_post_meta( $_GET['pw_id'], 'exclude_pw_category_depends', true );
$users_depends                 = get_post_meta( $_GET['pw_id'], 'users_depends', true );
$pw_users                      = get_post_meta( $_GET['pw_id'], 'pw_users', true );
$roles_depends                 = get_post_meta( $_GET['pw_id'], 'roles_depends', true );
$pw_roles                      = get_post_meta( $_GET['pw_id'], 'pw_roles', true );
$exclude_roles_depends         = get_post_meta( $_GET['pw_id'], 'exclude_roles_depends', true );
$pw_exclude_roles              = get_post_meta( $_GET['pw_id'], 'pw_exclude_roles', true );
$order_count                   = get_post_meta( $_GET['pw_id'], 'order_count', true );
$is_coupons                    = get_post_meta( $_GET['pw_id'], 'is_coupons', true );
$pw_cart_amount                = get_post_meta( $_GET['pw_id'], 'pw_cart_amount', true );
$pw_cart_amount_min            = get_post_meta( $_GET['pw_id'], 'pw_cart_amount_min', true );
$pw_cart_amount_max            = get_post_meta( $_GET['pw_id'], 'pw_cart_amount_max', true );
$criteria_nb_products_max      = get_post_meta( $_GET['pw_id'], 'criteria_nb_products_max', true );
$criteria_nb_products_min      = get_post_meta( $_GET['pw_id'], 'criteria_nb_products_min', true );
$pw_from                       = get_post_meta( $_GET['pw_id'], 'pw_from', true );
$order_op_count                = get_post_meta( $_GET['pw_id'], 'order_op_count', true );
$criteria_nb_products_op       = get_post_meta( $_GET['pw_id'], 'criteria_nb_products_op', true );
$cart_amount_op                = get_post_meta( $_GET['pw_id'], 'cart_amount_op', true );
$pw_to                         = get_post_meta( $_GET['pw_id'], 'pw_to', true );
$criteria_nb_products          = get_post_meta( $_GET['pw_id'], 'criteria_nb_products', true );
$gift_preselector_product_page = get_post_meta( $_GET['pw_id'], 'gift_preselector_product_page', true );
$disable_if                    = get_post_meta( $_GET['pw_id'], 'disable_if', true );
$gift_auto_to_cart             = get_post_meta( $_GET['pw_id'], 'gift_auto_to_cart', true );
$pw_limit_per_rule             = get_post_meta( $_GET['pw_id'], 'pw_limit_per_rule', true );
$pw_limit_per_user             = get_post_meta( $_GET['pw_id'], 'pw_limit_per_user', true );
$pw_limit_cunter               = get_post_meta( $_GET['pw_id'], 'pw_limit_cunter', true );
$pw_register_user              = get_post_meta( $_GET['pw_id'], 'pw_register_user', true );
$schedule_type                 = get_post_meta( $_GET['pw_id'], 'schedule_type', true );
$repeat                        = get_post_meta( $_GET['pw_id'], 'repeat', true );
$pw_weekly                     = get_post_meta( $_GET['pw_id'], 'pw_weekly', true );
$pw_daily                      = get_post_meta( $_GET['pw_id'], 'pw_daily', true );
$pw_monthly                    = get_post_meta( $_GET['pw_id'], 'pw_monthly', true );
$gift_notify_add               = get_post_meta( $_GET['pw_id'], 'gift_notify_add', true );
$can_several_gift              = get_post_meta( $_GET['pw_id'], 'can_several_gift', true );
$exclude_product_depends       = get_post_meta( $_GET['pw_id'], 'exclude_product_depends', true );
$pw_exclude_product_depends    = get_post_meta( $_GET['pw_id'], 'pw_exclude_product_depends', true );
if ( defined( 'plugin_dir_url_pw_woo_brand' ) ) {
	$brand_depends           = get_post_meta( $_GET['pw_id'], 'brand_depends', true );
	$pw_brand_depends        = get_post_meta( $_GET['pw_id'], 'pw_brand_depends', true );
	$pw_brand_depends_method = get_post_meta( $_GET['pw_id'], 'pw_brand_depends_method', true );
}


//Write New Post
$defaults = array( 'post_title'   => stripslashes( $pw_name ),
                   'post_type'    => 'pw_gift_rule',
                   'post_content' => 'demo text',
                   'post_status'  => 'publish'
);
if ( $post_id = wp_insert_post( $defaults ) ) {
	add_post_meta( $post_id, 'pw_type', 'rule' );
	add_post_meta( $post_id, 'status', $status );
	add_post_meta( $post_id, 'pw_number_gift_allowed', $pw_number_gift_allowed );
	add_post_meta( $post_id, 'order_op_count', $order_op_count );
	add_post_meta( $post_id, 'order_count', $order_count );
	add_post_meta( $post_id, 'is_coupons', $is_coupons );
	add_post_meta( $post_id, 'cart_amount_op', $cart_amount_op );
	add_post_meta( $post_id, 'criteria_nb_products_op', $criteria_nb_products_op );
	add_post_meta( $post_id, 'disable_if', $disable_if );
	add_post_meta( $post_id, 'pw_name', $pw_name );
	add_post_meta( $post_id, 'pw_rule_description', $pw_rule_description );

	add_post_meta( $post_id, 'product_depends', $product_depends );
	add_post_meta( $post_id, 'exclude_product_depends', $exclude_product_depends );
	add_post_meta( $post_id, 'pw_product_depends', $pw_product_depends );
	add_post_meta( $post_id, 'pw_product_depends_method', $pw_product_depends_method );
	add_post_meta( $post_id, 'pw_exclude_product_depends', $pw_exclude_product_depends );
	add_post_meta( $post_id, 'category_depends', $category_depends );
	add_post_meta( $post_id, 'pw_category_depends', $pw_category_depends );
	add_post_meta( $post_id, 'exclude_category_depends', $exclude_category_depends );
	add_post_meta( $post_id, 'exclude_pw_category_depends', $exclude_pw_category_depends );
	add_post_meta( $post_id, 'pw_category_depends_method', $pw_category_depends_method );
	add_post_meta( $post_id, 'users_depends', $users_depends );
	add_post_meta( $post_id, 'roles_depends', $roles_depends );
	add_post_meta( $post_id, 'pw_roles', $pw_roles );
	add_post_meta( $post_id, 'exclude_roles_depends', $exclude_roles_depends );
	add_post_meta( $post_id, 'pw_exclude_roles', $pw_exclude_roles );
	add_post_meta( $post_id, 'pw_users', $pw_users );
	add_post_meta( $post_id, 'pw_cart_amount', $pw_cart_amount );
	add_post_meta( $post_id, 'pw_cart_amount_min', $pw_cart_amount_min );
	add_post_meta( $post_id, 'pw_cart_amount_max', $pw_cart_amount_max );
	add_post_meta( $post_id, 'criteria_nb_products_min', $criteria_nb_products_min );
	add_post_meta( $post_id, 'criteria_nb_products_max', $criteria_nb_products_max );
	add_post_meta( $post_id, 'pw_from', $pw_from );
	add_post_meta( $post_id, 'pw_to', $pw_to );
	add_post_meta( $post_id, 'criteria_nb_products', $criteria_nb_products );
	add_post_meta( $post_id, 'gift_preselector_product_page', $gift_preselector_product_page );
	add_post_meta( $post_id, 'gift_auto_to_cart', $gift_auto_to_cart );
	add_post_meta( $post_id, 'pw_limit_per_rule', $pw_limit_per_rule );
	add_post_meta( $post_id, 'pw_register_user', $pw_register_user );
	add_post_meta( $post_id, 'schedule_type', $schedule_type );
	add_post_meta( $post_id, 'pw_weekly', $pw_weekly );
	add_post_meta( $post_id, 'pw_daily', $pw_daily );
	add_post_meta( $post_id, 'pw_monthly', $pw_monthly );
	add_post_meta( $post_id, 'can_several_gift', $can_several_gift );
	add_post_meta( $post_id, 'repeat_sum_qty', $repeat_sum_qty );
	add_post_meta( $post_id, 'repeat', $repeat );

	/***  For Dependency Product  ***/
	if ( isset( $pw_product_depends ) && count( $pw_product_depends ) > 0 ) {
		$id_product = array();
		foreach ( $pw_product_depends as $product_id_selected ) {
			$product_get  = wc_get_product( $product_id_selected );
			$product_type = $product_get->get_type();
			if ( $product_type == 'variable' ) {
				$variation_ids = version_compare( WC()->version, '2.7.0', '>=' ) ? $product_get->get_visible_children() : $product_get->get_children( true );
				foreach ( $variation_ids as $variation_id ) {
					$id_product[] = $variation_id;
				}
			} else {
				$id_product[] = $product_id_selected;
			}
		}
		add_post_meta( $post_id, 'pw_product_depends_temp', $id_product );
	}
	/***  End For Dependency Product ***/

	add_post_meta( $post_id, 'pw_gifts_category', $pw_gifts_category );

	add_post_meta( $post_id, 'pw_gifts_metod', $pw_gifts_metod );

	/***  For Gift Product  ***/
	$id_product = array();
	if ( isset( $pw_gifts_metod ) && $pw_gifts_metod == 'product' ) {
		if ( isset( $pw_gifts ) && count( $pw_gifts ) > 0 ) {
			foreach ( $pw_gifts as $product_id_selected ) {
				$product_get  = wc_get_product( $product_id_selected );
				$product_type = $product_get->get_type();
				if ( $product_type == 'variable' ) {
					$variation_ids = version_compare( WC()->version, '2.7.0', '>=' ) ? $product_get->get_visible_children() : $product_get->get_children( true );
					foreach ( $variation_ids as $variation_id ) {
						$id_product[] = $variation_id;
					}
				} else {
					$id_product[] = $product_id_selected;
				}
			}
		}
		add_post_meta( $post_id, 'pw_gifts', $pw_gifts );
	} else {
		if ( isset( $pw_gifts_category ) && count( $pw_gifts_category ) > 0 ) {
			$query_meta_query   = array();
			$query_meta_query[] = array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => $pw_gifts_category
				)
			);
			$matched_products   = get_posts(
				array(
					'post_type'     => 'product',
					'numberposts'   => - 1,
					'post_status'   => 'publish',
					'fields'        => 'ids',
					'no_found_rows' => true,
					'tax_query'     => $query_meta_query,
				)
			);
			add_post_meta( $post_id, 'pw_gifts', $matched_products );
			foreach ( $matched_products as $gift ) {
				$product_get  = wc_get_product( $gift );
				$product_type = $product_get->get_type();
				if ( $product_type == 'variable' ) {
					$variation_ids = version_compare( WC()->version, '2.7.0', '>=' ) ? $product_get->get_visible_children() : $product_get->get_children( true );
					foreach ( $variation_ids as $variation_id ) {
						$id_product[] = $variation_id;
					}
				} else {
					$id_product[] = $gift;
				}
			}
		}
	}
	add_post_meta( $post_id, 'all_gifts_items', $id_product );
	/***  End For Gift Product  ***/

	add_post_meta( $post_id, 'pw_limit_per_user', $pw_limit_per_user );
	$array_user_info = array(
		'count'     => 0,
		'user_info' => array(
			array(
				'id'     => '',
				'number' => '',
			)
		)
	);
	add_post_meta( $post_id, 'pw_limit_cunter', $array_user_info );

	do_action( 'custom_gift_field_save', $post_id );

	if ( defined( 'plugin_dir_url_pw_woo_brand' ) ) {
		add_post_meta( $post_id, 'brand_depends', $brand_depends );
		add_post_meta( $post_id, 'pw_brand_depends', $pw_brand_depends );
		add_post_meta( $post_id, 'pw_brand_depends_method', $pw_brand_depends_method );
	}
}

?>
<script type="text/javascript">
    window.location = "<?php echo admin_url( 'admin.php?page=rule_gift' );?>";
</script>