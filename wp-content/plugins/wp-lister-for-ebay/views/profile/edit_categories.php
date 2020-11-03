<style type="text/css">

	#ebay_categories_tree_wrapper,
	#store_categories_tree_wrapper {
		/*max-height: 320px;*/
		/*margin-left: 35%;*/
		overflow: auto;
		width: 65%;
		display: none;
	}

	#EbayCategorySelectionBox span.texyt_input,
	#StoreCategorySelectionBox span.texyt_input {
		line-height: 25px;
	}

	#EbayCategorySelectionBox .category_row_actions,
	#StoreCategorySelectionBox .category_row_actions {
		position: absolute;
		top: 0;
		right: 0;
	}


	a.link_select_category {
		float: right;
		padding-top: 3px;
		text-decoration: none;
	}
	a.link_remove_category {
		padding-left: 3px;
		text-decoration: none;
	}
	
</style>

					<?php
						// fetch full category names
						$item_details['ebay_category_1_name']  = EbayCategoriesModel::getFullEbayCategoryName( $item_details['ebay_category_1_id'], $wpl_site_id );
						$item_details['ebay_category_2_name']  = EbayCategoriesModel::getFullEbayCategoryName( $item_details['ebay_category_2_id'], $wpl_site_id );
						$item_details['store_category_1_name'] = EbayCategoriesModel::getFullStoreCategoryName( $item_details['store_category_1_id'], $wpl_account_id );
						$item_details['store_category_2_name'] = EbayCategoriesModel::getFullStoreCategoryName( $item_details['store_category_2_id'], $wpl_account_id );
					?>

					<div class="postbox" id="EbayCategorySelectionBox">
						<h3 class="hndle"><span><?php echo __( 'eBay categories', 'wp-lister-for-ebay' ); ?></span></h3>
						<div class="inside">

							<div style="position:relative; margin: 0 5px;">
								<label for="wpl-text-ebay_category_1_name" class="text_label">
									<?php echo __( 'Primary eBay category', 'wp-lister-for-ebay' ); ?> <?php echo WPLE_IS_LITE_VERSION ? '*' : '' ?>
	                                <?php wplister_tooltip('Select the first (or only) category in which the item will be listed.<br><br>
	                                						A number of listing features like available item conditions and item specifics depend on the primary category.<br><br>
	                                						You can leave this empty if you assigned a primary eBay category to your local WooCommerce categories at WP-Lister &raquo; Settings &raquo; Categories. (Pro only)') ?>
								</label>
								<input type="hidden" name="wpl_e2e_ebay_category_1_id" id="ebay_category_id_1" value="<?php echo $item_details['ebay_category_1_id']; ?>" class="" />
								<span  id="ebay_category_name_1" class="text_input" style="width:45%;float:left;line-height:2em;"><?php echo $item_details['ebay_category_1_name']; ?></span>
								<div class="category_row_actions">
									<input type="button" value="<?php echo __( 'select', 'wp-lister-for-ebay' ); ?>" class="button btn_select_ebay_category" onclick="">
									<input type="button" value="<?php echo __( 'remove', 'wp-lister-for-ebay' ); ?>" class="button btn_remove_ebay_category" onclick="">
								</div>
							</div>
							
							<div style="position:relative; margin: 0 5px; clear:both">
								<label for="wpl-text-ebay_category_2_name" class="text_label">
									<?php echo __( 'Secondary eBay category', 'wp-lister-for-ebay' ); ?>
	                                <?php wplister_tooltip('On the eBay UK, Ireland, Germany, Austria, Switzerland, and Italy sites you can list Store Inventory listings in two categories. On the eBay US and other sites, you cannot list Store Inventory listings in two categories.<br><br>
	                                						You cannot list US eBay Motors vehicles in two categories. However, you can list Parts & Accessories in two categories. The Final Value Fee is based on the primary category in which the item is listed.') ?>
								</label>
								<input type="hidden" name="wpl_e2e_ebay_category_2_id" id="ebay_category_id_2" value="<?php echo $item_details['ebay_category_2_id']; ?>" class="" />
								<span  id="ebay_category_name_2" class="text_input" style="width:45%;float:left;line-height:2em;"><?php echo $item_details['ebay_category_2_name']; ?></span>
								<div class="category_row_actions">
									<input type="button" value="<?php echo __( 'select', 'wp-lister-for-ebay' ); ?>" class="button btn_select_ebay_category" onclick="">
									<input type="button" value="<?php echo __( 'remove', 'wp-lister-for-ebay' ); ?>" class="button btn_remove_ebay_category" onclick="">
								</div>
							</div>
							<div class="clear"></div>

							<?php if ( @$wpl_default_ebay_category_id && ! $item_details['ebay_category_1_id'] && ! empty($wpl_item['profile_id']) ) : ?>
							<div style="position:relative; margin: 5px 10px; clear:both">
								<?php echo __( 'Conditions and item specifics are based on the category', 'wp-lister-for-ebay' ); ?>: <?php echo EbayCategoriesModel::getCategoryName( $wpl_default_ebay_category_id ) ?>
							</div>
							<?php endif; ?>

						</div>
					</div>

					<div class="postbox" id="StoreCategorySelectionBox">
						<h3 class="hndle"><span><?php echo __( 'Store categories', 'wp-lister-for-ebay' ); ?></span></h3>
						<div class="inside">

							<div style="position:relative; margin: 0 5px;">
								<label for="wpl-text-store_category_1_name" class="text_label">
									<?php echo __( 'Store category', 'wp-lister-for-ebay' ); ?> 1
                                	<?php wplister_tooltip('<b>Store category</b><br>A custom category that the seller created in their eBay Store.<br><br>
                                							eBay Stores sellers can create up to three levels of custom categories for their stores. Items can only be listed in root categories, or categories that have no child categories (subcategories).') ?>
								</label>
								<input type="hidden" name="wpl_e2e_store_category_1_id" id="store_category_id_1" value="<?php echo $item_details['store_category_1_id']; ?>" class="" />
								<span  id="store_category_name_1" class="text_input" style="width:45%;float:left;line-height:2em;"><?php echo $item_details['store_category_1_name']; ?></span>
								<div class="category_row_actions">
									<input type="button" value="<?php echo __( 'select', 'wp-lister-for-ebay' ); ?>" class="button btn_select_store_category" onclick="">
									<input type="button" value="<?php echo __( 'remove', 'wp-lister-for-ebay' ); ?>" class="button btn_remove_store_category" onclick="">
								</div>
							</div>
							
							<div style="position:relative; margin: 0 5px; clear:both">
								<label for="wpl-text-store_category_2_name" class="text_label">
									<?php echo __( 'Store category', 'wp-lister-for-ebay' ); ?> 2
                                	<?php wplister_tooltip('<b>Store category</b><br>A custom category that the seller created in their eBay Store.<br><br>
                                							eBay Stores sellers can create up to three levels of custom categories for their stores. Items can only be listed in root categories, or categories that have no child categories (subcategories).') ?>
								</label>
								<input type="hidden" name="wpl_e2e_store_category_2_id" id="store_category_id_2" value="<?php echo $item_details['store_category_2_id']; ?>" class="" />
								<span  id="store_category_name_2" class="text_input" style="width:45%;float:left;line-height:2em;"><?php echo $item_details['store_category_2_name']; ?></span>
								<div class="category_row_actions">
									<input type="button" value="<?php echo __( 'select', 'wp-lister-for-ebay' ); ?>" class="button btn_select_store_category" onclick="">
									<input type="button" value="<?php echo __( 'remove', 'wp-lister-for-ebay' ); ?>" class="button btn_remove_store_category" onclick="">
								</div>
							</div>
							<div class="clear"></div>

						</div>
					</div>


			<!-- hidden ajax categories tree -->
			<div id="ebay_categories_tree_wrapper">
				<div id="ebay_categories_tree_container"></div>
			</div>
			<!-- hidden ajax categories tree -->
			<div id="store_categories_tree_wrapper">
				<div id="store_categories_tree_container"></div>
			</div>


	<script type="text/javascript">

		var wpl_site_id    = '<?php echo $wpl_site_id ?>';
		var wpl_account_id = '<?php echo $wpl_account_id ?>';
		var wple_ajax_nonce = '<?php echo wp_create_nonce( 'wple_ajax_nonce' ); ?>';

		/* recusive function to gather the full category path names */
        function wpl_getCategoryPathName( pathArray, depth ) {
			var pathname = '';
			if (typeof depth == 'undefined' ) depth = 0;

        	// get name
	        if ( depth == 0 ) {
	        	var cat_name = jQuery('[rel=' + pathArray.join('\\\/') + ']').html();
	        } else {
		        var cat_name = jQuery('[rel=' + pathArray.join('\\\/') +'\\\/'+ ']').html();
	        }

	        // console.log('path...: ', pathArray.join('\\\/') );
	        // console.log('catname: ', cat_name);
	        // console.log('pathArray: ', pathArray);

	        // strip last (current) item
	        popped = pathArray.pop();
	        // console.log('popped: ',popped);

	        // call self with parent path
	        if ( pathArray.length > 2 ) {
		        pathname = wpl_getCategoryPathName( pathArray, depth + 1 ) + ' &raquo; ' + cat_name;
	        } else if ( pathArray.length > 1 ) {
		        pathname = cat_name;
	        }

	        return pathname;

        }

		jQuery( document ).ready(
			function () {


				// select ebay category button
				jQuery('input.btn_select_ebay_category').click( function(event) {
					// var cat_id = jQuery(this).parent()[0].id.split('sel_ebay_cat_id_')[1];
					e2e_selecting_cat = ('ebay_category_name_1' == jQuery(this).parent().parent().first().find('.text_input')[0].id) ? 1 : 2;

					var tbHeight = tb_getPageSize()[1] - 120;
					var tbURL = "#TB_inline?height="+tbHeight+"&width=500&inlineId=ebay_categories_tree_wrapper"; 
        			tb_show("Select a category", tbURL);  
					
				});
				// remove ebay category button
				jQuery('input.btn_remove_ebay_category').click( function(event) {
					var cat_id = ('ebay_category_name_1' == jQuery(this).parent().parent().first().find('.text_input')[0].id) ? 1 : 2;
					
					jQuery('#ebay_category_id_'+cat_id).attr('value','');
					jQuery('#ebay_category_name_'+cat_id).html('');
				});
		
				// select store category button
				jQuery('input.btn_select_store_category').click( function(event) {
					// var cat_id = jQuery(this).parent()[0].id.split('sel_store_cat_id_')[1];
					e2e_selecting_cat = ('store_category_name_1' == jQuery(this).parent().parent().first().find('.text_input')[0].id) ? 1 : 2;

					var tbHeight = tb_getPageSize()[1] - 120;
					var tbURL = "#TB_inline?height="+tbHeight+"&width=500&inlineId=store_categories_tree_wrapper"; 
        			tb_show("Select a category", tbURL);  
					
				});
				// remove store category button
				jQuery('input.btn_remove_store_category').click( function(event) {
					var cat_id = ('store_category_name_1' == jQuery(this).parent().parent().first().find('.text_input')[0].id) ? 1 : 2;
					
					jQuery('#store_category_id_'+cat_id).attr('value','');
					jQuery('#store_category_name_'+cat_id).html('');
				});
		
		
				// jqueryFileTree 1 - ebay categories
			    jQuery('#ebay_categories_tree_container').fileTree({
			        root: '/0/',
			        script: ajaxurl+'?action=wple_get_ebay_categories_tree&site_id='+wpl_site_id+'&_wpnonce='+wple_ajax_nonce,
			        expandSpeed: 400,
			        collapseSpeed: 400,
			        loadMessage: 'loading eBay categories...',
			        multiFolder: false
			    }, function(catpath) {

					// get cat id from full path
			        var cat_id = catpath.split('/').pop(); // get last item - like php basename()

			        // get name of selected category
			        var cat_name = '';

			        var pathname = wpl_getCategoryPathName( catpath.split('/') );
					// console.log('pathname: ',pathname);
			        
			        // update fields
			        jQuery('#ebay_category_id_'+e2e_selecting_cat).attr( 'value', cat_id );
			        jQuery('#ebay_category_name_'+e2e_selecting_cat).html( pathname );
			        
			        // close thickbox
			        tb_remove();

			        if ( e2e_selecting_cat == 1 ) {
			        	updateItemSpecifics();
			        	updateItemConditions();
			        }

			    });
	
				// jqueryFileTree 2 - store categories
			    jQuery('#store_categories_tree_container').fileTree({
			        root: '/0/',
			        script: ajaxurl+'?action=wple_get_store_categories_tree&account_id='+wpl_account_id+'&_wpnonce='+wple_ajax_nonce,
			        expandSpeed: 400,
			        collapseSpeed: 400,
			        loadMessage: 'loading store categories...',
			        multiFolder: false
			    }, function(catpath) {

					// get cat id from full path
			        var cat_id = catpath.split('/').pop(); // get last item - like php basename()

			        // get name of selected category
			        var cat_name = '';

			        var pathname = wpl_getCategoryPathName( catpath.split('/') );
					// console.log('pathname: ',pathname);

					if ( pathname.indexOf('[use this category]') > -1 ) {
						catpath = catpath + '/';
						pathname = wpl_getCategoryPathName( catpath.split('/') );
					}       
			        
			        // update fields
			        jQuery('#store_category_id_'+e2e_selecting_cat).attr( 'value', cat_id );
			        jQuery('#store_category_name_'+e2e_selecting_cat).html( pathname );
			        
			        // close thickbox
			        tb_remove();

			    });
	


			}
		);
	
	
	</script>
