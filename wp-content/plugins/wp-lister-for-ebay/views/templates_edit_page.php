<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<script src="<?php echo $wpl_plugin_url; ?>/js/ace/ace.js" type="text/javascript" charset="utf-8"></script>
<script src="<?php echo $wpl_plugin_url; ?>/js/ace/mode-scss.js" type="text/javascript" charset="utf-8"></script>
<script src="<?php echo $wpl_plugin_url; ?>/js/ace/mode-php.js" type="text/javascript" charset="utf-8"></script>
<script src="<?php echo $wpl_plugin_url; ?>/js/ace/mode-html.js" type="text/javascript" charset="utf-8"></script>
<script src="<?php echo $wpl_plugin_url; ?>/js/ace/theme-chrome.js" type="text/javascript" charset="utf-8"></script>

<style type="text/css">

	/* sidebar */
	#poststuff #side-sortables .postbox input.text_input,
	#poststuff #side-sortables .postbox select.select {
	    width: 30%;
	}
	#poststuff #side-sortables .postbox label.text_label {
	    width: 66%;
	}
    /* sidebar */
    #poststuff #side-sortables .postbox input.upload_image_button {
        float: right;
    }

	#poststuff #side-sortables .postbox .inside p.desc {
		/*margin-left: 2%;*/
	}

	#poststuff #side-sortables .postbox .inside h4 {
		/*margin-left: 1%;*/
		margin-top: 1em;
		margin-bottom: 0.5em;
	}


	/* edit styles */
	#html_editor,
	#styles_editor,
	#header_editor,
	#footer_editor,
    #slider_editor,
	#thumbs_editor,
	#thumb2_editor,
	#functs_editor {
		height: 240px;
		width: 100%;
		position: relative;
		border: 1px solid #ccc;
	}
	#html_editor,
	#styles_editor {
		height: 420px;
	}
	
	/* hide warnings in css editor */
	#styles_editor .ace_gutter-cell.ace_warning {
		background-image: none;
	}	


	.postbox h3 {
	    cursor: default;
	}
		
	/* backwards compatibility to WP 3.3 */
	#poststuff #post-body.columns-2 {
	    margin-right: 300px;
	}
	#poststuff #post-body {
	    padding: 0;
	}
	#post-body.columns-2 #postbox-container-1 {
	    float: right;
	    margin-right: -300px;
	    width: 280px;
	}
	#poststuff .postbox-container {
	    width: 100%;
	}
	#major-publishing-actions {
	    border-top: 1px solid #F5F5F5;
	    clear: both;
	    margin-top: -2px;
	    padding: 10px 10px 8px;
	}
	#post-body .misc-pub-section {
	    max-width: 100%;
	    border-right: none;
	}
</style>



