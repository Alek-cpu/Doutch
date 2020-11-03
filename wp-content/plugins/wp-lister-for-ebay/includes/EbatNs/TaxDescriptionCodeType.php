<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class TaxDescriptionCodeType extends EbatNs_FacetType
{
	const CodeType_SalesTax = 'SalesTax';
	const CodeType_ElectronicWasteRecyclingFee = 'ElectronicWasteRecyclingFee';
	const CodeType_TireRecyclingFee = 'TireRecyclingFee';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('TaxDescriptionCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_TaxDescriptionCodeType = new TaxDescriptionCodeType();
?>