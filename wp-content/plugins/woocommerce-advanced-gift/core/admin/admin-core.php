<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}


global $wpdb;

$status = $pw_name = $disable_if = $pw_rule_description = $pw_number_gift_allowed = $pw_gifts = $product_depends = $pw_product_depends = $category_depends = $pw_category_depends =$exclude_category_depends = $exclude_pw_category_depends = $users_depends = $roles_depends = $pw_roles= $exclude_roles_depends = $pw_exclude_roles = $pw_users = $pw_cart_amount= $pw_cart_amount_min = $pw_cart_amount_max = $pw_number_gift_allowed = $pw_from = $pw_to = $criteria_nb_products = $gift_preselector_product_page = $gift_auto_to_cart = $order_op_count = $criteria_nb_products_op = $cart_amount_op = $order_count = $is_coupons = $brand_depends = $pw_brand_depends = $pw_gifts_metod = $pw_gifts_category = $pw_category_depends_method = $pw_product_depends_method = $pw_brand_depends_method = $pw_limit_per_rule = $pw_limit_cunter = $pw_limit_per_user = $pw_register_user = $pw_daily = $pw_monthly = $schedule_type = $pw_weekly= $can_several_gift = $gift_notify_add = $repeat= $repeat_sum_qty  =$pw_exclude_product_depends  = "";
if (@$_GET['pw_action_type'] == "delete" || @$_GET['pw_action_type'] == "status" || @$_GET['pw_action_type'] == "clone") {

    if (@$_GET['pw_action_type'] == 'delete' && isset($_GET['pw_id'])) {
        wp_delete_post($_GET['pw_id']);
        ?>
        <script type="text/javascript">
            window.location = "<?php echo admin_url('admin.php?page=rule_gift');?>";
        </script>';
        <?php
        //	header('Location:'.admin_url( 'admin.php?page=rule_list'));
    } else if (@$_GET['pw_action_type'] == 'status' && isset($_GET['pw_id'])) {
        update_post_meta($_GET['pw_id'], 'status', @$_GET['status_type']);
        ?>
        <script type="text/javascript">
            window.location = "<?php echo admin_url('admin.php?page=rule_gift');?>";
        </script>';
        <?php
        //	header('Location:'.admin_url( 'admin.php?page=rule_list'));
    }else if (@$_GET['pw_action_type'] == 'clone' && isset($_GET['pw_id'])) {
       include_once(PW_WC_GiIFT_URL . 'core/admin/clone_rule.php');
    }	
    //$pw_action_type='add';

    $all_products = $this->get_all_product_list();
    include_once(PW_WC_GiIFT_URL . 'core/admin/add_edit_rule.php');
} //list_rule
elseif (!isset($_GET['pw_action_type']) || ($_GET['page'] = "rule_gift" && $_GET['tab'] == "gift_rules" && $_GET['pw_action_type'] == "list")) {
    include_once(PW_WC_GiIFT_URL . 'core/admin/list_rule.php');
}
?>