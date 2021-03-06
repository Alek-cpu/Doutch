<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class PolicyComplianceStatusCodeType extends EbatNs_FacetType
{
	const CodeType_Good = 'Good';
	const CodeType_Fair = 'Fair';
	const CodeType_Poor = 'Poor';
	const CodeType_Failing = 'Failing';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('PolicyComplianceStatusCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_PolicyComplianceStatusCodeType = new PolicyComplianceStatusCodeType();
?>