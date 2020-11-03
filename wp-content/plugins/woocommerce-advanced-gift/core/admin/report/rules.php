<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

wp_enqueue_style('pro-gift-datatables-css');
wp_enqueue_script('pro-gift-datatables-js');
global $wpdb;
if ($_GET['pw_action_type'] == 'user_rule') {

    if (isset($_GET['rule_id'])) {
        $rule_id = $_GET['rule_id'];
        $pw_name = get_post_meta($rule_id, 'pw_name', true);
        $pw_rule_description = get_post_meta($rule_id, 'pw_rule_description', true);
        $pw_from = get_post_meta($rule_id, 'pw_from', true);
        $pw_to = get_post_meta($rule_id, 'pw_to', true);
    } else {
        $rule_id = '0';
    }

    $sql = "SELECT pw_woocommerce_order_items.order_item_name	AS 'product_name' ,pw_woocommerce_order_items.order_item_id	AS order_item_id  ,pw_woocommerce_order_itemmeta7.meta_value	AS product_id ,pw_postmeta_customer_user.meta_value	AS customer_id ,DATE(shop_order.post_date) AS post_date ,pw_postmeta_billing_billing_email.meta_value	AS billing_email ,CONCAT(pw_postmeta_billing_billing_email.meta_value,' ',pw_woocommerce_order_itemmeta7.meta_value,' ',pw_postmeta_customer_user.meta_value)	AS group_column ,pw_woocommerce_order_items.order_id as order_id,CONCAT(pw_postmeta_billing_first_name.meta_value,' ',postmeta_billing_last_name.meta_value)	AS billing_name FROM {$wpdb->prefix}woocommerce_order_items as pw_woocommerce_order_items	 LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as pw_woocommerce_order_itemmeta7 ON pw_woocommerce_order_itemmeta7.order_item_id=pw_woocommerce_order_items.order_item_id	

LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as pw_woocommerce_order_itemmeta_gift ON pw_woocommerce_order_itemmeta_gift.order_item_id=pw_woocommerce_order_items.order_item_id
LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as pw_woocommerce_order_itemmeta_gift_rule_id ON pw_woocommerce_order_itemmeta_gift_rule_id.order_item_id=pw_woocommerce_order_items.order_item_id

LEFT JOIN {$wpdb->prefix}posts as shop_order ON shop_order.id=pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as pw_postmeta_billing_first_name ON pw_postmeta_billing_first_name.post_id	= pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as postmeta_billing_last_name ON postmeta_billing_last_name.post_id	= pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as pw_postmeta_billing_billing_email ON pw_postmeta_billing_billing_email.post_id	= pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as pw_postmeta_customer_user ON pw_postmeta_customer_user.post_id	= pw_woocommerce_order_items.order_id WHERE  pw_woocommerce_order_itemmeta7.meta_key = '_product_id' AND pw_woocommerce_order_itemmeta7.meta_key = '_product_id' AND pw_postmeta_billing_first_name.meta_key	= '_billing_first_name' AND postmeta_billing_last_name.meta_key	= '_billing_last_name' AND pw_postmeta_billing_billing_email.meta_key	= '_billing_email' AND pw_postmeta_customer_user.meta_key	= '_customer_user' AND pw_woocommerce_order_itemmeta_gift_rule_id.meta_key='_rule_id_free_gift' AND pw_woocommerce_order_itemmeta_gift_rule_id.meta_value='$rule_id' AND pw_woocommerce_order_itemmeta_gift.meta_key='_free_gift' and pw_woocommerce_order_itemmeta_gift.meta_value='yes' AND shop_order.post_status IN ('wc-processing','wc-on-hold','wc-completed') AND shop_order.post_status NOT IN ('trash') GROUP BY group_column ORDER BY billing_name ASC, product_name ASC";
    $rows = $wpdb->get_results($sql);
    ?>
    <div class="pw-report-cnt pw-full-cnt" style="margin-bottom: 15px">
        <div class="pw-rule-detail">
            <span class="pw-small-rul-detail"><?php _e('Rule Id', 'pw_wc_advanced_gift') ?> :</span>
            <span class="pw-big-rul-detail"><?php echo $rule_id; ?></span>

        </div>
        <div class="pw-rule-detail">
            <span class="pw-small-rul-detail"><?php _e('Rule name', 'pw_wc_advanced_gift') ?> : </span>
            <span class="pw-big-rul-detail"><?php echo $pw_name; ?></span>
        </div>
        <div class="pw-rule-detail">
            <span class="pw-small-rul-detail"><?php _e('Rule Description', 'pw_wc_advanced_gift') ?> :</span>
            <span class="pw-big-rul-detail"><?php echo $pw_rule_description; ?></span>
        </div>
        <div class="pw-rule-detail">
            <span class="pw-small-rul-detail"><?php _e('Valid Rule', 'pw_wc_advanced_gift') ?> : </span>
            <span class="pw-big-rul-detail">
                <?php _e('From', 'pw_wc_advanced_gift'); ?> <?php echo($pw_from == '' ? ' - ' : $pw_from); ?>
                <?php _e('To', 'pw_wc_advanced_gift'); ?> <?php echo($pw_to == '' ? ' - ' : $pw_to); ?>
            </span>
        </div>

    </div>

    <div class="pw-report-cnt pw-full-cnt">
        <h1><?php _e('Users That Used This Rule', 'pw_wc_advanced_gift'); ?></h1>
        <span class="pw-title-desc"></span>
        <table id="number_user_gifts" class="display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?php _e('Email/Billing email', 'pw_wc_advanced_gift'); ?></th>
                <th><?php _e('name', 'pw_wc_advanced_gift'); ?></th>
                <th><?php _e('Date', 'pw_wc_advanced_gift'); ?></th>
                <th><?php _e('Gift Name', 'pw_wc_advanced_gift'); ?></th>
                <th><?php _e('order', 'pw_wc_advanced_gift'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (count($rows) > 0) {
                foreach ($rows as $key => $value) {
                    $guest = '';
                    if ($value->customer_id != 0) {
                        echo '<tr>';
                        $user = get_user_by('id', $value->customer_id);
                        //                    print_r($user);
                        echo '<td>' . $user->user_email . '</td>';
                        echo '<td>' . $user->display_name . '</td>';
                        echo '<td>' . $value->post_date . '</td>';

                        echo '<td>' . $value->product_name . '</td>';

                        echo '<td><a class="pw-action-icon" href="post.php?post=' . $value->order_id . '&action=edit" title="' . __('View Order', 'pw_wc_advanced_gift') . '"><i class="fa fa-eye"></i></a></td>';
                        echo '</tr>';
                        //                    echo '<td><a href="' . admin_url('admin.php?page=rule_gift&tab=report&subtab=customer&pw_action_type=user_gifts&user_id=' . $value->customer_id) . '">' . __('List Gifts', 'pw_wc_advanced_gift') . '</a></td>';

                    } else {
                        echo '<tr class="guest">';
                        echo '<td>' . $value->billing_email . ' (' . __('Guest', 'pw_wc_advanced_gift') . ')' . '</td>';
                        echo '<td>' . $value->billing_name . '</td>';
                        echo '<td>' . $value->post_date . '</td>';

                        echo '<td>' . $value->product_name . $guest . '</td>';

                        echo '<td><a class="pw-action-icon" href="post.php?post=' . $value->order_id . '&action=edit" title="' . __('View Order', 'pw_wc_advanced_gift') . '"><i class="fa fa-eye"></i></a></td>';
                        echo '</tr>';
                    }
                }

            }
            ?>
            </tbody>
        </table>
    </div>
    <?php
    wc_enqueue_js("
        $(document) . ready(function () {  
            $('#number_user_gifts').DataTable( {
                'order': [[ 0 , 'desc' ]]
            } )  
        });
    ");

    // $pw_limit_cunter = get_post_meta($_GET['rule_id'], 'pw_limit_cunter', true);
    // $pw_limit_per_rule = get_post_meta($_GET['rule_id'], 'pw_limit_per_rule', true);
    // $pw_name = get_post_meta($_GET['rule_id'], 'pw_name', true);
    // ?>
    <!---->
    <!--    <div>-->
    <!--        --><?php //echo __('Rule name', '') . ' : ' . $pw_name; ?>
    <!--    </div>-->
    <!---->
    <!--    <table id="list_user_rule" class="display" cellspacing="0" width="100%">-->
    <!--        <thead>-->
    <!--        <tr>-->
    <!--            <th>--><?php //_e('user', 'pw_wc_advanced_gift'); ?><!--</th>-->
    <!--            <th>--><?php //_e('Quantity', 'pw_wc_advanced_gift'); ?><!--</th>-->
    <!--        </tr>-->
    <!--        </thead>-->
    <!--        <tbody>-->
    <!--        --><?php
    //        if (is_array($pw_limit_cunter['user_info'])) {
    //            foreach ($pw_limit_cunter['user_info'] as $user_info) {
    //                if ($user_info['id'] == '')
    //                    continue;
    //                $user = get_user_by('id', $user_info['id']);
    //                echo '<tr>';
    //                echo '<td>' . $user->display_name . '</td>';
    //                echo '<td>' . $user_info['number'] . '</td>';
    //                echo '</tr>';
    //            }
    //        }
    //        ?>
    <!--        </tbody>-->
    <!--    </table>-->
    <!--    --><?php
    //
    //    wc_enqueue_js("
    //        $(document) . ready(function () {
    //            $('#list_user_rule').DataTable( {
    //                'order': [[ 1 , 'desc' ]]
    //            })
    //        });
    //    ");
} else {
    ?>
    <div class="pw-report-cnt pw-full-cnt">
        <h1><?php _e('Rule Usage Report', 'pw_wc_advanced_gift'); ?></h1>
        <span class="pw-title-desc"><?php _e('The Usage Of Rules', 'pw_wc_advanced_gift'); ?></span>

        <table id="rule_usage_datatable" class="display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?php _e('Rule', 'pw_wc_advanced_gift'); ?></th>
                <th><?php _e('Usage By Customers', 'pw_wc_advanced_gift'); ?></th>
                <th><?php _e('Usage Limit Rule', 'pw_wc_advanced_gift'); ?></th>
                <th><?php _e('Action', 'pw_wc_advanced_gift'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $args = array(
                'post_type' => 'pw_gift_rule',
                'posts_per_page' => -1,
                'orderby' => 'modified',
            );
            $loop = new WP_Query($args);
            //        $i = 0;
            //        $pw_fetchs_data = '';
            //        $pw_fetchs_data [$i]["user"] = 'Non!!';
            //        $pw_fetchs_data [$i]["count"] = '0';
            while ($loop->have_posts()) :
                $loop->the_post();
                $pw_limit_cunter = get_post_meta(get_the_ID(), 'pw_limit_cunter', true);
                $pw_limit_per_rule = get_post_meta(get_the_ID(), 'pw_limit_per_rule', true);
                $pw_name = get_post_meta(get_the_ID(), 'pw_name', true);
//            if (@$pw_limit_cunter['count'] > 0) {
//                $pw_fetchs_data[$i]["user"] = $pw_name;
//                $pw_fetchs_data[$i]["count"] = $pw_limit_cunter['count'];
//                $i++;
//            }
                $status = get_post_meta(get_the_ID(), 'status', true);
                $msg_status = "";
                if ($status != "active") {
                    $msg_status = '<strong><span class="post-state"> — ' . __('Deactive', 'pw_wc_advanced_gift') . '</span></strong>';
                }
                echo '<tr>';
                echo '<td><a href="' . admin_url('admin.php?page=rule_gift&tab=add_rule&pw_action_type=edit&pw_id=' . get_the_ID()) . '">' . $pw_name .'</a>'. $msg_status . '</td>';
                echo '<td>' . (@$pw_limit_cunter['count'] > 0 ? $pw_limit_cunter['count'] : '0') . '</td>';
                echo '<td>' . ($pw_limit_per_rule > 0 ? $pw_limit_per_rule : '∞') . '</td>';
                echo '<td>
                           <a class="pw-action-icon" href="' . admin_url('admin.php?page=rule_gift&tab=report&subtab=rules&pw_action_type=user_rule&rule_id=' . get_the_ID()) . '" title="' . __('View List User Usage', 'pw_wc_advanced_gift') . '"><i class="fa fa-eye"></i></a>
                            <a  class="pw-action-icon" href="' . admin_url('admin.php?page=rule_gift&tab=add_rule&pw_action_type=edit&pw_id=' . get_the_ID()) . '" title="' . __('Edit Rule', 'pw_wc_advanced_gift') . ' "><i class="fa fa-pencil-alt"></i></a></div> 
                      </td>';

                echo '</tr>';
            endwhile;
            ?>
            </tbody>
        </table>
    </div>
    <?php
    wc_enqueue_js("
        $(document) . ready(function () {  
            $('#rule_usage_datatable').DataTable( {
                'order': [[ 1 , 'desc' ]]
            } )
    });
");
}
?>
