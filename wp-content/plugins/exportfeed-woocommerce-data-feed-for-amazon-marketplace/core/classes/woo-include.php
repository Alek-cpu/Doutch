<?php
if(!defined('WC_ABSPATH')){
   define('WC_PLUGIN_FILE', plugin_dir_path(__FILE__).'../../../woocommerce/');
   define( 'WC_ABSPATH', dirname( WC_PLUGIN_FILE ).'/woocommerce/');
   define('WC_VERSION', '3.3.3');
    $wc_create_order_file = plugin_dir_path(__FILE__).'../../../woocommerce/includes/wc-core-functions.php';
    $wc_class_file = plugin_dir_path(__FILE__).'../../../woocommerce/includes/class-woocommerce.php';
    $wc_order_file = plugin_dir_path(__FILE__).'../../../woocommerce/includes/class-wc-order.php';
    $wc_abstract_order_file = plugin_dir_path(__FILE__).'../../../woocommerce/includes/abstracts/abstract-wc-order.php';
    $wc_abstract_wc_legacy_order_file = plugin_dir_path(__FILE__).'../../../woocommerce/includes/legacy/abstract-wc-legacy-order.php';
    $wc_abstract_wc_data_file = plugin_dir_path(__FILE__).'../../../woocommerce/includes/abstracts/abstract-wc-data.php';
    $wc_data_store = plugin_dir_path(__FILE__).'../../../woocommerce/includes/class-wc-data-store.php';
    $instance = plugin_dir_path(__FILE__).'../../../woocommerce/includes/data-stores/abstract-wc-order-data-store-cpt.php';
    $WC_Data_Store_WP = plugin_dir_path(__FILE__).'../../../woocommerce/includes/data-stores/class-wc-data-store-wp.php';
    $WC_Object_Data_Store_Interface = plugin_dir_path(__FILE__).'../../../woocommerce/includes/interfaces/class-wc-object-data-store-interface.php';
    $WC_Abstract_Order_Data_Store_Interface = plugin_dir_path(__FILE__).'../../../woocommerce/includes/interfaces/class-wc-abstract-order-data-store-interface.php';
    $WC_Order_Data_Store_CPT = plugin_dir_path(__FILE__).'../../../woocommerce/includes/data-stores/class-wc-order-data-store-cpt.php';
    $WC_Order_Data_Store_Interface = plugin_dir_path(__FILE__).'../../../woocommerce/includes/interfaces/class-wc-order-data-store-interface.php';
    $WC_Geolocation = plugin_dir_path(__FILE__).'../../../woocommerce/includes/class-wc-geolocation.php';
    $WC_DateTime = plugin_dir_path(__FILE__).'../../../woocommerce/includes/class-wc-datetime.php';
    $AdminWC = plugin_dir_path(__FILE__).'../../../woocommerce/includes/class-woocommerce.php';
    $WC_Order_Item_Coupon = plugin_dir_path(__FILE__).'../../../woocommerce/includes/class-wc-order-item-coupon.php';
    $WC_Order_Item = plugin_dir_path(__FILE__).'../../../woocommerce/includes/class-wc-order-item.php';
    $WC_Customer_Download_Data_Store = plugin_dir_path(__FILE__).'../../../woocommerce/includes/data-stores/class-wc-customer-download-data-store.php';
    $WC_Customer_Download_Data_Store_Interface = plugin_dir_path(__FILE__).'../../../woocommerce/includes/interfaces/class-wc-customer-download-data-store-interface.php';
    $WC_Admin_Reports = plugin_dir_path(__FILE__).'../../../woocommerce/includes/admin/class-wc-admin-reports.php';
    $WC_Cache_Helper = plugin_dir_path(__FILE__).'../../../woocommerce/includes/class-wc-cache-helper.php';
    // print_r($wc_abstract_wc_data_file);exit;/var/www/html/exportfeed/wp-content/plugins/woocommerce/includes/class-wc-cache-helper.php
    if(file_exists($wc_create_order_file) && file_exists($wc_class_file)){
        // global $wp_rewrite ;
        // $wp_rewrite = new StdClass();
        // $wp_rewrite->use_trailing_slashes = ( '/' == substr($this->permalink_structure, -1, 1) );
        // require( ABSPATH . WPINC . '/pluggable.php' );
        // require_once( ABSPATH . WPINC . '/class-wp-rewrite.php' );
        // include $WC_DateTime;
        // include $WC_Geolocation;
        // require_once $wc_class_file;
        include $AdminWC;
        include $WC_Cache_Helper;
        include $WC_Admin_Reports;
        include $WC_Customer_Download_Data_Store_Interface;
        include $WC_Customer_Download_Data_Store;
        include $WC_Abstract_Order_Data_Store_Interface;
        include $WC_Object_Data_Store_Interface;
        include $WC_Data_Store_WP;
        include $instance;
        include $WC_Order_Data_Store_Interface;
        include $WC_Order_Data_Store_CPT;
        include $wc_data_store;
        include $WC_DateTime;
        include $wc_abstract_wc_data_file;
        include $WC_Order_Item;
        include $WC_Order_Item_Coupon;
        include $wc_abstract_wc_legacy_order_file;
        include $wc_abstract_order_file;
        require_once($wc_order_file);
        include $WC_Geolocation;
        require_once($wc_create_order_file);
        require( ABSPATH . WPINC . '/pluggable.php' );
        // include $wc_abstract_wc_data_file;
        // include $wc_abstract_wc_legacy_order_file;
    }
}
 ?>