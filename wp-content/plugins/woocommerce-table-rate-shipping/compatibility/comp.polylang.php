<?php
/*
 * Table Rate Shipping Method Extender Class
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

// Check if WooCommerce is active
if ( class_exists( 'WooCommerce' ) && class_exists('Polylang') ) {

	if ( class_exists( 'BETRS_Polylang' ) ) return;

	class BETRS_Polylang {

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
			add_filter( 'betrs_save_shipping_rate_label', array( $this, 'translate_shipping_label' ), 10, 3 );
			add_filter( 'betrs_shipping_rate_label', array( $this, 'get_translated_shipping_label' ), 10, 3 );
			add_filter( 'betrs_save_shipping_rate_description', array( $this, 'translate_shipping_description' ), 10, 4 );
			add_filter( 'betrs_shipping_rate_description', array( $this, 'get_translated_shipping_description' ), 10, 4 );

		}


		/**
		 * add shipping label to string translation.
		 *
		 * @access public
		 * @param string $label
		 * @return string
		 */
		function translate_shipping_label( $label, $option_id, $save_name ) {

			if( function_exists( 'icl_register_string' ) ) {
				$label = sanitize_text_field( $label );
				$option_id = (int) $option_id;
				list( $slug, $instance_id ) = explode( '-', sanitize_text_field( $save_name ) );
				icl_register_string( 'betrs_labels', 'instance_' . $instance_id . '-option_' . $option_id, $label );
			}

			return $label;
		}


		/**
		 * retrieve translated shipping label.
		 *
		 * @access public
		 * @param string $label
		 * @return string
		 */
		function get_translated_shipping_label( $label, $option_id, $instance_id ) {

			if( function_exists( 'icl_t' ) ) {
				$label = sanitize_text_field( $label );
				$option_id = (int) $option_id;
				$instance_id = (int) $instance_id;
			    $label = icl_t( 'betrs_labels', 'instance_' . $instance_id . '-option_' . $option_id, $label );
			}

			return $label;
		}


		/**
		 * add shipping description to string translation.
		 *
		 * @access public
		 * @param string $label
		 * @return string
		 */
		function translate_shipping_description( $desc, $option_id, $save_name, $row_id ) {
			global $betrs_shipping;

			if( function_exists( 'icl_register_string' ) ) {
				$desc = wp_kses_data( $desc );
				$option_id = (int) $option_id;
				list( $slug, $instance_id ) = explode( '-', sanitize_text_field( $save_name ) );
				icl_register_string( 'betrs_labels', 'instance_' . $instance_id . '-option_' . $option_id . '_' . $row_id . '-desc', $desc );
			}

			return $desc;
		}


		/**
		 * retrieve translated shipping description.
		 *
		 * @access public
		 * @param string $label
		 * @return string
		 */
		function get_translated_shipping_description( $desc, $option_id, $instance_id, $row_id ) {
			global $betrs_shipping;

			if( function_exists( 'icl_t' ) ) {
				$desc = wp_kses( $desc, $betrs_shipping->allowedtags );
				$option_id = (int) $option_id;
				$instance_id = (int) $instance_id;
			    $desc = icl_t( 'betrs_labels', 'instance_' . $instance_id . '-option_' . $option_id . '_' . $row_id . '-desc', $desc );
			}

			return $desc;
		}

	}

	new BETRS_Polylang();

}

?>