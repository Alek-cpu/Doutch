<?php
/**
 * Display product condition and notes as attributes in the product page
 */
class WPLA_Product_Attributes {

    /**
     * Register hooks
     */
    public function __construct() {
        if ( WPLA_SettingsPage::getOption( 'display_condition_and_notes', 0 ) == 1 ) {
            add_filter( 'woocommerce_product_get_attributes', array( $this, 'addProductAttributes' ) );
        }
    }

    /**
     * Add item condition and item note as attributes
     * @param array $attributes
     * @return array
     */
    public function addProductAttributes( $attributes = array() ) {
        global $product;

        if ( !is_object( $product ) ) {
            return $attributes;
        }

        $condition  = get_post_meta( wpla_get_product_meta( $product, 'id' ), '_amazon_condition_type', true );
        $note       = get_post_meta( wpla_get_product_meta( $product, 'id' ), '_amazon_condition_note', true );

        if ( $condition ) {
            $condition = self::getConditionString( $condition );
            $attributes[] = $this->addAttribute( __( 'Condition', 'wp-lister-for-amazon' ), $condition );
        }

        if ( $note ) {
            $attributes[] = $this->addAttribute( __( 'Note', 'wp-lister-for-amazon' ), $note );
        }

        return $attributes;
    }

    /**
     * Return a readable string from the given $conditionType
     *
     * @param string $conditionType
     * @return string
     */
    public static function getConditionString( $conditionType ) {
        $string = $conditionType;
        $map = array(
            'New'                   => __( 'New', 'wp-lister-for-amazon' ),
            'UsedLikeNew'           => __( 'Used - Like New', 'wp-lister-for-amazon' ),
            'UsedVeryGood'          => __( 'Used - Very Good', 'wp-lister-for-amazon' ),
            'UsedGood'              => __( 'Used - Good', 'wp-lister-for-amazon' ),
            'UsedAcceptable'        => __( 'Used - Acceptable', 'wp-lister-for-amazon' ),
            'Refurbished'           => __( 'Refurbished', 'wp-lister-for-amazon' ),
            'CollectibleLikeNew'    => __( 'Collectible - Like New', 'wp-lister-for-amazon' ),
            'CollectibleVeryGood'   => __( 'Collectible - Very Good', 'wp-lister-for-amazon' ),
            'CollectibleGood'       => __( 'Collectible - Good', 'wp-lister-for-amazon' ),
            'CollectibleAcceptable' => __( 'Collectible - Acceptable', 'wp-lister-for-amazon' ),
        );

        if ( isset( $map[ $conditionType ] ) ) {
            $string = $map[ $conditionType ];
        }

        return $string;
    }

    /**
     * @param $name
     * @param string $value
     * @param bool $is_visible
     * @return WC_Product_Attribute|array
     */
    private function addAttribute( $name, $value = '', $is_visible = true ) {
        if ( class_exists( 'WC_Product_Attribute' ) ) {
            $attr = new WC_Product_Attribute();
            $attr->set_name( $name );
            $attr->set_visible( $is_visible );

            if ( ! empty( $value ) ) {
                $attr->set_options( array( $value ) );
            }

            return $attr;
        } else {
            return  array(
                'is_visible'    => $is_visible,
                'is_taxonomy'   => false,
                'name'          => $name,
                'value'         => $value
            );
        }
    }

}