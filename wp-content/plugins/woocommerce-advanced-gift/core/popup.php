<?php

add_action( 'wp_head', 'pw_woo_gift_rule_popup_function' );
function pw_woo_gift_rule_popup_function() {

	$query_meta_query   = array( 'relation' => 'AND' );
	$query_meta_query[] = array(
		'key'     => 'status',
		'value'   => "active",
		'compare' => '=',
	);
	$matched_products   = get_posts(
		array(
			'post_type'     => 'pw_gift_rule',
			'numberposts'   => - 1,
			'post_status'   => 'publish',
			'fields'        => 'ids',
			'no_found_rows' => true,
			'orderby'       => 'modified',
			'meta_query'    => $query_meta_query,
		)
	);
	if ( ! is_array( $matched_products ) || count( $matched_products ) <= 0 ) {
		return;
	}
	$setting = get_option( "pw_gift_options" );


	$product_item = "";

	foreach ( $matched_products as $p ) {
		$pw_name          = get_post_meta( $p, 'pw_name', true );
		$cat_depends      = "";
		$category_depends = get_post_meta( $p, 'category_depends', true );
		$category_role    = '';
		if ( $category_depends == "yes" ) {
			$pw_category_depends = get_post_meta( $p, 'pw_category_depends', true );
			if ( get_post_meta( $p, 'pw_category_depends', true ) != "" ) {
				foreach ( $pw_category_depends as $r ) {
					$term          = get_term( $r, 'product_cat' );
					$term_link     = get_term_link( $term );
					$category_role .= '<a href="' . esc_url( $term_link ) . '">' . sprintf( '%s', $term->name ) . '</a><span>/</span>';

				}
			}
		}

		$pw_cart_amount      = get_post_meta( $p, 'pw_cart_amount', true );
		$pw_cart_amount_role = '';
		if ( $pw_cart_amount != '' && $pw_cart_amount != 0 ) {
			$pw_cart_amount_role .= __( 'MINIMUM CART AMOUNT', 'pw_wc_advanced_gift' ) . ' <span class="gift-popup-val">' . wc_price( $pw_cart_amount ) . '</span>';
		}

		$criteria_nb_products      = get_post_meta( $p, 'criteria_nb_products', true );
		$criteria_nb_products_role = '';
		if ( $criteria_nb_products != '' ) {
			$criteria_nb_products_role = __( 'NEED AT LEAST', 'pw_wc_advanced_gift' ) . ' <span class="gift-popup-val">' . sprintf( '%s', $criteria_nb_products ) . '</span> ' . __( 'PRODUCT(S) IN YOUR CART', 'pw_wc_advanced_gift' );
		}

		$pw_from = get_post_meta( $p, 'pw_from', true );
		//End Get From Rule
		//Get to Rule
		$pw_to = get_post_meta( $p, 'pw_to', true );
		//End Get to Rule

		//Get Product Gift
		$pw_gifts_metod = get_post_meta( $p, 'pw_gifts_metod', true );
		if ( $pw_gifts_metod == "product" ) {
			$pw_gifts = get_post_meta( $p, 'pw_gifts', true );
		} else {
			$pw_gifts_category  = get_post_meta( $p, 'pw_gifts_category', true );
			$query_meta_query[] = array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => $pw_gifts_category
				)
			);
			$matched_products   = get_posts(
				array(
					'post_type'     => 'product',
					'numberposts'   => - 1,
					'post_status'   => 'publish',
					'fields'        => 'ids',
					'no_found_rows' => true,
					'tax_query'     => $query_meta_query,
				)
			);
			$pw_gifts           = $matched_products;
		}

		//	if(($pw_to!="" && $blogtime>$pw_to) || ($pw_from!="" && $blogtime<$pw_from))
		//	{

		//	}

		foreach ( (array) $pw_gifts as $r ) {

			$product      = wc_get_product( $r );
			$img_url      = wp_get_attachment_image_src( $product->get_image_id(), 'large' );
			$img_url      = $img_url[0];
			$title        = $product->get_title();
			$permalink    = $product->get_permalink();
			$product_item .= '
					
						<div class="gift-product-item" >
							<a href="' . esc_url( $permalink ) . '"><img src="' . esc_url( $img_url ) . '" class="wg-img" /></a>
							<div class="gift-product-title"><a href="' . esc_url( $permalink ) . '">' . sprintf( '%s', $title ) . '</a>
							<div class="gift-popup-depends">';

			$product_item .= ( $category_role != '' ) ? '<div class="gift-popup-depends-item">' . __( 'CATEGORY DEPENDS', 'pw_wc_advanced_gift' ) . ': ' . sprintf( '%s', $category_role ) . '</div>' : '';

			$product_item .= ( $criteria_nb_products_role != '' ) ? '
								<div class="gift-popup-depends-item">' . sprintf( '%', $criteria_nb_products_role ) . '</div>' : '';

			$product_item .= ( $pw_cart_amount_role != '' ) ? '
								<div class="gift-popup-depends-item">' . sprintf( '%s', $pw_cart_amount_role ) . '</div>' : '';

			$product_item .= '
							</div>
						</div>
					</div>
					';
		}

	}
	$did = rand( 0, 1000 );
	echo '
	<div class="pw-cover"></div>
	<div class="pw_gift_popup_main" style="visibility:hidden">
		<h2 class="pw-title">' . sprintf( '%s', $setting['popup_title'] ) . '</h2><div class="pw_gift_popup_close"></div>
			<div class="pw-gifts gift-popup-car">
				<div class="owl-carousel wb-car-car  wb-carousel-layout wb-car-cnt slider_' . esc_attr( $did ) . '" id="" >
						' . sprintf( '%s', $product_item ) . '
				</div>
			</div>
	</div>';


	echo "<script type='text/javascript'>
				jQuery(window).on('resize', function(){
					chanegLayout_popup();
				});
				
				function chanegLayout_popup() {
				  jQuery('.pw_gift_popup_main').css({
					position: 'fixed',
					left: (jQuery(window).width() - jQuery('.pw_gift_popup_main').outerWidth()) / 2,
					top: (jQuery(window).height() - jQuery('.pw_gift_popup_main').outerHeight()) / 2  ,
				  });	
				}
				  	
                jQuery(document).ready(function() {
					
					var owl;
                    setTimeout(function(){
						jQuery.when(
							 jQuery(document).find('.slider_" . $did . "').owlCarousel({
								  margin : " . esc_attr( $setting['popup_pw_item_marrgin'] ) . " , 
								  loop:true,
								  dots:" . esc_attr( $setting['popup_pw_show_pagination'] ) . ",
								  nav:" . esc_attr( $setting['popup_pw_show_control'] ) . ",
								  slideBy: " . esc_attr( $setting['popup_pw_item_per_slide'] ) . ",
								  autoplay:" . esc_attr( $setting['popup_pw_auto_play'] ) . ",
								  autoplayTimeout : " . esc_attr( $setting['popup_pw_slide_speed'] ) . ",
								  responsive:{
									0:{
										items:1
									},
									600:{
										items:2
									},
									1000:{
										items:" . esc_attr( $setting['popup_pw_item_per_view'] ) . "
									}
								},
								autoplayHoverPause: true,
								navText: [ '>', '<' ]
							 }),
						).done(function( x ) {
							jQuery('.gift-popup').css('visibility','visible');
								jQuery('.pw_gift_popup_main').css('visibility','visible');
								jQuery(window).resize(chanegLayout_popup());
						});
					},500);
					
					jQuery('.pw-cover,.pw_gift_popup_close').on('click',function(){
						jQuery('.pw_gift_popup_main').css('visibility','hidden');
						jQuery('.pw-cover').css('visibility','hidden');
					});
				});
   </script>";

}

?>