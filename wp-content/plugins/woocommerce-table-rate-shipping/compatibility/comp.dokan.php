<?php
/*
 * Table Rate Shipping Method Extender Class
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

// Check if WooCommerce is active
if ( class_exists( 'WooCommerce' ) && function_exists('dokan') ) {

	if ( class_exists( 'BETRS_Dokan' ) ) return;

	class BETRS_Dokan {

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
		function __construct() {
			// modify the necessary settings values through hooks and filters
			add_filter( 'betrs_user_modification_types', array( $this, 'add_user_modification' ), 10, 1 );
			add_filter( 'woocommerce_shipping_instance_form_fields_betrs_shipping', array( $this, 'add_user_modification_settings' ), 10, 1 );
			add_filter( 'betrs_shipping_cost_conditionals', array( $this, 'add_vendor_condition' ), 10, 1 );
			add_filter( 'betrs_shipping_cost_conditionals_secondary', array( $this, 'add_vendor_condition_secondary' ), 10, 1 );
			add_action( 'betrs_shipping_cost_conditionals_tertiary', array( $this, 'add_vendor_condition_tertiary' ), 10, 5 );
			add_filter( 'betrs_calculated_totals-per_order', array( $this, 'get_vendor_data_order' ), 10, 2 );
			add_filter( 'betrs_calculated_totals-per_item', array( $this, 'get_vendor_data_item' ), 10, 2 );
			add_filter( 'betrs_calculated_totals-per_class', array( $this, 'get_vendor_data_class' ), 10, 2 );
			add_filter( 'betrs_determine_condition_result', array( $this, 'compare_vendor_condition' ), 10, 3 );

		}


		/**
		 * add_user_modification function.
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return array
		 */
		function add_user_modification( $user_types ) {
			$user_types['dokan-vendors'] = __( 'Specific Vendors', 'be-table-ship' );

			return $user_types;
		}


		/**
		 * add_user_modification_settings function.
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return array
		 */
		function add_user_modification_settings( $settings ) {
			// check for vendors setup before proceeding
			$dokan = dokan();
			if( ! isset( $dokan ) || ! isset( $dokan->vendors ) )
				return $settings;

			// get Dokan vendors
			$get_vendors = dokan_get_sellers( array( 'number' => -1 ) );
			$vendors = $get_vendors['users'];
			$vendors_ar = array();
			foreach( $vendors as $key => $vendor ) {
				$vendors_ar[ $vendor->data->ID ] = $vendor->data->display_name;
			}

			$settings['user_permissions']['settings']['user_modification_dokan'] = array(
				'title' 			=> __( 'Vendors', 'be-table-ship' ),
				'type' 				=> 'multiselect',
				'class'         	=> 'wc-enhanced-select',
				'default' 			=> '',
				'options' 			=> $vendor_ar,
			);

			return $settings;
		}


		/**
		 * add_vendor_condition function.
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return array
		 */
		function add_vendor_condition( $conditions ) {
		    // add new option to list
		    $conditions['dokan_vendor'] = 'Dokan Vendor';
		    
		    return $conditions;
		}


		/**
		 * add_vendor_condition_secondary function.
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return array
		 */
		function add_vendor_condition_secondary( $conditions ) {
		    // add new option to list
		    $conditions['includes']['conditions'][] = 'dokan_vendor';
		    $conditions['excludes']['conditions'][] = 'dokan_vendor';
		    
		    return $conditions;
		}


		/**
		 * add_vendor_condition_tertiary function.
		 *
		 * @access public
		 * @param string $cond_type, array $item, int $row_ID, int $option_ID (default: null), int $cond_key (default: 0)
		 * @return null
		 */
		function add_vendor_condition_tertiary( $cond_type, $item, $row_ID, $option_ID = null, $cond_key = 0 ) {
			// sanitize inputs
			$cond_tertiary = array();
			$cond_type = sanitize_title( $cond_type );
			$row_ID = (int) $row_ID;
			if( isset( $item['cond_tertiary'] ) && is_array( $item['cond_tertiary'] ) ) {
				$cond_tertiary = array_map( 'intval', $item['cond_tertiary'] );
			}

	        if( isset( $option_ID ) ) {
	            // setup table rate form fields
	            $option_ID = (int) $option_ID;
	            $op_name_tertiary = "cond_tertiary[" . $option_ID . "][" . $row_ID . "][" . $cond_key . "]";
	        } else {
	            // setup method condition form fields
	            $op_name_tertiary = "method_cond_tertiary[" . $row_ID . "]";
	        }

			// check if type is for Dokan
			if( sanitize_title( $cond_type ) != 'dokan_vendor' ) return;

			// get Dokan vendors
			$get_vendors = dokan_get_sellers( array( 'number' => -1 ) );
			$user_query = new WP_User_Query( array( 'role' => 'seller' ) );
			$vendors    = $user_query->get_results();
?>
<select name="<?php echo $op_name_tertiary; ?>[]" class="cond_tertiary wc-enhanced-select cond_multiple" multiple="multiple">
        <?php
            // decode if passed through importer
            if( is_string( $cond_tertiary ) )
                $cond_tertiary = json_decode( sanitize_text_field( $cond_tertiary ) );

            $sel_vendors = ( is_array( $cond_tertiary ) ) ? $cond_tertiary : array();
            foreach ( $vendors as $vendor ) {
            	$vendor_data = get_userdata( $vendor->ID );
                echo '<option value="' . esc_attr( $vendor->ID ) . '"' . selected( in_array( $vendor->ID, $sel_vendors ), true, false ) . '>' . wp_kses_post( $vendor_data->display_name ) . '</option>';
            }
        ?>
</select>
<?php
			return;
		}


		/**
		 * get_vendor_data_order function.
		 *
		 * @access public
		 * @param array $data, array $items
		 * @return array
		 */
		function get_vendor_data_order( $data, $items ) {
			$vendor_ids = array();

			// cycle through products
			if( is_array( $items ) && ! empty( $items ) ) {
				foreach( $items as $key => $item ) {
					$vendor = dokan_get_vendor_by_product( $item['data'] );
					$vendor_id = $vendor->id;

					if( is_array( $vendor_id ) ) {
						$merged = array_merge( $vendor_ids, $vendor_id );
						$vendor_ids = array_unique( $merged );
					} else {
						$vendor_ids[] = (int) $vendor_id;
					}
				}
				$data['dokan_vendors'] = $vendor_ids;
			}

			return $data;
		}


		/**
		 * get_vendor_data_item function.
		 *
		 * @access public
		 * @param array $data, array $items
		 * @return array
		 */
		function get_vendor_data_item( $data, $items ) {
			// cycle through products
			if( is_array( $data ) && ! empty( $data ) ) {
				foreach( $data as $key => $item ) {
					$vendor = dokan_get_vendor_by_product( $key );
					$vendor_id = $vendor->id;

					if( is_array( $vendor_id ) ) {
						$data[ $key ]['dokan_vendors'] = $vendor_id;
					} else {
						$data[ $key ]['dokan_vendors'] = array( intval( $vendor_id ) );
					}
				}
			}

			return $data;
		}


		/**
		 * get_vendor_data_class function.
		 *
		 * @access public
		 * @param array $data, array $items
		 * @return array
		 */
		function get_vendor_data_class( $data, $items ) {
			// cycle through products
			if( is_array( $data ) && ! empty( $data ) ) {
				foreach( $data as $key => $item ) {
					$vendor_ids = array();
					foreach( $item['products'] as $pid ) {
						$vendor = dokan_get_vendor_by_product( $pid );
						$vendor_id = $vendor->id;

						if( is_array( $vendor_id ) ) {
							$merged = array_merge( $vendor_ids, $vendor_id );
							$vendor_ids = array_unique( $merged );
						} else {
							$vendor_ids[] = (int) $vendor_id;
						}
					}
					
					$data[ $key ]['dokan_vendors'] = $vendor_ids;
				}
			}

			return $data;
		}


		/**
		 * compare_vendor_condition function.
		 *
		 * @access public
		 * @param bool $result (default: false), array $cond, array $cart_data
		 * @return bool
		 */
		function compare_vendor_condition( $result, $cond, $cart_data ) {
			// check if type is for Dokan
			if( ! isset( $cond['cond_type'] ) || sanitize_title( $cond['cond_type'] ) != 'dokan_vendor' ) return $result;
			if( ! isset( $cart_data['dokan_vendors'] ) || ! is_array( $cart_data['dokan_vendors'] ) ) return $result;

			// compare vendor data
			$array_comp = array_intersect( $cond['cond_tertiary'], $cart_data['dokan_vendors'] );
			if( $cond['cond_secondary'] == 'excludes' && empty( $array_comp ) ) {
				$result = true;
			} elseif( ! empty( $array_comp ) ) {
				$result = true;
			}

			return $result;
		}

	}

	new BETRS_Dokan();

}

?>