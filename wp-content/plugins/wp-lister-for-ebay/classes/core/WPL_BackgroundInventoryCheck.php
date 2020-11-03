<?php

class WPL_BackgroundInventoryCheck extends WPL_InventoryCheck {
    private $tasks = array();

    public function __construct() {
        $this->data_key         = 'wple_bg_inventory_check_queue_data';

        $data = $this->getTemporaryData();

        $this->mode             = $data['mode'];
        $this->oos_products     = $data['out_of_sync_products'];
        $this->compare_prices   = $data['compare_prices'];
        $this->published_count  = $data['published_count'];
        $this->batch_size       = get_option( 'wplister_inventory_check_batch_size', 200 );
        $this->tasks            = $data['tasks'];
    }

    public function buildTaskList() {
        WPLE()->logger->info( 'buildTaskList())' );
        // get accounts
        $accounts = WPLE_eBayAccount::getAll( false, true );

        if ( ! empty( $accounts ) ) {
            $account_ids = wp_list_pluck( $accounts, 'id' );

            foreach ( $account_ids as $account_id ) {
                $this->tasks[] = array(
                    'task'          => 'getSellerListings',
                    'account_id'    => $account_id,
                    'page'          => 1,
                );
            }

        }

        $this->saveTemporaryData();
        $this->toggleTaskRunner();
    }

    public function queueGetSellerListings() {
        WPLE()->logger->info( 'queueGetSellerListings())' );
        // get accounts
        $accounts = WPLE_eBayAccount::getAll( false, true );

        if ( ! empty( $accounts ) ) {
            $account_ids = wp_list_pluck( $accounts, 'id' );
            as_schedule_single_action( time(), 'wple_bg_inventory_check_get_listings', $account_ids );
        }
    }

    public function getSellerListings( $account_id, $page = 1 ) {
        WPLE()->logger->info( "getSellerListings for account #{$account_id} / Page {$page}" );
        WPLE()->initEC( $account_id );

        $mdl = new WPL_Model();
        $mdl->initServiceProxy( WPLE()->EC->session );

        $mdl->_cs->setHandler('ItemType', array( $this, 'checkListingQuantity' ) );

        $Pagination = new PaginationType();
        $Pagination->setEntriesPerPage( apply_filters( 'wple_get_seller_listings_per_page', 200 ) );
        $Pagination->setPageNumber( (int)$page );

        $endTimeFrom = gmdate('Y-m-d\TH:i:s', strtotime('-119 days') ).'.000Z'; // 2 days ago (3 days max)
        $endTimeTo   = gmdate('Y-m-d\TH:i:s' ).'.000Z'; // 2 days ago (3 days max)

        $req = new GetSellerListRequestType();
        //$req->setDetailLevel('ReturnAll');
        $req->setGranularityLevel( 'Coarse' );
        $req->setPagination( $Pagination );
        $req->setEndTimeFrom( $endTimeFrom );
        $req->setEndTimeTo( $endTimeTo );

        // get first page
        WPLE()->logger->info( 'fetching all items list - page '. $page );
        $response = $mdl->_cs->GetSellerList( $req );

        // checkSync for the items on all pages is being handled by self::checkListingQuantity()

        if ( $page == 1 ) {
            // Add tasks to load and process the remaining pages
            $pages = $response->PaginationResult->TotalNumberOfPages;
            for ($page = 2; $page <= $pages; $page++) {
                $this->tasks[] = array(
                    'task'          => 'getSellerListings',
                    'account_id'    => $account_id,
                    'page'          => $page
                );

                WPLE()->logger->info("Added a task for account #{$account_id} page #{$page}");
            }
        }


        $this->saveTemporaryData();
        $this->toggleTaskRunner();

        /*$startTimeFrom = gmdate('Y-m-d\TH:i:s', strtotime( "-1 days" ) ).'.000Z';

        $req = new GetSellerEventsRequestType();
        $req->setModTimeFrom( $startTimeFrom );
        $req->setIncludeVariationSpecifics( 'false' );
        $req->setNewItemFilter( 'true' );
        //$req->setDetailLevel( 'ReturnAll' );
        $req->setOutputSelector( 'Quantity', 'qty' );
        $req->setOutputSelector( 'ItemID', 'id' );
        $response = $mdl->_cs->GetSellerEvents( $req );

        // store the response in a file so we don't pull over and over
        //$uploads = wp_upload_dir();
        //file_put_contents( trailingslashit( $uploads['basedir'] ) . 'GetSellerEvents.txt', serialize( $response ) );

        //$response = unserialize( file_get_contents( trailingslashit( $uploads['basedir'] ) . 'GetSellerEvents.txt' ) );

        if ( $response && is_array( $response->ItemArray ) ) {
            foreach ( $response->ItemArray as $item ) {
                if ( !isset( $item->Quantity ) ) continue;

                $listing = ListingsModel::getItemByEbayID( $item->ItemID );

                if ( $listing ) {
                    $item = (array)$listing;
                    if (! $this->checkSync( $item ) ) {
                        $this->addToReport( $item );
                    }
                    //echo "<p>Found listing #{$listing->id} for eBay item #{$item->ItemID} (eBay: {$item->Quantity} / Local: {$listing->quantity})</p>";
                } else {
                    //echo "<p>No listings found for eBay ID #{$item->ItemID}</p>";
                }

            }
        }*/
    }

