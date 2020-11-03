<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
require_once dirname(__FILE__) . '/../data/rules.php';
if (!class_exists('AMWSCP_PBasicFeed')) {
    class AMWSCP_PBasicFeed
    {

        public $activityLogger = null; //If set, someone wants me to log what phase I'm at in feed-generation';
        public $aggregateProviders = array();
        public $allow_additional_images = true;
        public $allow_attributes = true;
        public $allow_attribute_details = false; //old style attribute detection = source of minor hitches
        public $allow_variation_permutations = false;
        public $allowRelatedData = true;
        public $attributeAssignments = array();
        public $attributeDefaults = array();
        public $attributeDefaultStages = array(0, 0, 0, 0, 0, 0);
        public $attribute_granularity = 5; //0=basic feed(future) 1..4=minimal_postmeta_conversion 5=all_postmeta
        public $attributeMappings = array();
        public $auto_free = true; //Allow descendants to retain productlist
        public $auto_update_feedlist = true;
        public $categories;
        public $create_attribute_slugs = false;
        public $current_category; //This is the active category while in formatProduct()
        public $currency;
        public $currency_shipping = ''; //(Currently uses currency_format)
        public $currency_format = '%1.2f';
        public $dimension_unit;
        public $errors = array();
        public $fileformat = 'xml';
        public $fieldDelimiter = "\t"; //For CSVs
        public $fields; //For CSVs
        public $feed_category;
        public $feedOverrides;
        public $forceCData = false; //Applies to ProductListXML only
        public $force_all_categories = false;
        public $force_currency = false;
        //public $force_featured_imgurl = false; //forces feature_imgurl even for variations
        public $force_featured_image = false; //forces feature_imgurl even for variations
        public $force_wc_api = false;
        public $get_wc_shipping_attributes = false;
        public $get_wc_shipping_class = false;
        public $get_tax_rates = false;
        public $gmc_enabled = false; //Allow Google merchant centre woothemes extension (WordPress)
        public $gmc_attributes = array(); //If anything added in here, restrict GMC list to these
        public $has_header = true;
        public $has_footer = true;
        public $has_product_range = false;
        public $ignoreDuplicates = true; //useful when products are assigned multiple categories and insufficient identifiers to distinguish them
        public $lang = '';
        public $max_description_length = 10000;
        //public $max_custom_field = 50000;
        public $message = ''; //For Error detection
        public $permutation_base_id = 1000000; //Fix to more than the max # of products and posts
        public $permutation_variant_multiplier = 1000; //Max # of "any" variants per product. Note: High values will cause IDs to spiral into the billions
        public $providerName = '';
        public $providerNameL = '';
        public $productCount = 0; //Number of products successfully exported
        public $productList;
        public $productTypeFromLocalCategory = false;
        public $providerType = 0;
        public $relatedData = array();
        public $reversible = false; //Feed accepts input data
        public $rules = array();
        //public $sellerName = ''; //Required Bing attribute - Merchant/Store that provides this product
        public $success = false;
        public $stripHTML = false;
        public $updateObject = null;
        public $utf8encode = false; //Temporary until a better encoding system can be engineered
        public $timeout = 0; //If >0 try to override max_execution time
        public $weight_unit;
        public $BaseFileName;
        public $currentFeedId = 0;

        // Hide out of stock
        public $hide_out_of_stock;

        public function addAttributeDefault($attributeName, $value, $defaultClass = 'AMWSCP_PAttributeDefault')
        {
            if (!class_exists($defaultClass)) {
                $this->addErrorMessage(5, 'AttributeDefault class "' . $defaultClass . '" not found. Reconfigure Advanced Commands to resolve.');
                return;
            }
            $thisDefault = new $defaultClass();
            //$thisDefault = new AMWSCP_PAttributeDefault();
            $thisDefault->attributeName = $attributeName;
            $thisDefault->value = $value;
            $thisDefault->parent_feed = $this;
            $tvalue = trim($value);
            if (strlen($tvalue) > 0 && $tvalue[0] == '$') {
                $thisDefault->value = trim($thisDefault->value);
                $thisDefault->isRuled = true;
            }
            $this->attributeDefaults[] = $thisDefault;
            $this->attributeDefaultStages[$thisDefault->stage] += 1;
            $thisDefault->initialize();
            return $thisDefault;
        }

        public function addAttributeMapping($attributeName, $mapTo, $usesCData = false, $isRequired = false, $isMapped = false)
        {

            $thisMapping = new stdClass();
            $thisMapping->attributeName = $attributeName;
            $thisMapping->mapTo = $mapTo;
            $thisMapping->enabled = true;
            $thisMapping->deleted = false;
            $thisMapping->usesCData = $usesCData;
            $thisMapping->isRequired = $isRequired;
            $thisMapping->systemDefined = false;
            $thisMapping->isMapped = $isMapped;
            $this->attributeMappings[] = $thisMapping;

            //Auto-delete any system defined Mappings with matching mapTo
            foreach ($this->attributeMappings as $mapping) {
                if ($mapping->systemDefined && $mapping->mapTo == $mapTo) {
                    $mapping->deleted = true;
                }
            }

            return $thisMapping;
        }

        public function addErrorMessage($id, $msg, $isWarning = false)
        {

            //Allows descendent providers to report errors
            if (!isset($this->errors[$id])) {
                $error = new stdClass();
                $error->msg = $msg;
                $error->occurrences = 0;
                $error->isWarning = $isWarning;
                $this->errors[$id] = $error;
            }
            $this->errors[$id]->occurrences++;

        }

        public function addRule($ruleName, $ruleClass, $parameters = array(), $order = 0)
        {
            $className = 'AMWSCP_PFeedRule' . ucwords(strtolower($ruleClass));
            if (!class_exists($className)) {
                $this->addErrorMessage(5, 'Rule "' . $ruleClass . '" not found. Reconfigure Advanced Commands to resolve.');
                return null;
            }
            $thisRule = new $className();
            $thisRule->name = $ruleName;
            $thisRule->parameters = $parameters;
            $thisRule->parent_feed = $this;
            $thisRule->order = $order;
            $thisRule->initialize();
            $this->rules[] = $thisRule;
            return $thisRule;
        }

        public function checkFolders()
        {

            global $message;

            $dir = AMWSCP_PFeedFolder::uploadRoot();
            if (!is_writable($dir)) {
                $message = $dir . ' should be writeable';
                return false;
            }

            $dir = AMWSCP_PFeedFolder::uploadFolder();
            if (!is_dir($dir)) {
                mkdir($dir);
            }

            if (!is_writable($dir)) {
                $message = "$dir should be writeable";
                return false;
            }
            $dir2 = $dir . $this->providerName . '/';
            if (!is_dir($dir2)) {
                mkdir($dir2);
            }

            return true;
        }

        protected function containsNonUTF8Character($text)
        {
            for ($i = 0; $i < strlen($text); $i++) //if ($text[$i] > "\xFF")
            {
                if (ord($text[$i]) > 224) {
                    return true;
                }
            }

            return false;
        }

        protected function continueFeed($category, $file_name, $file_path, $remote_category, $amazon_category)
        {
            //Note: protected function because it will be deleted in some future version -KH
            $mode = "a";
            if ($this->updateObject->startValue == 0) {
                $mode = "w";
            }

            $this->fileHandle = fopen($this->filename, $mode);
            if ($this->has_header && $this->updateObject->startValue == 0) {
                fwrite($this->fileHandle, $this->getFeedHeader($file_name, $file_path));
            }

            $this->productList->productStart = $this->updateObject->startValue;
            $this->productList->getProductList($this, $remote_category);
            $done = false;
            if ($this->productList->products == null || count($this->productList->products) < 50000) {
                $done = true;
            }

            if (isset($this->productList->products)) {
                $this->updateObject->startValue += count($this->productList->products);
                if (!isset($this->updateObject->productCount)) {
                    $this->updateObject->productCount = 0;
                }

                $this->updateObject->productCount += $this->productCount;
            }
            if ($this->has_footer && $done) {
                $this->updateObject->finished = true;
                fwrite($this->fileHandle, $this->getFeedFooter($file_name, $file_path));
                $this->productCount = $this->updateObject->productCount;
                AMWSCP_PFeedActivityLog::updateFeedList($category, $remote_category, $file_name, $file_path, $this->providerName, $this->productCount, $amazon_category);
            }
            fclose($this->fileHandle);
        }

        protected function createFeed($file_name, $file_path, $remote_category)
        {
            //$file_name is (incorrectly) a url due to an unfortunate nomenclature left over from v2.x
            if (is_readable($this->filename) || !file_exists($this->filename)) {
                $this->fileHandle = fopen($this->filename, "w");
            } else {
                $this->fileHandle = fopen($this->filename, "w");
                error_log(" Sorry, Permission Denied for opening file: " . $this->filename . ". File must be readable.");
            }
            if ($this->has_header) {
                fwrite($this->fileHandle, $this->getFeedHeader($file_name, $file_path));
            }
            $this->productList->getProductList($this, $remote_category);
            if ($this->has_footer) {
                fwrite($this->fileHandle, $this->getFeedFooter($file_name, $file_path));
            }

            fclose($this->fileHandle);
        }

        protected function createautoloaderFeed($file_name, $file_path, $remote_category, $products)
        {
            $this->products = $products;
            //$file_name is (incorrectly) a url due to an unfortunate nomenclature left over from v2.x
            if (is_readable($this->filename) || !file_exists($this->filename)) {
                $this->fileHandle = fopen($this->filename, "w");
            } else {
                $this->fileHandle = fopen($this->filename, "w");
                error_log(" Sorry, Permission Denied for opening file: " . $this->filename . ". File must be readable.");
            }
            if ($this->has_header) {
                fwrite($this->fileHandle, $this->getFeedHeader($file_name, $file_path));
            }
            $this->productList->fetchAndwriteProducts($this, $remote_category);
            if ($this->has_footer) {
                fwrite($this->fileHandle, $this->getFeedFooter($file_name, $file_path));
            }

            fclose($this->fileHandle);
        }

        public function fetchProductAttribute($name, $product)
        {
            $thisAttributeMapping = $this->getMapping($name);
            if ($thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName])) {
                return $product->attributes[$thisAttributeMapping->attributeName];
            } else {
                return '';
            }

        }

        public function finalizeRead()
        {
        }

        public function formatLine($attribute, $value, $cdata = false, $leader_space = '')
        {
            //Prep a single line for XML
            //Allow the $attribute to be overridden
            if (isset($this->feedOverrides->overrides[$attribute]) && (strlen($this->feedOverrides->overrides[$attribute]) > 0)) {
                $attribute = $this->feedOverrides->overrides[$attribute];
            }

            $c_leader = '';
            $c_footer = '';
            if ($cdata) {
                $c_leader = '<![CDATA[';
                $c_footer = ']]>';
            }
            //Allow force strip HTML
            if ($this->stripHTML) {
                $value = strip_tags(html_entity_decode($value));
            }

            //UTF8Encode is guaranteed to create garbled text because we don't know the source encoding type
            //However, it will create a feed that will process, so it's a good temporary measure
            if ($this->utf8encode || $this->utf8encode == 1) {
                $value = utf8_encode($value);
                $attribute = utf8_encode($attribute);
            }

            //if not CData, don't allow '&'
            if (!$cdata) {
                $value = htmlentities($value, ENT_QUOTES, 'UTF-8');
            }

            if (gettype($value) == 'array') {
                $value = json_encode($value);
            }

            //Done
            return '
        ' . $leader_space . '<' . $attribute . '>' . $c_leader . $value . $c_footer . '</' . $attribute . '>';
        }

        public function formatProduct($product)
        {
            return '';
        }

        public function getErrorMessages()
        {

            $error_messages = '';

            foreach ($this->errors as $index => $this_error) {
                if ($this_error->isWarning) {
                    $prefix = 'Warning: ';
                } else {
                    $prefix = 'Error: ';
                }

                $error_messages .= '<br>' . $prefix . $this_error->msg . '(' . $this_error->occurrences . ') <a href="http://docs.exportfeed.com/error_doc.php?id=' . $index . '" target="_blank">more...</a>';
            }

            return $this->message . $error_messages;
        }

        public function getFeedData($category, $remote_category, $file_name, $saved_feed = null, $amazon_category, $assignedCategory,$variationtheme,$feed_product_type,$recommended_browse_nodes,$item_type_keyword)
        {
            $this->currency = get_woocommerce_currency();
            $this->logActivity('Initializing...');
            global $message;
            global $amwcore;
            $x = new AMWSCPF_License();
            $this->loadAttributeUserMap();
            if ($this->updateObject == null) {
               /*This will initialize the feed attributes fetching and header parsing, mainly for amazon*/
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
            //Trying to change max_execution_time will throw privilege errors on some installs
            //so it's been left as an option
            if ($this->timeout > 0) {
                ini_set('max_execution_time', $this->timeout);
            }
            /*================== Add the space in pricestandard rules ==============================*/
            /*
             * if (strlen($this->currency) > 0)
                $this->currency = ' '.$this->currency;
            ========================================================================================*/

            /*---------------------------------------------------------------------------------------
              *insert record before creating feed to record the products in the feed
              * Available from version > 2.3.0.0
             ---------------------------------------------------------------------------------------*/
            $params = array(
                'category' => $category,
                'remote_category' => $remote_category,
                'amazon_category' => $amazon_category,
                'file_name' => $file_name,
                'file_path' => $file_path,
                'providerName' => $this->providerName,
                'providerName' => $this->providerName,
                'product_count' => 0,
                'variationtheme' => $variationtheme,
                'feed_product_type' => $feed_product_type,
                'recommended_browse_nodes' => $recommended_browse_nodes,
                'item_type_keyword' => $item_type_keyword,
            );
            $transactionId = AMWSCP_PFeedActivityLog::recordFeedId($params);
            $this->currentFeedId = $transactionId;
            /*----------------------------------------------------------------------------------------*/
                   //========================= Create the Feed =================================//
            /*----------------------------------------------------------------------------------------*/
            $this->logActivity('Creating feed data');
            $this->filename = $file_url;
            if ($this->updateObject == null) {
                $this->createFeed($file_name, $file_path, $remote_category);
            } else {
                $this->continueFeed($category, $file_name, $file_path, $remote_category, $amazon_category);
            }

            $this->logActivity('Updating Feed List');
            if ($this->auto_update_feedlist) {
                AMWSCP_PFeedActivityLog::updateFeedList($category, $assignedCategory, $file_name, $file_path, $this->providerName, $this->productCount, $amazon_category,$variationtheme,$feed_product_type,$recommended_browse_nodes,$item_type_keyword);
            }

            if ($this->auto_free) {
                //Free the Attribute defaults
                for ($i = 0; $i < count($this->attributeDefaults); $i++) {
                    unset($this->attributeDefaults[$i]);
                }

                //Free the Attribute Mapping Objects
                for ($i = 0; $i < count($this->attributeMappings); $i++) {
                    unset($this->attributeMappings[$i]);
                }

                //De-allocate the overrides object to prevent chain dependency that made the core unload too early
                unset($this->feedOverrides);
            }
            if ($this->productCount <= 0) {
                $this->message .= '<br>No products returned';
                return;
            }

            $this->success = true;
        }

        public function getFeedFooter($file_name, $file_path)
        {
            return '';
        }

        /*public function getFeedFooter($file_name, $file_path)
        {
        //Now we finally write the headers! Start by creating them
        $headers = array();
        foreach ($this->attributeMappings as $thisMapping) {
        if ($thisMapping->enabled && !$thisMapping->deleted) {
        $headers[] = $thisMapping->mapTo;
        }
        }

        $headerString = implode($this->fieldDelimiter, $headers);

        $savedData = file_get_contents($this->filename);
        file_put_contents($this->filename, $headerString . "\r\n" . $savedData);

        //Write the footer as a header in the aggregate
        foreach ($this->aggregateProviders as $thisProvider) {
        $thisProvider->aggregateHeaderWrite($this->savedFeedID, $headerString);
        }

        return '';

        }
         */
        public function getFeedHeader($file_name, $file_path)
        {
            return '';
        }

        public function getMapping($name)
        {
            foreach ($this->attributeMappings as $thisAttributeMapping) {
                if ($thisAttributeMapping->attributeName == $name) {
                    return $thisAttributeMapping;
                }
            }

            return null;
        }

        public function getMappingByMapto($name)
        {
            foreach ($this->attributeMappings as $thisAttributeMapping) {
                if ($thisAttributeMapping->mapTo == $name) {
                    return $thisAttributeMapping;
                }
            }

            return null;
        }

        public function getRuleByName($name)
        {
            foreach ($this->rules as $rule) {
                if ($rule->name == $name) {
                    return $rule;
                }
            }

            return null;
        }

        public function handleProduct($this_product)
        {
            $this_product->attributes['current_category'] = $this->current_category;

            //********************************************************************
            //Run the rules
            //********************************************************************

            foreach ($this->rules as $rule) {
                if ($rule->enabled) {
                    $rule->clearValue();
                }
            }

            foreach ($this->rules as $index => $rule) {
                if ($rule->enabled) {
                    $rule->process($this_product);
                }
            }

            foreach ($this->attributeDefaults as $thisDefault) {
                if ($thisDefault->isRuled) {
                    $rule = $this->getRuleByName($thisDefault->value);
                    if ($rule != null) {
                        $this_product->attributes[$thisDefault->attributeName] = $rule->value;
                    }

                }
            }

            //***********************************************************
            //Send to descendant feed-provider for formatting
            //***********************************************************
            $product_text = $this->formatProduct($this_product);
            if ($this->feed_category->verifyProduct($this_product) && $this_product->attributes['valid']) {
                AMWSCP_PFeedActivityLog::recordProductinFeed($this_product,$this->currentFeedId);
                $this->handleProductSave($this_product, $product_text);
                foreach ($this->aggregateProviders as $thisProvider) {
                    $thisProvider->aggregateProductSave($this->savedFeedID, $this_product, $product_text);
                }

                $this->productCount++;
            }

        }

        public function handleProductSave($product, $product_text)
        {
            if (is_resource($this->fileHandle)) {
                fwrite($this->fileHandle, $product_text);
            } else {
                if (file_exists($this->BaseFileName)) {
                    if (!is_readable($this->BaseFileName)) {
                        $this->fileHandle = fopen($this->BaseFileName, 'w');
                    } else {
                        error_log($this->BaseFileName . " is not writable. Please make sure apache can read and write the file in your server.");
                    }

                }
                error_log($this->BaseFileName . " doesn't exists in your server");
                exit();
            }
        }

        public function initializeFeed($category, $remote_category)
        {
            //Allow descendant to perform initialization based on category/remote category
        }

        public function initializeOverrides($saved_feed)
        {

            $this->logActivity('Initializing overrides...');
            //Mark all existing mappings as "SystemDefined" meaning auto-delete
            foreach ($this->attributeMappings as $mapping) {
                $mapping->systemDefined = true;
            }

            //Load Attribute mappings
            $this->feedOverrides = new AMWSCP_PFeedOverride($this->providerName, $this, $saved_feed);
        }

        public function initalizeRead()
        {
        }

        public function insertField($new_field, $index_field)
        {
            /*CSV feed providers will sometimes want to insert-field-after-this-other-field, which PHP doesn't provide
            insertField not currently used because the feedheader is created before productlist so there's no way to
            know if some later category will need to re-arrange the fields
            Edit: Debug Bing Feed provider uses insertField() for now*/
            if (in_array($new_field, $this->fields)) {
                return;
            }

            $new_array = array();
            foreach ($this->fields as $key => $item) {
                $new_array[] = $item;
                if ($item == $index_field) {
                    $new_array[] = $new_field;
                }

            }
            $this->fields = $new_array;
        }

        public function leaveFeed($updateObject)
        {
            /*
        The system is abandoning this feed.
        updateObject will be saved in JSON format and provided again in resumeFeed() at some point in the future
         */
        }

        public function loadAttributeUserMap()
        {
            //Called during feed initialization to map the Attributes
            global $amwcore;
            $map_string = $amwcore->settingGet('amwscp_attribute_user_map_' . $this->providerName);
            if ($map_string == '[]') {
                //if map_string is not object //temp fix for backwards compatibility... true fix below
                $amwcore->settingSet('amwscp_attribute_user_map_' . $this->providerName, '');
                $map_string = '';
            }
            if (strlen($map_string) == 0) {
                $map = new stdClass();
            } //Was array(); *true fix -K
            else {
                $map = json_decode($map_string);
                $map = get_object_vars($map);
            }

            foreach ($map as $mapto => $attr) {
                $thisAttribute = $this->getMappingByMapto($mapto);
                if ($thisAttribute != null && strlen($attr) > 0) {
                    $thisAttribute->attributeName = $attr;
                }

            }
        }

        public function logActivity($activity)
        {
            if ($this->activityLogger != null) {
                $this->activityLogger->logPhase($activity);
            }

        }

        public function must_exit()
        {
            //true means exit when feed complete so the browser page will remain in place (WordPress)
            return true;
        }

        public function resumeFeed($category, $remote_category, $updateObject)
        {
            /*Allow descendant to perform initialization based on category/remote category
            upon resuming a Feed. Note that previously saved data is available in updateObject*/
            $this->auto_update_feedlist = false;
        }

        /**
         * AMWSCP_PBasicFeed constructor.
         * @param null $saved_feed
         */
        public function __construct($saved_feed = null)
        {

            global $amwcore;

            $this->feed_category = new AMWSCP_md5y();
            $this->weight_unit = $amwcore->weight_unit;
            $this->dimension_unit = $amwcore->dimension_unit;
            $this->currency = $amwcore->currency;

            $this->addRule('description', 'description');

        }

    }
}
//AMWSCP_PBasicFeed

