<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
if (!class_exists('AMWSCPF_FeedPageDialogs')) {
    class AMWSCPF_FeedPageDialogs
    {

        public static function pageHeader()
        {
            define('IMAGE_PATH', plugins_url('/', __FILE__) . '../../images/');
            global $amwcore;

            $gap = '
            <div style="float:left; width: 50px;">
                &nbsp;
            </div>';

            if ($amwcore->cmsName == 'WordPress') {
                $reg = new AMWSCPF_License();
                if ($reg->valid) {
                    $lic = '<div class="logo-am" style="vertical-align: middle; display: inline-block;">
                <h4 class="icon-margin">Get standalone plugin for</h4>
                <div class="upsell-icon-logo">
                <div class="logo ebay" style="display:inline-block;">
                    <div class="ebay">
                        <a value="" href="https://www.exportfeed.com/woocommerce-product-feed/send-woocommerce-data-feeds-to-ebay-seller/" target="_blank">

                            <img src="' . IMAGE_PATH . 'ebay.png">
                        </a>
                        <span class="plugin-link"><a href="https://www.exportfeed.com/woocommerce-product-feed/send-woocommerce-data-feeds-to-ebay-seller/" target="_blank">Get eBay plugin</a></span>
                        <span class="plugin-desc">Bulk product uploads and variations to eBay</span>
                    </div>
                </div>

                <div class="logo etsy" style="display:inline-block;">

                    <div class="etsy">
                        <a value="" href="https://www.exportfeed.com/woocommerce-product-feed/woocommerce-product-feeds-to-etsy/" target="_blank">

                            <img src="' . IMAGE_PATH . '/etsy.png">
                    </a>
                    <span class="plugin-link"><a href="https://www.exportfeed.com/woocommerce-product-feed/woocommerce-product-feeds-to-etsy/" target="_blank">Get Etsy plugin</a></span>
                    <span class="plugin-desc">Bulk product uploads with multiple images</span>
                    </div>
                </div>
                </div>

                <div class="clear"></div>

            </div>';
                } else {
                    $lic = AMWSCP_PLicenseKeyDialog::small_registration_dialog('');
                }

            } else {
                $lic = '';
            }

            $providers = new AMWSCP_PProviderList();

            $output = '
            <div class="postbox" style="width:98%;">
                <div class="inside-export-target">
                    <div class="select-merchant">
                        <h4>Feed Type</h4>
                        <select id="selectFeedType" onchange="amwscp_doSelectFeed();">' . $providers->asOptionList() . '
                        </select>
                    </div>
                    ' . $lic . '
                </div>
            </div>
            <div class="clear"></div>';

            return $output;

        }

        public static function pageBody()
        {
            $output = '

      <div id="feedPageBody" class="postbox" style="width: 98%;">
        <div class="inside export-target">
          <h4>No feed type selected.</h4>
          <hr />
        </div>
      </div>
      ';
            return $output;
        }

    }
}
