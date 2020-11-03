<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>
<?php
    $thumbsize = get_option( 'wplister_thumbs_display_size', 0 );
    switch ($thumbsize) {
        case '1':
            $maxWidthHeight = 100;
            break;
        
        case '2':
            $maxWidthHeight = 150;
            break;
        
        default:
            $maxWidthHeight = 70;
            break;
    }
 ?>

<style type="text/css">

	td.column-price, 
	td.column-fees {
		text-align: right;
	}
	th.column-auction_title {
		width: 25%;
	}
	th.column-img {
		width: <?php echo $maxWidthHeight ?>px;
	}
	td.column-img {
		padding-top: 5px;
		padding-bottom: 0px;
        text-align: center;
	}
	td.column-img img {
		max-width:  <?php echo $maxWidthHeight ?>px;
		max-height: <?php echo $maxWidthHeight ?>px;
		border: 1px solid #eee;
		width: auto !important;
		height: auto !important;
	}
	
	td.column-auction_title a.product_title_link {
		color: #555;
	}
	td.column-auction_title a.product_title_link:hover {
		/*color: #21759B;*/
		color: #D54E21;
	}
	td.column-auction_title a.missing_product_title_link {
		color: #D54E21;
	}

	.tablenav .actions a.wpl_job_button {
		display: inline-block;
		margin: 0;
		margin-top: 1px;
		margin-right: 5px;
	}

	#TB_window table.variations_table {
		width: 99%
	}
	#TB_window table.variations_table th {
		border-bottom: 1px solid #aaa;
		padding: 4px 9px;
	}
	#TB_window table.variations_table td {
		border-bottom: 1px solid #ccc;
		padding: 4px 9px;
	}

</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/hammer-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<h2>eBay <?php echo __( 'Listings', 'wp-lister-for-ebay' ) ?></h2>
	<?php echo $wpl_message ?>

	<!-- show listings table -->
	<?php $wpl_listingsTable->views(); ?>
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="listings-filter" method="get" action="<?php echo $wpl_form_action; ?>" >
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo wp_create_nonce( 'bulk-auctions' ); ?>">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <input type="hidden" name="listing_status" value="<?php echo isset($_REQUEST['listing_status']) ? esc_attr($_REQUEST['listing_status']) : ''; ?>" />
        <!-- Now we can render the completed list table -->
		<?php $wpl_listingsTable->search_box( __( 'Search', 'wp-lister-for-ebay' ), 'listing-search-input' ); ?>
        <?php $wpl_listingsTable->display() ?>
    </form>

	<br style="clear:both;"/>


	<div class="submit" style="">

		<?php if ( isset($_REQUEST['listing_status']) && ( $_REQUEST['listing_status'] == 'archived' ) ) : ?>

			<form method="post" action="<?php echo $wpl_form_action; ?>">
				<div class="submit" style="padding-top: 0; float: left;">
					<?php wp_nonce_field( 'wplister_clean_listing_archive' ); ?>
					<input type="hidden" name="action" value="wple_clean_listing_archive" />
					<input type="hidden" name="listing_status" value="archived" />
					<input type="submit" value="<?php echo __( 'Clean Archive', 'wp-lister-for-ebay' ) ?>" name="submit" class="button"
						   title="<?php echo __( 'Delete all listings which have never been listed from the archive.', 'wp-lister-for-ebay' ) ?>">
				</div>
			</form>

		<?php else : ?>

			<a id="btn_verify_all_prepared_items" class="btn_verify_all_prepared_items button button-primary wpl_job_button"
			   title="<?php echo __( 'Verify all prepared items with eBay and get listing fees.', 'wp-lister-for-ebay' ) ?>"
				><?php echo __( 'Verify all prepared items', 'wp-lister-for-ebay' ); ?></a>

			<?php if ( current_user_can( 'publish_ebay_listings' ) ) : ?>

				<a id="btn_publish_all_verified_items" class="btn_publish_all_verified_items button button-primary wpl_job_button"
				   title="<?php echo __( 'Publish all verified items on eBay.', 'wp-lister-for-ebay' ) ?>"
					><?php echo __( 'Publish all verified items', 'wp-lister-for-ebay' ); ?></a>

				<a id="btn_publish_all_prepared_items" class="btn_publish_all_prepared_items button button-primary wpl_job_button"
				   title="<?php echo __( 'Publish all prepared items on eBay.', 'wp-lister-for-ebay' ) ?>"
					><?php echo __( 'Publish all prepared items', 'wp-lister-for-ebay' ); ?></a>

				<a id="btn_revise_all_changed_items" class="btn_revise_all_changed_items button button-primary wpl_job_button"
				   title="<?php echo __( 'Revise all changed items on eBay.', 'wp-lister-for-ebay' ) ?>"
					><?php echo __( 'Revise all changed items', 'wp-lister-for-ebay' ); ?></a>

				<a id="btn_update_all_published_items" class="btn_update_all_published_items button button-primary wpl_job_button"
				   title="<?php echo __( 'Update all published items from eBay.', 'wp-lister-for-ebay' ) .' '. 'Note: This will only update the listing items in WP-Lister. Products in WooCommerce will not be affected.' ?>"
					><?php echo __( 'Update all published items', 'wp-lister-for-ebay' ); ?></a>

			<?php endif; ?>

		<?php endif; ?>

	</div>

