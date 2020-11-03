<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
if (!class_exists('AMWSCP_FeedSettingsDialogs')) {
    class AMWSCP_FeedSettingsDialogs
    {

        public static function formatIntervalOption($value, $descriptor, $current_delay)
        {
            $selected = '';
            if ($value == $current_delay) {
                $selected = ' selected="selected"';
            }
            return '<option value="' . $value . '"' . $selected . '>' . $descriptor . '</option>';
        }

        public static function fetchRefreshIntervalSelect()
        {
            $current_delay = get_option('amwscp_feed_update_interval');
            return '
          <select name="delay" class="select_medium" id="selectDelay">' . "\r\n" .
            self::formatIntervalOption(604800, '1 Week', $current_delay) . "\r\n" .
            self::formatIntervalOption(86400, '24 Hours', $current_delay) . "\r\n" .
            self::formatIntervalOption(43200, '12 Hours', $current_delay) . "\r\n" .
            self::formatIntervalOption(21600, '6 Hours', $current_delay) . "\r\n" .
            self::formatIntervalOption(3600, '3 Hour', $current_delay) . "\r\n" .
            self::formatIntervalOption(3600, '1 Hour', $current_delay) . "\r\n" .
            self::formatIntervalOption(1800, '30 Minutes', $current_delay) . "\r\n" .
            self::formatIntervalOption(900, '15 Minutes', $current_delay) . "\r\n" .
            self::formatIntervalOption(600, '10 Minutes', $current_delay) . "\r\n" .
            self::formatIntervalOption(300, '5 Minutes', $current_delay) . "\r\n" . '
          </select>';
        }

        public static function refreshTimeOutDialog()
        {
            $check   = "";
            $display = "";
            $swtich  = get_option('amwscpf_interval_switch');
            if (true == $swtich) {
                $check = 'checked';
            } else {
                $display = 'style= "display:none"';
            }

            global $wpdb;
            return '
      <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-2" class="postbox-container">
<div class="postbox">
                  <h3 class="hndle">Feed Update Settings</h3>
                  <div class="inside export-target">
                   <table class="form-table update-table">
                      <tbody>
                        <tr>
                            <th style="width:90px;"><label>Auto Update</label></th>
                            <td>
                                <div id="cpf_switch">
                                    <label class="switch">
                                      <input type="checkbox" id="interval_switch" ' . $check . ' onclick="change_auto_update_status(this)">
                                      <div class="slider round"></div>
                                    </label>
                                </div>
                            </td>
                            <td> <div id="interval_options" ' . $display . '>
                          <label>Interval:</label>
                          <span class="update-interval"><div id="updateSettingMessage"></div>' . AMWSCP_FeedSettingsDialogs::fetchRefreshIntervalSelect() . '
                          </span>
                          <span>
                            <input class="button-primary" style="margin-left:30px;" type="submit" value="Update Interval" id="submit" name="submit" onclick="amwscp_doUpdateSetting(\'selectDelay\', \'amwscp_feed_update_interval\')">
                          </span>
                        </div>
                      </td>
                        </tr>
                      <tbody>
                    </table>
                          <div id="postbox-container-1" class="postbox-container desc-update">
                              <div class="postbox description">
                                  <div class="inside export-target">
                                      <span class="dashicons dashicons-arrow-right"></span><b>Turn on the Automatic Feed update. It helps you to update your feed in preferable time interval.</b>
                                      <br><br>
                                      <span class="dashicons dashicons-arrow-right"></span><b>Your feeds that are submitted to Amazon will also get updated in the same time interval.</b>
                                  </div>
                              </div>
                          </div>
                    <div class="manual-update"><label class="upd-txt">Made recent changes to your products?</label><input style="margin-left: 25px;" class="button-primary" type="submit" value="Update Now" id="submit" name="submit"
                        onclick="amwscp_doUpdateAllFeeds()">
                        <div id="update-message">&nbsp;</div>
                    </div>
                  </div>
                </div>
            </div>
        </div>
      </div>';
        }
    }
}