//********************************************************************
// AMWSCP_PCSVFeed has functions a CSV Feed would need
//********************************************************************

class AMWSCP_PCSVFeed extends AMWSCP_PBasicFeed
{

    public function __construct()
    {

        parent::__construct();
        //apply strictAttribute rule to removes html, special chars
        $this->addRule('description', 'description', array('strict'));

        /*commented from auto loading as it was removing apecial character also along with non printable
        $this->addRule('strict_attribute', 'strictAttribute', array('description_short'));*/
        //Descriptions and title: escape any quotes
        $this->addRule('csv_standard', 'CSVStandard', array('title'));
        $this->addRule('csv_standard', 'CSVStandard', array('description'));
        $this->addRule('csv_standard', 'CSVStandard', array('description_short'));
    }

    protected function asCSVString($current_feed)
    {

        //Build output in order of fields
        $output = '';
        foreach ($this->fields as $field) {
            if (isset($current_feed[$field])) {
                $output .= $current_feed[$field] . $this->fieldDelimiter;
            } else {
                $output .= $this->fieldDelimiter;
            }

        }

        //Trim trailing comma
        return substr($output, 0, -1) . "\r\n";

    }

    public function executeOverrides($product, &$current_feed)
    {

        /*Mapping v2.0 Deprecated
    //Run overrides
    //Note: One day, when the feed can report errors, we need to report duplicate overrides when used_so_far makes a catch
    $used_so_far = array();
    foreach($product->attributes as $key => $a)
    if (isset($this->feedOverrides->overrides[$key]) && !in_array($this->feedOverrides->overrides[$key], $used_so_far)) {
    $current_feed[$this->feedOverrides->overrides[$key]] = $a;
    $used_so_far[] = $this->feedOverrides->overrides[$key];
    }
     */

    }

