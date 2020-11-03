<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;
wp_enqueue_style('pro-gift-datatables-css');
wp_enqueue_script('pro-gift-datatables-js');
if ($_GET['pw_action_type'] == 'list') {
    ?>
    <div class="pw-report-cnt pw-full-cnt">
        <h1><?php _e('LIST REGISTERED USER', 'pw_wc_advanced_gift'); ?></h1>
        <span class="pw-title-desc"><?php _e("THE GIFT'S USED BY REGISTERED USER IN SHOP", 'pw_wc_advanced_gift'); ?></span>
        <?php

        $sql = "SELECT SUM(woocommerce_order_itemmeta.meta_value)	AS 'quantity' ,pw_postmeta_customer_user.meta_value	AS customer_id ,DATE(shop_order.post_date) AS post_date ,pw_postmeta_billing_billing_email.meta_value AS billing_email ,CONCAT(pw_postmeta_billing_first_name.meta_value,' ',postmeta_billing_last_name.meta_value)	AS billing_name	FROM {$wpdb->prefix}woocommerce_order_items as pw_woocommerce_order_items	LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=pw_woocommerce_order_items.order_item_id 

LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta_gift ON woocommerce_order_itemmeta_gift.order_item_id=pw_woocommerce_order_items.order_item_id 

LEFT JOIN {$wpdb->prefix}posts as shop_order ON shop_order.id=pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as pw_postmeta_billing_first_name ON pw_postmeta_billing_first_name.post_id	=	pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as postmeta_billing_last_name ON postmeta_billing_last_name.post_id	=	pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as pw_postmeta_billing_billing_email ON pw_postmeta_billing_billing_email.post_id	=	pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as pw_postmeta_customer_user ON pw_postmeta_customer_user.post_id	=	pw_woocommerce_order_items.order_id WHERE woocommerce_order_itemmeta.meta_key	= '_qty' AND pw_postmeta_customer_user.meta_value<>'0' AND pw_postmeta_billing_first_name.meta_key	= '_billing_first_name' AND postmeta_billing_last_name.meta_key	= '_billing_last_name' AND pw_postmeta_billing_billing_email.meta_key	= '_billing_email' AND pw_postmeta_customer_user.meta_key	= '_customer_user' AND woocommerce_order_itemmeta_gift.meta_key='_free_gift' AND woocommerce_order_itemmeta_gift.meta_value='yes' AND shop_order.post_status IN ('wc-processing','wc-on-hold','wc-completed') AND shop_order.post_status NOT IN ('trash') GROUP BY customer_id ORDER BY billing_name ASC";

        $rows = $wpdb->get_results($sql);
        ?>
        <table id="number_user_gifts" class="display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?php _e('User', 'pw_wc_advanced_gift'); ?></th>

                <th><?php _e('Quantity', 'pw_wc_advanced_gift'); ?></th>
                <th><?php _e('Show Gifts', 'pw_wc_advanced_gift'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            //        $pw_fetchs_data = '';
            //        $i = 0;
            //        $pw_fetchs_data [$i]["name"] = 'Non!!';
            //        $pw_fetchs_data [$i]["count"] = '0';
            if (count($rows) > 0) {
                foreach ($rows as $key => $value) {
//                if ($value->quantity > 0) {
//                    $pw_fetchs_data[$i]["name"] = $value->product_name;
//                    $pw_fetchs_data[$i]["count"] = $value->quantity;
//                    $i++;
//                }
                    echo '<tr>';
                    $guest = '';
                    if ($value->customer_id != 0) {
                        $user = get_user_by('id', $value->customer_id);
                        echo '<td>' . $user->display_name . ' (' . $user->user_email . ')' . '</td>';
                        echo '<td>' . $value->quantity . '</td>';
                        echo '<td>
                                <a class="pw-action-icon" href="' . admin_url('admin.php?page=rule_gift&tab=report&subtab=customer&pw_action_type=user_gifts&user_id=' . $value->customer_id) . '"><i class="fa fa-gift"></i></a></td>';

                    }
//                $product_name = $value->product_name;
//                $order_item_id = $value->order_item_id;
//                $product_id = $value->product_id;
//                $post_date = $value->post_date;
//                $quantity = $value->quantity;
                    echo '</tr>';
                }
            }
            ?>
            </tbody>
        </table>
        <?php
        wc_enqueue_js("
        $(document) . ready(function () {  
            $('#number_user_gifts').DataTable( {
                'order': [[ 1 , 'desc' ]]
            } )  
        });
    ");
        ?>
    </div>

    <?php
} elseif ($_GET['pw_action_type'] == 'user_gifts') {

    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
    } else {
        $user_id = '0';
    }

    $sql = "SELECT pw_woocommerce_order_items.order_item_name	AS 'product_name' ,pw_woocommerce_order_items.order_item_id	AS order_item_id  ,SUM(woocommerce_order_itemmeta.meta_value) AS 'quantity',pw_woocommerce_order_itemmeta7.meta_value AS product_id ,pw_postmeta_customer_user.meta_value	AS customer_id ,DATE(shop_order.post_date) AS post_date ,pw_postmeta_billing_billing_email.meta_value	AS billing_email ,CONCAT(pw_postmeta_billing_billing_email.meta_value,' ',pw_woocommerce_order_itemmeta7.meta_value,' ',pw_postmeta_customer_user.meta_value)	AS group_column ,CONCAT(pw_postmeta_billing_first_name.meta_value,' ',postmeta_billing_last_name.meta_value)	AS billing_name FROM {$wpdb->prefix}woocommerce_order_items as pw_woocommerce_order_items	 LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as pw_woocommerce_order_itemmeta7 ON pw_woocommerce_order_itemmeta7.order_item_id=pw_woocommerce_order_items.order_item_id	
LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=pw_woocommerce_order_items.order_item_id
LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as pw_woocommerce_order_itemmeta_gift ON pw_woocommerce_order_itemmeta_gift.order_item_id=pw_woocommerce_order_items.order_item_id

LEFT JOIN {$wpdb->prefix}posts as shop_order ON shop_order.id=pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as pw_postmeta_billing_first_name ON pw_postmeta_billing_first_name.post_id	= pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as postmeta_billing_last_name ON postmeta_billing_last_name.post_id	= pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as pw_postmeta_billing_billing_email ON pw_postmeta_billing_billing_email.post_id	= pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as pw_postmeta_customer_user ON pw_postmeta_customer_user.post_id	= pw_woocommerce_order_items.order_id WHERE  pw_postmeta_customer_user.meta_value = '$user_id' AND pw_woocommerce_order_itemmeta7.meta_key = '_product_id' AND pw_woocommerce_order_itemmeta7.meta_key = '_product_id' AND pw_postmeta_billing_first_name.meta_key	= '_billing_first_name'  AND woocommerce_order_itemmeta.meta_key = '_qty' AND  postmeta_billing_last_name.meta_key	= '_billing_last_name' AND pw_postmeta_billing_billing_email.meta_key	= '_billing_email' AND pw_postmeta_customer_user.meta_key	= '_customer_user' AND pw_woocommerce_order_itemmeta_gift.meta_key='_free_gift' and pw_woocommerce_order_itemmeta_gift.meta_value='yes' AND shop_order.post_status IN ('wc-processing','wc-on-hold','wc-completed') AND shop_order.post_status NOT IN ('trash') GROUP BY group_column ORDER BY billing_name ASC, product_name ASC";

    $rows = $wpdb->get_results($sql);
    $pw_limit_cunter = get_post_meta($_GET['user_id'], 'pw_limit_cunter', true);
    $pw_limit_per_rule = get_post_meta($_GET['user_id'], 'pw_limit_per_rule', true);
    $pw_name = get_post_meta($_GET['user_id'], 'pw_name', true);
    ?>

    <div>

    </div>

    <!--    <div class="pw-report-cnt pw-full-cnt" style="margin-bottom: 15px">-->
    <!---->
    <!--        <div class="pw-rule-detail">-->
    <!--            <span class="pw-small-rul-detail">--><?php //echo __('Rule name', '') . ':'; ?><!-- </span>-->
    <!--            <span class="pw-big-rul-detail">--><?php //echo $pw_name; ?><!--</span>-->
    <!--        </div>-->
    <!---->
    <!---->
    <!--    </div>-->
    <?php
    $user = get_user_by('id', $user_id);
    ?>
    <div class="pw-report-cnt pw-full-cnt">
        <h1>
            <?php
            _e("Gift's Used By", "pw_wc_advanced_gift");
            echo $user->display_name . ' (' . $user->user_email . ')';
            ?>
        </h1>

        <span class="pw-title-desc"><?php _e('Report Description', 'pw_wc_advanced_gift'); ?></span>
        <table id="list_user_rule" class="display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?php _e('Gifts Name', 'pw_wc_advanced_gift'); ?></th>
                <th><?php _e('quantity', 'pw_wc_advanced_gift'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (count($rows) > 0) {
                foreach ($rows as $key => $value) {
                    // $user = get_user_by('id', $user_info['id']);

                    echo '<tr>';
                    echo '<td><a href="'.get_permalink($value->product_id).'">' . $value->product_name . '</a></td>';
                    echo '<td>' . $value->quantity . '</td>';
                    echo '</tr>';
                }
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php

    wc_enqueue_js("
        $(document) . ready(function () {   
            $('#list_user_rule').DataTable( {
                'order': [[ 1 , 'desc' ]]
            })
        });   
    ");

}
?>

