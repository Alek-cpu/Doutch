<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'DisputeType.php';

/**
  *  This is the base response type for the <b>GetDispute</b> call. This call retrieves the details of a seller-initiated dispute. Seller-initiated disputes include mutually-cancelled transactions and unpaid items.
  * <br/><br/>
  * <span class="tablenote"><strong>Note:</strong>
  * This call does not support buyer-initiated cases created through eBay's Resolution Center. Buyer-initiated cases include Item Not Received (INR) and escalated Return cases. To retrieve and manage eBay Money Back Guarantee cases, the Case Management calls of the <a href="http://developer.ebay.com/Devzone/post-order/index.html" target="_blank">Post-Order API</a> can be used instead.
  * </span>
  * 
 **/

class GetDisputeResponseType extends AbstractResponseType
{
	/**
	* @var DisputeType
	**/
	protected $Dispute;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetDisputeResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'Dispute' =>
				array(
					'required' => false,
					'type' => 'DisputeType',
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
	 * @return DisputeType
	 **/
	function getDispute()
	{
		return $this->Dispute;
	}

	/**
	 * @return void
	 **/
	function setDispute($value)
	{
		$this->Dispute = $value;
	}

}
?>
