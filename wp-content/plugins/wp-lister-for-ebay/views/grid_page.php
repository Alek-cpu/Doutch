<style type="text/css">
</style>

<div id="wplge_grid_header">
	
	<!-- <div class="alignleft actions"></div> -->

	<div class="wplge_button_bar wplge_button_bar_left">
		<button class="button button-x-small" v-on:click="btnToggleGridSelection" id="wplge_btn_toggle_grid_selection" title="Toggle checkbox column">
			<span class="dashicons dashicons-yes"></span>
		</button>

		<select name="action" id="bulk-action-selector-">
			<option value="-1">Bulk Actions</option>
			<option value="wple_verify">Verify with eBay</option>
			<option value="wple_publish2e">Publish to eBay</option>
			<option value="wple_update">Update status from eBay</option>
			<option value="wple_change_profile">Select another profile</option>
			<option value="wple_reapply">Re-apply profile</option>
			<option value="wple_revise">Revise items</option>
			<option value="wple_end_item">End listings</option>
			<option value="wple_relist">Re-list ended items</option>
			<option value="wple_lock">Lock listings</option>
			<option value="wple_unlock">Unlock listings</option>
			<option value="wple_archive">Move to archive</option>
			<option value="wple_reset_status">Reset ended items</option>
			<option value="wple_clear_eps_data">Clear EPS cache</option>
		</select>
		<input type="submit" id="doaction" class="button action" value="Apply" v-on:click="doBulkAction">

		<button class="button button-secondary" onClick="WPLGE.GridController.gridOptions.api.undoCellEditing();" title="Undo last edit"><span class="dashicons dashicons-undo"></span></button>
		<button class="button button-secondary last" onClick="WPLGE.GridController.gridOptions.api.redoCellEditing();" title="Redo"><span class="dashicons dashicons-redo"></span></button>

		<select id="wplge_filter_status" v-model="filter_status" v-on:change="statusFilterChanged">
			<option value="">All statuses</option>
			<option value="prepared">Prepared items</option>
			<option value="verified">Verified items</option>
			<option value="published">Published items</option>
			<option value="changed">Changed items</option>
			<option value="ended">Ended items</option>
			<option value="sold">Sold items</option>
			<option value="error">With Errors items</option>
			<option value="locked">Locked items</option>
			<option value="unlocked">Unlocked items</option>
		</select>

		<select id="wplge_filter_profile_id" v-model="filter_profile_id" v-on:change="profileFilterChanged">
			<option value="">All profiles</option>
			<option v-for="profile in listing_profiles" v-bind:value="profile.value">
				{{ profile.text }}
			</option>
		</select>            

		<select name="account_id" v-model="filter_account_id" v-on:change="accountFilterChanged">
			<option value="">All accounts</option>
			<option v-for="account in ebay_accounts" v-bind:value="account.value">
				{{ account.text }}
			</option>
		</select>            
		<!-- <input type="submit" name="" id="post-query-submit" class="button" value="Filter"> -->

		
		<!--
  		<button class="button" v-on:click="btnToggleGridFilter" title="Toggle filter row">
			<span class="dashicons dashicons-filter"></span>
		</button>	
 		-->

 		<button class="button" v-on:click="btnOpenGridSettings" title="Open settings">
			<span class="dashicons dashicons-admin-generic"></span>
		</button>

		<button class="button" v-on:click="reloadGridData" title="Reload all listings">
			<span class="dashicons dashicons-update"></span>
		</button>

		<span class="v-message">
			{{ message }}		
		</span>

	</div>


	<div class="wplge_button_bar wplge_button_bar_right search-box">

		<input type="search" id="wplge_quickfilter_input" name="s" value="" v-on:keyup="quickFilterChanged">
		<button class="button button-primary" id="wplge_btn_start_quicksearch"><span class="dashicons dashicons-search"></span></button>
		<!-- <input type="submit" id="search-submit" class="button" value="Search"> -->
		<!-- <button class="button button-small" value="Test">Test</button> -->
		<!-- <button class="button button-secondary" value="Test">Test</button> -->
		<!-- <button class="button button-primary" value="Test">Test</button> -->
	</div>

