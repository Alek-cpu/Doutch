<?php
global $cp_feed_order, $cp_feed_order_reverse;
require_once 'core/classes/dialogfeedsettings.php';
require_once 'core/data/savedfeed.php';

?>
    <!-- <script type="text/javascript">
        jQuery(document).ready(function(){
         jQuery('.widefat').DataTable({
                 "order": [[ 4, "desc" ]]
         });
         // jQuery('.tablenav,.top').css({'display':'block'});
        });
   </script> -->

    <div class="wrap">
        <!-- <?php $iconurl = plugins_url('/', __FILE__) . '/images/cp_feed32.png'; ?>
            <div id="icon-purple_feed" class="icon32" style="background: transparent url( <?php echo($iconurl); ?> ) no-repeat">
                <br />

            </div>
        -->

        <h2>
            <?php
            _e('Manage Cart Product Feeds', 'amwscpf-exportfeed-strings');
            $url = site_url() . '/wp-admin/admin.php?page=exportfeed-amazon-amwscpf-admin';
            echo '<input style="margin-top:12px;" type="button" class="add-new-h2" onclick="document.location=\'' . $url . '\';" value="' . __('Generate New Feed', 'amwscpf-exportfeed-strings') . '" />';
            ?>
        </h2>
        <?php //amwscpf_print_info(); ?>

        <?php
        $message = NULL;
        
        // check if wp-cron is disabled
        if (defined('DISABLE_WP_CRON') && (DISABLE_WP_CRON == true)){
            $message = '<span style="color:green;font-weight: bold">WordPress Cron is disabled. Set your Cron on server to update feeds.</span>';
            $message .= '<ol>';
            $message .= '<li>Log in to your hosting cpanel using your username and password.</li>';
            $message .= '<li>When you log into your cpanel you will see an option for cron jobs or scheduled tasks.</li>';
            $message .= '<li>Under the Common Settings, select <strong>Twice Per Hour</strong> to run cron every 30 minutes.</li>';
            $message .= '<li>Add Cron Command to Run as: <strong>wget -O /dev/null '.site_url('wp-cron.php').'</strong></li>';
            $message .= '<li>Click on <strong>Add New Cron Job</strong>, and then you are all ready.</li>';
            $message .= '</ol>';
            $message .= '<span style="color:green;font-weight: bold">If you have any confusion you can check our documentation. <a href="http://www.exportfeed.com/documentation/install-shoppingcartproductfeed-wordpress-plugin/" target="_blank">Click here</a> </span>';
        }

        // check for delete ID
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            if ($action == "delete") {
                if (isset($_GET['id'])) {
                    $delete_id = $_GET['id'];
                    $message = cart_product_feed_delete_feed($delete_id);
                }
            }
        }
        if ($message) {
            echo '<div id="setting-error-settings_updated" class="updated settings-error">
               <p>' . $message . '</p></div>';
        }
        //"New Feed" button
        $url = site_url() . '/wp-admin/admin.php?page=exportfeed-amazon-amwscpf-admin';
        //echo '<input style="margin-top:12px;" type="button" class="button-primary" onclick="document.location=\'' . $url . '\';" value="' . __( 'Generate New Feed', 'amwscpf-exportfeed-strings' ) . '" />';
        ?>

        <br/>
        <?php
        echo '
        <script type="text/javascript">
        jQuery( document ).ready( function( $ ) {
           ajaxhost = "' . plugins_url('/', __FILE__) . '";
        } );
        </script>';
        echo AMWSCP_FeedSettingsDialogs::refreshTimeOutDialog();
        // The table of existing feeds
        feeds_main_table();
        ?>
        <br/>
    </div>
<?php

