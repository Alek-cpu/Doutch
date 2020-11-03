<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">
	
	td.column-profile_name a.title_link {
		color: #555;
	}
	td.column-profile_name a.title_link:hover {
		color: #D54E21;
	}

	th.column-profile_name {
		width: 25%;
	}

</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/hammer-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<h2><?php echo __( 'Profiles', 'wp-lister-for-ebay' ) ?> <a href="<?php echo $wpl_form_action; ?>&action=add_new_profile" class="add-new-h2"><?php echo __('Add New', 'wp-lister-for-ebay' ) ?></a> </h2>
	<?php echo $wpl_message ?>


	<!-- show profiles table -->
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="profiles-filter" method="get" action="<?php echo $wpl_form_action; ?>" >
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <!-- Now we can render the completed list table -->
		<?php $wpl_profilesTable->search_box( __( 'Search', 'wp-lister-for-ebay' ), 'profile-search-input' ); ?>
        <?php $wpl_profilesTable->display() ?>
    </form>
	<br style="clear:both;"/>

    <form id="profiles-addnew" method="get" action="<?php echo $wpl_form_action; ?>" style="display: inline;">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <input type="hidden" name="action" value="add_new_profile" />

		<input type="submit" value="<?php echo __( 'Add New Profile', 'wp-lister-for-ebay' ) ?>" name="submit" class="button">
    </form>

	<form id="profiles-upload" method="post" action="<?php echo $wpl_form_action; ?>" enctype="multipart/form-data" style="display: inline;">
        <a href="#" onclick="jQuery('#wple_file_upload_profile').click();return false;" class="button-secondary">
        	<?php echo __( 'Upload Profile', 'wp-lister-for-ebay' ); ?>
        </a> 

        <input type="hidden" name="action" value="wple_upload_listing_profile" />
        <?php wp_nonce_field( 'wple_upload_listing_profile' ); ?>
        <input type="file" id="wple_file_upload_profile" name="wple_file_upload_profile" onchange="this.form.submit();" style="display:none" />
    </form>
	<br style="clear:both;"/>

	<!--
	<p>
		debug info below:
	</p>
	-->

	<?php if ( get_option('wplister_log_level') > 5 ): ?>
	<pre><?php #print_r($wpl_profiles); ?></pre>
	<?php endif; ?>

	<script type="text/javascript">
		jQuery( document ).ready(
			function () {
		
				// ask again before deleting
				jQuery('.row-actions .delete a').on('click', function() {
					return confirm("<?php echo __( 'Are you sure you want to delete this item?.', 'wp-lister-for-ebay' ) ?>");
				})
	
			}
		);
	
	</script>

</div>