<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class MarkUpMarkDownEventTypeCodeType extends EbatNs_FacetType
{
	const CodeType_MarkUp = 'MarkUp';
	const CodeType_MarkDown = 'MarkDown';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('MarkUpMarkDownEventTypeCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_MarkUpMarkDownEventTypeCodeType = new MarkUpMarkDownEventTypeCodeType();
?>