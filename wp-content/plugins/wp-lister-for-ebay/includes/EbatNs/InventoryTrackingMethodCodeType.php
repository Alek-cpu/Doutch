<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class InventoryTrackingMethodCodeType extends EbatNs_FacetType
{
	const CodeType_ItemID = 'ItemID';
	const CodeType_SKU = 'SKU';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('InventoryTrackingMethodCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_InventoryTrackingMethodCodeType = new InventoryTrackingMethodCodeType();
?>