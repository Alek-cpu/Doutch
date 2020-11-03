<?php

namespace Vendidero\Germanized\Shipments;
use WC_Data;
use WC_Data_Store;
use Exception;
use WC_Order_Item;

defined( 'ABSPATH' ) || exit;

/**
 * Order item class.
 */
class ShipmentItem extends WC_Data {

    protected $order_item = null;

    protected $shipment = null;

    protected $product = null;

    /**
     * Order Data array. This is the core order data exposed in APIs since 3.0.0.
     *
     * @since 3.0.0
     * @var array
     */
    protected $data = array(
        'shipment_id'   => 0,
        'order_item_id' => 0,
        'parent_id'     => 0,
        'quantity'      => 1,
        'product_id'    => 0,
        'weight'        => '',
        'width'         => '',
        'length'        => '',
        'height'        => '',
        'sku'           => '',
        'name'          => '',
        'total'         => 0,
    );

    /**
     * Stores meta in cache for future reads.
     * A group must be set to to enable caching.
     *
     * @var string
     */
    protected $cache_group = 'shipment-items';

    /**
     * Meta type. This should match up with
     * the types available at https://developer.wordpress.org/reference/functions/add_metadata/.
     * WP defines 'post', 'user', 'comment', and 'term'.
     *
     * @var string
     */
    protected $meta_type = 'shipment_item';

    /**
     * This is the name of this object type.
     *
     * @var string
     */
    protected $object_type = 'shipment_item';

    /**
     * Constructor.
     *
     * @param int|object|array $item ID to load from the DB, or WC_Order_Item object.
     */
    public function __construct( $item = 0 ) {
        parent::__construct( $item );

        if ( $item instanceof ShipmentItem ) {
            $this->set_id( $item->get_id() );
        } elseif ( is_numeric( $item ) && $item > 0 ) {
            $this->set_id( $item );
        } else {
            $this->set_object_read( true );
        }

        $this->data_store = WC_Data_Store::load( 'shipment-item' );

	    // If we have an ID, load the user from the DB.
	    if ( $this->get_id() ) {
		    try {
			    $this->data_store->read( $this );
		    } catch ( Exception $e ) {
			    $this->set_id( 0 );
			    $this->set_object_read( true );
		    }
	    } else {
		    $this->set_object_read( true );
	    }
    }

    /**
     * Merge changes with data and clear.
     * Overrides WC_Data::apply_changes.
     * array_replace_recursive does not work well for order items because it merges taxes instead
     * of replacing them.
     *
     * @since 3.2.0
     */
    public function apply_changes() {
        if ( function_exists( 'array_replace' ) ) {
            $this->data = array_replace( $this->data, $this->changes ); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.array_replaceFound
        } else { // PHP 5.2 compatibility.
            foreach ( $this->changes as $key => $change ) {
                $this->data[ $key ] = $change;
            }
        }
        $this->changes = array();
    }

    /*
    |--------------------------------------------------------------------------
    | Getters
    |--------------------------------------------------------------------------
    */

    public function get_type() {
    	return 'simple';
    }

    /**
     * Get order ID this meta belongs to.
     *
     * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return int
     */
    public function get_shipment_id( $context = 'view' ) {
        return $this->get_prop( 'shipment_id', $context );
    }

    /**
     * Get order ID this meta belongs to.
     *
     * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return int
     */
    public function get_order_item_id( $context = 'view' ) {
        return $this->get_prop( 'order_item_id', $context );
    }

    /**
     * Get order ID this meta belongs to.
     *
     * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return int
     */
    public function get_product_id( $context = 'view' ) {
        return $this->get_prop( 'product_id', $context );
    }

	/**
	 * Get item parent id.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return int
	 */
	public function get_parent_id( $context = 'view' ) {
		return $this->get_prop( 'parent_id', $context );
	}

    /**
     * Get order ID this meta belongs to.
     *
     * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return int
     */
    public function get_total( $context = 'view' ) {
        return $this->get_prop( 'total', $context );
    }

    /**
     * Get quantity.
     *
     * @return int
     */
    public function get_sku( $context = 'view' ) {
        return $this->get_prop( 'sku', $context );
    }

