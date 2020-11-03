<?php

    // If this file is called directly, abort.
    defined( 'WPINC' ) || die;

    class GAOO_Admin {
        private $messages;
        private $csstidy;
        private $plugin;
        private $promotion;

        /**
         * GAOO_Admin constructor.
         */
        public function __construct() {
            add_action( 'admin_menu', array( $this, 'menu' ) );
            add_action( 'admin_notices', array( $this, 'admin_notices' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_action( 'save_post_page', array( $this, 'save_page' ), 10, 1 );
            add_action( 'gaoo_cronjob', array( $this, 'send_status_mail' ) );

            add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
            add_action( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

            $this->plugin    = GAOO_PLUGIN_NAME . '/ga-opt-out.php';
            $this->messages  = GAOO_Messages::getInstance();
            $this->csstidy   = new csstidy();
            $this->promotion = array(
                'link_txt' => esc_html__( 'AUTOMATICALLY UPDATE PRIVACY POLICY', 'ga-opt-out' ),
                'link'     => 'https://schweizer.to/datenschutzgenerator',
                'img'      => 'https://schweizersolutions.com/wordpress.org/easyrechtssicher.png',
            );

            $this->add_tinymce_button();
        }

        /**
         * Clear the checklist cache after the page saved.
         *
         * @param int $post_id ID of the post
         */
        public function save_page( $post_id ) {
            // run only if this page is configured for the monitoring
            if ( $post_id != GAOO_Utils::get_option( 'privacy_page_id' ) || $post_id != GAOO_Utils::get_option( 'wp_privacy_page' ) ) {
                return;
            }

            GAOO_Utils::delete_todo_cache();
        }

        /**
         * Load TinyMCE filters and hooks
         */
        public function add_tinymce_button() {
            global $post;

            $post_id = ! empty( $post ) ? $post->ID : filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

            // Check if it is the WP Privacy page and user has access to it
            if ( ! current_user_can( 'edit_pages' ) || empty( $post_id ) || $post_id != get_option( 'wp_page_for_privacy_policy', 0 ) || get_user_option( 'rich_editing' ) != 'true' ) {
                return;
            }

            add_filter( "mce_external_plugins", array( $this, 'add_tinymce_plugin' ) );
            add_filter( 'mce_buttons', array( $this, 'register_tinymce_button' ) );
        }

        /**
         * Add this plugin to TinyMCE.
         *
         * @param array $plugins List with plugin URLs
         *
         * @return array List with plugin URLs
         */
        public function add_tinymce_plugin( $plugins ) {
            $plugins[ 'gaoptout' ] = GAOO_PLUGIN_URL . '/assets/tinymce.js';

            return $plugins;
        }

        /**
         * Add buttons to TinyMCE.
         *
         * @param array $buttons List of buttons
         *
         * @return array List of buttons
         */
        public function register_tinymce_button( $buttons ) {
            array_push( $buttons, "gaoptout" );

            return $buttons;
        }

        /**
         * Add menu items to the admin menu
         */
        public function menu() {
            add_options_page( esc_html__( 'Google Analytics Opt-Out', 'ga-opt-out' ), 'GA Opt-Out', GAOO_CAPABILITY, 'gaoo', array( $this, 'menu_settings' ) );
        }

        /**
         * Customize the action links.
         *
         * @param array  $links Actions links.
         * @param string $file  Filename of the activated plugin.
         *
         * @return array Action links.
         */
        public function plugin_action_links( $links, $file ) {
            if ( $file == $this->plugin ) {
                $links[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=gaoo' ) ) . '">' . esc_html__( 'Settings' ) . '</a>';
                $links[] = '<strong><a class="gaoo-plugin-action" target="_blank" href="' . esc_url( $this->promotion[ 'link' ] ) . '">' . $this->promotion[ 'link_txt' ] . '</a></strong>';
            }

            return $links;
        }

        /**
         * Plugin row meta links
         *
         * @since 1.1
         *
         * @param array  $links already defined meta links.
         * @param string $file  plugin file path and name being processed.
         *
         * @return array $input
         */
        function plugin_row_meta( $links, $file ) {
            if ( $file == $this->plugin ) {
                $links[] = "<a href='https://wordpress.org/support/plugin/opt-out-for-google-analytics/reviews/?rate=5#new-post' target='_blank'>" . esc_html__( 'Rate:', 'ga-opt-out' ) . "
				<i class='gaoo-rate-stars'>"
                    . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
                    . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
                    . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
                    . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
                    . "<svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>"
                    . "</i></a>";

                if ( ! empty( $this->promotion ) && ! empty( $this->promotion[ 'link_txt' ] ) ) {
                    $links[] = '<strong><a target="_blank" href="' . esc_url( $this->promotion[ 'link' ] ) . '">' . $this->promotion[ 'link_txt' ] . '</a></strong>';
                }
            }

            return $links;
        }

        /**
         * Enqueue scripts and styles for the admin pages.
         */
        public function enqueue_scripts() {
            wp_enqueue_style( 'gaoo-admin-styles', GAOO_PLUGIN_URL . '/assets/admin.css' );

            // Run only if functionallity on the right pages
            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'gaoo' ) {
                wp_enqueue_script( 'gaoo-admin-script', GAOO_PLUGIN_URL . '/assets/admin.js', array( 'jquery' ), false, true );

                wp_localize_script( 'gaoo-admin-script', 'gaoo', array(
                    'edit_link' => esc_url( admin_url( 'post.php?action=edit&post=%d' ) ),
                    'text'      => array(
                        'copied'    => esc_html__( 'Copied!', 'ga-opt-out' ),
                        'notcopied' => esc_html__( "Couldn't copy!", 'ga-opt-out' ),
                    ),
                ) );
            }

            // Load the Gutenberg block
            $current_screen = get_current_screen();

            if ( ! empty( $current_screen ) && method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
                wp_enqueue_script( 'gaoo-block', GAOO_PLUGIN_URL . '/assets/optout-block.js', array( 'wp-blocks', 'wp-editor' ), true );
            }
        }

        /**
         * Add admin notices, if function is enabled and no UA code is configured.
         */
        public function admin_notices() {
            if ( current_user_can( GAOO_CAPABILITY ) ) {
                global $current_user;

                if ( isset( $_GET[ 'gaoo_promo' ] ) ) {
                    update_user_meta( $current_user->ID, GAOO_PREFIX . 'promotion_off', 1 );
                }

                $is_promotion_off = ! empty( get_user_meta( $current_user->ID, GAOO_PREFIX . 'promotion_off', true ) );

                if ( ! $is_promotion_off ) {
                    $this->render_promotion_box();
                }
            }

            if ( ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'gaoo' ) && $this->is_cronjob_disabled() ) {
                echo '<div class="notice update-nag is-dismissable"><p>' . esc_html__( 'It seems like you have disabled the cronjob through your wp-config.php ... Please make sure that you have installed a server-side cronjob, otherwise you wont receive the status reports via mail.', 'ga-opt-out' ) . '</p></div>';
            }

            // Run only if open todos available.
            if ( ! current_user_can( GAOO_CAPABILITY ) || GAOO_Utils::get_option( 'disable_monitoring', false ) || ! GAOO_Utils::has_todos() || ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'gaoo' ) ) {
                return;
            }

            echo '<div class="notice notice-error is-dismissable"><p>' . sprintf( __( 'Google Analytics does not appear to function in compliance with data protection regulations. Please check the settings <a href="%s">here</a>.', 'ga-opt-out' ), esc_url( admin_url( 'options-general.php?page=gaoo' ) ) ) . '</p></div>';
        }

        /**
         * Show the settings page.
         */
        public function menu_settings() {
            // Handle form save
            $form_data = isset( $_POST[ 'gaoo' ] ) ? $this->save_menu_settings() : GAOO_Utils::get_options();

            $ga_plugins = array(
                'monsterinsights' => array(
                    'label'        => 'Google Analytics for WordPress by MonsterInsights',
                    'url_install'  => admin_url( 'plugin-install.php?tab=search&s=Google+Analytics+MonsterInsights' ),
                    'url_activate' => admin_url( 'plugins.php?plugin_status=inactive&s=MonsterInsights' ),
                    'is_active'    => ( is_plugin_active( 'google-analytics-premium/googleanalytics-premium.php' ) || is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ),
                    'is_installed' => ( file_exists( WP_PLUGIN_DIR . '/google-analytics-premium/googleanalytics-premium.php' ) || file_exists( WP_PLUGIN_DIR . '/google-analytics-for-wordpress/googleanalytics.php' ) ),
                ),
                'gadash'          => array(
                    'label'        => 'Google Analytics Dashboard for WP by ExactMetrics (formerly GADWP)',
                    'url_install'  => admin_url( 'plugin-install.php?tab=search&s=google+analytics+dashboard+for+wp' ),
                    'url_activate' => admin_url( 'plugins.php?plugin_status=inactive&s=GADWP' ),
                    'is_active'    => is_plugin_active( 'google-analytics-dashboard-for-wp/gadwp.php' ),
                    'is_installed' => file_exists( WP_PLUGIN_DIR . '/google-analytics-dashboard-for-wp/gadwp.php' ),
                ),
                'analytify'       => array(
                    'label'        => 'Google Analytics Dashboard Plugin for WordPress by Analytify',
                    'url_install'  => admin_url( 'plugin-install.php?tab=search&s=Analytify' ),
                    'url_activate' => admin_url( 'plugins.php?plugin_status=inactive&s=Analytify' ),
                    'target'       => '_blank',
                    'is_active'    => is_plugin_active( 'wp-analytify/wp-analytify.php' ),
                    'is_installed' => file_exists( WP_PLUGIN_DIR . '/wp-analytify/wp-analytify.php' ),
                ),
                'gaga'            => array(
                    'label'        => 'GA Google Analytics',
                    'url_install'  => admin_url( 'plugin-install.php?tab=search&s=GA+Google+Analytics' ),
                    'url_activate' => admin_url( 'plugins.php?plugin_status=inactive&s=GA+Google' ),
                    'target'       => '_blank',
                    'is_active'    => is_plugin_active( 'ga-google-analytics/ga-google-analytics.php' ),
                    'is_installed' => file_exists( WP_PLUGIN_DIR . '/ga-google-analytics/ga-google-analytics.php' ),
                ),
                'sitekit'         => array(
                    'label'        => 'Site Kit by Google',
                    'url_install'  => admin_url( 'plugin-install.php?tab=search&s=Google+site+kit' ),
                    'url_activate' => admin_url( 'plugins.php?plugin_status=inactive&s=site+kit' ),
                    'target'       => '_blank',
                    'is_active'    => is_plugin_active( 'google-site-kit/google-site-kit.php' ),
                    'is_installed' => file_exists( WP_PLUGIN_DIR . '/google-site-kit/google-site-kit.php' ),
                ),
            );

            extract( $form_data );

            // if plugin set as current but not installed or activated, remove it from settings
            if ( ! empty( $ga_plugin ) && isset( $ga_plugins[ $ga_plugin ] ) && ( ( isset( $ga_plugins[ $ga_plugin ][ 'is_active' ] ) && ! $ga_plugins[ $ga_plugin ][ 'is_active' ] ) || isset( $ga_plugins[ $ga_plugin ][ 'is_installed' ] ) && ! $ga_plugins[ $ga_plugin ][ 'is_installed' ] ) ) {
                delete_option( GAOO_PREFIX . 'ga_plugin' );
                $ga_plugin = null;
            }

            $checklist          = GAOO_Utils::check_todos( $form_data, true );
            $wp_privacy_page_id = get_option( 'wp_page_for_privacy_policy', 0 );
            $wp_admin_mail      = get_option( 'admin_email', null );
            $promotion          = $this->promotion;

            include_once GAOO_PLUGIN_DIR . '/templates/settings.php';
        }

        public function render_promotion_box() {
            $promotion = $this->promotion;

            if ( empty( $promotion ) || empty( $promotion[ 'link' ] ) ) {
                return;
            }

            include_once GAOO_PLUGIN_DIR . '/templates/promotion.php';
        }

        /**
         * Handle the submited form data.
         *
         * @return array Stored options with the value.
         */
        private function save_menu_settings() {

            // Check if form submited by the right site
            if ( ! isset( $_REQUEST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'gaoo-settings' ) ) {
                $this->messages->addError( esc_html__( 'Security check fail!', 'ga-opt-out' ) );

                return array();
            }

            // Validate inputs
            $skip_save = array();
            $data      = filter_var_array( $_POST[ 'gaoo' ], array(
                'ga_plugin'          => FILTER_SANITIZE_STRING,
                'link_deactivate'    => FILTER_SANITIZE_STRING,
                'link_activate'      => FILTER_SANITIZE_STRING,
                'ua_code'            => FILTER_SANITIZE_STRING,
                'popup_deactivate'   => FILTER_SANITIZE_STRING,
                'popup_activate'     => FILTER_SANITIZE_STRING,
                'status'             => FILTER_SANITIZE_STRING,
                'privacy_page_id'    => FILTER_SANITIZE_STRING,
                'disable_monitoring' => FILTER_SANITIZE_NUMBER_INT,
                'force_reload'       => FILTER_SANITIZE_NUMBER_INT,
                'wp_privacy_page'    => FILTER_SANITIZE_NUMBER_INT,
                'custom_css'         => FILTER_UNSAFE_RAW,
                'tracking_code'      => FILTER_UNSAFE_RAW,
                'status_intervall'   => FILTER_SANITIZE_STRING,
                'status_mails'       => FILTER_SANITIZE_STRING,
                'status_mails_sync'  => FILTER_SANITIZE_NUMBER_INT,
            ), false );

            // Validate UA code if entered manually, otherwise empty field.
            if ( $data[ 'ga_plugin' ] == 'manual' && ! empty( $data[ 'ua_code' ] ) ) {
                $data[ 'ua_code' ] = GAOO_Utils::validate_ua_code( $data[ 'ua_code' ] );

                if ( empty( $data[ 'ua_code' ] ) ) {
                    $this->messages->addError( esc_html__( "Please enter a valid UA Code. Format: UA-XXXXXX-Y", 'ga-opt-out' ) );
                }
            }
            else {
                $data[ 'ua_code' ] = '';
            }

            // Removes space from start and end of the text.
            $data[ 'link_activate' ]    = trim( $data[ 'link_activate' ] );
            $data[ 'link_deactivate' ]  = trim( $data[ 'link_deactivate' ] );
            $data[ 'popup_activate' ]   = trim( $data[ 'popup_activate' ] );
            $data[ 'popup_deactivate' ] = trim( $data[ 'popup_deactivate' ] );
            $data[ 'tracking_code' ]    = trim( $data[ 'tracking_code' ] );

            // Check and validate the custom css code
            if ( ! empty( $data[ 'custom_css' ] ) ) {
                if ( ! $this->csstidy->parse( stripslashes( $data[ 'custom_css' ] ) ) ) {
                    $skip_save[] = 'custom_css';
                    $this->messages->addWarning( esc_html__( "Your custom css code was not stored, because it is not valid! We stoll hold the old version of it, please check below the code and save again.", 'ga-opt-out' ) );
                }
                else {
                    $data[ 'custom_css' ] = $this->csstidy->print->plain();
                }
            }

            // Store only validated mails into database
            if ( ! empty( $data[ 'status_mails' ] ) ) {
                $mails = explode( ',', $data[ 'status_mails' ] );
                $mails = array_filter( array_map( 'trim', $mails ), 'is_email' );

                $data[ 'status_mails' ] = empty( $mails ) ? '' : implode( ',', $mails );
            }

            if ( empty( $data[ 'link_deactivate' ] ) ) {
                $this->messages->addError( esc_html__( "Please enter the text for the deactivate link, otherwise link doesn't appears!", 'ga-opt-out' ) );
            }

            if ( empty( $data[ 'link_activate' ] ) ) {
                $this->messages->addError( esc_html__( "Please enter the text for the activate link, otherwise link doesn't appears!", 'ga-opt-out' ) );
            }

            if ( $this->messages->hasError() ) {
                return $data;
            }

            // If an option is not set, fill it with the default value.
            $data                 = array_merge( GAOO_Utils::get_options_list(), $data );
            $old_status_intervall = GAOO_Utils::get_option( 'status_intervall' );

            // Sync. with WP Privacy page
            if ( ! empty( $data[ 'wp_privacy_page' ] ) ) {
                $data[ 'privacy_page_id' ] = get_option( 'wp_page_for_privacy_policy', 0 );
            }

            foreach ( $data as $k => $v ) {
                if ( ! in_array( $k, $skip_save, true ) ) {
                    update_option( GAOO_PREFIX . $k, $v );
                }
            }

            // Show warniung if cronjob is disabled
            if ( $this->is_cronjob_disabled() ) {
                $this->messages->addWarning( esc_html__( 'It seems like you have disabled the cronjob through your wp-config.php ... Please make sure that you have installed a server-side cronjob, otherwise you wont receive the status reports via mail.', 'ga-opt-out' ) );
            }

            // Restart cronjob etc. if intervall is changed
            if ( $old_status_intervall != $data[ 'status_intervall' ] ) {
                GAOO_Utils::delete_todo_cache();
                GAOO_Utils::start_cronjob( true );
            }

            $this->messages->addSuccess( esc_html__( 'Settings saved.', 'ga-opt-out' ) );

            return $data;
        }

        /**
         * Send a mail with the status check report.
         */
        public function send_status_mail() {
            // Delete todo cache if cronjob is triggered
            GAOO_Utils::delete_todo_cache();

            $data = GAOO_Utils::get_options();

            if ( empty( $data[ 'status_mails' ] ) ) {
                return;
            }

            $checklist = GAOO_Utils::check_todos( $data, true );
            $checked   = array_unique( array_filter( array_column( $checklist, 'checked' ), 'is_bool' ) );

            if ( count( $checked ) == 1 && array_pop( $checked ) == true ) {
                return;
            }

            $mail_to = explode( ',', $data[ 'status_mails' ] );
            $mail_to = array_filter( array_map( 'trim', $mail_to ), 'is_email' );

            if ( empty( $mail_to ) ) {
                return;
            }

            $mail_subject = '[ ' . esc_html( get_bloginfo( 'name' ) ) . ' ] ' . esc_html__( 'Google Analytics Opt-Out - Status check failed!', 'ga-opt-out' );
            $mail_body    = esc_html__( 'Here is your status check report:', 'ga-opt-out' ) . PHP_EOL . PHP_EOL;

            foreach ( $checklist as $checkpoint ) {
                $checked = $checkpoint[ 'checked' ];

                if ( $checked === false ) {
                    $checked = '✕';
                }
                elseif ( $checked === true ) {
                    $checked = '✔';
                }
                else {
                    $checked = '?';
                }

                $mail_body .= "[ {$checked} ] " . esc_html( $checkpoint[ 'label' ] ) . PHP_EOL;
            }

            $mail_body .= PHP_EOL . sprintf( esc_html__( 'Check the settings: %s', 'ga-opt-out' ), esc_url( admin_url( 'options-general.php?page=gaoo' ) ) );

            wp_mail( $mail_to, $mail_subject, $mail_body );
        }

        /**
         * Check if cronjob is disabled, if status mails should be send.
         *
         * @return bool True if disabled, otherwise false.
         */
        private function is_cronjob_disabled() {
            return defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON && ! empty( GAOO_Utils::get_option( 'status_mails', false ) );
        }


    }