</div>

<div id="wplge_grid_container" style="height: 600px; width: 100%;" class="ag-theme-<?php echo WPLE_AGGRID_THEME ?>"></div>


<div id="wplge_grid_options" style="display: none;">

	<h4>
		Prices
	</h4>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="_regular_price"
		v-bind:class="isColumnVisible('_regular_price') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('_regular_price') ? visibleClass : invisibleClass"></span> Regular Price
	</button>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="_sale_price"
		v-bind:class="isColumnVisible('_sale_price') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('_sale_price') ? visibleClass : invisibleClass"></span> Sale Price
	</button>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="_ebay_start_price"
		v-bind:class="isColumnVisible('_ebay_start_price') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('_ebay_start_price') ? visibleClass : invisibleClass"></span> eBay Price
	</button>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="_amazon_price"
		v-bind:class="isColumnVisible('_amazon_price') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('_amazon_price') ? visibleClass : invisibleClass"></span> Amazon Price
	</button>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="_msrp_price"
		v-bind:class="isColumnVisible('_msrp_price') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('_msrp_price') ? visibleClass : invisibleClass"></span> MSRP
	</button>

	<h4>
		WooCommerce
	</h4>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="thumb"
		v-bind:class="isColumnVisible('thumb') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('thumb') ? visibleClass : invisibleClass"></span> Image
	</button>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="sku"
		v-bind:class="isColumnVisible('sku') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('sku') ? visibleClass : invisibleClass"></span> SKU
	</button>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="auction_title"
		v-bind:class="isColumnVisible('auction_title') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('auction_title') ? visibleClass : invisibleClass"></span> Title
	</button>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="_regular_price"
		v-bind:class="isColumnVisible('_regular_price') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('_regular_price') ? visibleClass : invisibleClass"></span> Regular Price
	</button>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="_sale_price"
		v-bind:class="isColumnVisible('_sale_price') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('_sale_price') ? visibleClass : invisibleClass"></span> Sale Price
	</button>

	<h4>
		eBay
	</h4>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="_ebay_start_price"
		v-bind:class="isColumnVisible('_ebay_start_price') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('_ebay_start_price') ? visibleClass : invisibleClass"></span> eBay Price
	</button>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="profile_id"
		v-bind:class="isColumnVisible('profile_id') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('profile_id') ? visibleClass : invisibleClass"></span> Profile
	</button>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="account_id"
		v-bind:class="isColumnVisible('account_id') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('account_id') ? visibleClass : invisibleClass"></span> Account
	</button>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="status"
		v-bind:class="isColumnVisible('status') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('status') ? visibleClass : invisibleClass"></span> Status
	</button>

	<h4>
		Amazon
	</h4>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="_amazon_price"
		v-bind:class="isColumnVisible('_amazon_price') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('_amazon_price') ? visibleClass : invisibleClass"></span> Amazon Price
	</button>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="_amazon_minimum_price"
		v-bind:class="isColumnVisible('_amazon_minimum_price') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('_amazon_minimum_price') ? visibleClass : invisibleClass"></span> Min. Price
	</button>

	<button class="button toggle-column" v-on:click="btnToggleGridColumn" title="toggle" data-key="_amazon_maximum_price"
		v-bind:class="isColumnVisible('_amazon_maximum_price') ? 'activated' : 'inactive'">
		<span class="dashicons" v-bind:class="isColumnVisible('_amazon_maximum_price') ? visibleClass : invisibleClass"></span> Max. Price
	</button>

	<p>
		Click a button to enable or disable a particular column.
	</p>

	<p class="v-message">
		{{ message }}		
	</p>

</div>

