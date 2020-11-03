<?php

class WPLA_Shipping_Options {

    // private $options = array();

    public function __construct() {
        // $this->options = apply_filters( 'wpla_shipping_options', array(
        //     'Standard', 'Expedited', 'Priority'
        // ) );

	    if ( ! is_admin() && ! defined( 'DOING_CRON' ) ) {
		    add_action( 'wp', array( $this, 'print_cart_message' ) );

		    // switch available shipping methods conditionally
		    add_filter( 'woocommerce_shipping_methods', array( $this, 'toggle_shipping_methods' ) );
	    }

        // register the custom shipping method
        add_action( 'woocommerce_shipping_init', array( $this, 'shipping_init' ) );
        add_filter( 'woocommerce_shipping_methods', array( $this, 'shipping_add_method' ) );

	    // store the shipping method
	    add_action( 'woocommerce_checkout_order_processed', array( $this, 'store_shipping_method' ), 10, 2 );
    }

    /**
     * Displays a notice above the cart page when the cart contains mixed items
     */
    public function print_cart_message() {
        if ( is_cart() ) {
            $cart_type = $this->get_cart_type();
            if ( $cart_type == 'mixed' ) {
                wc_add_notice(
                    __( 'There are items in your cart that are not fulfilled by Amazon. If you would like to select a specific Amazon shipping method, please remove non-Amazon items from your cart.', 'wp-lister-for-amazon' ),
                    'notice'
                );
            } elseif ( $cart_type != 'non-fba' && ! $this->cart_is_fulfillable() ) {
                wc_add_notice(
                    __( 'Your cart contains items that are not from the same marketplace, thus making your order unfulfillable by Amazon. Please split the items into separate orders to continue.', 'wp-lister-for-amazon' ),
                    'notice'
                );
            }
        }
    }

    /**
     * Checks all the items in the cart and returns one of these 3 strings: fba, non-fba, mixed
     * @return string
     */
    public function get_cart_type() {
        if ( $this->cart_is_fba() ) {
            WPLA()->logger->info('WPLASO: get_cart_type() returns FBA (1)');
            return 'fba';
        }

        $non_fba = 0;
        $fba = 0;
        foreach ( WC()->cart->get_cart() as $cart_item ) {
            $product_id = ( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
            if ( !$this->product_is_fba( $product_id ) ) {
                $non_fba++;
            } else {
                $fba++;
            }
        }

        if ( $fba > 0 && $non_fba > 0 ) {
            WPLA()->logger->info("WPLASO: get_cart_type() returns MIXED ($fba fba / $non_fba non fba)");
            return 'mixed';
        } else {
            WPLA()->logger->info("WPLASO: get_cart_type() returns NON-FBA ($fba fba / $non_fba non fba)");
            return 'non-fba';
        }
    }

    /**
     * Check if the cart items are all FBA items.
     * @return bool
     */
    public function cart_is_fba() {
        $all_fba = true;

        foreach ( WC()->cart->get_cart() as $cart_item ) {
            $product_id = ( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
            if ( !$this->product_is_fba( $product_id ) ) {
                $all_fba = false;
                break;
            }
        }

        WPLA()->logger->info("WPLASO: cart_is_fba() - return value: ".($all_fba ? 1 : 0));
        return $all_fba;
    }

    /**
     * Check if the given product is an FBA product
     *
     * @param int $product_id
     * @return bool
     */
    public function product_is_fba( $product_id ) {
        $listings = new WPLA_ListingsModel();
        $item = $listings->getItemByPostID( $product_id );

        return  ( $item && $item->fba_quantity > 0 );
    }

    /**
     * Check if all items in the cart can be fulfilled via FBA.
     * @return bool
     */
    public function cart_is_fulfillable() {
        // All cart items must be FBA listings
        if ( ! $this->cart_is_fba() ) {
            return false;
        }

        // All items must belong to the same Amazon Account/Marketplace
        $listings = new WPLA_ListingsModel();
        $last_account_id = false;
        $default_account = get_option( 'wpla_default_account_id', 0 );

        foreach ( WC()->cart->get_cart() as $cart_item ) {
            $product_id = ( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];

            // Pull all items linked to the Post ID
            $item  = false;
            $items = $listings->getAllItemsByPostID( $product_id );

            if ( count( $items ) > 1 ) {
                foreach ( $items as $listing ) {
                    if ( $listing->account_id == $default_account ) {
                        $item = $listing;
                        break;
                    }
                }

                // No items using default account. Use the first one in the list
                if ( !$item ) {
                    $item = array_shift( $items );
                }
            } else {
                $item = array_shift( $items );
            }

            if ( $last_account_id === false ) {
                $last_account_id = $item->account_id;
            } else {
                if ( $last_account_id != $item->account_id ) {
                    WPLA()->logger->info( 'WPLASO: Invalid cart. Items are under different accounts' );
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Load the WPLA_Shipping_Method class
     */
    public function shipping_init() {
        // include_once( 'classes/integration/WPLA_Shipping_Method.php' ); // handled by autoloader
    }

    /**
     * Add WPLA_Shipping_Method to the list of available shipping methods
     *
     * @param array $methods
     * @return array
     */
    public function shipping_add_method( $methods ) {
        $methods[] = 'WPLA_Shipping_Method';
        return $methods;
    }

    /**
     * Toggle between the available shipping methods depending on the cart contents
     * @param array $methods
     * @return array
     */
    public function toggle_shipping_methods( $methods ) {
        if ( is_admin() ) {
            return $methods;
        }

        if ( $this->cart_is_fulfillable() ) {
            $methods = array('WPLA_Shipping_Method');
            WPLA()->logger->info("WPLASO: toggle_shipping_methods() - enabling WPLA_Shipping_Method");
        } else {
            $key = array_search( 'WPLA_Shipping_Method', $methods );

            if ( $key !== false ) {
                unset( $methods[ $key ] );
            }
            WPLA()->logger->info("WPLASO: toggle_shipping_methods() - disabling WPLA_Shipping_Method");
        }

        return $methods;
    }

    /**
     * Store the selected shipping method as order meta if cart is FBA
     * @param int   $order_id
     * @param array $posted
     */
    public function store_shipping_method( $order_id, $posted) {
        $shipping_method = $posted['shipping_method'];
        if ( is_array( $posted['shipping_method'] ) ) {
            $shipping_method = current( $posted['shipping_method'] );
        }
        WPLA()->logger->info("WPLASO: store_shipping_method( $order_id ) - shipping method: $shipping_method");

        if ( in_array( $shipping_method, array('Standard', 'Expedited', 'Priority') ) ) {
            update_post_meta( $order_id, '_wpla_DeliverySLA', $shipping_method );
        }
    }

}

// init class
//new WPLA_Shipping_Options();