    public function runTasks() {
        WPLE()->logger->info('runTasks');

        if ( empty( $this->tasks ) ) {
            WPLE()->logger->info('Task list is empty');
            //$this->toggleTaskRunner();

            if ( !empty( $this->oos_products ) ) {
                WPLE()->logger->info( 'Tasks now empty. Send notification email and reset the data');
                // Looks like we're all done!
                $this->sendSyncNotificationEmail();
            }

            $this->reset();
            return;
        }

        $task = array_shift( $this->tasks );

        WPLE()->logger->info( 'Current task: '. print_r( $task, 1 ) );
        WPLE()->logger->info( 'Remaining tasks: '. print_r( $this->tasks, 1 ) );

        // Make sure we save the new tasks list
        $this->saveTemporaryData();

        if ( empty( $task['task'] ) ) {
            WPLE()->logger->info('$task[task] is empty');
            return false;
        }

        switch ( $task['task'] ) {
            case 'getSellerListings':
                $this->getSellerListings( $task['account_id'], $task['page'] );
                break;

        }

    }

    private function toggleTaskRunner() {
        WPLE()->logger->info('toggleTaskRunner');
        wple_enqueue_async_action( 'wple_bg_inventory_check_run_tasks' );
        if ( empty( $this->tasks ) ) {
            // runTasks one more time to send the email report
            //$this->runTasks();
        } else {
            //if ( ! as_next_scheduled_action( 'wple_bg_inventory_check_run_tasks' ) ) {
//                as_enqueue_async_action( 'wple_bg_inventory_check_run_tasks' );
                //as_schedule_recurring_action( time(), 20, 'wple_bg_inventory_check_run_tasks' );
            //}
        }
    }

    public function checkListingQuantity( $type, $item ) {
        $listing = WPLE_ListingQueryHelper::findItemByEbayID( $item->ItemID, false );

        if ( !$listing ) {
            return;
        }

        if ( !in_array( $listing->status, array( 'published', 'changed' ) ) ) {
            return;
        }

        if ( $item->SellingStatus->ListingStatus != 'Active' ) {
            return;
        }

        $data = array(
            'post_id'   => $listing->post_id,
            'ebay_id'       => $listing->ebay_id,
            'profile_data'  => $listing->profile_data,
            'variations'    => $listing->variations,
            'quantity'      => $item->Quantity,
            'quantity_sold' => $item->SellingStatus->QuantitySold,
            'status'        => $listing->status,
        );

        if ( ! $this->checkSync( $data, false ) ) {
            $this->addToReport( $data );
        }
    }

    public function sendSyncNotificationEmail() {
        WPLE()->logger->info('sendSyncNotificationEmail');
        $admin_email = get_option( 'wple_inventory_check_notification_email', get_bloginfo( 'admin_email' ) );
        $mailer = WC()->mailer();
        $subject = 'Your WP-Lister products are out of sync!';
        $message = '<p>Warning: '. sprintf( _n( '%d listing is', '%d listings are', sizeof( $this->oos_products ), 'wp-lister-for-ebay' ), sizeof( $this->oos_products ) ) .' out of sync or missing in WooCommerce.</p>';
        $message .= $this->generateResultsTable();
        $message = $mailer->wrap_message( $subject, $message );
        $mailer->send( $admin_email, $subject, $message );
    }

