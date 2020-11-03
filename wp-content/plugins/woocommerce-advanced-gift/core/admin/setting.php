<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}


if (isset($_POST['pw_gift_options'])) {
    update_option('pw_gift_options', $_POST['pw_gift_options']);
}
$setting = get_option("pw_gift_options");
//print_r($setting);
?>
<form action="" method="POST">
    <div class="pw-form-cnt">

        <div class="pw-form-content">
            <div class="pw-section-title"><?php _e('General Setting', 'pw_wc_advanced_gift'); ?></div>
            <table class="pw-form-table">
                <tbody>
                <tr>
                    <th>
                        <?php _e('Limit Gift In Any Order', 'pw_wc_advanced_gift') ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('Select How Many gift, user can select In Any Order.', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="radio" name="pw_gift_options[multiselect]"
                               value="yes" <?php checked($setting['multiselect'], "yes"); ?> class="multiple_depends"><?php _e('Limit', 'pw_wc_advanced_gift'); ?>
                        <input type="radio" name="pw_gift_options[multiselect]"
                               value="no" <?php if ($setting['multiselect'] != "yes") echo "checked"; ?>
                               class="multiple_depends"><?php _e('Based on Rules', 'pw_wc_advanced_gift'); ?>
                    </td>
                </tr>

                <tr class="pw_multiple_depends">
                    <th>
                        <?php _e('Minimum cart amount for multiple', 'pw_wc_advanced_gift') ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('Minimum cart amount to offer the gifts.', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="text" name="pw_gift_options[multiselect_cart_amount]" style="width: 100px;"
                               value="<?php echo $setting['multiselect_cart_amount']; ?>"/>
                        <?php
                        echo get_woocommerce_currency_symbol();
                        ?>
                    </td>
                </tr>

                <tr class="pw_multiple_depends">
                    <th>
                        <?php _e('Number of product gifts', 'pw_wc_advanced_gift') ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('please enter total number of multiple gifts that can be selected.', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="number" name="pw_gift_options[multiselect_gift_count]" style="width: 70px;"
                               value="<?php echo $setting['multiselect_gift_count']; ?>"/>
                    </td>
                </tr>
                </tbody>
            </table>

            <div class="pw-space"></div>
            <div class="pw-section-title"><?php _e('Popup Settings', 'pw_wc_advanced_gift') ?></div>
            <table class="pw-form-table">
                <tbody>
                <tr>
                    <th>
                        <?php _e('Show Popup every', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
						<?php 
							$expire=60;
							$expire=isset($setting['expire']) ? $setting['expire'] : 60;?>
                        <input type="number" name="pw_gift_options[expire]" style="width: 70px;"
                               value="<?php echo $expire; ?>"/> <?php _e('Minutes','pw_wc_advanced_gift');?>
                    </td>
                </tr>				
                <tr>
                    <th>
                        <?php _e('Show Popup in All Device', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <select name="pw_gift_options[show_popup]">
                            <option value="true" <?php selected($setting['show_popup'], 'true', 1) ?>><?php _e('Yes', 'pw_wc_advanced_gift'); ?></option>
                            <option value="false" <?php selected($setting['show_popup'], 'false', 1) ?>><?php _e('No', 'pw_wc_advanced_gift'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>
                        <?php _e('Hide Popup in Mobie', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <select name="pw_gift_options[hide_popup_in_mobile]">
                            <option value="true" <?php selected(@$setting['hide_popup_in_mobile'], 'true', 1) ?>><?php _e('Yes', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="false" <?php selected(@$setting['hide_popup_in_mobile'], 'false', 1) ?>><?php _e('No', 'pw_wc_advanced_gift'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>
                        <?php _e('Item Per View', 'pw_wc_advanced_gift') ?>							
                    </th>
                    <td>
                        <input type="number" value="<?php echo $setting['popup_pw_item_per_view']; ?>"
                               name="pw_gift_options[popup_pw_item_per_view]" style="width: 50px;"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Item Width', 'pw_wc_advanced_gift') ?>

                    </th>
                    <td>
                        <input type="number" name="pw_gift_options[popup_pw_item_width]" style="width: 50px;"
                               value="<?php echo $setting['popup_pw_item_width']; ?>"/> px
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Item margin', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="number" value="<?php echo $setting['popup_pw_item_marrgin']; ?>"
                               name="pw_gift_options[popup_pw_item_marrgin]" style="width: 50px;"/> px
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Item Per Slide', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="number" value="<?php echo $setting['popup_pw_item_per_slide']; ?>"
                               name="pw_gift_options[popup_pw_item_per_slide]" style="width: 50px;"/>
                    </td>
                </tr>

                <tr>
                    <th>
                        <?php _e('Show Controls', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <select name="pw_gift_options[popup_pw_show_control]">
                            <option value="true" <?php selected($setting['popup_pw_show_control'], 'true', 1) ?>><?php _e('Yes', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="false" <?php selected($setting['popup_pw_show_control'], 'false', 1) ?>><?php _e('No', 'pw_wc_advanced_gift'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Show Pagination', 'pw_wc_advanced_gift') ?>					
                    </th>
                    <td>
                        <select name="pw_gift_options[popup_pw_show_pagination]">
                            <option value="true" <?php selected($setting['popup_pw_show_pagination'], 'true', 1) ?>>
                                <?php _e('Yes', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="false" <?php selected($setting['popup_pw_show_pagination'], 'false', 1) ?>>
                                <?php _e('No', 'pw_wc_advanced_gift'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>
                        <?php _e('Slide Speed', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <select name="pw_gift_options[popup_pw_slide_speed]">
                            <option value="1000" <?php selected($setting['popup_pw_slide_speed'], '1000', 1) ?>>1 <?php _e('sec', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="2000" <?php selected($setting['popup_pw_slide_speed'], '2000', 1) ?>>2 <?php _e('sec', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="3000" <?php selected($setting['popup_pw_slide_speed'], '3000', 1) ?>>3 <?php _e('sec', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="4000" <?php selected($setting['popup_pw_slide_speed'], '4000', 1) ?>>4 <?php _e('sec', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="5000" <?php selected($setting['popup_pw_slide_speed'], '5000', 1) ?>>5 <?php _e('sec', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="6000" <?php selected($setting['popup_pw_slide_speed'], '6000', 1) ?>>6 <?php _e('sec', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="7000" <?php selected($setting['popup_pw_slide_speed'], '7000', 1) ?>>7 <?php _e('sec', 'pw_wc_advanced_gift'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Auto Play', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <select name="pw_gift_options[popup_pw_auto_play]">
                            <option value="true" <?php selected($setting['popup_pw_auto_play'], 'true', 1) ?>><?php _e('Yes', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="false" <?php selected($setting['popup_pw_auto_play'], 'false', 1) ?>><?php _e('No', 'pw_wc_advanced_gift'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>

            <div class="pw-space"></div>
            <div class="pw-section-title"><?php _e('Cart Gift Settings', 'pw_wc_advanced_gift') ?></div>
            <table class="pw-form-table">
                <tbody>
                <!-- <tr>
                    <th>
                        <?php _e('Show Popup', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <select name="pw_gift_options[show_popup]">
                            <option value="true" <?php selected($setting['show_popup'], 'true', 1) ?>>Yes</option>
                            <option value="false" <?php selected($setting['show_popup'], 'false', 1) ?>>No</option>
                        </select>
                    </td>
                </tr>-->
                <!--                <tr>-->
                <!--                    <th>-->
                <!--                        --><?php //_e('Hide if user selected gift', 'pw_wc_advanced_gift') ?>
                <!--                        <div class="pw-help-icon"-->
                <!--                             title="-->
                <?php //_e("if wasn't any gift select Hide Box Gifts", 'pw_wc_advanced_gift'); ?><!--">-->
                <!--                            ?-->
                <!--                        </div>-->
                <!--                    </th>-->
                <!--                    <td>-->
                <!--                        <input type="radio" name="pw_gift_options[hide_gifts_after_select]"-->
                <!--                               value="yes" -->
                <?php //checked($setting['hide_gifts_after_select'], "yes"); ?><!--><?php //_e('Yes', 'pw_wc_advanced_gift'); ?>
                <!--                        <input type="radio" name="pw_gift_options[hide_gifts_after_select]"-->
                <!--                               value="no" -->
                <?php //checked($setting['hide_gifts_after_select'], "no"); ?><!--><?php //_e('No', 'pw_wc_advanced_gift'); ?>
                <!--                    </td>-->
                <!--                </tr>-->
                <tr>
                    <th>
                        <?php _e('Show Stock quantity', 'pw_wc_advanced_gift'); ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('If activated, the gift products will be displayed Stock quantity in cart page', 'pw_wc_advanced_gift'); ?></span>
						</div>						
                    </th>
                    <td>
                        <input type="radio" name="pw_gift_options[show_gift_stock_qty]"
                               value="no" <?php if (@$setting['show_gift_stock_qty'] != "yes") echo "checked"; ?>><?php _e('No', 'pw_wc_advanced_gift'); ?>
                        <input type="radio" name="pw_gift_options[show_gift_stock_qty]"
                               value="yes" <?php checked(@$setting['show_gift_stock_qty'], "yes"); ?>><?php _e('Yes', 'pw_wc_advanced_gift'); ?>
                    </td>
                </tr>                
				<tr>
                    <th>
                        <?php _e('View Gifts in Cart', 'pw_wc_advanced_gift') ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('How view Gifts in cart page.', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <input type="radio" name="pw_gift_options[view_cart_gift]"
                               value="carousel" <?php checked($setting['view_cart_gift'], "carousel"); ?>
                               class="view_cart_gift_depends"><?php _e('Carousel', 'pw_wc_advanced_gift'); ?>
                        <input type="radio" name="pw_gift_options[view_cart_gift]"
                               value="grid" <?php checked($setting['view_cart_gift'], "grid"); ?>
                               class="view_cart_gift_depends"><?php _e('Grid', 'pw_wc_advanced_gift'); ?>
                    </td>
                </tr>
                <tr class="pw_view_cart_gift_grid_depends">
                    <th><label><?php _e('Desktop Columns', 'pw_wc_advanced_gift') ?></label></th>
                    <td>
                        <select name="pw_gift_options[desktop_columns]">
                            <option value="wg-col-md-12" <?php selected($setting['desktop_columns'], 'wg-col-md-12', 1) ?>>
                                1 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="wg-col-md-6" <?php selected($setting['desktop_columns'], 'wg-col-md-6', 1) ?>>
                                2 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="wg-col-md-4" <?php selected($setting['desktop_columns'], 'wg-col-md-4', 1) ?>>
                                3 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="wg-col-md-3" <?php selected($setting['desktop_columns'], 'wg-col-md-3', 1) ?>>
                                4 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="wg-col-md-2" <?php selected($setting['desktop_columns'], 'wg-col-md-2', 1) ?>>
                                6 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <tr class="pw_view_cart_gift_grid_depends">
                    <th><label><?php _e('Gift Number Per Page', 'pw_wc_advanced_gift') ?></label></th>
                    <td>
                        <select name="pw_gift_options[number_per_page]">
                            <option value="1" <?php selected($setting['number_per_page'], '1', 1) ?>>
                                1
                            </option>
                            <option value="2" <?php selected($setting['number_per_page'], '2', 1) ?>>
                                2
                            </option>
                            <option value="3" <?php selected($setting['number_per_page'], '3', 1) ?>>
                                3
                            </option>
                            <option value="4" <?php selected($setting['number_per_page'], '4', 1) ?>>
                                4
                            </option>
                            <option value="5" <?php selected($setting['number_per_page'], '5', 1) ?>>
                                5
                            </option>
                            <option value="6" <?php selected($setting['number_per_page'], '6', 1) ?>>
                                6
                            </option>	
                            <option value="7" <?php selected($setting['number_per_page'], '7', 1) ?>>
                                7
                            </option>	
                            <option value="8" <?php selected($setting['number_per_page'], '8', 1) ?>>
                                8
                            </option>	
                            <option value="9" <?php selected($setting['number_per_page'], '9', 1) ?>>
                                9
                            </option>
                            <option value="10" <?php selected($setting['number_per_page'], '10', 1) ?>>
                                10
                            </option>
                            <option value="20" <?php selected($setting['number_per_page'], '20', 1) ?>>
                                20
                            </option>
                            <option value="30" <?php selected($setting['number_per_page'], '30', 1) ?>>
                                30
                            </option>
                            <option value="40" <?php selected($setting['number_per_page'], '40', 1) ?>>
                                40
                            </option>	
                            <option value="50" <?php selected($setting['number_per_page'], '50', 1) ?>>
                                50
                            </option>							
                        </select>
                    </td>
                </tr>				
                <tr class="pw_view_cart_gift_grid_depends">
                    <th><label><?php _e('Tablet Columns', 'pw_wc_advanced_gift') ?></label></th>
                    <td>
                        <select name="pw_gift_options[tablet_columns]">
                            <option value="wg-col-sm-12" <?php selected($setting['tablet_columns'], 'wg-col-sm-12', 1) ?>>
                                1 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="wg-col-sm-6" <?php selected($setting['tablet_columns'], 'wg-col-sm-6', 1) ?>>
                                2 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="wg-col-sm-4" <?php selected($setting['tablet_columns'], 'wg-col-sm-4', 1) ?>>
                                3 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="wg-col-sm-3" <?php selected($setting['tablet_columns'], 'wg-col-sm-3', 1) ?>>
                                4 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="wg-col-sm-2" <?php selected($setting['tablet_columns'], 'wg-col-sm-2', 1) ?>>
                                6 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <tr class="pw_view_cart_gift_grid_depends">
                    <th><label><?php _e('Mobile Columns', 'pw_wc_advanced_gift') ?></label></th>
                    <td>
                        <select name="pw_gift_options[mobile_columns]">
                            <option value="wg-col-xs-12" <?php selected($setting['mobile_columns'], 'wg-col-xs-12', 1) ?>>
                                1 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="wg-col-xs-6" <?php selected($setting['mobile_columns'], 'wg-col-xs-6', 1) ?>>
                                2 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="wg-col-xs-4" <?php selected($setting['mobile_columns'], 'wg-col-xs-4', 1) ?>>
                                3 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="wg-col-xs-3" <?php selected($setting['mobile_columns'], 'wg-col-xs-3', 1) ?>>
                                4 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="wg-col-xs-2" <?php selected($setting['mobile_columns'], 'wg-col-xs-2', 1) ?>>
                                6 <?php _e('Columns', 'pw_wc_advanced_gift'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <tr class="pw_view_cart_gift_depends_depends">
                    <th>
                        <?php _e('Item Per View', 'pw_wc_advanced_gift') ?>						
                    </th>
                    <td>
                        <input type="number" value="<?php echo $setting['pw_item_per_view']; ?>"
                               name="pw_gift_options[pw_item_per_view]" style="width: 50px;"/>
                    </td>
                </tr>
                <tr class="pw_view_cart_gift_depends_depends">
                    <th>
                        <?php _e('Item Width', 'pw_wc_advanced_gift') ?>

                    </th>
                    <td>
                        <input type="number" name="pw_gift_options[pw_item_width]" style="width: 50px;"
                               value="<?php echo $setting['pw_item_width']; ?>"/> px
                    </td>
                </tr>
                <tr class="pw_view_cart_gift_depends_depends">
                    <th>
                        <?php _e('Item margin', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="number" value="<?php echo $setting['pw_item_marrgin']; ?>"
                               name="pw_gift_options[pw_item_marrgin]" style="width: 50px;"/> px
                    </td>
                </tr>
                <tr class="pw_view_cart_gift_depends_depends">
                    <th>
                        <?php _e('Item Per Slide', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="number" value="<?php echo $setting['pw_item_per_slide']; ?>"
                               name="pw_gift_options[pw_item_per_slide]" style="width: 50px;"/>
                    </td>
                </tr>

                <tr class="pw_view_cart_gift_depends_depends">
                    <th>
                        <?php _e('Show Controls', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <select name="pw_gift_options[pw_show_control]">
                            <option value="true" <?php selected($setting['pw_show_control'], 'true', 1) ?>><?php _e('Yes', 'pw_wc_advanced_gift'); ?></option>
                            <option value="false" <?php selected($setting['pw_show_control'], 'false', 1) ?>><?php _e('No', 'pw_wc_advanced_gift'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr class="pw_view_cart_gift_depends_depends">
                    <th>
                        <?php _e('Show Pagination', 'pw_wc_advanced_gift') ?>						
                    </th>
                    <td>
                        <select name="pw_gift_options[pw_show_pagination]">
                            <option value="true" <?php selected($setting['pw_show_pagination'], 'true', 1) ?>><?php _e('Yes', 'pw_wc_advanced_gift'); ?>
                            </option>
                            <option value="false" <?php selected($setting['pw_show_pagination'], 'false', 1) ?>><?php _e('No', 'pw_wc_advanced_gift'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr class="pw_view_cart_gift_depends_depends">
                    <th>
                        <?php _e('Slide Speed', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <select name="pw_gift_options[pw_slide_speed]">
                            <option value="1000" <?php selected($setting['pw_slide_speed'], '1000', 1) ?>>1 <?php _e('sec', 'pw_wc_advanced_gift'); ?></option>
                            <option value="2000" <?php selected($setting['pw_slide_speed'], '2000', 1) ?>>2 <?php _e('sec', 'pw_wc_advanced_gift'); ?></option>
                            <option value="3000" <?php selected($setting['pw_slide_speed'], '3000', 1) ?>>3 <?php _e('sec', 'pw_wc_advanced_gift'); ?></option>
                            <option value="4000" <?php selected($setting['pw_slide_speed'], '4000', 1) ?>>4 <?php _e('sec', 'pw_wc_advanced_gift'); ?></option>
                            <option value="5000" <?php selected($setting['pw_slide_speed'], '5000', 1) ?>>5 <?php _e('sec', 'pw_wc_advanced_gift'); ?></option>
                            <option value="6000" <?php selected($setting['pw_slide_speed'], '6000', 1) ?>>6 <?php _e('sec', 'pw_wc_advanced_gift'); ?></option>
                            <option value="7000" <?php selected($setting['pw_slide_speed'], '7000', 1) ?>>7 <?php _e('sec', 'pw_wc_advanced_gift'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr class="pw_view_cart_gift_depends_depends">
                    <th>
                        <?php _e('Auto Play', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <select name="pw_gift_options[pw_auto_play]">
                            <option value="true" <?php selected($setting['pw_auto_play'], 'true', 1) ?>><?php _e('Yes', 'pw_wc_advanced_gift'); ?></option>
                            <option value="false" <?php selected($setting['pw_auto_play'], 'false', 1) ?>><?php _e('No', 'pw_wc_advanced_gift'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr class="pw_view_cart_gift_depends_depends">
                    <th>
                        <?php _e('RTL Carousel', 'pw_wc_advanced_gift') ?>
						<div class="pw-help-icon tooltip">?
						  <span class="tooltiptext"><?php _e('change direction from Right to left', 'pw_wc_advanced_gift'); ?></span>
						</div>							
                    </th>
                    <td>
                        <select name="pw_gift_options[pw_slide_rtl]">
							<option value="false" <?php @selected($setting['pw_slide_rtl'], 'false', 1) ?>><?php _e('False', 'pw_wc_advanced_gift'); ?></option>
                            <option value="true" <?php @selected($setting['pw_slide_rtl'], 'true', 1) ?>><?php _e('True', 'pw_wc_advanced_gift'); ?></option>
                        </select>					
                    </td>
                </tr>				
                </tbody>
            </table>

            <div class="pw-space"></div>
            <div class="pw-section-title"><?php _e('Localization', 'pw_wc_advanced_gift') ?></div>
            <table class="pw-form-table">
                <tbody>
                <tr>
                    <th>
                        <?php _e('Popup Title', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="text" name="pw_gift_options[popup_title]" style="width: 30%;"
                               value="<?php echo $setting['popup_title']; ?>"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Shopping cart Gift Title', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="text" name="pw_gift_options[cart_title]" style="width: 30%;"
                               value="<?php echo $setting['cart_title']; ?>"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Hour', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="text" name="pw_gift_options[Hour]" style="width: 30%;"
                               value="<?php echo @$setting['Hour']; ?>"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Minutes', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="text" name="pw_gift_options[Minutes]" style="width: 30%;"
                               value="<?php echo @$setting['Minutes']; ?>"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Seconds', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="text" name="pw_gift_options[Seconds]" style="width: 30%;"
                               value="<?php echo @$setting['Seconds']; ?>"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('Free', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="text" name="pw_gift_options[free]" style="width: 30%;"
                               value="<?php echo @$setting['free']; ?>"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('ADD GIFT', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="text" name="pw_gift_options[add_gift]" style="width: 30%;"
                               value="<?php echo @$setting['add_gift']; ?>"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e('product gift(s)', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="text" name="pw_gift_options[txt_single_product]" style="width: 30%;"
                               value="<?php echo @$setting['txt_single_product']; ?>"/>
                    </td>
                </tr>
				<tr>
                    <th>
                        <?php _e('Select Gift', 'pw_wc_advanced_gift') ?>
                    </th>
                    <td>
                        <input type="text" name="pw_gift_options[select_gift]" style="width: 30%;"
                               value="<?php echo @$setting['select_gift']; ?>"/>
                    </td>
                </tr>
                <tr>
                    <th>&nbsp;

                    </th>
                    <td>
                        <input type="submit" class="button-primary"
                               value="<?php _e('Save Changes', 'pw_wc_advanced_gift') ?>">
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</form>

<script language="javascript">

    jQuery(document).ready(function () {
//	jQuery('.chosen-select').chosen();
//	jQuery('.pw_product_depends').dependsOn({
//		'.product_depends': {
//			values: ['yes']
//		}	
//	});

        jQuery('.pw_multiple_depends').dependsOn({
            '.multiple_depends': {
                values: ['yes']
            }
        });
        jQuery('.pw_view_cart_gift_depends_depends').dependsOn({
            '.view_cart_gift_depends': {
                values: ['carousel']
            }
        });
        jQuery('.pw_view_cart_gift_grid_depends').dependsOn({
            '.view_cart_gift_depends': {
                values: ['grid']
            }
        });
    });
</script>

<!--		<hr>
    	<h3>Pre Define Gift</h3>
		<table class="form-table" cellpadding="0" cellspacing="0">
			
			<tr>
				<th><?php _e('Active', 'pw_wc_advanced_gift'); ?></th>
				<td>
					<input type="radio" name="pw_gift_options[predifine_active]" value="yes" <?php checked($setting['predifine_active'], "yes"); ?> class="product_depends">Yes
					<input type="radio" name="pw_gift_options[predifine_active]" value="no" <?php if ($setting['predifine_active'] != "yes") echo "checked"; ?> class="product_depends">No
					<br/>
					<span class="description">
						<?php _e('Selecte Product as a predifined gift', 'pw_wc_advanced_gift'); ?>
					</span>
				</td>
			</tr>
			
			<tr class="pw_product_depends">
				<th><?php _e('Selecte Products', 'pw_wc_advanced_gift') ?></th>
				<td>
                    <div>
                    <select name="pw_gift_options[predifine_gift]" class="chosen-select" data-placeholder="<?php _e('Choose Product', 'pw_wc_advanced_gift') ?> ..." >
						<?php
/*	$args_post = array('post_type' => 'product','posts_per_page'=>-1);
    $loop_post = new WP_Query( $args_post );
    $option_data='';
    while ( $loop_post->have_posts() ) : $loop_post->the_post();
        //$selected='';
        //$meta=$setting['predifine_gift'];
        //if(is_array($meta))
           // $selected=(in_array(get_the_ID(),$meta) ? "SELECTED":"");
        $option_data.='<option value="'.get_the_ID().'" '. selected( $setting['predifine_gift'], get_the_ID() ,1).'>'.get_the_title().'</option>';
    endwhile;
    echo $option_data;
    */
?>
                    </select>
                    </div>					
				</td>
			</tr>
		</table>
		-->