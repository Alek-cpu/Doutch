<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if (!class_exists('AMWSCP_ProductlistrawDlg')){
    class AMWSCP_ProductlistrawDlg extends AMWSCP_PBaseFeedDialog {

        function __construct() {
            parent::__construct();
            $this->service_name = 'Productlistraw';
            $this->service_name_long = 'Product List RAW Export';
            $this->options = array();
            $this->blockCategoryList = true;
        }

        function categoryList($initial_remote_category){
            return '<input type = "hidden" id = "remote_category" name = "remote_category" value = "undefined" > ';
        }
    }
}