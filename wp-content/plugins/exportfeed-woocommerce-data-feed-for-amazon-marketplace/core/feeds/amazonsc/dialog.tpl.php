<?php
global $amwcore;
$tip = clone $amwcore;
if ($disabled) {
    $this->disable = 'disabled';
} else {
    $this->disable = '';
}
?>
<script type="text/javascript">
jQuery( document ).ready(function() {
		var shopID = jQuery("#edtRapidCartShop").val();
		if (shopID == null)
			shopID = "";
		var template = jQuery("#remote_category").val();
		if (template != null && template.length > 0) {
			jQuery.ajax({
				type: "post",
				url: ajaxurl,
				data: {
					shop_id: shopID,
					template: template,
					provider: "amazonsc",
					feedpath:amwscpf_object.cmdFetchTemplateDetails,
					security:amwscpf_object.security,
					action: amwscpf_object.action
				},
				success: function(res){
					jQuery("#attributeMappings").html(res);
				}
			});
		}
	});
</script>

<div class="attributes-mapping">

	<div class="createfeed" id="poststuff">
		<div class="postbox">

			 <!-- Ajax Loader div -->

                <div style="display: none;" id="ajax-loader-cat-import" ><span id="gif-message-span"></span></div>

			<!-- ***************
					Page Header
					****************** -->

			<h3 class="hndle"><?php echo $this->service_name_long; ?></h3>
			<div class="inside export-target">

				<!-- ***************
						LEFT SIDE
						****************** -->


				<!-- ***************
						RIGHT SIDE
						****************** -->

				<div class="feed-right">

					<!-- ROW 1: Local Categories -->
					<div class="feed-right-row cs-option">
						<span class="label">Amazon Marketplace : </span>
						<div class="input-boxes">
							<?php echo $this->amazonAccounts($initial_remote_category); ?>
							<span class="desc">Select Amazon account where you want to send the product feed. If none is selected,<br>US will be selected by default.</span>
						</div>
					</div>

					<!-- ROW 1: Local Categories -->
					<div class="feed-right-row cs-option">
						<span class="label"><?php echo $amwcore->cmsPluginName; ?> Category : </span>
						<div class="input-boxes">
							<?php echo $this->localCategoryList; ?>
							<!-- <br>-->
							<span class="desc">Select categories of your shop that you want to include in the product feed.</span>
						</div>
					</div>

					<!-- ROW 2: Remote Categories -->
					<!-- <?php // echo $this->line2(); ?>
					<div id="amazon-default-categories" style="" class="feed-right-row">
						<?php //echo $this->categoryList($initial_remote_category,$this->active_code); ?>
					</div> -->

                    <input id="amazon-remote-category-selected" type="hidden" name="amazon_remote_category" value="<?php echo $initial_remote_category; ?>">
                    <input type='hidden' id='remote_category' name='remote_category' value=''/>
                    <input id="page_action_determiner" type="hidden" name="page_action" value="create">


					<!-- ROW 4: Recommended Browse Node -->
					<?php echo $this->line2(); ?>
					<div style="display: none;" class="feed-right-row" id = "recommended_browse_node_box">
						<?php echo $this->recommended_browse_nodes($recommended_browse_nodes); ?>
					</div>

					<!-- ROW 4: Item Type -->
					<?php echo $this->line2(); ?>
					<div style="display: none;" class="feed-right-row" id = "item_type_keyword_box">
						<?php echo $this->item_type_keyword($item_type_keyword); ?>
					</div>

                    <!-- ROW 5 : Item Type -->
					<?php echo $this->line2(); ?>
					<div class="feed-right-row cs-option" id = "select-feed-type">
						<label class="label" for="select-feed-type" >Amazon Category : </label>
						<div class="input-boxes">
							<?php echo $this->feed_type($amazon_category); ?>
							<span class="desc">Your product will be listed in the merchant under the category you select</span>
						</div>
					</div>

					<?php echo $this->line2(); ?>
					<div style="display: none;" class="feed-right-row cs-option" id = "feed_product_type_box">
						<label class="label" for="feed_product_type" >Feed Product Type : </label>
						<div class="input-boxes">
						<?php echo $this->feed_product_type($initial_feed_product_type); ?>
						</div>

					</div>
					<!-- Text for Attributes -->
					<div style="display: none;" id="attribute-mapping-div" class="attribute-selection">
							<label class="attr-desc"><span style="display: block;">If you need to modify your product feed,
								<a onclick="show_advanced_attr(this)">click here to go to product feed customization options<span class="dashicons dashicons-arrow-down"></span></a>
								</span></label>

							<!-- Attribute Mapping DropDowns -->
							<div style="display: none;" class="attr-feed"  id="attributeMappings">
								<label for="categoryDisplayText"></label>
							<?php //echo $this->attributeMappings(); ?>
							</div>

							<div id="advance-section" style="display: none">
								<label class="un_collapse_label" title="Advanced" id="toggleAdvancedSettingsButton" onclick="amwscp_toggleAdvancedDialog()">[ Open Advanced Commands ]</label>
								<label class="un_collapse_label" title="Erase existing mappings" id="erase_mappings" onclick="amwscp_doEraseMappings('<?php echo $this->service_name; ?>')">[ Reset Attribute Mappings ]</label>
							</div>
							<div class="feed-advanced" id="feed-advanced">
							<textarea class="feed-advanced-text" id="feed-advanced-text"><?php echo $this->advancedSettings; ?></textarea>
							<?php echo $this->cbUnique; ?>
							<button class="navy_blue_button" id="bUpdateSetting" name="bUpdateSetting" onclick="amwscp_doUpdateSetting('feed-advanced-text', 'cp_advancedFeedSetting-<?php echo $this->service_name; ?>'); return false;" >Update</button>
							<div id="updateSettingMessage">&nbsp;</div>
				</div>
					</div>
					<!-- ROW 6: Filename -->
					<div class="feed-right-row">
						<span class="field-name label">File name for feed : </span>
						<span class="field-name">Please provide a unique yet identifiable filename for your feed.</span>
						<span class="field-name"><input type="text" name="feed_filename" id="feed_filename" class="text_big" value="<?php echo $this->initial_filename; ?>" /></span>
						<div class="feed-right-row">
						<label><span style="color: red;">*</span>Use alpha-numeric value for filename. <br>If you use an existing file name, the file will be overwritten.</label>
						</div>
					</div>


					<!-- ROW 8: Get Feed Button -->
					<div class="feed-right-row">
						<input class="button button-primary" type="button" onclick="amwscp_doGetFeed('Amazonsc')" value="Get Feed" />
						<div>
                            <p>
                                <span id="feed-status-display"></span>
                                <span id="feed-error-display"></span>
                            </p>
                        </div>
                        <!--<div  style="margin-top:5px;">&nbsp;</div>-->
					</div>
				</div>


				<!-- ***************
						Termination DIV
						****************** -->

				<!-- ***************
						FOOTER
						****************** -->

										<div style="clear: both;"></div>
			</div>
		</div>
	</div>
