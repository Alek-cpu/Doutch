<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;
wp_enqueue_style('pro-gift-datatables-css');
wp_enqueue_script('pro-gift-datatables-js');
?>
<div class="pw-report-cnt pw-full-cnt">
    <h1><?php _e("Used Guest Gift's", 'pw_wc_advanced_gift'); ?></h1>
    <span class="pw-title-desc"><?php _e("The Gift's Used By Guest In Shop", 'pw_wc_advanced_gift'); ?></span>
    <?php
    $sql = "SELECT pw_woocommerce_order_items.order_item_name	AS 'product_name' ,pw_woocommerce_order_items.order_item_id	AS order_item_id  ,pw_woocommerce_order_itemmeta7.meta_value	AS product_id ,pw_woocommerce_order_itemmeta_rule_id.meta_value as rule_id,pw_postmeta_customer_user.meta_value	AS customer_id ,pw_woocommerce_order_items.order_id as order_id,DATE(shop_order.post_date) AS post_date ,pw_postmeta_billing_billing_email.meta_value	AS billing_email ,CONCAT(pw_postmeta_billing_billing_email.meta_value,' ',pw_woocommerce_order_itemmeta7.meta_value,' ',pw_postmeta_customer_user.meta_value)	AS group_column ,CONCAT(pw_postmeta_billing_first_name.meta_value,' ',postmeta_billing_last_name.meta_value)	AS billing_name FROM {$wpdb->prefix}woocommerce_order_items as pw_woocommerce_order_items	 LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as pw_woocommerce_order_itemmeta7 ON pw_woocommerce_order_itemmeta7.order_item_id=pw_woocommerce_order_items.order_item_id	

LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as pw_woocommerce_order_itemmeta_gift ON pw_woocommerce_order_itemmeta_gift.order_item_id=pw_woocommerce_order_items.order_item_id

LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as pw_woocommerce_order_itemmeta_rule_id ON pw_woocommerce_order_itemmeta_rule_id.order_item_id=pw_woocommerce_order_items.order_item_id

LEFT JOIN {$wpdb->prefix}posts as shop_order ON shop_order.id=pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as pw_postmeta_billing_first_name ON pw_postmeta_billing_first_name.post_id	= pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as postmeta_billing_last_name ON postmeta_billing_last_name.post_id	= pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as pw_postmeta_billing_billing_email ON pw_postmeta_billing_billing_email.post_id	= pw_woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}postmeta as pw_postmeta_customer_user ON pw_postmeta_customer_user.post_id	= pw_woocommerce_order_items.order_id WHERE  pw_woocommerce_order_itemmeta7.meta_key = '_product_id' AND pw_woocommerce_order_itemmeta7.meta_key = '_product_id' AND pw_postmeta_customer_user.meta_value = '0' AND pw_postmeta_billing_first_name.meta_key	= '_billing_first_name' AND postmeta_billing_last_name.meta_key	= '_billing_last_name' AND pw_postmeta_billing_billing_email.meta_key	= '_billing_email' AND pw_postmeta_customer_user.meta_key	= '_customer_user' AND pw_woocommerce_order_itemmeta_gift.meta_key='_free_gift' AND pw_woocommerce_order_itemmeta_rule_id.meta_key='_rule_id_free_gift' AND pw_woocommerce_order_itemmeta_gift.meta_value='yes' AND shop_order.post_status IN ('wc-processing','wc-on-hold','wc-completed') AND shop_order.post_status NOT IN ('trash') GROUP BY group_column ORDER BY billing_name ASC, product_name ASC";
    $rows = $wpdb->get_results($sql);
    ?>
    <table id="number_user_gifts" class="display" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th><?php _e('Gift Name', 'pw_wc_advanced_gift'); ?></th>
            <th><?php _e('Biling Name', 'pw_wc_advanced_gift'); ?></th>
            <th><?php _e('Biling Email', 'pw_wc_advanced_gift'); ?></th>
            <th><?php _e('Date', 'pw_wc_advanced_gift'); ?></th>
            <th><?php _e('Action', 'pw_wc_advanced_gift'); ?></th>
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
                if ($value->customer_id == 0) {
                    echo '<td>' . $value->product_name . '</td>';
                    echo '<td>' . $value->billing_name . '</td>';
                    echo '<td>' . $value->billing_email . '</td>';
                    echo '<td>' . $value->post_date . '</td>';
//                    echo '<td>' . $value->order_id . '</td>';
                    echo '<td>
                            <a href="post.php?post=' . $value->order_id . '&action=edit" class="pw-action-icon" title="' . __('View Order', 'pw_wc_advanced_gift') . '"><i class="fa fa-shopping-cart"></i></a>
                            <a href="' . admin_url('admin.php?page=rule_gift&tab=add_rule&pw_action_type=edit&pw_id=' . $value->rule_id) . '" class="pw-action-icon" title="' . __('Used Rule', 'pw_wc_advanced_gift') . '"><i class="fa fa-eye"></i></a> 
                         </td>';
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
                'order': [[ 3 , 'desc' ]]
            } )  
        });
    ");
    ?>
</div>