    private function generateResultsTable() {
        WPLE()->logger->info('generateResultsTable');
        // restore previous data
        $out_of_sync_products = $this->oos_products;
        $published_count      = $this->published_count;
        $compare_prices       = $this->compare_prices;
        $mode                 = $this->mode;

        // return if empty
        if ( empty( $out_of_sync_products ) ) {
            WPLE()->logger->info('oos list is empty');
            return;
        }

        // table header
        $msg .= '<table style="width:100%">';
        $msg .= "<tr>";
        $msg .= "<th style='text-align:left'>SKU</th>";
        $msg .= "<th style='text-align:left'>Product</th>";
        $msg .= "<th style='text-align:left'>Local Qty</th>";
        $msg .= "<th style='text-align:left'>eBay Qty</th>";
        $msg .= "<th style='text-align:left'>Local Price</th>";
        $msg .= "<th style='text-align:left'>eBay Price</th>";
        $msg .= "<th style='text-align:left'>eBay ID</th>";
        $msg .= "<th style='text-align:left'>Status</th>";
        $msg .= "</tr>";

        // table rows
        foreach ( $out_of_sync_products as $item ) {
            // echo "<pre>";print_r($item['ebay_id']);echo"</pre>";#die();
            if ( !isset( $item['auction_title'] ) ) {
                $listing = ListingsModel::getItemByEbayID( $item['ebay_id'], false );
                $item = array_merge( (array)$listing, $item );
            }

            // get column data
            $qty          = $item['qty'];
            $sku          = get_post_meta( $item['post_id'], '_sku', true );
            $stock        = $item['stock'];
            $title        = $item['auction_title'];
            $post_id      = $item['post_id'];
            $ebay_id      = $item['ebay_id'];
            $status       = $item['status'];
            $exists       = $item['exists'];
            $locked       = $item['locked'] ? 'locked' : '';
            $price        = wc_price( $item['price'] );
            $price_woo    = wc_price( $item['price_woo'] );
            $product_type = $item['type'] == 'simple' ? '' : $item['type'];

            // highlight changed values
            $changed_stock     =   intval( $item['qty']   )     ==   intval( $item['stock']     )     ? false : true;
            $changed_price     = floatval( $item['price'] )     == floatval( $item['price_woo'] )     ? false : true;
            $changed_price_max = floatval(@$item['price_max'] ) == floatval( $item['price_woo_max'] ) ? false : true;
            $stock_css         = $changed_stock                       ? 'color:darkred; font-weight:bold;' : '';
            $price_css         = $changed_price || $changed_price_max ? 'color:darkred;'                   : '';
            if ( ! $compare_prices ) $price_css = '';

            // build links
            $ebay_url = $item['ViewItemURL'] ? $item['ViewItemURL'] : $ebay_url = 'http://www.ebay.com/itm/'.$ebay_id;
            $ebay_link = '<a href="'.$ebay_url.'" target="_blank">'.$ebay_id.'</a>';
            $edit_link = '<a href="'. admin_url( 'post.php?action=edit&post='.$post_id ) .'" target="_blank">'.$title.'</a>';

            // mark non existent products
            if ( ! $exists ) {
                $stock    = 'N/A';
                $post_id .= ' missing!';
            }

            // show price range for variations
            if ( $item['price_woo_max'] )
                $price_woo .= ' - '.wc_price( $item['price_woo_max'] );
            if ( @$item['price_max'] )
                $price .= ' - '.wc_price( $item['price_max'] );

            if ( $item['profile_start_price'] )
                $price .= ' ('. $item['profile_start_price'] .')';

            // build table row
            $msg .= "<tr>";
            $msg .= "<td>$sku</td>";
            $msg .= "<td>$edit_link <span style='color:silver'>$locked $product_type (#$post_id)</span></td>";
            $msg .= "<td style='$stock_css'>$stock</td>";
            $msg .= "<td style='$stock_css'>$qty</td>";
            $msg .= "<td style='$price_css'>$price_woo</td>";
            $msg .= "<td style='$price_css'>$price</td>";
            $msg .= "<td>$ebay_link</td>";
            $msg .= "<td>$status</td>";
            $msg .= "</tr>";
        }
        $msg .= '</table>';

        return $msg;
    }

    protected function getTemporaryData() {
        $data = get_option( $this->data_key, false );

        if ( ! $data ) {
            // ensure we return an array with these default keys
            $data = array(
                'mode'                  => 'published',
                'compare_prices'        => false,
                'out_of_sync_products'  => array(),
                'published_count'       => 0,
                'tasks'                 => array(),
            );
        }

        return $data;
    }

    protected function saveTemporaryData() {
        WPLE()->logger->info('saveTemporaryData');
        $data = array(
            'mode'                  => $this->mode,
            'compare_prices'        => $this->compare_prices,
            'out_of_sync_products'  => $this->oos_products,
            'published_count'       => $this->published_count,
            'tasks'                 => $this->tasks,
        );
        update_option( $this->data_key, $data );
    }

}
