<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'PaginatedTransactionArrayType.php';

/**
  * This is the base response type of the <b>GetItemsAwaitingFeedback</b> call. This call retrieves all completed order line items for which the user (buyer or seller) still needs to leave Feedback for their order partner.
  * 
 **/

class GetItemsAwaitingFeedbackResponseType extends AbstractResponseType
{
	/**
	* @var PaginatedTransactionArrayType
	**/
	protected $ItemsAwaitingFeedback;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetItemsAwaitingFeedbackResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'ItemsAwaitingFeedback' =>
				array(
					'required' => false,
					'type' => 'PaginatedTransactionArrayType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				)));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
));
	}

	/**
	 * @return PaginatedTransactionArrayType
	 **/
	function getItemsAwaitingFeedback()
	{
		return $this->ItemsAwaitingFeedback;
	}

	/**
	 * @return void
	 **/
	function setItemsAwaitingFeedback($value)
	{
		$this->ItemsAwaitingFeedback = $value;
	}

}
?>
