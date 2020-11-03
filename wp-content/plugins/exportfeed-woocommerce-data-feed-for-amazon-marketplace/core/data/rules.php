<?php
if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
if (!class_exists('AMWSCP_PFeedRule')) {
    class AMWSCP_PFeedRule
    {

        public $enabled     = true;
        public $name        = '';
        public $order       = 200; //200 ~ AMWSCP_PActionBeforeFeed, therefore don't assign order > 299 (300 ~ AMWSCPF_PActionAfterFeed)
        public $parameters  = array();
        public $parametersV = null; //Virtual parameters... or "Interpreted" params
        public $parent_feed = null; //points to feed provider that owns this rule
        public $value       = '';

        public function __destruct()
        {
            unset($this->parent_feed);
        }

        public function clearValue()
        {
            $this->value       = '';
            $this->parametersV = $this->parameters;
        }

        public function initialize()
        {
            if (strlen($this->name) > 0 && $this->name[0] != '$') {
                $this->name = '$' . $this->name;
            }

        }

        public function makeUnique()
        {

            //Unique rule -> Only one rule with this name allowed to be enabled at a given time
            //Note that at the time of the makeUnique call (within initialize), this rule
            //(the caller) does not exist in the rules list
            foreach ($this->parent_feed->rules as $rule) {
                if ($rule->name == $this->name) {
                    $rule->enabled = false;
                }
            }

        }

        public function process($product)
        {
        }

        public function resolveVirtualParameters()
        {
            $busy = true;
            while ($busy) {
                $busy = false;
                foreach ($this->parametersV as &$param) {
                    if (strlen($param) > 0 && $param[0] == '$') {
                        $rule = $this->parent_feed->getRuleByName($param);
                        if ($rule != null) {
                            $param = $rule->value;
                            $busy  = true;
                        }
                    }
                }

            }
        }

    }
}

//***********************************************************
//Concatenate
//***********************************************************

class AMWSCP_PFeedRuleConcat extends AMWSCP_PFeedRule
{

    public function initialize()
    {
        parent::initialize();
        $this->order = 210;
    }

    public function process($product)
    {
        $this->resolveVirtualParameters();
        foreach ($this->parametersV as $arg) {
            if (isset($product->attributes[$arg])) {
                $this->value .= $product->attributes[$arg];
            } else {
                if(end($this->parametersV) && end($this->parametersV)=='true'){
                    $this->value .= $arg;
                }else{
                    continue;
                }
            }
        }

    }
}

/*
Create rule generate links directly to selected variable product
ex: rule concat(link, "?attribute_pa_", attribute-slug, attribute-value) as new_link
note: the attribiute-value can be:
str_replace(' ', '+', attribute-value) OR attribute-value-slug.
 */

//***********************************************************
//Description: Format & Length
//***********************************************************

class AMWSCP_PFeedRuleDescription extends AMWSCP_PFeedRule
{

    public $allow_empty_title                = false;
    public $max_description_length           = 10000;
    public $descriptionStrict                = false;
    public $descriptionStrictReplacementChar = ' ';

    public function initialize()
    {

        parent::initialize();
        $this->makeUnique();
        $this->format = 0;

        foreach ($this->parameters as $param) {
            $cmd          = explode('=', $param);
            $original_cmd = $cmd;
            foreach ($cmd as &$item) {
                $item = trim($item);
            }

            switch (strtolower($cmd[0])) {
                case 'long':
                    $this->format = 1;
                    break;
                case 'short':
                    $this->format = 2;
                    break;
                case 'length':case 'max_length':case 'maximum_length':
                    $this->max_description_length = $cmd[1];
                    break;
                case 'replacement_character':
                    $this->descriptionStrictReplacementChar = $original_cmd[1];
                    break;
                case 'strict':
                    if (!isset($cmd[1]) || strtolower($cmd[1]) == 'true') {
                        $cmd[1] = true;
                    } else {
                        $cmd[1] = false;
                    }

                    $this->descriptionStrict = $cmd[1];
                    break;
                case 'allow_empty_title':
                    if (!isset($cmd[1]) || strtolower($cmd[1]) == 'true') {
                        $cmd[1] = true;
                    } else {
                        $cmd[1] = false;
                    }

                    $this->allow_empty_title = $cmd[1];
                    break;
            }
        }

    }

