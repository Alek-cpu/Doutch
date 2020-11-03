<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	#poststuff #side-sortables .postbox input.text_input,
	#poststuff #side-sortables .postbox select.select {
	    width: 50%;
	}
	#poststuff #side-sortables .postbox label.text_label {
	    width: 45%;
	}
	#poststuff #side-sortables .postbox p.desc {
	    margin-left: 5px;
	}

</style>

<div class="wrap wplister-page">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/hammer-32x32.png) no-repeat;" id="wpl-icon"><br /></div>

	<?php include_once( dirname(__FILE__).'/settings_tabs.php' ); ?>
	<?php echo $wpl_message ?>

	<form method="post" id="settingsForm" action="<?php echo $wpl_form_action; ?>">

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<div id="postbox-container-1" class="postbox-container">
				<div id="side-sortables" class="meta-box">


					<!-- first sidebox -->
					<div class="postbox" id="submitdiv">
						<!--<div title="Click to toggle" class="handlediv"><br></div>-->
						<h3 class="hndle"><span><?php echo __( 'Update', 'wp-lister-for-ebay' ); ?></span></h3>
						<div class="inside">

							<div id="submitpost" class="submitbox">

								<div id="misc-publishing-actions">
									<div class="misc-pub-section">
										<p><?php echo __( 'This page contains some advanced options for special use cases.', 'wp-lister-for-ebay' ) ?></p>
									</div>
								</div>

								<div id="major-publishing-actions">
									<div id="publishing-action">
                                        <?php wp_nonce_field( 'wplister_save_advanced_settings' ); ?>
										<input type="hidden" name="action" value="save_wplister_advanced_settings" >
										<input type="submit" value="<?php echo __( 'Save Settings', 'wp-lister-for-ebay' ); ?>" id="save_settings" class="button-primary" name="save">
									</div>
									<div class="clear"></div>
								</div>

							</div>

						</div>
					</div>

					<?php if ( ( ! is_multisite() ) || ( is_main_site() ) ) : ?>
					<div class="postbox" id="UninstallSettingsBox">
						<h3 class="hndle"><span><?php echo __( 'Uninstall on deactivation', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<label for="wpl-option-uninstall" class="text_label"><?php echo __( 'Uninstall', 'wp-lister-for-ebay' ); ?></label>
							<select id="wpl-option-uninstall" name="wpl_e2e_option_uninstall" class="required-entry select">
								<option value="0" <?php if ( $wpl_option_uninstall != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?></option>
								<option value="1" <?php if ( $wpl_option_uninstall == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Enable to completely remove listings, transactions and settings when deactivating the plugin.', 'wp-lister-for-ebay' ); ?><br><br>
								<?php echo __( 'To remove your listing templates as well, please delete the folder <code>wp-content/uploads/wp-lister/templates/</code>.', 'wp-lister-for-ebay' ); ?>
							</p>

						</div>
					</div>
					<?php endif; ?>

					<?php #include('profile/edit_sidebar.php') ?>
				</div>
			</div> <!-- #postbox-container-1 -->

			<!-- #postbox-container-3 -->
			<?php if ( ( ! is_multisite() || is_main_site() ) && apply_filters( 'wpl_enable_capabilities_options', true ) ) : ?>
			<div id="postbox-container-3" class="postbox-container">
				<div class="meta-box-sortables ui-sortable">

					<div class="postbox" id="PermissionsSettingsBox">
						<h3 class="hndle"><span><?php echo __( 'Roles and Capabilities', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<?php
								$wpl_caps = array(
									'manage_ebay_listings'  => __( 'Manage Listings', 'wp-lister-for-ebay' ),
									'manage_ebay_options'   => __( 'Manage Settings', 'wp-lister-for-ebay' ),
									'prepare_ebay_listings' => __( 'Prepare Listings', 'wp-lister-for-ebay' ),
									'publish_ebay_listings' => __( 'Publish Listings', 'wp-lister-for-ebay' ),
								);
							?>

							<table style="width:100%">
                            <?php foreach ($wpl_available_roles as $role => $role_name) : ?>
                            	<tr>
                            		<th style="text-align: left">
		                                <?php echo $role_name; ?>
		                            </th>

		                            <?php foreach ($wpl_caps as $cap => $cap_name ) : ?>
                            		<td>
		                                <input type="checkbox"
		                                    	name="wpl_permissions[<?php echo $role ?>][<?php echo $cap ?>]"
		                                       	id="wpl_permissions_<?php echo $role.'_'.$cap ?>" class="checkbox_cap"
		                                       	<?php if ( isset( $wpl_wp_roles[ $role ]['capabilities'][ $cap ] ) ) : ?>
		                                       		checked
		                                   		<?php endif; ?>
		                                       	/>
		                                       	<label for="wpl_permissions_<?php echo $role.'_'.$cap ?>">
				                               		<?php echo $cap_name; ?>
				                               	</label>
			                            </td>
		                            <?php endforeach; ?>

		                        </tr>
                            <?php endforeach; ?>
                        	</table>


						</div>
					</div>

				</div>
			</div> <!-- #postbox-container-1 -->
			<?php endif; ?>


			<!-- #postbox-container-2 -->
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables ui-sortable">

					<?php do_action( 'wple_before_advanced_settings' ) ?>

					<div class="postbox" id="TemplateSettingsBox">
						<h3 class="hndle"><span><?php echo __( 'Listing Templates', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<label for="wpl-process_shortcodes" class="text_label">
								<?php echo __( 'Shortcode processing', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('By default, WP-Lister runs your product description through the usual WordPress content filters which enabled you to use shortcodes in your product descriptions.<br>If a plugin causes trouble by adding unwanted HTML to your description on eBay, you should try setting this to "off".') ?>
							</label>
							<select id="wpl-process_shortcodes" name="wpl_e2e_process_shortcodes" class="required-entry select">
								<option value="off"     <?php if ( $wpl_process_shortcodes == 'off' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'off', 'wp-lister-for-ebay' ); ?></option>
								<option value="content" <?php if ( $wpl_process_shortcodes == 'content' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'only in product description', 'wp-lister-for-ebay' ); ?></option>
								<option value="full"    <?php if ( $wpl_process_shortcodes == 'full' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'in description and listing template', 'wp-lister-for-ebay' ); ?></option>
								<option value="remove"  <?php if ( $wpl_process_shortcodes == 'remove' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'remove all shortcodes from description', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Enable this if you want to use WordPress shortcodes in your product description or your listing template.', 'wp-lister-for-ebay' ); ?><br>
							</p>

                            <label for="wpl-do_template_autop" class="text_label">
                                <?php echo __( 'Convert line breaks to paragraphs', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('All line breaks in the product description are converted into paragraphs by default for a cleaner look.') ?>
                            </label>
                            <select id="wpl-do_template_autop" name="wpl_e2e_do_template_autop" class="required-entry select">
                                <option value="1" <?php selected( $wpl_do_template_autop, 1 ); ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay'); ?>)</option>
                                <option value="0" <?php selected( $wpl_do_template_autop, 0 ); ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?></option>
                            </select>

							<label for="wpl-remove_links" class="text_label">
								<?php echo __( 'Link handling', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Should WP-Lister replace links within the product description with plain text?') ?>
							</label>
							<select id="wpl-remove_links" name="wpl_e2e_remove_links" class="required-entry select">
								<option value="default"         <?php selected( 'default', $wpl_remove_links ); ?>><?php echo __( 'remove all links from description', 'wp-lister-for-ebay' ); ?></option>
								<option value="remove_external" <?php selected( 'remove_external', $wpl_remove_links ); ?>><?php echo __( 'remove all non-eBay links from description', 'wp-lister-for-ebay' ); ?></option>
								<option value="allow_all"       <?php selected( 'allow_all', $wpl_remove_links ); ?>><?php echo __( 'allow all links', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Links are removed from product descriptions by default to avoid violating the eBay Links policy.', 'wp-lister-for-ebay' ); ?>
								<?php echo __( 'Specifically you are not allowed to advertise products that you list on eBay by linking to their product pages on your site.', 'wp-lister-for-ebay' ); ?>

								<?php echo __( 'Read more about eBay\'s Link policy', 'wp-lister-for-ebay' ); ?>
								<a href="<?php echo __( 'http://pages.ebay.com/help/policies/listing-links.html', 'wp-lister-for-ebay' ); ?>" target="_blank"><?php echo __('here', 'wp-lister-for-ebay' ); ?></a>
							</p>

							<label for="wpl-template_ssl_mode" class="text_label">
								<?php echo __( 'HTTPS conversion', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Enable this to make sure all image links in your listing template use HTTPS.<br>If your site supports SSL, it is recommended to set this option to "Use HTTPS".') ?>
							</label>
							<select id="wpl-template_ssl_mode" name="wpl_e2e_template_ssl_mode" class="required-entry select">
								<option value=""           <?php if ( $wpl_template_ssl_mode == ''          ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Off', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="https"      <?php if ( $wpl_template_ssl_mode == 'https'     ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Use HTTPS', 'wp-lister-for-ebay' ); ?> (<?php _e('recommended', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="enforce"    <?php if ( $wpl_template_ssl_mode == 'enforce'   ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Convert all HTTP content to HTTPS', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Enable this if your site supports HTTPS.', 'wp-lister-for-ebay' ); ?>
							</p>

							<label for="wpl-wc2_gallery_fallback" class="text_label">
								<?php echo __( 'Product Gallery', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('In order to find additional product images, WP-Lister first checks if there is a dedicated <i>Product Gallery</i> (WC 2.0+).<br>
                                						If there\'s none, it can use all images which were uploaded (attached) to the product - as it was the default behaviour in WooCommerce 1.x.') ?>
							</label>
							<select id="wpl-wc2_gallery_fallback" name="wpl_e2e_wc2_gallery_fallback" class="required-entry select">
								<option value="attached" <?php if ( $wpl_wc2_gallery_fallback == 'attached' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'use attached images if no Gallery found', 'wp-lister-for-ebay' ); ?></option>
								<option value="none"     <?php if ( $wpl_wc2_gallery_fallback == 'none'     ): ?>selected="selected"<?php endif; ?>><?php echo __( 'use Product Gallery images', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
							</select>
							<?php if ( $wpl_wc2_gallery_fallback == 'attached' ): ?>
							<p class="desc" style="display: block;">
								<?php echo __( 'If you find unwanted images in your listings try disabling this option.', 'wp-lister-for-ebay' ); ?>
							</p>
							<?php else : ?>
							<p class="desc" style="display: block;">
								<?php echo __( 'It is recommended to keep the default setting.', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<?php endif; ?>

							<label for="wpl-default_image_size" class="text_label">
								<?php echo __( 'Default image size', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Select the image size WP-Lister should use on eBay. It is recommended to set this to "full size".') ?>
							</label>
							<select id="wpl-default_image_size" name="wpl_e2e_default_image_size" class="required-entry select">
								<option value="full"    <?php if ( $wpl_default_image_size == 'full'   ): ?>selected="selected"<?php endif; ?>><?php echo __( 'full size', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="large"   <?php if ( $wpl_default_image_size == 'large'  ): ?>selected="selected"<?php endif; ?>><?php echo __( 'large size', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'It is recommended to keep the default setting.', 'wp-lister-for-ebay' ); ?><br>
							</p>

							<label for="wpl-gallery_items_limit" class="text_label">
								<?php echo __( 'Gallery Widget limit', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Limit the number of items displayed by the gallery widgets in your listing template - like <i>recent additions</i> or <i>ending soon</i>. The default is 12 items.') ?>
							</label>
							<select id="wpl-gallery_items_limit" name="wpl_e2e_gallery_items_limit" class="required-entry select">
								<option value="3" <?php if ( $wpl_gallery_items_limit == '3' ): ?>selected="selected"<?php endif; ?>>3 <?php echo __( 'items', 'wp-lister-for-ebay' ); ?></option>
								<option value="6" <?php if ( $wpl_gallery_items_limit == '6' ): ?>selected="selected"<?php endif; ?>>6 <?php echo __( 'items', 'wp-lister-for-ebay' ); ?></option>
								<option value="9" <?php if ( $wpl_gallery_items_limit == '9' ): ?>selected="selected"<?php endif; ?>>9 <?php echo __( 'items', 'wp-lister-for-ebay' ); ?></option>
								<option value="12" <?php if ( $wpl_gallery_items_limit == '12' ): ?>selected="selected"<?php endif; ?>>12 <?php echo __( 'items', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="15" <?php if ( $wpl_gallery_items_limit == '15' ): ?>selected="selected"<?php endif; ?>>15 <?php echo __( 'items', 'wp-lister-for-ebay' ); ?></option>
								<option value="24" <?php if ( $wpl_gallery_items_limit == '24' ): ?>selected="selected"<?php endif; ?>>24 <?php echo __( 'items', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'The maximum number of items shown by listings template gallery widgets.', 'wp-lister-for-ebay' ); ?>
							</p>

						</div>
					</div>

					<div class="postbox" id="UISettingsBox">
						<h3 class="hndle"><span><?php echo __( 'User Interface', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<?php if ( ! defined('WPLISTER_RESELLER_VERSION') ) : ?>
							<label for="wpl-text-admin_menu_label" class="text_label">
								<?php echo __( 'Menu label', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('You can change the main admin menu label in your dashboard from WP-Lister to anything you like.') ?>
							</label>
							<input type="text" name="wpl_e2e_text_admin_menu_label" id="wpl-text-admin_menu_label" value="<?php echo $wpl_text_admin_menu_label; ?>" class="text_input" />
							<p class="desc" style="display: block;">
								<?php echo __( 'Customize the main admin menu label of WP-Lister.', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<?php endif; ?>

							<label for="wpl-option-preview_in_new_tab" class="text_label">
								<?php echo __( 'Open preview in new tab', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('WP-Lister uses a Thickbox modal window to display the preview by default. However, this can cause issues in rare cases where you embed some JavaScript code (like NivoSlider) - or you might just want more screen estate to preview your listings.') ?>
							</label>
							<select id="wpl-option-preview_in_new_tab" name="wpl_e2e_option_preview_in_new_tab" class="required-entry select">
								<option value="0" <?php if ( $wpl_option_preview_in_new_tab != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="1" <?php if ( $wpl_option_preview_in_new_tab == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Select if you want the listing preview open in a new tab by default.', 'wp-lister-for-ebay' ); ?><br>
							</p>

							<label for="wpl-option-thumbs_display_size" class="text_label">
								<?php echo __( 'Listing thumbnails', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('Select the thumbnail size on the Listings page.') ?>
							</label>
							<select id="wpl-option-thumbs_display_size" name="wpl_e2e_thumbs_display_size" class="required-entry select">
								<option value="0" <?php if ( $wpl_thumbs_display_size == '0' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Small', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="1" <?php if ( $wpl_thumbs_display_size == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Medium', 'wp-lister-for-ebay' ); ?></option>
								<option value="2" <?php if ( $wpl_thumbs_display_size == '2' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Large', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Select the thumbnail size on the Listings page.', 'wp-lister-for-ebay' ); ?><br>
							</p>

                            <label for="wpl-option-listing_sku_sorting" class="text_label">
                                <?php echo __( 'Enable sorting by SKU', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('Enable or disable sorting by SKU.<br><br>This is disabled by default as it can negatively impact the load time of the Listings page for stores with thousands of products.') ?>
                            </label>
                            <select id="wpl-option-listing_sku_sorting" name="wpl_e2e_listing_sku_sorting" class="required-entry select">
                                <option value="1" <?php selected( $wpl_listing_sku_sorting, 1 ); ?>><?php echo __( 'Enabled', 'wp-lister-for-ebay' ); ?></option>
                                <option value="0" <?php selected( $wpl_listing_sku_sorting, 0 ); ?>><?php echo __( 'Disabled', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
                            </select>
                            <p class="desc" style="display: block;">
                                <?php echo __( 'Enable this to make the SKU column sortable. Can affect load time of the Listings page.', 'wp-lister-for-ebay' ); ?><br>
                            </p>

							<label for="wpl-enable_custom_product_prices" class="text_label">
								<?php echo __( 'Enable custom price field', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('If do not use custom prices in eBay and prefer less options when editing a product, you can disable the custom price fields here.') ?>
							</label>
							<select id="wpl-enable_custom_product_prices" name="wpl_e2e_enable_custom_product_prices" class=" required-entry select">
								<option value="0" <?php if ( $wpl_enable_custom_product_prices == '0' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?></option>
								<option value="1" <?php if ( $wpl_enable_custom_product_prices == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="2" <?php if ( $wpl_enable_custom_product_prices == '2' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Hide for variations', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Show or hide the custom eBay price field.', 'wp-lister-for-ebay' ); ?><br>
							</p>

							<label for="wpl-enable_mpn_and_isbn_fields" class="text_label">
								<?php echo __( 'Enable MPN and ISBN fields', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('If your variable products have MPNs or ISBNs, set this option to <i>Yes</i>.<br><br>If you need MPNs or ISBNs only on simple products, leave it at the default setting.<br><br>If you never use MPNs nor ISBNs, set it to <i>No</i>.') ?>
							</label>
							<select id="wpl-enable_mpn_and_isbn_fields" name="wpl_e2e_enable_mpn_and_isbn_fields" class=" required-entry select">
								<option value="0" <?php if ( $wpl_enable_mpn_and_isbn_fields == '0' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?></option>
								<option value="1" <?php if ( $wpl_enable_mpn_and_isbn_fields == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
								<option value="2" <?php if ( $wpl_enable_mpn_and_isbn_fields == '2' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Hide for variations', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Show or hide the MPN and ISBN fields.', 'wp-lister-for-ebay' ); ?><br>
							</p>

							<label for="wpl-enable_categories_page" class="text_label">
								<?php echo __( 'Categories in main menu', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('This will add a <em>Categories</em> submenu entry visible to users who can manage listings.') ?>
							</label>
							<select id="wpl-enable_categories_page" name="wpl_e2e_enable_categories_page" class="required-entry select">
								<option value="0" <?php if ( $wpl_enable_categories_page != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="1" <?php if ( $wpl_enable_categories_page == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Enable this to make category settings available to users without access to other eBay settings.', 'wp-lister-for-ebay' ); ?><br>
							</p>

                            <label for="wpl-store_categories_sorting" class="text_label">
                                <?php echo __( 'Store Categories Order', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('Choose whether to display the store categories using the manual order from eBay, or sort them alphabetically.') ?>
                            </label>
                            <select id="wpl-store_categories_sorting" name="wpl_e2e_store_categories_sorting" class="required-entry select">
                                <option value="default" <?php selected( $wpl_store_categories_sorting, 'default' ); ?>><?php echo __( 'Manual sort order', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
                                <option value="alphabetical" <?php selected( $wpl_store_categories_sorting, 'alphabetical' ); ?>><?php echo __( 'Sort alphabetically', 'wp-lister-for-ebay' ); ?></option>
                            </select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Select whether you want your store categories to be sorted alphabetically.', 'wp-lister-for-ebay' ); ?><br>
							</p>

							<label for="wpl-enable_accounts_page" class="text_label">
								<?php echo __( 'Accounts in main menu', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('This will add a <em>Accounts</em> submenu entry visible to users who can manage listings.') ?>
							</label>
							<select id="wpl-enable_accounts_page" name="wpl_e2e_enable_accounts_page" class="required-entry select">
								<option value="0" <?php if ( $wpl_enable_accounts_page != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="1" <?php if ( $wpl_enable_accounts_page == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Enable this to make account settings available to users without access to other eBay settings.', 'wp-lister-for-ebay' ); ?><br>
							</p>

							<label for="wpl-option-disable_wysiwyg_editor" class="text_label">
								<?php echo __( 'Disable WYSIWYG editor', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('Depending in your listing template content, you might want to disable the built in WP editor to edit your template content.') ?>
							</label>
							<select id="wpl-option-disable_wysiwyg_editor" name="wpl_e2e_option_disable_wysiwyg_editor" class="required-entry select">
								<option value="0" <?php if ( $wpl_option_disable_wysiwyg_editor != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="1" <?php if ( $wpl_option_disable_wysiwyg_editor == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Select the editor you want to use to edit listing templates.', 'wp-lister-for-ebay' ); ?><br>
							</p>

							<label for="wpl-hide_dupe_msg" class="text_label">
								<?php echo __( 'Hide duplicates warning', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Technically, WP-Lister allows you to list the same product multiple times on eBay - in order to increase your visibility. However, this is not recommended as WP-Lister Pro would not be able to decrease the stock on eBay accordingly when the product is sold in WooCommerce.') ?>
							</label>
							<select id="wpl-hide_dupe_msg" name="wpl_e2e_hide_dupe_msg" class="required-entry select">
								<option value=""  <?php if ( $wpl_hide_dupe_msg == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (<?php _e('recommended', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="1" <?php if ( $wpl_hide_dupe_msg == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes, I know what I am doing.', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'If you do not plan to use the synchronize sales feature, you can safely list one product multiple times.', 'wp-lister-for-ebay' ); ?>
							</p>

                            <label for="wpl-option-display_product_counts" class="text_label">
                                <?php _e( 'Show eBay product totals', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('This will display the total number of products <i>On eBay</i> and <i>Not on eBay</i> on the Products admin page in WooCommerce.<br><br>Please note: Enabling this option requires some complex database queries which might slow down loading the Products admin page.<br><br>If the Products page is taking too long to load, you should disable this option or move to a more powerful hosting/server.'); ?>
                            </label>
                            <select id="wpl-option-display_product_counts" name="wpl_e2e_display_product_counts" class="required-entry select">
                                <option value="0" <?php selected( $wpl_display_product_counts, 0 ); ?>><?php _e( 'No', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
                                <option value="1" <?php selected( $wpl_display_product_counts, 1 ); ?>><?php _e( 'Yes', 'wp-lister-for-ebay' ); ?></option>
                            </select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Enable this to display the total number of products on eBay / not on eBay in WooCommerce.', 'wp-lister-for-ebay' ); ?>
							</p>

						</div>
					</div>











                    <div class="postbox" id="InventorySyncSettingsBox">
                        <h3 class="hndle"><span><?php echo __( 'Background Inventory Check', 'wp-lister-for-ebay' ) ?></span></h3>
                        <div class="inside">
                            <label for="wpl-option-run_background_inventory_check" class="text_label">
                                <?php echo __( 'Run background inventory checks', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('Download an inventory report from eBay regularly and compare inventory between eBay and your WooCommerce store.') ?>
                            </label>
                            <select id="wpl-option-run_background_inventory_check" name="wpl_e2e_run_background_inventory_check" class="required-entry select">
                                <option value="1" <?php selected( $wpl_run_background_inventory_check, 1 ); ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
                                <option value="0" <?php selected( $wpl_run_background_inventory_check, 0 ); ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?></option>
                            </select>
                            <p class="desc" style="display: block;"><?php echo __( 'Download an inventory report from eBay regularly and compare inventory between eBay and your WooCommerce store.', 'wp-lister-for-ebay' ); ?><br></p>

                            <label for="wpl-option-inventory_check_frequency" class="text_label show-if-inventory-check">
                                <?php _e( 'Inventory check frequency', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip( '' ); ?>
                            </label>
                            <select id="wpl-option-inventory_check_frequency" name="wpl_e2e_inventory_check_frequency" class="required-entry select show-if-inventory-check">
                                <option value="24" <?php selected( $wpl_inventory_check_frequency, 24 ); ?>><?php _e( 'Every 24 hours', 'wp-lister-for-ebay' ); ?></option>
                            </select>
                            <p class="desc show-if-inventory-check" style="display: block;">
                                <?php
                                echo __( 'Set how often to download inventory reports to compare against your local inventory.', 'wp-lister-for-ebay' );

                                if ( WPLE_IS_LITE_VERSION ) {
                                    echo __( ' PRO users can set this to as often as once an hour', 'wp-lister-for-ebay' );
                                }
                                ?><br/>
                            </p>

                            <label for="wpl-option-inventory_check_notification_email" class="text_label show-if-inventory-check">
                                <?php _e( 'Send inventory reports to', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip( sprintf( 'Defaults to your admin email address (%s)', get_bloginfo( 'admin_email' ) ) ); ?>
                            </label>
                            <input type="email" id="wpl-option_inventory_check_notification_email" name="wpl_e2e_inventory_check_notification_email" value="<?php esc_attr_e( $wpl_inventory_check_notification_email ); ?>" placeholder="<?php esc_attr_e( get_bloginfo( 'admin_email' ) ); ?>" class="text_input show-if-inventory-check" />
                            <p class="desc show-if-inventory-check" style="display: block;"><?php echo __( 'Set the email address where inventory reports will be sent when inventory inconsistencies are found.', 'wp-lister-for-ebay' ); ?><br>
                            </p>
						</div>
					</div>

					<div class="postbox" id="AttributeSettingsBox">
						<h3 class="hndle"><span><?php echo __( 'Units, Attributes and Item Specifics', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<label for="wpl-send_weight_and_size" class="text_label">
								<?php echo __( 'Send weight and dimensions', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('By default, product weight and dimensions are only sent to eBay when calculated shipping is used.<br>Enable this option to send weight and dimensions for all listings.') ?>
							</label>
							<select id="wpl-send_weight_and_size" name="wpl_e2e_send_weight_and_size" class=" required-entry select">
								<option value="default" <?php if ( $wpl_send_weight_and_size == 'default'): ?>selected="selected"<?php endif; ?>><?php echo __( 'Only for calculated shipping services', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="always"  <?php if ( $wpl_send_weight_and_size == 'always' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Always send weight and dimensions if set', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Enable this if eBay requires package weight or dimensions for flat shipping.', 'wp-lister-for-ebay' ); ?>
							</p>

							<label for="wpl-convert_dimensions" class="text_label">
								<?php echo __( 'Dimension Unit Conversion', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('WP-Lister assumes that you use the same dimension unit in WooCommerce as on eBay. Enable this to convert length, width and height from one unit to another.') ?>
							</label>
							<select id="wpl-convert_dimensions" name="wpl_e2e_convert_dimensions" class="required-entry select">
								<option value=""  <?php if ( $wpl_convert_dimensions == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No conversion', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="cm-in" <?php if ( $wpl_convert_dimensions == 'cm-in' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Convert centimeters to inches', 'wp-lister-for-ebay' ); ?> ( cm &raquo; in )</option>
								<option value="in-cm" <?php if ( $wpl_convert_dimensions == 'in-cm' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Convert inches to centimeters', 'wp-lister-for-ebay' ); ?> ( in &raquo; cm )</option>
								<option value="mm-cm" <?php if ( $wpl_convert_dimensions == 'mm-cm' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Convert milimeters to centimeters', 'wp-lister-for-ebay' ); ?> ( mm &raquo; cm )</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Convert length, width and height to the unit required by eBay.', 'wp-lister-for-ebay' ); ?>
							</p>

							<label for="wpl-convert_attributes_mode" class="text_label">
								<?php echo __( 'Use attributes as item specifics', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('The default is to convert all WooCommerce product attributes to item specifics on eBay.<br><br>If you disable this option, only the item specifics defined in your listing profile will be sent to eBay.') ?>
							</label>
							<select id="wpl-convert_attributes_mode" name="wpl_e2e_convert_attributes_mode" class="required-entry select">
								<option value="all"    <?php if ( $wpl_convert_attributes_mode == 'all'    ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Convert all attributes to item specifics', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="single" <?php if ( $wpl_convert_attributes_mode == 'single' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Convert all attributes, but disable multi value attributes', 'wp-lister-for-ebay' ); ?></option>
								<option value="none"   <?php if ( $wpl_convert_attributes_mode == 'none'   ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Disabled', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Disable this option if you do not want all product attributes to be sent to eBay.', 'wp-lister-for-ebay' ); ?>
							</p>

							<label for="wpl-exclude_attributes" class="text_label">
								<?php echo __( 'Exclude attributes', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('If you want to hide certain product attributes from eBay enter their names separated by commas here.<br>Example: Brand,Size,MPN') ?>
							</label>
							<input type="text" name="wpl_e2e_exclude_attributes" id="wpl-exclude_attributes" value="<?php echo $wpl_exclude_attributes; ?>" class="text_input" />
							<p class="desc" style="display: block;">
								<?php echo __( 'Enter a comma separated list of product attributes to exclude from eBay.', 'wp-lister-for-ebay' ); ?><br>
							</p>

							<label for="wpl-exclude_variation_values" class="text_label">
								<?php echo __( 'Exclude variations', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('If you want to hide certain variations from eBay enter their attribute values separated by commas here.<br>Example: Brown,Blue,Orange') ?>
							</label>
							<input type="text" name="wpl_e2e_exclude_variation_values" id="wpl-exclude_variation_values" value="<?php echo $wpl_exclude_variation_values; ?>" class="text_input" />
							<p class="desc" style="display: block;">
								<?php echo __( 'Enter a comma separated list of variation attribute values to exclude from eBay.', 'wp-lister-for-ebay' ); ?><br>
							</p>

						</div>
					</div>


					<div class="postbox" id="OtherSettingsBox">
						<h3 class="hndle"><span><?php echo __( 'Misc Options', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<label for="wpl-autofill_missing_gtin" class="text_label">
								<?php echo __( 'Missing Product Identifiers', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('eBay requires product identifiers (UPC/EAN) in selected categories starting 2015 - missing EANs/UPCs can cause the revise process to fail.<br><br>If your products do not have either UPCs or EANs, please use this option.') ?>
							</label>
							<select id="wpl-autofill_missing_gtin" name="wpl_e2e_autofill_missing_gtin" class="required-entry select">
								<option value=""  <?php if ( $wpl_autofill_missing_gtin == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Do nothing', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="upc" <?php if ( $wpl_autofill_missing_gtin == 'upc' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'If UPC is empty use "Does not apply" instead', 'wp-lister-for-ebay' ); ?></option>
								<option value="ean" <?php if ( $wpl_autofill_missing_gtin == 'ean' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'If EAN is empty use "Does not apply" instead', 'wp-lister-for-ebay' ); ?></option>
								<option value="both" <?php if ( $wpl_autofill_missing_gtin == 'both' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'If both fields are empty use "Does not apply" instead', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Enable this option if your products do not have UPCs or EANs.', 'wp-lister-for-ebay' ); ?>
							</p>

							<label for="wpl-option-local_timezone" class="text_label">
								<?php echo __( 'Local timezone', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('This is currently used to convert the order creation date from UTC to local time.') ?>
							</label>
							<select id="wpl-option-local_timezone" name="wpl_e2e_option_local_timezone" class="required-entry select">
								<option value="">-- <?php echo __( 'no timezone selected', 'wp-lister-for-ebay' ); ?> --</option>
								<?php foreach ($wpl_timezones as $tz_id => $tz_name) : ?>
									<option value="<?php echo $tz_id ?>" <?php if ( $wpl_option_local_timezone == $tz_id ): ?>selected="selected"<?php endif; ?>><?php echo $tz_name ?></option>
								<?php endforeach; ?>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Select your local timezone.', 'wp-lister-for-ebay' ); ?><br>
							</p>

							<label for="wpl-enable_item_compat_tab" class="text_label">
								<?php echo __( 'Enable Item Compatibility tab', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Item compatibility lists are currently only created for imported products. Future versions of WP-Lister Pro will allow to define compatibility lists in WooCommerce.') ?>
							</label>
							<select id="wpl-enable_item_compat_tab" name="wpl_e2e_enable_item_compat_tab" class="required-entry select">
								<option value=""  <?php if ( $wpl_enable_item_compat_tab == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?></option>
								<option value="1" <?php if ( $wpl_enable_item_compat_tab == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Show eBay Item Compatibility List as new tab on single product page.', 'wp-lister-for-ebay' ); ?>
							</p>

							<label for="wpl-disable_sale_price" class="text_label">
								<?php echo __( 'Use sale price', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Set this to No if you want your sale prices to be ignored. You can still use a relative profile price to increase your prices by a percentage.') ?>
							</label>
							<select id="wpl-disable_sale_price" name="wpl_e2e_disable_sale_price" class="required-entry select">
								<option value="0" <?php if ( $wpl_disable_sale_price != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="1" <?php if ( $wpl_disable_sale_price == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Should sale prices be used automatically on eBay?', 'wp-lister-for-ebay' ); ?><br>
							</p>

							<label for="wpl-apply_profile_to_ebay_price" class="text_label">
								<?php echo __( 'Apply profile to eBay price', 'wp-lister-for-ebay' ); ?>
								<?php wplister_tooltip('By default, a custom eBay price (set on the product level) takes precendence over any other prices, including regular prices, sale prices and prices in your listing profile.<br><br>So if you use a profile to reduce all prices by 10% - using the price modifier "-10%" - and you want this to be applied to custom eBay prices as well, please enable this option.') ?>
							</label>
							<select id="wpl-apply_profile_to_ebay_price" name="wpl_e2e_apply_profile_to_ebay_price" class="required-entry select">
								<option value="0" <?php selected( $wpl_apply_profile_to_ebay_price, 0 ); ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="1" <?php selected( $wpl_apply_profile_to_ebay_price, 1 ); ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Enable this to allow your listing profile to modify a custom eBay price set on the product level.', 'wp-lister-for-ebay' ); ?><br>
							</p>

						</div>
					</div>


					<div class="postbox" id="DeprecatedSettingsBox">
						<h3 class="hndle"><span><?php echo __( 'Deprecated Options', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<p>
								<?php echo __( 'These options can be ignored and should not be used anymore.', 'wp-lister-for-ebay' ); ?>
							</p>

							<label for="wpl-auto_update_ended_items" class="text_label">
								<?php echo __( 'Auto update ended items', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('This can be helpful if you manually relisted items on eBay - which is not recommended.<br><br>We recommend against using this option as it might cause performance issues and other unexpected results.<br><br>If you experience any problems with this option enabled, please disable it again and see if it solves the problem.') ?>
							</label>
							<select id="wpl-auto_update_ended_items" name="wpl_e2e_auto_update_ended_items" class="required-entry select">
								<option value="0" <?php if ( $wpl_auto_update_ended_items != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="1" <?php if ( $wpl_auto_update_ended_items == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?> (<?php _e('not recommended', 'wp-lister-for-ebay' ); ?>)</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Automatically update item details from eBay when a listing has ended.', 'wp-lister-for-ebay' ); ?> (experimental!)
							</p>


						</div>
					</div>

					<?php do_action( 'wple_after_advanced_settings' ) ?>


				<?php if ( ( is_multisite() ) && ( is_main_site() ) ) : ?>
				<p>
					<b>Warning:</b> Deactivating WP-Lister on a multisite network will remove all settings and data from all sites.
				</p>
				<?php endif; ?>


				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 -->


		</div> <!-- #post-body -->
		<br class="clear">
	</div> <!-- #poststuff -->

	</form>

    <script type="text/javascript">
        jQuery( document ).ready( function($) {
            $('#wpl-option-run_background_inventory_check').change(function () {
                if ($('#wpl-option-run_background_inventory_check').val() != 1) {
                    $('.show-if-inventory-check').hide();
                } else {
                    $('.show-if-inventory-check').show();
                }
            }).change();
        });
    </script>


</div>
