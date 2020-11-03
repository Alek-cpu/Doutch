<?php

/**
 * Class WPLE_Rest_Controller
 *
 * Example rest server that allows for CRUD operations on the wp_options table
 *
 */

class WPLE_Rest_Response {

    public $success = false;
    public $msg     = '';
    public $errors  = array();

    public function __construct( $success, $msg = '', $errors = false ) {
         $this->success = $success;
         $this->errors  = $errors;
         $this->msg     = $msg;
    }

}

class WPLE_Rest_Controller extends WP_Rest_Controller {

    public $namespace = 'wple/';
    public $version   = 'v1';

    public function __construct() {
        $this->init();
    }

    public function init() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        $namespace = $this->namespace . $this->version;

        register_rest_route( $namespace, '/listings', array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_listings' ),
                'permission_callback' => array( $this, 'get_listings_permission' )
            ),
        ) );

        register_rest_route( $namespace, '/listing/(?P<id>(.*)+)', array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_listing' ),
                'permission_callback' => array( $this, 'get_listings_permission' )
            ),
            array(
                'methods'  => WP_REST_Server::EDITABLE,
                'callback' => array( $this, 'edit_listing' ),
                'permission_callback' => array( $this, 'get_listings_permission' )
            ),
        ) );

    }

    public function get_listings( WP_REST_Request $request ) {
        
        $current_page = 1;
        $per_page     = get_option( 'wplister_grid_page_size', 10000 );;
        $result       = WPLE_ListingQueryHelper::getPageItems( $current_page, $per_page );

        foreach ($result->items as $key => &$item) {

            // remove bulky data to improve performance
            unset( $result->items[$key]['details'] );
            unset( $result->items[$key]['profile_data'] );
            unset( $result->items[$key]['post_content'] );
            unset( $result->items[$key]['history'] );
            $result->items[$key]['last_errors'] = 'todo';

            // decode HTML entities on title
            $result->items[$key]['auction_title'] = html_entity_decode( $result->items[$key]['auction_title'] );

            // add meta data
            $result->items[$key]['_ebay_start_price']     = get_post_meta( $result->items[$key]['post_id'], '_ebay_start_price', true );
            $result->items[$key]['_amazon_price']         = get_post_meta( $result->items[$key]['post_id'], '_amazon_price', true );
            $result->items[$key]['_amazon_minimum_price'] = get_post_meta( $result->items[$key]['post_id'], '_amazon_minimum_price', true );
            $result->items[$key]['_amazon_maximum_price'] = get_post_meta( $result->items[$key]['post_id'], '_amazon_maximum_price', true );
            $result->items[$key]['_regular_price']        = get_post_meta( $result->items[$key]['post_id'], '_regular_price', true );
            $result->items[$key]['_sale_price']           = get_post_meta( $result->items[$key]['post_id'], '_sale_price', true );
            $result->items[$key]['_msrp_price']           = get_post_meta( $result->items[$key]['post_id'], '_msrp_price', true );

            // get thumbnail
            $post_id   = $result->items[$key]['post_id'];
            $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), "thumbnail" );
            $result->items[$key]['thumb'] = $thumbnail[0];
        }

        return $result;
    }

    public function get_listings_permission() {

        if ( ! current_user_can( 'manage_ebay_listings' ) ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You do not have permissions to manage listings.', 'wp-lister-for-ebay' ), array( 'status' => 401 ) );
        }

        return true;
    }


    public function get_item_data( $id ) {
        $item = ListingsModel::getItem( $id );

        // remove bulky data to improve performance
        unset( $item['details'] );
        unset( $item['profile_data'] );
        unset( $item['post_content'] );
        unset( $item['history'] );
        $item['last_errors'] = 'todo';

        // decode HTML entities on title
        $item['auction_title'] = html_entity_decode( $item['auction_title'] );

        // add meta data
        $item['_ebay_start_price']     = get_post_meta( $item['post_id'], '_ebay_start_price', true );
        $item['_amazon_price']         = get_post_meta( $item['post_id'], '_amazon_price', true );
        $item['_amazon_minimum_price'] = get_post_meta( $item['post_id'], '_amazon_minimum_price', true );
        $item['_amazon_maximum_price'] = get_post_meta( $item['post_id'], '_amazon_maximum_price', true );
        $item['_regular_price']        = get_post_meta( $item['post_id'], '_regular_price', true );
        $item['_sale_price']           = get_post_meta( $item['post_id'], '_sale_price', true );
        $item['_msrp_price']           = get_post_meta( $item['post_id'], '_msrp_price', true );

        // get thumbnail
        $post_id   = $item['post_id'];
        $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), "thumbnail" );
        $item['thumb'] = $thumbnail[0];

        return $item;
    }

    public function return_update_response( $success, $msg, $id, $errors = null ) {

        $response = new stdClass();
        $response->success = $success;
        $response->errors  = $errors;
        $response->msg     = $msg;
        $response->item    = self::get_item_data( $id );

        return $response;
    }

    public function edit_listing( WP_REST_Request $request ) {
        $params = $request->get_params();

        if ( ! isset( $params['id'] ) || empty( $params['id'] ) ) {
            return new WP_Error( 'no-param', __( 'No id param' ) );
        }

        $body = $request->get_body();

        if ( empty( $body ) ) {
            return new WP_Error( 'no-body', __( 'Request body empty' ) );
        }

        $decoded_body = json_decode( $body );

        // return new WP_Error( 'no-param', __( 'Yes, body is: '.print_r($decoded_body,1) ) );

        if ( $decoded_body ) {
            if ( isset( $decoded_body->id, $decoded_body->col, $decoded_body->val ) ) {

                // check if listing ID exists
                if ( ! WPLE_ListingQueryHelper::getStatus( $decoded_body->id ) ) {
                    return false;
                }

                // update listing record in WPLE
                $result = self::update_listing( $decoded_body->id, $decoded_body->col, $decoded_body->val );
                return self::return_update_response( $result->success, $result->msg, $decoded_body->id, $result->errors );

            }
        }

        return false;
    }


    public function update_listing( $id, $col, $val ) {
        if ( ! class_exists('ListingsModel' ) ) return new WPLE_Rest_Response( false, 'wple missing' );

        $editable_columns = array(
            'auction_title',
            'price',
            'quantity',
            'profile_id',
            'locked',
            'status',
            '_ebay_start_price',
            '_amazon_price',
            '_amazon_minimum_price',
            '_amazon_maximum_price',
            '_regular_price',
            '_msrp_price',
            '_sale_price',
        );

        // check if column key is valid and editable
        if ( ! in_array( $col, $editable_columns ) ) {
            return new WPLE_Rest_Response( false, 'invalid column key '.$col );
        }

        // get previous item data
        $previous_data = self::get_item_data( $id );

        // perform status change - before updating listing record
        if ( 'status' == $col ) {

            $previous_status = $previous_data['status'];

            switch ($val) {
                case 'prepared':
                    # set status to prepared
                    if ( ! in_array( $previous_status, array('prepared','verified','ended') ) ) {
                        return new WPLE_Rest_Response( false, "It is not possible to change the listing status from $previous_status to $val." );
                    }
                    ListingsModel::updateListing( $id, array( $col => $val ) );
                    return new WPLE_Rest_Response( true );
                    break;
                 
                case 'verified':
                    # verify listing...
                    $results = apply_filters( 'wple_verify_item', $id );
                    if ( is_array($results) ) {
                        if ($results[0]->success) {
                            // ListingsModel::updateListing( $id, array( $col => $val ) ); // status should already be updated
                        }    
                        return new WPLE_Rest_Response( $results[0]->success, 'verified', $results[0]->errors );
                    }
                    return new WPLE_Rest_Response( false, 'unknown result: '.$results );
                    break;
                 
                case 'published':
                    # publish listing...
                    $results = apply_filters( 'wple_publish_item', $id );
                    if ( is_array($results) ) {
                        if ($results[0]->success) {
                            // ListingsModel::updateListing( $id, array( $col => $val ) ); // status should already be updated
                        }    
                        return new WPLE_Rest_Response( $results[0]->success, 'published', $results[0]->errors );
                    }
                    return new WPLE_Rest_Response( false, 'unknown result: '.$results );
                    break;
                 
                case 'ended':
                    # end listing...
                    if ( ! in_array( $previous_status, array('published','changed') ) ) {
                        return new WPLE_Rest_Response( false, "It is not possible to change the listing status from $previous_status to $val." );
                    }
                    $results = apply_filters( 'wple_end_item', $id );
                    if ( is_array($results) ) {
                        if ($results[0]->success) {
                            // ListingsModel::updateListing( $id, array( $col => $val ) ); // status should already be updated
                        }    
                        return new WPLE_Rest_Response( $results[0]->success, 'ended', $results[0]->errors );
                    }
                    return new WPLE_Rest_Response( false, 'unknown result: '.$results );
                    break;
                 
                case 'changed':
                    # set status to changed
                    if ( ! in_array( $previous_status, array('published','changed') ) ) {
                        return new WPLE_Rest_Response( false, "It is not possible to change the listing status from $previous_status to $val." );
                    }
                    ListingsModel::updateListing( $id, array( $col => $val ) );
                    return new WPLE_Rest_Response( true, 'Listing status was set to "changed".' );
                    break;
                 
                default:
                    # unknown status
                    return new WPLE_Rest_Response( false, 'unknown status: '.$val );
                    break;
            }
        }

        // process meta data
        $product_meta_fields = array(
            '_ebay_start_price',
            '_amazon_price',
            '_amazon_minimum_price',
            '_amazon_maximum_price',
            '_msrp_price',
            '_sale_price',
            '_price',
        );

        if ( in_array( $col, $product_meta_fields ) ) {
            $post_id = $previous_data['post_id'];
            update_post_meta( $post_id, $col, $val );
        }

        ListingsModel::updateListing( $id, array( $col => $val ) );

        // check if profile needs to be reapplied
        if ( in_array( $col, array('profile_id') ) ) {
            $profilesModel = new ProfilesModel();
            $profile = $profilesModel->getItem( intval($val) );
            $listingsModel = new ListingsModel();
            $items = $listingsModel->applyProfileToNewListings( $profile );         
        }

        return new WPLE_Rest_Response( true );
    }
} // class WPLE_Rest_Controller