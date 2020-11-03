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

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<div id="postbox-container-1" class="postbox-container">
				<div id="side-sortables" class="meta-box">


					<!-- first sidebox -->
					<div class="postbox" id="submitdiv">
						<!--<div title="Click to toggle" class="handlediv"><br></div>-->
						<h3 class="hndle"><span><?php echo __( 'Sync Status', 'wp-lister-for-ebay' ); ?></span></h3>
						<div class="inside">

							<div id="submitpost" class="submitbox">

								<div id="misc-publishing-actions">
									<div class="misc-pub-section">
									<?php if ( empty( WPLE()->accounts ) ): ?>
										<p><?php echo __( 'No eBay account has been set up yet.', 'wp-lister-for-ebay' ) ?></p>
									<?php elseif ( $wpl_option_cron_auctions && $wpl_option_handle_stock ): ?>
										<p><?php echo __( 'Sync is enabled.', 'wp-lister-for-ebay' ) ?></p>
										<p><?php echo __( 'Sales will be synchronized between WooCommerce and eBay.', 'wp-lister-for-ebay' ) ?></p>
									<?php elseif ( WPLE_IS_LITE_VERSION ): ?>
										<p><?php echo __( 'Sync is not available in WP-Lister Lite.', 'wp-lister-for-ebay' ) ?></p>
										<p><?php echo __( 'To synchronize sales across eBay and WooCommerce you need to upgrade to WP-Lister Pro.', 'wp-lister-for-ebay' ) ?></p>
									<?php else: ?>
										<p><?php echo __( 'Sync is currently disabled.', 'wp-lister-for-ebay' ) ?></p>
										<p><?php echo __( 'eBay and WooCommerce sales will not be synchronized!', 'wp-lister-for-ebay' ) ?></p>
									<?php endif; ?>
									</div>
								</div>

								<div id="major-publishing-actions">

									<div id="publishing-action">
										<input type="submit" value="<?php echo __( 'Save Settings', 'wp-lister-for-ebay' ); ?>" id="save_settings" class="button-primary" name="save">
									</div>
									<div class="clear"></div>
								</div>

							</div>

						</div>
					</div>

					<?php if ( $wpl_is_staging_site ) : ?>
					<div class="postbox" id="StagingSiteBox">
						<h3 class="hndle"><span><?php echo __( 'Staging Site', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">
							<p>
								<span style="color:darkred; font-weight:bold">
									Note: Automatic background updates and order creation have been disabled on this staging site.
								</span>
							</p>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( get_option( 'wplister_cron_auctions' ) ) : ?>
					<div class="postbox" id="UpdateScheduleBox">
						<h3 class="hndle"><span><?php echo __( 'Update Schedule', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<p>
							<?php if ( wp_next_scheduled( 'wplister_update_auctions' ) ) : ?>
								<?php echo __( 'Next scheduled update', 'wp-lister-for-ebay' ); ?> 
								<?php echo human_time_diff( wp_next_scheduled( 'wplister_update_auctions' ), current_time('timestamp',1) ) ?>
								<?php echo wp_next_scheduled( 'wplister_update_auctions' ) < current_time('timestamp',1) ? 'ago' : '' ?>
							<?php elseif ( $wpl_option_cron_auctions == 'external' ) : ?>
								<?php echo __( 'Background updates are handled by an external cron job.', 'wp-lister-for-ebay' ); ?> 
								<a href="#TB_inline?height=420&width=900&inlineId=cron_setup_instructions" class="thickbox">
									<?php echo __( 'Details', 'wp-lister-for-ebay' ); ?>
								</a>

								<div id="cron_setup_instructions" style="display: none;">
									<h2>
										<?php echo __( 'How to set up an external cron job', 'wp-lister-for-ebay' ); ?>
									</h2>
									<p>
										<?php echo __( 'Luckily, you don\'t have to be a server admin to set up an external cron job.', 'wp-lister-for-ebay' ); ?>
										<?php echo __( 'You can ask your server admin to set up a cron job on your own server - or use a 3rd party web based cron service, which provides a user friendly interface and additional features for a small annual fee.', 'wp-lister-for-ebay' ); ?>
									</p>

									<h3>
										<?php echo __( 'Option A: Web cron service', 'wp-lister-for-ebay' ); ?>
									</h3>
									<p>
										<?php $ec_link = '<a href="https://www.easycron.com/" target="_blank">www.easycron.com</a>' ?>
										<?php echo sprintf( __( 'The easiest way to set up a cron job is to sign up with %s and use the following URL to create a new task.', 'wp-lister-for-ebay' ), $ec_link ); ?><br>
									</p>
									<code>
										<?php echo bloginfo('url') ?>/wp-admin/admin-ajax.php?action=wplister_run_scheduled_tasks
									</code>

									<h3>
										<?php echo __( 'Option B: Server cron job', 'wp-lister-for-ebay' ); ?>
									</h3>
									<p>
										<?php echo __( 'If you prefer to set up a cron job on your own server you can create a cron job that will execute the following command:', 'wp-lister-for-ebay' ); ?>
									</p>

									<code style="font-size:0.8em;">
										wget -q -O - <?php echo bloginfo('url') ?>/wp-admin/admin-ajax.php?action=wplister_run_scheduled_tasks >/dev/null 2>&1
									</code>

									<p>
										<?php echo __( 'Note: Your cron job should run at least every 15 minutes but not more often than every 5 minutes.', 'wp-lister-for-ebay' ); ?>
									</p>
								</div>

							<?php else: ?>
								<span style="color:darkred; font-weight:bold">
									Warning: Update schedule is disabled.
								</span></p><p>
								Please click the "Save Settings" button above in order to reset the update schedule.
							<?php endif; ?>
							</p>

							<?php if ( get_option('wplister_cron_last_run') ) : ?>
							<p>
								<?php echo __( 'Last run', 'wp-lister-for-ebay' ); ?>: 
								<?php echo human_time_diff( get_option('wplister_cron_last_run'), current_time('timestamp',1) ) ?> ago
							</p>
							<?php endif; ?>

						</div>
					</div>
					<?php endif; ?>

				</div>
			</div> <!-- #postbox-container-1 -->


			<!-- #postbox-container-2 -->
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables ui-sortable">
					
				<form method="post" id="settingsForm" action="<?php echo $wpl_form_action; ?>">
                    <?php wp_nonce_field( 'wplister_save_settings' ); ?>
					<input type="hidden" name="action" value="save_wplister_settings" >

					<div class="postbox" id="UpdateOptionBox">
						<h3 class="hndle"><span><?php echo __( 'Background Tasks', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">
							<!-- <p><?php echo __( 'Enable to update listings and transactions using WP-Cron.', 'wp-lister-for-ebay' ); ?></p> -->

							<label for="wpl-option-cron_auctions" class="text_label">
								<?php echo __( 'Update interval', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('Select how often WP-Lister should run background jobs like checking for new sales on eBay, fetching messages, updating ended items, processing items scheduled for auto relist, etc.<br><br>It is recommended to use an external cron job or set this interval to 5 - 15 minutes.<br><br>Setting the update interval to <i>manually</i> will disable all background tasks and should only be used for testing and debugging but never on a live production site.') ?>
							</label>
							<select id="wpl-option-cron_auctions" name="wpl_e2e_option_cron_auctions" class=" required-entry select">
								<option value="fifteen_min" <?php if ( $wpl_option_cron_auctions == 'fifteen_min' ): ?>selected="selected"<?php endif; ?>><?php echo __( '15 min.', 'wp-lister-for-ebay' ) ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="thirty_min"  <?php if ( $wpl_option_cron_auctions == 'thirty_min'  ): ?>selected="selected"<?php endif; ?>><?php echo __( '30 min.', 'wp-lister-for-ebay' ) ?></option>
								<option value="hourly"      <?php if ( $wpl_option_cron_auctions == 'hourly'      ): ?>selected="selected"<?php endif; ?>><?php echo __( 'hourly', 'wp-lister-for-ebay' ) ?></option>
								<option value="daily"       <?php if ( $wpl_option_cron_auctions == 'daily'       ): ?>selected="selected"<?php endif; ?>><?php echo __( 'daily', 'wp-lister-for-ebay' ) ?> (<?php _e('not recommended', 'wp-lister-for-ebay' ) ?>)</option>
								<option value=""            <?php if ( $wpl_option_cron_auctions == ''            ): ?>selected="selected"<?php endif; ?>><?php echo __( 'manually', 'wp-lister-for-ebay' ) ?> (<?php _e('not recommended', 'wp-lister-for-ebay' ) ?>)</option>
								<option value="external"    <?php if ( $wpl_option_cron_auctions == 'external'    ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Use external cron job', 'wp-lister-for-ebay' ) ?> (<?php _e('recommended', 'wp-lister-for-ebay' ) ?>)</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Select how often to run background jobs, like checking for new sales on eBay.', 'wp-lister-for-ebay' ); ?>
							</p>

                            <label for="wpl-background_revisions" class="text_label">
                                <?php echo __( 'Push changes to eBay', 'wp-lister-for-ebay' ) ?>
                                <?php wplister_tooltip('With this option enabled, WP-Lister will periodically scan for and automatically revise changed listings in the background.<br><br>With this option turned off, only <i>locked</i> listings will be revised automatically. All other items will stay "changed" when modified, until they are manually revised by the user.') ?>
                            </label>
                            <select id="wpl-background_revisions" name="wpl_e2e_background_revisions" class="required-entry select">
                                <option value="0" <?php selected( $wpl_background_revisions, 0 ); ?>><?php echo __( 'Off', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
                                <option value="1" <?php selected( $wpl_background_revisions, 1 ); ?>><?php echo __( 'Yes, push changes automatically', 'wp-lister-for-ebay' ); ?></option>
                            </select>
                            <p class="desc" style="display: block;">
                                <?php echo __( 'Enable this to revise changed items automatically in the background.', 'wp-lister-for-ebay' ); ?>
                            </p>


						</div>
					</div>



					<div class="postbox" id="OtherSettingsBox">
						<h3 class="hndle"><span><?php echo __( 'Other Options', 'wp-lister-for-ebay' ) ?></span></h3>
						<div class="inside">

							<label for="wpl-enable_grid_editor" class="text_label">
								<?php echo __( 'Enable Grid Editor', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('The grid editor is still under active development and should be considered beta, which is why it is disabled by default.') ?>
							</label>
							<select id="wpl-enable_grid_editor" name="wpl_e2e_enable_grid_editor" class="required-entry select">
								<option value=""  <?php if ( $wpl_enable_grid_editor == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="1" <?php if ( $wpl_enable_grid_editor == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?> (beta)</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Enable the grid editor.', 'wp-lister-for-ebay' ); ?>
								<?php echo __( 'Please report any issues to support.', 'wp-lister-for-ebay' ); ?>
							</p>


							<label for="wpl-local_auction_display" class="text_label">
								<?php echo __( 'Link auctions to eBay', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('In order to prevent selling an item in WooCommerce which is currently on auction, WP-Lister can replace the "Add to cart" button with a "View on eBay" button.') ?>
							</label>
							<select id="wpl-local_auction_display" name="wpl_e2e_local_auction_display" class=" required-entry select">
								<option value="off" 	<?php if ( $wpl_local_auction_display == 'off'    ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Off', 'wp-lister-for-ebay' ); ?> (<?php _e('default', 'wp-lister-for-ebay' ); ?>)</option>
								<option value="always"  <?php if ( $wpl_local_auction_display == 'always' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Always show link to eBay for products on auction', 'wp-lister-for-ebay' ); ?></option>
								<option value="forced"  <?php if ( $wpl_local_auction_display == 'forced' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Always show link to eBay for auctions and fixed price items', 'wp-lister-for-ebay' ); ?> (<?php _e('not recommended', 'wp-lister-for-ebay' ); ?>)</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __( 'Enable this to modify the product details page for items currently on auction.', 'wp-lister-for-ebay' ); ?>
							</p>

						</div>
					</div>


				</form>

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






	<script type="text/javascript">
		jQuery( document ).ready(
			function () {
		
				// save changes button
				jQuery('#save_settings').click( function() {					

					// // handle input fields outside of form
					// var paypal_address = jQuery('#wpl-text_paypal_email-field').first().attr('value');
					// jQuery('#wpl_text_paypal_email').attr('value', paypal_address );

					jQuery('#settingsForm').first().submit();
					
				});

			}
		);
	
	</script>


</div>
