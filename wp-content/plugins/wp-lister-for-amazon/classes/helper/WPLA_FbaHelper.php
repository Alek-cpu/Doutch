<?php

class WPLA_FbaHelper {
	
    static public function getRecentOrders() {

        // $allowed_order_statuses = apply_filters( 'wpla_mcf_enabled_order_statuses', array( 'wc-completed', 'wc-processing', 'wc-on-hold' ) );
        $allowed_order_statuses = apply_filters( 'wpla_mcf_enabled_order_statuses', array( 'wc-completed', 'wc-processing' ) ); // removed on-hold for now - until "hold" FBA action is implemented

        // fetch orders - WC2.2+
        $orders = get_posts( array(
            'post_type'   => 'shop_order',
            'post_status' => $allowed_order_statuses,

            'posts_per_page'   => -1,
            'orderby'          => 'post_modified_gmt',
            'order'            => 'ASC',

            'date_query' => array(
                array(
                    'column' => 'post_modified_gmt',
                    'after'  => '1 day ago',
                ),
            ),

        ) );

        return $orders;
    } // getRecentOrders()


    // create a new FBA submission feed for order
    static public function submitOrderToFBA( $post_id ) {

        // make sure we don't submit the same order twice (just a precaution)
        $status = get_post_meta( $post_id, '_wpla_fba_submission_status', true );
        if ( $status && $status != 'failed' && $status != 'hold' ) return false; // should never happen - it might as well read: die('you are doing it wrong');

        // create FBA feed
        $feed = new WPLA_AmazonFeed();
        $feed->updateFbaSubmissionFeed( $post_id );

        // mark order as submitted (pending)
        update_post_meta( $post_id, '_wpla_fba_submission_status',   'pending' );

        $response = new stdClass();
        $response->success = true;

        do_action( 'wpla_order_submitted_to_fba', $post_id );

        return $response;
    } // submitOrderToFBA()


