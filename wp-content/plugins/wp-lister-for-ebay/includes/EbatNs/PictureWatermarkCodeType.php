<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class PictureWatermarkCodeType extends EbatNs_FacetType
{
	const CodeType_User = 'User';
	const CodeType_Icon = 'Icon';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('PictureWatermarkCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_PictureWatermarkCodeType = new PictureWatermarkCodeType();
?>