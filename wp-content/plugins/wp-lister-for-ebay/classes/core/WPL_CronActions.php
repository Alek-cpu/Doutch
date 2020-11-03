<?php
/**
 * WPL_CronActions
 *
 * This class contains action hooks that are usually trigger via wp_cron()
 *
 */

class WPL_CronActions extends WPL_Core {

	var $lockfile;

	public function __construct() {
		parent::__construct();

		// add cron handler
		add_action('wplister_update_auctions', 						array( &$this, 'cron_update_auctions' ) );
		add_action('wple_daily_schedule', 	   						array( &$this, 'cron_daily_schedule' ) );

		// add internal action hooks
		add_action('wple_clean_log_table', 							array( &$this, 'action_clean_log_table' ) );
		add_action('wple_clean_tables', 							array( &$this, 'action_clean_tables' ) );
		add_action('wple_clean_listing_archive', 					array( &$this, 'action_clean_listing_archive' ) );

		// add custom cron schedules
		add_filter( 'cron_schedules', 								array( &$this, 'cron_add_custom_schedules' ) );

 		// handle external cron calls
		add_action('wp_ajax_wplister_run_scheduled_tasks', 			array( &$this, 'cron_update_auctions' ) ); // wplister_run_scheduled_tasks
		add_action('wp_ajax_nopriv_wplister_run_scheduled_tasks', 	array( &$this, 'cron_update_auctions' ) );
		add_action('wp_ajax_wple_run_scheduled_tasks', 				array( &$this, 'cron_update_auctions' ) ); // wple_run_scheduled_tasks
		add_action('wp_ajax_nopriv_wple_run_scheduled_tasks', 		array( &$this, 'cron_update_auctions' ) );

		// background action-scheduler actions
        add_action( 'wple_do_background_revise_items',              'wple_do_background_revise_items', 10, 4 ); // this could go into another class...

        // Background Inventory Check
        add_action( 'admin_init', array( $this, 'set_inventory_check_cron_schedule' ) );
        add_action( 'wple_bg_inventory_check', array( $this, 'cron_bg_inventory_check' ) );
        add_action( 'wple_bg_inventory_check_run_tasks', array( $this, 'cron_bg_inventory_run_tasks' ) );

        add_action( 'wple_bg_inventory_check_get_listings', array( $this, 'cron_bg_inventory_check_get_listings' ), 10 );
        add_action( 'wple_bg_inventory_check_task_runner', array( $this, 'cron_bg_inventory_check_run_tasks' ), 10 );

	}


	// update auctions - called by wp_cron if activated
	public function cron_update_auctions() {
        WPLE()->logger->info("*** WP-CRON: cron_update_auctions()");

        // log cron run to db
		if ( get_option('wplister_log_to_db') == '1' ) {
            $dblogger = new WPL_EbatNs_Logger();
	        $dblogger->updateLog( array(
				'callname'    => 'cron_job_triggered',
				'request_url' => 'internal action hook',
				'request'     => maybe_serialize( $_REQUEST ),
				'response'    => 'last run: '.human_time_diff( get_option('wplister_cron_last_run') ).' ago',
				'success'     => 'Success'
	        ));
		}

        // check if this is a staging site
        if ( $this->isStagingSite() ) {
	        WPLE()->logger->info("WP-CRON: staging site detected! terminating execution...");
			self::updateOption( 'cron_auctions', '' );
			self::updateOption( 'create_orders', '' );
        	return;
        }

        // check if update is already running
        if ( ! $this->checkLock() ) {
	        WPLE()->logger->error("WP-CRON: already running! terminating execution...");
        	return;
        }

        // get accounts
		$accounts = WPLE_eBayAccount::getAll( false, true ); // sort by id
		if ( ! empty( $accounts) ) {

			// loop each active account
			$processed_accounts = array();
			foreach ( $accounts as $account ) {

				// make sure we don't process the same account twice
				if ( in_array( $account->user_name, $processed_accounts ) ) {
			        WPLE()->logger->info("skipping account {$account->id} - user name {$account->user_name} was already processed");
					continue;
				}

				$this->initEC( $account->id );
				$this->EC->updateEbayOrders();
				$this->EC->updateListings(); // TODO: specify account
				$this->EC->updateEbayMessages();
				$this->EC->closeEbay();
				$processed_accounts[] = $account->user_name;

			}

		// } else {

		// 	// fallback to pre 1.5.2 behaviour
		// 	$this->initEC();
		// 	$this->EC->updateEbayOrders();

		// 	// update ended items and process relist schedule
		// 	$this->EC->updateListings();
		// 	$this->EC->closeEbay();

		}

		// Check for changed listings to revise in the background
        ListingsModel::queueChangedListings();

		// check daily schedule - trigger now if not executed within 36 hours
        $last_run = get_option('wple_daily_cron_last_run');
        if ( $last_run < time() - 36 * 3600 ) {
	        WPLE()->logger->warn('*** WP-CRON: Daily schedule has NOT run since '.human_time_diff( $last_run ).' ago');
			do_action( 'wple_daily_schedule' );
        }


		// clean up
		$this->removeLock();

		// store timestamp
		self::updateOption( 'cron_last_run', time() );

        WPLE()->logger->info("*** WP-CRON: cron_update_auctions() finished");
	} // cron_update_auctions()


