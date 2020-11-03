<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	th.column-details {
		width: 45%;
	}

	th.column-item_title {
		width: 30%;
	}

	th.column-flag_read {
		width: 32px;
	}

</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/hammer-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<h2><?php echo __( 'eBay Messages', 'wp-lister-for-ebay' ) ?></h2>
	<?php echo $wpl_message ?>


	<!-- show profiles table -->
	<?php $wpl_messagesTable->views(); ?>
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="profiles-filter" method="post" action="<?php echo $wpl_form_action; ?>" >
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <!-- Now we can render the completed list table -->
        <?php //wp_nonce_field( 'wplister_messages_action' ); ?>
		<?php $wpl_messagesTable->search_box( __( 'Search', 'wp-lister-for-ebay' ), 'message-search-input' ); ?>
        <?php $wpl_messagesTable->display() ?>
    </form>

	<br style="clear:both;"/>


	<p>
	<?php if ( get_option('wplister_cron_last_run') ) : ?>
		<?php echo __( 'Last run', 'wp-lister-for-ebay' ); ?>: 
		<?php echo human_time_diff( get_option('wplister_cron_last_run'), current_time('timestamp',1) ) ?> ago &ndash;
	<?php endif; ?>

	<?php if ( wp_next_scheduled( 'wplister_update_auctions' ) ) : ?>
		<?php echo __( 'Next scheduled update', 'wp-lister-for-ebay' ); ?>: 
		<?php echo human_time_diff( wp_next_scheduled( 'wplister_update_auctions' ), current_time('timestamp',1) ) ?>
		<?php echo wp_next_scheduled( 'wplister_update_auctions' ) < current_time('timestamp',1) ? 'ago' : '' ?>
	<?php elseif ( get_option('wplister_cron_auctions') == 'external' ) : ?>
		<?php echo __( 'Background updates are executed by an external cron job.', 'wp-lister-for-ebay' ); ?>
	<?php else: ?>
		<?php echo __( 'Automatic background updates are currently disabled.', 'wp-lister-for-ebay' ); ?>
	<?php endif; ?>
	</p>

	<form method="post" action="<?php echo $wpl_form_action; ?>">
		<p>
			<?php wp_nonce_field( 'wplister_messages_action' ); ?>
			<input type="hidden" name="action" value="wple_update_messages" />
			<input type="submit" value="<?php echo __( 'Update messages', 'wp-lister-for-ebay' ) ?>" name="submit" class="button"
				   title="<?php echo __( 'Update recent messages from eBay.', 'wp-lister-for-ebay' ) ?>">
		</p>
	</form>


</div>