<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!class_exists('AMWSCPF_EditFeedDialog')) {
    class AMWSCPF_EditFeedDialog
    {

        public static function pageBody($feed_id, $disabled = false)
        {

            require_once dirname(__FILE__) . '/../data/savedfeed.php';
            require_once 'dialogbasefeed.php';

            if ($feed_id == 0)
                return;

            $feed = new AMWSCPF_SavedFeed($feed_id);
            $feed->disabled = $disabled;
            //Figure out the dialog for the provider
            $dialog_file = dirname(__FILE__) . '/../feeds/' . strtolower($feed->provider) . '/dialognew.php';
            if (file_exists($dialog_file))
                require_once $dialog_file;

            //instanciating template details
            echo '<script type="text/javascript">
            amwscpf_object.feed_product_type = "' . $feed->feed_product_type . '";
            amwscpf_object.disabled = "' . $feed->disabled . '";
        </script>';

            //Instantiate the dialog
            $provider = 'AMWSCP_'.$feed->provider . 'Dlg';
            $provider_dialog = new $provider();
          
            echo $provider_dialog->mainDialog($feed);

        }

    }
}
