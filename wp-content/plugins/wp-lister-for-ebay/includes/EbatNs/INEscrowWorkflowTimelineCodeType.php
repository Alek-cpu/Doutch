<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class INEscrowWorkflowTimelineCodeType extends EbatNs_FacetType
{
	const CodeType_Default = 'Default';
	const CodeType_WorkflowA = 'WorkflowA';
	const CodeType_WorkflowB = 'WorkflowB';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('INEscrowWorkflowTimelineCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_INEscrowWorkflowTimelineCodeType = new INEscrowWorkflowTimelineCodeType();
?>