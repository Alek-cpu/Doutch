<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$post_id = stripslashes(@$_GET['pw_id']);
wp_update_post(array(
    'ID' => @$_GET['pw_id'],
    'post_title' => @$_POST['pw_name']
));
update_post_meta($post_id, 'status', @$_POST['status']);
update_post_meta($post_id, 'order_op_count', @$_POST['order_op_count']);
update_post_meta($post_id, 'is_coupons', @$_POST['is_coupons']);
update_post_meta($post_id, 'cart_amount_op', @$_POST['cart_amount_op']);
update_post_meta($post_id, 'criteria_nb_products_op', @$_POST['criteria_nb_products_op']);
update_post_meta($post_id, 'order_count', @$_POST['order_count']);
update_post_meta($post_id, 'pw_number_gift_allowed', @$_POST['pw_number_gift_allowed']);
update_post_meta($post_id, 'disable_if', @$_POST['disable_if']);
update_post_meta($post_id, 'pw_name', @$_POST['pw_name']);
update_post_meta($post_id, 'pw_rule_description', @$_POST['pw_rule_description']);

if (isset($_POST['pw_product_depends']) &&  count(@$_POST['pw_product_depends']) > 0)
{
	$id_product=array();	
	foreach (@$_POST['pw_product_depends'] as $product_id_selected) {
		$product_get = wc_get_product($product_id_selected);
		$product_type = $product_get->get_type();
		if ($product_type == 'variable') {
			$variation_ids = version_compare(WC()->version, '2.7.0', '>=') ? $product_get->get_visible_children() : $product_get->get_children(true);
			foreach ($variation_ids as $variation_id) {
				$id_product[]=$variation_id;
			}
		}
		else{
			$id_product[]=$product_id_selected;
		}
	}
	update_post_meta($post_id, 'pw_product_depends_temp', $id_product);	
}

update_post_meta($post_id, 'pw_gifts_metod', @$_POST['pw_gifts_metod']);
update_post_meta($post_id, 'pw_gifts_category', @$_POST['pw_gifts_category']);

/***  For Gift Product  ***/
$id_product=array();
if(isset($_POST['pw_gifts_metod']) && $_POST['pw_gifts_metod']=='product')
{
	if (isset($_POST['pw_gifts']) &&  count(@$_POST['pw_gifts']) > 0)
	{
		foreach (@$_POST['pw_gifts'] as $product_id_selected) {
			$product_get = wc_get_product($product_id_selected);
			$product_type = $product_get->get_type();
			if ($product_type == 'variable') {
				$variation_ids = version_compare(WC()->version, '2.7.0', '>=') ? $product_get->get_visible_children() : $product_get->get_children(true);
				foreach ($variation_ids as $variation_id) {
					$id_product[]=$variation_id;
				}
			}
			else{
				$id_product[]=$product_id_selected;
			}
		}
		//update_post_meta($post_id, 'product_depends', @$_POST['product_depends']);
		update_post_meta($post_id, 'pw_gifts', @$_POST['pw_gifts']);
	}
	else{
		update_post_meta($post_id, 'pw_gifts', 'no');
	}
}
else
{
	
	if (isset($_POST['pw_gifts_category']) &&  count(@$_POST['pw_gifts_category']) > 0)	{
		$query_meta_query = array();
		$query_meta_query[] = array(
			array(
				'taxonomy' => 'product_cat',
				'field' => 'id',
				'terms' => $_POST['pw_gifts_category']
			)
		);
		$matched_products = get_posts(
			array(
				'post_type' => 'product',
				'numberposts' => -1,
				'post_status' => 'publish',
				'fields' => 'ids',
				'no_found_rows' => true,
				'tax_query' => $query_meta_query,
			)
		);

		update_post_meta($post_id, 'pw_gifts', $matched_products);
		foreach ($matched_products as $gift) {
			$product_get = wc_get_product($gift);
			$product_type = $product_get->get_type();
			if ($product_type == 'variable') {
				$variation_ids = version_compare(WC()->version, '2.7.0', '>=') ? $product_get->get_visible_children() : $product_get->get_children(true);
				foreach ($variation_ids as $variation_id) {
					$id_product[]=$variation_id;
				}
			}
			else{
				$id_product[]=$gift;
			}
		}
	}
}
update_post_meta($post_id, 'all_gifts_items', $id_product);
/***  End For Gift Product  ***/

update_post_meta($post_id, 'pw_product_depends', @$_POST['pw_product_depends']);
update_post_meta($post_id, 'product_depends', @$_POST['product_depends']);
update_post_meta($post_id, 'exclude_product_depends', @$_POST['exclude_product_depends']);
update_post_meta($post_id, 'pw_exclude_product_depends', @$_POST['pw_exclude_product_depends']);
update_post_meta($post_id, 'pw_product_depends_method', @$_POST['pw_product_depends_method']);

if (!isset($_POST['pw_category_depends']) || count(@$_POST['pw_category_depends']) <= 0)
    update_post_meta($post_id, 'category_depends', 'no');
