<?php
    /*
     * Plugin Name: Google Analytics Opt-Out (DSGVO / GDPR)
     * Plugin URI: https://schweizersolutions.com
     * Description: Adds the possibility for the user to opt out from Google Analytics. The user will not be tracked by Google Analytics on this site until he allows it again, clears his cookies or uses a different browser.
     * Version: 1.5
     * Author: Schweizer Solutions GmbH
     * Author URI: https://schweizersolutions.com
     * License: GPL-2.0+
     * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
     * Text Domain: ga-opt-out
     * Domain Path: /languages
    */

    // If this file is called directly, abort.
    defined( 'WPINC' ) || die;

    // Define global paths
    defined( 'GAOO_PLUGIN_NAME' ) || define( 'GAOO_PLUGIN_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
    defined( 'GAOO_PLUGIN_DIR' ) || define( 'GAOO_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . GAOO_PLUGIN_NAME );
    defined( 'GAOO_PLUGIN_URL' ) || define( 'GAOO_PLUGIN_URL', WP_PLUGIN_URL . '/' . GAOO_PLUGIN_NAME );
    defined( 'GAOO_PREFIX' ) || define( 'GAOO_PREFIX', '_gaoo_' );
    defined( 'GAOO_LOCALE' ) || define( 'GAOO_LOCALE', apply_filters( 'plugin_locale', get_user_locale(), 'ga-opt-out' ) );
    defined( 'GAOO_SHORTCODE' ) || define( 'GAOO_SHORTCODE', '[ga_optout]' );
    defined( 'GAOO_CAPABILITY' ) || define( 'GAOO_CAPABILITY', 'manage_options' );
    defined( 'GAOO_CRONJOB' ) || define( 'GAOO_CRONJOB', 'gaoo_cronjob' );

    require_once GAOO_PLUGIN_DIR . '/inc/utils.class.php';
    include_once GAOO_PLUGIN_DIR . '/inc/activator.class.php';
    include_once GAOO_PLUGIN_DIR . '/inc/deactivator.class.php';

    // Add custom schedules for the cronjob
    add_filter( 'cron_schedules', array( new GAOO_Utils(), 'add_cron_schedules' ), 9999 );

    register_activation_hook( __FILE__, array( 'GAOO_Activator', 'init' ) );
    register_deactivation_hook( __FILE__, array( 'GAOO_Deactivator', 'init' ) );

    Class GAOO {
        /**
         * Handling the start of the plugin
         */
        public function init() {
            $this->load_dependencies();
            $this->run();
        }

        /**
         * Runs initialisation of the plugin
         */
        public function run() {
            // Load translations
            load_textdomain( 'ga-opt-out', WP_LANG_DIR . '/ga-opt-out-' . GAOO_LOCALE . '.mo' );
            load_plugin_textdomain( 'ga-opt-out', false, GAOO_PLUGIN_NAME . '/languages' );

            // Starts Classes
            new GAOO_Admin();
            new GAOO_Public();

            // Load activator for MU support
            add_action( 'wpmu_new_blog', array( new GAOO_Activator, 'new_blog' ) );
        }

        /**
         * Load all classes.
         */
        public function load_dependencies() {
            require_once GAOO_PLUGIN_DIR . '/inc/singleton.class.php';
            require_once GAOO_PLUGIN_DIR . '/inc/messages.class.php';

            require_once GAOO_PLUGIN_DIR . '/lib/csstidy/class.csstidy.php';

            include_once GAOO_PLUGIN_DIR . '/inc/admin.class.php';
            include_once GAOO_PLUGIN_DIR . '/inc/public.class.php';
        }

        /**
         * Redirect to setting page, if plugin got activated.
         *
         * @param string $plugin Activated plugin
         */
        public function activated_plugin( $plugin ) {
            if ( isset( $_REQUEST[ 'action' ] ) && $_REQUEST[ 'action' ] == 'activate' && $plugin == plugin_basename( __FILE__ ) ) {
                exit( wp_redirect( esc_url( admin_url( 'options-general.php?page=gaoo' ) ) ) );
            }
        }
    }

    // Start the plugin.
    $gaoo = new GAOO();

    add_action( 'init', array( $gaoo, 'init' ) );
    add_action( 'activated_plugin', array( $gaoo, 'activated_plugin' ) );

    function gaoo_log( $data, $id = 0 ) {
        $file   = GAOO_PLUGIN_DIR . "/log.txt";
        $string = '##### ' . $id . ' - ' . date( 'd.m.Y H:i:s' ) . ' ####' . PHP_EOL . var_export( $data, true ) . PHP_EOL . PHP_EOL;

        return ( file_put_contents( $file, $string, FILE_APPEND ) !== false );
    }