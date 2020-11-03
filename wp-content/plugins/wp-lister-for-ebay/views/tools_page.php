<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	.inside p {
		width: 60%;
	}

	a.right,
	input.button {
		float: right;
	}


	.test_results h3 {
		margin-left: .4em;
	}

	.test_results .details {
		margin-left: 3em;
		font-size: 1em;
		color: #999;
		/*margin-bottom: 1em;*/
	}

	.test_results img.inline_status {
		vertical-align: bottom;
		height: 16px;
		margin-left: .5em;
		margin-right: 1em;
	}

</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/hammer-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<!-- <h2><?php echo __( 'Tools', 'wp-lister-for-ebay' ) ?></h2> -->

	<?php include_once( dirname(__FILE__).'/tools_tabs.php' ); ?>
	<?php echo $wpl_message ?>


	<div style="width:640px;" class="postbox-container">
		<div class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">
				
				<div class="postbox" id="InventoryToolBox" style="display:block;">
					<h3 class="hndle"><span><?php echo __( 'Inventory Check', 'wp-lister-for-ebay' ); ?></span></h3>
					<div class="inside">

						<!-- check for out of sync products (published) --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'e2e_tools_page' ); ?>
								<input type="hidden" name="action" value="check_wc_out_of_sync" />
								<input type="hidden" name="mode"   value="published" />
								<input type="hidden" name="prices" value="1" />
								<input type="submit" value="<?php echo __( 'Check product inventory and prices', 'wp-lister-for-ebay' ); ?>" name="submit" class="button button-primary">
								<p><?php echo __( 'Check all published listings and find products with different stock or price in WooCommerce.', 'wp-lister-for-ebay' ); ?>
									<br>
									<small>Note: If you are using price modifiers in your profile, this check could find false positives which are actually in sync.</small>
								</p>
						</form>
						<br style="clear:both;"/>

						<!-- check for out of sync products (published) --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'e2e_tools_page' ); ?>
								<input type="hidden" name="action" value="check_wc_out_of_sync" />
								<input type="hidden" name="mode"   value="published" />
								<input type="hidden" name="prices" value="0" />
								<input type="submit" value="<?php echo __( 'Check product inventory only', 'wp-lister-for-ebay' ); ?>" name="submit" class="button button-primary">
								<p><?php echo __( 'Check all published listings and find products with different stock levels in WooCommerce.', 'wp-lister-for-ebay' ); ?></p>
						</form>
						<br style="clear:both;"/>

						<!-- check for out of sync products (ended) --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'e2e_tools_page' ); ?>
								<input type="hidden" name="action" value="check_wc_out_of_sync" />
								<input type="hidden" name="mode" value="ended" />
								<input type="hidden" name="prices" value="0" />
								<input type="submit" value="<?php echo __( 'Check ended listings', 'wp-lister-for-ebay' ); ?>" name="submit" class="button button-primary">
								<p><?php echo __( 'Check all ended listings and find products with different stock levels in WooCommerce.', 'wp-lister-for-ebay' ); ?>
								</p>
						</form>
						<br style="clear:both;"/>

						<hr>

						<!-- check for sold products that are still in stock --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'e2e_tools_page' ); ?>
								<input type="hidden" name="action" value="check_wc_sold_stock" />
								<input type="submit" value="<?php echo __( 'Check sold listings', 'wp-lister-for-ebay' ); ?>" name="submit" class="button button-primary">
								<p><?php echo __( 'Check all sold listings and find products which are still in stock in WooCommerce.', 'wp-lister-for-ebay' ); ?>
								</p>
						</form>
						<br style="clear:both;"/>

						<!-- check for out of stock products --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'e2e_tools_page' ); ?>
								<input type="hidden" name="action" value="check_wc_out_of_stock" />
								<input type="submit" value="<?php echo __( 'Check out of stock products', 'wp-lister-for-ebay' ); ?>" name="submit" class="button button-primary">
								<p><?php echo __( 'Check all published listings and find products which are out of stock in WooCommerce.', 'wp-lister-for-ebay' ); ?>
								</p>
						</form>
						<br style="clear:both;"/>

						<hr>

						<!-- check order history log for stock reduction mismatch --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'e2e_tools_page' ); ?>
								<input type="hidden" name="action" value="check_wc_stock_reduction_mismatch" />
								<input type="submit" value="<?php echo __( 'Check orders', 'wp-lister-for-ebay' ); ?>" name="submit" class="button button-primary">
								<p><?php echo __( 'Check the history log of all completed orders in WP-Lister for discrepancies in stock reduction.', 'wp-lister-for-ebay' ); ?>
								</p>
						</form>
						<br style="clear:both;"/>

					</div>
				</div> <!-- postbox -->

				<div class="postbox" id="OtherToolBox" style="display:block;">
					<h3 class="hndle"><span><?php echo __( 'Other Tools', 'wp-lister-for-ebay' ); ?></span></h3>
					<div class="inside">

						<!-- check for out of sync products --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'e2e_tools_page' ); ?>
								<input type="hidden" name="action" value="check_ebay_image_requirements" />
								<input type="submit" value="<?php echo __( 'Check product images', 'wp-lister-for-ebay' ); ?>" name="submit" class="button button-primary">
								<p><?php echo __( 'Check all listings for product images smaller than 500px.', 'wp-lister-for-ebay' ); ?></p>
						</form>
						<br style="clear:both;"/>

						<!-- lock all listings --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'e2e_tools_page' ); ?>
								<input type="hidden" name="action" value="wple_lock_all_listings" />
								<input type="submit" value="<?php echo __( 'Lock all items', 'wp-lister-for-ebay' ); ?>" name="submit" class="button button-primary">
								<p><?php echo __( 'Lock all items.', 'wp-lister-for-ebay' ); ?></p>
						</form>
						<br style="clear:both;"/>

						<!-- unlock all listings --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'e2e_tools_page' ); ?>
								<input type="hidden" name="action" value="wple_unlock_all_listings" />
								<input type="submit" value="<?php echo __( 'Unlock all items', 'wp-lister-for-ebay' ); ?>" name="submit" class="button button-primary">
								<p><?php echo __( 'Unlock all items.', 'wp-lister-for-ebay' ); ?></p>
						</form>
						<br style="clear:both;"/>

						<!-- Import WPLA Product IDs --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'e2e_tools_page' ); ?>
								<input type="hidden" name="action" value="import_wpla_product_ids" />
								<input type="submit" value="<?php echo __( 'Import WPLA Product IDs', 'wp-lister-for-ebay' ); ?>" name="submit" class="button button-primary">
								<p><?php echo __( 'Import UPC / EAN from WP-Lister for Amazon.', 'wp-lister-for-ebay' ); ?></p>
						</form>
						<br style="clear:both;"/>

						<!-- fix cost of goods on ebay orders --> 
						<?php if ( class_exists('WC_COG') ) : ?>
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'e2e_tools_page' ); ?>
								<input type="hidden" name="action" value="fix_cog_on_imported_orders" />
								<input type="submit" value="<?php echo __( 'Update cost of goods data', 'wp-lister-for-ebay' ); ?>" name="submit" class="button button-primary">
								<p><?php echo __( 'Fix missing cost of goods data for eBay orders.', 'wp-lister-for-ebay' ); ?></p>
						</form>
						<br style="clear:both;"/>
						<?php endif; ?>

					</div>
				</div> <!-- postbox -->


				<div class="postbox" id="UpdateToolsBox">
					<h3 class="hndle"><span><?php echo __( 'Background Tasks', 'wp-lister-for-ebay' ); ?></span></h3>
					<div class="inside">

						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'e2e_tools_page' ); ?>
								<input type="hidden" name="action" value="wple_run_daily_schedule" />
								<input type="submit" value="<?php echo __( 'Run daily schedule', 'wp-lister-for-ebay' ); ?>" name="submit" class="button button-primary">
								<p>
									<?php echo __( 'Manually trigger the daily task schedule.', 'wp-lister-for-ebay' ); ?>
									(<?php echo sprintf( __( 'Last run: %s ago', 'wp-lister-for-ebay' ), human_time_diff( get_option('wple_daily_cron_last_run') ) ) ?>)
								</p>
						</form>
						<br style="clear:both;"/>

						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'e2e_tools_page' ); ?>
								<input type="hidden" name="action" value="wple_run_update_schedule" />
								<input type="submit" value="<?php echo __( 'Run update schedule', 'wp-lister-for-ebay' ); ?>" name="submit" class="button button-primary">
								<p><?php echo __( 'Manually run scheduled background tasks.', 'wp-lister-for-ebay' ); ?></p>
						</form>
						<br style="clear:both;"/>

					</div>
				</div> <!-- postbox -->


			</div>
		</div>
	</div>

	<br style="clear:both;"/>


</div>


<script type="text/javascript">
	
	// on page load
	jQuery( document ).ready( function () {
	
		// autosubmit next inventory check step
		var autosubmit_url = jQuery("#wple_auto_next_step").attr('href')
		if ( autosubmit_url != undefined ) {
			window.location.href = autosubmit_url;
		}

	});

</script>
