<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!class_exists('AMWSCP_PAProduct')) {
    class AMWSCP_PAProduct
    {

        public $id = 0;
        public $title = '';
        public $taxonomy = '';
        public $imgurls;
        public $attributes;

        function __construct()
        {
            $this->imgurls = array();
            $this->attributes = array();
        }
    }
}

if (!class_exists('AMWSCP_PProductEntry')){
    class AMWSCP_PProductEntry
    {
        public $taxonomyName;
        public $ProductID;
        public $Attributes;

        function __construct()
        {
            $this->Attributes = array();
        }

        function GetAttributeList()
        {
            $result = '';
            foreach ($this->Attributes as $ThisAttribute) {
                $result .= $ThisAttribute . ', ';
            }
            return '[' . $this->Name . '] ' . substr($result, 0, -2);
        }
    }
}
global $amwcore;
$productListScript = 'productlist' . strtolower($amwcore->callSuffix) . '.php';
require_once $productListScript;