<?php

namespace WPML\CF7;

class Shortcodes implements \IWPML_Frontend_Action {

	public function add_hooks() {
		add_filter( 'shortcode_atts_wpcf7', [ $this, 'translate_shortcode_form_id' ] );
	}

	/**
	 * Translate the `id` in the shortcode attributes on-the-fly.
	 *
	 * @param array $out Shortcode attributes to be filtered.
	 *
	 * @return array
	 */
	public function translate_shortcode_form_id( $out ) {
		$out['id'] = apply_filters( 'wpml_object_id', $out['id'], Constants::POST_TYPE, true );

		return $out;
	}
}
