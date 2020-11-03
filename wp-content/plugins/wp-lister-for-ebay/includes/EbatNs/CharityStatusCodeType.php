<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class CharityStatusCodeType extends EbatNs_FacetType
{
	const CodeType_Valid = 'Valid';
	const CodeType_NoLongerValid = 'NoLongerValid';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('CharityStatusCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_CharityStatusCodeType = new CharityStatusCodeType();
?>