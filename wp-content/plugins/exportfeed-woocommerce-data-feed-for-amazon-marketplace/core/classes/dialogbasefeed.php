<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
require_once dirname(__FILE__) . '/../data/productcategories.php';
require_once dirname(__FILE__) . '/../data/attributesfound.php';
require_once dirname(__FILE__) . '/../data/feedfolders.php';
if (!class_exists('AMWSCP_PBaseFeedDialog')) {
    class AMWSCP_PBaseFeedDialog
    {

        public $blockCategoryList = false;
        public $options; //Array to be filled by constructor of descendant
        public $service_name      = 'Google'; //Example only
        public $service_name_long = 'Google Products XML Export'; //Example only

        public function __construct()
        {
            $this->options = array();
        }

        public function createDropdown($thisAttribute, $index)
        {
            $found_options = new AMWSCP_FoundOptions($this->service_name, $thisAttribute);
            $output        = '
    <select class="attribute_select" id="attribute_select' . $index . '" onchange="amwscp_setAttributeOption(\'' . $this->service_name . '\', \'' . $thisAttribute . '\', ' . $index . ')">
      <option value=""></option>';
            foreach ($this->options as $option) {
                if ($option == $found_options->option_value) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }

                $output .= '<option value="' . $this->convert_option($option) . '"' . $selected . '>' . $option . '</option>';
            }
            $output .= '
    </select>';
            return $output;
        }

        public function createDropdownAttr($FoundAttributes, $defaultValue = '', $mapTo)
        {
            $output = '
    <select class="attribute_select" service_name="' . $this->service_name . '"
        mapto="' . $mapTo . '"
        onchange="amwscp_setAttributeOptionV2(this)" >
      <option value=""></option>
      <option value="(Reset)">(Reset)</option>';
            foreach ($FoundAttributes->attributes as $attr) {
                if ($defaultValue == $attr->attribute_name) {
                    $selected = ' selected="true"';
                } else {
                    $selected = '';
                }

                $output .= '<option value="' . $attr->attribute_name . '"' . $selected . '>' . $attr->attribute_name . '</option>';
            }
            $output .= '
        <option value="">--Common attributes--</option>
        <option value="brand">brand</option>
        <option value="description_short">description_short</option>
        <option value="id">id</option>
        <option value="regular_price">regular_price</option>
        <option value="sale_price">sale_price</option>
        <option value="sku">sku</option>
        <option value="tag">tag</option>
        <option value="title">title</option>
        <option value="">--CPF Additional Fields--</option>
        <option value="brand">brand</option>
        <option value="ean">ean</option>
        <option value="mpn">mpn</option>
        <option value="upc">upc</option>
        <option value="feature_imgurl">Man Image</option>
        <option value="description">description</option>
        <option value="">--Dummy attributes--</option>
        <option value="default1">default1</option>
        <option value="default2">default2</option>
        <option value="default3">default3</option>
        <option value="default4">default4</option>
        <option value="default5">default5</option>
        <option value="default6">default6</option>
    </select>';
            return $output;
        }

        public function attributeMappings()
        {

            global $amwcore;
            $FoundAttributes             = new AMWSCP_FoundAttribute();
            $savedAttributes             = $FoundAttributes->attributes;
            $FoundAttributes->attributes = array();
            foreach ($savedAttributes as $attr) {
                $FoundAttributes->attributes[] = $attr;
            }

            foreach ($this->provider->attributeMappings as $thisAttributeMapping) {
                //if empty mapping, don't add to drop down list
                if (strlen(trim($thisAttributeMapping->attributeName)) > 0) {
                    $attr                          = new stdClass();
                    $attr->attribute_name          = $thisAttributeMapping->attributeName;
                    $FoundAttributes->attributes[] = $attr;
                }
            }
            $output = '
                <p class="feed-create"><a target="blank" title="Generate Merchant Feed" href="http://www.exportfeed.com/documentation/generate-google-merchant-feed-woocommerce/">Generate your first feed</a> |
                <a target=\'_blank\' href=\'http://www.exportfeed.com/tos/\' >View guides</a></p>

                <p>Product attributes of your ' . $amwcore->cmsPluginName . ' are mapped automatically as per ' . $this->service_name . '\'s requirements. You can manually make changes <span class="cpf-danger" style="color:red;">if you are absolutely sure of what you are doing.</span><br>
                </p>
                <div class="mapping-container">
                <div class="required-attr">
                <label class="attributes-label" title="Required Attributes" id="amwscp_toggleRequiredAttributes" >Required Attributes</label>
                <div class="required-attributes" id=\'required-attributes\'>
                <table>
                <tr><td>Attribute</td><td></td><td>' . $this->service_name . ' Attribute</td></tr>';

            foreach ($this->provider->attributeMappings as $thisAttributeMapping) {
                if ($thisAttributeMapping->isRequired) {
                    $output .= '<tr><td>' . $this->createDropdownAttr($FoundAttributes, $thisAttributeMapping->attributeName, $thisAttributeMapping->mapTo) . '</td><td></td><td>' . $thisAttributeMapping->mapTo . '</td></tr>';
                }
            }

            $output .= '
              </table>
              </div>
              </div>
              <div class="additional-attr">
              <label class="attributes-label" title="Optional Attributes" id="amwscp_toggleOptionalAttributes">Additional Attributes</label>
              <div style="display:inline-block;" class="optional-attributes" id=\'optional-attributes\'>
              <table>
              <tr><td>Attribute</td><td></td><td>' . $this->service_name . ' Attribute</td></tr>';

            foreach ($this->provider->attributeMappings as $thisAttributeMapping) {
                if (!$thisAttributeMapping->isRequired) {
                    $output .= '<tr><td>' . $this->createDropdownAttr($FoundAttributes, $thisAttributeMapping->attributeName, $thisAttributeMapping->mapTo) . '</td><td></td><td>' . $thisAttributeMapping->mapTo . '</td></tr>';
                }
            }

            $output .= '
              </table>
              </div>
              </div>
              </div>';

            return $output;
        }

        public function categoryList($initial_remote_category)
        {
            if ($this->blockCategoryList) {
                return '<input type="hidden" id="remote_category" name="remote_category" value="undefined">';
            } else {
                return '
                  <span class="label">' . $this->service_name . ' Category : </span>
                  <span><input type="text" name="categoryDisplayText" class="text_big" id="categoryDisplayText"  onkeyup="amwscp_doFetchCategory_timed(\'' . $this->service_name . '\',  this.value)" value="' . $initial_remote_category . '" autocomplete="off" placeholder="Start typing for a category name"/></span>
                  <div id="categoryList" class="categoryList"></div>
                  <input type="hidden" id="remote_category" name="remote_category" value="' . $initial_remote_category . '">';
            }

        }

        public function getTemplateFile()
        {
            $filename = dirname(__FILE__) . '/../feeds/' . strtolower($this->service_name) . '/dialog.tpl.php';
            if (!file_exists($filename)) {
                $filename = dirname(__FILE__) . '/dialogbasefeed.tpl.php';
            }
            
            return $filename;
        }

        public function initializeProvider()
        {
            //Load the feed provider
            require_once dirname(__FILE__) . '/md5.php';
            require_once dirname(__FILE__) . '/../feeds/' . strtolower($this->service_name) . '/feed.php';
            $providerName   = 'AMWSCP_P' . $this->service_name . 'Feed';
            $this->provider = new $providerName;

            $this->provider->loadAttributeUserMap();
        }

        public function line2()
        {
            global $amwcore;
            if ($amwcore->cmsPluginName != 'RapidCart') {
                return '';
            }

            $listOfShops = $amwcore->listOfRapidCartShops();
            $output      = '<select class="text_big" id="edtRapidCartShop" onchange="amwscp_doFetchLocalCategories()" >';
            foreach ($listOfShops as $shop) {
                if ($shop->id == $amwcore->shopID) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = '';
                }

                $output .= '<option value="' . $shop->id . '"' . $selected . '>' . $shop->name . '</option>';
            }
            $output .= '</select>';
            return '
                <div class="feed-right-row">
                  <span class="label">Shop : </span>
                  ' . $output . '
                </div>';
        }

        public function mainDialog($source_feed = null)
        {

            global $amwcore;
            $dis = '';
            $this->advancedSettings = $amwcore->settingGet($this->service_name . '-amwscp-settings');
            if ($source_feed == null) {
                $initial_local_category          = '';
                $this->initial_local_category_id = '';
                $initial_remote_category         = '';
                $amazon_category                 = '';
                $this->initial_filename          = '';
                $this->script                    = '';
                $this->cbUnique                  = '';
                $initial_feed_product_type       = '';
                $recommended_browse_nodes        = '';
                $item_type_keyword               = '';
                $disabled                        = false;
            } else {

                $disabled = $source_feed->disabled;
                $dis      = '';
                if ($disabled) {
                    $dis = 'disabled';
                }
                $initial_local_category          = $source_feed->local_category;
                $this->initial_local_category_id = $source_feed->category_id;
                $initial_remote_category         = $source_feed->remote_category;
                $amazon_category                 = $source_feed->amazon_category;
                $initial_feed_product_type       = $source_feed->feed_product_type;
                $recommended_browse_nodes        = $source_feed->recommended_browse_nodes;
                $item_type_keyword               = $source_feed->item_type_keyword;
                $this->initial_filename          = $source_feed->filename;
                if(strlen($amazon_category)<3) {
                    $initial_remote_category_array =explode('_',$initial_remote_category);
                    $amazon_category = $initial_remote_category_array[0];
                }
                if(strlen($initial_remote_category)<3) $initial_remote_category = $amazon_category;
                if ($source_feed->own_overrides == 1) {
                    $strChecked             = 'checked="checked" ';
                    $this->advancedSettings = $source_feed->feed_overrides;
                } else {
                    $strChecked = '';
                }

                $this->cbUnique = '<div><label><input type="checkbox" id="cbUniqueOverride" ' . $strChecked . '/>Advanced commands unique to this feed</label></div>';
            }
            $this->servName = strtolower($this->service_name);

            $this->initializeProvider();
            $attrVal                  = array();
            $this->folders            = new AMWSCP_PFeedFolder();
            $this->product_categories = new AMWSCP_PProductCategories(); //used?

            $this->localCategoryList = '
            <input type="text" name="local_category_display" ' . $dis . ' class="text_big" id="local_category_display"  onclick="amwscp_showLocalCategories(\'' . $this->service_name . '\')" value="' . $initial_local_category . '" autocomplete="off" readonly="true" placeholder="Click here to select your categories"/>
            <input type="hidden" name="local_category" id="local_category" value="' . $this->initial_local_category_id . '" />';
            $this->source_feed = $source_feed;

            //Pass this to the template for processing
            include $this->getTemplateFile();
        }

        //Strip special characters out of an option so it can safely go in a <select /> in the dialog
        public function convert_option($option)
        {
            //Some Feeds (like Google & eBay) need to modify this
            return $option;
        }

    }
}
