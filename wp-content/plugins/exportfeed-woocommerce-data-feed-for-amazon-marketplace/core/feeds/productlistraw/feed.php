<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once dirname(__FILE__) . '/../basicfeed.php';
if (!class_exists('AMWSCP_PProductlistrawFeed')) {
    class AMWSCP_PProductlistrawFeed extends AMWSCP_PBasicFeed
    {

        function __construct()
        {
            parent::__construct();
            $this->providerName = 'Productlistraw';
            $this->providerNameL = 'productlistraw';
            $this->fileformat = 'txt';

        }

        function formatProduct($product)
        {

            //Images now soft-coded
            foreach ($product->imgurls as $image_count => $imgurl) {
                $product->attributes['additional_image_link' . $image_count] = $imgurl;
                if ($image_count > 9)
                    break;
            }

            $result = '
//********************************************************************
' . $product->attributes['title'] . '
//********************************************************************';

            foreach ($product->attributes as $key => $value)
                if (gettype($value) != 'array')
                    $result .= '
' . $key . ': ' . $value;

            return $result;

        }

    }
}