    public function process($product)
    {

        switch ($this->format) {
            case 1: //Force Long
                $product->attributes['description'] = $product->description_long;
                break;
            case 2: //Force Short
                $product->attributes['description'] = $product->description_short;
                break;
            default:
                //By default pick long... if no long, pick short (updated from original behaviour Mar 4 -cg)
                if (strlen($product->description_long) == 0) {
                    $product->attributes['description'] = $product->description_short;
                } else {
                    $product->attributes['description'] = $product->description_long;
                }

                break;
        }

        if (!$this->allow_empty_title)
        //If description is empty, use title
        {
            if (strlen(trim($product->attributes['description'])) == 0) {
                $product->attributes['description'] = $product->attributes['title'];
            }
        }

        if ($this->descriptionStrict) {
            $description = $product->attributes['description'];
            //I really should use preg_replace here one day
            //$product->description = preg_replace('/[^A-Za-z0-9\-]/', '', $product->description);
            for ($i = 0; $i < strlen($description); $i++) {
                if (($description[$i] < "\x20") || ($description[$i] > "\x7E")) {
                    $description[$i] = $this->descriptionStrictReplacementChar;
                }
            }
            $product->attributes['description']      = $description;
            $product->attributes['description_long'] = $description;

        }

        if (strlen($product->attributes['description']) > $this->max_description_length) {
            //$product->attributes['description'] = substr($product->attributes['description'], 0, $this->max_description_length);
            //"wordwrap", taken off SE.
            $product->attributes['description'] = substr($product->attributes['description'], 0, strpos($product->attributes['description'], ' ', $this->max_description_length));
            //add ellipses
            if ($product->attributes['description'] < 5000) //some MAX_LENGTH (provider dependent)
            {
                $product->attributes['description'] .= '...';
            }

        }

    } //rule description process

}

//***********************************************************
//Discount
//***********************************************************

class AMWSCP_PFeedRuleDiscount extends AMWSCP_PFeedRule
{

    public $discount_amount          = 0;
    public $discount_sale_amount     = 0;
    public $discount_multiplier      = 1.00;
    public $discount_sale_multiplier = 1.00;

    public function initialize()
    {

        /*
        Note: BEDMAS means multiplier stronger than additive value
        rule discount(5)                        Take 5 dollars off
        rule discount(5, s)                    Take 5 dollars off sale price (if sale given - if sale not given, do not apply discount)
        rule discount(0.95, *)            Take 95% of price (5% discount)
        rule discount(0.95, *, s)        Take 95% of sale price (5% discount)
         */

        parent::initialize();

        foreach ($this->parameters as $this_parameter) {
            if (is_numeric($this_parameter)) {
                $number_value = $this_parameter;
                break;
            }
        }

        if (in_array('*', $this->parameters)) {
            //multiplier. Default number_value -> 1.00
            if (!isset($number_value)) {
                $number_value = 1;
            }

            if (in_array('s', $this->parameters)) {
                $this->discount_sale_multiplier = $number_value;
            } else {
                $this->discount_multiplier = $number_value;
            }

        } else {
            //Additive value
            if (!isset($number_value)) {
                $number_value = 0;
            }

            if (in_array('s', $this->parameters)) {
                $this->discount_sale_amount = $number_value;
            } else {
                $this->discount_amount = $number_value;
            }

        }

    }

    public function process($product)
    {

        //discount_multiplier should act on regular_price (used to be sale_price)
        if ($this->discount_amount > 0 || $this->discount_multiplier != 1) {
            $product->attributes['regular_price'] = intval($product->attributes['regular_price']) * intval($this->discount_multiplier) - intval($this->discount_amount);
            //$product->attributes['has_sale_price'] = true;
        }
        //Possible to do sale as a function of sale price, but IFF product->has_sale_price already
        if (($this->discount_sale_amount > 0 || $this->discount_sale_multiplier != 1) && $product->attributes['has_sale_price']) {
            $product->attributes['sale_price']     = $product->attributes['sale_price'] * $this->discount_sale_multiplier - $this->discount_sale_amount;
            $product->attributes['has_sale_price'] = true;
        }
        //Disallow negative price
        if ($product->attributes['has_sale_price'] && $product->attributes['sale_price'] < 0) {
            $product->attributes['sale_price'] = 0;
        }

    }

}

//***********************************************************
//AddPriceFlatFee
//***********************************************************

class AMWSCP_PFeedRuleAddPriceFlatFee extends AMWSCP_PFeedRule
{

    public $flat_amount = 0;

    public function initialize()
    {

        /*
        rule addPriceFlatFee(5)     // Add 5 dollars to price
         */

        parent::initialize();

        foreach ($this->parameters as $this_parameter) {
            if (is_numeric($this_parameter)) {
                $number_value = $this_parameter;
                break;
            }
        }

        //Additive value
        if (!isset($number_value) or $number_value < 0) {
            $number_value = 0;
        }

        $this->flat_amount = $number_value;

    }

    public function process($product)
    {

        if ($this->flat_amount > 0) {
            $product->attributes['regular_price'] += $this->flat_amount;

            if ($product->attributes['has_sale_price']) {
                $product->attributes['sale_price'] += $this->flat_amount;
            }
        }
    }

}

//***********************************************************
//rule FindAndSet(1,2,3,4)
//Find attribute (1) that contains string (2), if present, set another attribute (3) to value (4)
//Example 1: rule FindAndSet(title, "adult", valid, "false")
//Example 2: rule FindAndSet(title, "Hip", brand, "Hippolite")
//***********************************************************
class AMWSCP_PFeedRuleFindAndSet extends AMWSCP_PFeedRule
{

