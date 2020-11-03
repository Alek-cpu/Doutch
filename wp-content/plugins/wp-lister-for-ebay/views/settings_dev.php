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

    <!-- <br class="clear"> -->
    <!-- <h2><?php _e( 'Developer Settings', 'wp-lister-for-ebay' ); ?></h2> -->

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
										<p><?php echo __( 'This page contains some special options intended for developers and debugging.', 'wp-lister-for-ebay' ) ?></p>
										<p><?php echo sprintf( __( 'The daily maintenance ran %s ago.', 'wp-lister-for-ebay' ), human_time_diff( get_option('wple_daily_cron_last_run') ) ) ?></p>
									</div>
								</div>

								<div id="major-publishing-actions">
									<div id="publishing-action">
                                        <?php wp_nonce_field( 'wplister_save_devsettings' ); ?>
										<input type="hidden" name="action" value="save_wplister_devsettings" >
										<input type="submit" value="<?php echo __( 'Save Settings', 'wp-lister-for-ebay' ); ?>" id="save_settings" class="button-primary" name="save">
									</div>
									<div class="clear"></div>
								</div>

							</div>

						</div>
					</div>



					<div class="postbox dev_box" id="VersionInfoBox" style="<?php echo defined('WPLISTER_RESELLER_VERSION') ? 'display:none' : ''; ?>">
						<h3 class="hndle"><span><?php echo __( 'Version Info', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<table style="width:100%">
								<tr><td>WP-Lister</td><td>	<?php echo WPLE_PLUGIN_VERSION ?> </td></tr>
								<tr><td>Database</td><td> <?php echo get_option('wplister_db_version') ?> </td></tr>
								<tr><td>WordPress</td><td> <?php global $wp_version; echo $wp_version ?> </td></tr>
								<tr><td>WooCommerce</td><td> <?php echo defined('WC_VERSION') ? WC_VERSION : WOOCOMMERCE_VERSION ?> </td></tr>
								<?php if ( defined('WPLISTER_RESELLER_VERSION') ) : ?>
									<tr><td>Reseller Add-On</td><td> <?php echo WPLISTER_RESELLER_VERSION ?> </td></tr>
								<?php endif; ?>
							</table>

						</div>
					</div>

				</div>
			</div> <!-- #postbox-container-1 -->


			<!-- #postbox-container-2 -->
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables ui-sortable">


					<div class="postbox" id="DbLoggingBox">
						<h3 class="hndle"><span><?php echo __( 'Logging and Maintenance', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<label for="wpl-option-log_to_db" class="text_label">
								<?php echo __( 'Log to database', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('If you have any issues or want support to look into a specific error message from eBay then please enable logging, repeat the steps and send the resulting log record to support.') ?>
							</label>
							<select id="wpl-option-log_to_db" name="wpl_e2e_option_log_to_db" title="Logging" class=" required-entry select">
								<option value="1" <?php if ( $wpl_option_log_to_db == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
								<option value="0" <?php if ( $wpl_option_log_to_db != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (default)</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Enable to log all communication with eBay to the database.', 'wp-lister-for-ebay' ); ?>
							</p>

							<label for="wpl-option-log_days_limit" class="text_label">
								<?php echo __( 'Keep log records for', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Select how long log records should be kept. Older records are removed automatically. The default is 30 days.') ?>
							</label>
							<select id="wpl-option-log_days_limit" name="wpl_e2e_log_days_limit" class=" required-entry select">
								<option value="7"  <?php if ( $wpl_log_days_limit == '7' ):  ?>selected="selected"<?php endif; ?>>7 days</option>
								<option value="14"  <?php if ( $wpl_log_days_limit == '14' ):  ?>selected="selected"<?php endif; ?>>14 days</option>
								<option value="30"  <?php if ( $wpl_log_days_limit == '30' ):  ?>selected="selected"<?php endif; ?>>30 days (default)</option>
								<option value="60"  <?php if ( $wpl_log_days_limit == '60' ):  ?>selected="selected"<?php endif; ?>>60 days</option>
								<option value="90"  <?php if ( $wpl_log_days_limit == '90' ):  ?>selected="selected"<?php endif; ?>>90 days</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Select how long log records should be kept.', 'wp-lister-for-ebay' ); ?>
							</p>

							<label for="wpl-option-orders_days_limit" class="text_label">
								<?php echo __( 'Keep sales data for', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Select how long eBay orders should be kept. Older orders are removed from WP-Lister automatically but will remain in WooCommerce. The default is forever.') ?>
							</label>
							<select id="wpl-option-orders_days_limit" name="wpl_e2e_orders_days_limit" class=" required-entry select">
								<option value=""    <?php if ( $wpl_orders_days_limit == ''   ):  ?>selected="selected"<?php endif; ?>>forever (default)</option>
								<option value="90"  <?php if ( $wpl_orders_days_limit == '90' ):  ?>selected="selected"<?php endif; ?>>90 days</option>
								<option value="180" <?php if ( $wpl_orders_days_limit == '180' ): ?>selected="selected"<?php endif; ?>>180 days</option>
								<option value="365" <?php if ( $wpl_orders_days_limit == '365' ): ?>selected="selected"<?php endif; ?>>1 year</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Select how long eBay orders should be kept.', 'wp-lister-for-ebay' ); ?>
							</p>

							<label for="wpl-archive_days_limit" class="text_label">
								<?php echo __( 'Keep archived items for', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Select how long archived listings should be kept. Older records are removed automatically. The default is 90 days.') ?>
							</label>
							<select id="wpl-archive_days_limit" name="wpl_e2e_archive_days_limit" class=" required-entry select">
								<option value="7"  <?php if ( $wpl_archive_days_limit == '7' ):  ?>selected="selected"<?php endif; ?>>7 days</option>
								<option value="14"  <?php if ( $wpl_archive_days_limit == '14' ):  ?>selected="selected"<?php endif; ?>>14 days</option>
								<option value="30"  <?php if ( $wpl_archive_days_limit == '30' ):  ?>selected="selected"<?php endif; ?>>30 days</option>
								<option value="60"  <?php if ( $wpl_archive_days_limit == '60' ):  ?>selected="selected"<?php endif; ?>>60 days</option>
								<option value="90"  <?php if ( $wpl_archive_days_limit == '90' ):  ?>selected="selected"<?php endif; ?>>90 days (default)</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Select how long archived listings should be kept.', 'wp-lister-for-ebay' ); ?>
							</p>

						</div>
					</div>


					<div class="postbox" id="StagingSiteSettingsBox">
						<h3 class="hndle"><span><?php echo __( 'Staging site', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<p>
								<?php echo __( 'If you frequently clone your WordPress installation to a staging site, you can make WP-Lister automatically disable background updates and order creation when running on the staging site.', 'wp-lister-for-ebay' ); ?>
								<?php echo __( 'Enter a unique part of your staging domain below to activate this feature.', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<label for="wpl-staging_site_pattern" class="text_label">
								<?php echo __( 'Staging site pattern', 'wp-lister-for-ebay' ) ?>
								<?php $tip_msg  = __( 'You do not need to enter the full domain name of your staging site.', 'wp-lister-for-ebay' ); ?>
								<?php $tip_msg .= __( 'If your staging domain is mydomain.staging.wpengine.com enter staging.wpengine.com as a general pattern.', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip($tip_msg) ?>
							</label>
							<input type="text" name="wpl_e2e_staging_site_pattern" id="wpl-staging_site_pattern" value="<?php echo $wpl_staging_site_pattern; ?>" class="text_input" />
							<p class="desc" style="display: block;">
								Example: staging.wpengine.com
							</p>

						</div>
					</div>

					<div class="postbox" id="ErrorHandlingBox">
						<h3 class="hndle"><span><?php echo __( 'Debug options', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<p>
								<?php echo __( 'Warning: These options are for debugging purposes only. Please do not change them unless our support told you to do so.', 'wp-lister-for-ebay' ); ?>
							</p>

							<label for="wpl-option-php_error_handling" class="text_label">
								<?php echo __( 'PHP error handling', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Please leave this set to Production unless told otherwise by support.') ?>
							</label>
							<select id="wpl-option-php_error_handling" name="wpl_e2e_php_error_handling" class=" required-entry select">
								<option value="0" <?php if ( $wpl_php_error_handling == '0' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Production Mode', 'wp-lister-for-ebay' ); ?> (default)</option>
								<option value="9" <?php if ( $wpl_php_error_handling == '9' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Production Mode', 'wp-lister-for-ebay' ); ?> (forced)</option>
								<option value="1" <?php if ( $wpl_php_error_handling == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Show all errors inline', 'wp-lister-for-ebay' ); ?></option>
								<option value="2" <?php if ( $wpl_php_error_handling == '2' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Show fatal errors on shutdown', 'wp-lister-for-ebay' ); ?></option>
								<option value="3" <?php if ( $wpl_php_error_handling == '3' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Show errors inline and on shutdown', 'wp-lister-for-ebay' ); ?></option>
								<option value="6" <?php if ( $wpl_php_error_handling == '6' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Show fatal and non-fatal errors on shutdown', 'wp-lister-for-ebay' ); ?></option>
							</select>

							<label for="wpl-option-ajax_error_handling" class="text_label">
								<?php echo __( 'AJAX error handling', 'wp-lister-for-ebay' ); ?>
								<?php $tip_msg = __( '404 errors for admin-ajax.php should actually never happen and are generally a sign of incorrect server configuration.', 'wp-lister-for-ebay' ) .' '. __('This setting is just a workaround. You should consider moving to a proper hosting provider instead.', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip($tip_msg) ?>
							</label>
							<select id="wpl-option-ajax_error_handling" name="wpl_e2e_ajax_error_handling" class=" required-entry select">
								<option value="halt" <?php if ( $wpl_ajax_error_handling == 'halt' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Halt on error', 'wp-lister-for-ebay' ); ?> (default)</option>
								<option value="skip" <?php if ( $wpl_ajax_error_handling == 'skip' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Continue with next item', 'wp-lister-for-ebay' ); ?></option>
								<option value="retry" <?php if ( $wpl_ajax_error_handling == 'retry' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Try again', 'wp-lister-for-ebay' ); ?></option>
							</select>

							<label for="wpl-option-eps_xfer_mode" class="text_label">
								<?php echo __( 'EPS transfer mode', 'wp-lister-for-ebay' ); ?>
								<?php $tip_msg = __( 'If you have trouble uploading your images to EPS (eBay Picture Service), set the transfer mode to active.', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip($tip_msg) ?>
							</label>
							<select id="wpl-option-eps_xfer_mode" name="wpl_e2e_eps_xfer_mode" class=" required-entry select">
								<option value="passive" <?php if ( $wpl_eps_xfer_mode == 'passive' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Passive', 'wp-lister-for-ebay' ); ?> (default)</option>
								<option value="active"  <?php if ( $wpl_eps_xfer_mode == 'active'  ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Active', 'wp-lister-for-ebay' ); ?></option>
							</select>

							<label for="wpl-option-disable_variations" class="text_label">
								<?php echo __( 'Disable variations', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('This is intended to work around an issue with the eBay API and will force using AddItem instead of AddFixedPriceItem, RelistItem instead of RelistFixedPriceItem, etc.<br>Do not enable this unless you do not want to list variations!') ?>
							</label>
							<select id="wpl-option-disable_variations" name="wpl_e2e_disable_variations" class=" required-entry select">
								<option value="0" <?php if ( $wpl_disable_variations == '0' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (default)</option>
								<option value="1" <?php if ( $wpl_disable_variations == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
							</select>

							<label for="wpl-option-disable_compat_list" class="text_label">
								<?php echo __( 'Disable parts compatibility', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('This is intended to work around an issue with the eBay API and will omit Parts Compatibility Lists from being submitted to eBay when an item is listed or revised.') ?>
							</label>
							<select id="wpl-option-disable_compat_list" name="wpl_e2e_disable_compat_list" class=" required-entry select">
								<option value="0" <?php if ( $wpl_disable_compat_list == '0' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (default)</option>
								<option value="1" <?php if ( $wpl_disable_compat_list == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
							</select>

							<label for="wpl-option-enable_item_edit_link" class="text_label">
								<?php echo __( 'Allow direct editing', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Shows an additional "edit" link on the listing page, which allows you to edit the listing database fields directly. It is not recommended to use this option at all - all your changes will be overwritten when the linked product is updated again!') ?>
							</label>
							<select id="wpl-option-enable_item_edit_link" name="wpl_e2e_enable_item_edit_link" class=" required-entry select">
								<option value="0" <?php if ( $wpl_enable_item_edit_link == '0' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (default)</option>
								<option value="1" <?php if ( $wpl_enable_item_edit_link == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
							</select>

							<label for="wpl-option-force_table_items_limit" class="text_label">
								<?php echo __( 'Limit displayed items', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('If you can not open the listings or orders page or receive a timeout error when doing so, you can use this option to temporarily limit the maxmimum number of displayed listings or orders.') ?>
							</label>
							<select id="wpl-option-force_table_items_limit" name="wpl_e2e_force_table_items_limit" class=" required-entry select">
								<option value=""><?php echo __( 'No limit', 'wp-lister-for-ebay' ); ?> (default) </option>
								<option value="1" <?php if ( $wpl_force_table_items_limit == '1' ): ?>selected="selected"<?php endif; ?>>1 item</option>
								<option value="2" <?php if ( $wpl_force_table_items_limit == '2' ): ?>selected="selected"<?php endif; ?>>2 items</option>
								<option value="3" <?php if ( $wpl_force_table_items_limit == '3' ): ?>selected="selected"<?php endif; ?>>3 items</option>
								<option value="5" <?php if ( $wpl_force_table_items_limit == '5' ): ?>selected="selected"<?php endif; ?>>5 items</option>
								<option value="10" <?php if ( $wpl_force_table_items_limit == '10' ): ?>selected="selected"<?php endif; ?>>10 items</option>
							</select>

							<label for="wpl-option-apply_profile_batch_size" class="text_label">
								<?php echo __( 'Apply profiles in batches of', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('If your server times out or runs out of memory when applying a profile (or template) to a huge number of items you may have to lower this setting.') ?>
							</label>
							<select id="wpl-option-apply_profile_batch_size" name="wpl_e2e_apply_profile_batch_size" class=" required-entry select">
								<option value="20"   <?php if ( $wpl_apply_profile_batch_size == '20'   ): ?>selected="selected"<?php endif; ?>>20 items</option>
								<option value="50"   <?php if ( $wpl_apply_profile_batch_size == '50'   ): ?>selected="selected"<?php endif; ?>>50 items</option>
								<option value="100"  <?php if ( $wpl_apply_profile_batch_size == '100'  ): ?>selected="selected"<?php endif; ?>>100 items</option>
								<option value="200"  <?php if ( $wpl_apply_profile_batch_size == '200'  ): ?>selected="selected"<?php endif; ?>>200 items</option>
								<option value="500"  <?php if ( $wpl_apply_profile_batch_size == '500'  ): ?>selected="selected"<?php endif; ?>>500 items</option>
								<option value="1000" <?php if ( $wpl_apply_profile_batch_size == '1000' ): ?>selected="selected"<?php endif; ?>>1000 items (default)</option>
							</select>

							<label for="wpl-option-inventory_check_batch_size" class="text_label">
								<?php echo __( 'Check inventory in batches of', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('If your server times out or runs out of memory when using the inventory check tool you may have to lower this setting.') ?>
							</label>
							<select id="wpl-option-inventory_check_batch_size" name="wpl_e2e_inventory_check_batch_size" class=" required-entry select">
								<option value="20"   <?php if ( $wpl_inventory_check_batch_size == '20'   ): ?>selected="selected"<?php endif; ?>>20 items</option>
								<option value="50"   <?php if ( $wpl_inventory_check_batch_size == '50'   ): ?>selected="selected"<?php endif; ?>>50 items</option>
								<option value="100"  <?php if ( $wpl_inventory_check_batch_size == '100'  ): ?>selected="selected"<?php endif; ?>>100 items</option>
								<option value="200"  <?php if ( $wpl_inventory_check_batch_size == '200'  ): ?>selected="selected"<?php endif; ?>>200 items (default)</option>
								<option value="500"  <?php if ( $wpl_inventory_check_batch_size == '500'  ): ?>selected="selected"<?php endif; ?>>500 items</option>
							</select>

							<label for="wpl-option-fetch_orders_page_size" class="text_label">
								<?php echo __( 'Limit scheduled order fetching', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('If your server times out or runs out of memory when fetching orders from eBay you may have to lower this setting.<br><br>This only applies to the automatic background process of fetching orders. It has no effect on fetching orders manually from the Orders page.') ?>
							</label>
							<select id="wpl-option-fetch_orders_page_size" name="wpl_e2e_fetch_orders_page_size" class=" required-entry select">
								<option value="25"   <?php if ( $wpl_fetch_orders_page_size == '25'   ): ?>selected="selected"<?php endif; ?>>25 orders</option>
								<option value="50"   <?php if ( $wpl_fetch_orders_page_size == '50'   ): ?>selected="selected"<?php endif; ?>>50 orders (default)</option>
								<option value="75"   <?php if ( $wpl_fetch_orders_page_size == '75'   ): ?>selected="selected"<?php endif; ?>>75 orders</option>
								<option value="100"  <?php if ( $wpl_fetch_orders_page_size == '100'  ): ?>selected="selected"<?php endif; ?>>100 orders</option>
							</select>

							<label for="wpl-option-grid_page_size" class="text_label">
								<?php echo __( 'Grid Editor page size', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Set the maximum number of items to load into the grid editor at once.') ?>
							</label>
							<select id="wpl-option-grid_page_size" name="wpl_e2e_grid_page_size" class=" required-entry select">
								<option value="100"    <?php if ( $wpl_grid_page_size == '100'    ): ?>selected="selected"<?php endif; ?>>100 items</option>
								<option value="200"    <?php if ( $wpl_grid_page_size == '200'    ): ?>selected="selected"<?php endif; ?>>200 items</option>
								<option value="500"    <?php if ( $wpl_grid_page_size == '500'    ): ?>selected="selected"<?php endif; ?>>500 items</option>
								<option value="1000"   <?php if ( $wpl_grid_page_size == '1000'   ): ?>selected="selected"<?php endif; ?>>1000 items</option>
								<option value="2000"   <?php if ( $wpl_grid_page_size == '2000'   ): ?>selected="selected"<?php endif; ?>>2000 items</option>
								<option value="5000"   <?php if ( $wpl_grid_page_size == '5000'   ): ?>selected="selected"<?php endif; ?>>5000 items</option>
								<option value="10000"  <?php if ( $wpl_grid_page_size == '10000'  ): ?>selected="selected"<?php endif; ?>>10000 items (default)</option>
							</select>

                            <label for="wpl-option-disable_profile_popup_errors" class="text_label">
                                <?php _e( 'Disable profile pop-up errors', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip( 'Disable the errors that pop up when saving a profile' ); ?>
                            </label>
                            <select id="wpl-option-disable_profile_popup_errors" name="wpl_e2e_disable_profile_popup_errors" class=" required-entry select">
                                <option value="0" <?php selected( $wpl_disable_profile_popup_errors, 0 ); ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (default)</option>
                                <option value="1" <?php selected( $wpl_disable_profile_popup_errors, 1 ); ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
                            </select>

                            <label for="wpl-option-item_specifics_cache" class="text_label">
                                <?php _e( 'Disable Item Specifics Cache', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip( 'Disable the cache to force WP-Lister to pull the item specifics from eBay.' ); ?>
                            </label>
                            <select id="wpl-option-item_specifics_cache" name="wpl_e2e_disable_item_specifics_cache" class=" required-entry select">
                                <option value="0" <?php selected( $wpl_disable_item_specifics_cache, 0 ); ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (default)</option>
                                <option value="1" <?php selected( $wpl_disable_item_specifics_cache, 1 ); ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
                            </select>

                            <label for="wpl-option-item_specifics_limit" class="text_label">
                                <?php _e( 'Item Specifics Limit', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip( 'Set the limit of the number of item specifics to pull from eBay' ); ?>
                            </label>
                            <input type="number" min="0" value="<?php echo esc_attr( $wpl_item_specifics_limit ); ?>" placeholder="0" id="wpl-option-item_specifics_limit" name="wpl_e2e_item_specifics_limit" class="text_input" />

                            <label for="wpl-option-revise_all_listings_limit" class="text_label">
                                <?php _e( 'Revise Changed Items Limit', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip( 'Add a limit to the Revise All Changed Listings action if you are getting errors due to the large amount of changed listings to process.' ); ?>
                            </label>
                            <input type="number" min="0" value="<?php echo esc_attr( $wpl_revise_all_listings_limit ); ?>" placeholder="0" id="wpl-option-revise_all_listings_limit" name="wpl_e2e_revise_all_listings_limit" class="text_input" />

							<label for="wpl-option-log_record_limit" class="text_label">
								<?php echo __( 'Log entry size limit', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Limit the maximum size of a single log record. The default value is 4k.') ?>
							</label>
							<select id="wpl-option-log_record_limit" name="wpl_e2e_log_record_limit" class=" required-entry select">
								<option value="4096"  <?php if ( $wpl_log_record_limit == '4096' ):  ?>selected="selected"<?php endif; ?>>4 kb (default)</option>
								<option value="8192"  <?php if ( $wpl_log_record_limit == '8192' ):  ?>selected="selected"<?php endif; ?>>8 kb</option>
								<option value="64000" <?php if ( $wpl_log_record_limit == '64000' ): ?>selected="selected"<?php endif; ?>>64 kb</option>
							</select>

							<label for="wpl-option-xml_formatter" class="text_label">
								<?php echo __( 'XML Beautifier', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Select which XML formatter should be used to display log records. If the default settings does not work, switch to the built in formatter.') ?>
							</label>
							<select id="wpl-option-xml_formatter" name="wpl_e2e_xml_formatter" class=" required-entry select">
								<option value="default" <?php if ( $wpl_xml_formatter == 'default' ): ?>selected="selected"<?php endif; ?>>auto detect</option>
								<option value="custom"  <?php if ( $wpl_xml_formatter == 'custom' ):  ?>selected="selected"<?php endif; ?>>force built in XML formatter (default)</option>
							</select>


							<label for="wpl-ignore_orders_before_ts" class="text_label">
								<?php echo __( 'Ignore orders before', 'wp-lister-for-ebay' ) ?>
								<?php wplister_tooltip('This is where WP-Lister remembers when it was connected to your eBay account. Orders placed before that date will be ignored.') ?>
							</label>
							<input type="text" name="wpl_e2e_ignore_orders_before_ts" id="wpl-ignore_orders_before_ts" value="<?php echo $wpl_ignore_orders_before_ts ? gmdate('Y-m-d H:i:s T',$wpl_ignore_orders_before_ts) : ''; ?>" class="text_input" />
							<p class="desc" style="display: block;">
								Example: <?php echo gmdate('Y-m-d H:i:s T') ?>
							</p>

						</div>
					</div>

					<div class="postbox dev_box" id="DeveloperToolBox" style="display:none;">
						<h3 class="hndle"><span><?php echo __( 'Developer options', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<label for="wpl-text-log_level" class="text_label"><?php echo __( 'Log to logfile', 'wp-lister-for-ebay' ); ?></label>
							<select id="wpl-text-log_level" name="wpl_e2e_text_log_level" title="Logging" class=" required-entry select">
								<option value=""> -- <?php echo __( 'no logfile', 'wp-lister-for-ebay' ); ?> -- </option>
								<option value="2" <?php if ( $wpl_text_log_level == '2' ): ?>selected="selected"<?php endif; ?>>Error</option>
								<option value="3" <?php if ( $wpl_text_log_level == '3' ): ?>selected="selected"<?php endif; ?>>Critical</option>
								<option value="4" <?php if ( $wpl_text_log_level == '4' ): ?>selected="selected"<?php endif; ?>>Warning</option>
								<option value="5" <?php if ( $wpl_text_log_level == '5' ): ?>selected="selected"<?php endif; ?>>Notice</option>
								<option value="6" <?php if ( $wpl_text_log_level == '6' ): ?>selected="selected"<?php endif; ?>>Info</option>
								<option value="7" <?php if ( $wpl_text_log_level == '7' ): ?>selected="selected"<?php endif; ?>>Debug</option>
								<option value="9" <?php if ( $wpl_text_log_level == '9' ): ?>selected="selected"<?php endif; ?>>All</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Write debug information to logfile.', 'wp-lister-for-ebay' ); ?>
								<?php if ( $wpl_text_log_level > 1 ): ?>
									<?php $wpl_log_size = file_exists( WPLE()->logger->file ) ? filesize( WPLE()->logger->file ) : 0; ?>
									<?php echo __( 'Current log file size', 'wp-lister-for-ebay' ); ?>: <?php echo round($wpl_log_size/1024/1024,1) ?> mb
									[<a href="<?php $upl=wp_get_upload_dir(); echo $upl['baseurl'] ?>/wp-lister/wplister.log" target="_blank">view log</a>]
									<?php if ( file_exists( WPLE()->logger->file_prev ) ) : ?>
										[<a href="<?php $upl=wp_get_upload_dir(); echo $upl['baseurl'] ?>/wp-lister/wplister-old.log" target="_blank">view previous log</a>]
										(<?php echo round( filesize( WPLE()->logger->file_prev ) /1024/1024,1) ?> mb)
									<?php endif; ?>
								<?php endif; ?>
							</p>

							<label for="wpl-option-log_include_authinfo" class="text_label">
								<?php echo __( 'Include auth debug info in log', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Collect additional debug information when the database log option is enabled.') ?>
							</label>
							<select id="wpl-option-log_include_authinfo" name="wpl_e2e_log_include_authinfo" class=" required-entry select">
								<option value="0" <?php if ( $wpl_log_include_authinfo == '0' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Disabled', 'wp-lister-for-ebay' ); ?> (default)</option>
								<option value="1" <?php if ( $wpl_log_include_authinfo == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Enabled', 'wp-lister-for-ebay' ); ?></option>
							</select>

							<label for="wpl-wple_instance" class="text_label"><?php echo __( 'Instance ID', 'wp-lister-for-ebay' ); ?></label>
							<input type="text" name="wpl_e2e_wple_instance" id="wpl-wple_instance" value="<?php echo get_option('wple_instance') ?>" class="text_input" />
							<p class="desc" style="display: block;">
								Don't change this unless you migrated your installation to a different domain.
							</p>

						</div>
					</div>


				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 -->



		</div> <!-- #post-body -->
		<br class="clear">
	</div> <!-- #poststuff -->

	</form>


</div>
