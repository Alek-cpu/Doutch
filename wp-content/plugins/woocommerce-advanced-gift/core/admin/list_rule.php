<?php
$pw_action_type = (isset($_GET['pw_action_type']) ? $_GET['pw_action_type'] : "list");
if ($pw_action_type == "list") {
    ?>

    <script type="text/javascript">
        jQuery(document).ready(function (e) {
            jQuery('.pw_delete_gift_rule').live('click', function (e) {
                if (!confirm('Are You Sure Delete This Rule ?'))
                    e.preventDefault();
            });
            jQuery('.pw_active_gift_rule').live('click', function (e) {
                if (!confirm('Are You Sure Change Status ?'))
                    e.preventDefault();
            });
            jQuery('.pw_clone_gift_rule').live('click', function (e) {
                if (!confirm('Are You Sure Clone This Rule ?'))
                    e.preventDefault();
            });			
        });
    </script>
    <div class="pw-form-cnt">
        <div class="pw-form-content">
            <table class="wp-list-table widefat fixed posts fs-rolelist-tbl" data-page-size="5"
                   data-page-previous-text="prev" data-filter-text-only="true" data-page-next-text="next"
                   cellspacing="0">
                <thead>
                <tr>
                    <th scope="col" class="manage-column"
                        style="width: 50px"><?php _e('Status', 'pw_wc_advanced_gift'); ?></th>
                    <th scope='col' class='manage-column' style=""><?php _e('Rule Name', 'pw_wc_advanced_gift'); ?></th>
                    <th scope='col' class='manage-column' style=""><?php _e('From Date', 'pw_wc_advanced_gift'); ?></th>
                    <th scope='col' class='manage-column' style=""><?php _e('To Date', 'pw_wc_advanced_gift'); ?></th>

                    <th scope="col" class="manage-column"
                        style=""><?php _e('Last Modified', 'pw_wc_advanced_gift'); ?></th>
                    <th scope="col" class="manage-column"
                        style=""><?php _e('Actions', 'pw_wc_advanced_gift'); ?></th>
                </tr>
                </thead>
                <tbody id="grid_level_result">
                <?php
                $blogtime = current_time('mysql');
                $args = array(
                    'post_type' => 'pw_gift_rule',
                    'posts_per_page' => -1,
                    'orderby' => 'modified',
                );
                $output = '';
                $i = 1;
                $loop = new WP_Query($args);
                $setting = get_option("pw_gift_options");
                while ($loop->have_posts()) :
                    $loop->the_post();
                    $type = get_post_meta(get_the_ID(), 'pw_type', true);
                    if ($type == "rule") {
                        $id = $html = $pw_to = $pw_from = "";
                        $pw_to = get_post_meta(get_the_ID(), 'pw_to', true);
                        $pw_type = get_post_meta(get_the_ID(), 'pw_to', true);
                        $pw_from = get_post_meta(get_the_ID(), 'pw_from', true);
//                        $id = rand(0, 1000);
//                        $countdown = "style-1";
//                        $fontsize = "medium";
//                        $html = '
//                                <ul class="fl-' . $countdown . ' fl-' . $fontsize . ' fl-countdown fl-countdown-pub countdown_' . $id . '">
//                                  <li><span class="days">--</span><p class="days_text">' . $setting['popup_title'] . '</p></li>
//                                    <li class="seperator">:</li>
//                                    <li><span class="hours">--</span><p class="hours_text">' . $setting['Hour'] . '</p></li>
//                                    <li class="seperator">:</li>
//                                    <li><span class="minutes">--</span><p class="minutes_text">' . $setting['Minutes'] . '</p></li>
//                                    <li class="seperator">:</li>
//                                    <li><span class="seconds">--</span><p class="seconds_text">' . $setting['Seconds'] . '</p></li>
//                                </ul>
//                                <script type="text/javascript">
//                                    jQuery(".countdown_' . $id . '").countdown({
//                                        date: "' . $pw_to . '",
//                                        offset: -8,
//                                        day: "Day",
//                                        days: "Days"
//                                    }, function () {
//                                    //	alert("Done!");
//                                    });
//                                </script>';
//                        $res = strtotime(get_post_meta(get_the_ID(), 'pw_to', true)) - strtotime(get_post_meta(get_the_ID(), 'pw_from', true));
//                        $days = floor(($res) / 86400);
//                        $hours = floor(($res - ($days * 86400)) / 3600);
//                        $res = 'Days: ' . $days . ' H : ' . $hours;

                        $status = get_post_meta(get_the_ID(), 'status', true);
                        $imgstatus = "";
                        if ($status == "active") {
                            $imgstatus = '<a class="pw_active_gift_rule pw-action-icon pw-green-icon" href="' . admin_url('admin.php?page=rule_gift&pw_action_type=status&status_type=deactive&pw_id=' . get_the_ID()) . '"><i class="fa fa-check"></i></a>';

                            $msg_status = '';
                        } else {
                            $imgstatus = '<a class="pw_active_gift_rule pw-action-icon pw-red-icon" href="' . admin_url('admin.php?page=rule_gift&pw_action_type=status&status_type=active&pw_id=' . get_the_ID()) . '"><i class="fa fa-times"></i></a>';
                            $msg_status = '<strong><span class="post-state"> — ' . __('Deactive', 'pw_wc_advanced_gift') . '</span></strong>';
                        }

                        $output .= '
                            <tr  id="' . get_the_ID() . '" data-active-status="' . $status . '">
                                <td >' . $imgstatus . '</td>
                                <td><a href="' . admin_url('admin.php?page=rule_gift&tab=add_rule&pw_action_type=edit&pw_id=' . get_the_ID()) . '">' . get_post_meta(get_the_ID(), 'pw_name', true) . '</a>' . $msg_status . '</td>
                                <td>' . get_post_meta(get_the_ID(), 'pw_from', true) . '</td>
                                <td>' . get_post_meta(get_the_ID(), 'pw_to', true) . '</td>
                                <td>' . get_the_modified_date('F j, Y g:i a') . '</td>
                                <td>
                                    <a class="pw-action-icon" href="' . admin_url('admin.php?page=rule_gift&tab=add_rule&pw_action_type=edit&pw_id=' . get_the_ID()) . '" title="Edit"><i class="fa fa-pencil-alt"></i></a>
                                    <a class="pw_delete_gift_rule pw-action-icon" href="' . admin_url('admin.php?page=rule_gift&pw_action_type=delete&pw_id=' . get_the_ID()) . '" title="Delete"><i class="fas fa-trash-alt"></i></a>
									<a class="pw_clone_gift_rule pw-action-icon" href="' . admin_url('admin.php?page=rule_gift&pw_action_type=clone&pw_id=' . get_the_ID()) . '" title="Clone"><i class="fa fa-clone"></i></a>									
                                </td>
                            </tr>';
                    }
                endwhile;
                echo $output;
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
?>