    public function initialize()
    {
        parent::initialize();
        if (!isset($this->parameters[0])) //If haystack does not exist, do not proceed
        {
            return;
        }

        //$this->order = 150;
    }

    public function process($product)
    {

        $this->resolveVirtualParameters();

        //assumes $this->parametersV[1] is blank. IE, if attribute[param[0]] is blank, set..
        //example: 
        //if brand is blank, set the stock status to out of stock
        if (count($this->parametersV) == 3) {
            if (!isset($product->attributes[$this->parametersV[0]])) {
                $product->attributes[$this->parametersV[1]] = $this->parametersV[2];
                return;
            } else {
                //0th parameter is set
                $findSet_haystack = $product->attributes[$this->parametersV[0]];
                $findSet_needle   = $this->parametersV[1];
                $findSet_pos      = stripos($findSet_haystack, $findSet_needle);
                if ($findSet_pos !== false) {
                    //if 1st parameter is found in 0th attribute
                    $product->attributes[$this->parametersV[2]] = null;
                    return;
                }
            }
        }

        //example: rule findAndSet(product_type_w,"",description,"")
        if (count($this->parametersV) == 2) {
            if (!isset($product->attributes[$this->parametersV[0]])) {
                $product->attributes[$this->parametersV[1]] = null;
                return;
            }
        }

        if (count($this->parametersV) == 4) {

            if ($this->parametersV[0] == 'stock_quantity') {
                $findSet_haystack = $product->attributes[$this->parametersV[0]];
                $findSet_needle   = $this->parametersV[1];
                if ($findSet_haystack == $findSet_needle) {
                    $product->attributes[$this->parametersV[2]] = $this->parametersV[3];
                }

            } else {
                $findSet_haystack = $product->attributes[$this->parametersV[0]];
                $findSet_needle   = $this->parametersV[1];
                $findSet_pos      = stripos($findSet_haystack, $findSet_needle);
                if ($this->parametersV[3] == 'false') {
                    $this->parametersV[3] = 0;
                }
                //to remove product.
                if ($findSet_pos !== false) {
                    $product->attributes[$this->parametersV[2]] = $this->parametersV[3];
                }
            }
        }
    }
}

//***********************************************************
//Google-Specific
//***********************************************************

class AMWSCP_PFeedRuleGooglecombotitle extends AMWSCP_PFeedRule
{

    public function process($product)
    {

        if ($this->parent_feed->google_combo_title) {

            $title_dash     = ' - ';
            $title_combo    = '';
            $title_original = $product->attributes['title'];
            $title_brand    = $product->attributes['brand'];
            $title_flavor   = $product->attributes['flavor'];
            $title_flavour  = $product->attributes['flavour'];
            $title_size     = $product->attributes['size'];
            $title_color    = $product->attributes['color'];

            //Modify Title to include Brand - Title - Flavor - Size - Color
            //IF attributes exist and aren't already present in title (removes redunancy)
            $title_combo = $product->attributes['title'];
            if (!empty($title_brand)) {
                $title_combo = $title_brand . $title_dash . $title_combo;
            }
            if (!empty($title_flavor) && stripos($title_original, $title_flavor) === false) {
                $title_combo = $title_combo . $title_dash . $title_flavor;
            }
            if (!empty($title_flavour) && stripos($title_original, $title_flavour) === false) {
                $title_combo = $title_combo . $title_dash . $title_flavour;
            }
            if (!empty($title_size) && stripos($title_original, $title_size) === false) {
                $title_combo = $title_combo . $title_dash . $title_size;
            }
            if (!empty($title_color) && stripos($title_original, $title_color) === false) {
                $title_combo = $title_combo . $title_dash . $title_color;
            }
            $product->attributes['title'] = $title_combo;
        }

    }

}

class AMWSCP_PFeedRuleGoogleexacttitle extends AMWSCP_PFeedRule
{

    public function process($product)
    {

        //Upper case the first character of each word in the title:
        //Google doesn't like block letters
        if (!$this->parent_feed->google_exact_title) {
            $product->attributes['title'] = ucwords(strtolower($product->attributes['title']));
        }

    }

}

//***********************************************************
//Price: adds currency to the price fields
//***********************************************************
class AMWSCP_PFeedRulePricestandard extends AMWSCP_PFeedRule
{

    public $unit = '';

    public function initialize()
    {

        parent::initialize();

        if (count($this->parameters) > 2) {
            $this->unit = $this->parameters[1];
        }

        $this->order = 220;

    }

