<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'RecommendationsType.php';

/**
  * This is the base response type for the <b>GetCategorySpecifics</b> call. This call retrieves recommended Item Specifics names and values for one or multiple eBay Categories.
  * 
 **/

class GetCategorySpecificsResponseType extends AbstractResponseType
{
	/**
	* @var RecommendationsType
	**/
	protected $Recommendations;

	/**
	* @var string
	**/
	protected $TaskReferenceID;

	/**
	* @var string
	**/
	protected $FileReferenceID;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetCategorySpecificsResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'Recommendations' =>
				array(
					'required' => false,
					'type' => 'RecommendationsType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => true,
					'cardinality' => '0..*'
				),
				'TaskReferenceID' =>
				array(
					'required' => false,
					'type' => 'string',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'FileReferenceID' =>
				array(
					'required' => false,
					'type' => 'string',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				)));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
));
	}

	/**
	 * @return RecommendationsType
	 * @param integer $index 
	 **/
	function getRecommendations($index = null)
	{
		if ($index !== null)
		{
			return $this->Recommendations[$index];
		}
		else
		{
			return $this->Recommendations;
		}
	}

	/**
	 * @return void
	 * @param RecommendationsType $value
	 * @param integer $index 
	 **/
	function setRecommendations($value, $index = null)
	{
		if ($index !== null)
		{
			$this->Recommendations[$index] = $value;
		}
		else
		{
			$this->Recommendations= $value;
		}
	}

	/**
	 * @return void
	 * @param RecommendationsType $value
	 **/
	function addRecommendations($value)
	{
		$this->Recommendations[] = $value;
	}

	/**
	 * @return string
	 **/
	function getTaskReferenceID()
	{
		return $this->TaskReferenceID;
	}

	/**
	 * @return void
	 **/
	function setTaskReferenceID($value)
	{
		$this->TaskReferenceID = $value;
	}

	/**
	 * @return string
	 **/
	function getFileReferenceID()
	{
		return $this->FileReferenceID;
	}

	/**
	 * @return void
	 **/
	function setFileReferenceID($value)
	{
		$this->FileReferenceID = $value;
	}

}
?>
