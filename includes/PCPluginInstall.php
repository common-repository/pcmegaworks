<?php
/**************************************************************************
* @CLASS PCPluginInstall
* @brief USE THIS TO CREATE NEW CLASSES FOR THE INCLUDES DIRECTORY.
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_HostRequest.php
*  -PCMW_Config.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_HostRequest.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_Config.php');
class PCPluginInstall{

   public static function Get(){
    //==== instantiate or retrieve singleton ====
    static $inst = NULL;
    if( $inst == NULL )
      $inst = new PCPluginInstall();
    return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }

  #REGION INSTALL
  /**
  * check to see if PC plugin has been installed
  * @return bool
  */
  function CheckForInstall(){
    if(!PCMW_ConfigCore::Get()->LoadConfigFromStorage()){
      //verify we can get the DB and install if none exist
      if(!PCMW_ConfigCore::Get()->CreateConfigValues()){
        PCMW_Abstraction::Get()->AddUserMSG( 'Installation failed, or PC Megaworks has become corrupted. Attempting re-install. Please re-install it to continue usage if this message appears again. ['.__LINE__.']',1);
        return FALSE;
      }
      return TRUE;
    }
    return TRUE;
  }

  /**
  * @brief: check to see if a feature is presently installed
  * @param $strFeatureName
  * @return bool
  */
  function IsFeatureInstalled($strFeatureName){
    if($strFeatures = get_option('PCMW_features')){
      $arrFeatures = json_decode($strFeatures,TRUE);
      if(array_key_exists($strFeatureName,$arrFeatures) && (int)$arrFeatures[$strFeatureName] > 0)
         return TRUE;
    }
    return FALSE;
  }

  /**
  * install core functionality of the plugin, so that
  * features can be added as needed
  * @return bool
  */
  function InstallCoreFeatures(){
  if(session_id() == '' || !isset($_SESSION))
        // We need session here
    session_start();
    $strInstallation = 'Begin installation....<br />';
    //in case this was left set
    unset($_SESSION['pcconfig']);
    if(PCMW_ConfigCore::Get()->CreateConfigValues()){
      $strInstallation .= 'Configuration data created...<br />';
      //make our fresh install
      if($this->MakeUpdateCall(TRUE)){
        $strInstallation .= 'Tables created...<br />';
        if($this->MakeAdminGroup()){
            $strInstallation .= 'Admin groups created...<br />';
            if($this->MakeAdminUser()){
              $strInstallation .= 'Admin users created...<br />';
              PCMW_Abstraction::Get()->AddUserMSG( 'Installation complete!...<br />'.$strInstallation.' ['.__LINE__.']',3);
              return TRUE;
            }
            else{
              PCMW_Abstraction::Get()->AddUserMSG( 'Cannot create admin users...<br />'.$strInstallation.' ['.__LINE__.']',1);
              $strInstallation .= 'Cannot create admin users ['.__LINE__.']'."\r\n";
              PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
              $this->GetTableDrops();
              //send PC failure description.
              $arrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
              $arrFailureData['failuredate'] = $strInstallation;
              $this->SendFailedInstallData($arrFailureData);
              return FALSE;
            }
        }
        else{
          PCMW_Abstraction::Get()->AddUserMSG( 'Cannot create admin groups...<br />'.$strInstallation.' ['.__LINE__.']',1);
          $strInstallation .= 'Cannot create admin groups ['.__LINE__.']'."\r\n";
          PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
          $this->GetTableDrops();
          //send PC failure description.
          $arrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
          $arrFailureData['failuredate'] = $strInstallation;
          $this->SendFailedInstallData($arrFailureData);
          return FALSE;
        }
      }
      PCMW_Abstraction::Get()->AddUserMSG( 'Cannot create tables...<br />'.$strInstallation.' ['.__LINE__.']',1);
      $strInstallation .= 'Cannot create tables['.__LINE__.']'."\r\n";
      PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      $this->GetTableDrops();
      //send PC failure description.
      $arrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
      $arrFailureData['failuredate'] = $strInstallation;
      $this->SendFailedInstallData($arrFailureData);
      return FALSE;
    }
    else{
        PCMW_Abstraction::Get()->AddUserMSG( 'Cannot create config values...<br />'.$strInstallation.' ['.__LINE__.']',3);
        $strInstallation .= 'Cannot create config values ['.__LINE__.']'."\r\n";
        PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        //send PC failure description.
        $arrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
        $arrFailureData['failuredate'] = $strInstallation;
        $this->SendFailedInstallData($arrFailureData);
        return FALSE;
    }
    //in case somehow this happens
    $this->SendFailedInstallData($arrFailureData);
    return FALSE;
  }

  /**
  * use a local member function to execute our config call
  * @param $arrFailureData
  * @return bool
  */
  function SendFailedInstallData($arrFailureData){
   return PCMW_ConfigCore::Get()->SendFailedInstallData($arrFailureData);
  }

  /**
  * install the map support
  * @return bool
  */
  function InstallPCMaps(){
   if($this->InstallFeature('maps')){
    PCMW_Logger::Debug('Maps installed. METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    return TRUE;
   }
   else{
    $arrInstallation = error_get_last();
    $strInstallation = var_export($arrInstallation,TRUE);
    PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    return FALSE;
   }  
  }

  /**
  * uninstall PC Maps
  * @return bool
  */
  function UnInstallPCMaps(){
    $this->RemoveTableUpdateIds('maps');
    if(PCMW_Database::Get()->DropTable('vendors')){
        if(PCMW_Database::Get()->DropTable('mapgroups')){
          if(PCMW_Database::Get()->DropTable('mapgrouplink')){
            //delete the form definitions. These are static IDs
            if(!$this->RemoveFormGroupData(6))
               return FALSE;
            if(!$this->RemoveFormGroupData(13))
               return FALSE;
            return TRUE;
          }
        }
    }
    return FALSE;
  }

  /**
  * install 404 redirect support
  * @return bool
  */
  function Install404Redirect(){
   if($this->InstallFeature('404redirects')){
    PCMW_Logger::Debug('404 redirects installed. METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    return TRUE;
   }
   else{
    $arrInstallation = error_get_last();
    $strInstallation = var_export($arrInstallation,TRUE);
    PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    return FALSE;
   }
  }

  /**
  * uninstall 404 redirect
  * @return bool
  */
  function UnInstall404Redirect(){
    $this->RemoveTableUpdateIds('404redirects');
    if(PCMW_Database::Get()->DropTable('404redirects')){
      //delete the form definitions. These are static IDs
      if(!$this->RemoveFormGroupData(12))
         return FALSE;
      return TRUE;
    }
    return FALSE;
  }

  /**
  * install video access
  * @return bool
  */
  function InstallVideoAccess(){
   if($this->InstallFeature('video-access')){
    PCMW_Logger::Debug('Video Access installed. METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    return TRUE;
   }
   else{
    $arrInstallation = error_get_last();
    $strInstallation = var_export($arrInstallation,TRUE);
    PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    return FALSE;
   }
  }

  /**
  * uninstall video access
  * @return bool
  */
  function UnInstallVideoAccess(){
    $this->RemoveTableUpdateIds('video-access');
    if(PCMW_Database::Get()->DropTable('videoaccess')){
      //delete the form definitions. These are static IDs
      if(!$this->RemoveFormGroupData(18))
         return FALSE;
      return TRUE;
    }
    return FALSE;
  }

  /**
  * install mail blast capability
  * @return bool
  */
  function InstallMailBlast(){
   if($this->InstallFeature('mailblast')){
    PCMW_Logger::Debug('Mail blast installed. METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    return TRUE;
   }
   else{
    $arrInstallation = error_get_last();
    $strInstallation = var_export($arrInstallation,TRUE);
    PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    return FALSE;
   }
  }

  /**
  * uninstall mail blast
  * @return bool
  */
  function UnInstallMailBlast(){
    $this->RemoveTableUpdateIds('mailblast');
    if(PCMW_Database::Get()->DropTable('mailblast')){
      //delete the form definitions. These are static IDs
      if(!$this->RemoveFormGroupData(12))
         return FALSE;
      return TRUE;
    }
    return FALSE;
  }

  /**
  * install basic chat capability
  * @return bool
  */
  function InstallBasicChat(){
   if($this->InstallFeature('basicchat')){
    PCMW_Logger::Debug('Basic chat installed. METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    return TRUE;
   }
   else{
    $arrInstallation = error_get_last();
    $strInstallation = var_export($arrInstallation,TRUE);
    PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    return FALSE;
   }
  }

  /**
  * uninstall basic chat
  * @return bool
  */
  function UnInstallBasicChat(){
    $this->RemoveTableUpdateIds('basicchat');
    PCMW_Database::Get()->DropTable('chatmessage');
    if(PCMW_Database::Get()->DropTable('chatsession')){
      //delete the form definitions. These are static IDs
      if(!$this->RemoveFormGroupData(19))
         return FALSE;
      return TRUE;
    }
    return FALSE;
  }

  /**
  * given a feature name, get the update ID's and remove the table creation specific updates from the list
  * @param $strFeatureName
  * @return bool
  */
  function RemoveTableUpdateIds($strFeatureName){
    $arrFeatures = $this->GetFeatureData($strFeatureName);
    $strPreviousUpdates = get_option('PCMW_updates');
    $arrPreviousUpdates = json_decode($strPreviousUpdates,TRUE);
    if($arrFeatures && array_key_exists('sqltables',$arrFeatures) && is_array($arrFeatures['sqltables'])){
     foreach($arrFeatures['sqltables'] as $intUpdateId=>$arrTable){
        if(array_key_exists('tabledelta',$arrTable) && trim($arrTable['tabledelta']) != ''){
          unset($arrPreviousUpdates[$intUpdateId]);
          PCMW_Logger::Debug('Update ['.$intUpdateId.']  removed. METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        }
        else if(array_key_exists('datadelta',$arrTable) && trim($arrTable['datadelta']) != ''){
          unset($arrPreviousUpdates[$intUpdateId]);
          PCMW_Logger::Debug('Update ['.$intUpdateId.']  removed. METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        }
        else  PCMW_Logger::Debug('Update ['.$intUpdateId.']  NOT removed. METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
     }
    }
    else
      PCMW_Logger::Debug('No SQL table data to unload ['.$strFeatureName.']  METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    update_option( 'PCMW_updates', json_encode($arrPreviousUpdates),NULL,'no' );
    return TRUE;
  }

  /**
  * check for updates for this version
  * @param $strLastTable - last table installed when updating group tables
  * @param $boolDebugRemoval - use debugging to capture removal errors
  * @return string ( JSON )
  */
  function GetTableDrops($strLastTable='',$boolDebugRemoval=TRUE){
    $this->CheckForInstall();
    //@TODO: make FTP and SQL updates
    $arrRequest = array('purpose'=>'gettabledrops',
                       'version'=>PCMW_ConfigCore::Get()->objConfig->GetPluginVersion(),
                       'userkey'=>PCMW_ConfigCore::Get()->objConfig->GetUserKey());
    if($arrPayLoad = PCMW_HostRequest::Get()->MakeHostRequest($arrRequest)){
      $strPayLoad = var_export($arrPayLoad,TRUE);
        //which tables do we want to remove?
    if($boolDebugRemoval)
      PCMW_Logger::Debug('Uninstallation failed, removing tables:'."\r\n".'['.$strPayLoad.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    if($arrPayLoad['payload']){
      //this is the table that failed install
      if(trim($strLastTable) != '')
          PCMW_Abstraction::Get()->AddUserMSG( 'Could NOT add table ['.$strLastTable.'] Please run the installation again, or contact the helpdesk. ['.__LINE__.']',1);
      $strFailMessages = '';
      $strSuccessMessages = '';
      //remove these tables now
      foreach($arrPayLoad['payload'] as $strTableName){
        if(PCMW_Database::Get()->CheckForTable($strTableName)){
          if(PCMW_Database::Get()->DropTable($strTableName))
            $strSuccessMessages .= 'Removed Table ['.$strTableName.']'."\r\n";
          else
            $strFailMessages .='Could NOT remove table ['.$strTableName.']'."\r\n";
        }
      }
      //do we have anything to add?
      if(trim($strSuccessMessages) != ''){//$strSuccessMessages
        PCMW_Abstraction::Get()->AddUserMSG( $strSuccessMessages.' ['.__LINE__.']',3);
      }
      if(trim($strFailMessages) != ''){//$strFailMessages
        PCMW_Abstraction::Get()->AddUserMSG( $strFailMessages.' ['.__LINE__.']',1);
        if($boolDebugRemoval)
            PCMW_Logger::Debug($strFailMessages.' ['.__LINE__.']',1);
      }
      return TRUE;
    }
   }
   return FALSE;
  }

  /**
  * @brief: remove the version on deletion
  * @return bool
  */
  function ResetUserVersion(){
    $arrRequest = array('purpose'=>'resetversion',
                       'version'=>'001.000.000',
                       'userkey'=>PCMW_ConfigCore::Get()->objConfig->GetUserKey());
    if($arrPayLoad = PCMW_HostRequest::Get()->MakeHostRequest($arrRequest)){
      if(is_array($arrPayLoad['payload']) && sizeof($arrPayLoad['payload']) > 0)
        return $arrPayLoad['payload'];
    }
    return FALSE;
  }

  /**
  * make the primary admin user
  * @return bool
  */
  function MakeAdminUser(){
   if(PCMW_AdminUserCore::GetAdminUserId(0,get_current_user_id()))
    return TRUE;
   $objAdminUser = new PCMW_AdminUser();
   $objAdminUser->intUserId = get_current_user_id();
   $objAdminUser->intHandlerId = get_current_user_id();
   $objAdminUser->intCustomerId = get_current_user_id();
   $objAdminUser->intAdminGroupId = PCMW_SUPERUSERS;
   $objAdminUser->intStatus = 20;
   $arrPOST = $objAdminUser->LoadArrayWithObject();
   $objFormManager = new PCMW_FormManager();
   return PCMW_AdminUserCore::Get()->CleanAndInsertAdminUser($objAdminUser,$arrPOST,$objFormManager,'');
  }


  /**
  * uninstall the application
  */
  function UninstallPCPlugin($dirPath=''){
    //remove database tables
    if($this->GetTableDrops('',FALSE)){
      $this->ResetUserVersion();
      $this->RemoveSessionData();
      return $this->RemovePCConfigData();
    }
    return FALSE;
  }
  /**
  * @brief: once the files and tables have been removed,
  * -remove session and config data
  * @return bool
  */
  function RemovePCConfigData(){
    //remove PCConfig option
    delete_option('PCPlugin');
    //remove active or inactive state
    delete_option('PCPlugin_Activation');
    delete_option('PCPlugin_Deactivation');
    delete_option('PCMW_updates');
    delete_option('PCMW_features');
    delete_option('PCMW_regcompletetext');
    delete_option('PCMW_regemailtext');
    delete_option('PCMW_ChatOptions');
    //all done, exit;
    return TRUE;
  }

  /**
  * @brief: remove all session data after deactivation and uninstall
  * @return bool
  */
  function RemoveSessionData(){
    //remove user
    unset($_SESSION['CURRENTUSER']);
    //remove plugin data
    unset($_SESSION['pcconfig']);
    unset($_SESSION['pcsessionconfig']);
    unset($_SESSION['PC_Redirects']);
    unset($_SESSION['pc_chatid']);
    unset($_SESSION['pc_chatsession']);
    unset($_SESSION['anonusername']);
    unset($_SESSION['pc_chatid']);
    unset($_SESSION['errout']);
    unset($_SESSION['msgs']);
  }

  /**
  * @brief: Given a form group ID, remove the group and defnitions
  * @param $intFormGroupId
  * @return bool
  */
  function RemoveFormGroupData($intFormGroupId){
    //remove the group
    if(!PCMW_Database::Get()->RunRawQuery('DELETE FROM PC_formgroups WHERE formid = '.$intFormGroupId))
      return FALSE;
    //remove the definitions
    if(!PCMW_Database::Get()->RunRawQuery('DELETE FROM PC_formdefinitions WHERE formgroup = '.$intFormGroupId))
      return FALSE;
    return TRUE;
  }


  /**
  * Make the primary admin group
  * @return bool
  */
  function MakeAdminGroup(){
   if($arrAccessLevels = PCMW_StaticArrays::Get()->LoadStaticArrayType('accesslevels',FALSE,0,FALSE,0,'',FALSE)){
     foreach($arrAccessLevels as $intLevel=>$arrLevelData){
       $objAdminGroup = new PCMW_AdminGroup();
       $objAdminGroup->intAdminGroupId = $intLevel;
       $objAdminGroup->strGroupName = $arrLevelData[0];
       $objAdminGroup->intGroupStatus = PCMW_USERSUSPENDED;
       if($intLevel > PCMW_SUSPENDED)
        $objAdminGroup->intGroupStatus = PCMW_USERREAD;
       if($intLevel > PCMW_PREMIUMUSER)
        $objAdminGroup->intGroupStatus = PCMW_USERREADWRITE;
       if($intLevel > PCMW_MODERATOR)
        $objAdminGroup->intGroupStatus = PCMW_USERADMIN;
       $objAdminGroup->intClientId = 1;
       PCMW_AdminUserCore::Get()->InsertAdminGroup($objAdminGroup,TRUE);
     }
     return TRUE;
   }
  }


  /**
  * create the admin users table
  * @param $boolFreshInstall flag to get fresh install updates
  * @return bool
  */
  function CheckPluginVersionUpdate($boolFreshInstall=TRUE){
    //load our previous version for comparison
    // fresh install requires all updates for all time, for now
    $strVersion = ($boolFreshInstall)? '001.000.000':PCMW_ConfigCore::Get()->objConfig->GetPreviousVersion() ;
   $arrRequest = array('purpose'=>'versionupdated',
                       'version'=>$strVersion,
                       'userkey'=>PCMW_ConfigCore::Get()->objConfig->GetUserKey(),
                       'listedversion'=>PCMW_ConfigCore::Get()->objConfig->GetPluginVersion(),
                       'address'=>get_site_url().'/');
   if($arrPayLoad = PCMW_HostRequest::Get()->MakeHostRequest($arrRequest)){
    return $arrPayLoad['payload'];
   }
   return FALSE;
  }

  /**
  * given a feature group, get the SQL and install it
  * @param $strFeatureGroup
  * @return string results
  */
  function GetFeatureData($strFeatureGroup){
    $arrRequest = array('purpose'=>'getfeature',
                       'feature'=>$strFeatureGroup,
                       'userkey'=>PCMW_ConfigCore::Get()->objConfig->GetUserKey(),
                       'address'=>get_site_url().'/');
    if($arrPayLoad = PCMW_HostRequest::Get()->MakeHostRequest($arrRequest)){
      if(is_array($arrPayLoad['payload']) && sizeof($arrPayLoad['payload']) > 0)
        return $arrPayLoad['payload'];
    }
    return FALSE;
  }


  /**
  * On plugin update, check the server for updated database delta data, and update the version
  * @return bool
  */
  function UpdatePluginVersion($boolFreshInstall=TRUE){
    $arrPluginDetails = get_plugin_data( dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'PCMegaworks.php');
    //update our local version first   UpdatePCConfig()
    PCMW_ConfigCore::Get()->objConfig->SetPreviousVersion(PCMW_ConfigCore::Get()->objConfig->GetPluginVersion());
    PCMW_ConfigCore::Get()->objConfig->SetPluginVersion($arrPluginDetails['Version']);
    PCMW_ConfigCore::Get()->UpdatePCConfig();
    return $this->MakeUpdateCall($boolFreshInstall);
  }

  /**
  * make the version update
  * @param $boolFreshInstall
  * @return bool
  */
  function MakeUpdateCall($boolFreshInstall){
    //update our PC record and get updates for the difference
    $arrUpdateData = $this->CheckPluginVersionUpdate($boolFreshInstall);
    return $this->ExecuteUpdateResults($arrUpdateData);
  }

  /**
  * get a specific update and install it
  * @param $strUpdateGroup - group to request all SQL updates for
  * @return bool
  */
  function InstallFeature($strFeatureGroup){
   if(trim($strFeatureGroup) == '')
    return FALSE;
   $strPreviousFeatures = get_option('PCMW_features');
   $arrPreviousFeatures = json_decode($strPreviousFeatures,TRUE);
   $boolTablesOnly = FALSE;
   if(array_key_exists($strFeatureGroup,$arrPreviousFeatures) && (int)$arrPreviousFeatures[$strFeatureGroup] < 1)
    $boolTablesOnly = TRUE;
   //has this feature been installed?
   if(!array_key_exists($strFeatureGroup,$arrPreviousFeatures) || $boolTablesOnly){
     //get the feature data
     if(!($arrFeaturesData = $this->GetFeatureData($strFeatureGroup)))
        return FALSE;
     //install our feature
     if($this->ExecuteUpdateResults($arrFeaturesData,$boolTablesOnly)){
      return TRUE;
     }
     else{//this feature could not be installed
        $strFeaturesData = var_export($arrFeaturesData,TRUE);
        PCMW_Logger::Debug('FeaturesData FAILURE ['.$strFeaturesData.']  METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        //send PC failure description.
        $arrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
        $arrFailureData['failuredate'] = $strFeaturesData;
        $this->SendFailedInstallData($arrFailureData);
        return FALSE;
     }
   }
   else{//do not overwrite feature data
        $strFeaturesData = var_export($arrFeaturesData,TRUE);
        PCMW_Logger::Debug('FeaturesData FAILURE ['.$strFeaturesData.']  METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        //send PC failure description.
        $arrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
        $arrFailureData['failuredate'] = $strFeaturesData;
        $this->SendFailedInstallData($arrFailureData);
      return FALSE;
   }
  }

  /**
  * given an update set, execute it
  * @param $arrUpdateData CURL response
  * @param $boolTablesOnly
  * @return bool
  */
  function ExecuteUpdateResults($arrUpdateData,$boolTablesOnly=FALSE){
    //get our previous updates
    $strPreviousUpdates = get_option('PCMW_updates');
    if($strPreviousUpdates)
        $arrPreviousUpdates = json_decode($strPreviousUpdates,TRUE);
    else
        $arrPreviousUpdates = array();
    //PCMW_Logger::Debug(var_export($arrUpdateData,TRUE).' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    $boolSuccess = FALSE;
    //let's see if there are any tables to update
    if($arrUpdateData && array_key_exists('sqltables',$arrUpdateData) && is_array($arrUpdateData['sqltables'])){
     foreach($arrUpdateData['sqltables'] as $intUpdateId=>$arrTable){
       if(array_key_exists($intUpdateId,$arrPreviousUpdates))
        continue 1;
       if(array_key_exists('tabledelta',$arrTable) && trim($arrTable['tabledelta']) != ''){
         if(substr($arrTable['tabledelta'],0,6) == 'CREATE' && PCMW_Database::Get()->CheckForTable($arrTable['tablename']))
            continue 1;//we have this table already
         if(PCMW_Database::Get()->CreateTable(urldecode(urldecode($arrTable['tabledelta'])))){
            //update our update list
            $arrPreviousUpdates[$intUpdateId] = $intUpdateId;
            PCMW_Logger::Debug('Table ['.$arrTable['tablename'].'] updated. METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
         }
         else{
            PCMW_Logger::Debug('Table ['.$arrTable['tablename'].'] NOT updated! METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
         }
       }
     }
     //run it again for table delta
     foreach($arrUpdateData['sqltables'] as $intUpdateId=>$arrTable){
       if(array_key_exists($intUpdateId,$arrPreviousUpdates))
        continue 1;
       //run data update
       if(!$boolTablesOnly && array_key_exists('datadelta',$arrTable) && trim($arrTable['datadelta']) != ''){
         if(PCMW_Database::Get()->RunRawQuery(stripslashes(urldecode(urldecode($arrTable['datadelta']))))){
            //update our update list
            $arrPreviousUpdates[$intUpdateId] = $intUpdateId;
            PCMW_Logger::Debug('Table ['.$arrTable['tablename'].'] data updated. METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
         }
         else
            PCMW_Logger::Debug('Table ['.$arrTable['tablename'].'] data NOT updated! METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
       }
     }
    }
    else{
       //do we have a reason?
       if(is_array($arrUpdateData) && array_key_exists('errormsg',$arrUpdateData)){
        PCMW_Abstraction::Get()->AddUserMSG( $arrUpdateData['errormsg'].' ['.__LINE__.']',1);
        PCMW_Logger::Debug($arrUpdateData['errormsg'].' [data NOT updated!] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
       }
       return FALSE;
    }
    //update our options
    update_option( 'PCMW_updates', json_encode($arrPreviousUpdates),NULL,'no' );
    return TRUE;
  }

  /**
  * install, or disable a page
  * @param $strPageName
  * @param $strPageContent
  * @param $boolActivate
  * @return bool
  */
  function AddSupportingPage($strPageName,$strPageContent,$boolActivate=TRUE){
    if($boolActivate){
      if(!($objMapsPage = get_page_by_title( $strPageName ))){
          $arrMapsPage = array();
          $arrMapsPage['post_title'] = $strPageName;
          $arrMapsPage['post_content'] = $strPageContent;
          $arrMapsPage['post_status'] = 'publish';
          $arrMapsPage['post_type'] = 'page';
          $arrMapsPage['comment_status'] = 'closed';
          $arrMapsPage['ping_status'] = 'closed';
          $arrMapsPage['post_category'] = array(1);
          $arrMapsPage['meta_input'] = array('_wp_page_template'=>'page-templates/full-width.php');
          // Insert the post into the database
          $intMapsPageid = wp_insert_post( $arrMapsPage );
          }
      else {
          $intMapsPageid = $objMapsPage->ID;
          $objMapsPage->post_status = 'publish';
          $intMapsPageid = wp_update_post( $objMapsPage );

      }
    }
    else{
      if(($objMapsPage = get_page_by_title( $strPageName ))){
          $intMapsPageid = $objMapsPage->ID;
          $objMapsPage->post_status = 'draft';
          $intMapsPageid = wp_update_post( $objMapsPage );
      }
    }
    return TRUE;
  }

  /**
  * check to see if we have an admin user account to control the plugin with
  * No controls, no access
  * Verify we have internet access and reschedule an installation attempt at a
  * later date if we don't have it. DB construction requires internet access
  * @return nool
  */
  function VerifyPluginAdmin(){
    $boolAdminPresent = TRUE;
    //check session for an admin ID
    if(!$arrPresentUser = PCMW_Abstraction::Get()->CheckUserStatus()){
      //check for an admin account
      if(!(PCMW_AdminUserCore::GetAdminUserId(0,get_current_user_id()))){
        //PCPluginInstall::Get()->InstallCoreFeatures()
        if(!PCPluginInstall::Get()->InstallCoreFeatures()){
          $boolAdminPresent = FALSE;
          PCMW_Abstraction::Get()->AddUserMSG( '<h3>It seems something has gone wrong with your installation. Please re-install PC Megaworks, or contact '.PCMW_SUPPORT.' for further assistance.['.__LINE__.']</h3>',1);
          $strSESSION = var_export($_SESSION,TRUE);
          PCMW_Logger::Debug('Admin account info not created, or otherwise doesn\'t exist. SESSION ['.$strSESSION.']  METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        }
      }
    }
    //check access
    if(!PCMW_Utility::Get()->GetURLHeaderHTTP(get_site_url(),TRUE)){
      PCMW_Abstraction::Get()->AddUserMSG( '<h3>We\re sorry, but you don\'t seem to be connected to the internet and necessary updates cannot be made. You may not have access to any features.</h3>',2);
      if($boolAdminPresent){
        $strSESSION = var_export($_SESSION,TRUE);
        PCMW_Logger::Debug('Admin options failed. No internet. SESSION ['.$strSESSION.']  METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      }
      $boolAdminPresent = FALSE;
    }
    return $boolAdminPresent;
  }

}//end class
?>