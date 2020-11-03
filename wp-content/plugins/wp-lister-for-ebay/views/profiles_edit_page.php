<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	.postbox h3 {
	    cursor: default;
	}

	#tiptip_holder #tiptip_content {
		max-width: 250px;
	}

    #ShippingOptionsBox .service_table,
    #IntShippingOptionsBox .service_table { 
    	width: 100%; 
    }

</style>

<?php
	$item_details = $wpl_item['details'];
?>

<div class="wrap wplister-page">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/hammer-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<?php if ( $wpl_item['profile_id'] ): ?>
	<h2><?php echo __( 'Edit Profile', 'wp-lister-for-ebay' ) ?></h2>
	<?php else: ?>
	<h2><?php echo __( 'New Profile', 'wp-lister-for-ebay' ) ?></h2>
	<?php endif; ?>
	
	<?php echo $wpl_message ?>

	<form method="post" action="<?php echo $wpl_form_action; ?>">

	<!--
	<div id="titlediv" style="margin-top:10px; margin-bottom:5px; width:60%">
		<div id="titlewrap">
			<label class="hide-if-no-js" style="visibility: hidden; " id="title-prompt-text" for="title">Enter title here</label>
			<input type="text" name="wpl_e2e_profile_name" size="30" tabindex="1" value="<?php echo $wpl_item['profile_name']; ?>" id="title" autocomplete="off">
		</div>
	</div>
	-->

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<div id="postbox-container-1" class="postbox-container">
				<div id="side-sortables" class="meta-box">
					<?php include('profile/edit_sidebar.php') ?>
				</div>
			</div> <!-- #postbox-container-1 -->


			<!-- #postbox-container-2 -->
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables ui-sortable">
					

					<div class="postbox" id="GeneralSettingsBox">
						<h3 class="hndle"><span><?php echo __( 'General eBay settings', 'wp-lister-for-ebay' ); ?></span></h3>
						<div class="inside">

							<div id="titlediv" style="margin-bottom:5px;">
								<div id="titlewrap">
									<label for="wpl-text-profile_description" class="text_label"><?php echo __( 'Profile name', 'wp-lister-for-ebay' ); ?> *</label>
									<input type="text" name="wpl_e2e_profile_name" size="30" value="<?php echo $wpl_item['profile_name']; ?>" id="title" autocomplete="off" style="width:65%;">
								</div>
							</div>

							<label for="wpl-text-profile_description" class="text_label">
								<?php echo __( 'Profile description', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('A profile description is optional and only used within WP-Lister.') ?>
							</label>
							<input type="text" name="wpl_e2e_profile_description" id="wpl-text-profile_description" value="<?php echo str_replace('"','&quot;', $wpl_item['profile_description'] ); ?>" class="text_input" />
							<br class="clear" />

							<label for="wpl-text-auction_type" class="text_label">
								<?php echo __( 'Type', 'wp-lister-for-ebay' ); ?> *
                                <?php wplister_tooltip('Select if you want to list your products as fixed price items or put them on auction. This can be overwritten on the product level.<br>Note: eBay does not allow changing the listing type for already published items.') ?>
							</label>
							<select id="wpl-text-auction_type" name="wpl_e2e_auction_type" title="Type" class=" required-entry select">
								<option value="">-- <?php echo __( 'Please select', 'wp-lister-for-ebay' ); ?> --</option>
								<option value="Chinese" <?php if ( $item_details['auction_type'] == 'Chinese' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Auction', 'wp-lister-for-ebay' ); ?></option>
								<option value="FixedPriceItem" <?php if ( $item_details['auction_type'] == 'FixedPriceItem' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Fixed Price', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<?php if ($wpl_published_listings_count) : ?>
							<p class="desc" style="display: block;">
								<?php echo __( 'Note: eBay does not allow changing the listing type for already published items.', 'wp-lister-for-ebay' ); ?>
							</p>
							<?php endif; ?>

							<label for="wpl-text-start_price" class="text_label">
								<?php echo __( 'Price / Start price', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('You can adjust the price for fixed price listings - or the start price for auctions.<br>Leave empty to use the product price as it is.<br>Note: This option is ignored for locked items!' ) ?>
							</label>
							<input type="text" name="wpl_e2e_start_price" id="wpl-text-start_price" value="<?php echo $item_details['start_price']; ?>" class="text_input" />
							<br class="clear" />

							<div id="wpl-text-fixed_price_container" style="display:none">
							<label for="wpl-text-fixed_price" class="text_label">
								<?php echo __( 'Buy Now Price', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Set a Buy Now Price to enable the Buy Now option for your listing.<br>You can set a custom Buy Now Price on the edit product page as well.' ) ?>
							</label>
							<input type="text" name="wpl_e2e_fixed_price" id="wpl-text-fixed_price" value="<?php echo $item_details['fixed_price']; ?>" class="text_input" />
							<br class="clear" />
							</div>

							<p class="desc" style="display: block;">
								<?php echo __( 'Fixed price (199), percent (+10% / -10%) or fixed change (+5 / -5)', 'wp-lister-for-ebay' ); ?><!br>
								<?php #echo __( 'Leave this empty to use the product price as it is.', 'wp-lister-for-ebay' ); ?>
							</p>


							<label for="wpl-text-listing_duration" class="text_label">
								<?php echo __( 'Duration', 'wp-lister-for-ebay' ); ?> *
                                <?php wplister_tooltip('Set your desired listing duration. eBay fees for GTC (Good `Till Cancelled) listings will be charged every 30 days.') ?>
							</label>
							<select id="wpl-text-listing_duration" name="wpl_e2e_listing_duration" title="Duration" class=" required-entry select">
								<option value="">-- <?php echo __( 'Please select', 'wp-lister-for-ebay' ); ?> --</option>
								<option value="Days_1" <?php if ( $wpl_item['listing_duration'] == 'Days_1' ): ?>selected="selected"<?php endif; ?>>1 <?php echo __( 'Day', 'wp-lister-for-ebay' ); ?></option>
								<option value="Days_3" <?php if ( $wpl_item['listing_duration'] == 'Days_3' ): ?>selected="selected"<?php endif; ?>>3 <?php echo __( 'Days', 'wp-lister-for-ebay' ); ?></option>
								<option value="Days_5" <?php if ( $wpl_item['listing_duration'] == 'Days_5' ): ?>selected="selected"<?php endif; ?>>5 <?php echo __( 'Days', 'wp-lister-for-ebay' ); ?></option>
								<option value="Days_7" <?php if ( $wpl_item['listing_duration'] == 'Days_7' ): ?>selected="selected"<?php endif; ?>>7 <?php echo __( 'Days', 'wp-lister-for-ebay' ); ?></option>
								<option value="Days_10" <?php if ( $wpl_item['listing_duration'] == 'Days_10' ): ?>selected="selected"<?php endif; ?>>10 <?php echo __( 'Days', 'wp-lister-for-ebay' ); ?></option>
								<option value="Days_14" <?php if ( $wpl_item['listing_duration'] == 'Days_14' ): ?>selected="selected"<?php endif; ?>>14 <?php echo __( 'Days', 'wp-lister-for-ebay' ); ?></option>
								<option value="Days_28" <?php if ( $wpl_item['listing_duration'] == 'Days_28' ): ?>selected="selected"<?php endif; ?>>28 <?php echo __( 'Days', 'wp-lister-for-ebay' ); ?></option>
								<option value="Days_30" <?php if ( $wpl_item['listing_duration'] == 'Days_30' ): ?>selected="selected"<?php endif; ?>>30 <?php echo __( 'Days', 'wp-lister-for-ebay' ); ?></option>
								<option value="Days_60" <?php if ( $wpl_item['listing_duration'] == 'Days_60' ): ?>selected="selected"<?php endif; ?>>60 <?php echo __( 'Days', 'wp-lister-for-ebay' ); ?></option>
								<option value="Days_90" <?php if ( $wpl_item['listing_duration'] == 'Days_90' ): ?>selected="selected"<?php endif; ?>>90 <?php echo __( 'Days', 'wp-lister-for-ebay' ); ?></option>
								<option value="GTC"     <?php if ( $wpl_item['listing_duration'] == 'GTC'     ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Good Till Canceled', 'wp-lister-for-ebay' ); ?> (GTC)</option>
							</select>
							<br class="clear" />
							<!--
							<p class="desc" style="display: block;">
								<?php echo __( 'GTC listings will be charged every 30 days.', 'wp-lister-for-ebay' ); ?>
							</p>
							-->


							<label for="wpl-text-condition_id" class="text_label">
								<?php echo __( 'Condition', 'wp-lister-for-ebay' ); ?> *
								<?php if ( $wpl_item['profile_id'] ): ?>
	                                <?php wplister_tooltip('Which item conditions are available depends on the primary eBay category.<br><br>Please select a primary category below to load the available item conditions.<br>    Or you can set a default primary category in <i>eBay &raquo; Settings &raquo; Categories</i>.') ?>
								<?php else: ?>
    	                            <?php wplister_tooltip('Which item conditions are available depends on the primary eBay category.<br><br>Please select a primary category below to load the available item conditions.<br><br>Or you can set a default primary category in <i>eBay &raquo; Settings &raquo; Categories</i>, but <b>first you need to save your profile </b> in order to see the conditions for your default category here.') ?>
								<?php endif; ?>
							</label>
							<select id="wpl-text-condition_id" name="wpl_e2e_condition_id" title="Condition" class=" required-entry select">
							<?php if ( isset( $wpl_available_conditions ) && is_array( $wpl_available_conditions ) ): ?>
								<?php foreach ($wpl_available_conditions as $condition_id => $desc) : ?>
									<option value="<?php echo $condition_id ?>" 
										<?php if ( $item_details['condition_id'] == $condition_id ) : ?>
											selected="selected"
										<?php endif; ?>
										><?php echo $desc ?></option>
								<?php endforeach; ?>
							<?php elseif ( $wpl_available_conditions == 'none' ) : ?>
								<option value="none" selected="selected"><?php echo __( 'none', 'wp-lister-for-ebay' ); ?></option>
							<?php else: ?>
								<option value="1000" <?php echo $item_details['condition_id'] == 1000 ? 'selected="selected"' : '' ?> ><?php echo __( 'New', 'wp-lister-for-ebay' ); ?></option>
								<option value="3000" <?php echo $item_details['condition_id'] == 3000 ? 'selected="selected"' : '' ?> ><?php echo __( 'Used', 'wp-lister-for-ebay' ); ?></option>
							<?php endif; ?>
							</select>
							<br class="clear" />
							<p class="desc" style="display: none;">
								<?php echo __( 'Available conditions may vary for different categories.', 'wp-lister-for-ebay' ); ?>
								<?php echo __( 'You should set the category first.', 'wp-lister-for-ebay' ); ?>
							</p>

							<div id="wpl-text-condition_description_container">
							<label for="wpl-text-condition_description" class="text_label">
								<?php echo __( 'Condition description', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip(__( 'This field should only be used to further clarify the condition of used items.', 'wp-lister-for-ebay' )) ?>
							</label>
							<input type="text" name="wpl_e2e_condition_description" id="wpl-text-condition_description" value="<?php echo esc_attr( @$item_details['condition_description'] ); ?>" class="text_input" />
							<br class="clear" />
							<p class="desc" style="display: none;">
								<?php echo __( 'This field should only be used to further clarify the condition of used items.', 'wp-lister-for-ebay' ); ?>
							</p>
							</div>


							<label for="wpl-text-dispatch_time" class="text_label">
								<?php echo __( 'Handling time', 'wp-lister-for-ebay' ); ?> *
                                <?php wplister_tooltip( __( 'The maximum number of business days a seller commits to for shipping an item to domestic buyers after receiving a cleared payment.', 'wp-lister-for-ebay' ) ) ?>
							</label>
							<select id="wpl-text-dispatch_time" name="wpl_e2e_dispatch_time" title="Condition" class=" required-entry select">
							<?php if ( isset( $wpl_available_dispatch_times ) && is_array( $wpl_available_dispatch_times ) ): ?>
								<?php foreach ($wpl_available_dispatch_times as $dispatch_time => $desc) : ?>
									<option value="<?php echo $dispatch_time ?>" 
										<?php if ( $item_details['dispatch_time'] == $dispatch_time ) : ?>
											selected="selected"
										<?php endif; ?>
										><?php echo $desc ?></option>
								<?php endforeach; ?>
							<?php else: ?>
								<option value="">-- <?php echo __( 'Please select', 'wp-lister-for-ebay' ); ?> --</option>
								<option value="0"  <?php echo $item_details['dispatch_time'] === 0 ? 'selected="selected"' : '' ?> >0 Days</option>
								<option value="1"  <?php echo $item_details['dispatch_time'] ==  1 ? 'selected="selected"' : '' ?> >1 Day</option>
								<option value="2"  <?php echo $item_details['dispatch_time'] ==  2 ? 'selected="selected"' : '' ?> >2 Days</option>
								<option value="3"  <?php echo $item_details['dispatch_time'] ==  3 ? 'selected="selected"' : '' ?> >3 Days</option>
								<option value="4"  <?php echo $item_details['dispatch_time'] ==  4 ? 'selected="selected"' : '' ?> >4 Days</option>
								<option value="5"  <?php echo $item_details['dispatch_time'] ==  5 ? 'selected="selected"' : '' ?> >5 Days</option>
								<option value="10" <?php echo $item_details['dispatch_time'] == 10 ? 'selected="selected"' : '' ?> >10 Days</option>
							<?php endif; ?>
							</select>
							<br class="clear" />
							<p class="desc" style="display: none;">
								<?php echo __( 'The maximum number of business days a seller commits to for shipping an item to domestic buyers after receiving a cleared payment.', 'wp-lister-for-ebay' ); ?>
							</p>


                            <label for="wpl-text-exclude_attributes" class="text_label">
                                <?php echo __( 'Exclude attributes', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('If you want to hide certain product attributes from eBay, enter their names separated by commas here.' ) ?>
                            </label>
                            <input type="text" name="wpl_e2e_exclude_attributes" id="wpl-text-exclude_attributes" value="<?php echo isset($item_details['exclude_attributes']) ? $item_details['exclude_attributes'] : ''; ?>" class="text_input" />
                            <br class="clear" />

						</div>
					</div>


					<?php include('profile/edit_categories.php') ?>
					<?php include('profile/edit_item_specifics.php') ?>
					<?php include('profile/edit_shipping.php') ?>


					<div class="postbox" id="PaymentOptionsBox">
						<h3 class="hndle"><span><?php echo __( 'Payment methods', 'wp-lister-for-ebay' ); ?></span></h3>
						<div class="inside">

							<label for="wpl-text-payment_options" class="text_label"><?php echo __( 'Payment methods', 'wp-lister-for-ebay' ); ?> *</label>
							<table id="payment_options_table" style="width:65%;">
								
								<?php foreach ($item_details['payment_options'] as $service) : ?>
								<tr class="row">
									<td>
										<select name="wpl_e2e_payment_options[][payment_name]" 
												class="required-entry select" style="width:100%;">
											<option value="">-- <?php echo __( 'Please select', 'wp-lister-for-ebay' ); ?> --</option>
											<?php foreach ($wpl_payment_options as $opt) : ?>
												<option value="<?php echo $opt['payment_name'] ?>" 
													<?php if ( @$service['payment_name'] == $opt['payment_name'] ) : ?>
														selected="selected"
													<?php endif; ?>
													><?php echo $opt['payment_description'] ?></option>
											<?php endforeach; ?>
										</select>
									</td><td align="right">
										<input type="button" value="<?php echo __( 'remove', 'wp-lister-for-ebay' ); ?>" class="button" 
											onclick="jQuery(this).parent().parent().remove();" />
									</td>
								</tr>
								<?php endforeach; ?>

							</table>

							<input type="button" value="<?php echo __( 'Add payment method', 'wp-lister-for-ebay' ); ?>" name="btn_add_payment_option" 
								onclick="jQuery('#payment_options_table').find('tr.row').first().clone().appendTo('#payment_options_table');"
								class="button">

							<br class="clear" />


							<?php if ( $wpl_cod_available ) : ?>
							<label for="wpl-text-cod_cost" class="text_label">
								<?php echo __( 'Cash on delivery fee', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Provide the additional fee you want to charge for cash on delivery.') ?>
							</label>
							<input type="text" name="wpl_e2e_cod_cost" id="wpl-text-cod_cost" value="<?php echo @$item_details['cod_cost']; ?>" class="text_input" />
							<br class="clear" />
							<?php endif; ?>

							<label for="wpl-text-payment_instructions" class="text_label">
								<?php echo __( 'Payment instructions', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Payment instructions from the seller to the buyer. These instructions appear on eBay\'s View Item page and on eBay\'s checkout page when the buyer pays for the item. <br><br>
														Sellers usually use this field to specify payment instructions, how soon the item will shipped, feedback instructions, and other reminders that the buyer should be aware of when they bid on or buy an item.<br>
														Note: eBay only allows a maximum of 500 characters.') ?>
							</label>
							<textarea name="wpl_e2e_payment_instructions" id="wpl-text-payment_instructions" class="textarea"><?php echo stripslashes( @$item_details['payment_instructions'] ); ?></textarea>
							<br class="clear" />

							<?php if ( isset( $wpl_seller_payment_profiles ) && is_array( $wpl_seller_payment_profiles ) ): ?>
							<label for="wpl-text-seller_payment_profile_id" class="text_label">
								<?php echo __( 'Payment policy', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Instead of setting your payment details in WP-Lister you can select a predefined payment policy from your eBay account.<br><br>Please note that if you use a predefined payment policy, you might have to use shipping and return policies as well.') ?>
							</label>
							<select id="wpl-text-seller_payment_profile_id" name="wpl_e2e_seller_payment_profile_id" class=" required-entry select">
								<option value="">-- <?php echo __( 'no policy', 'wp-lister-for-ebay' ); ?> --</option>
								<?php foreach ($wpl_seller_payment_profiles as $seller_profile ) : ?>
									<option value="<?php echo $seller_profile->ProfileID ?>" 
										<?php if ( @$item_details['seller_payment_profile_id'] == $seller_profile->ProfileID ) : ?>
											selected="selected"
										<?php endif; ?>
										><?php echo $seller_profile->ProfileName . ' - ' . $seller_profile->ShortSummary ?></option>
								<?php endforeach; ?>
							</select>
							<br class="clear" />
							<?php endif; ?>

						</div>
					</div>


					<div class="postbox" id="ReturnsSettingsBox">
						<h3 class="hndle"><span><?php echo __( 'Return Policy', 'wp-lister-for-ebay' ); ?></span></h3>
						<div class="inside">

							<label for="wpl-text-returns_accepted" class="text_label">
								<?php echo __( 'Enable return policy', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Enable this to include a return policy in your listings. Most categories on most eBay sites require the seller to include a return policy.') ?>
							</label>
							<select id="wpl-text-returns_accepted" name="wpl_e2e_returns_accepted" title="Returns" class=" required-entry select">
								<option value="">-- <?php echo __( 'Please select', 'wp-lister-for-ebay' ); ?> --</option>
								<option value="1" <?php if ( $item_details['returns_accepted'] == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Yes', 'wp-lister-for-ebay' ); ?></option>
								<option value="0" <?php if ( $item_details['returns_accepted'] == '0' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<br class="clear" />

							<div id="returns_details_container">

							<label for="wpl-text-returns_within" class="text_label">
								<?php echo __( 'Returns within', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('The buyer can return the item within this period of time from the day they receive the item. Use the description field to explain the policy details.') ?>
							</label>
							<select id="wpl-text-returns_within" name="wpl_e2e_returns_within" class=" required-entry select">
							<?php // $ReturnsWithinOptions = get_option('wplister_ReturnsWithinOptions') ?>
							<?php if ( isset( $wpl_ReturnsWithinOptions ) && is_array( $wpl_ReturnsWithinOptions ) ): ?>
								<?php foreach ($wpl_ReturnsWithinOptions as $option_id => $desc) : ?>
									<option value="<?php echo $option_id ?>" 
										<?php if ( $item_details['returns_within'] == $option_id ) : ?>
											selected="selected"
										<?php endif; ?>
										><?php echo $desc ?></option>
								<?php endforeach; ?>
							<?php else: ?>
								<option value="">-- <?php echo __( 'not specified', 'wp-lister-for-ebay' ); ?> --</option>
								<option value="Days_10" <?php if ( $item_details['returns_within'] == 'Days_10' ): ?>selected="selected"<?php endif; ?>>10 <?php echo __( 'days', 'wp-lister-for-ebay' ); ?></option>
								<option value="Days_14" <?php if ( $item_details['returns_within'] == 'Days_14' ): ?>selected="selected"<?php endif; ?>>14 <?php echo __( 'days', 'wp-lister-for-ebay' ); ?></option>
								<option value="Days_30" <?php if ( $item_details['returns_within'] == 'Days_30' ): ?>selected="selected"<?php endif; ?>>30 <?php echo __( 'days', 'wp-lister-for-ebay' ); ?></option>
								<option value="Days_60" <?php if ( $item_details['returns_within'] == 'Days_60' ): ?>selected="selected"<?php endif; ?>>60 <?php echo __( 'days', 'wp-lister-for-ebay' ); ?></option>
								<option value="Months_1" <?php if ( $item_details['returns_within'] == 'Months_1' ): ?>selected="selected"<?php endif; ?>>3 <?php echo __( 'month', 'wp-lister-for-ebay' ); ?></option>
							<?php endif; ?>
							</select>
							<br class="clear" />

							<label for="wpl-text-ShippingCostPaidBy" class="text_label">
								<?php echo __( 'Shipping cost paid by', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('The party who pays the shipping cost for a returned item.') ?>
							</label>
							<select id="wpl-text-ShippingCostPaidBy" name="wpl_e2e_ShippingCostPaidBy" class=" required-entry select">
							<?php // $ShippingCostPaidByOptions = get_option('wplister_ShippingCostPaidByOptions') ?>
							<?php if ( isset( $wpl_ShippingCostPaidByOptions ) && is_array( $wpl_ShippingCostPaidByOptions ) ): ?>
								<?php foreach ($wpl_ShippingCostPaidByOptions as $option_id => $desc) : ?>
									<option value="<?php echo $option_id ?>" 
										<?php if ( @$item_details['ShippingCostPaidBy'] == $option_id ) : ?>
											selected="selected"
										<?php endif; ?>
										><?php echo $desc ?></option>
								<?php endforeach; ?>
							<?php else: ?>
								<option value="">-- <?php echo __( 'not specified', 'wp-lister-for-ebay' ); ?> --</option>
								<option value="Buyer"  <?php if ( @$item_details['ShippingCostPaidBy'] == 'Buyer'  ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Buyer', 'wp-lister-for-ebay' ); ?></option>
								<option value="Seller" <?php if ( @$item_details['ShippingCostPaidBy'] == 'Seller' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'Seller', 'wp-lister-for-ebay' ); ?></option>
							<?php endif; ?>
							</select>
							<br class="clear" />

							<label for="wpl-text-RefundOption" class="text_label">
								<?php echo __( 'Refund option', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Indicates how the seller will compensate the buyer for a returned item. Use the description field to explain the policy details. Not applicable on AU and EU sites.') ?>
							</label>
							<select id="wpl-text-RefundOption" name="wpl_e2e_RefundOption" class=" required-entry select">
								<option value="">-- <?php echo __( 'not specified', 'wp-lister-for-ebay' ); ?> --</option>
								<option value="Exchange"  <?php if ( @$item_details['RefundOption'] == 'Exchange'  ): ?>selected="selected"<?php endif; ?>><?php echo ('Exchange'); ?></option>
								<option value="MerchandiseCredit" <?php if ( @$item_details['RefundOption'] == 'MerchandiseCredit' ): ?>selected="selected"<?php endif; ?>><?php echo ('Merchandise Credit'); ?></option>
								<option value="MoneyBack"  <?php if ( @$item_details['RefundOption'] == 'MoneyBack'  ): ?>selected="selected"<?php endif; ?>><?php echo ('Money Back'); ?></option>
								<option value="MoneyBackOrExchange" <?php if ( @$item_details['RefundOption'] == 'MoneyBackOrExchange' ): ?>selected="selected"<?php endif; ?>><?php echo ('Money Back or Exchange'); ?></option>
								<option value="MoneyBackOrReplacement"  <?php if ( @$item_details['RefundOption'] == 'MoneyBackOrReplacement'  ): ?>selected="selected"<?php endif; ?>><?php echo ('Money Back or Replacement'); ?></option>
							</select>
							<br class="clear" />
							<?php if ( get_option('wplister_ebay_site_id') ) : ?>
							<p class="desc">
								<?php echo __( 'Not applicable on AU and EU sites.', 'wp-lister-for-ebay' ); ?>
							</p>
							<?php endif; ?>

							<label for="wpl-text-RestockingFee" class="text_label">
								<?php echo __( 'Restocking fee', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('This value indicates the restocking fee charged by the seller for returned items.') ?>
							</label>
							<select id="wpl-text-RestockingFee" name="wpl_e2e_RestockingFee" class=" required-entry select">
								<option value="">-- <?php echo __( 'not specified', 'wp-lister-for-ebay' ); ?> --</option>
								<option value="NoRestockingFee" <?php if ( @$item_details['RestockingFee'] == 'NoRestockingFee' ): ?>selected="selected"<?php endif; ?>><?php echo __( 'No restocking fee', 'wp-lister-for-ebay' ); ?></option>
								<option value="Percent_10" <?php if ( @$item_details['RestockingFee'] == 'Percent_10' ): ?>selected="selected"<?php endif; ?>>10 <?php echo __( 'percent', 'wp-lister-for-ebay' ); ?></option>
								<option value="Percent_15" <?php if ( @$item_details['RestockingFee'] == 'Percent_15' ): ?>selected="selected"<?php endif; ?>>15 <?php echo __( 'percent', 'wp-lister-for-ebay' ); ?></option>
								<option value="Percent_20" <?php if ( @$item_details['RestockingFee'] == 'Percent_20' ): ?>selected="selected"<?php endif; ?>>20 <?php echo __( 'percent', 'wp-lister-for-ebay' ); ?></option>
							</select>
							<br class="clear" />

							<label for="wpl-text-returns_description" class="text_label">
								<?php echo __( 'Returns description', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('A detailed description of your return policy.<br>eBay uses this text string as-is in the Return Policy section of the View Item page. Avoid HTML. Maximum length: 5000 characters') ?>
							</label>
							<textarea name="wpl_e2e_returns_description" id="wpl-text-returns_description" maxlength="5000" class="textarea"><?php echo stripslashes( $item_details['returns_description'] ); ?></textarea>
							<br class="clear" />

							</div>

							<?php if ( isset( $wpl_seller_return_profiles ) && is_array( $wpl_seller_return_profiles ) ): ?>
							<label for="wpl-text-seller_return_profile_id" class="text_label">
								<?php echo __( 'Return policy', 'wp-lister-for-ebay' ); ?>
                                <?php wplister_tooltip('Instead of setting your return policy details in WP-Lister you can select a predefined return policy from your eBay account.<br><br>Please note that if you use a predefined return policy, you might have to use shipping and payment policies as well.') ?>
							</label>
							<select id="wpl-text-seller_return_profile_id" name="wpl_e2e_seller_return_profile_id" class=" required-entry select">
								<option value="">-- <?php echo __( 'no policy', 'wp-lister-for-ebay' ); ?> --</option>
								<?php foreach ($wpl_seller_return_profiles as $seller_profile ) : ?>
									<option value="<?php echo $seller_profile->ProfileID ?>" 
										<?php if ( @$item_details['seller_return_profile_id'] == $seller_profile->ProfileID ) : ?>
											selected="selected"
										<?php endif; ?>
										><?php echo $seller_profile->ProfileName . ' - ' . $seller_profile->ShortSummary ?></option>
								<?php endforeach; ?>
							</select>
							<br class="clear" />
							<?php endif; ?>

						</div>
					</div>




					<div class="submit" style="padding-top: 0; float: right; display:none;">
						<input type="submit" value="<?php echo __( 'Save profile', 'wp-lister-for-ebay' ); ?>" name="submit" class="button-primary">
					</div>
						
				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 -->



		</div> <!-- #post-body -->
		<br class="clear">
	</div> <!-- #poststuff -->

	</form>


	<?php if ( get_option('wplister_log_level') > 6 ): ?>
	<pre><?php print_r($wpl_item); ?></pre>
	<?php endif; ?>

    <input type="hidden" id="disable_popups" value="<?php echo get_option( 'wplister_disable_profile_popup_errors', 0 ); ?>" />

	<script type="text/javascript">
		jQuery( document ).ready(
			function () {
                var disable_errors = jQuery("#disable_popups").val() == 1;

				// enable chosen.js
				jQuery("select.wple_chosen_select").chosen();
				

				// hide fixed price field for fixed price listings
				// (fixed price listings only use StartPrice)
				jQuery('#wpl-text-auction_type').change(function() {
  					if ( jQuery('#wpl-text-auction_type').val() == 'Chinese' ) {
  						jQuery('#wpl-text-fixed_price_container').show();
  					} else {
  						jQuery('#wpl-text-fixed_price_container').hide();
  					}
  					if ( jQuery('#wpl-text-auction_type').val() == 'ClassifiedAd' ) {
  						// jQuery('#wpl-option-PayPerLeadEnabled_container').show();
  					} else {
  						// jQuery('#wpl-option-PayPerLeadEnabled_container').hide();
  					}
				});
				jQuery('#wpl-text-auction_type').change();

				// hide condition description field for "new" conditions (Condition IDs 1000-1499)
				jQuery('#wpl-text-condition_id').change(function() {
  					if ( 1000 <= jQuery('#wpl-text-condition_id').val() <= 1499 ) {
  						jQuery('#wpl-text-condition_description_container').show();
  					} else {
  						jQuery('#wpl-text-condition_description_container').hide();
  					}
				});
				jQuery('#wpl-text-condition_id').change();

				// set Return Policy details visibility
				jQuery('#wpl-text-returns_accepted').change(function() {
  					if ( jQuery('#wpl-text-returns_accepted').val() == 1 ) {
  						jQuery('#returns_details_container').slideDown(200);
  					} else {
  						jQuery('#returns_details_container').slideUp(200);
  					}
				});
				jQuery('#wpl-text-returns_accepted').change();


				// set Tax Mode options visibility
				jQuery('#wpl-text-tax_mode').change(function() {
  					if ( jQuery('#wpl-text-tax_mode').val() == 'fix' ) {
  						jQuery('#tax_mode_fixed_options_container').show();
  					} else {
  						jQuery('#tax_mode_fixed_options_container').hide();
  					}
				});
				jQuery('#wpl-text-tax_mode').change();

				// set Subtitle options visibility
				jQuery('#wpl-text-subtitle_enabled').change(function() {
  					if ( jQuery('#wpl-text-subtitle_enabled').val() == 1 ) {
  						jQuery('#subtitle_options_container').show();
  					} else {
  						jQuery('#subtitle_options_container').hide();
  					}
				});
				jQuery('#wpl-text-subtitle_enabled').change();

				// set Best Offer options visibility
				jQuery('#wpl-text-bestoffer_enabled').change(function() {
  					if ( jQuery('#wpl-text-bestoffer_enabled').val() == 1 ) {
  						jQuery('#best_offer_options_container').slideDown(200);
  					} else {
  						jQuery('#best_offer_options_container').slideUp(200);
  					}
				});
				jQuery('#wpl-text-bestoffer_enabled').change();

				// set Schedule Time details visibility
				jQuery('#wpl-text-schedule_time').change(function() {
  					if ( jQuery('#wpl-text-schedule_time').val() != '' ) {
  						jQuery('#schedule_time_details_container').show();
  					} else {
  						jQuery('#schedule_time_details_container').hide();
  					}
				});
				jQuery('#wpl-text-schedule_time').change();

				// set Auto Relist options visibility
				jQuery('#wpl-text-autorelist_enabled').change(function() {
  					if ( jQuery('#wpl-text-autorelist_enabled').val() == 1 ) {
  						jQuery('#autorelist_options_container').slideDown(200);
  					} else {
  						jQuery('#autorelist_options_container').slideUp(200);
  					}
				});
				jQuery('#wpl-text-autorelist_enabled').change();

				// update ended items automatically when deactivating autorelist option - after calling .change()
				jQuery('#wpl-text-autorelist_enabled').change(function() {
  					if ( jQuery('#wpl-text-autorelist_enabled').val() == 0 ) {
  						jQuery('#wpl_e2e_apply_changes_to_all_ended').attr('checked','checked');
  					}
				});

				// set Selling Manager Pro options visibility
				jQuery('#wpl-text-sellingmanager_enabled').change(function() {
  					if ( jQuery('#wpl-text-sellingmanager_enabled').val() == 1 ) {
  						jQuery('#sm_auto_relist_options_container').slideDown(200);
  					} else {
  						jQuery('#sm_auto_relist_options_container').slideUp(200);
  					}
				});
				jQuery('#wpl-text-sellingmanager_enabled').change();

				// set custom quantity options visibility
				jQuery('#wpl-custom_quantity_enabled').change(function() {
  					if ( jQuery('#wpl-custom_quantity_enabled').val() != '' ) {
  						jQuery('#wpl-custom_quantity_container').show();
  					} else {
  						jQuery('#wpl-custom_quantity_container').hide();
  					}
				});
				jQuery('#wpl-custom_quantity_enabled').change();


			    // 
			    // Validation
			    // 
				// check required values on submit
				jQuery('.wplister-page form').on('submit', function() {
					
					// duration is required
					if ( jQuery('#wpl-text-listing_duration')[0].value == '' && !disable_errors ) {
						alert('Please select a listing duration.'); return false;
					}

					// dispatch time is required
					if ( jQuery('#wpl-text-dispatch_time')[0].value == '' && !disable_errors ) {
						alert('Please enter a handling time.'); return false;
					}

					// location required
					if ( jQuery('#wpl-text-location')[0].value == '' && !disable_errors ) {
						alert('Please enter a location.'); return false;
					}

					// country required
					if ( jQuery('#wpl-text-country')[0].value == '' && !disable_errors ) {
						alert('Please select a country.'); return false;
					}


					// validate shipping options
					var shipping_type = jQuery('.select_shipping_type')[0] ? jQuery('.select_shipping_type')[0].value : 'disabled';
					var seller_profile = jQuery('#wpl-text-seller_shipping_profile_id')[0] ? jQuery('#wpl-text-seller_shipping_profile_id')[0].value : false;

					if ( ! seller_profile ) {

						// check domestic shipping options
						if ( shipping_type == 'flat' || shipping_type == 'FreightFlat' || shipping_type == 'FlatDomesticCalculatedInternational' ) {

							// local flat shipping option required
							if ( jQuery('#loc_shipping_options_table_flat .select_service_name')[0].value == ''  && !disable_errors) {
								alert('Please select at least one domestic shipping service for eBay.'); return false;
							}
	
							// local flat shipping price required
							if ( jQuery('#loc_shipping_options_table_flat input.price_input')[0].value == ''  && !disable_errors ) {
								alert('Please enter a shipping fee for eBay.'); return false;
							}

							// max 5 shipping service options
							if ( jQuery('#loc_shipping_options_table_flat .select_service_name').length > 5  && !disable_errors ) {
								alert('You have selected more than 5 local shipping services, which is not allowed by eBay.'); return false;
							}

						} else if ( shipping_type == 'calc' || shipping_type == 'CalculatedDomesticFlatInternational' ) {

							// local calc shipping option required
							if ( jQuery('#loc_shipping_options_table_calc .select_service_name')[0].value == ''  && !disable_errors ) {
								alert('Please select at least one domestic shipping service for eBay.'); return false;
							}						

							// max 5 shipping service options
							if ( jQuery('#loc_shipping_options_table_calc .select_service_name').length > 5  && !disable_errors ) {
								alert('You have selected more than 5 local shipping services, which is not allowed by eBay.'); return false;
							}

						}

						// max 5 international shipping service options
						if ( shipping_type == 'flat' || shipping_type == 'FreightFlat' || shipping_type == 'CalculatedDomesticFlatInternational' ) {
							if ( jQuery('#int_shipping_options_table_flat .select_service_name').length > 5  && !disable_errors ) {
								alert('You have selected more than 5 international shipping services, which is not allowed by eBay.'); return false;
							}
						} else if ( shipping_type == 'calc' || shipping_type == 'FlatDomesticCalculatedInternational' ) {
							if ( jQuery('#int_shipping_options_table_calc .select_service_name').length > 5  && !disable_errors ) {
								alert('You have selected more than 5 international shipping services, which is not allowed by eBay.'); return false;
							}
						}

					}


					// payment method required
					var seller_payment_profile = jQuery('#wpl-text-seller_payment_profile_id')[0] ? jQuery('#wpl-text-seller_payment_profile_id')[0].value : false;
					if ( ( ! seller_payment_profile ) && ( jQuery('#payment_options_table select')[0].value == '' && !disable_errors ) ) {
						alert('Please select at least one payment method.'); return false;
					}

					// country required
					// if ( jQuery('#wpl-text-country')[0].value == '' ) {
					// 	alert('Please select a country.'); return false;
					// }


					// template is required
					var template_options = jQuery("input[name='wpl_e2e_template']");
					if( template_options.filter(':checked').length == 0 && !disable_errors){
						alert('Please select a listing template.'); return false;
					}

					return true;
				})


			}
		);






		// load item conditions on primary category change

		<?php
			// get item conditions as json
			$conditions = unserialize( @$wpl_item['category_conditions'] );
		?>
		var CategoryConditionsData = <?php echo json_encode( $conditions ) ?>;
		// var CurrentItemSpecifics = <?php echo json_encode( @$item_details['item_conditions'] ) ?>;
		// var default_ebay_category_id = <?php echo @$wpl_default_ebay_category_id ? $wpl_default_ebay_category_id : 0 ?>;

		var wpl_site_id    = '<?php echo $wpl_site_id ?>';
		var wpl_account_id = '<?php echo $wpl_account_id ?>';

		// handle new primary category
		// update item conditions
		function updateItemConditions() {
			var primary_category_id = jQuery('#ebay_category_id_1')[0].value;

			// jQuery('#EbayItemSpecificsBox .inside').slideUp(500);
			// jQuery('#EbayItemSpecificsBox .loadingMsg').slideDown(500);

	        // fetch category conditions
	        var params = {
	            action: 'wple_getCategoryConditions',
	            id: primary_category_id,
	            site_id: wpl_site_id,
	            account_id: wpl_account_id,
	            _wpnonce: '<?php echo wp_create_nonce( 'wple_getCategoryConditions' ) ?>'
	        };
	        var jqxhr = jQuery.getJSON( ajaxurl, params )
	        .success( function( response ) { 

	            // append to log
	            // console.log( 'response: ', response ); 
	            CategoryConditionsData = response;

	            buildItemConditions();
				// jQuery('#EbayItemConditionsBox .inside').slideDown(500);
				// jQuery('#EbayItemConditionsBox .loadingMsg').slideUp(500);

	        })
	        .error( function(e,xhr,error) { 
	            console.log( "error", xhr, error ); 
	            console.log( e.responseText ); 
	        });			
		}

		// built item conditions table
		function buildItemConditions() {

			var primary_category_id = jQuery('#ebay_category_id_1')[0].value;
			// var conditions = CategoryConditionsData[ primary_category_id ];
			var conditions = CategoryConditionsData;

			// console.log('buildItemConditions()');
			// console.log('primary_category_id',primary_category_id);
			// console.log('conditions step 1',conditions);

			// // possibly use default category
			// if ( ( ! conditions ) && ( default_ebay_category_id ) ) {
			// 	conditions = CategoryConditionsData[ default_ebay_category_id ];
			// }
			// // console.log('conditions step 2',conditions);

			// console.log('conditions: ',conditions);
			// console.log('CategoryConditionsData: ',CategoryConditionsData);
			// console.log('default_ebay_category_id: ',default_ebay_category_id);
			// console.log('primary_category_id: ',primary_category_id);

			if ( ( ! conditions ) || ( conditions == 'none' ) ) {
				jQuery('#wpl-text-condition_id').children().remove();
	            jQuery('#wpl-text-condition_id').append( jQuery('<option/>').val( 'none' ).html( 'none' ) );
				return;
			}
			// console.log('conditions step 3',conditions);


			// save current selection
			var selected_condition_id = jQuery('#wpl-text-condition_id')[0].value;		
			// console.log('selected_condition_id',selected_condition_id);

			// clear options
			jQuery('#wpl-text-condition_id').children().remove();

			// add options
			for (var condition_id in conditions ) {
				// console.log('condition_id ',condition_id);
				// console.log('condition_name ',conditions[condition_id]);
				condition_name = conditions[condition_id];
	            jQuery('#wpl-text-condition_id').append( jQuery('<option/>').val( condition_id ).html( condition_name ) );

			}

			// restore current selection
			jQuery("#wpl-text-condition_id option[value='"+selected_condition_id+"']").attr('selected',true);


		}

		// init item conditions when page is loaded
		jQuery( document ).ready( function () {
			// buildItemConditions();
		});	

	
	</script>

</div>



	
