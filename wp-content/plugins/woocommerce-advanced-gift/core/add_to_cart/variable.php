<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<?php 
	$available_variations = $product->get_available_variations();
	$attributes	= $product->get_variation_attributes();
	$selected_attributes = $product->get_default_attributes();
?>
<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<?php $result[0] .= '<form class="variations_form cart" method="post" enctype=\'multipart/form-data\' data-product_id="' . $post->ID . '" data-product_variations="' . esc_attr( json_encode( $available_variations ) ) . '">';
	if ( ! empty( $available_variations ) ) :
		$result[0] .= '<table class="variations" cellspacing="0">';
			$result[0] .= '<tbody>';
				$loop = 0; foreach ( $attributes as $name => $options ) : $loop++;
					$result[0] .= '<tr>';
						$result[0] .= '<td class="label"><label for="' . sanitize_title($name) . '">' . wc_attribute_label( $name ) . '</label></td>';
						$result[0] .= '<td class="value"><select id="' . esc_attr( sanitize_title( $name ) ) . '" name="attribute_' .  sanitize_title( $name ) . '">';
							$result[0] .= '<option value="">' .  __( 'Choose an option', 'woocommerce' ) . '&hellip;</option>';
							
								if ( is_array( $options ) ) {

									if ( isset( $_REQUEST[ 'attribute_' . sanitize_title( $name ) ] ) ) {
										$selected_value = $_REQUEST[ 'attribute_' . sanitize_title( $name ) ];
									} elseif ( isset( $selected_attributes[ sanitize_title( $name ) ] ) ) {
										$selected_value = $selected_attributes[ sanitize_title( $name ) ];
									} else {
										$selected_value = '';
									}

									// Get terms if this is a taxonomy - ordered
									if ( taxonomy_exists( sanitize_title( $name ) ) ) {

										$orderby = wc_attribute_orderby( sanitize_title( $name ) );

										switch ( $orderby ) {
											case 'name' :
												$args = array( 'orderby' => 'name', 'hide_empty' => false, 'menu_order' => false );
											break;
											case 'id' :
												$args = array( 'orderby' => 'id', 'order' => 'ASC', 'menu_order' => false, 'hide_empty' => false );
											break;
											case 'menu_order' :
												$args = array( 'menu_order' => 'ASC', 'hide_empty' => false );
											break;
										}

										$terms = get_terms( sanitize_title( $name ), $args );

										foreach ( $terms as $term ) {
											if ( ! in_array( $term->slug, $options ) )
												continue;

											$result[0] .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $selected_value ), sanitize_title( $term->slug ), false ) . '>' . apply_filters( 'woocommerce_variation_option_name', $term->name ) . '</option>';
										}
									} else {

										foreach ( $options as $option ) {
											$result[0] .= '<option value="' . esc_attr( sanitize_title( $option ) ) . '" ' . selected( sanitize_title( $selected_value ), sanitize_title( $option ), false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
										}

									}
								}
							
						$result[0] .= '</select>';
							if ( sizeof( $attributes ) == $loop )
								$result[0] .= '<a class="reset_variations" href="#reset">' . __( 'Clear selection', 'woocommerce' ) . '</a>';
						$result[0] .= '</td>';
					$result[0] .= '</tr>';
		        endforeach;
			$result[0] .= '</tbody>';
		$result[0] .= '</table>';

		do_action( 'woocommerce_before_add_to_cart_button' );

		$result[0] .= '<div class="single_variation_wrap" style="display:none;">';
			do_action( 'woocommerce_before_single_variation' );

			$result[0] .= '<div class="single_variation"></div>';

			$result[0] .= '<div class="variations_button">';
				//$result[0] .= woocommerce_quantity_input( array(), null, false );
				$result[0] .= '<button type="submit" class="single_add_to_cart_button button btn btn-5 btn-5a icon-cart adc">' .  $product->single_add_to_cart_text() . '</button>';
			$result[0] .= '</div>';

			$result[0] .= '<input type="hidden" name="add-to-cart" value="' . $product->id . '" />';
			$result[0] .= '<input type="hidden" name="product_id" value="' . esc_attr( $post->ID ) . '" />';
			$result[0] .= '<input type="hidden" name="variation_id" value="" />';

			do_action( 'woocommerce_after_single_variation' );
		$result[0] .= '</div>';

		do_action( 'woocommerce_after_add_to_cart_button' );

	else :

		$result[0] .= '<p class="stock out-of-stock">' . __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) . '</p>';

	endif;

$result[0] .= '</form>';

do_action( 'woocommerce_after_add_to_cart_form' );
?>