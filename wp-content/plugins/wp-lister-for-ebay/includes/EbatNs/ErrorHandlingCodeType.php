<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class ErrorHandlingCodeType extends EbatNs_FacetType
{
	const CodeType_Legacy = 'Legacy';
	const CodeType_BestEffort = 'BestEffort';
	const CodeType_AllOrNothing = 'AllOrNothing';
	const CodeType_FailOnError = 'FailOnError';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('ErrorHandlingCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_ErrorHandlingCodeType = new ErrorHandlingCodeType();
?>