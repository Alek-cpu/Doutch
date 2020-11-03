<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	#ebay_categories_tree_wrapper,
	#store_categories_tree_wrapper {
		/*max-height: 320px;*/
		/*margin-left: 35%;*/
		overflow: auto;
		width: 65%;
		display: none;
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
	
	.column-category {
		width: 25%;
		line-height: 24px;
	}
	.column-store_category {
		/*position: relative;*/
		width: 30%;
	}
	.column-ebay_category {
		/*position: relative;*/
		width: 45%;
	}
	.row-actions-wrapper {
		position: relative;
	}
	.row-actions-wrapper .row-actions {
		float: right;
		padding: 0;
		/*left: inherit;*/ /* uncomment to make buttons always visible */
	}
	table.categories .row-actions input.button {
		/*margin: 0;*/
		/*height: 25px;*/
		/*line-height: 24px;*/
	}

</style>

<div class="wrap wplister-page">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/hammer-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
          
	<?php include_once( dirname(__FILE__).'/settings_tabs.php' ); ?>
	<?php echo $wpl_message ?>

	<p>
    	<?php echo __( 'This is where you can assign official eBay categories and eBay Store categories to your all your local WooCommerce product categories.', 'wp-lister-for-ebay' ) ?>
    </p><p>
    	<?php echo __( 'Once you selected a destination category for each local category, you can leave the profile category settings empty to automatically select the category according to this list.', 'wp-lister-for-ebay' ) ?>
    	<!-- <br> -->
    	<?php echo __( 'In order to access item specifics and custom item conditions in a profile without a primary category it is recommended to select a default category at the bottom of this page.', 'wp-lister-for-ebay' ) ?>
	</p>

	<form method="post" action="<?php echo $wpl_form_action; ?>">
        <?php wp_nonce_field( 'wplister_save_categories_map' ); ?>
		<input type="hidden" name="action" value="save_wplister_categories_map" >

		<!-- display categories table -->
        <?php $wpl_categoriesMapTable->display() ?>

		<!-- hidden ajax categories tree -->
		<div id="ebay_categories_tree_wrapper">
			<div id="ebay_categories_tree_container"></div>
		</div>
		<!-- hidden ajax categories tree -->
		<div id="store_categories_tree_wrapper">
			<div id="store_categories_tree_container"></div>
		</div>


		<div class="submit" style="padding-top: 0; float: left;">
            <p class="categorySelector" style="margin:0;float:left;">
                <input type="hidden" name="wpl_e2e_default_ebay_category_id" id="ebay_category_id_default" value="<?php echo $wpl_default_category_id ?>" class="">
	        	<b><?php echo __( 'Default category', 'wp-lister-for-ebay' ) ?>:</b>
                <span id="ebay_category_name_default" class="text_input"><?php echo $wpl_default_category_name ?></span><br>
	        	<?php echo __( 'Use this to make additional item conditions available in your profile.', 'wp-lister-for-ebay' ) ?>
            </p>
        </div>

		<div class="submit" style="padding-top: 0; float: right; margin-right:-44px;">
            <input type="button" value="<?php echo __( 'Select default category', 'wp-lister-for-ebay' ) ?>" class="button btn_select_default_category">
	        <!-- <a href="#" onclick="jQuery('#import_export_container').slideToggle();return false;" class="button"><?php echo __( 'Import / Export', 'wp-lister-for-ebay' ); ?></a>  -->
			<input type="submit" value="<?php echo __( 'Save changes', 'wp-lister-for-ebay' ) ?>" name="submit" class="button-primary">
		</div>
		<br style="clear:both;"/>
	

		<!-- Import / Export --> 
        <h3><?php echo __( 'Backup and restore category mappings', 'wp-lister-for-ebay' ); ?></h3>
        <a href="#" onclick="jQuery('#import_export_container').slideToggle();return false;" class="button right"><?php echo __( 'Import / Export', 'wp-lister-for-ebay' ); ?></a> 
		<p><?php echo __( 'Export and import your category mappings as JSON.', 'wp-lister-for-ebay' ); ?></p>
		<!-- <br style="clear:both;"/> -->

	</form>


    <div id="import_export_container" style="display:none; clear:both;">
        <!-- <hr> -->
        <!-- <h3><?php echo __( 'Backup and restore category mappings', 'wp-lister-for-ebay' ); ?></h3> -->

            <p>
                <a href="<?php echo $wpl_form_action ?>&action=wplister_export_categories_map&account_id=<?php echo $wpl_account_id; ?>&_wpnonce=<?php echo wp_create_nonce( 'wplister_export_categories_map' ); ?>" class="button"><?php echo __( 'Export category mappings', 'wp-lister-for-ebay' ); ?></a>
            </p>

            <p>
                <form id="upload_json" method="post" action="<?php echo $wpl_form_action; ?>" enctype="multipart/form-data" >

                    <a href="#" onclick="alert('Please select a file using the button right next to this button.');return false;" class="button"><?php echo __( 'Import category mappings', 'wp-lister-for-ebay' ); ?></a> 
                    <?php wp_nonce_field( 'wplister_import_categories_map' ); ?>
                    <input type="hidden" name="action" value="wplister_import_categories_map" />
                    <input type="file" name="wpl_file_upload" onchange="this.form.submit();" />
                    <input type="hidden" name="account_id" value="<?php echo $wpl_account_id; ?>" />

                </form>
            </p>
    </div>





	<script type="text/javascript">
		jQuery( document ).ready(
			function () {

				var wpl_site_id    = '<?php echo $wpl_site_id ?>';
				var wpl_account_id = '<?php echo $wpl_account_id ?>';
				var wple_ajax_nonce = '<?php echo wp_create_nonce( 'wple_ajax_nonce' ); ?>';

				// select default ebay category button
				// jQuery('.select_default_category input.btn_select_category').click( function(event) {
				jQuery('input.btn_select_default_category').click( function(event) {
					e2e_selecting_cat = 'default';

					var tbHeight = tb_getPageSize()[1] - 120;
					var tbURL = "#TB_inline?height="+tbHeight+"&width=500&inlineId=ebay_categories_tree_wrapper"; 
        			tb_show("Select a category", tbURL);  
					
				});
		
				// select ebay category button
				jQuery('td.column-ebay_category input.btn_select_category').click( function(event) {
					var cat_id = jQuery(this).parent()[0].id.split('sel_ebay_cat_id_')[1];
					e2e_selecting_cat = cat_id;

					var tbHeight = tb_getPageSize()[1] - 120;
					var tbURL = "#TB_inline?height="+tbHeight+"&width=500&inlineId=ebay_categories_tree_wrapper"; 
        			tb_show("Select a category", tbURL);  
					
				});
				// remove ebay category button
				jQuery('td.column-ebay_category input.btn_remove_category').click( function(event) {
					var cat_id = jQuery(this).parent()[0].id.split('sel_ebay_cat_id_')[1];
					jQuery('#ebay_category_id_'+cat_id).attr('value','');
					jQuery('#ebay_category_name_'+cat_id).html('');
					// console.log(cat_id);
				});
		
				// select store category button
				jQuery('td.column-store_category input.btn_select_category').click( function(event) {
					var cat_id = jQuery(this).parent()[0].id.split('sel_store_cat_id_')[1];
					e2e_selecting_cat = cat_id;

					var tbHeight = tb_getPageSize()[1] - 120;
					var tbURL = "#TB_inline?height="+tbHeight+"&width=500&inlineId=store_categories_tree_wrapper"; 
        			tb_show("Select a category", tbURL);  
					
				});
				// remove store category button
				jQuery('td.column-store_category input.btn_remove_category').click( function(event) {
					var cat_id = jQuery(this).parent()[0].id.split('sel_store_cat_id_')[1];
					jQuery('#store_category_id_'+cat_id).attr('value','');
					jQuery('#store_category_name_'+cat_id).html('');
					// console.log(cat_id);
				});

				// jqueryFileTree 1
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

			    });
	
				// jqueryFileTree 2
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
	
	</script>

</div>