	// run daily schedule - called by wp_cron
	public function cron_daily_schedule() {
        WPLE()->logger->info("*** WP-CRON: cron_daily_schedule()");
        $manually = isset($_REQUEST['action']) && $_REQUEST['action'] == 'wple_run_daily_schedule' ? true : false;

		// clean log table
		do_action('wple_clean_log_table');
		do_action('wple_clean_tables');

		// clean archive
		do_action('wple_clean_listing_archive');

		// store timestamp
		update_option( 'wple_daily_cron_last_run', time() );

        WPLE()->logger->info("*** WP-CRON: cron_daily_schedule() finished");
        if ( $manually ) wple_show_message('Daily maintenance schedule was executed successfully.');
	}

	public function action_clean_log_table() {
		global $wpdb;
		// if ( get_option('wplister_log_to_db') == '1' ) {
		if ( $days_to_keep = get_option( 'wplister_log_days_limit', 30 ) ) {
			$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'ebay_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL '.intval($days_to_keep).' DAY )');
			WPLE()->logger->info('Cleaned table ebay_log - affected rows: ' . $rows);

			$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'ebay_jobs WHERE date_created < DATE_SUB(NOW(), INTERVAL '.intval($days_to_keep).' DAY )');
			WPLE()->logger->info('Cleaned table ebay_jobs - affected rows: ' . $rows);

