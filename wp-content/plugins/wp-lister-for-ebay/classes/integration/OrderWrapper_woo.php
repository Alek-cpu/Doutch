<?php
/**
 * wrapper functions to access orders on WooCommerce
 */

class OrderWrapper {

	const plugin = 'woo';
	const post_type = 'shop_order';

	// get custom post type
	static function getPostType() {
		return self::post_type;
	}

	static function getOrder( $order_id ) {
	    if ( is_callable( 'wc_get_order' ) ) {
            return wc_get_order( $order_id );
        } else {
	        return new WC_Order( $order_id );
        }
    }



} // class OrderWrapper


