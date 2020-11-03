<?php #include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">
	p.desc {
		padding-left: 14px;
	}
</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/hammer-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<h2><?php echo __( 'Tutorial', 'wp-lister-for-ebay' ) ?></h2>
	
	<div style="width:640px;" class="postbox-container">
		<div class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">
				<form method="post" action="<?php echo $wpl_form_action; ?>">
				
					<?php if ( get_option('wplister_setup_next_step') != '0' ): ?>
					<div class="postbox" id="ConnectionSettingsBox">
						<h3 class="hndle"><span><?php echo __( 'Installation and Setup', 'wp-lister-for-ebay' ); ?></span></h3>
						<div class="inside">

							<?php echo $wpl_content_help_setup ?>

						</div>
					</div>
					<div class="submit" style="padding-top:0">
						<input type="submit" value="<?php echo __( 'Begin the setup', 'wp-lister-for-ebay' ); ?>" name="submit" class="button-primary">
					</div>
					<?php endif; ?>

					<div class="postbox" id="UserQuickstartBox">
						<h3 class="hndle"><span><?php echo __( 'Listing items', 'wp-lister-for-ebay' ); ?></span></h3>
						<div class="inside">

							<?php echo $wpl_content_help_listing ?>

						</div>
					</div>

					<div class="postbox" id="LinksBox">
						<h3 class="hndle"><span><?php echo __( 'Ressources', 'wp-lister-for-ebay' ); ?></span></h3>
						<div class="inside">

							<p><strong><?php echo __( 'Helpful links', 'wp-lister-for-ebay' ); ?></strong></p>
							<p class="desc" style="display: block;">
								<a href="https://www.wplab.com/plugins/wp-lister/faq/" target="_blank"><?php echo __( 'FAQ', 'wp-lister-for-ebay' ); ?></a> <br>
								<a href="https://www.wplab.com/plugins/wp-lister/documentation/" target="_blank"><?php echo __( 'Documentation', 'wp-lister-for-ebay' ); ?></a> <br>
								<a href="https://www.wplab.com/plugins/wp-lister/installing-wp-lister/" target="_blank"><?php echo __( 'Installing WP-Lister', 'wp-lister-for-ebay' ); ?></a> <br>
								<a href="https://www.wplab.com/plugins/wp-lister/screencasts/" target="_blank"><?php echo __( 'Screencasts', 'wp-lister-for-ebay' ); ?></a> <br>
								<a href="https://www.wplab.com/plugins/wp-lister/changelog/" target="_blank"><?php echo __( 'Changelog', 'wp-lister-for-ebay' ); ?></a> <br>
							</p>
							<br class="clear" />

						</div>
					</div>

				</form>
			</div>
		</div>
	</div>

	<script type="text/javascript">
	</script>

</div>