    public function process($product)
    {

        global $amwcore;

        if ($amwcore->callSuffix == 'J') {
            $this->unit = $product->attributes['currency'];
        } else {
            //if currency_format is set to: "%1.2f CAD"
            if (strpos($this->parent_feed->currency_format, ' ') !== false) {
                $this->unit = '';
            } else {
                $this->unit = $this->parent_feed->currency;
            }

        }

        if (strlen($product->attributes['regular_price']) == 0) {
            //if regular doesn't exist, but sales price does
            if ($product->attributes['sale_price'] > 0) {
                $product->attributes['regular_price'] = $product->attributes['sale_price'];
            } else {
                $product->attributes['regular_price'] = '0.00';
            }

        }

        $product->attributes['regular_price'] = sprintf($this->parent_feed->currency_format, $product->attributes['regular_price']) . ' ' . $this->unit;
        //$this->parent_feed->getMapping('sale_price')->enabled = $product->attributes['has_sale_price'];
        if ($product->attributes['has_sale_price'] || $product->attributes['sale_price'] > 0) {
            $product->attributes['sale_price'] = sprintf($this->parent_feed->currency_format, $product->attributes['sale_price']) . ' ' . $this->unit;
        } elseif ($product->attributes['sale_price'] == '') {
            unset($product->attributes['sale_price']);
        }
        //unset sale_price, since rapidcart will still map an empty attribute

    }

}

//formats price to 2 decimal places
class AMWSCP_PFeedRulePricerounding extends AMWSCP_PFeedRule
{

    public function initialize()
    {

        parent::initialize();

        $this->digits = 2;
        if (count($this->parameters) > 0) {
            $this->digits = $this->parameters[0];
        }

        $this->order = 210; //Just before a Price-standard

    }

    public function process($product)
    {
        //string number_format ( float $number , int $decimals = 0 , string $dec_point = "." , string $thousands_sep = "," )
        $product->attributes['regular_price'] = number_format((double) $product->attributes['regular_price'], $this->digits, '.', '');
        if ($product->attributes['has_sale_price']) {
            $product->attributes['sale_price'] = number_format((double) $product->attributes['sale_price'], $this->digits, '.', '');
        }

    }

}

class AMWSCP_PFeedRulePricedecimalseparator extends AMWSCP_PFeedRule
{

    public function initialize()
    {

        parent::initialize();

        $this->separator = ',';
        if (count($this->parameters) > 0) {
            $this->separator = $this->parameters[0];
        }

        $this->order = 225; //Just After a Price-standard

    }

    public function process($product)
    {
        //string number_format ( float $number , int $decimals = 0 , string $dec_point = "." , string $thousands_sep = "," )
        $product->attributes['regular_price'] = str_replace('.', $this->separator, $product->attributes['regular_price']);
        if ($product->attributes['has_sale_price']) {
            $product->attributes['sale_price'] = str_replace('.', $this->separator, $product->attributes['sale_price']);
        }

    }

}

class AMWSCP_PFeedRuleNoVariations extends AMWSCP_PFeedRule
{

    public function initialize()
    {

        parent::initialize();

//all categories
        if (in_array('*', $this->parameters)) {
            $this->parent_feed->addAttributeDefault('allcat', $this_parameter, 'AMWSCP_PCatVar');
        }
//individual categories
        else {
            foreach ($this->parameters as $this_parameter) {
                if (is_numeric($this_parameter)) {
                    $this->parent_feed->addAttributeDefault('xVar', $this_parameter, 'AMWSCP_PCatVar');
                }
            }

        }
    }
}

class AMWSCP_PFeedRulePricegroup extends AMWSCP_PFeedRule
{

    public $unit = '';

    public function initialize()
    {

        parent::initialize();

        $this->limit_low  = 0;
        $this->limit_high = 10000;
        $this->term       = 'price group';
        if (count($this->parameters) > 0) {
            $this->limit_low = $this->parameters[0];
        }

        if (count($this->parameters) > 1) {
            $this->limit_high = $this->parameters[1];
        }

        if (count($this->parameters) > 2) {
            $this->term = $this->parameters[2];
        }

        $this->order = 190;

    }

    public function process($product)
    {

        if (isset($product->attributes['regular_price']) && strlen($product->attributes['regular_price']) > 0) {
            if ($product->attributes['regular_price'] < $this->limit_low) {
                $product->attributes['price_label_1'] = $this->term;
            }

        }

    }

}

//***********************************************************
//Remove Empty (blank) Attributes.
//example: rule removeEmptyAttributes(attribute-slug or custom field)
//TODO: Should allow multiple parameters/attributes
//***********************************************************

class AMWSCP_PFeedRuleRemoveEmptyAttributes extends AMWSCP_PFeedRule
{
    //Caution: if attribute does not exist, it's values will be blank and all products will be removed
    public function process($product)
    {
        $blankAttribute = $this->parameters[0];
        if (empty($product->attributes[$blankAttribute]) || $product->attributes[$blankAttribute] == '0.00') {
            $product->attributes['valid'] = false;
        }

        return true;
    }
}

//***********************************************************
//Shipping
//***********************************************************

class AMWSCP_PFeedRuleShipping extends AMWSCP_PFeedRule
{

