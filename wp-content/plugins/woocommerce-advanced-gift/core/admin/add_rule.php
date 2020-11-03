<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}


$defaults = array('post_title' => stripslashes($_POST['pw_name']), 'post_type' => 'pw_gift_rule', 'post_content' => 'demo text', 'post_status' => 'publish');
if ($post_id = wp_insert_post($defaults)) {
    add_post_meta($post_id, 'pw_type', 'rule');

    add_post_meta($post_id, 'status', @$_POST['status']);
    add_post_meta($post_id, 'pw_number_gift_allowed', @$_POST['pw_number_gift_allowed']);
    add_post_meta($post_id, 'order_op_count', @$_POST['order_op_count']);
    add_post_meta($post_id, 'order_count', @$_POST['order_count']);
    add_post_meta($post_id, 'is_coupons', @$_POST['is_coupons']);
    add_post_meta($post_id, 'cart_amount_op', @$_POST['cart_amount_op']);
    add_post_meta($post_id, 'criteria_nb_products_op', @$_POST['criteria_nb_products_op']);
    add_post_meta($post_id, 'disable_if', @$_POST['disable_if']);
    add_post_meta($post_id, 'pw_name', @$_POST['pw_name']);
    add_post_meta($post_id, 'pw_rule_description', @$_POST['pw_rule_description']);

    add_post_meta($post_id, 'product_depends', @$_POST['product_depends']);
    add_post_meta($post_id, 'exclude_product_depends', @$_POST['exclude_product_depends']);
    add_post_meta($post_id, 'pw_product_depends', @$_POST['pw_product_depends']);
    add_post_meta($post_id, 'pw_product_depends_method', @$_POST['pw_product_depends_method']);
    add_post_meta($post_id, 'pw_exclude_product_depends', @$_POST['pw_exclude_product_depends']);
    add_post_meta($post_id, 'category_depends', @$_POST['category_depends']);
    add_post_meta($post_id, 'pw_category_depends', @$_POST['pw_category_depends']);    
	add_post_meta($post_id, 'exclude_category_depends', @$_POST['exclude_category_depends']);
    add_post_meta($post_id, 'exclude_pw_category_depends', @$_POST['exclude_pw_category_depends']);
    add_post_meta($post_id, 'pw_category_depends_method', @$_POST['pw_category_depends_method']);
    add_post_meta($post_id, 'users_depends', @$_POST['users_depends']);
    add_post_meta($post_id, 'roles_depends', @$_POST['roles_depends']);
    add_post_meta($post_id, 'pw_roles', @$_POST['pw_roles']);    
	add_post_meta($post_id, 'exclude_roles_depends', @$_POST['exclude_roles_depends']);
    add_post_meta($post_id, 'pw_exclude_roles', @$_POST['pw_exclude_roles']);
    add_post_meta($post_id, 'pw_users', @$_POST['pw_users']);
    add_post_meta($post_id, 'pw_cart_amount', @$_POST['pw_cart_amount']);
    add_post_meta($post_id, 'pw_cart_amount_min', @$_POST['pw_cart_amount_min']);
    add_post_meta($post_id, 'pw_cart_amount_max', @$_POST['pw_cart_amount_max']);
    add_post_meta($post_id, 'criteria_nb_products_min', @$_POST['criteria_nb_products_min']);
    add_post_meta($post_id, 'criteria_nb_products_max', @$_POST['criteria_nb_products_max']);
    add_post_meta($post_id, 'pw_from', @$_POST['pw_from']);
    add_post_meta($post_id, 'pw_to', @$_POST['pw_to']);
    add_post_meta($post_id, 'criteria_nb_products', @$_POST['criteria_nb_products']);
    add_post_meta($post_id, 'gift_preselector_product_page', @$_POST['gift_preselector_product_page']);
    add_post_meta($post_id, 'gift_auto_to_cart', @$_POST['gift_auto_to_cart']);
    add_post_meta($post_id, 'pw_limit_per_rule', @$_POST['pw_limit_per_rule']);
    add_post_meta($post_id, 'pw_register_user', @$_POST['pw_register_user']);
    add_post_meta($post_id, 'schedule_type', @$_POST['schedule_type']);
    add_post_meta($post_id, 'pw_weekly', @$_POST['pw_weekly']);
    add_post_meta($post_id, 'pw_daily', @$_POST['pw_daily']);
    add_post_meta($post_id, 'pw_monthly', @$_POST['pw_monthly']);
    add_post_meta($post_id, 'can_several_gift', @$_POST['can_several_gift']);
    add_post_meta($post_id, 'repeat_sum_qty', @$_POST['repeat_sum_qty']);
    add_post_meta($post_id, 'repeat', @$_POST['repeat']);
	
	/***  For Dependency Product  ***/
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
		add_post_meta($post_id, 'pw_product_depends_temp', $id_product);			
	}
	/***  End For Dependency Product ***/
	
    add_post_meta($post_id, 'pw_gifts_category', @$_POST['pw_gifts_category']);
    
	add_post_meta($post_id, 'pw_gifts_metod', @$_POST['pw_gifts_metod']);
	
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
		}
		add_post_meta($post_id, 'pw_gifts', @$_POST['pw_gifts']);		
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
			add_post_meta($post_id, 'pw_gifts', $matched_products);		
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
	add_post_meta($post_id, 'all_gifts_items', $id_product);
	/***  End For Gift Product  ***/
	
    add_post_meta($post_id, 'pw_limit_per_user', @$_POST['pw_limit_per_user']);
    $array_user_info = array(
        'count' => 0,
        'user_info' => array(
            array(
                'id' => '',
                'number' => '',
            )
        )
    );
    add_post_meta($post_id, 'pw_limit_cunter', @$array_user_info);
	
	do_action('custom_gift_field_save',$post_id);
	
    if (defined('plugin_dir_url_pw_woo_brand')) {
        add_post_meta($post_id, 'brand_depends', @$_POST['brand_depends']);
        add_post_meta($post_id, 'pw_brand_depends', @$_POST['pw_brand_depends']);
        add_post_meta($post_id, 'pw_brand_depends_method', @$_POST['pw_brand_depends_method']);
    }
    ?>
    <script type="text/javascript">
        window.location = "<?php echo admin_url('admin.php?page=rule_gift&tab=add_rule&pw_action_type=edit&pw_id=' . $post_id);?>";
    </script>';
    <?php
}


?>