    /**
     * Get quantity.
     *
     * @return int
     */
    public function get_quantity( $context = 'view' ) {
        return $this->get_prop( 'quantity', $context );
    }

    /**
     * Get weight.
     *
     * @return string
     */
    public function get_weight( $context = 'view' ) {
        return $this->get_prop( 'weight', $context );
    }

    /**
     * Get width.
     *
     * @return string
     */
    public function get_width( $context = 'view' ) {
        return $this->get_prop( 'width', $context );
    }

    /**
     * Get length.
     *
     * @return string
     */
    public function get_length( $context = 'view' ) {
        return $this->get_prop( 'length', $context );
    }

    /**
     * Get height.
     *
     * @return string
     */
    public function get_height( $context = 'view' ) {
        return $this->get_prop( 'height', $context );
    }

    public function get_name( $context = 'view' ) {

        if ( 'view' === $context && ( $item = $this->get_order_item() ) ) {
            return $item->get_name();
        }

        return $this->get_prop( 'name', $context );
    }

    /**
     * Get parent order object.
     *
     * @return SimpleShipment|ReturnShipment|Shipment
     */
    public function get_shipment() {
        if ( is_null( $this->shipment ) && 0 < $this->get_shipment_id() ) {
            $this->shipment = wc_gzd_get_shipment( $this->get_shipment_id() );
        }

        $shipment = ( $this->shipment ) ? $this->shipment : false;

        return $shipment;
    }

	/**
	 * Sets the linked shipment instance.
	 *
	 * @param Shipment $shipment
	 */
    public function set_shipment( &$shipment ) {
    	$this->shipment = $shipment;
    }

	/**
	 * Syncs an item with either it's parent item or the corresponding order item.
	 *
	 * @param array $args
	 */
    public function sync( $args = array() ) {
    	$item = false;

	    if ( $shipment = $this->get_shipment() ) {
	    	if ( 'return' === $shipment->get_type() ) {
	    		if ( $shipment = $this->get_shipment() ) {
	    			if ( $order_shipment = $shipment->get_order_shipment() ) {
					    $item = $order_shipment->get_simple_shipment_item( $this->get_order_item_id() );
				    }
			    }
		    } else {
	    		$item = $this->get_order_item();
		    }
	    }

    	if ( is_a( $item, '\Vendidero\Germanized\Shipments\ShipmentItem' ) ) {

		    $default_data = $item->get_data();

		    unset( $default_data['id'] );
		    unset( $default_data['shipment_id'] );

		    $default_data['parent_id']   = $item->get_id();
		    $args                        = wp_parse_args( $args, $default_data );

	    } elseif( is_a( $item, 'WC_Order_Item' ) ) {

    		if ( is_callable( array( $item, 'get_variation_id' ) ) && is_callable( array( $item, 'get_product_id' ) ) ) {
			    $this->set_product_id( $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id() );
		    } elseif( is_callable( array( $item, 'get_product_id' ) ) ) {
			    $this->set_product_id( $item->get_product_id() );
		    }

		    $product    = $this->get_product();
		    $tax_total  = is_callable( array( $item, 'get_total_tax' ) ) ? $item->get_total_tax() : 0;;
		    $total      = is_callable( array( $item, 'get_total' ) ) ? $item->get_total() : 0;

		    $args = wp_parse_args( $args, array(
			    'order_item_id' => $item->get_id(),
			    'product_id'    => is_callable( array( $item, 'get_product_id' ) ) ? $item->get_product_id() : 0,
			    'quantity'      => 1,
			    'name'          => $item->get_name(),
			    'sku'           => $product ? $product->get_sku() : '',
			    'total'         => $total + $tax_total,
			    'weight'        => $product ? wc_get_weight( $product->get_weight(), $shipment->get_weight_unit() ) : '',
			    'length'        => $product ? wc_get_dimension( $product->get_length(), $shipment->get_dimension_unit() ) : '',
			    'width'         => $product ? wc_get_dimension( $product->get_width(), $shipment->get_dimension_unit() ) : '',
			    'height'        => $product ? wc_get_dimension( $product->get_height(), $shipment->get_dimension_unit() ) : '',
		    ) );
	    }

	    $this->set_props( $args );

	    /**
	     * Action that fires after a shipment item has been synced. Syncing is used to
	     * keep the shipment item in sync with the corresponding order item or parent shipment item.
	     *
	     * @param WC_Order_Item|ShipmentItem $item The order item object or parent shipment item.
	     * @param array                       $args Array containing props in key => value pairs which have been updated.
	     *
	     * @since 3.0.0
	     * @package Vendidero/Germanized/Shipments
	     */
	    do_action( 'woocommerce_gzd_shipment_item_synced', $this, $item, $args );
    }

