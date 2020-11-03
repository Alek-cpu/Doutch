<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';
require_once 'FeedbackDetailType.php';

/**
  * This type is used by the <b>FeedbackDetailArray</b> container that is returned in the <b>GetFeedback</b> call. The <b>FeedbackDetailArray</b> container consists of an array of one or more Feedback entries. The Feedback entries that are returned will depend on the fields/values included in the call request.
  * 
 **/

class FeedbackDetailArrayType extends EbatNs_ComplexType
{
	/**
	* @var FeedbackDetailType
	**/
	protected $FeedbackDetail;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('FeedbackDetailArrayType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'FeedbackDetail' =>
				array(
					'required' => false,
					'type' => 'FeedbackDetailType',
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
	 * @return FeedbackDetailType
	 * @param integer $index 
	 **/
	function getFeedbackDetail($index = null)
	{
		if ($index !== null)
		{
			return $this->FeedbackDetail[$index];
		}
		else
		{
			return $this->FeedbackDetail;
		}
	}

	/**
	 * @return void
	 * @param FeedbackDetailType $value
	 * @param integer $index 
	 **/
	function setFeedbackDetail($value, $index = null)
	{
		if ($index !== null)
		{
			$this->FeedbackDetail[$index] = $value;
		}
		else
		{
			$this->FeedbackDetail= $value;
		}
	}

	/**
	 * @return void
	 * @param FeedbackDetailType $value
	 **/
	function addFeedbackDetail($value)
	{
		$this->FeedbackDetail[] = $value;
	}

}
?>
