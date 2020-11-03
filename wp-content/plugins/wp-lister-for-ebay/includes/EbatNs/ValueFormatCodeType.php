<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class ValueFormatCodeType extends EbatNs_FacetType
{
	const CodeType_FullDate = 'FullDate';
	const CodeType_PartialDate = 'PartialDate';
	const CodeType_Year = 'Year';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('ValueFormatCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_ValueFormatCodeType = new ValueFormatCodeType();
?>