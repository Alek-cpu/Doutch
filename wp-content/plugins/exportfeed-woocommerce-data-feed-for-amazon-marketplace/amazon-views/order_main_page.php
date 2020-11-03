<?php
global $amwcore;
require_once AMWSCPF_PATH . "/core/classes/tables/Order_Table.php";
$orders = new AMWS_Order_Table();
$orders->prepare_items();
global $wpdb;
$table = $wpdb->prefix . "amwscp_orders";
// $sql = $wpdb->prepare("SELECT * FROM $table");
$sql = "SELECT DISTINCT status FROM $table ";
$statuses = $wpdb->get_results($sql);
?>
<div class="wrap">
    <!-- <div id="setting-error-settings_updated" class="updated settings-error">
      <p>Please check all the orders below</p>
    </div> -->
    <div id="poststuff">
        <!-- Ajax Loader div -->
        <div style="display: none;" id="ajax-loader-cat-import"></div>

        <!-- Order mapping Section -->

        <div id="postbox-container-2" class="postbox-container">
            <div class="postbox">

                <div id="postbox-container-2" class="postbox-container">
                    <div class="postbox">
                        <h3 class="hndle">Interval at which amazon order auto-refreshes</h3>
                        <div class="inside export-target">
                            <table class="form-table">
                                <tbody>
                                <tr>
                                    <th style="width:90px;"><label>Auto Update</label></th>
                                    <td>
                                        <div id="cpf_switch">
                                            <label class="switch">
                                                <input type="checkbox" id="interval_switch" checked=""
                                                       onclick="change_auto_update_status(this)">
                                                <div class="slider round"></div>
                                            </label>
                                        </div>
                                    </td>
                                </tr>

                                <tr id="interval_options">
                                    <th style="width:90px;"><label>Interval:</label></th>
                                    <td style="width:120px;">
                                        <?php echo AMWS_Order_Table::fetchRefreshIntervalSelect(); ?>
                                        <div id="updateSettingMessage"></div>
                                    </td>
                                    <td>
                                        <input class="button-primary" style="margin-left:30px; float:left;"
                                               type="submit" value="Update Interval" id="submit" name="submit"
                                               onclick="amwscp_doUpdateSetting('selectDelay', 'amwscp_order_fetch_interval')">
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
                <div id="post-body" class="metabox-holder columns-2" style="margin-bottom: 45px;">
                    <div id="postbox-container-2" class="postbox-container">
                        <select id="amwscp_update_before">
                            <option value="">Since latest order received</option>
                            <option value="1">1 Days</option>
                            <option value="2">2 Days</option>
                            <option value="3">3 Days</option>
                            <option value="5">5 Days</option>
                            <option value="6">8 Days</option>
                            <option value="14">2 Weeks</option>
                            <option value="30">1 Month</option>
                            <option value="90">3 Months</option>
                            <option value="182">6 Months</option>
                            <option value="365">1 Years</option>
                        </select>
                        <button class="button button-primary" onclick="amwscpf_update_orders()">Update Orders</button>
                        <?php $amwcore->amwscpf_loader_2('amwscpf_update_order') ?>
                        <p id="amwscp_order_update_msg"></p>
                    </div>
                </div>

                <!-- Added table -->
                <!--<div class="postbox">
                    <div class="tableOptions">
                        <p class="tableSort">
                            <span class="active">All</span>|<span>Shipped</span>|<span>Unshipped</span>|<span>Canceled</span>|<span>Pending</span>|<span>In Amazon</span>|<span>Not in Amazon</span>
                            <span class="searchTable"><input type="text"><input class="submit" type="submit" value="Search"></span>
                        </p>
                        <p class="tableAction">
                            <span class="bulkAction">
                                <select style="width:185px">
                                    <option selected hidden>Bulk Action</option>
                                    <option>Delete</option>
                                    <option>Move</option>
                                </select>
                                <input class="submit" type="submit" value="Apply">
                            </span>
                            <span class="accountFilter">
                                <select>
                                    <option>All accounts</option>
                                </select>
                                <input class="submit" type="submit" value="Filter">
                            </span>
                        </p>
                    </div>
                    <div class="amazon_Buyer_wrapper am-order-items-editable">
                        <table class="amazon_buyer_list">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="order_select_all_checkbox" onclick="selectAllProducts()"></th>
                                    <th><a>Created at</a></th>
                                    <th>Buyer</th>
                                    <th>Total</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Order ID</th>
                                    <th><a>Last change</a></th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td><input type="checkbox"></td>
                                    <td>
                                        <p>July 3, 2018</p>
                                        <p class="subText">0:47</p>
                                    </td>
                                    <td>
                                        <p>Whan J. Joh</p>
                                        <p class="subText">Placed on Amazon.com</p>
                                        <p class="orderName">Order 113-7129305-0560205 is processing</p>
                                        <p class="showOnHover"><a>Details</a>|<a>View Order</a></p>
                                    </td>
                                    <td>
                                        <p>33.7 USD</p>
                                        <p class="subText">1 item</p>
                                    </td>
                                    <td>
                                        <p>Other</p>
                                    </td>
                                    <td>
                                        <p>Unshipped</p>
                                    </td>
                                    <td>
                                        <p>113-7129305-0560205</p>
                                        <p class="subText">Starbase One(US)</p>
                                    </td>
                                    <td>
                                        <p>July 3, 2018</p>
                                        <p class="subText">04:17</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox"></td>
                                    <td>
                                        <p>July 3, 2018</p>
                                        <p class="subText">0:47</p>
                                    </td>
                                    <td>
                                        <p>Whan J. Joh</p>
                                        <p class="subText">Placed on Amazon.com</p>
                                        <p class="orderName">Order 113-7129305-0560205 is processing</p>
                                        <p class="showOnHover"><a>Details</a>|<a>View Order</a></p>
                                    </td>
                                    <td>
                                        <p>33.7 USD</p>
                                        <p class="subText">1 item</p>
                                    </td>
                                    <td>
                                        <p>Other</p>
                                    </td>
                                    <td>
                                        <p class="shipped">Shipped</p>
                                    </td>
                                    <td>
                                        <p>113-7129305-0560205</p>
                                        <p class="subText">Starbase One(US)</p>
                                    </td>
                                    <td>
                                        <p>July 3, 2018</p>
                                        <p class="subText">04:17</p>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th><input type="checkbox" id="order_select_all_checkbox_footer" onclick="selectAllProductsFooter()"></th>
                                    <th><a>Created at</a></th>
                                    <th>Buyer</th>
                                    <th>Total</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Order ID</th>
                                    <th><a>Last change</a></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="tableOptions" style="margin-top: 0px;">
                        <p class="tableAction">
                            <span class="bulkAction">
                                <select style="width:185px">
                                    <option selected hidden>Bulk Action</option>
                                    <option>Delete</option>
                                    <option>Move</option>
                                </select>
                                <input class="submit" type="submit" value="Apply">
                            </span>
                        </p>
                    </div>
                </div>-->

                <div id="postbox-container-3" class="postbox-container">
                    <div class="order-info">
                        <h3>The orders your shop has received from Amazon are listed below.<br>
                            You can manually search for orders according to Amazon order ID or order status using the
                            "Search" button</h3>
                        <div id="search-order">
                            <!--  <form id="search-form"> -->
                            <input id="search-type" type="hidden" name="searchtype" value="byamazonorderid">
                            <input id="search-keyword" type="text" name="ordersearchbox" value=""
                                   placeholder="Search by amazon order ID">
                            <!-- <select id="searchbystatus" type="text" name="ordersearchbox" value = "" placeholder="Search by order status"> -->
                            <select id="searchbystatus" name="ordersearchbox">
                                <option value="">Select Order Status</option>
                                <?php foreach ($statuses as $status) { ?>
                                    <option value="<?php echo $status->status ?>"> <?php echo $status->status ?> </option>
                                <?php } ?>
                            </select>
                            <input class="button-primary" id="searchbtn" type="button" name="amazon_order_id"
                                   value="Search" onclick="return amwscp_doSearchOrder('search-form')">
                            <!-- update orders -->
                        </div>
                    </div>
                </div>
                <!-- </form> -->
                <form id="amwscpf-order-filter" method="get">
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                    <?php $orders->display() ?>
                </form>
            </div>
        </div>
        <script type="text/javascript">
            jQuery('.tablenav').show();
        </script>