    // check if an order can be fulfilled via FBA
    // parameter: $post - a wp post object or post_id of an order
    static public function orderCanBeFulfilledViaFBA( $post, $is_cron = false ) {

        // make sure we have a wp post object
        if ( is_numeric($post) ) $post = get_post( $post );

        // check if this is an order created by WP-Lister for Amazon
        $amazon_order_id = get_post_meta( $post->ID, '_wpla_amazon_order_id', true );
        if ( $amazon_order_id ) return 'Order was placed on Amazon';

        // check if this order has already been submitted to FBA
        $submission_status = get_post_meta( $post->ID, '_wpla_fba_submission_status', true );
        if ( $submission_status == 'pending' ) {
            return __( 'This order is going to be submitted to Amazon and will be fulfilled via FBA.', 'wp-lister-for-amazon' );
        }
        if ( $submission_status == 'success' ) {
            return __( 'This order has been successfully submitted to Amazon and will be fulfilled via FBA.', 'wp-lister-for-amazon' );
        }
        if ( $submission_status == 'shipped' ) {
            return __( 'This order has been fulfilled by Amazon.', 'wp-lister-for-amazon' );
        }
        if ( $submission_status == 'hold' ) {
            // held submissions are handled in Woo_OrderMetaBox - only manually for now, on-hold orders are ignored by cron job
            // return __( 'The ordered items(s) have been held back on FBA until this order is completed. To ship the held items please visit Seller Central.', 'wp-lister-for-amazon' );
        }
        if ( $submission_status == 'failed' ) {
            // failed submissions can be submitted again - but only manually for now 
            // (automatic resubmittion will require proper error handling for Error 560001: Delivery SLA is not available for destination address - and fallback to Standard shipping)
            if ( $is_cron ) return __( 'There was a problem submitting this order to be fulfilled by Amazon!', 'wp-lister-for-amazon' );
        }

        // skip cancelled and pending orders
        // Removed the wc-completed order status so WPLA doesn't make the mistake of send completed orders to Amazon to be fulfilled #27349
        //$allowed_order_statuses = apply_filters( 'wpla_mcf_enabled_order_statuses', array( 'wc-completed', 'wc-processing', 'wc-on-hold' ) );
        $allowed_order_statuses = apply_filters( 'wpla_mcf_enabled_order_statuses', array( 'wc-processing', 'wc-on-hold' ) );
        if ( ! in_array( $post->post_status, $allowed_order_statuses ) ) {
            // return __( 'Order status is neither processing nor completed nor on hold.', 'wp-lister-for-amazon' );
            return sprintf( __( 'Order status %s is not enabled for FBA. Allowed order statuses are: %s', 'wp-lister-for-amazon' ), $post->post_status, join( ', ', $allowed_order_statuses ) );
        }

        // check if FBA is enabled (not really required)
        // if ( !  get_option( 'wpla_fba_enabled' ) ) return 'FBA support is disabled.';


        // get order and order items
        if ( ! function_exists('wc_get_order') ) return;
        $_order      = wc_get_order( $post->ID );
        $order_items = $_order->get_items();

        // check if destination country matches fulfillment center
        $shipping_country = wpla_get_order_meta( $_order, 'shipping_country' );
        $fba_default_fcid = get_option( 'wpla_fba_fulfillment_center_id', 'AMAZON_NA' );
        if ( 'AMAZON_NA' == $fba_default_fcid ) {
            $allowed_countries = array( 'US', 'CA', 'PR' );
            if ( ! in_array( $shipping_country, $allowed_countries ) ) {
                return __( 'Shipping destination is not within an allowed country for FBA delivery.<br>FBA shipments are only possible to: US, CA, PR', 'wp-lister-for-amazon' );
            }
        } elseif ( 'AMAZON_EU' == $fba_default_fcid ) {
            $allowed_countries = array( 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HU', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' );
            if ( ! in_array( $shipping_country, $allowed_countries ) ) {
                return __( 'Shipping destination is not within the EU.', 'wp-lister-for-amazon' );
            }
        } elseif ( 'AMAZON_CA' == $fba_default_fcid ) {
            $allowed_countries = array( 'CA' );
            if ( ! in_array( $shipping_country, $allowed_countries ) ) {
                return __( 'Shipping destination is not within Canada.', 'wp-lister-for-amazon' );
            }
        } elseif ( 'AMAZON_IN' == $fba_default_fcid ) {
            $allowed_countries = array( 'IN' );
            if ( ! in_array( $shipping_country, $allowed_countries ) ) {
                return __( 'Shipping destination is not within India.', 'wp-lister-for-amazon' );
            }
        } elseif ( 'AMAZON_AU' == $fba_default_fcid ) {
            $allowed_countries = array( 'AU' );
            if ( ! in_array( $shipping_country, $allowed_countries ) ) {
                return __( 'Shipping destination is not within Australia.', 'wp-lister-for-amazon' );
            }
        }

        // Allow 3rd-party code to add checks and return either an error message or TRUE to continue processing
        $fulfillable = apply_filters( 'wpla_order_can_be_fulfilled_via_fba', true, $_order );

        if ( $fulfillable != true ) {
            return $fulfillable;
        }

        // check if ordered items are available on FBA
        $items_available_on_fba     = array();
        $count_not_available_on_fba = 0;
        $item_account_ids           = array();
        $default_account_id         = get_option( 'wpla_default_account_id' );
        $lm = new WPLA_ListingsModel();
        foreach ( $order_items as $item ) {

            // skip tax and shipping rows
            if ( $item['type'] != 'line_item' ) continue;

            // find amazon listing
            $post_id = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];

            $listings = $lm->getAllItemsByPostID( $post_id );

            // For products linked to multiple listings, attempt to use the one linked to the default account when processing FBA submissions #24051
            if ( count( $listings ) == 1 ) {
                $listing = array_shift( $listings );
            } else {
                $listing = false;

                foreach ( $listings as $amz_listing ) {
                    if ( $amz_listing->account_id == $default_account_id ) {
                        $listing = $amz_listing;
                        break;
                    }
                }

                // If a listing for the defaul account doesn't exist, use the first one it finds
                if ( !$listing ) {
                    $listing = array_shift( $listings );
                }
            }

            if ( ! $listing ) {
                $count_not_available_on_fba++;
                continue;
            } else {
                // Fix for undefined offset warning #34313
                if ( empty( $item_account_ids[ $listing->account_id ] ) ) {
                    $item_account_ids[ $listing->account_id ] = 0;
                }

                $item_account_ids[ $listing->account_id ]++;
            }

            // check FBA inventory
            $fba_quantity = $listing->fba_quantity;
            if ( $fba_quantity > 0 ) {
                $listing->purchased_qty = $item['qty'];
                $items_available_on_fba[] = $listing;
            } else {
                $count_not_available_on_fba++;
            }

        } // each order line item


        if ( empty( $items_available_on_fba ) ) {
            $msg  = __( 'This order can not be fulfilled by Amazon.', 'wp-lister-for-amazon' ) . ' '; 
            $msg .= __( 'The purchased item(s) are currently not available on FBA.', 'wp-lister-for-amazon' );
            return $msg;         
        }

        if ( $count_not_available_on_fba > 0 ) {
            $msg  = __( 'This order can not be fulfilled by Amazon.', 'wp-lister-for-amazon' ) . ' ';
            $msg .= __( 'Not all purchased items are currently available on FBA.', 'wp-lister-for-amazon' );
            return $msg;         
        }

        if ( count( $item_account_ids ) > 1 ) {
            $msg  = __( 'This order can not be fulfilled by Amazon.', 'wp-lister-for-amazon' ) . ' ';
            $msg .= __( 'Purchased items are from different marketplaces.', 'wp-lister-for-amazon' );
            return $msg;
        }

        // this order can be filfilled via FBA - return array of items
        return $items_available_on_fba;

    } // orderCanBeFulfilledViaFBA()


} // class WPLA_FbaHelper