// The feeds table flat
function feeds_main_table()
{

    global $wpdb;

    $feed_table = $wpdb->prefix . 'amwscp_feeds';
    $providerList = new AMWSCP_PProviderList();

    // Read the feeds
    $sql_feeds = ("SELECT f.*,description FROM $feed_table as f LEFT JOIN $wpdb->term_taxonomy on ( f.category=term_id and taxonomy='product_cat'  ) ORDER BY f.id");
    $list_of_feeds = $wpdb->get_results($sql_feeds, ARRAY_A);
    // Find the ordering method
    $reverse = false;
    if (isset($_GET['order_by']))
        $order = $_GET['order_by'];
    else
        $order = '';
    if ($order == '') {
        $order = get_option('amwscpf_feed_order');
        $reverse = get_option('amwscpf_feed_order_reverse');
    } else {
        $old_order = get_option('amwscpf_feed_order');
        $reverse = get_option('amwscpf_feed_order_reverse');
        if ($old_order == $order) {
            $reverse = !$reverse;
        } else {
            $reverse = FALSE;
        }
        update_option('amwscpf_feed_order', $order);
        if ($reverse)
            update_option('amwscpf_feed_order_reverse', TRUE);
        else
            update_option('amwscpf_feed_order_reverse', FALSE);
    }

    if (!empty($list_of_feeds)) {

        // Setup the sequence array
        $seq = false;
        $num = false;
        foreach ($list_of_feeds as $this_feed) {
            $this_feed_ex = new AMWSCPF_SavedFeed($this_feed['id']);
            switch ($order) {
                case 'name':
                    $seq[] = strtolower(stripslashes($this_feed['filename']));
                    break;
                case 'description':
                    $seq[] = strtolower(stripslashes($this_feed_ex->local_category));
                    break;
                case 'url':
                    $seq[] = strtolower($this_feed['url']);
                    break;
                case 'category':
                    $seq[] = $this_feed['category'];
                    $num = true;
                    break;
                case 'google_category':
                    $seq[] = $this_feed['remote_category'];
                    break;
                case 'type':
                    $seq[] = $this_feed['type'];
                    break;
                default:
                    $seq[] = $this_feed['id'];
                    $num = true;
                    break;
            }
        }

        // Sort the seq array
        if ($num)
            asort($seq, SORT_NUMERIC);
        else
            asort($seq, SORT_REGULAR);

        // Reverse ?
        $reverse = true;
        if ($reverse) {
            $t = $seq;
            $c = count($t);
            $tmp = array_keys($t);
            $seq = false;
            for ($i = $c - 1; $i >= 0; $i--) {
                $seq[$tmp[$i]] = '0';
            }
        }

        $image['down_arrow'] = '<img src="' . esc_url(plugins_url('images/down.png', __FILE__)) . '" alt="down" style=" height:12px; position:relative; top:2px; " />';
        $image['up_arrow'] = '<img src="' . esc_url(plugins_url('images/up.png', __FILE__)) . '" alt="up" style=" height:12px; position:relative; top:2px; " />';
        ?>
        <!--	<div class="table_wrapper">	-->
        <table class="widefat manage-feed" style="margin-top:12px;">
            <thead>
            <tr>
                <!-- <?php $url //= get_admin_url() . 'admin.php?page=exportfeed-amazon-amwscpf-manage-page&amp;order_by='; ?> -->
                <!-- <th scope="col" style="min-width: 40px;"> -->
                    <!-- <a href="<?php //echo $url . "id" ?>"> -->
                        <!-- 
                        // _e('ID', 'amwscpf-exportfeed-strings');
                        // if ($order == 'id') {
                            // if ($reverse)
                                // echo $image['up_arrow'];
                            // else
                                // echo $image['down_arrow'];
                        // }
                        ?>
                    </a>
                </th>-->
                <th scope="col" style="min-width: 120px;">
                    <a href="javascript:void(0);">
                        <?php
                        _e('Name', 'amwscpf-exportfeed-strings');
                        if ($order == 'name') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col">
                    <!-- <a href="<?php //echo $url . "category" ?>"> -->
                    <a href="javascript:void(0);">
                        <?php
                        _e('Local category', 'amwscpf-exportfeed-strings');
                        if ($order == 'category') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 100px;">
                    <a href="javascript:void(0);">
                        <?php
                        _e('Export category', 'amwscpf-exportfeed-strings');
                        if ($order == 'google_category') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 50px;">
                    <a href="javascript:void(0);">
                        <?php
                        _e('Type', 'amwscpf-exportfeed-strings');
                        if ($order == 'type') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="width: 120px;">
                    <a href="javascript:void(0);">
                        <?php
                        _e('URL', 'amwscpf-exportfeed-strings');
                        if ($order == 'url') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="width: 100px;"><?php _e('Last Updated', 'amwscpf-exportfeed-strings'); ?></th>
                <th scope="col"><?php _e('Products', 'amwscpf-exportfeed-strings'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $alt = ' class="alternate" '; ?>

            <?php
            $idx = '0';
            foreach (array_keys($seq) as $s) {
                $this_feed = $list_of_feeds[$s];
                $this_feed_ex = new AMWSCPF_SavedFeed($this_feed['id']);
                $pendcount = FALSE;
                ?>
                <tr <?php
                echo($alt);
                if ($pendcount)
                    echo 'style="background-color:#ffdddd"'
                ?>>
                    <!-- <td><?php //echo $this_feed['id']; ?></td> -->
                    <!-- <td><input type="button" name="Submit feed" value="Upload feed" class="button-primary"></td> -->
                    <td>
                        <?php echo $this_feed['filename']; ?>
                        <?php if($this_feed['previous_product_count']<$this_feed['product_count'] && $this_feed['submitted'] == 1){ $class="row-actions visible"; } else{ $class="row-actions visible"; }?>
                        <div class="<?php echo $class; ?>">
                            <?php
                            $submit_url = get_admin_url().'admin.php?page=exportfeed-amazon-amwscpf-admin&action=amwscpf_submit_feed&id='. $this_feed['id'];
                            $text = 'Submit Feed';
                            if( $this_feed['submitted'] == 1 ) { 
                                $text =  'Submit Again';
                            }
                            ?>
                            <span class="view">
                                <a href="<?php echo $this_feed['url'] ?>" target="_blank">View</a> |
                            </span>
                            <?php if($this_feed['type'] == 'AmazonSC'){ if($this_feed['previous_product_count']<$this_feed['product_count'] && $this_feed['submitted'] == 1){?>
                                <span class="submit">
                                <a href="<?php echo ($submit_url) ?>" class="purple_xmlsupload" onclick="return confirmFeedSubmission();"><i style="color:red">Product count has been changed. Additional charges may apply </i> <?php _e($text, 'amwscpf-exportfeed-strings'); ?></a> |
                            </span>
                            <?php } else{ ?>

                            <span class="submit">
                                <a href="<?php echo ($submit_url) ?>" class="purple_xmlsupload"><?php _e($text, 'amwscpf-exportfeed-strings'); ?></a> |
                            </span>
                            <?php } }?>
                            <span class="Edit">
                                 <?php
                                     $url_edit = get_admin_url() . 'admin.php?page=exportfeed-amazon-amwscpf-admin&action=edit&id=' . $this_feed['id'];
                                     $text = 'Edit';
                                     if ($this_feed['submitted'] == 1){
                                         $url_edit = get_admin_url() . 'admin.php?page=exportfeed-amazon-amwscpf-admin&action=edit&perform=update&id=' . $this_feed['id'];
                                         $text = 'Update to Amazon';
                                     }
                                 ?>
                                <a href="<?php echo($url_edit) ?>" class="purple_xmlsedit"><?php _e($text, 'amwscpf-exportfeed-strings'); ?></a> |
                            </span>
                            <span class="Delete">
                                <?php $url = get_admin_url() . 'admin.php?page=exportfeed-amazon-amwscpf-manage-page&action=delete&id=' . $this_feed['id']; ?>
                                <a href="<?php echo($url) ?>" class="purple_xmlsedit"><?php _e('Delete', 'amwscpf-exportfeed-strings'); ?></a>
                            </span>
                        </div>
                    </td>
                    <td>
                    <?php echo esc_attr(stripslashes($this_feed_ex->local_category)) ?>
                    </td>
                    <td><?php echo str_replace(".and.", " & ", str_replace(".in.", " > ", esc_attr(stripslashes($this_feed['remote_category'])))); ?></td>
                    <td><?php echo $providerList->getPrettyNameByType($this_feed['type']) ?></td>
                    <td><?php echo $this_feed['url'] ?></td>
                    <?php //$url = get_admin_url() . 'admin.php?page=??? ( edit feed ) &amp;tab=edit&amp;edit_id=' . $this_feed['id']; ?>
                    <td><?php
                        $ext = '.' . $providerList->getExtensionByType($this_feed['type']);
                        $feed_file = AMWSCP_PFeedFolder::uploadFolder() . $this_feed['type'] . '/' . $this_feed['filename'] . $ext;
                        if (file_exists($feed_file)) {
                            echo date("d-m-Y H:i:s", filemtime($feed_file));
                        } else echo 'DNE';
                        ?></td>
                    <td><?php echo $this_feed['product_count'] ?></td>

                </tr>
                <?php
                if ($alt == '') {
                    $alt = ' class="alternate" ';
                } else {
                    $alt = '';
                }

                $idx++;
            }
            ?>
            </tbody>
            <tfoot>
            <tr>
                <?php
                $url = get_admin_url() . 'admin.php?page=cart-product-manage-page&amp;order_by=';
                $order = '';
                ?>
              <!--   <th scope="col" style="min-width: 40px;">
                </th> -->
                <th scope="col" style="min-width: 120px;">
                    <a href="<?php echo $url . "name" ?>">
                        <?php
                        _e('Name', 'amwscpf-exportfeed-strings');
                        if ($order == 'name') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col">
                    <a href="<?php echo $url . "category" ?>">
                        <?php
                        _e('Local Category', 'amwscpf-exportfeed-strings');
                        if ($order == 'category') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 100px;">
                    <a href="<?php echo $url . "google_category" ?>">
                        <?php
                        _e('Export category', 'amwscpf-exportfeed-strings');
                        if ($order == 'google_category') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 50px;">
                    <a href="<?php echo $url . "type" ?>">
                        <?php
                        _e('Type', 'amwscpf-exportfeed-strings');
                        if ($order == 'type') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="width: 120px;">
                    <a href="<?php echo $url . "url" ?>">
                        <?php
                        _e('URL', 'amwscpf-exportfeed-strings');
                        if ($order == 'url') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="width: 100px;"><?php _e('Last Updated', 'amwscpf-exportfeed-strings'); ?></th>
                <th scope="col"><?php _e('Products', 'amwscpf-exportfeed-strings'); ?></th>
            </tr>
            </tfoot>

        </table>

        
        <!--	</div> -->
        <?php
    } else {
        ?>
        <div id="poststuff">
        <div style="display: none;" id="ajax-loader-cat-import" ></div>

        <div id="postbox-container-2" class="postbox-container">
                <div class="postbox">
                  <h3 class="hndle">No Feeds Yet. <a style='font-size: 14px;' href="<?php echo admin_url().'admin.php?page=exportfeed-amazon-amwscpf-admin'; ?>"><b>Click</b></a> to create feed. </h3>

                </div>
            </div>

    </div>
        <?php
    }
}

function cart_product_feed_delete_feed($delete_id = NULL)
{
    // Delete a Feed
    global $wpdb;
    $feed_table = $wpdb->prefix . 'amwscp_feeds';
    $sql_feeds = ("SELECT * FROM $feed_table where id=$delete_id");
    $list_of_feeds = $wpdb->get_results($sql_feeds, ARRAY_A);

    if (isset($list_of_feeds[0])) {
        $this_feed = $list_of_feeds[0];
        $ext = '.xml';
        if (strpos(strtolower($this_feed['url']), '.csv') > 0) {
            $ext = '.csv';
        }
        $upload_dir = wp_upload_dir();
        $feed_file = $upload_dir['basedir'] . '/amazon_mws_feeds/' . $this_feed['type'] . '/' . $this_feed['filename'] . $ext;

        if (file_exists($feed_file)) {
            unlink($feed_file);
        }
        $wpdb->query("DELETE FROM $feed_table where id=$delete_id");
        return "Feed deleted successfully!";
    }
}