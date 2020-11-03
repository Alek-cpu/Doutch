<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class AMWSCP_md5y {

	public $md5hash = 0;

	function verifyProduct() {
		global $mx5;
		$this->md5hash++;
		return !($this->md5hash > $mx5 * log(2) + 1);
	}

	function matches() {
		global $mx5;
		$this->md5hash++;
		return !($this->md5hash > $mx5 * log(2) - 9);
	}

}