<?php

if ( ! defined( 'ABSPATH' ) )
	exit;

// Check if WooCommerce is active
if ( class_exists( 'Woocommerce' ) || class_exists( 'WooCommerce' ) ) {
		
	if ( class_exists( 'BETRS_User_Management' ) ) return;

	class BETRS_User_Management {

		/*
		 * Table Rates Options Class
		 */
		private $table_rate_options;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// exit if user does not have these permissions
			if( ! $this->user_has_permissions() ) return;

			// register necessary JS files from WooCommerce
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script( 'woocommerce_settings', WC()->plugin_url() . '/assets/js/admin/settings' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris', 'selectWoo' ), WC()->version, true );

			// register action hooks and filters
			add_action( 'admin_menu', array( $this, 'register_admin_pages' ) );
			add_action( 'load-toplevel_page_betrs-manage-shipping', array( $this, 'process_post_data' ) );
			add_action( 'betrs_user_shipping_manage', array( $this, 'display_management_home' ) );
			add_action( 'betrs_user_shipping_instance', array( $this, 'display_management_instance' ) );

		}


		/**
		 * add page to dashboard.
		 *
		 * @access public
		 * @return bool
		 */
		function register_admin_pages() {

		    add_menu_page(
		        __( 'Manage Shipping', 'be-table-ship' ),
		        __( 'Manage Shipping', 'be-table-ship' ),
		        'betrs_manage_shipping',
		        'betrs-manage-shipping',
		        array( $this, 'display' ),
		        'dashicons-cart',
		        52
		    );
		}


		/**
		 * determine if user can manage shipping.
		 *
		 * @access public
		 * @return bool
		 */
		function display() {

			wp_localize_script( 'woocommerce_settings', 'woocommerce_settings_params', array(
				'i18n_nav_warning' => __( 'The changes you made will be lost if you navigate away from this page.', 'woocommerce' ),
			) );

			// output HTML from external file
			include( dirname( __FILE__ ) . '/view.user-management.php' );
		}


		/**
		 * show main list page for management page.
		 *
		 * @access public
		 * @return bool
		 */
		function display_management_home() {

			// retrieve user information
			$user = wp_get_current_user();
			$user_id = $user->ID;
			$user_roles = $user->roles;

			// retrieve zones with table rate methods
			$shipping_zones = WC_Shipping_Zones::get_zones();
			foreach( $shipping_zones as $zone_id => $zone ) {
				$shipping_methods = $zone['shipping_methods'];
				$eligible_methods = array();

				// cycle through each method added to zone looking for Table Rate
				foreach( $shipping_methods as $instance_id => $method ) {
					if( $method->id == 'betrs_shipping' ) {
						$settings = $method->instance_settings;
						$eligible = false;

						// determine if user is eligible to modify this zone
						switch( $settings['user_modification'] ) {
							case 'specific-users':
								if( in_array( $user_id, $settings['user_modification_users'] ) ) {
									$eligible = true;
									$eligible_methods[ $instance_id ] = $method;
								}
								break;
							
							case 'specific-roles':
								$array_intersect = array_intersect( $settings['user_modification_users'], $user_roles );
								if( ! empty( $array_intersect ) ) {
									$eligible = true;
									$eligible_methods[ $instance_id ] = $method;
								}
								break;
							
							default:
								do_action( 'betrs_user_restriction_action' );
								break;
						}
					}
				}

				// print out list row if eligible
				if( $eligible ) {
?>
<div class="betrs-user-shipping-zone">
	<h2><?php echo esc_html( $zone['zone_name'] ); ?></h2>
	<h4><?php echo __( 'Zone regions', 'woocommerce' ) . ': ' . esc_html( $zone['formatted_zone_location'] ); ?></h4>

	<table class="betrs-user-shipping-method-table widefat">
		<thead>
			<tr>
				<th><?php _e( 'Shipping Method', 'woocommerce' ); ?></th>
				<th><?php _e( 'Enabled', 'woocommerce' ); ?></th>
				<th><?php _e( 'Description', 'woocommerce' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody class="betrs-user-shipping-method-rows">
			<?php foreach ($eligible_methods as $instance_id => $method) : ?>
			<tr>
				<td>
					<a href="<?php echo esc_html( admin_url( 'admin.php?page=betrs-manage-shipping&instance_id=' . esc_attr( $method->instance_id ) ) ); ?>"><?php echo esc_html( $method->title ); ?></a>
				</td>
				<td width="1%"><span class="betrs-enabled-<?php echo sanitize_title( $method->enabled ); ?>"><?php echo sanitize_title( $method->enabled ); ?></span></td>
				<td>
					<?php echo wp_kses_post( $method->method_description ); ?>
				</td>
				<td>
					<a class="button" href="<?php echo esc_html( admin_url( 'admin.php?page=betrs-manage-shipping&instance_id=' . esc_attr( $method->instance_id ) ) ); ?>"><?php _e( 'Manage', 'be-table-ship' ); ?></a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php
				}
			}
		}


		/**
		 * show zone edit page for management page.
		 *
		 * @access public
		 * @return bool
		 */
		function display_management_instance() {
			global $hide_save_button;

			// retrieve user information
			$user = wp_get_current_user();
			$user_id = $user->ID;
			$user_roles = $user->roles;
			$instance_id = (int) $_REQUEST['instance_id'];
			$shipping_method = WC_Shipping_Zones::get_shipping_method( $instance_id );

			// Exit if zone does not exist or is not a Table Rate method
			if( ! $shipping_method || $shipping_method->id != 'betrs_shipping' ) {
				echo "<h2>" . __( 'Sorry, you are not allowed to access this page.' ) . "</h2>";
				$hide_save_button = true;
				return;
			}

			// setup variables
			$settings = $shipping_method->instance_settings;
			$eligible = false;

			// determine if user is eligible to modify this zone
			switch( $settings['user_modification'] ) {
				case 'specific-users':
					if( in_array( $user_id, $settings['user_modification_users'] ) ) {
						$eligible = true;
					}
					break;
				
				case 'specific-roles':
					$intersect = array_intersect( $settings['user_modification_users'], $user_roles );
					if( ! empty( $intersect ) ) {
						$eligible = true;
					}
					break;
				
				default:
					do_action( 'betrs_user_restriction_action' );
					break;
			}

			// print out list row if eligible
			if( $eligible ) {
				$form_fields = $shipping_method->instance_form_fields;
?>
<p style="text-align: right"><a href="<?php echo esc_html( admin_url( 'admin.php?page=betrs-manage-shipping' ) ); ?>" class="page-title-action"><?php _e( 'Return to list of shipping zones', 'be-table-ship' ); ?></a></p>
<div class="betrs-user-shipping-zone">

	<?php foreach( $form_fields as $sid => $section ) : ?>
	
	<div id="<?php echo sanitize_title( $sid ); ?>" class="betrs_settings_section">
		<h4><?php echo esc_html( $section['title'] ); ?></h4>
		<table>
			<tbody>

				<?php if( isset( $section['settings'] ) ) : ?>
				
				<?php foreach( $section['settings'] as $fid => $field ) : if( $sid == 'user_permissions' && $fid != 'user_limitation' ) { continue; } ?>
				
				<tr>
					<th><?php echo $field['title']; ?></th>
					<td>
					<?php
						switch( $field['type'] ) {
							case 'checkbox':
								$toggle = ( $settings[ $fid ] == 'yes' ) ? 'yes' : 'no';
								echo '<span class="betrs-enabled-' . $toggle . '">' . $toggle . '</span>';
								break;
							case 'select':
								if( $sid == 'user_permissions' && $fid == 'user_limitation' && $settings[ $fid ] != 'everyone' ) {
									echo implode( ', ', $settings['user_limitation_roles'] );
								} else {
									echo sanitize_text_field( $field['options'][ $settings[ $fid ] ] );
								}
								break;
							default:
								if( empty( $settings[ $fid ] ) ) {
									echo "<em>" . __( 'Empty' ) . "</em>";
								} else {
									echo sanitize_text_field( $settings[ $fid ] );
								}
								break;
						}
					?></td>
				</tr>

				<?php endforeach; ?>

				<?php else : ?>

					<p><?php _e( 'None' ); ?></p>

				<?php endif; ?>

			</tbody>
		</table>
	</div>
	
	<?php endforeach; ?>

	<div id="table_rates" class="betrs_settings_section">

		<h4><?php _e( 'Table of Rates', 'be-table-ship' ); ?></h4>

		<?php $shipping_method->section_table_rates(); ?>

	</div>

</div>
<?php
			} else {
				echo "<h2>" . __( 'Sorry, you are not allowed to access this page.' ) . "</h2>";
			}
		}


		/**
		 * process post data if form has been submitted.
		 *
		 * @access public
		 * @return bool
		 */
		function process_post_data() {
			global $betrs_shipping;

			// exit if no form submitted
			if( empty( $_POST ) )
				return;

			// exit if user has zero permissions on this page
			if( ! $this->user_has_permissions() )
				return;

			// retrieve user information
			$user = wp_get_current_user();
			$user_id = $user->ID;
			$user_roles = $user->roles;
			$instance_id = (int) $_REQUEST['instance_id'];
			$shipping_method = WC_Shipping_Zones::get_shipping_method( $instance_id );

			// Exit if zone does not exist or is not a Table Rate method
			if( ! $shipping_method || $shipping_method->id != 'betrs_shipping' ) {
				wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );
			}

			// setup variables
			$settings = $shipping_method->instance_settings;
			$eligible = false;

			// determine if user is eligible to modify this zone
			switch( $settings['user_modification'] ) {
				case 'specific-users':
					if( in_array( $user_id, $settings['user_modification_users'] ) ) {
						$eligible = true;
					}
					break;
				
				case 'specific-roles':
					$temp_ar = array_intersect( $settings['user_modification_users'], $user_roles );
					if( ! empty( $temp_ar ) ) {
						$eligible = true;
					}
					break;
				
				default:
					do_action( 'betrs_user_restriction_action' );
					break;
			}

			// print out list row if eligible
			if( $eligible ) {
				$options_name = $shipping_method->get_options_save_name();
				$betrs_shipping->table_rates->process_table_rates( $options_name );
			} else {
				wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );
			}
		}


		/**
		 * determine if user can manage shipping.
		 *
		 * @access public
		 * @return bool
		 */
		function user_has_permissions() {
			// exit if user is not logged in
			if( ! is_user_logged_in() )
				return false;

			// exit if user has shop permissions. they should always manage shipping under the 'WooCommerce' tab
			if( current_user_can( 'manage_woocommerce' ) )
				return false;

			// exit if user has shop permissions. they should always manage shipping under the 'WooCommerce' tab
			if( ! current_user_can( 'betrs_manage_shipping' ) )
				return false;

			return true;
		}

	}

	return new BETRS_User_Management();

}

?>