</div>


<div id="myModal" class="modal">

  <!-- Modal content -->
  <div id = "parent-cat-modal" class="modal-content">
  	<div class="select-category">
  		<span>All amazon category <span id="all-amazon-category">US</span>:</span>
  		<span  id="selected-category-text" class="display-text"></span>
  	</div>
	    <div class="category-select-body">
	    	<span class="sub_category_icon_left" onclick="showPrev();" id="category_icon_left"><img class="icon-arrow-left" src="<?php echo plugins_url('/../../../', __FILE__) . '/images/left.png' ?>"></span>
	    	<div class="category-main-div">
	    	 <div id="div-0"></div>
		     <div id="div-1"></div>
		     <div id="div-2"></div>
		     <!-- <div id="div-3"></div> -->
		</div>
		<span class="sub_category_icon_right" onclick="showNext();" id="category_icon_right"><img class="icon-arrow-right" src="<?php echo plugins_url('/../../../', __FILE__) . '/images/right.png' ?>"></span>
  </div>

	<div class="close-button"><button class="button-primary close" onclick="">Cancel</button></div>
</div>
</div>



<script type="text/javascript">
	jQuery(document).ready(function (){
		jQuery(".tips, .help_tip").tipTip({
			'attribute' : 'data-tip',
			'maxWidth' : '250px',
			'fadeIn' : 50,
			'fadeOut' : 50,
			'delay' : 200
		});
	});
</script>