<!--
	<br>

	<form method="post" action="<?php echo $wpl_form_action; ?>">
		<div class="submit" style="padding-top: 0; float: left;">
			<?php #wp_nonce_field( 'e2e_tools_page' ); ?>
			<input type="hidden" name="action" value="verify_all_prepared_items" />
			<input type="submit" value="<?php echo __( 'Verify all prepared items', 'wp-lister-for-ebay' ) ?>" name="submit" class="button"
				   title="<?php echo __( 'Verify all prepared items with eBay and get listing fees.', 'wp-lister-for-ebay' ) ?>">
		</div>
	</form>

	<form method="post" action="<?php echo $wpl_form_action; ?>">
		<div class="submit" style="padding-top: 0; float: left; padding-left:15px;">
			<?php #wp_nonce_field( 'e2e_tools_page' ); ?>
			<input type="hidden" name="action" value="publish_all_verified_items" />
			<input type="submit" value="<?php echo __( 'Publish all verified items', 'wp-lister-for-ebay' ) ?>" name="submit" class="button" 
				   title="<?php echo __( 'Publish all verified items on eBay.', 'wp-lister-for-ebay' ) ?>">
		</div>
	</form>

	<form method="post" action="<?php echo $wpl_form_action; ?>">
		<div class="submit" style="padding-top: 0; float: left; padding-left:15px;">
			<?php #wp_nonce_field( 'e2e_tools_page' ); ?>
			<input type="hidden" name="action" value="revise_all_changed_items" />
			<input type="submit" value="<?php echo __( 'Revise all changed items', 'wp-lister-for-ebay' ) ?>" name="submit" class="button" 
				   title="<?php echo __( 'Revise all changed items on eBay.', 'wp-lister-for-ebay' ) ?>">
		</div>
	</form>

	<form method="post" action="<?php echo $wpl_form_action; ?>">
		<div class="submit" style="padding-top: 0; float: left; padding-left:15px;">
			<?php #wp_nonce_field( 'e2e_tools_page' ); ?>
			<input type="hidden" name="action" value="update_all_published_items" />
			<input type="submit" value="<?php echo __( 'Update all published items', 'wp-lister-for-ebay' ) ?>" name="submit" class="button" 
				   title="<?php echo __( 'Update all published items from eBay.', 'wp-lister-for-ebay' ) ?>">
		</div>
	</form>
