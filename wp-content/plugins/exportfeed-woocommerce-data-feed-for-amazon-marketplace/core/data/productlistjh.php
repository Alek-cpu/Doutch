<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class AMWSCP_PProductList {

	public $products = null;
	
	public $productStart = -1;

	//********************************************************************
	//Load the products from the DB
	//********************************************************************

	public function loadProducts($parent) {

		global $amwcore;

		$db = JFactory::getDBO();

		$parent->logActivity('Reading products...');

		if ($parent->has_product_range)
			$limit = 'LIMIT ' . $parent->product_limit_low . ', ' . ($parent->product_limit_high - $parent->product_limit_low);
		else
			$limit = '';

		if ($this->productStart > -1)
			$limit = 'LIMIT ' . $this->productStart . ', 50000';

		$query = '
			SELECT product.*, tblCategories.categories
				#price.price_value, currency.currency_symbol
			FROM #__hikashop_product product
			#LEFT JOIN #__hikashop_price as price on (price.price_product_id = product.product_id)
			#LEFT JOIN #__hikashop_currency currency on (currency.currency_id = price.price_currency_id)
			LEFT JOIN
				(
					SELECT product_id, GROUP_CONCAT(category_id) as categories
					FROM #__hikashop_product_category
				) tblCategories ON (tblCategories.product_id = product.product_id)
			' . $limit;

		$db->setQuery($query);
		$this->products = $db->loadObjectList();


	}

	//********************************************************************
	//Convert the ProductList
	//********************************************************************

	public function getProductList($parent, $remote_category) {

		global $amwcore;

		//********************************************************************
		//Initialize
		//********************************************************************
		$parent->logActivity('Retrieving product list from database');
		if ($this->products == null)
			$this->loadProducts($parent);
		$db = JFactory::getDBO();

		@include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php');

		//********************************************************************
		//Convert the RapidCart_Product List into a Cart-Product Product List (ListItems)
		//********************************************************************

		foreach ($this->products as $index => $prod) {

			if ($index % 100 == 0)
				$parent->logActivity('Converting master product list...' . round($index / count($this->products) * 100) . '%' );

			$skip = true;
			$cats = explode(',', $prod->categories);
			if (count($cats) == 0)
				$cats[] = $prod->category;
			foreach($cats as $cat)
				if ($parent->categories->containsCategory($cat)) {
					$skip = false;
					break;
				}
			if ($skip)
				continue;

			$item = new AMWSCP_PAProduct();

			//Basics
			$item->id = $prod->product_id;

			//Attributes
			$attributes = array();
			$productClass = hikashop_get('class.product');
			$product = $productClass->get($prod->product_id);

			$attributes['stock_quantity'] = $product->product_quantity;
			$attributes['title'] = $product->product_name;
			$attributes['handle'] = $product->product_code;
			$attributes['product_parent_id'] = $product->product_parent_id;
			$attributes['sale_price_dates_from'] = $product->product_sale_start;
			$attributes['sale_price_dates_to'] = $product->product_sale_end;
			$attributes['link'] = JURI::base() . 'index.php?option=com_hikashop&ctrl=product&task=show&cid=' . $prod->product_id;
			$attributes['valid'] = 'true';

			$db->setQuery('SELECT file_path FROM #__hikashop_file WHERE file_ref_id=' . $prod->product_id . ' ORDER BY file_ordering');
			$image = $db->loadResult();
			if (isset($image))
				$image = JURI::base() . 'images/com_hikashop/upload/' . $image;
			else
				$image = '';
			$attributes['feature_imgurl'] = $image;

			$item->attributes = $attributes;

			//Prices
			$db->setQuery('
				SELECT price.price_value, currency.currency_symbol
				FROM #__hikashop_price as price
				LEFT JOIN #__hikashop_currency currency on (currency.currency_id = price.price_currency_id)
				WHERE price.price_product_id = ' . $prod->product_id);
			$item->prices = $db->loadObjectList();
			if ($item->prices != null) {
				$item->attributes['regular_price'] = $item->prices [0]->price_value;
				$item->attributes['has_sale_price'] = false;
				foreach($item->prices as $price_index => $price) {
					$item->attributes['price' . $price_index] = $price->price_value;
					$item->attributes['currency' . $price_index] = $price->currency_symbol;
				}
			}

			//Carry on defining product
			$item->attributes['id'] = $prod->product_id;
			$item->taxonomy = '';
			$item->attributes['isVariable'] = false;
			$item->attributes['isVariation'] = false;
			$item->description_short = substr(strip_tags($product->product_description), 0, 8000);
			$item->description_long = substr(strip_tags($product->product_description), 0, 8000);

			//if ($prod->parent_id > 0)
				//$item->attributes['item_group_id'] = $prod->parent_id;

			//Fetch any default attributes Stage 0 (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 0 && !$thisDefault->isRuled && !isset($item->attributes[$thisDefault->attributeName]))
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);

			//Deal with Product type... ensure it exists
			if (!isset($item->attributes['product_type']) || strlen($item->attributes['product_type']) == 0) {
				$item->attributes['product_type'] = '';
				$pcat = $parent->categories->idToCategory($cats[0]);
				if ($pcat != null)
					$item->attributes['product_type'] = $pcat->title;
			}

			$item->attributes['category'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $item->attributes['product_type']));
			$item->attributes['localCategory'] = str_replace(".and.", " & ", str_replace(".in.", " > ", $item->attributes['product_type']));
			$item->attributes['localCategory'] = str_replace("|", ">", $item->attributes['localCategory']);

			$item->attributes['condition'] = 'New';
			if (isset($item->attributes['grams']))
				$item->attributes['weight'] = $item->attributes['grams'] / 1000;

			//In-stock status
			$item->attributes['stock_status'] = 1;
			if ($item->attributes['stock_quantity'] == 0)
				$item->attributes['stock_status'] = 0;
			//Hide out of stock
			if (($amwcore->hide_outofstock) && ($item->attributes['stock_status'] == 0))
				$item->attributes['valid'] = false;

			unset($item->attributes['price']);
			unset($item->attributes['grams']);
			unset($item->attributes['inventory_quantity']);

			if (isset($item->attributes['vendor']) && !isset($item->attributes['brand']))
				$item->attributes['brand'] = $item->attributes['vendor'];

			//Fetch any default attributes (Mapping 3.0)
			foreach ($parent->attributeDefaults as $thisDefault)
				if ($thisDefault->stage == 1 && !$thisDefault->isRuled)
					$item->attributes[$thisDefault->attributeName] = $thisDefault->getValue($item);
	  
			$parent->handleProduct($item);
			unset($item);

		}

  }

}