    public function get_order_item() {
        if ( is_null( $this->order_item ) && 0 < $this->get_order_item_id() ) {
            if ( $shipment = $this->get_shipment() ) {

                if ( $order = $shipment->get_order() ) {
                    $this->order_item = $order->get_item( $this->get_order_item_id() );
                }
            }
        }

        $item = ( $this->order_item ) ? $this->order_item : false;

        return $item;
    }

    public function get_product() {

        if ( is_null( $this->product ) && 0 < $this->get_product_id() ) {
            $this->product = wc_get_product( $this->get_product_id() );
        }

        $product = ( $this->product ) ? $this->product : false;

        return $product;
    }

    /*
    |--------------------------------------------------------------------------
    | Setters
    |--------------------------------------------------------------------------
    */

    /**
     * Set order ID.
     *
     * @param int $value Order ID.
     */
    public function set_shipment_id( $value ) {
        $this->order_item = null;
        $this->shipment   = null;

        $this->set_prop( 'shipment_id', absint( $value ) );
    }

    /**
     * Set order ID.
     *
     * @param int $value Order ID.
     */
    public function set_order_item_id( $value ) {
        $this->set_prop( 'order_item_id', absint( $value ) );
    }

    /**
     * Set order ID.
     *
     * @param int $value Order ID.
     */
    public function set_total( $value ) {
        $value = wc_format_decimal( $value );

        if ( ! is_numeric( $value ) ) {
            $value = 0;
        }

        $this->set_prop( 'total', $value );
    }

    /**
     * Set order ID.
     *
     * @param int $value Order ID.
     */
    public function set_product_id( $value ) {
        $this->product = null;
        $this->set_prop( 'product_id', absint( $value ) );
    }

	/**
	 * Set parent id.
	 *
	 * @param int $value parent id.
	 */
	public function set_parent_id( $value ) {
		$this->set_prop( 'parent_id', absint( $value ) );
	}

    public function set_sku( $sku ) {
        $this->set_prop( 'sku', $sku );
    }

	/**
	 * Set weight in kg
	 *
	 * @param $weight
	 */
    public function set_weight( $weight ) {
        $this->set_prop( 'weight', '' === $weight ? '' : wc_format_decimal( $weight ) );
    }

	/**
	 * Set width in cm
	 *
	 * @param $weight
	 */
    public function set_width( $width ) {
        $this->set_prop( 'width', '' === $width ? '' : wc_format_decimal( $width ) );
    }

	/**
	 * Set length in cm
	 *
	 * @param $weight
	 */
    public function set_length( $length ) {
        $this->set_prop( 'length', '' === $length ? '' : wc_format_decimal( $length ) );
    }

	/**
	 * Set height in cm
	 *
	 * @param $weight
	 */
    public function set_height( $height ) {
        $this->set_prop( 'height', '' === $height ? '' : wc_format_decimal( $height ) );
    }

    public function get_dimensions() {
        return array(
            'length' => $this->get_length(),
            'width'  => $this->get_width(),
            'height' => $this->get_height(),
        );
    }

    public function set_quantity( $quantity ) {
        $this->set_prop( 'quantity', absint( $quantity ) );
    }

    public function set_name( $name ) {
        $this->set_prop( 'name', $name );
    }

    /*
    |--------------------------------------------------------------------------
    | Other Methods
    |--------------------------------------------------------------------------
    */
}