-->

	<script type="text/javascript">
		jQuery( document ).ready(
			function () {
		
				// ask again before ending items
				jQuery('.row-actions .end_item a').on('click', function() {
					return confirm("<?php echo __( 'Are you sure you want to end this listing?', 'wp-lister-for-ebay' ) ?>");
				})
	
				// ask again before relisting items
				jQuery('.row-actions .relist a').on('click', function() {
					return confirm("<?php echo __( 'Are you sure you want to relist this ended listing?', 'wp-lister-for-ebay' ) ?>");
				})
	
				// ask again before deleting items
				jQuery('.row-actions .delete a').on('click', function() {
					return confirm("<?php echo __( 'Are you sure you want to remove this listing?', 'wp-lister-for-ebay' ) .' '.  __('You should not delete listings which have been recently published on eBay!', 'wp-lister-for-ebay' ) ?>");
				})
				jQuery('#wpl_dupe_details a.delete').on('click', function() {
					return confirm("<?php echo __( 'Are you sure you want to remove this listing?', 'wp-lister-for-ebay' ) .' '.  __('You should not delete listings which have been recently published on eBay!', 'wp-lister-for-ebay' ) ?>");
				})

				// handle bulk actions click
				jQuery(".tablenav .actions input[type='submit'].action").on('click', function() {

					if ( 'doaction'  == this.id ) var selected_action = jQuery("select[name='action']").first().val();
					if ( 'doaction2' == this.id ) var selected_action = jQuery("select[name='action2']").first().val();

					// console.log( this.id );
					// console.log('action',selected_action);

					if ( selected_action == 'wple_delete_listing' ) {
						var confirmed = confirm("<?php echo __( 'Are you sure you want to do this?', 'wp-lister-for-ebay' ) .' '.  __('You should not delete listings which have been recently published on eBay!', 'wp-lister-for-ebay' ) ?>");
						if ( ! confirmed ) return false;
					}

					if ( selected_action == 'wple_reset_status' ) {
						var confirmed = confirm("<?php echo __( 'Are you sure you want to do this?', 'wp-lister-for-ebay' ) .' '.  __('This will reset the status of all selected items to prepared. Only use this if you want to list them as new listings instead of relisting.', 'wp-lister-for-ebay' ) ?>");
						if ( ! confirmed ) return false;
					}

					// create array of selected listing IDs
					var item_ids = [];
					var checked_items = jQuery(".check-column input:checked[name='auction[]']");
					checked_items.each( function(index, checkbox) {
						 item_ids.push( checkbox.value );
						 // console.log( 'checked listing ID', checkbox.value );
					});
					// console.log( item_ids );

					// check if any items were selected
					if ( item_ids.length > 0 ) {
						var params = {
							'listing_ids': item_ids
						}

						if ( 'wple_verify' == selected_action ) {
							WpLister.JobRunner.runJob( 'verifyItems', 'Verifying selected items...', params );
							return false;
						}
						if ( 'wple_publish2e' == selected_action ) {
							WpLister.JobRunner.runJob( 'publishItems', 'Publishing selected items...', params );
							return false;
						}
						if ( 'wple_revise' == selected_action ) {
							WpLister.JobRunner.runJob( 'reviseItems', 'Revising selected items...', params );
							return false;
						}
						if ( 'wple_update' == selected_action ) {
							WpLister.JobRunner.runJob( 'updateItems', 'Updating selected items...', params );
							return false;
						}

						if ( 'wple_end_item' == selected_action ) {
							WpLister.JobRunner.runJob( 'endItems', 'Ending selected items...', params );
							return false;
						}
						if ( 'wple_relist' == selected_action ) {
							WpLister.JobRunner.runJob( 'relistItems', 'Relisting selected items...', params );
							return false;
						}

					}

					return true;

				})

	
			}
		);
	
	</script>


	<?php if ( get_option('wple_job_reapply_profile_id' ) ) : ?>
		<script type="text/javascript">
			jQuery( document ).ready( function () {	
				// auto start reapply profile job
				setTimeout(function() {
					jQuery('#btn_run_delayed_profile_application').click();
				}, 1000); // delays 1 sec
			});
		</script>
	<?php endif; ?>

	<?php if ( get_option('wple_job_reapply_template_id' ) ) : ?>
		<script type="text/javascript">
			jQuery( document ).ready( function () {	
				// auto start reapply template job
				setTimeout(function() {
					jQuery('#btn_run_delayed_template_application').click();
				}, 1000); // delays 1 sec
			});
		</script>
	<?php endif; ?>

	<?php if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'verifyPreparedItemsNow' ) ) : ?>
		<script type="text/javascript">
			jQuery( document ).ready( function () {	
				// auto start verify job
				setTimeout(function() {
					jQuery('#btn_verify_all_prepared_items').click();
				}, 1000); // delays 1 sec
			});
		</script>
	<?php endif; ?>


</div>