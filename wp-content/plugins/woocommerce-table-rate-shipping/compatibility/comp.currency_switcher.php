<?php
/*
 * Table Rate Shipping Method Extender Class
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

// Check if WooCommerce is active
if ( class_exists( 'WooCommerce' ) && class_exists('WOOCS') ) {

	if ( class_exists( 'BETRS_Currency_Switcher' ) ) return;

	class BETRS_Currency_Switcher {

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
			add_filter( 'betrs_condition_tertiary_subtotal', array( $this, 'price_currency_conversion' ), 10, 2 );

		}


		/**
		 * calculate_shipping function.
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return void
		 */
		function price_currency_conversion( $value, $cond ) {
			
			return apply_filters( 'woocs_exchange_value', $value );
		}

	}

	new BETRS_Currency_Switcher();

}

?>