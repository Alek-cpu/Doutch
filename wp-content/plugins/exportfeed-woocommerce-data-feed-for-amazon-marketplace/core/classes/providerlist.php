<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class AMWSCP_PProviderList {

	public $items = array();

	public function __construct() {

		global $amwcore;

		//***************************************************
		//Targetted Feeds
		//***************************************************
        $np = $this->addProvider('AmazonSC', 'Amazon Seller Central', 'txt'); $np->prettyName = 'Amazon Seller'; // st
        $np = $this->addProvider('Productlistraw', 'Product List Plain Text Export', 'txt');
        $np->prettyName = 'Raw Export';
	}

	public function addProvider($name, $description, $fileformat = 'xml') {
		$np = new stdClass();
		$np->name = $name;
		$np->prettyName = $name; //Used by ManageFeeds Page
		$np->description = $description;
		$np->fileformat = $fileformat;
		$this->items[] = $np;
		return $np;
	}

	public function asOptionList() {
		$output = '';
		foreach($this->items as $item)
			$output .= '
						<option value="' . $item->name . '">' . $item->description . '</option>';
		return $output;
	}

	public function getExtensionByType($type) {
		//Used by ManageFeeds to create a filename
		foreach($this->items as $provider)
			if ($provider->name == $type)
				return $provider->fileformat;
		return '';
	}

	public function getFileFormatByType($type) {
		//Used by ManageFeeds to create a filename
		foreach($this->items as $provider)
			if ($provider->name == $type)
				return $provider->fileformat;
		return '';
	}

	public function getPrettyNameByType($type) {
		//Used by ManageFeeds to create a filename
		foreach($this->items as $provider)
			if ($provider->name == $type)
				return $provider->prettyName;
		return '';
	}

}