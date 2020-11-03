<?php

if ( ! defined( 'ABSPATH' ) )
	exit;

// Check if WooCommerce is active
if ( class_exists( 'WooCommerce' ) ) {
		
	if ( class_exists( 'BETRS_Shipping_Classes_Ext' ) ) return;

	class BETRS_Shipping_Classes_Ext {


		/**
		 * Constructor.
		 */
		public function __construct() {

	   		// extend Shipping Class columns
			add_filter( 'woocommerce_get_shipping_classes', array( $this, 'get_class_priorities' ), 10, 1 );
			add_filter( 'woocommerce_shipping_classes_columns', array( $this, 'add_priority_column' ), 10, 1 );
			add_action( 'woocommerce_shipping_classes_column_wc-shipping-class-priority', array( $this, 'add_priority_settings' ) );
			add_action( 'woocommerce_shipping_classes_save_class', array( $this, 'save_priority_settings' ), 10, 2 );
		}


		/**
		 * Retrieve shipping class priorities
		 */
		public function get_class_priorities( $shipping_classes ) {

			// cycle through each class and add priority to array
			foreach( $shipping_classes as $key => $class ) {

				$get_priority = get_term_meta( $class->term_id, 'priority', true );
				$term_meta = get_term_meta( $class->term_id, 'priority', true );
				$shipping_classes[ $key ]->priority = ( ! empty( $term_meta ) ) ? $term_meta : 0;

			}

			return $shipping_classes;
	    }


		/**
		 * Setup priority column
		 */
		public function add_priority_column( $columns ) {

			$columns['wc-shipping-class-priority'] = __( 'Priority', 'be-table-ship' );
			return $columns;
	    }


		/**
		 * Display priority and edit settings
		 */
		public function add_priority_settings() {
?>
	<div class="view">{{ data.priority }}</div>
	<div class="edit"><input type="number" name="priority[{{ data.term_id }}]" data-attribute="priority" value="{{ data.priority }}" placeholder="0" size="3" /></div>
<?php
	    }


		/**
		 * Save priority column data
		 */
		public function save_priority_settings( $term_id, $data ) {

			if ( isset( $data['priority'] ) ) {
				update_term_meta( $term_id, 'priority', (int) $data['priority'] );
			}

	    }

	}

	new BETRS_Shipping_Classes_Ext();

}

?>