    public $shipping_amount          = '0.00';
    public $shipping_multiplier      = 0;
    public $shipping_sale_multiplier = 0;
    public $shipping_type            = 'Ground';

    public function initialize()
    {

        /*
        Note: BEDMAS means multiplier stronger than additive value
        rule shipping(5)                        Shipping cost is $5
        rule shipping(0.95, *)            Shipping is 95% of the full price
        rule shipping(0.95, *, s)        Shipping is 95% of the sale price
        rule shipping(grnd|air, t)    Type
         */

        parent::initialize();

        foreach ($this->parameters as $this_parameter) {
            if (is_numeric($this_parameter)) {
                $number_value = $this_parameter;
                break;
            }
        }

        if (in_array('*', $this->parameters)) {
            //multiplier. Default number_value -> 1.00
            if (!isset($number_value)) {
                $number_value = 1;
            }

            if (in_array('s', $this->parameters)) {
                $this->shipping_sale_multiplier = $number_value;
            } else {
                $this->shipping_multiplier = $number_value;
            }

        } elseif (in_array('t', $this->parameters)) {
            $this->shipping_type = $this->parameters[0];
        } else {
            //Additive value
            if (!isset($number_value)) {
                $number_value = 0;
            }

            $this->shipping_amount = $number_value;
        }

        if ($this->parent_feed->providerName == 'Google') {
            $this->parent_feed->addAttributeDefault('shipping', 'none', 'AMWSCP_PGoogleShipping');
        }

    }

    public function process($product)
    {

        $product->attributes['shipping_amount'] =
        $this->shipping_amount + //Base amount
        $product->attributes['regular_price'] * $this->shipping_multiplier + //% of Price
        ($product->attributes['has_sale_price'] ? $product->attributes['sale_price'] * $this->shipping_sale_multiplier : 0); //% of Sale Price
        $product->attributes['shipping_type'] = $this->shipping_type;

    }

}

//***********************************************************
//Status: convert to "in stock" or "out of stock"
//***********************************************************

class AMWSCP_PFeedRuleStatusstandard extends AMWSCP_PFeedRule
{

    public function process($product)
    {

        if ($product->attributes['stock_status'] == 1) {
            $product->attributes['stock_status'] = 'in stock';
        } else {
            $product->attributes['stock_status'] = 'out of stock';
        }

    }

}

//***********************************************************
//Pos
//***********************************************************

class AMWSCP_PFeedRulePos extends AMWSCP_PFeedRule
{
// public function initialize() {
    //     $this->order = 190;
    // }
    public function process($product)
    {

        $this->resolveVirtualParameters();

        if (!isset($product->attributes[$this->parametersV[0]])) {
            return;
        }

        if (count($this->parametersV) < 2) {
            return;
        }

        $this->value = strpos($product->attributes[$this->parametersV[0]], $this->parametersV[1]);

    }

}

//***********************************************************
//This rule will allow the user to 'set' or 'fill in mising' attributes
//***********************************************************
class AMWSCP_PFeedRuleSetAll extends AMWSCP_PFeedRule
{

    public function initialize()
    {
        parent::initialize();
        if (!isset($this->parameters[0])) {
            return;
        }

    }
    public function process($product)
    {
        $product->attributes[$this->parameters[0]] = $this->parameters[1];
    }

}
//***********************************************************
//Strlen
//***********************************************************

class AMWSCP_PFeedRuleStrlen extends AMWSCP_PFeedRule
{

    public function process($product)
    {

        if (!isset($product->attributes[$this->parametersV[0]])) {
            $this->value = 0;
            return;
        }

        $this->value = strlen($product->attributes[$this->parametersV[0]]);

    }

}

//***********************************************************
//StrReplace: rule strReplace(find, replace, subject/string)
//***********************************************************

class AMWSCP_PFeedRuleStrReplace extends AMWSCP_PFeedRule
{

    public function process($product)
    {
        $this->resolveVirtualParameters();

        if (!isset($product->attributes[$this->parametersV[2]])) {
            return;
        }

        // if ( stripos($product->attributes[$this->parametersV[2]], $this->parametersV[0]) === false ) //if not found
        //     return;
        if (count($this->parametersV) == 3) {
            $this->value = str_replace($this->parametersV[0], $this->parametersV[1], $product->attributes[$this->parametersV[2]]);
            $product->attributes[$this->parametersV[2]] = $this->value;
        }

    }

}

//***********************************************************
//SubString: rule substr(source, start, length, true/false) as name
//The 4th parameter if true, assigns the source to the portion of string
//***********************************************************

class AMWSCP_PFeedRuleSubstr extends AMWSCP_PFeedRule
{

