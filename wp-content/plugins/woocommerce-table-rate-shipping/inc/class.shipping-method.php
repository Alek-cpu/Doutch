<?php
/*
 * Table Rate Shipping Method Extender Class
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

// Check if WooCommerce is active
if ( class_exists( 'WooCommerce' ) ) {

	if ( ! class_exists('WC_Shipping_Method')) return;

	if ( class_exists( 'BE_Table_Rate_Method' ) ) return;

	class BE_Table_Rate_Method extends WC_Shipping_Method {

		/*
		 * Table Rates from Database
		 */
		protected $options_save_name;
		
		/*
		 * Table Rates from Database
		 */
		protected $m_conds_save_name;

		/*
		 * BE_Table_Rate_Calculate class.
		 */
		protected $calcClass;

		/*
		 * Table Rates from Database
		 */
		public $default_option;

		/**
		 * Cloning is forbidden. Will deactivate prior 'instances' users are running
		 *
		 * @since 4.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cloning this class could cause catastrophic disasters!', 'be-table-ship' ), '4.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 4.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Unserializing is forbidden!', 'be-table-ship' ), '4.0' );
		}

		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		function __construct( $instance_id = 0 ) {
			global $betrs_shipping;

			$this->id 					= 'betrs_shipping';
			$this->instance_id 			= absint( $instance_id );
	 		$this->method_title 		= __( 'Table Rate', 'be-table-ship' );
	 		$this->method_description 	= __( 'Charge varying rates based on user defined conditions', 'be-table-ship' );
			$this->supports 			= array( 'shipping-zones', 'instance-settings' );
			$this->options_save_name	= $this->id . '_options-' . $this->instance_id;
			$this->m_conds_save_name	= $this->id . '_method_conds-' . $this->instance_id;
			$this->default 				= "";

			// Initialize settings
			$this->init();
			$betrs_shipping->method_conditions->set_save_name( $this->get_method_conditions_save_name() );

			// additional hooks for post-calculations settings
			add_filter( 'betrs_shipping_rate_description', array( $this, 'shortcode_free_counter' ), 10, 5 );
			add_filter( 'woocommerce_shipping_chosen_method', array( $this, 'select_default_rate' ), 10, 2 );
			add_filter( 'woocommerce_package_rates', array( $this, 'hide_shipping_when_free_is_available' ), 100 );
			add_filter( 'betrs_calculated_table_rate_options', array( $this, 'hide_other_options' ), 100, 1 );
			add_filter( 'woocommerce_shipping_' . $this->id . '_instance_settings_values', array( $this, 'update_management_permissions' ), 10, 2 );

		}


		/**
		* init function.
		* initialize variables to be used
		*
		* @access public
		* @return void
		*/
		function init() {
			// Load the form fields.
			$this->instance_form_fields = include( 'instance-settings-fields.php' );
			$this->instance_form_fields = apply_filters( 'woocommerce_shipping_instance_form_fields_' . $this->id, $this->instance_form_fields );

			// Define user set variables
			$this->title 				= $this->get_instance_option( 'title' );
			$this->tax_status 			= $this->get_instance_option( 'tax_status' );
			$this->condition 			= $this->get_instance_option( 'condition' );
			$this->user_limitation 		= $this->get_instance_option( 'user_limitation' );
			$this->user_limit_roles 	= $this->get_instance_option( 'user_limitation_roles' );
			$this->user_modification 	= $this->get_instance_option( 'user_modification' );
			$this->user_mod_users 		= ( version_compare( WC_VERSION, '3.0', ">=" ) ) ? $this->get_instance_option( 'user_modification_users' ) : '';
			$this->user_mod_roles 		= $this->get_instance_option( 'user_modification_roles' );
			$this->includetax 			= $this->get_instance_option( 'includetax' );
			$this->volumetric_number 	= $this->get_instance_option( 'volumetric_number' );
			$this->volumetric_operand 	= $this->get_instance_option( 'volumetric_operand' );
			$this->volumetric_exclude 	= $this->get_instance_option( 'volumetric_exclude' );
			$this->includetax	 		= $this->get_instance_option( 'includetax' );
			$this->include_coupons 		= $this->get_instance_option( 'include_coupons' );
			$this->single_class 		= $this->get_instance_option( 'single_class' );
			$this->round_weight 		= $this->get_instance_option( 'round_weight' );
			$this->hide_method 			= $this->get_instance_option( 'hide_method' );

			do_action( 'betrs_shipping_initialize_settings' );

			// Setup empty array for selecting defaults
			$this->default_rates = array();
		}


		/**
		 * Get settings fields for instances of this shipping method (within zones).
		 *
		 * @access public
		 * @return array
		 */
		public function get_instance_form_fields() {

			$fields = ( is_array( $this->instance_form_fields ) ) ? $this->alter_options_array( $this->instance_form_fields ) : array();

			return $fields;
		}


		/**
		 * Initialize settings for instances.
		 *
		 * @access public
		 * @return array
		 */
		public function init_instance_settings() {
			$this->instance_settings = get_option( $this->get_instance_option_key(), null );

			// If there are no settings defined, use defaults.
			if ( ! is_array( $this->instance_settings ) ) {
				$form_fields             = $this->get_instance_form_fields();
				$this->instance_settings = array_merge( array_fill_keys( array_keys( $form_fields ), '' ), wp_list_pluck( $form_fields, 'default' ) );
			}
		}


		/**
		* condense multidimensional array into one array of options
		*
		* @access public
		* @return array
		*/
		function alter_options_array( $instance_form_fields ) {

			$copy = array();
			foreach( $instance_form_fields as $kg => $group )
				if( isset( $group[ 'settings' ] ) )
					foreach( $group[ 'settings' ] as $ko => $option )
						$copy[ $ko ] = $option;

			// add empty 'table_rates' field for saving purposes
			$copy[ 'table_rates' ] = array(
				'title' 		=> __( 'Table of Rates', 'be-table-ship' ),
				'type' 			=> 'table_rates',
				'default' 		=> array(),
				'description' 	=> '',
				);

			return apply_filters('betrs_shipping_altered_form_fields_' . $this->id, $copy );
		}


		/**
		 * Admin Panel Options
		 *
		 * @access public
		 * @return void
		 */
		public function admin_options() {
			global $woocommerce;

			$section_counter = 1;
?>
<div id="BETRS-method-options">
	<a href="#" class="expand">Expand All</a> | 
	<a href="#" class="collapse">Collapse All</a>
</div>
<?php
			if( is_array( $this->instance_form_fields ) ) :
				// sort by priority number
				$priority = array();
				foreach( $this->instance_form_fields as $key => $instance_fields )
					$priority[ $key ] = ( isset( $instance_fields[ 'priority' ] ) ) ? absint( $instance_fields[ 'priority' ] ) : 30;
				array_multisort( $priority, SORT_ASC, SORT_NUMERIC, $this->instance_form_fields );

				foreach( $this->instance_form_fields as $key => $instance_fields ) :
					
					if( isset( $instance_fields ) && is_array( $instance_fields ) ) :

						$this->generate_settings_section( $key, $instance_fields, $section_counter );
						$section_counter++;

					endif;

				endforeach;

			endif;

			// Setup table of rates section
			$table_rate_section = array(
				'title'		=> __( 'Table of Rates', 'be-table-ship' ),
				'callback'	=> array( $this, 'section_table_rates' ),
				);
			$this->generate_settings_section( 'table_rates', $table_rate_section, $section_counter++ );
		}



		/**
		 * Section for Admin Panel Options
		 *
		 * @access public
		 * @return void
		 */
		public function generate_settings_section( $key, $admin_section, $section_counter = 1 ) {
?>
<div id="<?php echo $key; ?>" class="betrs_settings_section">

<?php if( isset( $admin_section[ 'title' ] ) ) : ?>
	<h3><span class="counter"><?php echo $section_counter; ?></span>
		<span><?php echo $admin_section[ 'title' ]; ?></span>
		</h3>
<?php endif; ?>

	<div class="betrs_settings_inner">

<?php
		if( isset( $admin_section[ 'description' ] ) && ! empty( $admin_section[ 'description' ] ) ) {
			echo '<p>' . sanitize_text_field( $admin_section[ 'description' ] ) . '</p>';
		}
?>
		<?php if( isset( $admin_section[ 'settings' ] ) && is_array( $admin_section[ 'settings' ] ) ) : ?>
		<table class="form-table">
			<?php $this->generate_settings_html( $admin_section[ 'settings' ] ); ?>
		</table>

<?php
		elseif( isset( $admin_section[ 'callback' ] ) ) : // Call user function for custom option blocks
			if( is_callable( $admin_section[ 'callback' ] ) )
				call_user_func( $admin_section[ 'callback' ] );
		endif;
?>
		<p class="next-link"><a href="#" class="button">Next</a></p>
	</div>
</div>
<?php
		}



		/**
		 * Section for Admin Panel Options
		 *
		 * @access public
		 * @return void
		 */
		public function section_table_rates() {
			global $betrs_shipping;

			$betrs_shipping->table_rates->set_saved_table_rates( get_option( $this->get_options_save_name() ) );
			$betrs_shipping->table_rates->display();
		}


		/**
		 * Process settings for table of rates
		 *
		 * @access public
		 * @return void
		 */
		public function validate_table_rates_field( $key, $data ) {
			global $betrs_shipping;
			
			$betrs_shipping->table_rates->process_table_rates( $this->get_options_save_name() );
		}


		/**
		 * Process settings for method conditions
		 *
		 * @access public
		 * @return void
		 */
		public function validate_method_conditions_field( $key, $data ) {
			global $betrs_shipping;
			
			$betrs_shipping->method_conditions->process_method_conditions( $this->get_method_conditions_save_name() );
		}


		/**
		 * calculate_shipping function.
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return void
		 */
		function calculate_shipping( $package = array() ) {
			// do not calculate if user permissions are set and not qualified
			if( ! $this->user_restrictions_qualified() ) return;

			// check for other external requirements
			$results = apply_filters( 'betrs_custom_restrictions', array( true ), $package, $this );
			if( ! in_array( true, (array) $results ) || in_array( false, (array) $results ) ) return;

			// setup necessary class for calculations
			$table_rates = get_option( $this->get_options_save_name() );
			$this->calcClass = new BE_Table_Rate_Calculate( $this, $table_rates );

			// get qualified shipping rates
			$rates = $this->calcClass->calculate_shipping( $package );
			$rates = apply_filters( 'betrs_calculated_table_rate_options', $rates );
			
			// send shipping rates to WooCommerce
			if( is_array( $rates ) && count( $rates ) > 0 ) {
				// cycle through rates to send and alter post-add settings
				foreach( $rates as $key => $rate ) {

					$this->add_rate( array(
						'id'        => $rate['id'],
						'label'     => apply_filters( 'betrs_shipping_rate_label', $rate['label'], $key, $this->instance_id ),
						'cost'      => $rate['cost'],
						'meta_data' => ( ! empty( $rate['description'] ) ) ? array( 'description' => (object) apply_filters( 'betrs_shipping_rate_description', $rate['description'], $key, $this->instance_id, $rate['row_id'], $rate['contents_cost'] ) ) : array(),
						'package'   => $package,
						));

					if( $rate['default'] == 'on' )
						$this->default = $rate['id'];

				}

			}

		}


	    /**
	     * determine if current user qualifies for this method.
	     *
	     * @access public
	     * @param null
	     * @return bool
	     */
	    function user_restrictions_qualified() {

			switch( $this->user_limitation ) {
				case 'specific-roles':
					if( empty( $this->user_limit_roles ) || ! is_array( $this->user_limit_roles ) )
						return false;

					// retrieve user's roles if logged in
					if( is_user_logged_in() ) {
						$current_user = wp_get_current_user();
						$current_user_data = get_userdata( $current_user->ID );
						$current_user_roles = $current_user_data->roles;
					} else {
						$current_user_roles = array( "guest" );
					}

					// determine if user's role is accepted
					foreach( $this->user_limit_roles as $role ) {
						if( in_array( $role, $current_user_roles ) )
							return true;
					}
					break;

				case 'everyone':
					return true;
					break;
				default:
					return (bool) apply_filters( 'betrs_user_restrictions_condition', true, $this->user_limitation );
					break;
			}

			return false;
		}


	    /**
	     * assign permissions to users/roles selected.
	     *
	     * @access public
	     * @param array $instance_settings, array $method
	     * @return bool
	     */
	    function update_management_permissions( $instance_settings, $method ) {
	    	$post_data = $method->get_post_data();
	    	if( ! is_array( $post_data ) || empty( $post_data ) ) return $instance_settings;

	    	switch( sanitize_title( $post_data['woocommerce_betrs_shipping_user_modification'] ) ) {
	    		case 'specific-users':
	    			$selected_users = $post_data['woocommerce_betrs_shipping_user_modification_users'];
	    			if( is_array( $selected_users ) && ! empty( $selected_users ) ) {
		    			foreach( $selected_users as $user_id ) {
					    	$user = new WP_User( (int) $user_id );
							$user->add_cap( 'betrs_manage_shipping' );
		    			}
		    		}
	    			break;
	    		case 'specific-roles':
	    			$selected_roles = $post_data['woocommerce_betrs_shipping_user_modification_roles'];
	    			if( is_array( $selected_roles ) && ! empty( $selected_roles ) ) {
		    			foreach( $selected_roles as $role_id ) {
					    	$role = get_role( sanitize_title( $role_id ) );
							$role->add_cap( 'betrs_manage_shipping' );
		    			}
		    		}
	    			break;
	    		default:
	    			break;
	    	}

			return $instance_settings;
	    	
	    }


	    /**
	     * alter the default rate if one is chosen in settings.
	     *
	     * @access public
	     * @param mixed $package
	     * @return bool
	     */
	    function select_default_rate( $chosen_method, $_available_methods ) {

			//Select the 'Default' method from WooCommerce settings
			if( array_key_exists( $this->default, $_available_methods ) ) {

				return $this->default;
		    }

			return $chosen_method;
	    }


	    /**
		 * Hide shipping rates when free shipping is available.
		 * Updated to support WooCommerce 2.6 Shipping Zones.
		 *
	     * @access public
		 * @param array $rates Array of rates found for the package.
		 * @return array
		 */
		function hide_shipping_when_free_is_available( $rates ) {
			if( $this->hide_method !== 'yes' ) return $rates;

			// determine if free shipping is available
			$free_shipping = false;
			foreach ( $rates as $rate_id => $rate ) {
				if ( 'free_shipping' === $rate->method_id ) {
					$free_shipping = true;
					break;
				}
			}
			// if available, remove all options from this method
			if( $free_shipping ) {
				foreach ( $rates as $rate_id => $rate ) {
					if ( $this->id === $rate->method_id && strpos( $rate_id, $this->id . ':' . $this->instance_id . '-') !== false ) {
						unset( $rates[ $rate_id ] );
					}
				}
			}

			return $rates;
		}


	    /**
		 * Hide shipping rates when one has option enabled.
		 *
	     * @access public
		 * @param array $rates Array of rates found for the package.
		 * @return array
		 */
		function hide_other_options( $rates ) {
			$hide_key = false;

			// return if no rates have been added
			if( ! isset( $rates ) || empty( $rates ) )
				return $rates;

			// cycle through available rates
			foreach( $rates as $key => $rate ) {
				if( $rate['hide_ops'] === 'on' ) {
					$hide_key = $key;
				}
			}

			if( $hide_key ) {
				return array( $hide_key => $rates[ $hide_key ] );
			}

			return $rates;
		}


	    /**
		 * Return formatted price for remainder until free shipping is qualified
		 *
	     * @access public
		 * @param array $atts Array of shortcode parameters.
		 * @return string
		 */
		function shortcode_free_counter( $description, $key, $instance_id, $row_id, $contents_cost  ) {
			$remainder = 0;
			$qualifier = '{free-shipping@';

			// return if description does not contain shortcode
			if( ! strstr( $description, $qualifier ) )
				return $description;

			// determine the free shipping amount
			$price_string = substr( $description, strpos( $description, $qualifier ) + strlen( $qualifier ) );
			$free_ship_price = floatval( substr( $price_string, 0, strpos( $price_string, '}' ) ) );
		    $remainder = $free_ship_price - $contents_cost;

		    // reformat description to include price
		    $description = preg_replace('#\\' . $qualifier . '[^\]]+\}#', wc_price( $remainder ), $description);

			return $description;
		}


	    /**
		 * Allow 3rd party to retrieve shipping calculations class
		 *
	     * @access protected
		 * @param void
		 * @return string
		 */
		public function get_class_calculate_shipping() {

			return $this->calcClass;
		}


	    /**
		 * Allow 3rd party to override the options key to allow managing of multiple sets of options (e.g. multi-currency support)
		 *
	     * @access protected
		 * @param void
		 * @return string
		 */
		public function get_options_save_name() {

			return apply_filters('betrs_instance_options_save_name', $this->options_save_name, $this);
		}


	    /**
		 * Allow 3rd party to override the options key to allow managing of multiple sets of options
		 *
	     * @access protected
		 * @param void
		 * @return string
		 */
		public function get_method_conditions_save_name() {

			return apply_filters('betrs_instance_method_conditions_save_name', $this->m_conds_save_name, $this);
		}

	}

}

?>