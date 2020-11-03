<?php
require_once 'amwscpf-wpincludes.php';
if (!class_exists('AMWSCPF_Product_Listing')) {

    class AMWSCPF_Product_Listing
    {
        var $selected_ids = '';
        var $prepared = false;
        var $result = "";
        var $exported = [];
        var $skipped = [];

        function __construct()
        {
            if(is_admin()) {
                global $post_type;
                add_filter('post_row_actions',      array(&$this,'amwscpf_action_link'), 10, 2);
                add_action('admin_footer-edit.php', array(&$this, 'custom_bulk_admin_footer'));
                add_action('admin_print_styles',    array(&$this, 'printProductsPageStyles'));
                add_action('load-edit.php',         array(&$this, 'custom_bulk_action'));
                add_action('admin_notices',         array(&$this, 'custom_bulk_admin_notices'));
                if (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'product'){
                    add_action('manage_posts_columns',         array(&$this, 'add_sticky_column'));
                    add_action('manage_posts_custom_column',         array(&$this, 'display_posts_stickiness'), 10, 2);
                }
                if (isset($_GET['ids'])){
                    $this->selected_ids = sanitize_text_field($_GET['ids']);
                    $this->prepared = true;
                }
            }
        }

        function display_posts_stickiness( $column, $post_id ) {
            if ($column == 'amwscpf_has_feed'){
                $is_uploaded = $this->is_uploaded($post_id);
                if ($is_uploaded)
                    echo '<span class="dashicons dashicons-thumbs-up help_tip" data-tip="Product is submitted to amazon. Click here to download the report" onclick="download_report('.$post_id.',\'product\')" style="cursor:pointer"></span>';
                else
                    echo '<span class="dashicons dashicons-thumbs-down help_tip" data-tip="Product is not yet submitted to amazon"></span>';
            }
        }

        function is_uploaded($id){
            global $wpdb;
            $table = $wpdb->prefix."amwscp_amazon_feeds";
            $sql = $wpdb->prepare("SELECT status FROM $table WHERE type_id = %d",[$id]);
            $status = $wpdb->get_var($sql);
            return $status;
        }

        function add_sticky_column($columns) {
                return array_merge($columns,
                    ['amwscpf_has_feed'=>__('Amazon','amwscpf-exportfeed-string')]
                    );
        }

        function amwscpf_action_link($actions,$item) {
            global $post_type;
            if ($post_type == 'product'){
//                $actions['move_on_amazon'] = "<a class='amwscpf_list_on_etsy' data-product = '".$item->ID."' href='#'>" . __( 'Move to Amazon', 'amwscpf' ) . "</a>";
            }
            return $actions;
        }

        function custom_bulk_admin_footer() {
            global $post_type;

            if($post_type == 'product') {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function() {
                        jQuery('<option>').val('amwscpf_move').text('<?php _e('Upload to Amazon')?>').appendTo("select[name='action']");
                        jQuery('<option>').val('amwscpf_move').text('<?php _e('Upload to Amazon')?>').appendTo("select[name='action2']");
                    });
                </script>
                <?php
            }
        }

        function printProductsPageStyles(){
            global $wp_scripts,$amwcore;

            wp_register_script( 'amwscpf_product_listing', AMWSCPF_URL.'/js/category_selector.js?ver='.time(), array( 'jquery' ) );
            wp_enqueue_script( 'amwscpf_product_listing' );

            wp_localize_script('amwscpf_product_listing', 'amwscpf_i18n', array(
                    'nonce_check'           =>  wp_create_nonce('amazon-exportfeed-nonce'),
                    'cmdAmazonProcessings'    =>  "core/ajax/wp/amazon_processings.php",
                    'loadImg'               => $amwcore->amwscpf_loader('amwcpf_loader'),
                    'cmdSubmissionFeedResult' => "core/ajax/wp/submission_feed_result.php"
                )
            );
        }

        function custom_bulk_admin_notices() {
            global $post_type, $pagenow;

            if($pagenow == 'edit.php' && $post_type == 'product' && isset($_REQUEST['amwscpf_exported']) && isset($_REQUEST['amwscpf_skipped'])) {
//                echo "<div class=\"updated\"><pre></pre></div>";
                $exported = explode(",",$_REQUEST['amwscpf_exported']);
                $skipped = explode(",",$_REQUEST['amwscpf_skipped']);

                echo '<div class="updated">';
                if (count($exported) > 0){
                    foreach ($exported as $key => $id){
                        if ($id){
                            $product = wc_get_product($id);
                            echo "<p style='color:green'>{$product->post->post_title} has been submitted to your amazon.</p>";
                        }

                    }
                }
                if (count($skipped) > 0){
                    foreach ($skipped as $key => $id){
                        if ($id){
                            $product = wc_get_product($id);
                            echo "<p style='color:red'>{$product->post->post_title} has been skipped.</p>";
                        }

                    }
                }
                echo '</div>';
            }
        }

        function custom_bulk_action() {
            global $typenow;
            $post_type = $typenow;

            if($post_type == 'product') {

                // get the action
                $wp_list_table = _get_list_table('WP_Posts_List_Table');  // depending on your resource type this could be WP_Users_List_Table, WP_Comments_List_Table, etc
                $action = $wp_list_table->current_action();

                $allowed_actions = array("amwscpf_move");
                if(!in_array($action, $allowed_actions)) return;

                // security check
                check_admin_referer('bulk-posts');

                // make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
                if(isset($_REQUEST['post'])) {
                    $post_ids = array_map('intval', $_REQUEST['post']);
                }

                if(empty($post_ids)) return;

                // this is based on wp-admin/edit.php
                $sendback = remove_query_arg( array('amwscpf_exported', 'untrashed', 'deleted', 'ids'), wp_get_referer() );
                if ( ! $sendback )
                    $sendback = admin_url( "edit.php?post_type=$post_type" );

                $pagenum = $wp_list_table->get_pagenum();
                $sendback = add_query_arg( 'paged', $pagenum, $sendback );
                $exported = [];
                $skipped = [];
                switch($action) {
                    case 'amwscpf_move':

                        foreach ($post_ids as $id){
                            // check if already submitted
                            $report_id = $this->getReport($id);
                            if (!$report_id){
                                // check if template exists
                                $tpl_id = get_post_meta($id,'_amwscpf_template');
                                if ( count($tpl_id) > 0 ){

                                    // prepare data for report
                                    $this->prepareReportData($tpl_id[0],$id);

                                    // generates a report and gives its url
                                    $file = $this->prepareReport($tpl_id[0],$id);

                                    // uploads the report
                                    $result = $this->upload($file,$id);

                                    if ($result)
                                        $exported[] = $id;

                                } else $skipped[] = $id;
                            } else $skipped[] = $id;

                        }
                        $exported = join(",",$exported);
                        $skipped = join(",",$skipped);
                        $this->selected_ids = $exported;
                        break;

                    default: return;
                }
                $sendback = add_query_arg( array(
                    'amwscpf_exported'  => $exported,
                    'amwscpf_skipped'   => $skipped
                ), $sendback );

                $sendback = remove_query_arg( array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status',  'post', 'bulk_edit', 'post_view'), $sendback );

                wp_redirect($sendback);
                exit();
            }
        }

        function prepareReportData($tpl_id,$id){
            global $wpdb;

            $product = wc_get_product($id);

            $table = $wpdb->prefix.'amwscp_template_values';
            $sql = $wpdb->prepare("SELECT fields FROM $table WHERE tmpl_id = %d",[$tpl_id]);
            $template_fields = $wpdb->get_results($sql);

            $data = array();
            foreach ($template_fields as $key => $field){
                $data[$field->fields] = get_post_meta($id,'_amwscpf_'.$field->fields)[0];
            }
            $this->data = $data;
        }

        function getReport($id){
            global $wpdb;
            $table = $wpdb->prefix."amwscp_amazon_feeds";
            $sql = $wpdb->prepare("SELECT id FROM $table WHERE type_id = %d",[$id]);
            $report = $wpdb->get_var($sql);
            return $report;
        }


        function prepareReport($tpl_id,$id){
            global $wpdb;
            // making a format of tempalte and importing data into it
            $table = $wpdb->prefix."amwscp_amazon_templates";
            $sql = $wpdb->prepare("SELECT raw FROM $table WHERE id = %d",[$tpl_id]);
            $template = $wpdb->get_row($sql);

            return $this->doFormating($template->raw,$this->data,$id);
        }

        function upload($file,$id){

            require_once 'core/classes/amazon_main.php';
            global $wpdb;
            $table = $wpdb->prefix."amwscp_amazon_feeds";

            $amazon = new CPF_Amazon_Main();
            $result = $amazon->upload_from_product($file);
            #echo "<pre>";print_r($result);die;
            if ($result->success){
                $data = [
                    'FeedSubmissionId'      => $result->FeedSubmissionId,
                    'FeedType'              => $result->FeedType,
                    'SubmittedDate'         => $result->SubmittedDate,
                    'FeedProcessingStatus'  => $result->FeedProcessingStatus,
                    'data'                  => file_get_contents($file),
                    'type_id'               => $id,
                    'type'                  => 'product',
                    'status'                => $result->status,
                    'account_id'            => $result->account,
                    'feed_title'            => get_the_title($id)
                ];

                $ins = $wpdb->insert($table,$data);
            }
            return $result->success;

        }

        function doFormating($headers,$values,$id){
            $product = wc_get_product($id)->post;

            $output = $headers;
            foreach ($values as $key => $fields){
                $output .= $fields."\t";
            }

            $upload_dir     = wp_upload_dir();
            $dir            = '/amazon_mws_feeds/products/';
            $upload_path    = $upload_dir['basedir'].$dir;


            if( ! is_dir($upload_path)){
                mkdir($upload_path);
            }
            if( ! is_writable($upload_path)){
                wp_die("Error: Upload directory is not writable");
            }
            $filename = $product->post_title.".txt";

            // save file in uploads folder
            $local_file = trailingslashit( $upload_path ) . basename( $filename );
            if ( ! file_put_contents( $local_file, $output ) ) {
                wp_die('Cannot Write File. Please try once again');
            }
            $filename = $upload_path.$filename;
            return $filename;
        }

    }
}

new AMWSCPF_Product_Listing();