    public function initialize()
    {

        parent::initialize();
        $this->order = 205; //after rule pos

    }
    public function process($product)
    {
        $this->resolveVirtualParameters();

        if (!isset($product->attributes[$this->parametersV[0]])) {
            return;
        }

        if (count($this->parametersV) == 2) {
            $this->value = substr($product->attributes[$this->parametersV[0]], (int) $this->parametersV[1]);
        }

        if (count($this->parametersV) == 3) {
            $this->value = substr($product->attributes[$this->parametersV[0]], (int) $this->parametersV[1], (int) $this->parametersV[2]);
        }

        if (count($this->parametersV) == 4) {
            if ($this->parametersV[3]) {
                $product->attributes[$this->parametersV[0]] = substr($product->attributes[$this->parametersV[0]], (int) $this->parametersV[1], (int) $this->parametersV[2]);
            } else {
                $this->value = substr($product->attributes[$this->parametersV[0]], (int) $this->parametersV[1], (int) $this->parametersV[2]);
            }

        }

    }

}

//***********************************************************
//Tax
//***********************************************************

class AMWSCP_PFeedRuleTax extends AMWSCP_PFeedRule
{

    public $rate = 0;

    public function initialize()
    {

        parent::initialize();

        foreach ($this->parameters as $this_parameter) {
            if (is_numeric($this_parameter)) {
                $this->rate = $this_parameter;
                break;
            }
        }

        if ($this->parent_feed->providerName == 'AmazonSC') {
            $this->parent_feed->addAttributeDefault('tax', 'none', 'AMWSCP_PGoogleTax');
        }

    }

    public function process($product)
    {

        if (!isset($product->attributes['tax'])) {
            $product->attributes['tax'] = $this->rate;
        }

        if (!isset($product->attributes['tax_country'])) {
            $product->attributes['tax_country'] = 'US';
        }

    }

}

//***********************************************************
//Valid
//***********************************************************

class AMWSCP_PFeedRuleValidalways extends AMWSCP_PFeedRule
{

    public function process($product)
    {

        $product->attributes['valid'] = true;

    }

}

//***********************************************************
//Weight Unit
//append weight unit to weight (value)
//rule weightUnit(lb)
//***********************************************************

class AMWSCP_PFeedRuleWeightunit extends AMWSCP_PFeedRule
{

    public $unit = '';

    public function initialize()
    {

        parent::initialize();

        if (count($this->parameters) > 0) {
            $this->unit = $this->parameters[0];
        }

    }

    public function process($product)
    {

        if (!isset($product->attributes['weight']) || strlen(trim($product->attributes['weight'])) == 0) {
//TODO: ?Upon 'update all' -- PHP Warning:  Creating default object from empty value in ~/rules.php
            //$this->parent_feed->getMapping('weight')->enabled = false;
            return;
        }
        $this->weight_unit = $this->parent_feed->weight_unit;
        //woocommerce weight unit uses lbs, but many feed specs require just: lb
        if ($this->weight_unit == 'lbs') {
            $this->weight_unit = 'lb';
        }

        //$this->parent_feed->getMapping('weight')->enabled = true;
        if (strlen($this->unit) > 0) {
            $product->attributes['weight'] = $product->attributes['weight'] . ' ' . $this->unit;
        } else {
            $product->attributes['weight'] = $product->attributes['weight'] . ' ' . $this->weight_unit;
        }

    }
}
//***********************************************************
//Dimension Unit
//rule dimensionunit(width, "cm")
//***********************************************************

class AMWSCP_PFeedRuleDimensionunit extends AMWSCP_PFeedRule
{

    public $dimension = '';
    public $unit      = '';

    public function initialize()
    {

        parent::initialize();

        if (count($this->parameters) == 1) {
            $this->dimension = $this->parameters[0];
        }
        if (count($this->parameters) == 2) {
            $this->dimension = $this->parameters[0];
            $this->unit      = $this->parameters[1];
        }

    }

    public function process($product)
    {

        //if length,width or height is not set or is == 0
        // if ( !isset($product->attributes[$this->dimension]) || strlen( trim($product->attributes[$this->dimension]) ) == 0 ) {
        //     return;
        // }
        //null, empty, or 0 value dimensions shouldn't be included.
        if (empty($product->attributes[$this->dimension])) {
            $product->attributes[$this->dimension] = null;
            return;
        }

        //$this->parent_feed->getMapping('weight')->enabled = true;
        if (strlen($this->unit) > 0) {
            $product->attributes[$this->dimension] = $product->attributes[$this->dimension] . ' ' . $this->unit;
        } else {
            $product->attributes[$this->dimension] = $product->attributes[$this->dimension] . ' ' . $this->parent_feed->dimension_unit;
        }

    }
//rule word(n, b)
    //returns nth word in string b.

}

//***********************************************************
//strip_tags: Strip HTML and PHP tags from a string
//***********************************************************

class AMWSCP_PFeedRuleStrip_tags extends AMWSCP_PFeedRule
{

    public function process($product)
    {
        $this->resolveVirtualParameters();

        if (!isset($product->attributes[$this->parametersV[0]])) {
            return;
        }

        if (isset($this->parametersV[1])) {
            $allowable_tags = $this->parametersV[1];
        }

        $product->attributes[$this->parametersV[0]] = strip_tags($product->attributes[$this->parametersV[0]], $allowable_tags);
    }

}

