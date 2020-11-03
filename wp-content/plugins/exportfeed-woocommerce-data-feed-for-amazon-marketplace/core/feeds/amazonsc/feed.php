<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
/*** Changes to categories.txt should be relfected in initializeTemplateData() ***/

require_once dirname(__FILE__) . '/../basicfeed.php';
if (!class_exists('AMWSCP_PAmazonSCFeed')) {
    class AMWSCP_PAmazonSCFeed extends AMWSCP_PCSVFeed
    {
        public $feed_product_type = '';
        public $headerTemplateType; //Attached to the top of the feed
        public $headerTemplateVersion;
        public $templateLoaded = false;
        public $current_tpl;
        public $feed_data = array();
        public $tpl_id;
        public $template;
        public $error_message;
        
        public function __construct()
        {
            parent::__construct();
            $this->providerName = 'AmazonSC';
            $this->providerNameL = 'amazonsc';
            $this->fileformat = 'txt';
            $this->fields = array();
            $this->fieldDelimiter = "\t";
            
            $this->external_product_id_type = '';
            $this->stripHTML = true;
            
            $this->addAttributeDefault('price', 'none', 'AMWSCP_PSalePriceIfDefined');
            $this->addAttributeDefault('local_category', 'none', 'AMWSCP_PCategoryTree'); //store's local category tree
            $this->addRule('price_rounding', 'pricerounding');
            $this->addRule('exclude_oos', 'excludeoos');
            // below is applied in basicfeed.php
            // $this->addRule('description', 'description', array('strict'));
            // $this->addRule( 'csv_standard', 'CSVStandard',array('title') );
            // $this->addRule( 'csv_standard', 'CSVStandard',array('description') );
        }
        
        public function loadTemplate($template)
        {
            $amazon_templates_attributes_to_be_mapped = '';
            if ($template) {
                
                $this->initializeTemplateData($template);
                
                //lower case headertemplatetype to match templateType.php
                $thisHeaderTemplateType = strtolower($this->headerTemplateType);
                /* 
                For now we don't do this, we rather parse the data and perferm our operation
                
                $url = 'https://services.exportfeed.com/init.php';
                $postfields = array(
                    'fetch' => 'amwscp_amazon_template_values',
                    'id' => $template->id,
                    'flat_tmpl_id' => isset($template->flat_tmpl_id) ? $template->flat_tmpl_id : '',
                    'country' => $template->country,
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                curl_setopt($ch, CURLOPT_TIMEOUT, 100);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $data = curl_exec($ch);

                $data = json_decode($data);
                
                if (curl_errno($ch)) {
                    $this->error_message = 'Curl error: ' . curl_error($ch);
                }

                curl_close($ch);
                $amazon_templates_attributes_to_be_mapped = $data->results;*/
                
                $amazon_templates_attributes_to_be_mapped = $this->parse_and_get_amazon_attributes_from_template_raw($template);
                
            }
            
            if (is_array($amazon_templates_attributes_to_be_mapped) && count($amazon_templates_attributes_to_be_mapped) > 0) {
                foreach ($amazon_templates_attributes_to_be_mapped as $key => $column) {
                    if ($column == 'item_sku') {
                        $this->addAttributeMapping('sku', $column, false, true)->localized_name = $column;
                    } elseif ($column == 'item_name') {
                        $this->addAttributeMapping('title', $column, false, true)->localized_name = $column;
                    } elseif ($column == 'brand_name' ||
                        $column == 'manufacturer'
                    ) {
                        $this->addAttributeMapping('brand', $column, false, true)->localized_name = $column;
                    } elseif ($column == 'external_product_id' ||
                        $column == 'product-id-number' ||
                        $column == 'product-id'
                    ) {
                        $this->addAttributeMapping('upc', $column, false, true)->localized_name = $column;
                    } elseif ($column == 'standard_price') {
                        $this->addAttributeMapping('regular_price', $column, false, true)->localized_name = $column;
                    } elseif ($column == 'product_description') {
                        $this->addAttributeMapping('description_short', $column, true, true)->localized_name = $column;
                    } elseif ($column == 'main_image_url') {
                        $this->addAttributeMapping('feature_imgurl', $column, false, true)->localized_name = $column;
                    } elseif ($column == 'item_quantity' ||
                        $column == 'quantity'
                    ) {
                        $this->addAttributeMapping('stock_quantity', $column, false, true)->localized_name = $column;
                    } elseif ($column == 'item_length') {
                        $this->addAttributeMapping('length', $column, false, true)->localized_name = $column;
                    } elseif ($column == 'item_width') {
                        $this->addAttributeMapping('width', $column, false, true)->localized_name = $column;
                    } elseif ($column == 'item_height') {
                        $this->addAttributeMapping('height', $column, false, true)->localized_name = $column;
                    } elseif ($column == 'external_product_id_type' ||
                        $column == 'product-id-number-type' ||
                        $column == 'product-id-type'
                    ) {
                        $this->addAttributeMapping($column, $column, true)->localized_name = $column;
                    } elseif ($column == 'bullet_point1') {
                        $this->addAttributeMapping('bullet_point1', $column, true, false)->localized_name = $column;
                    } elseif ($column == 'bullet_point2') {
                        $this->addAttributeMapping('bullet_point2', $column, true, false)->localized_name = $column;
                    } elseif ($column == 'bullet_point3') {
                        $this->addAttributeMapping('bullet_point3', $column, true, false)->localized_name = $column;
                    } elseif ($column == 'bullet_point4') {
                        $this->addAttributeMapping('bullet_point4', $column, true, false)->localized_name = $column;
                    } elseif ($column == 'bullet_point5') {
                        $this->addAttributeMapping('bullet_point5', $column, true, false)->localized_name = $column;
                    } elseif ($column == 'other_image_url1') {
                        $this->addAttributeMapping($column, $column, true, false)->localized_name = $column;
                    } elseif ($column == 'other_image_url2') {
                        $this->addAttributeMapping($column, $column, true, false)->localized_name = $column;
                    } elseif ($column == 'other_image_url3') {
                        $this->addAttributeMapping($column, $column, true, false)->localized_name = $column;
                    } elseif ($column == 'other_image_url4') {
                        $this->addAttributeMapping($column, $column, true, false)->localized_name = $column;
                    } elseif ($column == 'other_image_url5') {
                        $this->addAttributeMapping($column, $column, true, false)->localized_name = $column;
                    } elseif ($column == 'sale_price') {
                        $this->addAttributeMapping('sale_price', $column, true, false)->localized_name = $column;
                    } elseif ($column == 'sale_price_dates_from') {
                        $this->addAttributeMapping('sale_price_dates_from', $column, true, false)->localized_name = $column;
                    } elseif ($column == 'sale_price_dates_to') {
                        $this->addAttributeMapping('sale_price_dates_to', $column, true, false)->localized_name = $column;
                    } elseif ($column == 'parent_child') {
                        $this->addAttributeMapping('parent_child', $column, true, false)->localized_name = $column;
                    } elseif ($column == 'parent_sku') {
                        $this->addAttributeMapping('parent_sku', $column, true, false)->localized_name = $column;
                    } elseif ($column == 'variation_theme') {
                        $this->addAttributeMapping('variation_theme', $column, true, false)->localized_name = $column;
                    } elseif ($column == 'relationship_type') {
                        $this->addAttributeMapping('relationship_type', $column, true, false)->localized_name = $column;
                    } elseif ($column == 'recommended_browse_nodes') {
                        $this->addAttributeMapping('recommended_browse_nodes', $column, true, false)->localized_name = $column;
                    } elseif ($column == 'recommended_browse_nodes1' || $column == 'recommended_browse_nodes2') {
                        $this->addAttributeMapping('recommended_browse_nodes', $column, true, false)->localized_name = $column;
                    } else {
                        $this->addAttributeMapping($column, $column, false, false)->localized_name = $column;
                    }
                }
            }
            if (isset($thisHeaderTemplateType) && $thisHeaderTemplateType == 'offer') {
                $this->addAttributeMapping('sku', 'sku', false, true, true)->localized_name = 'SKU';
                $this->addAttributeMapping('price', 'price', false, true, true)->localized_name = 'Price';
                $this->addAttributeMapping('stock_quantity', 'quantity', false, true, true)->localized_name = 'Quantity';
                $this->addAttributeMapping('upc', 'product-id', false, true, true)->localized_name = 'Product ID';
                $this->addAttributeMapping('', 'product-id-type', false, true, true)->localized_name = 'Product ID Type';
                $this->addAttributeMapping('', 'condition-type', false, true, true)->localized_name = 'Condition Type';
                $this->addAttributeMapping('', 'condition-note', false, true, true)->localized_name = 'Condition Note';
                $this->addAttributeMapping('', 'ASIN-hint', false, false, false)->localized_name = 'ASIN-hint';
                $this->addAttributeMapping('title', 'title', false, false, true)->localized_name = 'Item Name';
                $this->addAttributeMapping('', 'product-tax-code', false, false, false)->localized_name = 'Tax Code';
                $this->addAttributeMapping('', 'operation-type', false, false, false)->localized_name = 'Operation Type';
                $this->addAttributeMapping('', 'sale-price', false, false, true)->localized_name = 'Sale Price';
                $this->addAttributeMapping('', 'sale-start-date', false, false, false)->localized_name = 'Sale Start Date';
                $this->addAttributeMapping('', 'sale-end-date', false, false, false)->localized_name = 'Sale End Date';
                $this->addAttributeMapping('', 'leadtime-to-ship', false, false, false)->localized_name = 'Shipping Time';
                $this->addAttributeMapping('', 'launch-date', false, false, false)->localized_name = 'Release Date';
                $this->addAttributeMapping('', 'is-giftwrap-available', false, false, false)->localized_name = 'Is Giftwrap Available';
                $this->addAttributeMapping('', 'is-gift-message-available', false, false, false)->localized_name = 'Is Gift Message Available';
                $this->addAttributeMapping('', 'fulfillment-center-id', false, false, false)->localized_name = 'Fulfillment Center Id';
                $this->addAttributeMapping('feature_imgurl', 'main-offer-image', false, false, true)->localized_name = 'Main Offer Image';
                
                for ($i = 1; $i <= 5; $i++) {
                    $this->addAttributeMapping('other_image_url' . $i, 'offer-image' . $i, false, true, true)->localized_name = 'Offer Image' . $i;
                }
            }
            //Note external_product_id_type is generated on the fly, under formatProducts function
            //$this->addAttributeMapping('external_product_id_type', 'external_product_id_type')->localized_name = 'Product ID Type';
            //item_type: please refer to BTG13
            $this->templateLoaded = true;
            $this->loadAttributeUserMap();
        }
        
        public function formatProduct($product)
        {
            global $wpdb;
            $feed_id = $this->currentFeedId;
            $table_feed = $wpdb->prefix . 'amwscp_feeds';
            $result = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_feed WHERE id=%d", $feed_id)
            );
            if ($result) {
                $variation_theme = $result->variation_theme;
                $feed_product_type = $result->feed_product_type;
                $recommended_browse_nodes = $result->recommended_browse_nodes;
                $item_type_keyword = $result->item_type_keyword;
            }
            /*========================================================================================================
             * if($product->attributes['isVariation']){
                echo "<pre>";
                print_r($product);
                echo "</pre>";
                exit();
            }
            ========================================================================================================*/
            
            global $amwcore; //required to set sale_date_from/to
            
            if (!$this->templateLoaded) {
                $this->loadTemplate();
            }
            
            //********************************************************************
            //Prepare the product
            //********************************************************************
            // product sku - auto generate
            if (array_key_exists('sku', $product->attributes) && !isset($product->attributes['sku'])) {
                $sku = strtoupper(substr(str_replace(' ', '', $product->attributes['title']), 0, 11)) . $product->id;
                update_post_meta($product->id, '_sku', $sku);
                $product->attributes['sku'] = $sku;
            }
            if (array_key_exists('isVariable', $product->attributes) && isset($product->attributes['isVariable']) && (array_key_exists('parent_child', $product->attributes) && isset($product->attributes['parent_child']) && $product->attributes['parent_child'] == 'parent')) {
                if (array_key_exists('$product->attributes', $product->attributes)) {
                    $product->attributes['regular_price'] = $product->attributes['min_variation_price'];
                }
                
                // put regular price blank
            }
            $product->attributes['category'] = $this->current_category;
            
            if (empty($product->attributes['variation_theme'])) {
                $product->attributes['variation_theme'] = $variation_theme;
            }
            //remove variation theme from single product
            if (isset($product->attributes['isVariable']) && $product->attributes['isVariable'] != 1) {
                $product->attributes['variation_theme'] = '';
            }
            
            //sometimes templates only have one feed_product_type
            $this->feed_product_type = $feed_product_type;
            if (isset($this->feed_product_type) && strlen($this->feed_product_type) > 0) //if (isset($product->attributes['feed_product_type']) && (strlen($product->attributes['feed_product_type']) == 0) )
            {
                $product->attributes['feed_product_type'] = $this->feed_product_type;
            }
            
            /*
            fix missing brand error (customized error)
            if (isset($product->attributes['brand']))
            $product->attributes['brand_name'] = $product->attributes['brand'];
             */
            
            //default values
            if (!isset($product->attributes['item_package_quantity'])) //The number of individiually packaged units/distinct items in a package
            {
                $product->attributes['item_package_quantity'] = 1;
            }
            
            if (!isset($product->attributes['number_of_items'])) {
                $product->attributes['number_of_items'] = 1;
            }
            //Number of items included in a single package labeled for individual sale
            if (!isset($product->attributes['handling_time'])) {
                $product->attributes['handling_time'] = 2;
            }
            //Indicates the time, in days, between when you receive an order for an item and when you can ship the item.
            // if ( !isset($product->attributes['feed_product_type']) )
            //      $product->attributes['feed_product_type'] = 'Feed Product Type value required. Refer to Inventory Template.';
            if (!isset($product->attributes['item_type'])) {
                $product->attributes['item_type'] = 'Item Type Keyword required. Please refer to Template\'s BTG.';
            }
            
            /*if ( !isset($product->attributes['item-type-keyword']) )
            $product->attributes['item-type-keyword'] = 'Item Type Keyword required. Please refer to Template\'s BTG.';*/
            
            //remove s from https
            /*if (strpos($product->attributes['feature_imgurl'], 'https') !== false) {
            $product->attributes['feature_imgurl'] = str_replace('https://', 'http://', $product->attributes['feature_imgurl']);
            //Warn user because server might not be listening for http connections
            //$this->addErrorMessage(<Shopzilla Range Warning + 1>, 'Converted an https image url http ' . $product->attributes['title'] . image url);
            }*/
            
            $image_count = 1;
            foreach ($product->imgurls as $imgurl) {
                $image_index = "other_image_url$image_count";
                $product->attributes[$image_index] = $imgurl;
                $image_count++;
                if ($image_count >= 9) {
                    break;
                }
                
            }
            
            /*** sale price and sale price dates ***/
            if ($product->attributes['has_sale_price']) {
                if (isset($product->attributes['sale_price_dates_from']) && isset($product->attributes['sale_price_dates_to'])) {
                    $product->attributes['sale_from_date'] = $amwcore->localizedDate('Y-m-d', $product->attributes['sale_price_dates_from']);
                    $product->attributes['sale_end_date'] = $amwcore->localizedDate('Y-m-d', $product->attributes['sale_price_dates_to']);
                } else //sale price is set, but no schedule.
                {
                    // $product->attributes['regular_price'] = $product->attributes['sale_price'];
                    $product->attributes['sale_price'] = $product->attributes['sale_price'];
                }
            }
            
            if (array_key_exists('parent_child', $product->attributes) && $product->attributes['parent_child'] == "parent" && $product->attributes['isVariable']) {
                $product->attributes['regular_price'] = "";
            }
            
            $product->attributes['shipping_cost'] = '0.00';
            $product->attributes['shipping_weight'] = $product->attributes['weight'];
            
            //********************************************************************
            //Validation checks & Error messages
            //********************************************************************
            
            if (isset($product->attributes['description']) && strlen($product->attributes['description']) > 2000) {
                $product->attributes['description'] = substr($product->attributes['description'], 0, 2000);
                $this->addErrorMessage(8000, 'Description truncated for ' . $product->attributes['title'], true);
            }
            if (isset($product->attributes['sku']) && strlen($product->attributes['sku']) == 0) {
                $this->addErrorMessage(8000, 'Sku Missing so automatically generated for ' . $product->attributes['title'], true);
                $product->attributes['sku'] = str_replace(' ', '-', substr($product->attributes['title'], 0, 10) . $product->attributes['id']);
            }
            if (!isset($product->attributes['product_description'])) {
                $product->attributes['product_description'] = '';
            }
            
            /**======================================================================================================
             * if (!isset($product->attributes['brand']) || (strlen($product->attributes['brand']) == 0))
             * $this->addErrorMessage(8001, 'Brand not set for ' . $product->attributes['title'], true);
             * if (($this->external_product_id_type == 'UPC') && (strlen($product->attributes['external_product_id']) == 0))
             * $this->addErrorMessage(8002, 'external_product_id not set for ' . $product->attributes['title'], true);
             * if (($this->template == 'health') && (strlen($product->attributes['manufacturer']) == 0))
             * $this->addErrorMessage(8003, 'Manufacturer not set for ' . $product->attributes['title'], true);
             * 8004 seems a bit too aggressive
             * if ($product->attributes['has_sale_price'] && (!isset($product->attributes['sale_from_date']) || !isset($product->attributes['sale_end_date'])))
             * $this->addErrorMessage(8004, 'Sale price set for ' . $product->attributes['title'] . ' but no sale_from_date and/or sale_end_date provided', true);
             * ==========================================================================================================*/
            
            
            //********************************************************************
            //Trigger Mapping 3.0 Before-Feed Event
            //********************************************************************
            #if ($this->tpl_id)
            #$this->saveAttributes($product);
            
            foreach ($this->attributeDefaults as $thisDefault) {
                if ($thisDefault->stage == 2) {
                    $product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);
                }
            }
            
            //$parent_sku = $product->attributes['parent_sku'];
            //********************************************************************
            //Build output in order of fields
            //********************************************************************
            
            $output = '';
            $feed_data = array();
            
            foreach ($this->attributeMappings as $key => $value) {
                if ($value->usesCData) {
                    $quotes = '"';
                } else {
                    $quotes = '';
                }
                
                if ($value->enabled && !$value->deleted) {
                    
                    if ($value->mapTo == 'external_product_id' ||
                        $value->mapTo == 'product-id-number' ||
                        $value->mapTo == 'product-id' ||
                        $value->mapTo == 'shop-id'
                    ) {
                        if (!isset($product->attributes[$value->attributeName])) {
                            $product->attributes[$value->attributeName] = '';
                        }
                        //Should probably warn the user there's a bad product here -KH:2014-12
                        //output external_product_id (12,13 digit code)
                        //$output .= $quotes . $product->attributes[$value->attributeName] . $quotes;
                        //$output .= $this->fieldDelimiter;
                        //convert digit to string and get the length. Depending on the length, product id type can be upc, ean or gcid...
                        $productId_strlen = strlen((string)$product->attributes[$value->attributeName]);
                        switch ($productId_strlen) {
                            case 10:
                                $this->external_product_id_type = 'ASIN'; //10 digit Amazon number
                                break;
                            case 11:
                                $this->external_product_id_type = 'UPC'; //11 digit UPC (start with 0)
                                //                            $product->attributes[$value->attributeName] = '0' . $product->attributes[$value->attributeName];
                                break;
                            case 12:
                                $this->external_product_id_type = 'UPC'; //12 digit UPC
                                break;
                            case 13:
                                $this->external_product_id_type = 'EAN'; //13 digit EAN
                                break;
                            case 14:
                                $this->external_product_id_type = 'EAN'; //14 digit EAN
                                break;
                            case 16:
                                $this->external_product_id_type = 'GCID'; //16 digit GCID
                                break;
                            default:
                                $this->external_product_id_type = ''; //valid ASIN, UPC or EAN required for product id
                        }
                        $output .= $quotes . str_replace("\t", ' ', $product->attributes[$value->attributeName]) . $quotes;
                        $output .= $this->fieldDelimiter;
                        continue;
                    }
                    
                    if ($value->mapTo == 'external_product_id_type' ||
                        $value->mapTo == 'product-id-number-type' ||
                        $value->mapTo == 'product-id-type'
                    ) {
                        
                        $feed_data[$value->mapTo] = $this->external_product_id_type;
                        $output .= $quotes . str_replace("\t", ' ', $this->external_product_id_type) . $quotes;
                        $output .= $this->fieldDelimiter;
                        continue;
                        
                    } elseif ($value->mapTo == 'feed_product_type' || $value->mapTo == 'product_subtype') {
                        if(empty($this->feed_product_type))
                            $this->feed_product_type = $product->attributes[$value->attributeName];
                        $feed_data[$value->mapTo] = $this->feed_product_type;
                        $output .= $quotes . $this->feed_product_type . $quotes;
                        
                    } elseif ($value->mapTo == 'recommended_browse_nodes' || $value->mapTo == 'recommended_browse_nodes1' || $value->mapTo == 'recommended_browse_nodes2') {
                        $this->recommended_browse_nodes = $recommended_browse_nodes;
                        // print_r(get_option($this->current_tpl . '_recommended_browse_nodes'));exit;
                        
                        $feed_data[$value->mapTo] = $this->recommended_browse_nodes;
                        $output .= $quotes . $this->recommended_browse_nodes . $quotes;
                        
                    } elseif ($value->mapTo == 'item_type_keyword' ||
                        $value->mapTo == 'product_subtype' ||
                        $value->mapTo == 'item_type'
                    ) {
                        $this->item_type_keyword = $item_type_keyword;
                        $feed_data[$value->mapTo] = $this->item_type_keyword;
                        $output .= $quotes . trim(preg_replace('/\t+/', '', $this->item_type_keyword)) . $quotes;
                        
                    } elseif (isset($product->attributes[$value->attributeName])) {
                        $feed_data[$value->mapTo] = $product->attributes[$value->attributeName];
                        $output .= $quotes . trim(preg_replace('/\t+/', '', $product->attributes[$value->attributeName])) . $quotes;
                    } else {
                        $output .= ' ';
                    }
                    
                    $output .= $this->fieldDelimiter;
                    
                }
            }
            
            //********************************************************************
            //Trigger Mapping 3.0 After-Feed Event
            //********************************************************************
            foreach ($this->attributeDefaults as $thisDefault) {
                if ($thisDefault->stage == 3) {
                    $thisDefault->postProcess($product, $output);
                }
            }
            
            $this->saveAttributes($feed_data, $product->id);
            return substr($output, 0, -1) . "\r\n";
            
        } //format Product
        
        public function saveAttributes($feed_data, $pID)
        {
            update_post_meta($pID, '_amwscpf_template', $this->tpl_id);
            // add_post_meta($pID , '_amwscpf_feed_data_'.$this->headerTemplateType , maybe_serialize($feed_data));
            $this->feed_data[$pID] = $feed_data;
        }
        
        //First row of txt/csv. Requires getFeedFooter otherwise attributeMappings will output twice
        public function getFeedHeader($file_name, $file_path)
        {
            return $this->template;
        }
        
        public function initializeTemplateData($template)
        {
            #echo "<pre>";print_r($template);die;
            if (is_object($template)) {
                $this->tpl_id = $template->id;
                $this->template = utf8_decode($template->raw);
                $this->headerTemplateType = $template->tpl_name;
                $this->headerTemplateVersion = $template->version;
                $this->feed_product_type = $template->feed_product_type;
                $this->recommended_browse_nodes = $template->recommended_browse_nodes;
                $this->item_type_keyword = $template->item_type_keyword;
            } else {
                $this->tpl_id = null;
                $this->template = null;
                $this->headerTemplateType = null;
                $this->headerTemplateVersion = null;
                $this->feed_product_type = null;
                $this->recommended_browse_nodes = null;
                $this->item_type_keyword = null;
            }
            
        }
        
        public function initializeFeed($category, $remote_category)
        {
            if ($remote_category == 'listingloader') {
                $tpl = new stdClass();
                $tpl->id = '';
                $tpl->feed_product_type = 'listingloader';
                $tpl->recommended_browse_nodes = '';
                $tpl->item_type_keyword = '';
                $tpl->raw = $this->getListingLoaderTemplate($remote_category);
                $tpl->tpl_name = 'Offer';
                $tpl->version = '2014.0703';
                $tpl->country = '';
            } else {
                $this->current_tpl = $remote_category;
                $remote_category = explode('_', $remote_category); // [0] => tpl_name [1] => country
                /*if($remote_category[1]=='MX'){
                    $remote_category[1] = 'US';
                }*/
                $url = 'https://services.exportfeed.com/init.php';
                $postfields = array(
                    'fetch' => 'amwscp_amazon_services_templates',
                    'tpl_name' => $remote_category[0],
                    'country' => $remote_category[1],
                );
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                curl_setopt($ch, CURLOPT_TIMEOUT, 200);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $data = curl_exec($ch);
                $data = json_decode($data);
                $tpl_des = $category;
                if (is_object($data) && property_exists($data, 'results')) {
                    $tpl = $data->results->results[0];
                } else {
                    error_log("Templates list for $remote_category could not be obtained from exportfeed services API.");
                    $tpl = null;
                    
                }
                
                if (gettype(get_option($tpl_des . '_feed_type')) == 'string' && $tpl != null) {
                    $feed_product_type = get_option($tpl_des . '_feed_type');
                    $tpl->feed_product_type = $feed_product_type;
                } else {
                    $tpl->feed_product_type = null;
                }
                
                $recommended_browse_nodes = is_string(get_option($tpl_des . '_recommended_browse_nodes')) ? get_option($tpl_des . '_recommended_browse_nodes') : null;
                $item_type_keyword = is_string(get_option($tpl_des . '_item_type_keyword')) ? get_option($tpl_des . '_item_type_keyword') : null;
                
                $tpl->recommended_browse_nodes = $recommended_browse_nodes;
                $tpl->item_type_keyword = $item_type_keyword;
            }
            
            if ($tpl && $tpl != null) {
                $current_tpl_remote_id = get_option('current_remote_tpl_id');
                if ($current_tpl_remote_id) {
                    if (is_object($tpl) && property_exists($tpl, 'id')) {
                        update_option('current_remote_tpl_id', $tpl->id);
                    }
                } else {
                    if (is_object($tpl) && property_exists($tpl, 'id')) {
                        update_option('current_remote_tpl_id', $tpl->id);
                    }
                }
                $this->loadTemplate($tpl);
            }
            return;
        } //initialize feed
        
        //Not safe to assume continueFeed will exist next version
        protected function continueFeed($category, $file_name, $file_path, $remote_category, $amazon_category)
        {
            $this->loadTemplate($remote_category);
            parent::continueFeed($category, $file_name, $file_path, $remote_category, $amazon_category);
        }
        
        public function getListingLoaderTemplate($country)
        {
            $exploded = explode('_', $country);
            if (is_array($exploded) && count($exploded) > 1) {
                $country = $exploded[1];
            } else {
                $country = null;
            }
            if ($country == 'AU') {
                $template = "TemplateType=Offer\tVersion=2014.0703
sku\tprice\tquantity\tproduct-id\tproduct-id-type\tcondition-type\tcondition-note\tASIN-hint\ttitle\toperation-type\tsale-price\tsale-start-date\tsale-end-date\tleadtime-to-ship\tlaunch-date\tfulfillment-center-id\tmain-offer-image\toffer-image1\toffer-image2\toffer-image3\toffer-image4\toffer-image5
sku\tprice\tquantity\tproduct-id\tproduct-id-type\tcondition-type\tcondition-note\tASIN-hint\ttitle\toperation-type\tsale-price\tsale-start-date\tsale-end-date\tleadtime-to-ship\tlaunch-date\tfulfillment-center-id\tmain-offer-image\toffer-image1\toffer-image2\toffer-image3\toffer-image4\toffer-image5\r\n";
            } else {
                $template = "TemplateType=Offer\tVersion=2014.0703
sku\tprice\tquantity\tproduct-id\tproduct-id-type\tcondition-type\tcondition-note\tASIN-hint\ttitle\tproduct-tax-code\toperation-type\tsale-price\tsale-start-date\tsale-end-date\tleadtime-to-ship\tlaunch-date\tis-giftwrap-available\tis-gift-message-available\tfulfillment-center-id\tmain-offer-image\toffer-image1\toffer-image2\toffer-image3\toffer-image4\toffer-image5
sku\tprice\tquantity\tproduct-id\tproduct-id-type\tcondition-type\tcondition-note\tASIN-hint\ttitle\tproduct-tax-code\toperation-type\tsale-price\tsale-start-date\tsale-end-date\tleadtime-to-ship\tlaunch-date\tis-giftwrap-available\tis-gift-message-available\tfulfillment-center-id\tmain-offer-image\toffer-image1\toffer-image2\toffer-image3\toffer-image4\toffer-image5 \r\n";
            }
            return $template;
        }
        
        
        public function getListingLoaderFeed($category, $remote_category, $file_name, $saved_feed = null, $products)
        {
            try {
                $this->currency = get_woocommerce_currency();
                $this->logActivity('Initializing...');
                global $message;
                global $amwcore;
                $x = new AMWSCPF_License();
                $this->loadAttributeUserMap();
                if ($this->updateObject == null) {
                    $this->initializeFeed($category, $remote_category);
                } else {
                    $this->resumeFeed($category, $remote_category, $this->updateObject);
                }
                $this->logActivity('Loading paths...');
                if (!$this->checkFolders()) {
                    return;
                }
                $file_url = AMWSCP_PFeedFolder::uploadFolder() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
                $file_path = AMWSCP_PFeedFolder::uploadURL() . $this->providerName . '/' . $file_name . '.' . $this->fileformat;
                //Special (WordPress): where admin is https and site is http, path to wp-uploads works out incorrectly as https
                //  we check the content_url() for https... if not present, patch the file_path
                if (($amwcore->cmsName == 'WordPress') && (strpos($file_path, 'https://') !== false) && (strpos(content_url(), 'https') === false)) {
                    $file_path = str_replace('https://', 'http://', $file_path);
                }
                $this->file_path = $file_path;
                $this->BaseFileName = $file_path;
                //Shipping and Taxation systems
                $this->shipping = new AMWSCP_PShippingData($this);
                $this->taxData = new AMWSCP_PTaxationData($this);
                $this->logActivity('Initializing categories...');
                //Figure out what categories the user wants to export
                $this->categories = new AMWSCP_PProductCategories($category);
                //Get the ProductList ready
                if ($this->productList == null) {
                    $this->productList = new AMWSCP_PProductList();
                }
                //Initialize some useful data
                //(must occur before overrides)
                $this->current_category = str_replace(".and.", " & ", str_replace(".in.", " > ", $remote_category));
                $this->initializeOverrides($saved_feed);
                //Reorder the rules
                usort($this->rules, 'sort_rule_func');
                //Load relations into ProductList
                //Note: if relation exists, we don't overwrite
                foreach ($this->relatedData as $relatedData) {
                    if (!isset($this->productList->relatedData[$relatedData[1]])) {
                        $this->productList->relatedData[$relatedData[1]] = new AMWSCP_PProductSupplementalData($relatedData[0]);
                    }
                }
                /*----------------------------------------------------------------------------------------*/
                //========================= Create the Feed =================================//
                /*----------------------------------------------------------------------------------------*/
                $this->logActivity('Creating feed data');
                $this->filename = $file_url;
                $this->createautoloaderFeed($file_name, $file_path, $remote_category, $products);
                unset($this->attributeDefaults);
                unset($this->attributeMappings);
                unset($this->feedOverrides);
                return true;
            } catch (Error $e) {
                error_log(json_encode($e));
                return $e;
            }
        }
        
        public function parse_and_get_amazon_attributes_from_template_raw($template){
          try{
              $raw_header_value = $template->raw;
              $third_row_header_value=explode("\n",$raw_header_value)[2];
              return explode("\t",$third_row_header_value);
          }catch (Exception $e){
              error_log($e->getMessage());
              echo json_encode(array('message'=>$e->getMessage(),'status'=>'failed'));
              exit;
          }
        }
        
    } /*Class Ended*/
    
}
