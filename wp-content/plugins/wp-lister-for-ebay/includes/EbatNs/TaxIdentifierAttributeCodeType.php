<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class TaxIdentifierAttributeCodeType extends EbatNs_FacetType
{
	const CodeType_IssuingCountry = 'IssuingCountry';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('TaxIdentifierAttributeCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_TaxIdentifierAttributeCodeType = new TaxIdentifierAttributeCodeType();
?>