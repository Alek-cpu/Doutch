<div id="poststuff">
    <div id="postbox-container-2" class="postbox-container">
        <div class="postbox">

            <div class="inside export-target-order-status">
                <table width="100%">
                    <tr>
                        <h3 class="hndle">Map your local order status with amazon order status</h3>
                    </tr>

                </table>
                <table width="100%">
                    <tbody>
                    <tr>
                        <td width="50"></td>
                        <td><h2>Amazon Order Status </h2></td>
                        <td><h2>Woocommerce Orders Status</h2></td>
                    </tr>
                    <?php foreach ($amazon_order_status as $aos => $k) { ?>
                        <tr style="border:">
                            <td width="50"></td>
                            <td width="500"><h3> <?php echo $k; ?> <span style="float: right;margin-right: 20px;"><img
                                                src="<?php echo AMWSCPF_URL ?>/../images/rightbar.png"></span>
                                </h3></td>
                            <td width="500">
                                <select aos="<?php echo $aos; ?>" class="order-status-mapping-amazon">
                                    <option value="">Select option</option>
                                    <?php if (is_array($woo_order_status)) foreach ($woo_order_status as $key => $value) { ?>
                                        <option value="<?php echo $key ?>" <?php if (get_option('amwscp_' . str_replace(' ', '_', $aos)) == $key) {
                                            echo 'selected';
                                        } ?> > <?php echo $value ?> </option>
                                    <?php } ?>

                                </select>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

            </div>
            <!-- Order mapping Section Ends -->
        </div>
    </div>
    <div id="postbox-container-2" class="postbox-container">
        <div class="postbox">
            <div class="inside export-target-order-status">
                <table width="100%">
                    <tr>
                        <h3 class="hndle">General Setting</h3>
                    </tr>
                </table>
                <table width="100%">
                    <tr style="border:">
                        <td width="50"></td>
                        <td width="500">
                            <h3> Woo VAT Percent <span style="float: right;margin-right: 20px;">
                                <img src="<?php echo AMWSCPF_URL ?>/../images/rightbar.png"></span>
                            </h3>
                        </td>
                        <td width="500">
                            <input type="text" name="custom_vat_amount" id="custom_vat_amount"
                                   value="<?php echo get_option('amwscp_custom_vat_amount') ?>">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div id="post-body" class="metabox-holder columns-2">

        <div id="postbox-container-3" class="postbox-container" style="width: 100%;">

            <div class="meta-box-sortables ui-sortable">

                <div class="postbox" id="Product-management-box">
                    <h3 class="hndle">
                        <span>Amazon Cron Intervals Settings</span>
                    </h3>
                    <div class="inside">
                        <table style="width:100%">
                            <tbody>
                            <tr>
                                <th style="text-align: left">
                                    Order fetch interval
                                </th>
                                <?php $time_intervals = array('every_minute'=>60,'five_minute'=>300,'ten_minute'=>600,'fifteen_minute'=>900,'thirty_minute'=>1800,'one_hour'=>3600,'three_hours'=>10800,'six_hours'=>21600,'twice_daily'=>43200,'daily'=>'86400','weekly'=>604800); ?>
                                <td>
                                    <select class="cron-update" style="min-width: 30%;" name="order_fetch_interval"
                                            id="etcpf_order_fetch_interval">
                                        <option selected value=""> Choose One</option>
                                        <option value="">--------------------------------------</option>
                                        <?php foreach ($time_intervals as $key=> $time_interval) {
                                            $selected = '';
                                            if($time_interval==get_option('amwscp_order_fetch_interval'))
                                                $selected = 'selected';
                                            echo '<option '.$selected.' value="'.$time_interval.'">'.ucfirst(str_replace('_',' ',$key)).'</option>';
                                        } ?>
                                    </select></td>

                            </tr>
                            <tr>
                                <th style="text-align: left">
                                    Feed submission interval
                                </th>
                                <td>
                                    <select class="cron-update" style="min-width: 30%;" name="feed_submission_interval"
                                            id="etcpf_feed_submission_interval">
                                        <option value=""> Choose One</option>
                                        <option value="">--------------------------------------</option>
                                        <?php foreach ($time_intervals as $key=> $time_interval) {
                                            $selected = '';
                                            if($time_interval==get_option('amwscp_feed_submission_interval'))
                                                $selected = 'selected';
                                            echo '<option '.$selected.' value="'.$time_interval.'">'.ucfirst(str_replace('_',' ',$key)).'</option>';
                                        } ?>

                                    </select></td>

                            </tr>
                            <tr>
                                <th style="text-align: left">
                                    Feed update interval
                                </th>

                                <td>
                                    <select class="cron-update" style="min-width: 30%;" name="feed_update_interval" id="etcpf_feed_update_interval">
                                        <option value=""> Choose One</option>
                                        <option value="">--------------------------------------</option>
                                        <?php foreach ($time_intervals as $key=> $time_interval) {
                                            $selected = '';
                                            if($time_interval==get_option('amwscp_feed_update_interval'))
                                                $selected = 'selected';
                                            echo '<option '.$selected.' value="'.$time_interval.'">'.ucfirst(str_replace('_',' ',$key)).'</option>';
                                        } ?>
                                    </select></td>

                            </tr>

                            </tbody>
                        </table>


                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<script>
    jQuery(document).ready(function () {

        jQuery(document).on('change', '.order-status-mapping-amazon', function (event) {
            event.preventDefault();
            t = this;
            console.log(this);
            let amazon_category = jQuery(this).attr('aos'),
                woo_category = jQuery(this).val();
            let payload = {
                amazon_category: amazon_category,
                woo_category: woo_category
            };
            globalAjax(payload, function (data, error) {
                if (error) {
                    console.log(error);
                } else {
                    console.log("success");
                }
            })
        });
        
        jQuery(document).on('change', '.cron-update', function (event) {
            event.preventDefault();
            t = this;
            console.log(this);
            let amazon_category = jQuery(this).attr('name'),
                woo_category = jQuery(this).val();
            let payload = {
                amazon_category: amazon_category,
                woo_category: woo_category
            };
            globalAjax(payload, function (data, error) {
                if (error) {
                    console.log(error);
                } else {
                    console.log("success");
                }
            })
        });

        jQuery(document).on("input", '#custom_vat_amount', function (event) {
            let payload = {
                amazon_category: 'custom_vat_amount', //option name is sent as amazon_category for post compatible
                woo_category: jQuery('#custom_vat_amount').val() //option value is sent as woo_category for post compatible
            }

            globalAjax(payload, function (data, error) {
                if (error) {
                    console.log(error);
                } else {
                    console.log("success");
                }
            })
        });
    });

    var globalAjax = function (payload, callback) {
        jQuery.ajax({
            type: 'post',
            dataType: 'json',
            url: ajaxurl,
            data: {
                feedpath: amwscpf_object.cmdUpdatecategoryMapping,
                security: amwscpf_object.security,
                action: amwscpf_object.action,
                value: payload
            },
            success: function (res) {
                if (res.status) {
                    callback(res, null);
                } else {
                    callback(res, null);
                }
            },
            error: function (res) {
                callback(null, res);
            }
        });
    }
</script>
