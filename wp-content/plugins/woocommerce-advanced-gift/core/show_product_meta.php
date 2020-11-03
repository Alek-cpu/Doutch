<?php

class pw_class_woocommerce_gift_show_product_meta
{

    public function __construct()
    {
		add_shortcode('pw_gift_ptoduct',array($this,  'pw_woocommerc_show_gift_shortcode'));
        add_action('woocommerce_single_product_summary', array($this, 'pw_woocommerc_show_gift'), 10);
    }
	
	function pw_woocommerc_show_gift_shortcode($atts, $content = null)
	{
		extract(shortcode_atts(array(
			'pw' => '1',
		), $atts));
        global $post, $woocommerce;
        if (!is_singular('product'))
            return;
        $cart_contents = "";
        $query_meta_query = array('relation' => 'AND');
        $query_meta_query[] = array(
            'key' => 'status',
            'value' => "active",
            'compare' => '=',
        );
        $cart_contents = $woocommerce->cart->cart_contents;
        $matched_products = get_posts(
            array(
                'post_type' => 'pw_gift_rule',
                'numberposts' => -1,
                'post_status' => 'publish',
                'fields' => 'ids',
                'no_found_rows' => true,
                'orderby' => 'modified',
                'meta_query' => $query_meta_query,
            )
        );
        $products = array();
        $category = $this->get_cart_item_categories($post->ID);
        if (defined('plugin_dir_url_pw_woo_brand')) {
            $brands = $this->get_cart_item_brands($post->ID);
        }
        $cart_count = $woocommerce->cart->cart_contents_count;
		$pw_rule_description = '';
        foreach ($matched_products as $pr) {
            $is_ok = true;
            $amount = 0;
            $product_depends = $category_depend = $pw_cart_amount = "";

            //$gift_preselector_product_page = get_post_meta($pr, 'gift_preselector_product_page', true);
            //check for display product
           // if ($gift_preselector_product_page == "no")
			//{
               // continue;
			//}
            $pw_to = strtotime(get_post_meta($pr, 'pw_to', true));
            $pw_from = strtotime(get_post_meta($pr, 'pw_from', true));
            $blogtime = strtotime(current_time('mysql'));

            if (($pw_to != "" && $blogtime > $pw_to) || ($pw_from != "" && $blogtime < $pw_from))
			{
				continue;
			}

            //Check number product in cart
            $criteria_nb_products = get_post_meta($pr, 'criteria_nb_products', true);
            if ($cart_count < $criteria_nb_products)
			{
                continue;
			}
            //For Amont_cart
            $product_curennt = wc_get_product($post->ID);
            $amount = floatval(preg_replace('#[^\d.]#', '', $woocommerce->cart->get_cart_total()));
            $pw_cart_amount = get_post_meta($pr, 'pw_cart_amount', true);
            $pw_cart_amount = ($pw_cart_amount != "" ? $pw_cart_amount : 0);
            $amount += $product_curennt->get_price();

            if ($pw_cart_amount > $amount)
			{
                continue;
			}

            //End check Roles
            $pw_users = get_post_meta($pr, 'pw_users', true);
            $users_depends = get_post_meta($pr, 'users_depends', true);
            if ($users_depends == 'yes' && count($pw_users) && is_array($pw_users)) {
                $result = false;
                if (is_user_logged_in()) {
                    if (in_array(get_current_user_id(), $pw_users)) {
                        $result = true;
                    }
                }
                if ($result == false)
				{
					continue;
				}
            }//End Check Users

            $product_depends = get_post_meta($pr, 'product_depends', true);
            $category_depends = get_post_meta($pr, 'category_depends', true);
            $exclude_category_depends = get_post_meta($pr, 'exclude_category_depends', true);

            if (defined('plugin_dir_url_pw_woo_brand')) {
                $brand_depends = get_post_meta($pr, 'brand_depends', true);
                if ($brand_depends == "yes")
				{
                    if (get_post_meta($pr, 'pw_brand_depends', true) != "")
					{
                        if (count(array_intersect($brands, get_post_meta($pr, 'pw_brand_depends', true))) <= 0)
						{
                            $is_ok = false;
						}
					}
				}
            }
            //$quantity = (isset($cart_item['quantity']) && $cart_item['quantity']) ? $cart_item['quantity'] : 1;
            //$amount += $cart_item['data']->get_price() * $quantity;
			$pw_product_depends_check=get_post_meta($pr, 'pw_product_depends_temp',true);
            if ($product_depends == "yes" && count($pw_product_depends_check) > 0) {
                if (!in_array($post->ID, $pw_product_depends_check)) {
                    $is_ok = false;
                }
            }
            if ($category_depends == "yes")
			{
                if (get_post_meta($pr, 'pw_category_depends', true) != "")
				{
                    if (count(array_intersect($category, get_post_meta($pr, 'pw_category_depends', true))) <= 0)
					{
                        $is_ok = false;
					}
				}
			}
			
            if ($exclude_category_depends == "yes")
			{
                if (get_post_meta($pr, 'exclude_pw_category_depends', true) != "")
				{
                    if (count(array_intersect($category, get_post_meta($pr, 'exclude_pw_category_depends', true))) > 0 )
					{
                        $is_ok = false;
					}
				}
			}			
            //if($pw_cart_amount>$amount)
            //	$is_ok=false;

            if ($is_ok == false)
			{
				continue;
			}

            //Get Gift Product's For Display
            $pw_gifts_metod = get_post_meta($pr, 'pw_gifts_metod', true);
            if ($pw_gifts_metod == "product") {
                $pw_gifts = get_post_meta($pr, 'pw_gifts', true);
				if(is_array($pw_gifts)){
					foreach ($pw_gifts as $r) {
						$products[] = $r;
						//echo $r;
					}
				}
            } else {
                $pw_gifts_category = get_post_meta($pr, 'pw_gifts_category', true);
                $query_meta_query[] = array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'id',
                        'terms' => $pw_gifts_category
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
                foreach ($matched_products as $r) {
                    $products[] = $r;
                }
            }
			$pw_rule_description .= get_post_meta($pr, 'pw_rule_description', true);
            //break_one_rule:
        }
        if ($products == "" || !is_array($products) || count($products) <= 0)
		{
            return;
		}
		
        //print_r($products);
        $products = array_unique($products);
        $cart_page_id = wc_get_page_id('cart');
        $setting = get_option("pw_gift_options");
        $ret.= '<div class="rol-thumb-cnt">
					<div class="rol-thumb-title">' . $setting['txt_single_product'] . ':</div>	
				  ' . $pw_rule_description;
        foreach ($products as $r) {
            $product = wc_get_product($r);
            //echo $product->id;
            $title = $product->get_title();
            $img_url = wp_get_attachment_image_src($product->get_image_id(), 'medium');
            $img_url = $img_url[0];

            $ret.= '<div class="rol-thumb"><img src="' . $img_url . '" title="' . $title . '" /></div>';
            //echo $title.' '.$img_url;
        }
        $ret.= '</div>';
		return $ret;
	}
    public function pw_woocommerce_brand_single_position()
    {
        $position = get_option('pw_woocommerce_brands_position_single_brand', "default");
        if ($position == 'default') {
            add_action('woocommerce_product_meta_end', array($this, 'pw_woocommerc_show_gift'), 10);
        } elseif ($position == '1') {
            add_action('woocommerce_single_product_summary', array($this, 'pw_woocommerc_show_gift'), 3);
        } elseif ($position == '2') {
            add_action('woocommerce_single_product_summary', array($this, 'pw_woocommerc_show_gift'), 7);
        } elseif ($position == '3') {
            add_action('woocommerce_single_product_summary', array($this, 'pw_woocommerc_show_gift'), 15);
        } elseif ($position == '4') {
            add_action('woocommerce_single_product_summary', array($this, 'pw_woocommerc_show_gift'), 25);
        } elseif ($position == '5') {
            add_action('woocommerce_single_product_summary', array($this, 'pw_woocommerc_show_gift'), 35);
        } elseif ($position == '6') {
            add_action('woocommerce_single_product_summary', array($this, 'pw_woocommerc_show_gift'), 55);
        }
    }

