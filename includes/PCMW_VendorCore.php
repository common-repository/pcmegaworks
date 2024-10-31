<?php
/**************************************************************************
* @CLASS PCMW_VendorCore
* @brief USE THIS TO CREATE NEW CLASSES FOR THE INCLUDES DIRECTORY.
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_Vendor.php
*  -PCMW_MapGroup.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
  die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_Vendor.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_MapGroup.php');
class PCMW_VendorCore extends PCMW_BaseClass{

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_VendorCore();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }



  /**
  * given a Vendor object, validate and insert, or update it
  * @param $objVendor
  * @param $arrPOST  post data to validate against
  * @param &$objFormManager instance of form manager for error compiling
  * @param $strFormAlias form alias to extract definition requires from
  * @param $arrIgnoreFields Fields to ignore validation for
  * @return bool || int (id)
  */
  function CleanAndInsertVendor($objVendor,$arrPOST,&$objFormManager,$strFormAlias,$arrIgnoreFields=array()){
     $objFormManager->arrIgnoreFields =PCMW_Utility::Get()->MergeArrays($objFormManager->arrIgnoreFields,$arrIgnoreFields);
    $strAction = 'insert';
    if($objVendor->intVendorId > 0)
        $strAction = 'update';
    if(!$objFormManager->ValidateDefinitionRequires(0,$arrPOST,$objVendor,$strFormAlias,TRUE,$strAction)){
     //load the errors for all to see
     $strErrors = var_export($objVendor->arrValidationErrors,TRUE);
     PCMW_Logger::Debug('['.__CLASS__.'] validation Errors ['.$strErrors.'] LINE ['.__LINE__.'] METHOD ['.__METHOD__.']',1);
     return FALSE;
    }
    //we're done, execute
    return $this->InsertOrUpdateVendor($objVendor);
  }

  /**
  * given data about a Vendor, save or update it
  * @param $objVendor
  * @return bool || new group id
  */
  function InsertOrUpdateVendor($objVendor){
    if(!is_numeric($objVendor->intVendorId))
        $objVendor->intVendorId = 0;
    if(($objVendor->intVendorId) > 0)
    //is an update
        return PCMW_Database::Get()->UpdateVendor($objVendor);
    else
    //is insert
        return PCMW_Database::Get()->InsertVendor($objVendor);
  }

  /**
  * given a MapGroup object, validate and insert, or update it
  * @param $objMapGroup
  * @param $arrPOST  post data to validate against
  * @param &$objFormManager instance of form manager for error compiling
  * @param $strFormAlias form alias to extract definition requires from
  * @param $arrIgnoreFields Fields to ignore validation for
  * @return bool || int (id)
  */
  function CleanAndInsertMapGroup($objMapGroup,$arrPOST,&$objFormManager,$strFormAlias, $arrIgnoreFields=array()){
     $objFormManager->arrIgnoreFields =PCMW_Utility::Get()->MergeArrays($objFormManager->arrIgnoreFields,$arrIgnoreFields);
    $strAction = 'insert';
    if($objMapGroup->intMapGroupId > 0)
        $strAction = 'update';
    if(!$objFormManager->ValidateDefinitionRequires(0,$arrPOST,$objMapGroup,$strFormAlias,TRUE,$strAction)){
     //load the errors for all to see
     $strErrors = var_export($objMapGroup->arrValidationErrors,TRUE);
     PCMW_Logger::Debug('['.__CLASS__.'] validation Errors ['.$strErrors.'] LINE ['.__LINE__.'] METHOD ['.__METHOD__.']',1);
     return FALSE;
    }
    //we're done, execute
    return $this->InsertOrUpdateMapGroup($objMapGroup);
  }

  /**
  * given data about a map group, save or update it
  * @param $objMapGroup
  * @return bool || new group id
  */
  function InsertOrUpdateMapGroup($objMapGroup){
    if(!is_numeric($objMapGroup->intMapGroupId))
        $objMapGroup->intMapGroupId = 0;
    if(($objMapGroup->intMapGroupId) > 0)
    //is an update
        return PCMW_Database::Get()->UpdateMapGroup($objMapGroup);
    else
    //is insert
        return PCMW_Database::Get()->InsertMapGroup($objMapGroup);
  }

  /**
  * get all vendors
  * @param $boolNameList
  * @return array()
  */
  public static function GetAllVendors($boolNameList = TRUE,$boolActiveOnly=FALSE){
    $arrVendors = PCMW_Database::Get()->GetAllVendors($boolActiveOnly);
    if($arrVendors){
      if($boolNameList){
        return PCMW_Utility::Get()->MakeNameValueArray('vendorid','vendorname',$arrVendors);
      }
      return $arrVendors;
    }
  }

  /**
  * get all MapGroups
  * @param $boolNameList
  * @return array()
  */
  public static function GetAllMapGroups($boolNameList = TRUE,$boolActiveOnly=FALSE){
    $arrMapGroups = PCMW_Database::Get()->GetAllMapGroups($boolActiveOnly);
    if($arrMapGroups){
      if($boolNameList){
        return PCMW_Utility::Get()->MakeNameValueArray('groupid','groupname',$arrMapGroups);
      }
      return $arrMapGroups;
    }
  }

  /**
  * Get a single Vendor
  * @param $intVendorId
  * @return array
  */
  function GetSingleVendor($intVendorId){
    $arrVendor = PCMW_Database::Get()->GetVendorById($intVendorId);
    if($arrVendor)
        return $arrVendor[0];
    return $arrVendor;
  }

  /**
  * Get a single MapGroup
  * @param $intMapGroupId
  * @return array
  */
  function GetSingleMapGroup($intMapGroupId){
    $arrMapGroup = PCMW_Database::Get()->GetMapGroupById($intMapGroupId);
    if($arrMapGroup){
      $arrGroupOptions = PCMW_Utility::Get()->JSONDecode($arrMapGroup[0]['groupsettings']);
      unset($arrMapGroup[0]['groupsettings']);
        return PCMW_Utility::Get()->MergeArrays($arrMapGroup[0],$arrGroupOptions,FALSE);
    }
    return $arrMapGroup;
  }

  /**
  * get all the live vendors in a list
  * @return string (HTML)
  */
  function GetActiveVendorsList($arrParams){
    $strVendorList = '';
	$arrUsedVendors = array();
    foreach($this->GetAllVendors(TRUE,TRUE) as $intID => $strName){
     if(in_array($strName,$arrUsedVendors))
	 	continue 1;
     $arrUsedVendors[] = $strName;
     $strVendorList .= '<a href="'.$arrParams['locatorpage'].'#'.str_replace(' ','',$strName).$intID.'" style="color:'.$arrParams['linkcolor'].';">'.$strName.'</a>';
     $strVendorList .= '<span style="color:'.$arrParams['separatorcolor'].';"> &bull; </span>';
    }
    return $strVendorList;
  }

  /**
  * get all the live MapGroups in a list
  * @return string (HTML)
  */
  function GetActiveMapGroupsList($arrParams){
    $strMapGroupList = '';
    foreach($this->GetAllMapGroups(TRUE,TRUE) as $intID => $strName){
     $strMapGroupList .= '<a href="'.$arrParams['locatorpage'].'#'.str_replace(' ','',$strName).$intID.'" style="color:'.$arrParams['linkcolor'].';">'.$strName.'</a>';
     $strMapGroupList .= '<span style="color:'.$arrParams['separatorcolor'].';"> &bull; </span>';
    }
    return $strMapGroupList;
  }

  /**
  * given a group ID, get all of the vendors in the group
  * @param $intGroupId
  * @param $boolAsList
  * @return array
  */
  function GetMapGroupVendors($intGroupId,$boolAsList=FALSE){
    $arrMapGroupVendors = PCMW_Database::Get()->GetVendorsByGroup($intGroupId);
      if($boolAsList)
        return PCMW_Utility::Get()->MakeNameValueArray('lvendorid','lgroupid',$arrMapGroupVendors);
    return $arrMapGroupVendors;
  }

  /**
  * make vendors table
  * @return string HTML
  *     -['tabledescription'] = ['tabledescription']
  *     -['tableheader']
  *         -['headerkey'] = ['headername']
  *     -['tabledata'][unique key]
  *         -['headerkey'] = ['columnvalue']
  *         -['linkvalue'] = ['linkvalue'] || ['onclickvalue'] = ['onclickvalue']
  */
  function MakeVendorsSelectionTable($arrPOST){
    $arrTableData = $arrPOST;
    $arrTableData['tabledescription'] = 'Manage Group Maps&nbsp;&nbsp;<button onclick="UpdateCheckBoxes(this)" class="btn">Check all</button>';
    //define our columns
    $arrTableData['tableheader'] = array('addvendor'=>'Add',
                                         'vendorid'=>'ID',
                                         'vendorname'=>'Name',
                                         'vendoraddress'=>'Address',
                                         'vendordescription'=>'Description',
                                         'vendorstatus'=>'Status');
    //get the status list
    $arrStatus = PCMW_StaticArrays::Get()->LoadStaticArrayType('activestatus',FALSE);
    //make the vendor table data
    $arrTableData['tabledata'] = array();
    $arrVendors =  $this->GetAllVendors(FALSE,FALSE);
    $arrVendorLink = $this->GetMapGroupVendors($arrPOST['groupid'],TRUE);
    foreach($arrVendors as $arrVendor){
      $arrTableData['tabledata'][$arrVendor['vendorid']] = array();
      foreach($arrTableData['tableheader'] as $strKey=>$strValue){
        if($arrVendor['vendorstatus'] < 1)
            continue 1;
        $arrTableData['tabledata'][$arrVendor['vendorid']][$strKey] = array();
        if($strKey == 'vendorid'){
            $arrTableData['tabledata'][$arrVendor['vendorid']][$strKey]['value'] = $arrVendor[$strKey];
            $arrTableData['tabledata'][$arrVendor['vendorid']]['rowid'] = 'v_'.$arrVendor['vendorid'];
        }
        else if($strKey == 'vendorstatus')
            $arrTableData['tabledata'][$arrVendor['vendorid']][$strKey]['value'] = $arrStatus[$arrVendor[$strKey]];
        else if($strKey == 'addvendor'){
            $boolVendorInGroup = (array_key_exists($arrVendor['vendorid'],$arrVendorLink))? TRUE: FALSE;
            $arrTableData['tabledata'][$arrVendor['vendorid']][$strKey]['checkbox'] = (bool)$boolVendorInGroup;
            $arrTableData['tabledata'][$arrVendor['vendorid']][$strKey]['value'] = (bool)$boolVendorInGroup;
            $arrTableData['tabledata'][$arrVendor['vendorid']][$strKey]['ajaxlinkcall'] = 'AddAnonymousAction(\'addremovemap\','.$arrVendor['vendorid'].',this.checked,\'groupid='.$arrPOST['groupid'].'\');';
        }
        else
            $arrTableData['tabledata'][$arrVendor['vendorid']][$strKey]['value'] = $arrVendor[$strKey];
      }
    }
    return PCMW_FormManager::Get()->MakeBootStrapTable($arrTableData);
  }

}//end class
?>