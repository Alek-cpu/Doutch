<?php
    // If this file is called directly, abort.
    defined( 'WPINC' ) || die;

    class GAOO_Utils {

        /**
         * List of all options with the default value.
         *
         * @param bool $only_names Show only the names (keys) of the options, no defaults.
         *
         * @return array
         */
        public static function get_options_list( $only_names = false ) {
            $options = array(
                'ga_plugin'          => 'manual',
                'link_deactivate'    => esc_html__( 'Disallow Google Analytics to track me', 'ga-opt-out' ),
                'link_activate'      => esc_html__( 'Allow Google Analytics to track me', 'ga-opt-out' ),
                'ua_code'            => null,
                'popup_deactivate'   => esc_html__( 'Tracking is now disabled. Click the link again to enable it.', 'ga-opt-out' ),
                'popup_activate'     => esc_html__( 'Tracking is now enabled. Click the link again to disable it.', 'ga-opt-out' ),
                'status'             => 'off',
                'privacy_page_id'    => 0,
                'disable_monitoring' => 0,
                'force_reload'       => 0,
                'wp_privacy_page'    => 0,
                'custom_css'         => '',
                'tracking_code'      => '',
                'status_intervall'   => 'weekly',
                'status_mails'       => '',
                'status_mails_sync'  => 0,
            );

            if ( ! empty( $only_names ) ) {
                return array_keys( $options );
            }

            return $options;
        }

        /**
         * Returns all options from this plugin
         *
         * @return array with fields (fieldname => fieldvalue)
         */
        public static function get_options() {
            // Set defaults
            $data = self::get_options_list();

            foreach ( $data as $k => &$v ) {
                $option = get_option( GAOO_PREFIX . $k, $v );

                if ( is_string( $option ) ) {
                    $option = stripslashes( $option );
                }

                $data[ $k ] = $option;
            }

            // Prefer WordPress Privacy page, is sync is enabled.
            if ( ! empty( $data[ 'wp_privacy_page' ] ) ) {
                $data[ 'privacy_page_id' ] = get_option( 'wp_page_for_privacy_policy', $data[ 'privacy_page_id' ] );
            }

            return $data;
        }

        /**
         * Return value of a option.
         *
         * @param string $name    Name of the option.
         * @param mixed  $default Value to be returned, if option wasn't found. (Default: null)
         *
         * @return mixed Value of the option if found, otherwise the default value.
         */
        public static function get_option( $name, $default = null ) {
            $names = self::get_options_list( true );

            if ( empty( $name ) || ! in_array( $name, $names, true ) ) {
                return $default;
            }

            $option = get_option( GAOO_PREFIX . $name, $default );

            // Prefer WordPress Privacy page, is sync is enabled.
            if ( $name == 'privacy_page_id' && ! empty( self::get_option( 'wp_privacy_page' ) ) ) {
                $option = get_option( 'wp_page_for_privacy_policy', $option );
            }

            if ( is_string( $option ) ) {
                return stripslashes( $option );
            }

            return $option;
        }

        /**
         * Return the JSON string with form data for the JavaScript code.
         *
         * @param array|null  $form_data Current settings array (Default: null)
         * @param string|null $ua_code   UA-Code (Default: null)
         *
         * @return array|null
         */
        public static function get_json( $form_data = null, $ua_code = null ) {
            if ( empty( $form_data ) ) {
                $form_data = self::get_options();
            }

            if ( empty( $ua_code ) && ! empty( $form_data ) ) {
                $ua_code = GAOO_Utils::get_ua_code( $form_data[ 'ga_plugin' ], $form_data[ 'ua_code' ] );
            }

            if ( empty( $ua_code ) ) {
                return null;
            }

            $json_data = array(
                'link_deactivate'        => apply_filters( 'gaoo_link_deactivate_text', $form_data[ 'link_deactivate' ] ),
                'link_activate'          => apply_filters( 'gaoo_link_activate_text', $form_data[ 'link_activate' ] ),
                'force_reload'           => apply_filters( 'gaoo_force_reload', boolval( $form_data[ 'force_reload' ] ) ),
                'disable_string'         => 'ga-disable-' . $ua_code,
                'generic_disable_string' => 'ga-opt-out',
            );

            if ( ! empty( $form_data[ 'popup_activate' ] ) ) {
                $json_data[ 'popup_activate' ] = apply_filters( 'gaoo_popup_activate_text', $form_data[ 'popup_activate' ] );
            }

            if ( ! empty( $form_data[ 'popup_deactivate' ] ) ) {
                $json_data[ 'popup_deactivate' ] = apply_filters( 'gaoo_popup_deactivate_text', $form_data[ 'popup_deactivate' ] );
            }

            return $json_data;
        }

        /**
         * Returns the UA-Code
         *
         * @param string $ga_plugin Key (monsterinsights, gadash, analytify, manual) of choosen GA Plugin
         * @param string $ua_code   The UA-Code if GA Plugin is set to manual (Default: null)
         *
         * @return string The UA-Code (UA-XXXXXX-Y)
         */
        public static function get_ua_code( $ga_plugin, $ua_code = null ) {

            switch ( $ga_plugin ) {
                case 'monsterinsights':

                    if ( function_exists( 'monsterinsights_get_ua_to_output' ) ) {
                        $ua_code = monsterinsights_get_ua_to_output();
                    }

                    break;

                case 'gadash':

                    // backward compatibility to older versions
                    if ( function_exists( 'GADWP' ) ) {
                        $gadwp = GADWP();

                        $profiles_list = $gadwp->config->options[ 'ga_profiles_list' ];
                        $tableid       = $gadwp->config->options[ 'tableid_jail' ];

                        // backwards compatibility for older versions. New version renamed options.
                        if ( empty( $profiles_list ) ) {
                            $profiles_list = $gadwp->config->options[ 'ga_dash_profile_list' ];
                        }

                        if ( empty( $tableid ) ) {
                            $tableid = $gadwp->config->options[ 'ga_dash_tableid_jail' ];
                        }

                        $profile_info = GADWP_Tools::get_selected_profile( $profiles_list, $tableid );
                        $ua_code      = $profile_info[ 2 ];
                    }

                    if ( function_exists( 'exactmetrics_get_ua' ) ) {
                        $ua_code = exactmetrics_get_ua();
                    }

                    break;

                case 'analytify':

                    if ( class_exists( 'WP_ANALYTIFY_FUNCTIONS' ) ) {
                        $ua_code = WP_ANALYTIFY_FUNCTIONS::get_UA_code();
                    }

                    if ( empty( $ua_code ) && class_exists( 'WP_Analytify_Settings' ) ) {
                        $analytify_settings = new WP_Analytify_Settings();
                        $ua_code            = $analytify_settings->get_option( 'manual_ua_code', 'wp-analytify-authentication', false );
                    }

                    break;

                case 'gaga':

                    if ( ! empty( $GLOBALS[ 'GA_Google_Analytics' ] ) ) {
                        global $GA_Google_Analytics;

                        $options = get_option( 'gap_options', $GA_Google_Analytics->default_options() );
                        $ua_code = ( ! empty( $options[ 'gap_id' ] ) ) ? $options[ 'gap_id' ] : null;
                    }

                    break;

                case 'sitekit':

                    $analytics = get_option( 'googlesitekit_analytics_settings' );

                    if ( ! empty( $analytics ) && ! empty( $analytics[ 'propertyID' ] ) ) {
                        $ua_code = $analytics[ 'propertyID' ];
                    }

                    break;

                case 'manual':

                    $ua_code = self::get_option( 'ua_code' );

                    break;

                default:
                    $ua_code = null;
            }

            return self::validate_ua_code( apply_filters( 'gaoo_get_ua_code', $ua_code, $ga_plugin ) );
        }

        /**
         * Validate the UA code.
         *
         * @param string $ua_code UA code to check validity.
         *
         * @return string Cleaned UA code if valid, otherwise an empty string.
         */
        public static function validate_ua_code( $ua_code = '' ) {
            $ua_code = (string) $ua_code;
            $ua_code = preg_replace( '/\s+/', '', $ua_code );

            if ( empty( $ua_code ) ) {
                return '';
            }

            // Replace all type of dashes (n-dash, m-dash, minus) with normal dashes.
            $ua_code = str_replace( array( '–', '—', '−' ), '-', $ua_code );

            if ( preg_match( "/^(UA|YT|MO)-\d{4,}-\d+$/", strval( $ua_code ) ) ) {
                return $ua_code;
            }

            return '';
        }

        /**
         * Checking the status of the current website.
         *
         * @param array $data       Data with the plugin options.
         * @param bool  $get_labels Return array with labels. (Default: false)
         *
         * @return array Labeld array or an array with sum of the states.
         */
        public static function check_todos( $data, $get_labels = false ) {
            // Check if shortcode is set on page
            $privacy_page_accessibile = $shortcode_url = $shortcode_available = null;

            if ( ! empty( $data[ 'privacy_page_id' ] ) && ( $page = get_post( $data[ 'privacy_page_id' ] ) ) ) {
                $page_content        = sanitize_post_field( 'post_content', $page->post_content, $page->ID, 'raw' );
                $shortcode_available = ( ! empty( $page_content ) && false !== strpos( $page_content, GAOO_SHORTCODE ) );
                $shortcode_url       = admin_url( 'post.php?action=edit&post=' . $data[ 'privacy_page_id' ] );

                $privacy_page_accessibile = ( sanitize_post_field( 'post_status', $page->post_status, $page->ID, 'raw' ) == 'publish' && empty( $page->post_password ) );

                // ACF support
                if ( class_exists( 'ACF' ) && ! $shortcode_available ) {
                    $fields = get_fields( $page->ID );

                    foreach ( $fields as $name => $value ) {
                        if ( ! empty( $value ) && is_string( $value ) && false !== strpos( $value, GAOO_SHORTCODE ) ) {
                            $shortcode_available = true;
                            break;
                        }
                    }
                }
            }

            // Check if ip anonymization is enabled and set the urls
            if ( $data[ 'ga_plugin' ] == 'monsterinsights' && function_exists( 'monsterinsights_get_option' ) ) {
                $anonymip_enabled = monsterinsights_get_option( 'anonymize_ips', false );
                $anonymip_url     = admin_url( 'admin.php?page=monsterinsights_settings#/engagement' );

                $uacode_url = admin_url( 'admin.php?page=monsterinsights_settings' );

            }
            elseif ( $data[ 'ga_plugin' ] == 'gadash' ) {

                // backward compatibility to older versions
                if ( function_exists( 'GADWP' ) ) {
                    $gadwp            = GADWP();
                    $anonymip_enabled = $gadwp->config->options[ 'ga_anonymize_ip' ];

                    // backwards compatibility for older versions. New version renamed options.
                    if ( is_null( $anonymip_enabled ) ) {
                        $anonymip_enabled = $gadwp->config->options[ 'ga_dash_anonim' ];
                    }

                    $anonymip_url = admin_url( 'admin.php?page=gadwp_tracking_settings#top#gadwp-advanced' );
                    $uacode_url   = admin_url( 'admin.php?page=gadwp_tracking_settings' );
                }
                elseif ( function_exists( 'exactmetrics_get_option' ) ) {
                    $anonymip_enabled = exactmetrics_get_option( 'anonymize_ips', false );
                    $uacode_url       = admin_url( 'admin.php?page=exactmetrics_settings#/' );
                    $anonymip_url     = admin_url( 'admin.php?page=exactmetrics_settings#/engagement' );
                }

            }
            elseif ( $data[ 'ga_plugin' ] == 'analytify' && class_exists( 'WP_Analytify' ) ) {
                $analytify        = WP_Analytify::get_instance();
                $anonymip_enabled = $analytify->settings->get_option( 'anonymize_ip', 'wp-analytify-advanced' );
                $anonymip_url     = admin_url( 'admin.php?page=analytify-settings#wp-analytify-advanced' );

                $uacode_url = admin_url( 'admin.php?page=analytify-settings#wp-analytify-profile' );

            }
            elseif ( $data[ 'ga_plugin' ] == 'gaga' && ! empty( $GLOBALS[ 'GA_Google_Analytics' ] ) ) {
                global $GA_Google_Analytics;

                $options          = get_option( 'gap_options', $GA_Google_Analytics->default_options() );
                $anonymip_enabled = ( ! empty( $options[ 'gap_anonymize' ] ) ) ? $options[ 'gap_anonymize' ] : false;
                $anonymip_url     = admin_url( 'options-general.php?page=ga-google-analytics#gap-panel-settings' );

                $uacode_url = admin_url( 'options-general.php?page=ga-google-analytics#gap-panel-settings' );

            }
            elseif ( $data[ 'ga_plugin' ] == 'sitekit' && ( $analytics = get_option( 'googlesitekit_analytics_settings' ) ) ) {
                $anonymip_url     = null;
                $uacode_url       = admin_url( 'admin.php?page=googlesitekit-module-analytics' );
                $anonymip_enabled = ! empty( $analytics[ 'anonymizeIP' ] );
            }
            else { // manual
                $anonymip_enabled = $anonymip_url = $uacode_url = null;

                if ( ! empty( $data[ 'tracking_code' ] ) ) {
                    $anonymip_enabled = (bool) preg_match_all( '/(anonymizeIp|anonymize_ip).*true/mi', $data[ 'tracking_code' ] );
                }
            }

            $ua_code     = GAOO_Utils::get_ua_code( $data[ 'ga_plugin' ] );
            $ua_code_txt = empty( $ua_code ) ? '' : " ($ua_code)";
            $checklist   = array(
                array(
                    'label'   => esc_html__( 'Opt-Out Enabled', 'ga-opt-out' ),
                    'checked' => ( $data[ 'status' ] == 'on' || empty( $data[ 'status' ] ) ),
                ),
                array(
                    'label'   => esc_html__( 'Found valid UA-Code', 'ga-opt-out' ) . $ua_code_txt,
                    'checked' => ( ! empty( $ua_code ) ),
                    'url'     => $uacode_url,
                ),
                array(
                    'label'   => esc_html__( 'IP anonymization is enabled', 'ga-opt-out' ),
                    'checked' => boolval( $anonymip_enabled ),
                    'url'     => $anonymip_url,
                ),
                array(
                    'label'   => esc_html__( 'Found shortcode on page', 'ga-opt-out' ),
                    'checked' => $shortcode_available,
                    'url'     => $shortcode_url,
                ),
                array(
                    'label'   => esc_html__( 'Page accessibile', 'ga-opt-out' ),
                    'checked' => $privacy_page_accessibile,
                    'url'     => $shortcode_url,
                ),
                array(
                    'label'   => esc_html__( 'Cronjob for this plugin activated', 'ga-opt-out' ),
                    'checked' => ! empty( wp_next_scheduled( GAOO_CRONJOB ) ),
                ),
            );

            // Remove cached value if status of todos has been updated.
            self::delete_todo_cache();

            // Return labeled array
            if ( ! empty( $get_labels ) ) {
                return $checklist;
            }

            $dontknow = $check = $todo = 0;

            foreach ( $checklist as $item ) {
                if ( false === $item[ 'checked' ] ) {
                    $todo++;
                }
                elseif ( true === $item[ 'checked' ] ) {
                    $check++;
                }
                else {
                    $dontknow++;
                }
            }

            return array(
                'sum'      => count( $checklist ),
                'dontknow' => $dontknow,
                'check'    => $check,
                'todo'     => $todo,
            );
        }

        /**
         * Stopping the cronjob.
         */
        public static function stop_cronjob() {
            wp_clear_scheduled_hook( GAOO_CRONJOB );
        }

        /**
         * Add custom schedules to WordPress defaults.
         *
         * @param array $schedules List of schedules
         *
         * @return array All schedules
         */
        public function add_cron_schedules( $schedules ) {
            if ( ! isset( $schedules[ 'weekly' ] ) ) {
                $schedules[ 'weekly' ] = array(
                    'interval' => 604800,
                    'display'  => __( 'weekly', 'ga-opt-out' ),
                );
            }

            if ( ! isset( $schedules[ 'monthly' ] ) ) {
                $schedules[ 'monthly' ] = array(
                    'interval' => 2592000,
                    'display'  => __( 'monthly', 'ga-opt-out' ),
                );
            }

            return $schedules;
        }

        /**
         * (Re-)Start the cronjob.
         *
         * @param bool $restart Should restart running cronjob. (Default: false)
         *
         * @return bool True on success, otherwise false.
         */
        public static function start_cronjob( $restart = false ) {
            if ( ! $restart && wp_next_scheduled( GAOO_CRONJOB ) ) {
                return true;
            }

            wp_unschedule_hook( GAOO_CRONJOB );

            return false !== wp_schedule_event( strtotime( date( "Y-m-d" ) . ' 00:05:00' ), self::get_status_check_intervall( false ), GAOO_CRONJOB );
        }

        /**
         * Delete the checklist cache
         *
         * @return bool True on success, otherwise false
         */
        public static function delete_todo_cache() {
            return delete_transient( GAOO_PREFIX . 'has_todos' );
        }

        /**
         * Check if there are open todos.
         *
         * @return bool True if open todos available, otherwise false.
         */
        public static function has_todos() {
            $transient_name = GAOO_PREFIX . 'has_todos';
            $has_todos      = get_transient( $transient_name );

            if ( false !== $has_todos ) {
                return boolval( $has_todos );
            }

            $data  = self::get_options();
            $todos = self::check_todos( $data );

            if ( empty( $todos ) ) {
                return;
            }

            $has_todos = (int) ( $todos[ 'sum' ] - $todos[ 'dontknow' ] ) == $todos[ 'check' ];

            self::delete_todo_cache();
            set_transient( $transient_name, $has_todos, self::get_status_check_intervall( true ) * DAY_IN_SECONDS );

            return boolval( $has_todos );
        }

        /**
         * Get the configurated status check intervall in days.
         *
         * @param bool $in_days Return in days or as string. (Default: false)
         *
         * @return int|string Intervall in days or as a string.
         */
        public static function get_status_check_intervall( $in_days = false ) {
            $intervall = GAOO_Utils::get_option( 'status_intervall', 'weekly' );

            switch ( $intervall ) {
                case 'daily':
                    return $in_days ? 1 : $intervall;

                case 'weekly':
                default:
                    return $in_days ? 7 : $intervall;

                case 'monthly':
                    return $in_days ? 30 : $intervall;
            }
        }
    }