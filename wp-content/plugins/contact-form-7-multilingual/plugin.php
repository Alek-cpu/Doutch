<?php
/**
 * Plugin Name: Contact Form 7 Multilingual
 * Plugin URI:
 * Description: Make forms from Contact Form 7 translatable with WPML | <a href="https://wpml.org/documentation/plugins-compatibility/using-contact-form-7-with-wpml/">Documentation</a>
 * Author: OnTheGoSystems
 * Author URI: http://www.onthegosystems.com/
 * Version: 1.0.2
 * Plugin Slug: contact-form-7-multilingual
 *
 * @package wpml/cf7
 */

if ( defined( 'CF7ML_VERSION' ) ) {
	return;
}

define( 'CF7ML_VERSION', '1.0.2' );
define( 'CF7ML_PLUGIN_PATH', dirname( __FILE__ ) );

require_once CF7ML_PLUGIN_PATH . '/vendor/autoload.php';

/**
 * Entry point.
 */
function cf7ml_init() {
	$action_loader = new \WPML_Action_Filter_Loader();
	$action_loader->load( [
		WPML\CF7\Translations::class,
		WPML\CF7\Language_Metabox::class,
		WPML\CF7\Shortcodes::class,
	] );
}

add_action( 'wpml_loaded', 'cf7ml_init' );
