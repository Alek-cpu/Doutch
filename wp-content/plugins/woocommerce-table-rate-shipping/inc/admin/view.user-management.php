<?php
/**
 * Admin View: User Shipping Management
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_tab = ! empty( $_REQUEST['instance_id'] ) ? 'instance' : 'manage';
$current_tab_label = ( $current_tab == 'instance' ) ? __( 'Edit Shipping Method', 'be-table-ship' ) : __( 'Manage Shipping', 'be-table-ship' );

?>
<div id="betrs_user_shipping_settings" class="wrap woocommerce">
	<form method="<?php echo esc_attr( apply_filters( 'woocommerce_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<h1 class="wp-heading-inline"><?php echo esc_html( $current_tab_label ); ?></h1>

		<?php do_action( 'betrs_user_shipping_' . $current_tab ); ?>

		<p class="submit">
			<?php if ( $current_tab == 'instance' && empty( $GLOBALS['hide_save_button'] ) ) : ?>
				<button name="save" class="button-primary woocommerce-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>"><?php esc_html_e( 'Save changes', 'woocommerce' ); ?></button>
			<?php endif; ?>
			<?php wp_nonce_field( 'woocommerce-settings' ); ?>
		</p>
	</form>
</div>