    public function pw_woocommerc_show_gift()
    {
        global $post, $woocommerce;
        if (!is_singular('product'))
            return;
        $cart_contents = "";
        $query_meta_query = array('relation' => 'AND');
        $query_meta_query[] = array(
            'key' => 'status',
            'value' => "active",
            'compare' => '=',
        );
        $cart_contents = $woocommerce->cart->cart_contents;
        $matched_products = get_posts(
            array(
                'post_type' => 'pw_gift_rule',
                'numberposts' => -1,
                'post_status' => 'publish',
                'fields' => 'ids',
                'no_found_rows' => true,
                'orderby' => 'modified',
                'meta_query' => $query_meta_query,
            )
        );
        $products = array();
        $category = $this->get_cart_item_categories($post->ID);
        if (defined('plugin_dir_url_pw_woo_brand')) {
            $brands = $this->get_cart_item_brands($post->ID);
        }
        $cart_count = $woocommerce->cart->cart_contents_count;
		$pw_rule_description = '';
        foreach ($matched_products as $pr) {
            $is_ok = true;
            $amount = 0;
            $product_depends = $category_depend = $pw_cart_amount = "";

            $gift_preselector_product_page = get_post_meta($pr, 'gift_preselector_product_page', true);
            //check for display product
            if ($gift_preselector_product_page == "no")
			{
                continue;
			}
            $pw_to = strtotime(get_post_meta($pr, 'pw_to', true));
            $pw_from = strtotime(get_post_meta($pr, 'pw_from', true));
            $blogtime = strtotime(current_time('mysql'));

            if (($pw_to != "" && $blogtime > $pw_to) || ($pw_from != "" && $blogtime < $pw_from))
			{
				continue;
			}

            //Check number product in cart
            $criteria_nb_products = get_post_meta($pr, 'criteria_nb_products', true);
            if ($cart_count < $criteria_nb_products)
			{
                continue;
			}
            //For Amont_cart
            $product_curennt = wc_get_product($post->ID);
            $amount = floatval(preg_replace('#[^\d.]#', '', $woocommerce->cart->get_cart_total()));
            $pw_cart_amount = get_post_meta($pr, 'pw_cart_amount', true);
            $pw_cart_amount = ($pw_cart_amount != "" ? $pw_cart_amount : 0);
            $amount += $product_curennt->get_price();

            if ($pw_cart_amount > $amount)
			{
                continue;
			}

            //End check Roles
            $pw_users = get_post_meta($pr, 'pw_users', true);
            $users_depends = get_post_meta($pr, 'users_depends', true);
            if ($users_depends == 'yes' && count($pw_users) && is_array($pw_users)) {
                $result = false;
                if (is_user_logged_in()) {
                    if (in_array(get_current_user_id(), $pw_users)) {
                        $result = true;
                    }
                }
                if ($result == false)
				{
					continue;
				}
            }//End Check Users
            /*	//For Check Roles
                $pw_roles = get_post_meta($pr,'pw_roles');
                $roles_depends = get_post_meta($pr,'roles_depends',true);
                if($roles_depends=="yes" && count($pw_roles)>0)
                {

                    $result=false;
                    if (is_user_logged_in()) {
                        foreach ($pw_roles as $role) {
                            if (current_user_can($role)) {
                                $result = true;
                                break;
                            }
                        }
                    }

                    if($result==false)
                        goto break_one_rule;
                }
                //End For Check Roles
                */

            $product_depends = get_post_meta($pr, 'product_depends', true);
            $category_depends = get_post_meta($pr, 'category_depends', true);
            $exclude_category_depends = get_post_meta($pr, 'exclude_category_depends', true);

            if (defined('plugin_dir_url_pw_woo_brand')) {
                $brand_depends = get_post_meta($pr, 'brand_depends', true);
                if ($brand_depends == "yes")
				{
                    if (get_post_meta($pr, 'pw_brand_depends', true) != "")
					{
                        if (count(array_intersect($brands, get_post_meta($pr, 'pw_brand_depends', true))) <= 0)
						{
                            $is_ok = false;
						}
					}
				}
            }
            //$quantity = (isset($cart_item['quantity']) && $cart_item['quantity']) ? $cart_item['quantity'] : 1;
            //$amount += $cart_item['data']->get_price() * $quantity;
			$pw_product_depends_check=get_post_meta($pr, 'pw_product_depends_temp',true);
            if ($product_depends == "yes" && count($pw_product_depends_check) > 0) {
                if (!in_array($post->ID, $pw_product_depends_check)) {
                    $is_ok = false;
                }
            }
            if ($category_depends == "yes")
			{
                if (get_post_meta($pr, 'pw_category_depends', true) != "")
				{
                    if (count(array_intersect($category, get_post_meta($pr, 'pw_category_depends', true))) <= 0)
					{
                        $is_ok = false;
					}
				}
			}

            if ($exclude_category_depends == "yes")
			{
                if (get_post_meta($pr, 'exclude_pw_category_depends', true) != "")
				{
                    if (count(array_intersect($category, get_post_meta($pr, 'exclude_pw_category_depends', true))) > 0 )
					{
                        $is_ok = false;
					}
				}
			}			
            //if($pw_cart_amount>$amount)
            //	$is_ok=false;

            if ($is_ok == false)
			{
				continue;
			}

            //Get Gift Product's For Display
            $pw_gifts_metod = get_post_meta($pr, 'pw_gifts_metod', true);
            if ($pw_gifts_metod == "product") {
                $pw_gifts = get_post_meta($pr, 'pw_gifts', true);
				if(is_array($pw_gifts)){
					foreach ($pw_gifts as $r) {
						$products[] = $r;
						//echo $r;
					}
				}
            } else {
                $pw_gifts_category = get_post_meta($pr, 'pw_gifts_category', true);
                $query_meta_query[] = array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'id',
                        'terms' => $pw_gifts_category
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
                foreach ($matched_products as $r) {
                    $products[] = $r;
                }
            }
			$pw_rule_description .= get_post_meta($pr, 'pw_rule_description', true);
            //break_one_rule:
        }
        if ($products == "" || !is_array($products) || count($products) <= 0)
		{
            return;
		}
		
        //print_r($products);
        $products = array_unique($products);
        $cart_page_id = wc_get_page_id('cart');
        $setting = get_option("pw_gift_options");
        echo '<div class="rol-thumb-cnt">
					<div class="rol-thumb-title">' . $setting['txt_single_product'] . ':</div>	
				  ' . $pw_rule_description;
        foreach ($products as $r) {
            $product = wc_get_product($r);
            //echo $product->id;
            $title = $product->get_title();
            $img_url = wp_get_attachment_image_src($product->get_image_id(), 'medium');
            $img_url = $img_url[0];

            echo '<div class="rol-thumb"><img src="' . $img_url . '" title="' . $title . '" /></div>';
            //echo $title.' '.$img_url;
        }
        echo '</div>';
    }


    public function get_cart_item_brands($item)
    {
        $categories = array();
        $current = wp_get_post_terms($item, 'product_brand');
        foreach ($current as $category) {
            $categories[] = $category->term_id;
        }
        return $categories;
    }

    public function get_cart_item_categories($item)
    {
        $categories = array();
        $current = wp_get_post_terms($item, 'product_cat');
        foreach ($current as $category) {
            $categories[] = $category->term_id;
        }
        return $categories;
    }
}

new pw_class_woocommerce_gift_show_product_meta();
?>