else
    update_post_meta($post_id, 'category_depends', @$_POST['category_depends']);

if (!isset($_POST['exclude_pw_category_depends']) || count(@$_POST['exclude_pw_category_depends']) <= 0)
    update_post_meta($post_id, 'exclude_category_depends', 'no');
else
    update_post_meta($post_id, 'exclude_category_depends', @$_POST['exclude_category_depends']);

update_post_meta($post_id, 'pw_category_depends', @$_POST['pw_category_depends']);
update_post_meta($post_id, 'exclude_pw_category_depends', @$_POST['exclude_pw_category_depends']);
update_post_meta($post_id, 'pw_category_depends_method', @$_POST['pw_category_depends_method']);

if (!isset($_POST['pw_users']) || count(@$_POST['pw_users']) <= 0)
    update_post_meta($post_id, 'users_depends', 'no');
else
    update_post_meta($post_id, 'users_depends', @$_POST['users_depends']);

update_post_meta($post_id, 'pw_users', @$_POST['pw_users']);

if (!isset($_POST['pw_roles']) || count(@$_POST['pw_roles']) <= 0)
    update_post_meta($post_id, 'roles_depends', 'no');
else
    update_post_meta($post_id, 'roles_depends', @$_POST['roles_depends']);

update_post_meta($post_id, 'pw_roles', @$_POST['pw_roles']);

if (!isset($_POST['pw_exclude_roles']) || count(@$_POST['pw_exclude_roles']) <= 0)
    update_post_meta($post_id, 'exclude_roles_depends', 'no');
else
    update_post_meta($post_id, 'exclude_roles_depends', @$_POST['exclude_roles_depends']);

update_post_meta($post_id, 'pw_exclude_roles', @$_POST['pw_exclude_roles']);

update_post_meta($post_id, 'pw_cart_amount', @$_POST['pw_cart_amount']);
update_post_meta($post_id, 'pw_cart_amount_min', @$_POST['pw_cart_amount_min']);
update_post_meta($post_id, 'pw_cart_amount_max', @$_POST['pw_cart_amount_max']);
update_post_meta($post_id, 'criteria_nb_products_min', @$_POST['criteria_nb_products_min']);
update_post_meta($post_id, 'criteria_nb_products_max', @$_POST['criteria_nb_products_max']);
update_post_meta($post_id, 'can_several_gift', @$_POST['can_several_gift']);
update_post_meta($post_id, 'pw_from', @$_POST['pw_from']);
update_post_meta($post_id, 'pw_to', @$_POST['pw_to']);
update_post_meta($post_id, 'criteria_nb_products', @$_POST['criteria_nb_products']);
update_post_meta($post_id, 'gift_preselector_product_page', @$_POST['gift_preselector_product_page']);
update_post_meta($post_id, 'gift_auto_to_cart', @$_POST['gift_auto_to_cart']);

update_post_meta($post_id, 'pw_limit_per_rule', @$_POST['pw_limit_per_rule']);
update_post_meta($post_id, 'pw_register_user', @$_POST['pw_register_user']);
update_post_meta($post_id, 'schedule_type', @$_POST['schedule_type']);
update_post_meta($post_id, 'pw_weekly', @$_POST['pw_weekly']);
update_post_meta($post_id, 'pw_daily', @$_POST['pw_daily']);
update_post_meta($post_id, 'pw_monthly', @$_POST['pw_monthly']);
update_post_meta($post_id, 'repeat', @$_POST['repeat']);
//update_post_meta($post_id, 'gift_notify_add', @$_POST['gift_notify_add']);

//For old version plugin install
$pw_limit_cunter = get_post_meta($post_id, 'pw_limit_cunter', true);
if (!is_array($pw_limit_cunter)) {
    $array_user_info = array(
        'count' => 0,
        'user_info' => array(
            array(
                'id' => '',
                'number' => '',

            )
        )
    );
    update_post_meta($post_id, 'pw_limit_cunter', $array_user_info);

}
//$pw_limit_per_rule_cunter = (@$_POST['pw_limit_per_rule_cunter'] < @$_POST['pw_limit_per_rule'] ? @$_POST['pw_limit_per_rule_cunter'] : @$_POST['pw_limit_per_rule']);
//update_post_meta($post_id, 'pw_limit_per_rule_cunter', @$_POST['pw_limit_per_rule_cunter']);

update_post_meta($post_id, 'pw_limit_per_user', @$_POST['pw_limit_per_user']);
if (defined('plugin_dir_url_pw_woo_brand')) {
    if (!isset($_POST['pw_brand_depends']) || count(@$_POST['pw_brand_depends']) <= 0)
        update_post_meta($post_id, 'brand_depends', 'no');
    else
        update_post_meta($post_id, 'brand_depends', @$_POST['brand_depends']);

    update_post_meta($post_id, 'pw_brand_depends', @$_POST['pw_brand_depends']);
    update_post_meta($post_id, 'pw_brand_depends_method', @$_POST['pw_brand_depends_method']);
}

do_action('custom_gift_field_update',$post_id);
?>