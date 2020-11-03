<?php
/**
 * Save settings ajax action
 *
 * @author        Alex Kovalev <alex.kovalevv@gmail.com>, Github: https://github.com/alexkovalevv
 * @copyright (c) 21.09.2019, Webcraftic
 * @version       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajax action for save plugin settings.
 *
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  2.0.0
 */
function wam_save_settings_action() {
	check_ajax_referer( 'wam_save_settigns' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [
			'error_message_title'   => __( 'Save settings failed!', 'gonzales' ),
			'error_message_content' => __( 'You don\'t have enough capability to edit this information.', 'gonzales' )
		] );
	}

	/*$scrape_key   = md5( rand() );
	$transient    = 'scrape_key_' . $scrape_key;
	$scrape_nonce = strval( rand() );
	set_transient( $transient, $scrape_nonce, 60 ); // It shouldn't take more than 60 seconds to make the two loopback requests.

	$cookies       = wp_unslash( $_COOKIE );
	$scrape_params = array(
		'wp_scrape_key'   => $scrape_key,
		'wp_scrape_nonce' => $scrape_nonce,
	);
	$headers       = array(
		'Cache-Control' => 'no-cache',
	);

	/** This filter is documented in wp-includes/class-wp-http-streams.php */ ///*$sslverify = apply_filters( 'https_local_ssl_verify', false );
	//
	//// Include Basic auth in loopback requests.
	//if ( isset( $_SERVER['PHP_AUTH_USER'] ) && isset( $_SERVER['PHP_AUTH_PW'] ) ) {
	//	$headers['Authorization'] = 'Basic ' . base64_encode( wp_unslash( $_SERVER['PHP_AUTH_USER'] ) . ':' . wp_unslash( $_SERVER['PHP_AUTH_PW'] ) );
	//}
	//
	//// Make sure PHP process doesn't die before loopback requests complete.
	//set_time_limit( 300 );
	//
	//// Time to wait for loopback requests to finish.
	//$timeout = 100;
	//
	//$needle_start = "###### wp_scraping_result_start:$scrape_key ######";
	//$needle_end   = "###### wp_scraping_result_end:$scrape_key ######";
	//
	//// Attempt loopback request to editor to see if user just whitescreened themselves.
	//if ( $plugin ) {
	//	$url = add_query_arg( compact( 'plugin', 'file' ), admin_url( 'plugin-editor.php' ) );
	//} elseif ( isset( $stylesheet ) ) {
	//	$url = add_query_arg(
	//		array(
	//			'theme' => $stylesheet,
	//			'file'  => $file,
	//		),
	//		admin_url( 'theme-editor.php' )
	//	);
	//} else {
	//	$url = admin_url();
	//}
	//$url                    = add_query_arg( $scrape_params, $url );
	//$r                      = wp_remote_get( $url, compact( 'cookies', 'headers', 'timeout', 'sslverify' ) );
	//$body                   = wp_remote_retrieve_body( $r );
	//$scrape_result_position = strpos( $body, $needle_start );
	//
	//$loopback_request_failure = array(
	//	'code'    => 'loopback_request_failed',
	//	'message' => __( 'Unable to communicate back with site to check for fatal errors, so the PHP change was reverted. You will need to upload your PHP file change by some other means, such as by using SFTP.' ),
	//);
	//$json_parse_failure       = array(
	//	'code' => 'json_parse_error',
	//);
	//
	//$result = null;
	//if ( false === $scrape_result_position ) {
	//	$result = $loopback_request_failure;
	//} else {
	//	$error_output = substr( $body, $scrape_result_position + strlen( $needle_start ) );
	//	$error_output = substr( $error_output, 0, strpos( $error_output, $needle_end ) );
	//	$result       = json_decode( trim( $error_output ), true );
	//	if ( empty( $result ) ) {
	//		$result = $json_parse_failure;
	//	}
	//}
	//
	//// Try making request to homepage as well to see if visitors have been whitescreened.
	//if ( true === $result ) {
	//	$url                    = home_url( '/' );
	//	$url                    = add_query_arg( $scrape_params, $url );
	//	$r                      = wp_remote_get( $url, compact( 'cookies', 'headers', 'timeout' ) );
	//	$body                   = wp_remote_retrieve_body( $r );
	//	$scrape_result_position = strpos( $body, $needle_start );
	//
	//	if ( false === $scrape_result_position ) {
	//		$result = $loopback_request_failure;
	//	} else {
	//		$error_output = substr( $body, $scrape_result_position + strlen( $needle_start ) );
	//		$error_output = substr( $error_output, 0, strpos( $error_output, $needle_end ) );
	//		$result       = json_decode( trim( $error_output ), true );
	//		if ( empty( $result ) ) {
	//			$result = $json_parse_failure;
	//		}
	//	}
	//}
	//
	//delete_transient( $transient );
	//
	//if ( true !== $result ) {
	//
	//	// Roll-back file change.
	//	file_put_contents( $real_file, $previous_content );
	//	if ( function_exists( 'opcache_invalidate' ) ) {
	//		opcache_invalidate( $real_file, true );
	//	}
	//
	//	if ( ! isset( $result['message'] ) ) {
	//		$message = __( 'Something went wrong.' );
	//	} else {
	//		$message = $result['message'];
	//		unset( $result['message'] );
	//	}
	//	return new WP_Error( 'php_error', $message, $result );
	//}*/

	$save_message_title   = __( 'Settings saved successfully!', 'clearfy' );
	$save_message_content = __( 'If you use test mode, do not forget to disable it. We also recommend that you flush the cache if you use caching plugins.', 'clearfy' );
	$scope                = WGZ_Plugin::app()->request->post( 'scope', 'frontend' );
	$raw_updated_settings = WGZ_Plugin::app()->request->post( 'settings', [], true );

	if ( ! empty( $raw_updated_settings ) ) {
		if ( 'networkadmin' === $scope ) {
			$settings = WGZ_Plugin::app()->getNetworkOption( 'backend_assets_states', [] );
		} else if ( 'admin' === $scope ) {
			$settings = WGZ_Plugin::app()->getOption( 'backend_assets_states', [] );
		} else {
			$settings = WGZ_Plugin::app()->getOption( 'assets_states', [] );
		}

		if ( ! defined( 'WGZP_PLUGIN_ACTIVE' ) || ( is_array( $settings ) && ! isset( $settings['save_mode'] ) ) ) {
			$settings['save_mode'] = false;
		}

		if ( ! empty( $raw_updated_settings['plugins'] ) ) {
			foreach ( (array) $raw_updated_settings['plugins'] as $plugin_name => $plugin_group ) {
				if ( ! empty( $plugin_group['load_mode'] ) ) {
					if ( 'enable' == $plugin_group['load_mode'] ) {
						$plugin_group['visability'] = "";
					} else {
						foreach ( [ 'js', 'css' ] as $assets_type ) {
							if ( ! empty( $plugin_group[ $assets_type ] ) ) {
								foreach ( $plugin_group[ $assets_type ] as $resource_handle => $resource_params ) {
									$plugin_group[ $assets_type ][ $resource_handle ]['visability'] = "";
								}
							}
						}
					}
				}

				$settings['plugins'][ $plugin_name ] = $plugin_group;
			}
		}

		if ( ! empty( $raw_updated_settings['theme'] ) ) {
			$settings['theme'] = $raw_updated_settings['theme'];
		}

		if ( ! empty( $raw_updated_settings['misc'] ) ) {
			$settings['misc'] = $raw_updated_settings['misc'];
		}

		/**
		 * Filter run before save settings.
		 *
		 * @param array  $settings
		 * @param array  $raw_updated_settings
		 * @param string $scope
		 */
		$settings = apply_filters( 'wam/before_save_settings', $settings, $raw_updated_settings, $scope );

		if ( 'networkadmin' === $scope ) {
			WGZ_Plugin::app()->updateNetworkOption( 'backend_assets_states', $settings );
		} else if ( 'admin' === $scope ) {
			WGZ_Plugin::app()->updateOption( 'backend_assets_states', $settings );
		} else {
			WGZ_Plugin::app()->updateOption( 'assets_states', $settings );
		}

		// If mu  plugin does not exist, install it.
		wbcr_gnz_deploy_mu_plugin();

		// Flush cache for all cache plugins
		WbcrFactoryClearfy221_Helpers::flushPageCache();
	}

	wp_send_json_success( [
		'save_massage_title'   => $save_message_title,
		'save_message_content' => $save_message_content
	] );
}

add_action( 'wp_ajax_nopriv_wam-save-settings', 'wam_save_settings_action' );
add_action( 'wp_ajax_wam-save-settings', 'wam_save_settings_action' );