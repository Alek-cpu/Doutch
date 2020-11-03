<?php
/*
 * Table Rate Shipping Method Extender Class
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

// Check if WooCommerce is active
if ( class_exists( 'WooCommerce' ) ) {

	if ( class_exists( 'BE_Table_Rate_Method_Conditions' ) ) return;

	class BE_Table_Rate_Method_Conditions {

		/*
		 * Table Rates Options Class
		 */
		private $table_rate_options;

		/*
		 * Table Rates Options Class
		 */
		private $save_name;

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
		function __construct( $shipping_method = null ) {

			add_filter( 'woocommerce_shipping_instance_form_fields_betrs_shipping', array( $this, 'add_instance_form_fields' ), 10, 1 );
			add_filter( 'betrs_shipping_altered_form_fields_betrs_shipping', array( $this, 'add_instance_form_fields' ), 10, 1 );
			add_filter( 'betrs_custom_restrictions', array( $this, 'validate_conditions' ), 10, 3 );

	   		// add ajax commands
			add_action( 'wp_ajax_betrs_add_method_condition', array( $this, 'add_method_condition' ) );
			add_action( 'wp_ajax_betrs_add_method_condition_extras', array( $this, 'add_method_condition_extras' ) );
		}


		/**
		 * Setup & display table structure
		 */
		public function init_table_ops() {

			if( empty( $this->table_rate_options ) )
	    		$this->table_rate_options = new BETRS_Table_Options();
	    }


		/**
		 * Get settings fields for instances of this shipping method (within zones).
		 *
		 * @access public
		 * @return array
		 */
		public function add_instance_form_fields( $fields ) {
			
			// Setup table of rates section
			$fields['conditions'] = array(
				'title'				=> __( 'Method Conditions', 'be-table-ship' ),
				'callback'			=> array( $this, 'section_method_conditions' ),
				//'sanitize_callback'	=> array( $this, 'process_method_conditions' ), // to be added in WooCommerce 3.4
				'type' 				=> 'method_conditions',
				'default' 			=> array(),
				'description' 		=> __( 'These optional conditions will be required in order for any shipping options below to be returned. They are applied to the order as a whole regardless to your \'Base Table Rates\' selection above.', 'be-table-ship' ),
				'priority'			=> 15,
				);

			return $fields;
		}


		/**
		 * Process settings for table of rates
		 *
		 * @access public
		 * @return void
		 */
		public function process_method_conditions( $save_name ) {
			global $betrs_shipping;

			$saved_conditions = array();
			
			if( isset( $_POST['method_cond'] ) && is_array( $_POST['method_cond'] ) ) {

				foreach( $_POST['method_cond'] as $key => $cond ) {
					// sanitize the first and second conditional entries
					$cond_type_processed = sanitize_title( $_POST['method_cond'][ $key ] );
					$cond_secondary_processed = sanitize_text_field( $_POST['method_cond_secondary'][ $key ] );

					// sanitize tertiary value
					if( isset( $_POST['method_cond_tertiary'][ $key ] ) && is_array( $_POST['method_cond_tertiary'][ $key ] ) ) {
						$cond_tertiary_processed = array_map( 'intval', $_POST['method_cond_tertiary'][ $key ] );
					} else {
						$cond_tertiary_processed = sanitize_text_field( $_POST['method_cond_tertiary'][ $key ] );

						// sanitize prices, weight, and dimensions according to locale
		                if( $cond_type_processed == 'subtotal' ) {
		                    $cond_tertiary_processed = wc_format_localized_price( $cond_tertiary_processed );
		                } elseif( $cond_type_processed == 'weight' ) {
		                    $cond_tertiary_processed = floatval( $cond_tertiary_processed );
		                } elseif( array_key_exists( $cond_type_processed, $betrs_shipping->table_rates->get_dimensions_types() ) ) {
		                    $cond_tertiary_processed = floatval( $cond_tertiary_processed );
		                }
					}

					$saved_conditions[] = array(
						'cond_type'					=> $cond_type_processed,
						'cond_secondary'			=> $cond_secondary_processed,
						'cond_tertiary'				=> $cond_tertiary_processed,
						);
				}
			}

			$saved_conditions = apply_filters( 'betrs_processed_method_conditions_settings', $saved_conditions );
			update_option( sanitize_title( $save_name ), $saved_conditions );
		}


		/**
		 * Determine results of conditions
		 *
		 * @access public
		 * @return void
		 */
		public function validate_conditions( $results_ar, $package, $method ) {
			// get conditions and exit if empty
			$conditions = get_option( $method->get_method_conditions_save_name() );

			if( empty( $conditions ) ) return $results_ar;

			// setup necessary calculation functions
			$table_rates = get_option( $method->get_options_save_name() );
			$calcClass = new BE_Table_Rate_Calculate( $method, $table_rates );

			// calculate order statistics
			$results = array();
			$cart_data = array( 'per-order' => $calcClass->calculate_totals_order( $package['contents'] ) );
			foreach( $conditions as $cond ) {
				$results[] = $calcClass->determine_condition_result( $cond, $cart_data['per-order'] );
			}

			// add the appropriate result to the array
			if( in_array( true, $results ) && !in_array( false, $results ) )
				$results_ar[] = true;
			else
				$results_ar[] = false;

			return $results_ar;
		}


		/**
		 * Display settings for the Method Conditions section.
		 *
		 * @access public
		 * @return array
		 */
		public function section_method_conditions() {
?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label>Conditions</label>
				</th>
				<td class="forminp">
					<table id="method_conditions">
					<?php $this->display_conditions_list(); ?>
					</table>
					<p><a href="#" class="betrs_add_method_cond"><?php _e( 'Add Condition', 'be-table-ship' ); ?></a></p>
				</td>
			</tr>
		</tbody>
	</table>
<?php
		}


		/**
		 * Create list of already added conditions.
		 *
		 * @access public
		 */
		public function display_conditions_list() {

			$conditions = get_option( $this->save_name );
			if( $conditions && is_array( $conditions ) && ! empty( $conditions ) ) {
				foreach( $conditions as $key => $cond ) {
					echo $this->display_condition( $cond, $key );
				}
			}

		}


		/**
		 * Display a single condition row.
		 *
		 * @access public
		 */
		public function display_condition( $item, $cond_ID ) {
			global $betrs_shipping;
	        
	        $price = ( isset( $item['price'] ) ) ? wc_format_decimal( $item['price'], '' ) : '';
	        $type = ( isset( $item[ 'cond_type' ] ) ) ? sanitize_title( $item[ 'cond_type' ] ) : 'subtotal';
	        $cond_secondary_name = 'method_cond_secondary';
	        $cond_tertiary_name = 'method_cond_tertiary';
	        $table_rate_ops = $betrs_shipping->table_rates->get_table_rate_ops_class();

	        $return = '<tr><td>';

	        // setup select box options
	        $return .= '<select name="method_cond[]" class="method_cond">';
	        foreach( $betrs_shipping->table_rates->conditional_statements as $key => $value )
	            $return .= '<option value="' . sanitize_title( $key ) . '" ' . selected( $type, $key, false ) . '>' . sanitize_text_field( $value ) . '</option>';
	        $return .= '</select>';

	        $return .= $table_rate_ops->generate_conditions_section_extras( $type, $item, $cond_ID );

	        $return .= '</td><td class="del-col"><span class="betrs_delete_method_cond betrs-small-delete"></span></td></tr>';

	        return $return;

		}


		/**
		 * Generate new condition settings row
		 *
		 * @access public
		 */
		public function add_method_condition() {

	    	// Exit if no option ID is provided
	    	if( ! isset( $_POST['condID'] ) ) die();

			// Initialize necessary variables
	    	$cond_ID = (int) $_POST['condID'];

			// setup condition for display
			echo $this->display_condition( array(), $cond_ID );

			die();
		}


		/**
		 * Generate new condition settings row
		 *
		 * @access public
		 */
		public function add_method_condition_extras() {
			global $betrs_shipping;

			// Initialize necessary variables
	    	$cond_ID = (int) $_POST['condID'];
	        $cond_secondary_name = 'method_cond_secondary';
	        $cond_tertiary_name = 'method_cond_tertiary';
	        $table_rate_ops = $betrs_shipping->table_rates->get_table_rate_ops_class();

			// setup condition for display
			echo $table_rate_ops->generate_conditions_section_extras( sanitize_title( $_POST['selected'] ), array(), $cond_ID );

			die();
		}


	    /**
	     * setup the instance ID for saving purposes
	     */
	    public function set_save_name( $method_save_name ) {

	    	$this->save_name = $method_save_name;
	    }

	}

}

?>