<div class="wrap wplister-page">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/hammer-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<?php if ( $wpl_item['template_id'] ): ?>
	<h2><?php echo __( 'Edit Template', 'wp-lister-for-ebay' ) ?></h2>
	<?php else: ?>
	<h2><?php echo __( 'New Template', 'wp-lister-for-ebay' ) ?></h2>
	<?php endif; ?>
	
	<?php echo $wpl_message ?>

	<form method="post" action="<?php echo $wpl_form_action; ?>">
	<?php if ( $wpl_add_new_template ) : ?>
		<input type="hidden" name="wpl_add_new_template" value="<?php echo $wpl_add_new_template ?>" />
	<?php endif; ?>

	<!--
	<div id="titlediv" style="margin-top:10px; margin-bottom:5px; width:60%">
		<div id="titlewrap">
			<label class="hide-if-no-js" style="visibility: hidden; " id="title-prompt-text" for="title">Enter title here</label>
			<input type="text" name="wpl_e2e_template_name" size="30" tabindex="1" value="<?php echo $wpl_item['template_name']; ?>" id="title" autocomplete="off">
		</div>
	</div>
	-->


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

									<?php if ( ! $wpl_add_new_template ): ?>

										<div class="misc-pub-section">
										<!-- optional save and apply to all prepared listings already using this template -->

										<?php if ( $wpl_total_listings_count > get_option( 'wplister_apply_profile_batch_size', 1000 ) ): ?>

											<input type="hidden" name="wple_delay_template_application" value="yes" />
											<?php $_GET['return_to'] = 'listings'; ?>

										<?php endif; ?>

										<?php if ( $wpl_prepared_listings_count > -1 ): ?>
											<p><?php printf( __( 'There are %s prepared, %s verified and %s published items using this template.', 'wp-lister-for-ebay' ), $wpl_prepared_listings_count, $wpl_verified_listings_count, $wpl_published_listings_count ) ?></p>
										<?php else: ?>
											<p><?php echo __( 'There are no prepared items using this template.', 'wp-lister-for-ebay' ); ?></p>
										<?php endif; ?>

											<p><?php echo __( 'To update already published items you need to revise them after saving the template.', 'wp-lister-for-ebay' ); ?></p>
										</div>

										<!--
										<div class="misc-pub-section">
											<p>
												<?php echo __( 'You can find the theme files in this folder:', 'wp-lister-for-ebay' ); ?>
												<em><?php echo $wpl_template_location; ?></em>
											</p>
										</div>
										-->

									<?php else: ?>

										<div class="misc-pub-section">
											<p><?php echo __( 'To update already published items you need to revise them after saving the template.', 'wp-lister-for-ebay' ); ?></p>
										</div>

									<?php endif; ?>



								</div>



								<div id="major-publishing-actions">
									<div id="publishing-action">
                                        <?php wp_nonce_field( 'wplister_save_template' ); ?>
										<input type="hidden" name="action" value="wple_save_template" />
										<input type="hidden" name="wpl_e2e_template_id" value="<?php echo $wpl_item['template_id']; ?>" />
										<input type="hidden" name="return_to" value="<?php echo sanitize_key(@$_GET['return_to']); ?>" />
										<input type="hidden" name="listing_status" value="<?php echo sanitize_key(@$_GET['listing_status']); ?>" />
										<input type="hidden" name="s" value="<?php echo sanitize_text_field(@$_GET['s']); ?>" />
										<input type="submit" value="<?php echo __( 'Save template', 'wp-lister-for-ebay' ); ?>" id="publish" class="button-primary" name="save">
									</div>
									<div class="clear"></div>
								</div>

							</div>

						</div>
					</div>


					<?php if ( $wpl_tpl_fields && sizeof($wpl_tpl_fields) > 0 ) : ?>
					<div class="postbox" id="TemplateFieldsBox">
						<h3 class="hndle"><span><?php echo __( 'Template Options', 'wp-lister-for-ebay' ); ?></span></h3>
						<div class="inside">
							<!--<p>This listing template provides the following custom settings:</p>-->

							<?php foreach ($wpl_tpl_fields as $field_id => $field) : ?>
								
								<?php if ( 'title' == $field->type ) : ?>

									<h4><?php echo $field->label ?></h4>

								<?php elseif ( 'color' == $field->type ) : ?>

								<!-- color input -->
								<div>
									<label for="<?php echo $field->id ?>" class="text_label"><?php echo $field->label ?></label>
									<input 	type="<?php echo 'text' ?>" 
										   	  id="<?php echo $field->id ?>"
										   	name="wpl_e2e_tpl_field_<?php echo $field->id ?>"
										   value="<?php echo strtoupper( $field->value ) ?>"
										   class="text_input colorpick"
										   
									/>
									<div id="colorPickerDiv_<?php echo $field->id ?>" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;right:1.2em;display:none;"></div>
								</div>

								<?php elseif ( 'select' == $field->type ) : ?>

									<label for="<?php echo $field->id ?>" class="text_label"><?php echo $field->label ?></label>
									<select	  id="<?php echo $field->id ?>"
										   	name="wpl_e2e_tpl_field_<?php echo $field->id ?>"										   
										   class="select" >
										<?php foreach ($field->options as $option_name => $option_value) : ?>
											<option value="<?php echo $option_value ?>" <?php if ($field->value == $option_value) echo "selected" ?>><?php echo $option_name ?></option>
										<?php endforeach; ?>
									</select>

                                <?php elseif ( 'media' == $field->type ) : ?>
                                    <div class="media_uploader">
                                        <label for="<?php echo $field->id ?>" class="text_label"><?php echo $field->label ?></label>
                                        <input id="wpl_media_<?php echo $field->id; ?>" name="wpl_e2e_tpl_field_<?php echo $field->id; ?>" value="<?php echo $field->value ?>" type="text" class="text_input" />

                                        <input id="wpl_media_button_<?php echo $field->id; ?>" name="wpl_media_button_<?php echo $field->id; ?>" class="upload_image_button" type="button" value="<?php _e( 'Add Media', 'wp-lister-for-ebay' ); ?>" data-target="wpl_media_<?php echo $field->id; ?>" />
                                    </div>
                                    <div class="clear"></div>
								<?php else : ?>
									<!-- default text inpit -->
									<label for="<?php echo $field->id ?>" class="text_label"><?php echo $field->label ?></label>
									<input 	type="<?php echo 'text' ?>" 
										   	  id="<?php echo $field->id ?>"
										   	name="wpl_e2e_tpl_field_<?php echo $field->id ?>"
										   value="<?php echo $field->value ?>"
										   class="text_input"
									/>

								<?php endif; ?>
								<!-- <pre><?php print_r($field) ?></pre> -->

							<?php endforeach; ?>

						</div>
					</div>
					<?php endif; ?>


					<div class="postbox" id="HelpBox">
						<h3 class="hndle"><span><?php echo __( 'Help', 'wp-lister-for-ebay' ); ?></span></h3>
						<div class="inside">
							<p>
								<?php echo __( 'You can use the following shortcodes in your listing template.', 'wp-lister-for-ebay' ); ?>
								<?php echo __( 'WordPress shortcodes will not work here.', 'wp-lister-for-ebay' ); ?>
							</p>
							<p>
								<b><?php echo __( 'Available Shortcodes', 'wp-lister-for-ebay' ); ?></b><br>
							</p>
							<p>
								<code>[[product_title]]</code><br>
								<?php echo __( 'product title', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<p>
								<code>[[product_content]]</code><br>
								<?php echo __( 'product main description', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<p>
								<code>[[product_excerpt]]</code> or<br>
								<code>[[product_excerpt_nl2br]]</code> or<br>
								<code>[[product_additional_content]]</code><br>
								<?php echo __( 'additional product description', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<p>
								<code>[[product_price]]</code><br>
								<?php echo __( 'product price', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<p>
								<code>[[product_sku]]</code><br>
								<?php echo __( 'product SKU', 'wp-lister-for-ebay' ); ?><br>
							</p>
                            <p>
                                <code>[[product_gallery]]</code><br>
                                <?php _e( 'New product gallery with thumbnails and without active content', 'wp-lister-for-ebay' ); ?><br>
                            </p>
							<p>
								<code>[[product_main_image]]</code><br>
								<?php echo __( 'main product image as HTML tag', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<p>
								<code>[[product_main_image_url]]</code><br>
								<?php echo __( 'main product image as raw URL', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<p>
								<code>[[product_thumbnails]]</code><br>
								<?php echo __( 'clickable thumbnails for all images', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<p>
								<code>[[additional_product_images]]</code><br>
								<?php echo __( 'additional images with JS (deprecated)', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<p>
								<code>[[img_1]]</code> ... <code>[[img_9]]</code><br>
								<code>[[img_url_1]]</code> ... <code>[[img_url_9]]</code><br>
								<?php echo __( 'single images or their urls', 'wp-lister-for-ebay' ); ?>
							</p>
							<p>
								<code>[[product_weight]]</code><br>
								<code>[[product_height]]</code><br>
								<code>[[product_width]]</code><br>
								<code>[[product_length]]</code><br>
								<?php echo __( 'product dimensions', 'wp-lister-for-ebay' ); ?>
							</p>
							<p>
								<code>[[attribute_Size]]</code><br>
								<code>[[attribute_Brand]]</code><br>
								<?php echo __( 'example for custom product attributes', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<p>
								<code>[[product_variations]]</code><br>
								<?php echo __( 'product variations as HTML table', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<p>
								<code>[[product_category]]</code><br>
								<?php echo __( 'main product category name', 'wp-lister-for-ebay' ); ?><br>
							</p>
                            <p>
                                <code>[[product_tags]]</code><br>
                                <?php echo __( 'comma-separated product tags', 'wp-lister-for-ebay' ); ?><br>
                            </p>
							<p>
								<code>[[meta_<em>custom-meta-field-name</em>]]</code><br>
								<?php echo __( 'custom meta values', 'wp-lister-for-ebay' ); ?><br>
							</p>
							<p>
								<code>[[widget_new_listings]]</code><br>
								<code>[[widget_ending_listings]]</code><br>
								<code>[[widget_related_listings]]</code><br>
								<code>[[widget_featured_listings]]</code><br>
								<?php // echo __( 'Use these dynamic widgets to add a gallery showing your other listings.', 'wp-lister-for-ebay' ); ?>
								<?php echo __( 'Note: These legacy widgets are deprecated since eBay banned active content in 2017.', 'wp-lister-for-ebay' ); ?>
							</p>
							<!--
							<p>
								<small><?php // echo __( 'Note: The related listings widget will only show up-sells or cross-sells, which need to be defined for each product in WooCommerce.', 'wp-lister-for-ebay' ); ?></small>
							</p>
							-->
							<p>
								<b><?php echo __( 'Shortcodes for advanced developers', 'wp-lister-for-ebay' ); ?></b><br>
							</p>
							<p>
								<!-- For advanced developers:<br> -->
								<code>[[ebay_item_id]]</code><br>
								<code>[[ebay_store_url]]</code><br>
								<code>[[ebay_store_category_id]]</code><br>
								<code>[[ebay_store_category_name]]</code><br>
								<code>[[wpl_listing_id]]</code><br>
								<code>[[admin_ajax_url]]</code><br>
							</p>
							
							<?php #if ( ! get_option('wpl_reseller_enable_whitelabel' ) ) : ?>
							<?php if ( ! defined('WPLISTER_RESELLER_VERSION') ) : ?>
							<p>
								For more information visit the 
								<a href="https://www.wplab.com/plugins/wp-lister/faq/" target="_blank">FAQ</a>.
							</p>
							<p>
								<!-- If you need help setting up your template, please contact support at wplab.com. -->
							</p>
							<?php endif; ?>
							
						</div>
					</div>


				</div>
			</div> <!-- #postbox-container-2 -->

			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables ui-sortable">
					

					<div class="postbox" id="TemplateSettingsBox">
						<h3 class="hndle"><span><?php echo __( 'Template settings', 'wp-lister-for-ebay' ); ?></span></h3>
						<div class="inside">

							<div id="titlediv" style="margin-bottom:5px;">
								<div id="titlewrap">
									<label for="wpl-text-template_description" class="text_label"><?php echo __( 'Name', 'wp-lister-for-ebay' ); ?>:</label>
									<input type="text" name="wpl_e2e_template_name" size="30" value="<?php echo $wpl_item['template_name']; ?>" id="title" autocomplete="off" style="width:65%;">
								</div>
							</div>

							<label for="wpl-text-template_description" class="text_label"><?php echo __( 'Description', 'wp-lister-for-ebay' ); ?>:</label>
							<input type="text" name="wpl_e2e_template_description" id="wpl-text-template_description" value="<?php echo $wpl_item['template_description']; ?>" class="text_input" />
							<br class="clear" />

							<label for="wpl-text-template_version" class="text_label"><?php echo __( 'Version', 'wp-lister-for-ebay' ); ?>:</label>
							<input type="text" name="wpl_e2e_template_version" id="wpl-text-template_version" value="<?php echo $wpl_item['template_version']; ?>" class="text_input" />
							<br class="clear" />

							<!--
							<?php if ( ! $wpl_add_new_template ): ?>
								<label for="wpl-text-template_location" class="text_label"><?php echo __( 'Location', 'wp-lister-for-ebay' ); ?>:</label>
								<input type="text" name="wpl_e2e_template_location" id="wpl-text-template_location" disabled value="<?php echo $wpl_template_location; ?>" class="text_input" style="background-color:#f5f5f5;" />
								<br class="clear" />
							<?php endif; ?>

							<p class="desc" style="display: block;">
								<?php echo __( 'Template description is only for internal use.', 'wp-lister-for-ebay' ); ?>
							</p>
							-->				

						</div>
					</div>

					<?php if ( $wpl_disable_wysiwyg_editor != 1 ) : ?>

					    <?php 
					    	// template stylesheet url
					    	$stylesheet 	 = WP_CONTENT_DIR . $wpl_item['template_path'] . '/style.css';
					    	$stylesheet_url  = WP_CONTENT_URL . $wpl_item['template_path'] . '/style.css' . '?ver='.@filemtime( $stylesheet );

					    	// get parsed stylesheet (v2)
					    	$stylesheet_url  = 'admin-ajax.php?action=wpl_get_tpl_css&tpl=' . $wpl_item['template_id'] . '&ver='.@filemtime( $stylesheet );
					    	
					    	// $stylesheet_url = str_replace(' ', urlencode(' '), $stylesheet_url);
					    	if ( $wpl_add_new_template ) $stylesheet_url = $wpl_plugin_url . '/templates/default/default.css';
					    	// echo "loading stylesheet $stylesheet_url <br>";

					        // unstyle editor content
					        add_filter( 'mce_css', function() use ( $stylesheet_url ) { return $stylesheet_url; } );

					        // default settings
					        $settings = array( 
					            'wpautop' => false, 
					            'media_buttons'=>true,
					            'teeny'=>false, 
					            'textarea_name' => 'wpl_e2e_tpl_html' 
					        );
					    ?>
					    <div id="wp-editor-wrapper">
					        <!-- <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" /> -->
					        <!-- <input type="hidden" name="action" value="saveListingTemplate" /> -->
					        <input type="hidden" name="wpl_e2e_filename" value="<?php echo $wpl_item['template_path'] ?>" />
					        <?php wp_editor( $wpl_html, 'tpl_html', $settings ); ?> 
					    </div>
					    <p><i><?php echo __( 'Note: You can disable the WYSIWYG editor on the advanced settings page.', 'wp-lister-for-ebay' ); ?></i></p>
					    <p>&nbsp;</p>
					
					<?php else: ?>

					    <h2><?php echo __( 'Template Content', 'wp-lister-for-ebay' ); ?></h2>

					    <div id="html-editor-wrapper">
					    	<textarea name="wpl_e2e_tpl_html"><?php echo $wpl_html ?></textarea>
					    	<div id="html_editor"></div>
					    </div>
					    <p><i><?php echo __( 'You have disabled the WYSIWYG editor.', 'wp-lister-for-ebay' ); ?></i></p>
					    <p>&nbsp;</p>
				    
					<?php endif; ?>
				    
				    <h2><?php echo __( 'Stylesheet', 'wp-lister-for-ebay' ); ?></h2>

				    <div id="css-editor-wrapper">
				    	<textarea name="wpl_e2e_tpl_css"><?php echo $wpl_css ?></textarea>
				    	<div id="styles_editor"></div>
				    </div>
				    <p>&nbsp;</p>
				    
				    <a name="header">&nbsp;</a>
				    <h2>header.php</h2>

				    <div id="header-editor-wrapper">
				    	<textarea name="wpl_e2e_tpl_header"><?php echo $wpl_header ?></textarea>
				    	<div id="header_editor"><?php #echo htmlspecialchars( $wpl_header ) ?></div>
				    </div>
				    <p>&nbsp;</p>
				    
				    <h2>footer.php</h2>

				    <div id="footer-editor-wrapper">
				    	<textarea name="wpl_e2e_tpl_footer"><?php echo $wpl_footer ?></textarea>
				    	<div id="footer_editor"></div>
				    </div>
				    <p>&nbsp;</p>
				    
				    <h2>functions.php</h2>

				    <div id="functions-editor-wrapper">
				    	<textarea name="wpl_e2e_tpl_functions"><?php echo $wpl_functions ?></textarea>
				    	<div id="functs_editor"></div>
				    </div>
				    <p>&nbsp;</p>

                    <h2>gallery_slider.php (new)</h2>
                    <div id="slider-editor-wrapper">
                        <textarea name="wpl_e2e_tpl_slider"><?php echo $wpl_slider ?></textarea>
                        <div id="slider_editor"></div>
                    </div>
                    <p>&nbsp;</p>

 				    <h2>thumbnails_nojs.php</h2>

				    <div id="thumbnails_nojs-editor-wrapper">
				    	<textarea name="wpl_e2e_tpl_thumbnails_nojs"><?php echo $wpl_thumbnails_nojs ?></textarea>
				    	<div id="thumb2_editor"></div>
				    </div>
				    <p>&nbsp;</p>


				    <h2>thumbnails.php (legacy)</h2>
				    <div id="thumbnails-editor-wrapper">
				    	<textarea name="wpl_e2e_tpl_thumbnails"><?php echo $wpl_thumbnails ?></textarea>
				    	<div id="thumbs_editor"></div>
				    </div>
				    <p>&nbsp;</p>


<!--				    <h2>thumbnails_nojs.php - controls output for [[product_thumbnails]] shortcode</h2>
				    <div id="thumbnails_nojs-editor-wrapper">
				    	<?php if ( empty($wpl_thumbnails_nojs) ) : ?>
						    <div style="border: 1px solid #ccc; overflow: auto; padding: 2em;"><i>Your template does not use this file.</i></div>
				    	<?php else : ?>
						    <pre style="border: 1px solid #ccc; overflow: auto;"><?php echo htmlspecialchars($wpl_thumbnails_nojs) ?></pre>
				    	<?php endif; ?>
				    </div>
				    <p>&nbsp;</p>

				    <h2>thumbnails.php - controls output for legacy [[additional_product_images]] shortcode</h2>
				    <div id="thumbnails-editor-wrapper">
				    	<?php if ( empty($wpl_thumbnails) ) : ?>
						    <div style="border: 1px solid #ccc; overflow: auto; padding: 2em;"><i>Your template does not use this file.</i></div>
				    	<?php else : ?>
						    <pre style="border: 1px solid #ccc; overflow: auto;"><?php echo htmlspecialchars($wpl_thumbnails) ?></pre>
				    	<?php endif; ?>
				    </div>
				    <p>&nbsp;</p>

                    <h2>gallery_slider.php - controls output for the [[product_gallery]] shortcode</h2>
                    <div id="slider-editor-wrapper">
                        <?php if ( empty($wpl_slider) ) : ?>
                            <div style="border: 1px solid #ccc; overflow: auto; padding: 2em;"><i>Your template does not use this file.</i></div>
                        <?php else : ?>
                            <pre style="border: 1px solid #ccc; overflow: auto;"><?php echo htmlspecialchars($wpl_slider) ?></pre>
                        <?php endif; ?>
                    </div>
                    <p>&nbsp;</p>
-->

					<div class="submit" style="padding-top: 0; float: right; display:none;">
                        <?php wp_nonce_field( 'wplister_save_template' ); ?>
						<input type="hidden" name="action" value="wple_save_template" />
						<input type="hidden" name="wpl_e2e_template_id" value="<?php echo $wpl_item['template_id']; ?>" />
						<input type="submit" value="<?php echo __( 'Save template', 'wp-lister-for-ebay' ); ?>" name="submit" class="button-primary">
					</div>

						
				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-2 -->


		</div> <!-- #post-body -->
		<br class="clear">
	</div> <!-- #poststuff -->

	</form>

	<br style="clear:both;"/>

	<?php if ( get_option('wplister_log_level') > 6 ): ?>
	<pre><?php print_r($wpl_item); ?></pre>
	<?php endif; ?>

	<?php if ( $wpl_disable_wysiwyg_editor == 1 ) : ?>
	<script type="text/javascript">
		jQuery( document ).ready( function () {

			    var html_editor = ace.edit("html_editor");
			    var html_textarea = jQuery('textarea[name="wpl_e2e_tpl_html"]').hide();

			    html_editor.setTheme("ace/theme/chrome");
			    html_editor.setShowPrintMargin( false );

			    var HtmlMode = require("ace/mode/html").Mode;
			    html_editor.getSession().setMode(new HtmlMode());
	
			    // connect editors with textareas
			    html_editor.getSession().setValue(html_textarea.val());

			    html_editor.getSession().on('change', function(){
					html_textarea.val(html_editor.getSession().getValue());
				});

		});	
	</script>
	<?php endif; ?>


	<script type="text/javascript">
		jQuery( document ).ready(
			function () {

			    var styles_editor = ace.edit("styles_editor");
			    var header_editor = ace.edit("header_editor");
			    var footer_editor = ace.edit("footer_editor");
			    var functs_editor = ace.edit("functs_editor");
			    var slider_editor = ace.edit("slider_editor");
			    var thumbs_editor = ace.edit("thumbs_editor");
			    var thumb2_editor = ace.edit("thumb2_editor");
			    var styles_textarea = jQuery('textarea[name="wpl_e2e_tpl_css"]').hide();
			    var header_textarea = jQuery('textarea[name="wpl_e2e_tpl_header"]').hide();
			    var footer_textarea = jQuery('textarea[name="wpl_e2e_tpl_footer"]').hide();
			    var functs_textarea = jQuery('textarea[name="wpl_e2e_tpl_functions"]').hide();
			    var slider_textarea = jQuery('textarea[name="wpl_e2e_tpl_slider"]').hide();
			    var thumbs_textarea = jQuery('textarea[name="wpl_e2e_tpl_thumbnails"]').hide();
			    var thumb2_textarea = jQuery('textarea[name="wpl_e2e_tpl_thumbnails_nojs"]').hide();

			    styles_editor.setTheme("ace/theme/chrome");
			    header_editor.setTheme("ace/theme/chrome");
			    footer_editor.setTheme("ace/theme/chrome");
			    functs_editor.setTheme("ace/theme/chrome");
			    slider_editor.setTheme("ace/theme/chrome");
			    thumbs_editor.setTheme("ace/theme/chrome");
			    thumb2_editor.setTheme("ace/theme/chrome");
			    styles_editor.setShowPrintMargin( false );
			    header_editor.setShowPrintMargin( false );
			    footer_editor.setShowPrintMargin( false );
			    functs_editor.setShowPrintMargin( false );
			    slider_editor.setShowPrintMargin( false );
			    thumbs_editor.setShowPrintMargin( false );
			    thumb2_editor.setShowPrintMargin( false );

			    // var JavaScriptMode = require("ace/mode/javascript").Mode;
			    var PhpMode = require("ace/mode/php").Mode;
			    var ScssMode = require("ace/mode/scss").Mode;
			    styles_editor.getSession().setMode(new ScssMode());
			    header_editor.getSession().setMode(new PhpMode());
			    footer_editor.getSession().setMode(new PhpMode());
			    functs_editor.getSession().setMode(new PhpMode());
			    slider_editor.getSession().setMode(new PhpMode());
			    thumbs_editor.getSession().setMode(new PhpMode());
			    thumb2_editor.getSession().setMode(new PhpMode());
	
			    // connect editors with textareas
			    // http://stackoverflow.com/questions/6440439/how-do-i-make-a-textarea-an-ace-editor
			    styles_editor.getSession().setValue(styles_textarea.val());
			    header_editor.getSession().setValue(header_textarea.val());
			    footer_editor.getSession().setValue(footer_textarea.val());
			    functs_editor.getSession().setValue(functs_textarea.val());
			    slider_editor.getSession().setValue(slider_textarea.val());
			    thumbs_editor.getSession().setValue(thumbs_textarea.val());
			    thumb2_editor.getSession().setValue(thumb2_textarea.val());
			    
			    styles_editor.getSession().on('change', function(){
					styles_textarea.val(styles_editor.getSession().getValue());
				});
			    header_editor.getSession().on('change', function(){
					header_textarea.val(header_editor.getSession().getValue());
				});
			    footer_editor.getSession().on('change', function(){
					footer_textarea.val(footer_editor.getSession().getValue());
				});
			    functs_editor.getSession().on('change', function(){
					functs_textarea.val(functs_editor.getSession().getValue());
				});
                slider_editor.getSession().on('change', function(){
                    slider_textarea.val(slider_editor.getSession().getValue());
                });
			    thumbs_editor.getSession().on('change', function(){
					thumbs_textarea.val(thumbs_editor.getSession().getValue());
				});
			    thumb2_editor.getSession().on('change', function(){
					thumb2_textarea.val(thumb2_editor.getSession().getValue());
				});
				// or just call
				// textarea.val(editor.getSession().getValue());
				// only when you submit the form 

			}
		);	


		jQuery( document ).ready(
			function () {

				// farbtastic color picker
				jQuery('.colorpick').each(function(){
					jQuery( '.colorpickdiv', jQuery(this).parent() ).farbtastic(this);
					jQuery(this).click(function() {
						if ( jQuery(this).val() == "" ) jQuery(this).val('#');
						jQuery('.colorpickdiv', jQuery(this).parent() ).show();
					});
				});
				jQuery(document).mousedown(function(){
					jQuery('.colorpickdiv').hide();
				});

				// new WP color picker
			    // if ( typeof jQuery.wp === 'object' && typeof jQuery.wp.wpColorPicker === 'function' ) {
		    	//     jQuery( '.colorpick' ).wpColorPicker();
		    	// }


				// check required values on submit
				jQuery('.wplister-page form').on('submit', function() {
					
					// folder name is required
					if ( jQuery('#title')[0].value == '' ) {
						alert('Please enter a template name.');
						return false;
					}

					return true;
				})

                // Uploading files
                var file_frame;
				var file_target;

                jQuery('.upload_image_button').live('click', function( event ){
                    var upload_btn = this;
                    event.preventDefault();

                    file_target = jQuery(this).data("target");

                    // If the media frame already exists, reopen it.
                    if ( file_frame ) {
                        file_frame.open();
                        return;
                    }

                    // Create the media frame.
                    file_frame = wp.media.frames.file_frame = wp.media({
                        title: jQuery( this ).data( 'uploader_title' ),
                        button: {
                            text: jQuery( this ).data( 'uploader_button_text' ),
                        },
                        multiple: false  // Set to true to allow multiple files to be selected
                    });

                    // When an image is selected, run a callback.
                    file_frame.on( 'select', function() {
                        // We set multiple to false so only get one image from the uploader
                        attachment = file_frame.state().get('selection').first().toJSON();

                        // Do something with attachment.id and/or attachment.url here
                        var target_el = file_target;

                        jQuery( "#"+ target_el ).val( attachment.url );
                    });

                    // Finally, open the modal
                    file_frame.open();
                });
			}
		);	
	</script>

</div>
