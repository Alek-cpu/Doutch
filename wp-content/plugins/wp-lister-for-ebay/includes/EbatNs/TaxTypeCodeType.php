<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class TaxTypeCodeType extends EbatNs_FacetType
{
	const CodeType_SalesTax = 'SalesTax';
	const CodeType_WasteRecyclingFee = 'WasteRecyclingFee';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('TaxTypeCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_TaxTypeCodeType = new TaxTypeCodeType();
?>