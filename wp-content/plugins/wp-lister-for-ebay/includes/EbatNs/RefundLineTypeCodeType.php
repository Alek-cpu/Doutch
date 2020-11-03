<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class RefundLineTypeCodeType extends EbatNs_FacetType
{
	const CodeType_PurchasePrice = 'PurchasePrice';
	const CodeType_ShippingPrice = 'ShippingPrice';
	const CodeType_Additional = 'Additional';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('RefundLineTypeCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_RefundLineTypeCodeType = new RefundLineTypeCodeType();
?>