    public function formatProduct($product)
    {
        //Trigger Mapping 3.0 Before-Feed Event
        foreach ($this->attributeDefaults as $thisDefault) {
            if ($thisDefault->stage == 2) {
                $product->attributes[$thisDefault->attributeName] = $thisDefault->getValue($product);
            }
        }

        //Build output in order of fields
        $output = '';
        foreach ($this->fields as $field) {
            $thisAttributeMapping = $this->getMappingByMapto($field);
            if (($thisAttributeMapping != null) && $thisAttributeMapping->enabled && !$thisAttributeMapping->deleted && isset($product->attributes[$thisAttributeMapping->attributeName])) {
                if ($thisAttributeMapping->usesCData) {
                    $quotes = '"';
                } else {
                    $quotes = '';
                }

                $output .= $quotes . $product->attributes[$thisAttributeMapping->attributeName] . $quotes;
            }
            $output .= $this->fieldDelimiter;
        }

        //Trigger Mapping 3.0 After-Feed Event
        foreach ($this->attributeDefaults as $thisDefault) {
            if ($thisDefault->stage == 3) {
                $thisDefault->postProcess($product, $output);
            }
        }

        //Trim trailing comma
        return substr($output, 0, -1) . "\r\n";

    }

    // public function getFeedHeader($file_name, $file_path)
    // {

    //     $output = '';
    //     foreach ($this->fields as $field) {
    //         if (isset($this->feedOverrides->overrides[$field])) {
    //             $field = $this->feedOverrides->overrides[$field];
    //         }

    //         $output .= $field . $this->fieldDelimiter;
    //     }
    //     //Trim trailing comma
    //     return;

    // }

}

if (!function_exists('sort_rule_func')) {
    function sort_rule_func($a, $b)
    {
        if ($a->order == $b->order) {
            return 0;
        } else {
            return ($a->order < $b->order) ? -1 : 1;
        }

    }
}
