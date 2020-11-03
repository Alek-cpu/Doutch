<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class AddressOwnerCodeType extends EbatNs_FacetType
{
	const CodeType_PayPal = 'PayPal';
	const CodeType_eBay = 'eBay';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('AddressOwnerCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_AddressOwnerCodeType = new AddressOwnerCodeType();
?>