//***********************************************************
//Remove attribute condition:
//rule removeProductByAttributeCondition(attribute,condition,value)
//example: rule removeProductByAttributeCondition(regular_price,">",10000)
//removes attributes with regular_price greater than $10000
//***********************************************************
class AMWSCP_PFeedRuleRemoveProductByAttributeCondition extends AMWSCP_PFeedRule
{

    public $arrConditions = array(">", "<", "=", "<>", "<=", ">=", "!="); //only allow these conditions

    //needs to be called after discount() but before pricestandard
    //needs to be before price round too

    public function initialize()
    {
        parent::initialize();
        $this->order = 205;
    }

    public function process($product)
    {

        $this->resolveVirtualParameters();
        //if first param is not set, end;
        if (!isset($this->parametersV[0])) {
            return;
        }

        //check if there are three arguments
        if (count($this->parametersV) == 3) {

            //if 'price' -- get lowest price
            if ($this->parametersV[0] == 'price') {
                if ($product->attributes['has_sale_price']) {
                    $attr = $product->attributes['sale_price'];
                } else {
                    $attr = $product->attributes['regular_price'];
                }

            } else {
                $attr = $product->attributes[$this->parametersV[0]];
            }
            //target attribute, ex: brand
            //$attr = $this->parametersV[0];
            $condition = $this->parametersV[1]; //>,<,=,<>
            $condValue = $this->parametersV[2];
            if (in_array($condition, $this->arrConditions)) {
                switch ($condition) {
                    case '>':
                        if ($attr > $condValue) {
                            $product->attributes['valid'] = false;
                        }

                        break;
                    case '<':
                        if ($attr < $condValue) {
                            $product->attributes['valid'] = false;
                        }

                        break;
                    case '=':
                        if ($attr == $condValue) {
                            $product->attributes['valid'] = false;
                        }

                        break;
                    case '!=':
                        if ($attr != $condValue) {
                            $product->attributes['valid'] = false;
                        }

                        break;
                    case '<=':
                        if ($attr <= $condValue) {
                            $product->attributes['valid'] = false;
                        }

                        break;
                    case '>=':
                        if ($attr >= $condValue) {
                            $product->attributes['valid'] = false;
                        }

                        break;
                    default:
                        //$product->attributes['valid'] = false;
                        break;
                }
            }

        }

    }

}

//***********************************************************
//Remove attribute condition:
//rule removeAttributeCondition(attribute,condition,value)
//example: rule removeAttributeCondition(regular_price,">",10000)
//removes attributes with regular_price greater than $10000
//***********************************************************
class AMWSCP_PFeedRuleRemoveAttributeCondition extends AMWSCP_PFeedRule
{

    public $arrConditions = array(">", "<", "=", "<>", "<=", ">=", "!="); //only allow these conditions

    //needs to be called after discount() but before pricestandard
    //needs to be before price round too

    public function initialize()
    {
        parent::initialize();
        $this->order = 205;
    }

    public function process($product)
    {

        $this->resolveVirtualParameters();
        //if first param is not set, end;
        if (!isset($this->parametersV[0])) {
            return;
        }

        //check if there are three arguments
        if (count($this->parametersV) == 3) {

            //if 'price' -- get lowest price
            if ($this->parametersV[0] == 'price') {
                if ($product->attributes['has_sale_price']) {
                    $attr = $product->attributes['sale_price'];
                } else {
                    $attr = $product->attributes['regular_price'];
                }

            } else {
                $attr = $product->attributes[$this->parametersV[0]];
            }
            //target attribute, ex: brand
            //$attr = $this->parametersV[0];
            $condition = $this->parametersV[1]; //>,<,=,<>
            $condValue = $this->parametersV[2];
            if (in_array($condition, $this->arrConditions)) {
                switch ($condition) {
                    case '>':
                        if ($attr > $condValue) {
                            $product->attributes['valid'] = false;
                        }

                        break;
                    case '<':
                        if ($attr < $condValue) {
                            $product->attributes['valid'] = false;
                        }

                        break;
                    case '=':
                        if ($attr == $condValue) {
                            $product->attributes['valid'] = false;
                        }

                        break;
                    case '!=':
                        if ($attr != $condValue) {
                            $product->attributes['valid'] = false;
                        }

                        break;
                    case '<=':
                        if ($attr <= $condValue) {
                            $product->attributes['valid'] = false;
                        }

                        break;
                    case '>=':
                        if ($attr >= $condValue) {
                            $product->attributes['valid'] = false;
                        }

                        break;
                    default:
                        //$product->attributes['valid'] = false;
                        break;
                }
            }

        }

    }

}

