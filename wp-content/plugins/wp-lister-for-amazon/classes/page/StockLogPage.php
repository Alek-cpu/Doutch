<?php
/**
 * WPLA_StockLogPage class
 * 
 */

class WPLA_StockLogPage extends WPLA_Page {

	const slug = 'tools';

	public function onWpInit() {

		// Add custom screen options
		$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".self::slug;
		add_action( $load_action, array( &$this, 'addScreenOptions' ) );

	}

    public function handleSubmit() {
        if ( ! current_user_can('manage_amazon_options') ) return;

        // handle delete action
        if ( $this->requestAction() == 'wpla_delete_stock_logs' ) {
            check_admin_referer( 'bulk-logs' );

            $log_ids = wpla_clean(@$_REQUEST['log']);
            if ( is_array($log_ids)) {
                foreach ($log_ids as $id) {
                    $this->deleteLogEntry( $id );
                }
                $this->showMessage( __( 'Selected items were removed.', 'wp-lister-for-amazon' ) );
            }
        }

        if ( $this->requestAction() == 'wpla_clear_amazon_stock_log' ) {
            check_admin_referer( 'wpla_clear_amazon_stock_log' );

            $this->clearLog();
            $this->showMessage( __( 'Stock log has been cleared.', 'wp-lister-for-amazon' ) );
        }
        if ( $this->requestAction() == 'wpla_optimize_amazon_stock_log' ) {
            check_admin_referer( 'wpla_optimize_amazon_stock_log' );
            $count = $this->optimizeLog();
            $this->showMessage( $count . ' ' . __( 'expired records have been removed and the database table has been optimized.', 'wp-lister-for-amazon' ) );
        }

    }

	function addScreenOptions() {
		if ( isset($_GET['tab']) && $_GET['tab'] != 'stock_log' ) return;
		if ( ! isset($_GET['tab']) ) return;
		
		if ( ( isset($_GET['action']) ) && ( $_GET['action'] == 'edit' ) ) {
			// on edit page render developers options
			add_screen_options_panel('wpla_developer_options', '', array( &$this, 'renderDeveloperOptions'), 'toplevel_page_wpla' );

		} else {

			// render table options
			$option = 'per_page';
			$args = array(
		    	'label' => 'Log entries',
		        'default' => 20,
		        'option' => 'logs_per_page'
		        );
			add_screen_option( $option, $args );
			$this->stocklogTable = new WPLA_StockLogTable();

		}

	}
	
	public function displayStockLogPage() {

	    // create table and fetch items to show
	    $this->stocklogTable = new WPLA_StockLogTable();
	    $this->stocklogTable->prepare_items();

		$active_tab  = 'stock_log';
	    $form_action = 'admin.php?page='.self::ParentMenuId.'-tools'.'&tab='.$active_tab;

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'listingsTable'				=> $this->stocklogTable,
			'default_account'			=> get_option( 'wpla_default_account_id' ),
			'tableSize'					=> $this->getTableSize(),

			'tools_url'				    => 'admin.php?page='.self::ParentMenuId.'-tools',
			'form_action'				=> $form_action
		);
		$this->display( 'tools_stocklog', $aData );
	}

	public function getTableSize() {
		global $wpdb;
		$dbname = $wpdb->dbname;
		$table  = $wpdb->prefix.'amazon_stock_log';

		// check if MySQL server has gone away and reconnect if required - WP 3.9+
		if ( method_exists( $wpdb, 'check_connection') ) $wpdb->check_connection();

		$sql = "
			SELECT round(((data_length + index_length) / 1024 / 1024), 1) AS 'size' 
			FROM information_schema.TABLES 
			WHERE table_schema = '$dbname'
			  AND table_name = '$table' ";
		// echo "<pre>";print_r($sql);echo"</pre>";#die();

		$size = $wpdb->get_var($sql);
		if ( $wpdb->last_error ) echo 'Error in getTableSize(): '.$wpdb->last_error;

		return $size;
	}

    public function clearLog() {
        global $wpdb;
        $table = $wpdb->prefix.'amazon_stock_log';

        $wpdb->query("DELETE FROM $table");
        if ( $wpdb->last_error ) echo 'Error in clearLog(): '.$wpdb->last_error;

        $wpdb->query("OPTIMIZE TABLE $table");
        if ( $wpdb->last_error ) echo 'Error in clearLog(): '.$wpdb->last_error;
    }

    public function optimizeLog() {
        global $wpdb;
        $table = $wpdb->prefix.'amazon_stock_log';

        $days_to_keep = self::getOption( 'stock_days_limit', 30 );
        $delete_count = $wpdb->get_var('SELECT count(id) FROM '.$wpdb->prefix.'amazon_stock_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');

        // clean stock log table
        if ( $delete_count ) {
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'amazon_stock_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
            // $this->showMessage( 'Log entries removed: ' . $delete_count );
        }
        if ( $wpdb->last_error ) echo 'Error in optimizeLog(): '.$wpdb->last_error;

        $wpdb->query("OPTIMIZE TABLE $table");
        if ( $wpdb->last_error ) echo 'Error in optimizeLog(): '.$wpdb->last_error;

        return $delete_count;
    }


} // WPLA_StockLogPage
