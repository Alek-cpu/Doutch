<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractRequestType.php';
require_once 'DetailNameCodeType.php';

/**
  * This is the base request type for the <b>GeteBayDetails</b> call. This call retrieves the latest eBay feature-related metadata values that are supported when listing items. This metadata includes country codes, currency codes, Item Specifics thresholds, supported Return Policy values, available shipping carriers and shipping service options, and more. This call may be used to keep metadata up-to-date in your applications.
  * <br><br>
  * In some cases, the data returned in the response will vary according to the eBay site that you use for the request.
  * 
 **/

class GeteBayDetailsRequestType extends AbstractRequestType
{
	/**
	* @var DetailNameCodeType
	**/
	protected $DetailName;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GeteBayDetailsRequestType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'DetailName' =>
				array(
					'required' => false,
					'type' => 'DetailNameCodeType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => true,
					'cardinality' => '0..*'
				)));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
));
	}

	/**
	 * @return DetailNameCodeType
	 * @param integer $index 
	 **/
	function getDetailName($index = null)
	{
		if ($index !== null)
		{
			return $this->DetailName[$index];
		}
		else
		{
			return $this->DetailName;
		}
	}

	/**
	 * @return void
	 * @param DetailNameCodeType $value
	 * @param integer $index 
	 **/
	function setDetailName($value, $index = null)
	{
		if ($index !== null)
		{
			$this->DetailName[$index] = $value;
		}
		else
		{
			$this->DetailName= $value;
		}
	}

	/**
	 * @return void
	 * @param DetailNameCodeType $value
	 **/
	function addDetailName($value)
	{
		$this->DetailName[] = $value;
	}

}
?>