            $rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'ebay_stocks_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL '.intval($days_to_keep).' DAY )');
            WPLE()->logger->info('Cleaned table ebay_stocks_log - affected rows: ' . $rows);
		}
	} // action_clean_log_table()

	public function action_clean_tables() {
		global $wpdb;

		// clean orders table (date_created)
		$days_to_keep = get_option( 'wplister_orders_days_limit', '' );
		if ( $days_to_keep ) {
			$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'ebay_orders WHERE date_created < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
			WPLE()->logger->info('Cleaned table ebay_orders - affected rows: ' . $rows);

			$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'ebay_transactions WHERE date_created < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
			WPLE()->logger->info('Cleaned table ebay_transactions - affected rows: ' . $rows);
		}

	} // action_clean_tables()

	public function action_clean_listing_archive() {
		global $wpdb;
		if ( $days_to_keep = get_option( 'wplister_archive_days_limit', 90 ) ) {
		    // Don't clear out GTC listings at least until we know what's happening in #32429
		    // log IDs before deleting them
            $items = $wpdb->get_results("SELECT id, ebay_id, end_date FROM {$wpdb->prefix}ebay_auctions WHERE status = 'archived' AND listing_duration <> 'GTC' AND end_date < DATE_SUB(NOW(), INTERVAL ". intval($days_to_keep) ." DAY )");

			$rows = $wpdb->query('DELETE FROM '.$wpdb->prefix.'ebay_auctions WHERE status = "archived" AND listing_duration <> "GTC" AND end_date < DATE_SUB(NOW(), INTERVAL '.intval($days_to_keep).' DAY )');
			WPLE()->logger->info('Cleaned table ebay_auctions - affected rows: ' . $rows);
			WPLE()->logger->info( print_r( $items, 1 ) );
		}
	} // action_clean_listing_archive()


	public function checkLock() {

		// get full path to lockfile
		$uploads        = wp_upload_dir();
		$lockfile       = $uploads['basedir'] . '/' . 'wplister_sync.lock';
		$this->lockfile = $lockfile;

		// skip locking if lockfile is not writeable
		if ( ! is_writable( $lockfile ) && ! is_writable( dirname( $lockfile ) ) ) {
	        WPLE()->logger->error("lockfile not writable: ".$lockfile);
	        return true;
		}

		// create lockfile if it doesn't exist
		if ( ! file_exists( $lockfile ) ) {
			$ts = time();
			file_put_contents( $lockfile, $ts );
	        WPLE()->logger->info("lockfile created at TS $ts: ".$lockfile);
	        return true;
		}

		// lockfile exists - check TS
		$ts = (int) file_get_contents($lockfile);

		// check if TS is outdated (after 10min.)
		if ( $ts < ( time() - 600 ) ) {
	        WPLE()->logger->info("stale lockfile found for TS ".$ts.' - '.human_time_diff( $ts ).' ago' );

	        // update lockfile
			$ts = time();
			file_put_contents( $lockfile, $ts );

	        WPLE()->logger->info("lockfile updated for TS $ts: ".$lockfile);
	        return true;
		} else {
			// process is still alive - can not run twice
	        WPLE()->logger->info("SKIP CRON - sync already running with TS ".$ts.' - '.human_time_diff( $ts ).' ago' );
			return false;
		}

		return true;
	} // checkLock()

	public function removeLock() {
		if ( file_exists( $this->lockfile ) ) {
			unlink( $this->lockfile );
	        WPLE()->logger->info("lockfile was removed: ".$this->lockfile);
		}
	}

	public function cron_add_custom_schedules( $schedules ) {
		$schedules['five_min'] = array(
			'interval' => 60 * 5,
			'display' => 'Once every five minutes'
		);
		$schedules['ten_min'] = array(
			'interval' => 60 * 10,
			'display' => 'Once every ten minutes'
		);
		$schedules['fifteen_min'] = array(
			'interval' => 60 * 15,
			'display' => 'Once every fifteen minutes'
		);
		$schedules['thirty_min'] = array(
			'interval' => 60 * 30,
			'display' => 'Once every thirty minutes'
		);
		return $schedules;
	}

    public function set_inventory_check_cron_schedule() {
	    // Make sure WooCommerce is installed/activated before proceeding #41102
        if ( !function_exists( 'as_next_scheduled_action' ) ) {
            return;
        }

        if ( get_option( 'wple_run_background_inventory_check', 1) ) {
            // Turn it on
            if ( ! as_next_scheduled_action( 'wple_bg_inventory_check' ) ) {
                $frequency = get_option( 'wple_inventory_check_frequency', 24 );
                as_schedule_recurring_action( time(), $frequency * 3600, 'wple_bg_inventory_check' );
            }
        } else {
            if ( as_next_scheduled_action( 'wple_bg_inventory_check' ) ) {
                as_unschedule_all_actions( 'wple_bg_inventory_check' );
            }
        }
    }

    /**
     * Check product inventory in the background
     *
     * This initiates the background inventory check by pulling new reports for all active accounts. Once the reports
     * are ready, they are then processed by WPLE_InventoryCheck::checkInventorySync() which takes care of the notification
     * if inconsistencies are found.
     */
    public function cron_bg_inventory_check() {
        WPLE()->logger->info( 'cron_bg_inventory_check invoked' );

        $ic = new WPL_BackgroundInventoryCheck();
        $ic->buildTaskList();
    }

    public function cron_bg_inventory_run_tasks() {
        WPLE()->logger->info( 'cron_bg_inventory_run_tasks invoked' );

        $ic = new WPL_BackgroundInventoryCheck();
        $ic->runTasks();
    }

    public function cron_bg_inventory_check_get_listings( ...$accounts ) {
        WPLE()->logger->info( 'cron_bg_inventory_check_get_listings' );
        WPLE()->logger->debug( 'accounts: '. print_r( $accounts, 1 ) );

        $account = array_shift( $accounts );

        WPLE()->logger->info( 'Processing account #'. $account );

        $ic = new WPL_BackgroundInventoryCheck();
        $ic->getSellerListings( $account );

        if ( empty( $accounts ) ) {
            // no more accounts left to process - send the report email if inconsistencies are found
            $ic->sendSyncNotificationEmail();
            $ic->reset();
        } else {
            // schedule another cron run for the remaining accounts
            WPLE()->logger->info( 'Scheduling wple_bg_inventory_check_get_listings for the remaining accounts: '. print_r( $accounts, 1 ) );
            as_schedule_single_action( time(), 'wple_bg_inventory_check_get_listings', $accounts );
        }
    }

    public function cron_bg_inventory_check_run_tasks() {
        WPLE()->logger->info( 'cron_bg_inventory_check_run_tasks invoked' );

        $ic = new WPL_BackgroundInventoryCheck();
        $ic->runTasks();
    }

} // class WPL_CronActions

$WPL_CronActions = new WPL_CronActions();