//Escape quotes in csv/txt files
//rule csvStandard(title, 80)
//warning: should only be applied once
class AMWSCP_PFeedRuleCSVStandard extends AMWSCP_PFeedRule
{
    //csvstandard should also elminiate characters outside \x20-\x7E ...what about other language chars? -cg
    public function initialize()
    {
        parent::initialize();
        $this->order = 210; //after description(strict)
    }
    public function process($product)
    {

        //$this->resolveVirtualParameters();

        if (!isset($product->attributes[$this->parameters[0]]) || !is_string($product->attributes[$this->parameters[0]])) {
            return;
        }

        if(isset($this->parameters[1])&&($this->parameters[1]=='overwrite')){
            $product->attributes[$this->parameters[0]] = str_replace('""','"',$product->attributes[$this->parameters[0]]);
            return;
        }

        if (!isset($this->parameters[1])) {
            $attribute_max_length = 10000;
        } else {
            $attribute_max_length = $this->parameters[1];
        }

        $attrValue                                 = (strlen($product->attributes[$this->parameters[0]]) > $attribute_max_length) ? substr($product->attributes[$this->parameters[0]], 0, $attribute_max_length) : $product->attributes[$this->parameters[0]];
        $attrValue                                 = str_replace('"', '""', $attrValue);
        $attrValue                                 = trim($attrValue);
        $product->attributes[$this->parameters[0]] = $attrValue;

    }
}

/********
 * Similar to strict description, but for a specified attribute
 * Example: rule strictAttribute(description_short)
 ***********/
class AMWSCP_PFeedRuleStrictAttribute extends AMWSCP_PFeedRule
{

    public $descriptionStrictReplacementChar = ' ';

    public function process($product)
    {

        if (isset($product->attributes[$this->parameters[0]])) {

            $strict_attribute = $product->attributes[$this->parameters[0]];
            if (is_array($strict_attribute)) {
                return;
            }
            //fix warning: strlen() expects parameter 1 to be string, array given
            //replace non-printable characters
            for ($i = 0; $i < strlen($strict_attribute); $i++) {
                if (($strict_attribute[$i] < "\x20") || ($strict_attribute[$i] > "\x7E")) {
                    $strict_attribute[$i] = $this->descriptionStrictReplacementChar;
                }
            }
            $product->attributes[$this->parameters[0]] = $strict_attribute;
        }

    }
}

//Try to find identifiers (upc, brand, mpn) within short description
class AMWSCP_PFeedRuleFindIdentifiers extends AMWSCP_PFeedRule
{

    public function process($product)
    {

        if (!isset($product->attributes['description_short'])) {
            return;
        }

        //separate description by new line
        $data = explode(PHP_EOL, $product->attributes['description_short']);
        foreach ($data as $datum) {
            if (strpos($datum, ':') !== false) {
                $more = explode(":", $datum);
                if (trim(strtolower($more[0])) == 'upc') {
                    $product->attributes['upc'] = trim($more[1]);
                }

                if (trim(strtolower($more[0])) == 'brand') {
                    $product->attributes['brand'] = trim($more[1]);
                }

                if (trim(strtolower($more[0])) == 'mpn') {
                    $product->attributes['mpn'] = trim($more[1]);
                }

            }
        }
    }

}

//returns the most granular category of a product
//places the subcategory into the 'subcategory' attribute
class AMWSCP_PFeedRuleGetSubcategory extends AMWSCP_PFeedRule
{
    public function process($post)
    {
        //woocommerce product category
        $taxonomies = array('product_cat');

        //get the post id
        $id_branch_parent = $post->attributes['id'];
        if ($post->attributes['isVariation'] == true) {
            $id_branch_parent = $post->attributes['item_group_id'];
        }

        //let's get an array with ids of all the terms a post has
        $post_tids = wp_get_post_terms($id_branch_parent, 'product_cat', array("fields" => "ids"));

        //in case of multiple ids we have to loop through them
        foreach ($post_tids as $tid) {
            //get the term information by id
            $tobj = get_term_by('id', $tid, 'product_cat');
            //store the term names into an array
            $pnb_name_arr[] = $tobj;
            // $pnb_name_arr['name'] = $tobj->name;
        }

        usort($pnb_name_arr, function ($a, $b) {
            return $a->parent - $b->parent;
        });
        //error_log(print_r($pnb_name_arr,TRUE));

        $subcategory = '';
        foreach ($pnb_name_arr as $pnb) {
            $subcategory = $pnb->name;
        }
        $post->attributes['subcategory'] = $subcategory;
    }
}

// Removes 0 quantity products if stock is managed
class AMWSCP_PFeedRuleExcludeoos extends AMWSCP_PFeedRule
{

    public function initialize()
    {
        parent::initialize();
    }

    public function process($product)
    {
        if(empty($product->attributes['escaperule'])){
            if (array_key_exists('manage_stock', $product->attributes) && strtolower($product->attributes['manage_stock']) == 'yes') {
                if ( array_key_exists('stock_quantity', $product->attributes) && $product->attributes['stock_quantity'] <= 0) {
                    $product->attributes['valid'] = false;
                }
            }
        }
    }

}
