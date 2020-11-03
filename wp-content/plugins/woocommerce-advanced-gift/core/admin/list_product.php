<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}


$pw_array = (get_post_meta($_GET['pw_id'], 'pw_array', true) == "" ? array() : get_post_meta($_GET['pw_id'], 'pw_array', true));
$pw_to = get_post_meta($_GET['pw_id'], 'pw_to', true);
$id = rand(0, 1000);
$countdown = "style-1";
$fontsize = "medium";
$html = '
			<div class="fl-pcountdown-cnt">
				<ul class="fl-' . $countdown . ' fl-' . $fontsize . ' fl-countdown fl-countdown-pub countdown_' . $id . '">
				  <li><span class="days">00</span><p class="days_text">' . __('Days', 'pw_wc_advanced_gift') . '</p></li>
					<li class="seperator">:</li>
					<li><span class="hours">00</span><p class="hours_text">' . __('Hours', 'pw_wc_advanced_gift') . '</p></li>
					<li class="seperator">:</li>
					<li><span class="minutes">00</span><p class="minutes_text">' . __('Minutes', 'pw_wc_advanced_gift') . '</p></li>
					<li class="seperator">:</li>
					<li><span class="seconds">00</span><p class="seconds_text">' . __('Seconds', 'pw_wc_advanced_gift') . '</p></li>
				</ul>
			</div>
			<script type="text/javascript">
				jQuery(".countdown_' . $id . '").countdown({
					date: "' . $pw_to . '",
					offset: -8,
					day: "Day",
					days: "Days"
				}, function () {
				//	alert("Done!");
				});
			</script>';
?>
<div class="product-list-title"><p>Time Remening</p> <?php echo $html; ?></div>
<table class="wp-list-table widefat fixed posts" data-page-size="5" data-page-previous-text="prev"
       data-filter-text-only="true" data-page-next-text="next" cellspacing="0">
    <thead>
    <tr>
        <th scope='col' data-toggle="true" class='manage-column column-serial_number' style="">
            <a href="#"><span><?php _e('S.No', 'pw_wc_advanced_gift'); ?></span></a>
        </th>
        <th scope='col' class='manage-column' style=""><?php _e('Name', 'pw_wc_advanced_gift'); ?></th>
        <th scope='col' class='manage-column' style=""><?php _e('SKU', 'pw_wc_advanced_gift'); ?></th>
        <th scope='col' class='manage-column' style=""><?php _e('Price', 'pw_wc_advanced_gift'); ?></th>
        <th scope='col' class='manage-column' style=""><?php _e('Categories', 'pw_wc_advanced_gift'); ?></th>
        <th scope='col' class='manage-column' style=""><?php _e('Tags', 'pw_wc_advanced_gift'); ?></th>
        <th scope="col" class="manage-column" style=""><?php _e('Featured', 'pw_wc_advanced_gift'); ?></th>
        <th scope="col" class="manage-column" style=""><?php _e('Date', 'pw_wc_advanced_gift'); ?></th>
    </tr>
    </thead>
    <tbody id="grid_level_result">
    <?php
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post__in' => $pw_array,

    );
    $loop = new WP_Query($args);
    //
    //$pr=$product->get_price_html();
    //	echo  $pr;
    //	echo get_the_content();
    $output = "";
    if ($loop->have_posts()) {
        $i = 1;
        $pw_discount = get_post_meta($_GET['pw_id'], 'pw_discount', true);
        while ($loop->have_posts()) :
            $loop->the_post();
            $price = get_post_meta(get_the_ID(), '_regular_price', true);
            $sku = get_post_meta(get_the_ID(), '_sku', true);
            $_stock_status = get_post_meta(get_the_ID(), '_stock_status', true);
            $_featured = get_post_meta(get_the_ID(), '_featured', true);
            $num_decimals = apply_filters('woocommerce_wc_pricing_get_decimals', (int)get_option('woocommerce_price_num_decimals'));
            if (false !== strpos($pw_discount, '%')) {
                $max_discount = calculate_discount_modifiera($pw_discount, $price);
                $result = round(floatval($price) - (floatval($max_discount)), (int)$num_decimals);
            } else
                $result = $price - $pw_discount;

            $cate = get_the_term_list(get_the_ID(), 'product_cat', '', ',');
            $tag = get_the_term_list(get_the_ID(), 'product_tag', '', ',');
            //pw_woocommerc_get_cat( $post->ID, ', ', ' <span class="posted_in">' . $tax . ': ', '</span>');
            $output .= '
				<tr class="pw_level_tr" id="' . get_the_ID() . '">
					<td>' . $i++ . '</td>
					<td><a href="' . get_permalink() . '">' . get_the_title() . '</a></td>
					<td>' . $sku . '</td>							
					<td><del>' . woocommerce_price($price) . '</del> ' . woocommerce_price($result) . '</td>
					<td>' . $cate . '</td>
					<td>' . $tag . '</td>
					<td>' . $_featured . '</td>
					<td>' . get_the_date() . '</td>
				</tr>';
        endwhile;
    }
    echo $output;
    ?>
    </tbody>
</table>
