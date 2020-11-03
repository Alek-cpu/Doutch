<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class SkypeContactOptionCodeType extends EbatNs_FacetType
{
	const CodeType_Chat = 'Chat';
	const CodeType_Voice = 'Voice';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('SkypeContactOptionCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_SkypeContactOptionCodeType = new SkypeContactOptionCodeType();
?>