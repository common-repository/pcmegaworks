<?php
/**************************************************************************
* @CLASS PCMW_AdminUserCore
* @brief USE THIS TO CREATE NEW CLASSES FOR THE INCLUDES DIRECTORY.
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_AdminUser.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_AdminUser.php');
class PCMW_AdminUserCore extends PCMW_BaseClass{

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_AdminUserCore();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }


  /**
  * Get admin group by ID
  * @param $intAdminGroupId
  * @return object (adminuser)
  */
  function GetAdminGroupById($intAdminGroupId=0,$intUserId=0){
    if($arrAdminGroup = PCMW_Database::Get()->GetAdminGroups($intUserId,$intAdminGroupId)){
      return PCMW_AdminUser::Get()->LoadObjectWithArray($arrAdminGroup[0]);
    }
    return FALSE;
  }

  /**
  * Get admin group by ID
  * @param $intAdminGroupId
  * @return object (adminuser)
  */
  public static function GetAdminUserId($intAdminUserId=0,$intUserId=0,$intHandlerId=0,$intCustomerId=0,$boolAsObject=FALSE){
    if(($arrAdminUser = PCMW_Database::Get()->GetAdminUser($intAdminUserId,$intUserId,$intHandlerId,$intCustomerId))){
      if(!$boolAsObject){
        return $arrAdminUser[0];
      }
      return PCMW_AdminUser::Get()->LoadObjectWithArray($arrAdminUser[0]);
    }
    return FALSE;
  }

  /**
  * given an admin user object, validate and insert, or update it
  * @param $objAdminUser
  * @param $arrPOST  post data to validate against
  * @param &$objFormManager instance of form manager for error compiling
  * @param $strFormAlias form alias to extract AdminGroup requires from
  * @param $arrIgnoreFields Fields to ignore validation for
  * @return bool || int (id)
  */
  function CleanAndInsertAdminUser($objAdminUser,$arrPOST,&$objFormManager,$strFormAlias,$arrIgnoreFields=array()){
    $strAction = 'insert';
    if($objAdminUser->intAdminUserId > 0)
        $strAction = 'update';
    if(!$objFormManager->ValidateDefinitionRequires(0,$arrPOST,$objAdminUser,$strFormAlias,TRUE,$strAction)){
     //load the errors for all to see
     $strErrors = var_export($objAdminUser->arrValidationErrors,TRUE);
     $strAdminObject = var_export($objAdminUser,TRUE);
     PCMW_Logger::Debug('['.__CLASS__.'] validation Errors ['.$strErrors.'] $strAdminObject ['.$strAdminObject.'] LINE ['.__LINE__.'] METHOD ['.__METHOD__.']',1);
     return FALSE;
    }
    return $this->InsertAdminUser($objAdminUser);
  }

  /**
  * given an admin user object, insert it.
  * it is assumed the data is cleaned at this point
  * @param $objAdminUser
  * @return int (ID) || bool
  */
  function InsertAdminUser($objAdminUser){                              
    if($objAdminUser->intAdminUserId > 0)
        return PCMW_Database::Get()->UpdateAdminUser($objAdminUser);
    else
        return PCMW_Database::Get()->InsertAdminGroupUser($objAdminUser);
  }

  /**
  * given a client object, validate and insert, or update it
  * @param $objAdminGroup
  * @param $arrPOST  post data to validate against
  * @param &$objFormManager instance of form manager for error compiling
  * @param $strFormAlias form alias to extract AdminGroup requires from
  * @param $arrIgnoreFields Fields to ignore validation for
  * @return bool || int (id)
  */
  function CleanAndInsertAdminGroup($objAdminGroup,$arrPOST,&$objFormManager,$strFormAlias,$arrIgnoreFields=array()){
    $strAction = 'insert';
    if($objAdminGroup->intAdminGroupId > 0)
        $strAction = 'update';
    if(!$objFormManager->ValidateDefinitionRequires(0,$arrPOST,$objAdminGroup,$strFormAlias,TRUE,$strAction)){
     //load the errors for all to see
     $strErrors = var_export($objAdminGroup->arrValidationErrors,TRUE);
     PCMW_Logger::Debug('['.__CLASS__.'] validation Errors ['.$strErrors.'] LINE ['.__LINE__.'] METHOD ['.__METHOD__.']',1);
     return FALSE;
    }
    return $this->InsertAdminGroup($objAdminGroup);
  }

  /**
  * given an admin group object, insert it.
  * it is assumed the data is cleaned at this point
  * @param $objAdminGroup
  * @return int (ID) || bool
  */
  function InsertAdminGroup($objAdminGroup,$boolNewRecord=FALSE){
    if($objAdminGroup->intAdminGroupId > 0 && !$boolNewRecord)
        return PCMW_Database::Get()->UpdateAdminGroup($objAdminGroup);
    else
        return PCMW_Database::Get()->InsertAdminGroup($objAdminGroup);
  }

  /**
  * given an array of WP users, get the associated admin group data for each
  * @param $arrWPUsers
  * @return array
  */
  function GetWPUserAdminGroups($arrWPUsers){
   $arrAdminUsers = array();
   foreach($arrWPUsers as $objWPUser){
    $arrAdminUsers[$objWPUser->ID]['WPUSEROBJECT'] = $objWPUser;
    $arrAdminUsers[$objWPUser->ID]['pcgroup'] = $this->GetAdminUserId(0,$objWPUser->ID);
    if((int)$arrAdminUsers[$objWPUser->ID]['pcgroup']['adminid'] < 1){
       $arrPOST = $arrAdminUsers[$objWPUser->ID]['pcgroup'];
       //pre install account, create admin group for them
       $objAdminUser = new PCMW_AdminUser();
       $objAdminUser->intUserId = $objWPUser->ID;
       $objAdminUser->intHandlerId = $objWPUser->ID;
       $objAdminUser->intCustomerId = $objWPUser->ID;
       $objAdminUser->intAdminGroupId = PCMW_BASICUSER;
       $objAdminUser->intStatus = 10;
       $objFormManager = new PCMW_FormManager();
       PCMW_AdminUserCore::Get()->CleanAndInsertAdminUser($objAdminUser,$arrPOST,$objFormManager,'');
    }
   }                                                          
   return $arrAdminUsers;
  }

 
}//end class
?>