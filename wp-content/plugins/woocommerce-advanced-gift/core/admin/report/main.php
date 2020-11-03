<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

wp_enqueue_style('pro-gift-datatables-css');
wp_enqueue_script('pro-gift-datatables-js');
?>
<style>
    #count_rule_usage_serial {
        width: 100%;
        height: 500px;
        font-size: 11px;
    }

    #count_rule_usage_donut {
        width: 100%;
        height: 500px;
    }

    #best_gift_product_donut {
        width: 100%;
        height: 500px;
    }
</style>
<div class="pw-report-cnt">
    <h1><?php _e('Best Gifts', 'pw_wc_advanced_gift'); ?></h1>
    <span class="pw-title-desc"><?php _e("The Most Selected Gifts", 'pw_wc_advanced_gift'); ?>,</span>
    <div class="pw-report-title-icon">
        <i class="fa fa-table pw_report_icon" data-target="best_product_gift"></i>
        <i class="fa fa-pie-chart pw_report_icon" data-target="pw-chart-donut-best-gift-product"></i>

    </div>
    <?php
    global $wpdb;
    $query = "SELECT {$wpdb->prefix}woocommerce_order_items.order_item_name	AS 'product_name' ,{$wpdb->prefix}woocommerce_order_items.order_item_id	AS order_item_id ,{$wpdb->prefix}woocommerce_order_itemmeta7.meta_value	AS product_id	,DATE(shop_order.post_date)	AS post_date ,SUM(woocommerce_order_itemmeta.meta_value) AS 'quantity' ,SUM({$wpdb->prefix}woocommerce_order_itemmeta6.meta_value) AS 'total_amount' FROM {$wpdb->prefix}woocommerce_order_items as {$wpdb->prefix}woocommerce_order_items	LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id={$wpdb->prefix}woocommerce_order_items.order_item_id LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as {$wpdb->prefix}woocommerce_order_itemmeta6 ON {$wpdb->prefix}woocommerce_order_itemmeta6.order_item_id={$wpdb->prefix}woocommerce_order_items.order_item_id LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as {$wpdb->prefix}woocommerce_order_itemmeta7 ON {$wpdb->prefix}woocommerce_order_itemmeta7.order_item_id={$wpdb->prefix}woocommerce_order_items.order_item_id	LEFT JOIN {$wpdb->prefix}posts as shop_order ON shop_order.id={$wpdb->prefix}woocommerce_order_items.order_id LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as {$wpdb->prefix}woocommerce_order_itemmeta_gift ON {$wpdb->prefix}woocommerce_order_itemmeta_gift.order_item_id= {$wpdb->prefix}woocommerce_order_items.order_item_id WHERE 1*1 AND woocommerce_order_itemmeta.meta_key	= '_qty' AND {$wpdb->prefix}woocommerce_order_itemmeta6.meta_key	= '_line_total'	AND {$wpdb->prefix}woocommerce_order_itemmeta7.meta_key = '_product_id'	AND shop_order.post_type	= 'shop_order' AND {$wpdb->prefix}woocommerce_order_itemmeta_gift.meta_key='_free_gift' AND {$wpdb->prefix}woocommerce_order_itemmeta_gift.meta_value='yes' AND shop_order.post_status NOT IN ('trash') GROUP BY {$wpdb->prefix}woocommerce_order_itemmeta7.meta_value ORDER BY total_amount DESC";

    //    (DATE(shop_order.post_date) BETWEEN '2017-09-05' AND '2017-09-05') AND shop_order.post_status NOT IN ("trash") GROUP BY pw_woocommerce_order_itemmeta7.meta_value ORDER BY total_amount DESC

    $rows = $wpdb->get_results($query);
    ?>
    <div class="best_product_gift pw_report_target">
        <table id="best_product_gift" class="display" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th><?php _e('Product Name', 'pw_wc_advanced_gift'); ?></th>
            <th><?php _e('Quantity', 'pw_wc_advanced_gift'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $pw_fetchs_data = array();
        $i = 0;
        $pw_fetchs_data [$i]["name"] = 'Non!!';
        $pw_fetchs_data [$i]["count"] = '0';
        if (count($rows) > 0) {
            foreach ($rows as $key => $value) {
                if ($value->quantity > 0) {
                    $pw_fetchs_data[$i]["name"] = $value->product_name;
                    $pw_fetchs_data[$i]["count"] = $value->quantity;
                    $i++;
                }
                echo '<tr>';
                echo '<td><a href="'.get_permalink($value->product_id).'">' . $value->product_name . '</a></td>';
                echo '<td>' . $value->quantity . '</td>';

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
    </div>
    <div class="pw-chart-donut-best-gift-product pw_report_target" style="display: none">
        <!-- Chart code -->
        <script>
            var chart = AmCharts.makeChart("best_gift_product_donut", {
                "type": "pie",
                "theme": "light",
                "dataProvider": <?php echo json_encode($pw_fetchs_data);?>,
                "titles": [{
                    "text": "Best Gifts Product",
                    "size": 16
                }],
                "valueField": "count",
                "titleField": "name",
                "startEffect": "elastic",
                "startDuration": 2,
                "labelRadius": 15,
                "innerRadius": "50%",
                "depth3D": 10,
                "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
                "angle": 15,
                "export": {
                    "enabled": true
                }
            });
        </script>
        <!-- HTML -->
        <div id="best_gift_product_donut"></div>
    </div>
</div>


<div class="pw-report-cnt">
    <h1><?php _e('Count of used rules', 'pw_wc_advanced_gift'); ?></h1>

    <div class="pw-report-title-icon">
        <i class="fa fa-table pw_report_icon" data-target="rule_usage_count_datatable"></i>
        <i class="fa fa-pie-chart pw_report_icon" data-target="pw-chart-serial-content-gift"></i>
        <i class="fa fa-bar-chart pw_report_icon" data-target="pw-chart-donut-content-gift"></i>

    </div>

    <div class="rule_usage_count_datatable pw_report_target">
        <table id="rule_usage_count_datatable" class="display" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th><?php _e('Rule', 'pw_wc_advanced_gift'); ?></th>
            <th><?php _e('Count Used', 'pw_wc_advanced_gift'); ?></th>
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
        $i = 0;
        $pw_fetchs_data = array();
        $pw_fetchs_data [$i]["rule_name"] = 'Non!!';
        $pw_fetchs_data [$i]["count"] = '0';
        while ($loop->have_posts()) :
            $loop->the_post();
            $pw_limit_cunter = get_post_meta(get_the_ID(), 'pw_limit_cunter', true);
            $pw_name = get_post_meta(get_the_ID(), 'pw_name', true);
            if (@$pw_limit_cunter['count'] > 0) {
                $pw_fetchs_data[$i]["rule_name"] = $pw_name;
                $pw_fetchs_data[$i]["count"] = $pw_limit_cunter['count'];
                $i++;
            }

            echo '<tr>';
            echo '<td><a href="' . admin_url('admin.php?page=rule_gift&tab=add_rule&pw_action_type=edit&pw_id=' . get_the_ID()) . '">' . $pw_name . '</a></td>';
            echo '<td>' . (@$pw_limit_cunter['count'] > 0 ? $pw_limit_cunter['count'] : '0') . '</td>';
            echo '</tr>';
        endwhile;
        ?>
        </tbody>
    </table>
    </div>
    <div class="pw-chart-serial-content-gift pw_report_target" style="display: none">
        <!-- Chart code -->
        <script>
            var chart = AmCharts.makeChart("count_rule_usage_serial", {
                "type": "serial",
                "theme": "light",
                "dataProvider": <?php echo json_encode($pw_fetchs_data);?>,
                "valueAxes": [{
                    "gridColor": "#FFFFFF",
                    "gridAlpha": 0.2,
                    "dashLength": 0,
                    "title": "Usage Customers Rule",
                    "minimum": 0,
                }],
                "gridAboveGraphs": true,
                "startDuration": 1,
                "graphs": [{
                    "balloonText": "[[category]]: <b>[[value]]</b>",
                    "fillAlphas": 0.8,
                    "lineAlpha": 0.2,
                    "type": "column",
                    "valueField": "count"
                }],
                "chartCursor": {
                    "categoryBalloonEnabled": false,
                    "cursorAlpha": 0,
                    "zoomable": false
                },
                "categoryField": "rule_name",
                "categoryAxis": {
                    "gridPosition": "start",
                    "gridAlpha": 0,
                    "tickPosition": "start",
                    "tickLength": 20
                },
                "export": {
                    "enabled": true
                }
            });
        </script>

        <!-- HTML -->
        <div id="count_rule_usage_serial"></div>
    </div>

    <div class="pw-chart-donut-content-gift pw_report_target" style="display: none">
        <!-- Chart code -->
        <script>
            var chart = AmCharts.makeChart("count_rule_usage_donut", {
                "type": "pie",
                "theme": "light",
                "dataProvider": <?php echo json_encode($pw_fetchs_data);?>,
                "titles": [{
                    "text": "Usage Customers Rule",
                    "size": 16
                }],
                "valueField": "count",
                "titleField": "rule_name",
                "startEffect": "elastic",
                "startDuration": 2,
                "labelRadius": 15,
                "innerRadius": "50%",
                "depth3D": 10,
                "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
                "angle": 15,
                "export": {
                    "enabled": true
                }
            });
        </script>
        <!-- HTML -->
        <div id="count_rule_usage_donut"></div>
    </div>
</div>
<?php
wc_enqueue_js("
        $(document) . ready(function () {               
            $('#best_product_gift').DataTable( {
                'order': [[ 1 , 'desc' ]]
            } )  
            $('#rule_usage_count_datatable').DataTable( {
                'order': [[ 1 , 'desc' ]]
            } )
                        
            $('.pw_report_icon').click(function(){
                var target='.'+$(this).attr('data-target');
                $(this).siblings('.pw_report_icon').removeClass('pw-active-btn');
                $(this).addClass('pw-active-btn');

                $(target).siblings('.pw_report_target').hide();
                $(target).show();
            });            
    });
");

?>
