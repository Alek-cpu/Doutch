<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	td.column-price, 
	td.column-fees {
		text-align: right;
	}
	th.column-callname {
		width: 42%;
	}
	th.column-user {
		width: 10%;
	}
	th.column-success {
		width: 15%;
	}

	.widefat tbody th.check-column {
		padding-bottom: 0;
	}
</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/hammer-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<h2>
		<?php echo __( 'Logs', 'wp-lister-for-ebay' ) ?>
		<a href="<?php echo $wpl_form_action; ?>" class="add-new-h2">Refresh</a>
	</h2>
	<?php echo $wpl_message ?>


	<!-- show log table -->
	<?php $wpl_logTable->views(); ?>

    <form id="logs-filter" method="get" action="<?php echo $wpl_form_action; ?>" >
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <input type="hidden" name="paged" value="<?php echo isset( $_REQUEST['paged'] ) ? esc_attr($_REQUEST['paged']) : '-1' ?>" />
        <input type="hidden" name="log_status" value="<?php echo isset( $_REQUEST['log_status'] ) ? esc_attr($_REQUEST['log_status']) : '' ?>" />
		<?php $wpl_logTable->search_box(__( 'Search', 'wp-lister-for-ebay' ), 'log-search-input'); ?>
    </form>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="logs-tableform" method="get" action="<?php echo $wpl_form_action; ?>" >
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <!-- Now we can render the completed list table -->
        <?php $wpl_logTable->display() ?>
    </form>


	<div class="submit" style="">

		<form method="post" action="<?php echo $wpl_form_action; ?>">
			<div class="submit" style="padding-top: 0; float: left;">
				<?php wp_nonce_field( 'wplister_clear_ebay_log' ); ?>
				<input type="hidden" name="action" value="wpl_clear_ebay_log" />
				<input type="submit" value="<?php echo __( 'Empty log', 'wp-lister-for-ebay' ) ?>" name="submit" class="button">
				<!-- &nbsp; current size: <?php echo $wpl_tableSize ?> mb -->
			</div>
		</form>

		<form method="post" action="<?php echo $wpl_form_action; ?>">
			<div class="submit" style="padding-top: 0; float: left; padding-left:15px;">
				<?php wp_nonce_field( 'wplister_optimize_ebay_log' ); ?>
				<input type="hidden" name="action" value="wpl_optimize_ebay_log" />
				<input type="submit" value="<?php echo __( 'Optimize log', 'wp-lister-for-ebay' ) ?>" name="submit" class="button">
			</div>
		</form>

	</div>

	<br style="clear:both;"/>
	Current log size: <?php echo $wpl_tableSize ?> mb

</div>