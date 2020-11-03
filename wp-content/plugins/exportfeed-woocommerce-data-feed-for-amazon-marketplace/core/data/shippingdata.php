<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 //Retrieves user-defined shipping settings and saves into class-local variable
class AMWSCP_PShippingData {

	function __construct($parentfeed) {
		global $amwcore;
		$loadProc = 'loadShippingData' . $amwcore->callSuffix;
		return $this->$loadProc($parentfeed);
	}

	function loadShippingDataJ($parentfeed) {
	}

	function loadShippingDataJH($parentfeed) {
	}

	function loadShippingDataJS($parentfeed) {
	}

	function loadShippingDataW( $parentfeed ) 
	{
		

	}

	function loadShippingDataWe($parentfeed) {
	}

}