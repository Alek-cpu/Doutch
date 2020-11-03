<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
wp_enqueue_style( 'woocommerce_admin_styles' );
wp_enqueue_style( 'jquery-ui-style' );
wp_enqueue_script( 'wc-enhanced-select' );
?>
<form id="pw_create_level_form" class="pw_create_level_form" method="POST">
    <div class="pw-form-cnt">
        <div class="pw-form-content">
            <div class="pw-section-title"><?php _e('General Setting', 'pw_wc_advanced_gift') ?></div>
            <table class="pw-form-table">
                <tbody>
                <tr>
                    <th>
                        <span><?php _e('Status', 'pw_wc_advanced_gift'); ?></span>
                    </th>
                    <td>
                        <input type="radio" name="status" value="active" <?php checked($status, "active"); ?>
                               checked><?php _e('Active', 'pw_wc_advanced_gift') ?>
                        <input type="radio" name="status"
                               value="deactive" <?php checked($status, "deactive"); ?>><?php _e('Deactive', 'pw_wc_advanced_gift') ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <span><?php _e('Rule Name', 'pw_wc_advanced_gift') ?></span>
                    </th>
                    <td>
                        <input type="text" name="pw_name" id="pw_name" value="<?php echo $pw_name; ?>" class="require">
                    </td>
                </tr>
                <tr>
                    <th>
                        <span><?php _e('Rule Description', 'pw_wc_advanced_gift') ?></span>						
                    </th>
                    <td>
                        <?php
                        $editor_args = array(
                            'textarea_rows' => 5,
                        );
                        $editor_id = 'pw_rule_description';
                        wp_editor($pw_rule_description, $editor_id, $editor_args);
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <span><?php _e('Only for Register Users', 'pw_wc_advanced_gift'); ?></span>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('This Gifts rule is only for register user in site', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="radio" name="pw_register_user" class="pw_register_user"
                               value="no" <?php checked($pw_register_user, "no"); ?> checked><?php _e('No', 'pw_wc_advanced_gift') ?>
                        <input type="radio" name="pw_register_user" class="pw_register_user"
                               value="yes" <?php checked($pw_register_user, "yes"); ?>><?php _e('Yes', 'pw_wc_advanced_gift') ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <span><?php _e('Usage limit per Rule', 'pw_wc_advanced_gift') ?>	</span>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('How many times this Gifts Rule can be used before it is void.', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="number" min="0" step="1" name="pw_limit_per_rule"
                               placeholder="<?php _e('Unlimited usage', 'pw_wc_advanced_gift'); ?>"
                               id="pw_limit_per_rule"
                               value="<?php echo $pw_limit_per_rule; ?>" class="require">
                        <?php
                        if (isset($_GET['pw_id'])) {
                            ?>
                            <span class="pw-limit-alert">
                                <?php _e('(This rule is used in <span class="pw_limit_cunter_count">' . @$pw_limit_cunter['count'] . '</span> order)', 'pw_wc_advanced_gift'); ?>
                            </span>

                            <a class="pw_reset_gift_rule pw-res-btn pw-action-icon"
                               href="#" title="<?php _e('Reset', 'pw_wc_advanced_gift'); ?>"><i
                                        class="fa fa-repeat"></i><?php _e('Reset', 'pw_wc_advanced_gift'); ?></a>

                            <script language="javascript">
                                jQuery('.pw_reset_gift_rule').click(function (e) {
                                    e.preventDefault();
                                    if (confirm('Are You Sure !!!')) {
                                        jQuery.ajax({
                                            type: "POST",
                                            url: ajaxurl,
                                            data: "action=pw_rest_usage_rule_gift&pw_id=<?php echo $_GET['pw_id'];?>",
                                            success: function (data) {
                                                jQuery(".pw_limit_cunter_count").fadeOut();
                                                jQuery(".pw_limit_cunter_count").html('0');
                                                jQuery(".pw_limit_cunter_count").fadeIn(3000)
                                            }
                                        });
                                    }
                                });
                            </script>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <tr class="pw_gifts_limit_user">
                    <th>
                        <span><?php _e('Usage limit per user', 'pw_wc_advanced_gift') ?>	</span>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('How many times this Gifts Rule can be used by an individual user.user ID for logged in users.', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="number" min="0" step="1" name="pw_limit_per_user"
                               placeholder="<?php _e('Unlimited usage', 'pw_wc_advanced_gift'); ?>"
                               id="pw_limit_per_user"
                               value="<?php echo $pw_limit_per_user; ?>" class="require">
                    </td>
                </tr>
				<?php 
				do_action('custom_gift_field_after_limit_user');
				?>				
                <tr>
                    <th>
                        <span><?php _e('Gifts', 'pw_wc_advanced_gift') ?></span>

                    </th>
                    <td>
                        <div>
                            <select name="pw_gifts_metod" class="pw_gifts_metod" data-placeholder="Choose...">
                                <option value="product" <?php selected("product", $pw_gifts_metod, 1); ?>><?php _e('Product', 'pw_wc_advanced_gift'); ?></option>
                                <option value="category" <?php selected("category", $pw_gifts_metod, 1); ?>><?php _e('Category', 'pw_wc_advanced_gift'); ?></option>
                            </select>
                        </div>
                    </td>
                </tr>
                <tr class="pw_gifts_metod_product">
                    <th>
                        <span><?php _e('Products', 'pw_wc_advanced_gift') ?></span>

                    </th>
                    <td>
                        <div>
							<select class="wc-product-search" multiple="multiple" style="width: 50%;" name="pw_gifts[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations">
							  <?php
								  $meta=$pw_gifts;
                                  if (is_array($meta)) {
                                      foreach ($meta as $product_id) {
                                          $product = wc_get_product($product_id);
                                          if (is_object($product)) {
                                              echo '<option value="' . esc_attr($product_id) . '"' . selected(true, true, false) . '>' . wp_kses_post($product->get_formatted_name()) . '</option>';
                                          }
                                      }
                                  }
							  ?>
							</select>							
                        </div>
                    </td>
                </tr>
                <tr class="pw_gifts_metod_category">
                    <th>
                        <?php _e('Selecte Category', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <div>
                            <select name="pw_gifts_category[]" class="chosen-select" multiple="multiple"
                                    data-placeholder="<?php _e('Choose Category', 'pw_wc_advanced_gift') ?> ...">
                                <?php
                                $categories = $this->get_all_category_list();
                                $param_line = "";
                                foreach ($categories as $category) {
                                    $selected = '';
                                    $meta = $pw_gifts_category;
                                    if (is_array($meta))
                                        $selected = (in_array($category->term_id, $meta) ? "SELECTED" : "");

                                    $option = '<option value="' . $category->term_id . '" ' . $selected . '>';
                                    $option .= $category->name;
                                    $option .= ' (' . $category->count . ')';
                                    $option .= '</option>';
                                    $param_line .= $option;
                                }
                                $param_line .= '</select>';
                                echo $param_line;
                                ?>
                            </select>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Number of gifts allowed', 'pw_wc_advanced_gift') ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('number select gift in this rule! (if you selected multi product or category in above for gift)', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="text" class="pw_number_gift_allowed" name="pw_number_gift_allowed"
                               value="<?php echo $pw_number_gift_allowed; ?>">
                        <?php _e('Please check "User Can Add Multiple Gift In One Order" In ', 'pw_wc_advanced_gift');
                        echo ' <a target="_blank" href="' . admin_url('admin.php?page=rule_gift&tab=setting&pw_action_type=list') . '">' . __('Settings', 'woocommerce-brands') . '</a>';
                        ?>
                    </td>
                </tr>
                <tr class="can_several_gift_tr">
                    <th>
                        <?php _e('User Can choose a same gifts several times', 'pw_wc_advanced_gift'); ?>

                    </th>
                    <td>
                        <input type="radio" name="can_several_gift"
                               value="no" <?php if ($can_several_gift != "yes") echo "checked"; ?>
                               class="can_several_gift"><?php _e('No', 'pw_wc_advanced_gift'); ?>					
                        <input type="radio" name="can_several_gift"
                               value="yes" <?php checked($can_several_gift, "yes"); ?>
                               class="can_several_gift"><?php _e('Yes', 'pw_wc_advanced_gift'); ?>

                    </td>
                </tr>
                </tbody>
            </table>

            <div class="pw-space"></div>
            <div class="pw-section-title"><?php _e('Rule Criteria', 'pw_wc_advanced_gift'); ?></div>
            <table class="pw-form-table">
                <tbody>
                <tr>
                    <th>
                        <?php _e('Product Dependency', 'pw_wc_advanced_gift'); ?>

                    </th>
                    <td>
                        <input type="radio" name="product_depends"
                               value="no" <?php if ($product_depends != "yes") echo "checked"; ?>
                               class="product_depends"><?php _e('No', 'pw_wc_advanced_gift'); ?>					
                        <input type="radio" name="product_depends"
                               value="yes" <?php checked($product_depends, "yes"); ?> class="product_depends"><?php _e('Yes', 'pw_wc_advanced_gift'); ?>

                    </td>
                </tr>

                <tr class="pw_product_depends">
                    <th>
                        <?php _e('Select Products', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <div>
                            <select name="pw_product_depends_method" class="pw_product_depends"
                                    data-placeholder="Choose...">
                                <option value="at_least_one" <?php selected("at_least_one", $pw_product_depends_method, 1); ?>><?php _e('at least one of selected', 'pw_wc_advanced_gift'); ?></option>
                                <option value="all" <?php selected("all", $pw_product_depends_method, 1); ?>><?php _e('all of selected', 'pw_wc_advanced_gift'); ?></option>
                            </select>
							<select class="wc-product-search" multiple="multiple" style="width: 50%;" name="pw_product_depends[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations">
							  <?php
								  $meta=$pw_product_depends;
								  if (is_array($meta)) {
									  foreach ($meta as $product_id) {
										  $product = wc_get_product($product_id);
										  if (is_object($product)) {
											  echo '<option value="' . esc_attr($product_id) . '"' . selected(true, true, false) . '>' . wp_kses_post($product->get_formatted_name()) . '(#'.$product_id .')' .  '</option>';
										  }
									  }
								  }
							  ?>
							</select>							
                        </div>
                    </td>
                </tr>


                <tr>
                    <th>
                        <?php _e('exclude Product Dependency', 'pw_wc_advanced_gift'); ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('if At least one of the products assigned to cart , this Rule is exclude', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="radio" name="exclude_product_depends"
                               value="no" <?php if ($exclude_product_depends != "yes") echo "checked"; ?>
                               class="exclude_product_depends"><?php _e('No', 'pw_wc_advanced_gift'); ?>					
                        <input type="radio" name="exclude_product_depends"
                               value="yes" <?php checked($exclude_product_depends, "yes"); ?> class="exclude_product_depends"><?php _e('Yes', 'pw_wc_advanced_gift'); ?>

                    </td>
                </tr>

                <tr class="pw_exclude_product_depends">
                    <th>
                        <?php _e('Select exclude Products', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <div>
							<select class="wc-product-search" multiple="multiple" style="width: 50%;" name="pw_exclude_product_depends[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations">
							  <?php
								  $meta=$pw_exclude_product_depends;
                                  if (is_array($meta)) {
                                      foreach ($meta as $product_id) {
                                          $product = wc_get_product($product_id);
                                          if (is_object($product)) {
                                              echo '<option value="' . esc_attr($product_id) . '"' . selected(true, true, false) . '>' . wp_kses_post($product->get_formatted_name()) . '(#'.$product_id .')' .  '</option>';
                                          }
                                      }
                                  }
							  ?>
							</select>						
                        </div>
                    </td>
                </tr>


                <tr>
                    <th>
                        <?php _e('Category Dependency', 'pw_wc_advanced_gift'); ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e(' At least one of the products assigned to category from list must be included in cart ', 'pw_wc_advanced_gift'); ?></span>
						</div>	
						
                    </th>
                    <td>
                        <input type="radio" name="category_depends"
                               value="no" <?php if ($category_depends != "yes") echo "checked"; ?>
                               class="category_depends"><?php _e('No', 'pw_wc_advanced_gift'); ?>					
                        <input type="radio" name="category_depends"
                               value="yes" <?php checked($category_depends, "yes"); ?> class="category_depends"><?php _e('Yes', 'pw_wc_advanced_gift'); ?>

                    </td>
                </tr>

                <tr class="pw_category_depends">
                    <th>
                        <?php _e('Selecte Category', 'pw_wc_advanced_gift') ?>						
                    </th>
                    <td>
                        <div>
                            <select name="pw_category_depends_method" class="pw_category_depends"
                                    data-placeholder="Choose...">
                                <option value="at_least_one" <?php selected("at_least_one", $pw_category_depends_method, 1); ?>><?php _e('at least one of selected', 'pw_wc_advanced_gift'); ?></option>
                                <option value="all" <?php selected("all", $pw_category_depends_method, 1); ?>><?php _e('all of selected', 'pw_wc_advanced_gift'); ?></option>
                            </select>
                            <select name="pw_category_depends[]" class="chosen-select" multiple="multiple"
                                    data-placeholder="<?php _e('Choose Category', 'pw_wc_advanced_gift') ?> ...">
                                <?php
                                $param_line = "";
                                foreach ($categories as $category) {
                                    $selected = '';
                                    $meta = $pw_category_depends;
                                    if (is_array($meta))
                                        $selected = (in_array($category->term_id, $meta) ? "SELECTED" : "");

                                    $option = '<option value="' . $category->term_id . '" ' . $selected . '>';
                                    $option .= $category->name;
                                    $option .= ' (' . $category->count . ')';
                                    $option .= '</option>';
                                    $param_line .= $option;
                                }
                                $param_line .= '</select>';
                                echo $param_line;
                                ?>
                            </select>
                        </div>
                    </td>
                </tr>
				
                <tr>
                    <th>
                        <?php _e('Exclude Category Dependency', 'pw_wc_advanced_gift'); ?>
                    </th>
                    <td>
                        <input type="radio" name="exclude_category_depends"
                               value="no" <?php if ($exclude_category_depends != "yes") echo "checked"; ?>
                               class="exclude_category_depends"><?php _e('No', 'pw_wc_advanced_gift'); ?>					
                        <input type="radio" name="exclude_category_depends"
                               value="yes" <?php checked($exclude_category_depends, "yes"); ?> class="exclude_category_depends"><?php _e('Yes', 'pw_wc_advanced_gift'); ?>

                    </td>
                </tr>
				<tr class="exclude_pw_category_depends">
                    <th>
                        <?php _e('Selecte Exclude Category', 'pw_wc_advanced_gift') ?>						
                    </th>
                    <td>
                        <div>
                            <select name="exclude_pw_category_depends[]" class="chosen-select" multiple="multiple"
                                    data-placeholder="<?php _e('Choose Exclude Category', 'pw_wc_advanced_gift') ?> ...">
                                <?php
                                $param_line = "";
                                foreach ($categories as $category) {
                                    $selected = '';
                                    $meta = $exclude_pw_category_depends;
                                    if (is_array($meta))
                                        $selected = (in_array($category->term_id, $meta) ? "SELECTED" : "");

                                    $option = '<option value="' . $category->term_id . '" ' . $selected . '>';
                                    $option .= $category->name;
                                    $option .= ' (' . $category->count . ')';
                                    $option .= '</option>';
                                    $param_line .= $option;
                                }
                                $param_line .= '</select>';
                                echo $param_line;
                                ?>
                            </select>
                        </div>
                    </td>
                </tr>

                <?php
                if (defined('plugin_dir_url_pw_woo_brand')) {
                    ?>
                    <tr>
                        <th>
                            <?php _e('Brands Dependency', 'pw_wc_advanced_gift'); ?>
							<div class="pw-help-icon tooltip">?
							  <span class="tooltiptext"><?php _e(' At least one of the products assigned to brand_depends from list must be included in cart ', 'pw_wc_advanced_gift'); ?></span>
							</div>								
                        </th>
                        <td>
                            <input type="radio" name="brand_depends"
                                   value="no" <?php if ($brand_depends != "yes") echo "checked"; ?>
                                   class="brand_depends"><?php _e('No', 'pw_wc_advanced_gift'); ?>						
                            <input type="radio" name="brand_depends"
                                   value="yes" <?php checked($brand_depends, "yes"); ?>
                                   class="brand_depends"><?php _e('Yes', 'pw_wc_advanced_gift'); ?>

                        </td>
                    </tr>
                    <tr class="pw_brand_depends">
                        <th>
                            <?php _e('Selecte Brands', 'pw_wc_advanced_gift') ?>
                        </th>
                        <td>
                            <div>
                                <select name="pw_brand_depends_method" class="pw_brand_depends"
                                        data-placeholder="Choose...">
                                    <option value="at_least_one" <?php selected("at_least_one", $pw_brand_depends_method, 1); ?>><?php _e('at least one of selected', 'pw_wc_advanced_gift'); ?></option>
                                    <option value="all" <?php selected("all", $pw_brand_depends_method, 1); ?>><?php _e('all of selected', 'pw_wc_advanced_gift'); ?></option>
                                </select>
                                <select name="pw_brand_depends[]" class="chosen-select" multiple="multiple"
                                        data-placeholder="<?php _e('Choose Brands', 'pw_wc_advanced_gift') ?> ...">
                                    <?php

                                    $args = array('hide_empty=0');

                                    $brands = get_terms('product_brand', $args);

                                    $param_line = "";
                                    foreach ($brands as $brand) {
                                        $selected = '';
                                        $meta = $pw_brand_depends;
                                        if (is_array($meta))
                                            $selected = (in_array($brand->term_id, $meta) ? "SELECTED" : "");

                                        $option = '<option value="' . $brand->term_id . '" ' . $selected . '>';
                                        $option .= $brand->name;
                                        $option .= ' (' . $brand->count . ')';
                                        $option .= '</option>';
                                        $param_line .= $option;
                                    }
                                    $param_line .= '</select>';
                                    echo $param_line;
                                    ?>
                                </select>
                            </div>
                        </td>
                    </tr>

                <?php } else {
                    ?>
                    <tr>
                        <th>
                            <?php _e('Brands Dependency', 'pw_wc_advanced_gift'); ?>
                        </th>
                        <td>
                            <?php _e('Please BUY/activated woocommerce brand', 'pw_wc_advanced_gift'); ?> <a
                                    href="http://codecanyon.net/item/woocommerce-brands/8039481?ref=proword">Click for
                                Buy</a>
                        </td>
                    </tr>
                    <?php
                }
                ?>

                <tr>
                    <th>
                        <?php _e('Users Dependency', 'pw_wc_advanced_gift'); ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('Which users can select gifts', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="radio" name="users_depends"
                               value="no" <?php if ($users_depends != "yes") echo "checked"; ?> class="users_depends"><?php _e('No', 'pw_wc_advanced_gift'); ?>					
                        <input type="radio" name="users_depends" value="yes" <?php checked($users_depends, "yes"); ?>
                               class="users_depends"><?php _e('Yes', 'pw_wc_advanced_gift'); ?>

                    </td>
                </tr>

                <tr class="pw_users_depends">
                    <th>
                        <?php _e('Users', 'pw_wc_advanced_gift'); ?>

                    </th>
                    <td>
                        <?php
                        echo '<select name="pw_users[]" class="chosen-select" multiple="multiple" data-placeholder="Choose Users">';
                        $blogusers = get_users(array('fields' => array('ID', 'user_email')));
                        foreach ($blogusers as $user) {
                            $meta = $pw_users;
                            $selected = "";
                            if (is_array($meta)) {
                                $selected = (in_array($user->ID, $meta) ? "SELECTED" : "");
                            }
                            echo '<option ' . $selected . ' value="' . $user->ID . '">ID:' . $user->ID . ' ' . $user->user_email . '</option>';
                        }
                        echo '</select>';
                        /*					echo '<select name="pw_users[]" class="chosen-select" multiple="multiple" data-placeholder="Choose Users">';
                                            foreach(get_users() as $user) {
                                                $meta=$pw_users;
                                                $selected="";
                                                if(is_array($meta))
                                                {
                                                    $selected=(in_array($user->ID ,$meta) ? "SELECTED":"");
                                                }
                                                echo '<option '.$selected.' value="'. $user->ID .'">ID:'.$user->ID.' '.$user->user_email.'</option>';
                                            }
                                            echo '</select>';
                        */
                        ?>
                    </td>
                </tr>

                <tr>
                    <th>
                        <?php _e('Roles Dependency', 'pw_wc_advanced_gift'); ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('Which roles can select gifts', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="radio" name="roles_depends"
                               value="no" <?php if ($roles_depends != "yes") echo "checked"; ?> class="roles_depends"><?php _e('No', 'pw_wc_advanced_gift'); ?>					
                        <input type="radio" name="roles_depends" value="yes" <?php checked($roles_depends, "yes"); ?>
                               class="roles_depends"><?php _e('Yes', 'pw_wc_advanced_gift'); ?>
                    </td>
                </tr>
                <tr class="pw_roles_depends">
                    <th>
                        <?php _e('Roles', 'pw_wc_advanced_gift'); ?>
                    </th>
                    <td>
                        <?php
                        //For Create
                        if (!isset($wp_roles)) {
                            $wp_roles = new WP_Roles();
                        }
                        $all_roles = $wp_roles->roles;
                        $chunks = array_chunk($all_roles, ceil(count($all_roles) / 3), true);
                        echo '<select name="pw_roles[]" class="chosen-select" multiple="multiple" data-placeholder="Choose Roles">';
                        foreach ($chunks as $chunk) :
                            foreach ($chunk as $role_id => $role) :
                                $selected = "";
                                $meta = $pw_roles;
                                if (is_array($meta)) {
                                    $selected = (in_array($role_id, $meta) ? "SELECTED" : "");
                                }
                                echo '<option ' . $selected . ' value="' . $role_id . '">' . $role['name'] . '</option>';
                            endforeach;
                        endforeach;
                        echo '</select>';
                        ?>
                    </td>
                </tr>
              <tr>
                    <th>
                        <?php _e('exclude Roles Dependency', 'pw_wc_advanced_gift'); ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e("Which roles can't select gifts", 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="radio" name="exclude_roles_depends"
                               value="no" <?php if ($exclude_roles_depends != "yes") echo "checked"; ?> class="exclude_roles_depends"><?php _e('No', 'pw_wc_advanced_gift'); ?>					
                        <input type="radio" name="exclude_roles_depends" value="yes" <?php checked($exclude_roles_depends, "yes"); ?>
                               class="exclude_roles_depends"><?php _e('Yes', 'pw_wc_advanced_gift'); ?>

                    </td>
                </tr>
                <tr class="pw_exclude_roles_depends">
                    <th>
                        <?php _e('Roles', 'pw_wc_advanced_gift'); ?>
                    </th>
                    <td>
                        <?php
                        //For Create
                        if (!isset($wp_roles)) {
                            $wp_roles = new WP_Roles();
                        }
                        $all_roles = $wp_roles->roles;
                        $chunks = array_chunk($all_roles, ceil(count($all_roles) / 3), true);
                        echo '<select name="pw_exclude_roles[]" class="chosen-select" multiple="multiple" data-placeholder="Choose Roles">';
                        foreach ($chunks as $chunk) :
                            foreach ($chunk as $role_id => $role) :
                                $selected = "";
                                $meta = $pw_exclude_roles;
                                if (is_array($meta)) {
                                    $selected = (in_array($role_id, $meta) ? "SELECTED" : "");
                                }
                                echo '<option ' . $selected . ' value="' . $role_id . '">' . $role['name'] . '</option>';
                            endforeach;
                        endforeach;
                        echo '</select>';
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Disable if any coupon is applied', 'pw_wc_advanced_gift'); ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('Disable this rule if any coupon is applied in cart', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="radio" name="is_coupons"
                               value="no" <?php if ($is_coupons != "yes") echo "checked"; ?>><?php _e('No', 'pw_wc_advanced_gift'); ?>					
                        <input type="radio" name="is_coupons"
                               value="yes" <?php checked($is_coupons, "yes"); ?>><?php _e('Yes', 'pw_wc_advanced_gift'); ?>

                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('User Orders count completed', 'pw_wc_advanced_gift') ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('Enter number of orders', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <div>
                            <select name="order_op_count" class="pw_gifts_metod"
                                    data-placeholder="Choose...">
                                <option value=">" <?php selected(">", $order_op_count, 1); ?>><?php _e('is greater than', 'pw_wc_advanced_gift'); ?></option>
                                <option value="<" <?php selected("<", $order_op_count, 1); ?>><?php _e('is less than', 'pw_wc_advanced_gift'); ?></option>
                                <option value="==" <?php selected("==", $order_op_count, 1); ?>><?php _e('is equal to', 'pw_wc_advanced_gift'); ?></option>
                            </select>
                            <input type="text" name="order_count"
                                   value="<?php echo $order_count; ?>">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Subtotal cart', 'pw_wc_advanced_gift') ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('Subtotal cart to offer the gifts , if you set min/max please use `:` for example 10:30', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <div>
                            <select name="cart_amount_op" class="pw_gifts_metod cart_amount_op_class"
                                    data-placeholder="Choose...">
                                <option value=">" <?php selected(">", $cart_amount_op, 1); ?>><?php _e('is greater than', 'pw_wc_advanced_gift'); ?></option>
                                <option value="<" <?php selected("<", $cart_amount_op, 1); ?>><?php _e('is less than', 'pw_wc_advanced_gift'); ?></option>
                                <option value="==" <?php selected("==", $cart_amount_op, 1); ?>><?php _e('is equal to', 'pw_wc_advanced_gift'); ?></option>
                                <option value="min_max" <?php selected("min_max", $cart_amount_op, 1); ?>><?php _e('From / To', 'pw_wc_advanced_gift'); ?></option>
                            </select>
							
                            <input type="text" name="pw_cart_amount" value="<?php echo $pw_cart_amount; ?>" class="cart_amount_op_value">
                            <div class="min_max_dep">
								<label><?php _e('From', 'pw_wc_advanced_gift'); ?></label><input type="text" name="pw_cart_amount_min" value="<?php echo $pw_cart_amount_min; ?>">
								<label><?php _e('To', 'pw_wc_advanced_gift'); ?></label><input type="text" name="pw_cart_amount_max" value="<?php echo $pw_cart_amount_max; ?>">
							</div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>
                        <?php _e('Sum of item quantities', 'pw_wc_advanced_gift') ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('Enter Sum of item quantities products in cart', 'pw_wc_advanced_gift'); ?></span>
						</div>	 						
                    </th>
                    <td>
                        <div>
                            <select name="criteria_nb_products_op" class="pw_gifts_metod criteria_nb_products_op"
                                    data-placeholder="Choose...">
                                <option value=">" <?php selected(">", $criteria_nb_products_op, 1); ?>><?php _e('is greater than', 'pw_wc_advanced_gift'); ?></option>
                                <option value="<" <?php selected("<", $criteria_nb_products_op, 1); ?>><?php _e('is less than', 'pw_wc_advanced_gift'); ?></option>
                                <option value="==" <?php selected("==", $criteria_nb_products_op, 1); ?>><?php _e('is equal to', 'pw_wc_advanced_gift'); ?></option>
								<!-- <option value="min_max" <?php selected("min_max", $criteria_nb_products_op, 1); ?>><?php _e('From / To', 'pw_wc_advanced_gift'); ?></option> -->
                            </select>
                            <input type="text" name="criteria_nb_products" value="<?php echo $criteria_nb_products; ?>" class="criteria_nb_products_value">
							<div class="min_max_criteria_nb_products_dep">
								<label><?php _e('From', 'pw_wc_advanced_gift'); ?></label><input type="text" name="criteria_nb_products_min" value="<?php echo $criteria_nb_products_min; ?>">
								<label><?php _e('To', 'pw_wc_advanced_gift'); ?></label><input type="text" name="criteria_nb_products_max" value="<?php echo $criteria_nb_products_max; ?>">
							</div>				
                        </div>
                    </td>
                </tr>
				<?php 
				do_action('custom_gift_field_after_sum_of_quantities');
				?>					
                <tr class="repeat_class">
                    <th>
						
                        <?php _e('Enable Repeat For', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>

                        <div>
							<input type="radio" name="repeat" class="repeat_none"
								   tabindex="0" checked="checked"
								   value="none"><label class="none"><?php _e('None', 'pw_wc_advanced_gift'); ?></label>	
							<input type="radio" name="repeat" class="repeat_sub"
								   tabindex="0" <?php echo (@$repeat=="repeat_sub") ? 'checked="checked"' : ''; ?>
								   value="repeat_sub"><label class="repeat_sub"><?php _e('Subtotal cart', 'pw_wc_advanced_gift'); ?></label>
							<input type="radio" name="repeat" class="repeat_qty"
								   tabindex="0" <?php echo (@$repeat=="repeat_qty") ? 'checked="checked"' : ''; ?>
								   value="repeat_qty"><label class="repeat_qty"><?php _e('Sum of item quantities', 'pw_wc_advanced_gift'); ?></label>
                        </div>
                    </td>
                </tr>
				

				

				
				<?php 
				do_action('custom_gift_field_after_order_op_count');
				?>

                <tr class="pw_dependency_special_date">
                    <th>
					
                        <?php _e('valid Rule Date from', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="text" autocomplete="off" id="date_timepicker_from" name="pw_from" value="<?php echo $pw_from; ?>">
                        <script type="text/javascript">
                            jQuery(function () {
                                jQuery('#date_timepicker_from').datetimepicker();
                            });
                        </script>
                    </td>
                </tr>
                <tr class="pw_dependency_special_date">
                    <th>
                        <?php _e('valid Rule Date to', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="text" autocomplete="off" id="date_timepicker_to" name="pw_to" value="<?php echo $pw_to; ?>">
                        <script type="text/javascript">
                            jQuery(function () {
                                jQuery('#date_timepicker_to').datetimepicker();
                            });
                        </script>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Auto Add Gifts To cart', 'pw_wc_advanced_gift'); ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('If activated, the gift will be added to cart Automatically', 'pw_wc_advanced_gift'); ?></span>
						</div>	                        
                    </th>
                    <td>
                        <input type="radio" name="gift_auto_to_cart" class="auto_add_gift"
                               value="no" <?php if ($gift_auto_to_cart != "yes") echo "checked"; ?>><?php _e('No', 'pw_wc_advanced_gift'); ?>					
                        <input type="radio" name="gift_auto_to_cart" class="auto_add_gift"
                               value="yes" <?php checked($gift_auto_to_cart, "yes"); ?>><?php _e('Yes (Gifts will be hidden)', 'pw_wc_advanced_gift'); ?>
                    </td>
                </tr>
                <tr class="diable_gift_in_cart">
                    <th>
                        <?php _e('if any gift added to cart', 'pw_wc_advanced_gift'); ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('Disable Gifts if any gift was to cart', 'pw_wc_advanced_gift'); ?></span>
						</div>								
                    </th>
                    <td>
                        <select name="disable_if" class=""
                                data-placeholder="Choose...">
                            <option value="show" <?php selected("show", $disable_if, 1); ?>><?php _e('Show all gifts this rule', 'pw_wc_advanced_gift'); ?></option>
                            <option value="disable" <?php selected("disable", $disable_if, 1); ?>><?php _e('Disable Gifts this rule', 'pw_wc_advanced_gift'); ?></option>
                            <option value="hide" <?php selected("hide", $disable_if, 1); ?>><?php _e('Hide Gifts this rule', 'pw_wc_advanced_gift'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th> <?php _e('Schedule Type', 'pw_wc_advanced_gift'); ?></th>
                    <td>
                        <select name="schedule_type" class="pw_schedule_type"
                                data-placeholder="Choose...">
                            <option value="unlimited" <?php selected("unlimited", $schedule_type, 1); ?>><?php _e('All Of the Time', 'pw_wc_advanced_gift'); ?></option>
                            <option value="daily" <?php selected("daily", $schedule_type, 1); ?>><?php _e('Daily', 'pw_wc_advanced_gift'); ?></option>
                            <option value="weekly" <?php selected("weekly", $schedule_type, 1); ?>><?php _e('Weekly', 'pw_wc_advanced_gift'); ?></option>
                            <option value="monthly"
                                <?php selected("monthly", $schedule_type, 1); ?>>
                                <?php _e('Day Of Month', 'pw_wc_advanced_gift'); ?></option>
                            <!--                            <option value="special_date" -->
                            <?php //selected("special_date", $schedule_type, 1); ?><!-->-->
                            <?php //_e('Specific Date', 'pw_wc_advanced_gift'); ?><!--</option>-->
                        </select>
                    </td>
                </tr>
                </tr>

                <tr class="pw_dependency_weekly">
                    <th></th>
                    <td>
                        <div class="pw-sch-fields-cnt">
                            <div class="pw-field">

                                <input type="checkbox" name="pw_weekly[]" class=""
                                       tabindex="0" <?php echo (@in_array('Monday', $pw_weekly)) ? 'checked="checked"' : ''; ?>
                                       value="Monday">
                                <label><?php _e('Monday', 'pw_wc_advanced_gift'); ?></label>

                            </div>
                            <div class="pw-field">
                                <input type="checkbox" name="pw_weekly[]" class=""
                                    <?php echo (@in_array('Tuesday', $pw_weekly)) ? 'checked="checked"' : ''; ?>
                                       value="Tuesday"
                                       tabindex="0">
                                <label><?php _e('Tuesday', 'pw_wc_advanced_gift'); ?></label>
                            </div>
                            <div class="pw-field">
                                <input type="checkbox" name="pw_weekly[]"
                                       class="" <?php echo (@in_array('Wednesday', $pw_weekly)) ? 'checked="checked"' : ''; ?>
                                       value="Wednesday"
                                       tabindex="0">
                                <label><?php _e('Wednesday', 'pw_wc_advanced_gift'); ?></label>
                            </div>
                            <div class="pw-field">
                                <input type="checkbox" name="pw_weekly[]"
                                       class="" <?php echo (@in_array('Thursday', $pw_weekly)) ? 'checked="checked"' : ''; ?>
                                       value="Thursday"
                                       tabindex="0">
                                <label><?php _e('Thursday', 'pw_wc_advanced_gift'); ?></label>
                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_weekly[]"
                                       class="" <?php echo (@in_array('Friday', $pw_weekly)) ? 'checked="checked"' : ''; ?>
                                       value="Friday"
                                       tabindex="0">
                                <label><?php _e('Friday', 'pw_wc_advanced_gift'); ?></label>

                            </div>
                            <div class="pw-field">
                                <input type="checkbox" name="pw_weekly[]"
                                       class="" <?php echo (@in_array('Saturday', $pw_weekly)) ? 'checked="checked"' : ''; ?>
                                       value="Saturday"
                                       tabindex="0">
                                <label><?php _e('Saturday', 'pw_wc_advanced_gift'); ?></label>
                            </div>
                            <div class="pw-field">
                                <input type="checkbox" name="pw_weekly[]"
                                       class="" <?php echo (@in_array('Sunday', $pw_weekly)) ? 'checked="checked"' : ''; ?>
                                       value="Sunday"
                                       tabindex="0">
                                <label><?php _e('Sunday', 'pw_wc_advanced_gift'); ?></label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="pw_dependency_daily">
                    <th></th>
                    <td>

                        <div class="pw-sch-fields-cnt">
                            <div class="pw-field">
                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('1', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="1"
                                       tabindex="0">
                                <label>01</label>
                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('2', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="2"
                                       tabindex="0">
                                <label>02</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('3', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="3"
                                       tabindex="0">
                                <label>03</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('4', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="4"
                                       tabindex="0">
                                <label>04</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('5', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="5"
                                       tabindex="0">
                                <label>05</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('6', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="6"
                                       tabindex="0">
                                <label>06</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('7', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="7"
                                       tabindex="0">
                                <label>07</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('8', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="8"
                                       tabindex="0">
                                <label>08</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('9', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="9"
                                       tabindex="0">
                                <label>09</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('10', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="10"
                                       tabindex="0">
                                <label>10</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('11', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="11"
                                       tabindex="0">
                                <label>11</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('12', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="12"
                                       tabindex="0">
                                <label>12</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('13', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="13"
                                       tabindex="0">
                                <label>13</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('14', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="14"
                                       tabindex="0">
                                <label>14</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('15', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="15"
                                       tabindex="0">
                                <label>15</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('16', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="16"
                                       tabindex="0">
                                <label>16</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('17', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="17"
                                       tabindex="0">
                                <label>17</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('18', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="18"
                                       tabindex="0">
                                <label>18</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('19', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="19"
                                       tabindex="0">
                                <label>19</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('20', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="20"
                                       tabindex="0">
                                <label>20</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('21', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="21"
                                       tabindex="0">
                                <label>21</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('22', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="22"
                                       tabindex="0">
                                <label>22</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('23', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="23"
                                       tabindex="0">
                                <label>23</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('24', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="24"
                                       tabindex="0">
                                <label>24</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('25', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="25"
                                       tabindex="0">
                                <label>25</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('26', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="26"
                                       tabindex="0">
                                <label>26</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('27', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="27"
                                       tabindex="0">
                                <label>27</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('28', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="28"
                                       tabindex="0">
                                <label>28</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('29', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="29"
                                       tabindex="0">
                                <label>29</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('30', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="30"
                                       tabindex="0">
                                <label>30</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]"
                                       class="" <?php echo (@in_array('31', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="31"
                                       tabindex="0">
                                <label>31</label>

                            </div>
                            <div class="pw-field">

                                <input type="checkbox" name="pw_daily[]" class=""
                                    <?php echo (@in_array('last', $pw_daily)) ? 'checked="checked"' : ''; ?>
                                       value="last"
                                       tabindex="0">
                                <label><?php _e('last', 'pw_wc_advanced_gift'); ?></label>

                            </div>

                        </div>

                    </td>
                </tr>
                <tr class="pw_dependency_monthly">
                    <th></th>
                    <td>
                        <div class="pw-each-fields-cnt">
                            <div class="pw-field">
                                <label><?php _e('Each', 'pw_wc_advanced_gift'); ?>:</label>
                                <select class="pwui small dropdown" name="pw_monthly[each]">
                                    <option <?php @selected("first", $pw_monthly['each'], 1); ?> value="first">
                                        <?php _e('First', 'pw_wc_advanced_gift'); ?>
                                    </option>
                                    <option <?php @selected("second", $pw_monthly['each'], 1); ?> value="second">
                                        <?php _e('Second', 'pw_wc_advanced_gift'); ?>
                                    </option>
                                    <option <?php @selected("third", $pw_monthly['each'], 1); ?> value="third">
                                        <?php _e('Third', 'pw_wc_advanced_gift'); ?>
                                    </option>
                                    <option <?php @selected("fourth", $pw_monthly['each'], 1); ?> value="fourth">
                                        <?php _e('Fourth', 'pw_wc_advanced_gift'); ?>
                                    </option>
                                    <option <?php @selected("last", $pw_monthly['each'], 1); ?> value="last">
                                        <?php _e('Last', 'pw_wc_advanced_gift'); ?>
                                    </option>
                                </select>
                            </div>
                            <div class="pw-field">
                                <select class="pwui small dropdown"
                                        name="pw_monthly[day]">
                                    <option <?php @selected("monday", $pw_monthly['day'], 1); ?> value="monday">
                                        <?php _e('Monday', 'pw_wc_advanced_gift'); ?>
                                    </option>
                                    <option <?php @selected("tuesday", $pw_monthly['day'], 1); ?> value="tuesday">
                                        <?php _e('Tuesday', 'pw_wc_advanced_gift'); ?>
                                    </option>
                                    <option <?php @selected("wednesday", $pw_monthly['day'], 1); ?>
                                            value="wednesday">
                                        <?php _e('Wednesday', 'pw_wc_advanced_gift'); ?>
                                    </option>
                                    <option <?php @selected("thursday", $pw_monthly['day'], 1); ?> value="thursday">
                                        <?php _e('Thursday', 'pw_wc_advanced_gift'); ?>
                                    </option>
                                    <option <?php @selected("friday", $pw_monthly['day'], 1); ?> value="friday">
                                        <?php _e('Friday', 'pw_wc_advanced_gift'); ?>
                                    </option>
                                    <option <?php @selected("saturday", $pw_monthly['day'], 1); ?> value="saturday">
                                        <?php _e('Saturday', 'pw_wc_advanced_gift'); ?>
                                    </option>
                                    <option <?php @selected("sunday", $pw_monthly['day'], 1); ?> value="sunday">
                                        <?php _e('Sunday', 'pw_wc_advanced_gift'); ?>
                                    </option>
                                </select>
                                <label><?php _e('Of The Month', 'pw_wc_advanced_gift'); ?></label>
                            </div>
                        </div>
                    </td>
                </tr>
				
				<tr>
                    <th>
                        <?php _e('Show Gifts in Single product page', 'pw_wc_advanced_gift'); ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('If activated, the gift will be displayed in the product page', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="radio" name="gift_preselector_product_page"
                               value="no" <?php if ($gift_preselector_product_page != "yes") echo "checked"; ?>><?php _e('No', 'pw_wc_advanced_gift'); ?>
                        <input type="radio" name="gift_preselector_product_page"
                               value="yes" <?php checked($gift_preselector_product_page, "yes"); ?>><?php _e('Yes', 'pw_wc_advanced_gift'); ?>
                    </td>
                </tr>
				<!-- -->
                <tr>
                    <th>

                        &nbsp;
                    </th>
                    <td>
                        <input type="submit" value="<?php _e('Save Changes', 'pw_wc_advanced_gift') ?>"
                               class="btn button-primary">
                        <input type="hidden" name="pw_action_type" id="pw_action_type"
                               value="<?php echo $pw_action_type; ?>"/>
                    </td>
                </tr>

                </tbody>
            </table>

            <div class="pw-space"></div>
        </div>
    </div>
</form>
<script language="javascript">
    jQuery(document).ready(function (e) {
        jQuery('.chosen-select').chosen();
        jQuery('.pw_product_depends').dependsOn({
            '.product_depends': {
                values: ['yes']
            }
        });
        jQuery('.pw_exclude_product_depends').dependsOn({
            '.exclude_product_depends': {
                values: ['yes']
            }
        });
        jQuery('.pw_category_depends').dependsOn({
            '.category_depends': {
                values: ['yes']
            }
        });        
		jQuery('.exclude_pw_category_depends').dependsOn({
            '.exclude_category_depends': {
                values: ['yes']
            }
        });
        jQuery('.pw_gifts_metod_category').dependsOn({
            '.pw_gifts_metod': {
                values: ['category']
            }
        });
        jQuery('.pw_gifts_metod_product').dependsOn({
            '.pw_gifts_metod': {
                values: ['product']
            }
        });
        jQuery('.pw_users_depends').dependsOn({
            '.users_depends': {
                values: ['yes']
            }
        })
        jQuery('.pw_roles_depends').dependsOn({
            '.roles_depends': {
                values: ['yes']
            }
        });
		jQuery('.pw_exclude_roles_depends').dependsOn({
            '.exclude_roles_depends': {
                values: ['yes']
            }
        });
        jQuery('.pw_gifts_limit_user').dependsOn({
            '.pw_register_user': {
                values: ['yes']
            }
        });        
		jQuery('.diable_gift_in_cart').dependsOn({
            '.auto_add_gift': {
                values: ['no']
            }
        });
//        jQuery('.pw_dependency_special_date').dependsOn({
//            '.pw_schedule_type': {
//                values: ['special_date']
//            }
//        });
//        jQuery('.can_several_gift_tr').dependsOn({
//            '.pw_number_gift_allowed': {
//                range: ['2', '1000', '1']
//            }
//        });

        jQuery('.pw_dependency_weekly').dependsOn({
            '.pw_schedule_type': {
                values: ['weekly']
            }
        });
        
		
		jQuery('.min_max_dep').dependsOn({
            '.cart_amount_op_class': {
                values: ['min_max']
            }
        });	
		
		jQuery('.cart_amount_op_value').dependsOn({
            '.cart_amount_op_class': {
                values: ['>','<','==']
            }
        });		
		jQuery('.min_max_criteria_nb_products_dep').dependsOn({
            '.criteria_nb_products_op': {
                values: ['min_max']
            }
        });			
		jQuery('.criteria_nb_products_value').dependsOn({
            '.criteria_nb_products_op': {
                values: ['>','<','==']
            }
        });			
		
		jQuery('.repeat_qty').dependsOn({
            '.criteria_nb_products_op': {
                values: ['==']
            }
        });				
		
		jQuery('.repeat_sub').dependsOn({
            '.cart_amount_op_class': {
                values: ['==']
            }
        });		
		
		
		jQuery('.repeat_class').dependsOn({
            '.cart_amount_op_class': {
                values: ['==']
            }		
        }).or({
            '.criteria_nb_products_op': {
                values: ['==']
            },				
		});
		
        jQuery('.pw_dependency_daily').dependsOn({
            '.pw_schedule_type': {
                values: ['daily']
            }
        });
        jQuery('.pw_dependency_monthly').dependsOn({
            '.pw_schedule_type': {
                values: ['monthly']
            }
        });


        <?php
        if(defined('plugin_dir_url_pw_woo_brand')){
        ?>
        jQuery('.pw_brand_depends').dependsOn({
            '.brand_depends': {
                values: ['yes']
            }
        });
        <?php }?>
        //var a = jQuery('.pw_number_gift_allowed').val();
        //if (parseInt(a) > 1) {
            //jQuery('.can_several_gift_tr').show();
        //}
        //else {
            //jQuery('.can_several_gift_tr').hide();
        //}
        //jQuery(".pw_number_gift_allowed").keyup(function () {
            //if (parseInt(this.value) > 1) {
                //jQuery('.can_several_gift_tr').show();
            //}
            //else {
                //jQuery('.can_several_gift_tr').hide();
//            }
        //})

    });
</script>