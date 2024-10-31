<?php
/**************************************************************************
* @CLASS PCMW_FormDefinitionsCore
* @brief Create, update and get definitions for forms.
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_Utility.php
*  -PCMW_FormDefinition.php
*  -PCMW_FormGroup.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_FormDefinition.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_FormGroup.php');
class PCMW_FormDefinitionsCore extends PCMW_BaseClass{


   public static function Get(){
		//==== instantiate or retrieve singleton from Josh and medad code====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_FormDefinitionsCore();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }

  /**
  * given a form ID, get the definitions
  * @param $intFormId
  * @return array of objects
  */
  function GetDefinitionsById($intDefinitionId,$boolAsArray=FALSE){
    $arrDefinitions = array();
    if($arrDefinitionData = PCMW_Database::Get()->GetFormDefinitions($intDefinitionId)){
      if($boolAsArray)
        return $arrDefinitionData;
       foreach($arrDefinitionData as $arrDefinition){
         $arrDefinitions[$arrDefinition['definitionid']] = new PCMW_FormDefinition();
         $arrDefinitions[$arrDefinition['definitionid']]->LoadObjectWithArray($arrDefinition);
       }
    }
    //$strDefintiions = var_export($arrDefinitions,TRUE);
    //PCMW_Logger::Debug('$strDefintiions ['.$strDefintiions.'] METHOD ['.__METHOD__.'] LINE '.__LINE__,1);
    return $arrDefinitions;
  }

  /**
  * given an alias and admin group, get the definitions
  * @param $strFormAlias
  * @param $intAdminGroup
  * @return array of objects
  */
  function GetDefinitionsByAlias($strFormAlias,$intAdminGroup=0,$boolAsArray=FALSE){
    $arrDefinitions = array();
    if($arrDefinitionData = PCMW_Database::Get()->GetFormDefinitionsByAlias($strFormAlias,$intAdminGroup)){
      if($boolAsArray)
        return $arrDefinitionData;
       foreach($arrDefinitionData as $arrDefinition){
         $arrDefinitions[$arrDefinition['definitionid']] = new PCMW_FormDefinition();
         $arrDefinitions[$arrDefinition['definitionid']]->LoadObjectWithArray($arrDefinition);
       }
    }
    return $arrDefinitions;
  }

  /**
  * given a Definition object, validate and insert, or update it
  * @param $objDefinition Assembled object
  * @param $arrPOST  post data to validate against
  * @param &$objFormManager instance of form manager for error compiling
  * @param $strFormAlias form alias to extract definition requires from
  * @param $arrIgnoreFields Fields to ignore validation for
  * @return bool || int (id)
  */
  function CleanAndInsertDefinition($objDefinition,$arrPOST,&$objFormManager,$strFormAlias,$arrIgnoreFields=array()){
     $objFormManager->arrIgnoreFields =PCMW_Utility::Get()->MergeArrays($objFormManager->arrIgnoreFields,$arrIgnoreFields);
    $strAction = 'insert';
    if($objDefinition->intDefinitionId > 0)
        $strAction = 'update';
        $strDefinitionObject = var_export($objDefinition,TRUE);
        //PCMW_Logger::Debug('$strDefinitionObject ['.$strDefinitionObject.'] $strAction['.$strAction.']',1);
        //return TRUE;
    if(!$objFormManager->ValidateDefinitionRequires(0,$arrPOST,$objDefinition,$strFormAlias,TRUE,$strAction)){
     //load the errors for all to see
     $strErrors = var_export($objDefinition->arrValidationErrors,TRUE);
     PCMW_Logger::Debug('['.__CLASS__.'] validation Errors ['.$strErrors.'] LINE ['.__LINE__.'] METHOD ['.__METHOD__.']',1);
     return FALSE;
    }
    //we're done, execute
    return $this->InsertOrUpdateDefinition($objDefinition);
  }

  /**
  * given a validated Definition object, insert/update it and give back the ID
  * @param $objDefinition
  * @return int definition id
  */
  function InsertOrUpdateDefinition($objDefinition){
    if($objDefinition->intDefinitionId > 0)
      return PCMW_Database::Get()->UpdateDefinition($objDefinition);
    else
      return PCMW_Database::Get()->InsertNewDefinition($objDefinition);
  }


  /**
  * given a validated Definition Group object, insert/update it and give back the ID
  * @param $objDefinitionGroup
  * @return int definition group id
  */
  function InsertOrUpdateDefinitionGroup($objDefinitionGroup){
    if($objDefinitionGroup->intFormId > 0)
      return PCMW_Database::Get()->UpdateFormData($objDefinitionGroup);
    else
      return PCMW_Database::Get()->InsertNewFormData($objDefinitionGroup);
  }


  /**
  * given a DefinitionGroup object, validate and insert, or update it
  * @param $objDefinitionGroup Assembled object
  * @param $arrPOST  post data to validate against
  * @param &$objFormManager instance of form manager for error compiling
  * @param $strFormAlias form alias to extract DefinitionGroup requires from
  * @param $arrIgnoreFields Fields to ignore validation for
  * @return bool || int (id)
  */
  function CleanAndInsertDefinitionGroup($objDefinitionGroup,$arrPOST,&$objFormManager,$strFormAlias,$arrIgnoreFields=array()){
     $objFormManager->arrIgnoreFields =PCMW_Utility::Get()->MergeArrays($objFormManager->arrIgnoreFields,$arrIgnoreFields);

    $strAction = 'insert';
    if($objDefinitionGroup->intFormId > 0)
        $strAction = 'update';
    if(!$objFormManager->ValidateDefinitionRequires(0,$arrPOST,$objDefinitionGroup,$strFormAlias,TRUE,$strAction)){
     //load the errors for all to see
     $strErrors = var_export($objDefinitionGroup->arrValidationErrors,TRUE);
     PCMW_Logger::Debug('['.__CLASS__.'] validation Errors ['.$strErrors.'] LINE ['.__LINE__.'] METHOD ['.__METHOD__.']',1);
     return FALSE;
    }
    //all done
    return $this->InsertOrUpdateDefinitionGroup($objDefinitionGroup);
  }

}//end class
?>