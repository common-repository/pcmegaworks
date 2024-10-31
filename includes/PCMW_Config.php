<?php
/**************************************************
* Class :PCMW_Config
* @brief assemble config data for use in the plugin
* defaults will be set here, and overwritten with key=>value import array
* @REQUIRES
*   -PCMW_BaseClass.php   
*
***************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;

class PCMW_Config extends PCMW_BaseClass
{
    private $strServerPath='';//SERVERPATH
    private $intPluginActive=0;//PCPLUGINACTIVE
    private $strServerAdress='';//SERVERADDRESS
    private $strPluginVersion='';//VERSION
    private $strPreviousVersion='';//PREVIOUSVERSION
    private $strLogo='';//LOGO
    private $strAdminEmail='';//ADMIN
    private $strSiteName='';//SITENAME
    private $strUserKey='';//USERKEY
    private $strFTPAddress='';//FTPADDRESS;//
    private $strGoogleMapKey='';//GOOGLEMAPKEY;//
    private $strProductFolder='';//PRODUCTFOLDER;//
    private $strHomePage='';//HOMEPAGE;//
    private $boolUseCustomLogin=0;//USECUSTOMLOGIN;//
    private $boolUseCustomMenu=0;//LOGINMENUOPTION;//
    private $strLoginPage='';//LOGINPAGE;//
    private $boolUseCustomRegistration=0;//USECUSTOMREGISTRATION;//
    private $boolRegistrationInMenu=0;//REGISTRATIONMENUOPTION;//
    private $strRegistrationPage='';//REGISTRATIONPAGE;//
    private $boolUseContactUs=0;//USECONTACTUS;//
    private $boolUseCustomHAWD=0;//USECUSTOMHAWD;//
    private $intDebugArg=1;//DEBUG_ARG;//
    private $boolRestrictPages=0;//RESTRICTPAGES;//
    private $strPostLoginRedirect='';//POSTLOGINREDIRECT;//
    private $strCompanyName='';//COMPANYNAME;//
    private $strCompanyAddress='';//COMPANYADDRESS;//
    private $strCompanyCity='';//COMPANYCITY;//
    private $strCompanyState='';//COMPANYSTATE;//
    private $intCompanyZip=0;//COMPANYZIP;//
    private $strCompanyTelephone='';//COMPANYTELEPHONE;//
    private $strSalesEmail='';//SALES;//
    private $boolCanHelpDesk=0;//CANHELPDESK;//
    //hold validation errors
    public $arrValidationErrors;

  function __construct()
  {
    //set mandatory values
    $this->strServerPath = $_SERVER['DOCUMENT_ROOT'];
  }

  /**
  * Server Path for internal use
  * @param $strPluginVersion
  * @return bool
  */
  function SetPluginVersion($strPluginVersion='000.000.000'){
    $this->strPluginVersion = $strPluginVersion;
    return TRUE;
  }//VERSION

  /**
  * Server Path for internal use
  * @param $strPreviousVersion
  * @return bool
  */
  function SetPreviousVersion($strPreviousVersion='000.000.000'){
    $this->strPreviousVersion = $strPreviousVersion;
    return TRUE;
  }//PREVIOUSVERSION

  /**
  * Server Path for internal use
  * @param $strServerPath
  * @return bool
  */
  function SetServerPath($strServerPath=''){
    $this->strServerPath = $strServerPath;
    return TRUE;
  }//SERVERPATH

  /**
  * Server Address for internal use
  * @param $strServerAddress
  * @return bool
  */
  function SetServerAddress($strServerAddress=''){
    $this->strServerAddress = $strServerAddress;
    return TRUE;
  }//SERVERADDRESS

  /**
  * Server Path for internal use
  * @param $strLogo
  * @return bool
  */
  function SetLogo($strLogo=''){
    $this->strLogo = $strLogo;
    return TRUE;
  }//LOGO


  /**
  * Server Path for internal use
  * @param $intPluginActive
  * @return bool
  */
  function SetPluginActive($intPluginActive=0){
    $this->intPluginActive = $intPluginActive;
    return TRUE;
  }//PCPLUGINACTIVE

  /**
  * Server Path for internal use
  * @param $strAdminEmail
  * @return bool
  */
  function SetAdminEmail($strAdminEmail=''){
    $this->strAdminEmail = $strAdminEmail;
    return TRUE;
  }//ADMIN

  /**
  * Server Path for internal use
  * @param $strSiteName
  * @return bool
  */
  function SetSiteName($strSiteName=''){
    $this->strSiteName = $strSiteName;
    return TRUE;
  }//SITENAME
  /**
  * Server Path for internal use
  * @param $strUserKey
  * @return bool
  */
  function SetUserKey($strUserKey=''){
    $this->strUserKey = $strUserKey;
    return TRUE;
  }//USERKEY
  /**
  * Server Path for internal use
  * @param $strFTPAddress
  * @return bool
  */
  function SetFTPAddress($strFTPAddress=''){
    $this->strFTPAddress = $strFTPAddress;
    return TRUE;
  }//FTPADDRESS

  /**
  * Server Path for internal use
  * @param $strGoogleMapKey
  * @return bool
  */
  function SetGoogleMapKey($strGoogleMapKey=''){
    $this->strGoogleMapKey = $strGoogleMapKey;
    return TRUE;
  }//GOOGLEMAPKEY

  /**
  * Server Path for internal use
  * @param $strProductFolder
  * @return bool
  */
  function SetProductFolder($strProductFolder=''){
    $this->strProductFolder = $strProductFolder;
    return TRUE;
  }//PRODUCTFOLDER

  /**
  * Server Path for internal use
  * @param $strHomePage
  * @return bool
  */
  function SetHomePage($strHomePage=''){
    $this->strHomePage = $strHomePage;
    return TRUE;
  }//HOMEPAGE

  /**
  * Server Path for internal use
  * @param $boolUseCustomLogin
  * @return bool
  */
  function SetUseCustomLogin($boolUseCustomLogin=FALSE){
    $this->boolUseCustomLogin = $boolUseCustomLogin;
    return TRUE;
  }//USECUSTOMLOGIN


  /**
  * Server Path for internal use
  * @param $boolUseCustomMenu
  * @return bool
  */
  function SetUseCustomMenu($boolUseCustomMenu=FALSE){
    $this->boolUseCustomMenu = $boolUseCustomMenu;
    return TRUE;
  }//LOGINMENUOPTION

  /**
  * Server Path for internal use
  * @param $strLoginPage
  * @return bool
  */
  function SetLoginPage($strLoginPage=''){
    $this->strLoginPage = $strLoginPage;
    return TRUE;
  }//LOGINPAGE

  /**
  * Server Path for internal use
  * @param $boolUseCustomRegistration
  * @return bool
  */
  function SetUseCustomRegistration($boolUseCustomRegistration=FALSE){
    $this->boolUseCustomRegistration = $boolUseCustomRegistration;
    return TRUE;
  }//USECUSTOMREGISTRATION

  /**
  * Server Path for internal use
  * @param $boolRegistrationInMenu
  * @return bool
  */
  function SetRegistrationInMenu($boolRegistrationInMenu=FALSE){
    $this->boolRegistrationInMenu = $boolRegistrationInMenu;
    return TRUE;
  }//REGISTRATIONMENUOPTION

  /**
  * Server Path for internal use
  * @param $strRegistrationPage
  * @return bool
  */
  function SetRegistrationPage($strRegistrationPage=''){
    $this->strRegistrationPage = $strRegistrationPage;
    return TRUE;
  }//REGISTRATIONPAGE

    /**
  * Server Path for internal use
  * @param $boolUseContactUs
  * @return bool
  */
  function SetUseContactUs($boolUseContactUs=FALSE){
    $this->boolUseContactUs = $boolUseContactUs;
    return TRUE;
  }//USECONTACTUS

  /**
  * Server Path for internal use
  * @param $boolUseCustomHAWD
  * @return bool
  */
  function SetUseCustomHAWD($boolUseCustomHAWD=FALSE){
    $this->boolUseCustomHAWD = $boolUseCustomHAWD;
    return TRUE;
  }//USECUSTOMHAWD

  /**
  * Server Path for internal use
  * @param $intDebugArg
  * @return bool
  */
  function SetDebugArg($intDebugArg = 0){
    $this->intDebugArg = $intDebugArg;
    return TRUE;
  }//DEBUG_ARG

  /**
  * Server Path for internal use
  * @param $boolRestrictPages
  * @return bool
  */
  function SetRestrictPages($boolRestrictPages=FALSE){
    $this->boolRestrictPages = $boolRestrictPages;
    return TRUE;
  }//RESTRICTPAGES

  /**
  * Server Path for internal use
  * @param $strPostLoginRedirect
  * @return bool
  */
  function SetPostLoginRedirect($strPostLoginRedirect=''){
    $this->strPostLoginRedirect = $strPostLoginRedirect;
    return TRUE;
  }//POSTLOGINREDIRECT

  /**
  * Server Path for internal use
  * @param $strCompanyName
  * @return bool
  */
  function SetCompanyName($strCompanyName=''){
    $this->strCompanyName = $strCompanyName;
    return TRUE;
  }//COMPANYNAME

  /**
  * Server Path for internal use
  * @param $strCompanyAddress
  * @return bool
  */
  function SetCompanyAddress($strCompanyAddress=''){
    $this->strCompanyAddress = $strCompanyAddress;
    return TRUE;
  }//COMPANYADDRESS

  /**
  * Server Path for internal use
  * @param $strCompanyCity
  * @return bool
  */
  function SetCompanyCity($strCompanyCity=''){
    $this->strCompanyCity = $strCompanyCity;
    return TRUE;
  }//COMPANYCITY

  /**
  * Server Path for internal use
  * @param $strCompanyState
  * @return bool
  */
  function SetCompanyState($strCompanyState=''){
    $this->strCompanyState = $strCompanyState;
    return TRUE;
  }//COMPANYSTATE

  /**
  * Server Path for internal use
  * @param $intCompanyZip
  * @return bool
  */
  function SetCompanyZip($intCompanyZip = 0){
    $this->intCompanyZip = $intCompanyZip;
    return TRUE;
  }//COMPANYZIP

  /**
  * Server Path for internal use
  * @param $strCompanyTelephone
  * @return bool
  */
  function SetCompanyTelephone($strCompanyTelephone=''){
    $this->strCompanyTelephone = $strCompanyTelephone;
    return TRUE;
  }//COMPANYTELEPHONE

  /**
  * Server Path for internal use
  * @param $strSalesEmail
  * @return bool
  */
  function SetSalesEmail($strSalesEmail=''){
    $this->strSalesEmail = $strSalesEmail;
    return TRUE;
  }//SALES

  /**
  * Server Path for internal use
  * @param $boolCanHelpDesk
  * @return bool
  */
  function SetCanHelpDesk($boolCanHelpDesk=FALSE){
    $this->boolCanHelpDesk = $boolCanHelpDesk;
    return TRUE;
  }//CANHELPDESK


  /***********************************************************
  ******* Get Private Members ********************************
  ***********************************************************/
  /**
  * Server Path for internal use
  * @return $strPluginVersion
  */
  function GetPluginVersion(){
    return $this->strPluginVersion;
  }//VERSION

  /**
  * Server Path for internal use
  * @return $strPreviousVersion
  */
  function GetPreviousVersion(){
    return $this->strPreviousVersion;
  }//PREVIOUSVERSION

  /**
  * Server Path for internal use
  * @return  $strServerPath
  */
  function GetServerPath(){
    return $this->strServerPath;
  }//SERVERPATH

  /**
  * Server Address for internal use
  * @return  $strServerAddress
  */
  function GetServerAddress(){
    return $this->strServerAddress;
  }//SERVERADDRESS

  /**
  * Server Path for internal use
  * @return  $strLogo
  */
  function GetLogo(){
    return $this->strLogo;
  }//LOGO

  /**
  * Server Path for internal use
  * @return  $intPluginActive
  */
  function GetPluginActive(){
    return $this->intPluginActive;
  }//PCPLUGINACTIVE

  /**
  * Server Path for internal use
  * @return  $strAdminEmail
  */
  function GetAdminEmail(){
    return $this->strAdminEmail;
  }//ADMIN

  /**
  * Server Path for internal use
  * @return  $strSiteName
  */
  function GetSiteName(){
    return $this->strSiteName;
  }//SITENAME
  /**
  * Server Path for internal use
  * @return  $strUserKey
  */
  function GetUserKey(){
    return $this->strUserKey;
  }//USERKEY
  /**
  * Server Path for internal use
  * @return  $strFTPAddress
  */
  function GetFTPAddress(){
    return $this->strFTPAddress;
  }//FTPADDRESS

  /**
  * Server Path for internal use
  * @return  $strGoogleMapKey
  */
  function GetGoogleMapKey(){
    return $this->strGoogleMapKey;
  }//GOOGLEMAPKEY

  /**
  * Server Path for internal use
  * @return  $strProductFolder
  */
  function GetProductFolder(){
    return $this->strProductFolder;
  }//PRODUCTFOLDER

  /**
  * Server Path for internal use
  * @return  $strHomePage
  */
  function GetHomePage(){
    return $this->strHomePage;
  }//HOMEPAGE

  /**
  * Server Path for internal use
  * @return  $boolUseCustomLogin
  */
  function GetUseCustomLogin(){
    return $this->boolUseCustomLogin;
  }//USECUSTOMLOGIN


  /**
  * Server Path for internal use
  * @return  $boolUseCustomMenu
  */
  function GetUseCustomMenu(){
    return $this->boolUseCustomMenu;
  }//LOGINMENUOPTION

  /**
  * Server Path for internal use
  * @return  $strLoginPage
  */
  function GetLoginPage(){
    return $this->strLoginPage;
  }//LOGINPAGE

  /**
  * Server Path for internal use
  * @return  $boolUseCustomRegistration
  */
  function GetUseCustomRegistration(){
    return $this->boolUseCustomRegistration;
  }//USECUSTOMREGISTRATION

  /**
  * Server Path for internal use
  * @return  $boolRegistrationInMenu
  */
  function GetRegistrationInMenu(){
    return $this->boolRegistrationInMenu;
  }//REGISTRATIONMENUOPTION

  /**
  * Server Path for internal use
  * @return  $strRegistrationPage
  */
  function GetRegistrationPage(){
    return $this->strRegistrationPage;
  }//REGISTRATIONPAGE

    /**
  * Server Path for internal use
  * @return  $boolUseContactUs
  */
  function GetUseContactUs(){
    return $this->boolUseContactUs;
  }//USECONTACTUS

  /**
  * Server Path for internal use
  * @return  $boolUseCustomHAWD
  */
  function GetUseCustomHAWD(){
    return $this->boolUseCustomHAWD;
  }//USECUSTOMHAWD

  /**
  * Server Path for internal use
  * @return  $intDebugArg
  */
  function GetDebugArg(){
    return $this->intDebugArg;
  }//DEBUG_ARG

  /**
  * Server Path for internal use
  * @return  $boolRestrictPages
  */
  function GetRestrictPages(){
    return $this->boolRestrictPages;
  }//RESTRICTPAGES

  /**
  * Server Path for internal use
  * @return  $strPostLoginRedirect
  */
  function GetPostLoginRedirect(){
    return $this->strPostLoginRedirect;
  }//POSTLOGINREDIRECT

  /**
  * Server Path for internal use
  * @return  $strCompanyName
  */
  function GetCompanyName(){
    return $this->strCompanyName;
  }//COMPANYNAME

  /**
  * Server Path for internal use
  * @return  $strCompanyAddress
  */
  function GetCompanyAddress(){
    return $this->strCompanyAddress;
  }//COMPANYADDRESS

  /**
  * Server Path for internal use
  * @return  $strCompanyCity
  */
  function GetCompanyCity(){
    return $this->strCompanyCity;
  }//COMPANYCITY

  /**
  * Server Path for internal use
  * @return  $strCompanyState
  */
  function GetCompanyState(){
    return $this->strCompanyState;
  }//COMPANYSTATE

  /**
  * Server Path for internal use
  * @return  $intCompanyZip
  */
  function GetCompanyZip(){
    return $this->intCompanyZip;
  }//COMPANYZIP

  /**
  * Server Path for internal use
  * @return  $strCompanyTelephone
  */
  function GetCompanyTelephone(){
    return $this->strCompanyTelephone;
  }//COMPANYTELEPHONE

  /**
  * Server Path for internal use
  * @return  $strSalesEmail
  */
  function GetSalesEmail(){
    return $this->strSalesEmail;
  }//SALES

  /**
  * Server Path for internal use
  * @return  $boolCanHelpDesk
  */
  function GetCanHelpDesk(){
    return $this->boolCanHelpDesk;
  }//CANHELPDESK


  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_Config();
		return( $inst );
  }



  /**
  * serialize and store our config
  * @return bool
  */
  function UnSerializeConfig(){
    if(!$objConfig = $this->getObject('pcsessionconfig'))
        return FALSE;
    $this->UpdateObjectWithObject($objConfig);
    return TRUE;
  }

  public function LoadObjectWithArray($arrArray){
    $this->intPluginActive=(int) $arrArray['PCPLUGINACTIVE'];//PCPLUGINACTIVE
    $this->strServerAdress=(string) $arrArray['PCMW_SERVERADDRESS'];//SERVERADDRESS
    $this->strPluginVersion=(string) $arrArray['PCMW_VERSION'];//VERSION
    $this->strPreviousVersion=(string) $arrArray['PCMW_PREVIOUSVERSION'];//PREVIOUSVERSION
    $this->strServerPath = (string) $arrArray['PCMW_SERVERPATH'];//SERVERPATH
    $this->strLogo = (string) $arrArray['PCMW_LOGO'];//LOGO
    $this->strAdminEmail = (string) $arrArray['PCMW_ADMIN'];//ADMIN
    $this->strSiteName = (string) $arrArray['PCMW_SITENAME'];//SITENAME
    $this->strUserKey = (string) $arrArray['PCMW_USERKEY'];//USERKEY
    $this->strFTPAddress = (string) $arrArray['PCMW_FTPADDRESS'];//FTPADDRESS;//
    $this->strGoogleMapKey = (string) $arrArray['PCMW_GOOGLEMAPKEY'];//GOOGLEMAPKEY;//
    $this->strProductFolder = (string) $arrArray['PCMW_PRODUCTFOLDER'];//PRODUCTFOLDER;//
    $this->strHomePage = (string) $arrArray['PCMW_HOMEPAGE'];//HOMEPAGE;//
    $this->boolUseCustomLogin = (bool) $arrArray['PCMW_USECUSTOMLOGIN'];//USECUSTOMLOGIN;//
    $this->boolUseCustomMenu = (bool) $arrArray['PCMW_LOGINMENUOPTION'];//LOGINMENUOPTION;//
    $this->strLoginPage = (string) $arrArray['PCMW_LOGINPAGE'];//LOGINPAGE;//
    $this->boolUseCustomRegistration = (bool) $arrArray['PCMW_USECUSTOMREGISTRATION'];//USECUSTOMREGISTRATION;//
    $this->boolRegistrationInMenu = (bool) $arrArray['PCMW_REGISTRATIONMENUOPTION'];//REGISTRATIONMENUOPTION;//
    $this->strRegistrationPage = (string) $arrArray['PCMW_REGISTRATIONPAGE'];//REGISTRATIONPAGE;//
    $this->boolUseContactUs = (bool) $arrArray['PCMW_USECONTACTUS'];//USECONTACTUS;//
    $this->boolUseCustomHAWD = (bool) $arrArray['PCMW_USECUSTOMHAWD'];//USECUSTOMHAWD;//
    $this->intDebugArg = (int) $arrArray['PCMW_DEBUG_ARG'];//DEBUG_ARG;//
    $this->boolRestrictPages = (bool) $arrArray['PCMW_RESTRICTPAGES'];//RESTRICTPAGES;//
    $this->strPostLoginRedirect = (string) $arrArray['PCMW_POSTLOGINREDIRECT'];//POSTLOGINREDIRECT;//
    $this->strCompanyName = (string) $arrArray['PCMW_COMPANYNAME'];//COMPANYNAME;//
    $this->strCompanyAddress = (string) $arrArray['PCMW_COMPANYADDRESS'];//COMPANYADDRESS;//
    $this->strCompanyCity = (string) $arrArray['PCMW_COMPANYCITY'];//COMPANYCITY;//
    $this->strCompanyState = (string) $arrArray['PCMW_COMPANYSTATE'];//COMPANYSTATE;//
    $this->intCompanyZip = (int) $arrArray['PCMW_COMPANYZIP'];//COMPANYZIP;//
    $this->strCompanyTelephone = (string) $arrArray['PCMW_COMPANYTELEPHONE'];//COMPANYTELEPHONE;//
    $this->strSalesEmail = (string) $arrArray['PCMW_SALES'];//SALES;//
    $this->boolCanHelpDesk = (bool) $arrArray['PCMW_CANHELPDESK'];//CANHELPDESK;//
    return $this;
  }

    /**
    * given an array sent in JSON format, rehydrate the object
    * @param $arrObject
    * @return $this
    */
    public function LoadObjectWithArrayObject($arrArray){
      foreach($arrArray as $varKey=>$varValue){
        if(property_exists($this,$varKey))
            $this->{$varKey} = $varValue;
      }
      return $this;
    }

    /**
    * update an object with an object
    * @param $objUpdatingObject
    * @return bool
    */
    function UpdateObjectWithObject($objUpdatingObject){
     $arrObjectVars = get_object_vars($objUpdatingObject);
     foreach($arrObjectVars as $strName => $varValue)
        $this->$strName = $varValue;
     return TRUE;
    }

    /**
    * given an array of table data, check for updates
    * @param $arrObjectData
    * return bool
    */
    function UpdateObjectWithArray($arrObjectData){           
      if(isset($arrObjectData['PCPLUGINACTIVE']))
        $this->intPluginActive = (int) $arrObjectData['PCPLUGINACTIVE'];//PCPLUGINACTIVE
      if(isset($arrObjectData['PCMW_SERVERADDRESS']))
        $this->strServerAdress = (string) $arrObjectData['PCMW_SERVERADDRESS'];//SERVERADDRESS
      if(isset($arrObjectData['PCMW_VERSION']))
        $this->strPluginVersion = (string) $arrObjectData['PCMW_VERSION'];//VERSION
      if(isset($arrObjectData['PCMW_PREVIOUSVERSION']))
        $this->strPreviousVersion = (string) $arrObjectData['PCMW_PREVIOUSVERSION'];//PREVIOUSVERSION
      if(isset($arrObjectData['PCMW_SERVERPATH']))
        $this->strServerPath = (string) $arrObjectData['PCMW_SERVERPATH'];//SERVERPATH
      if(isset($arrObjectData['PCMW_LOGO']))
        $this->strLogo = (string) $arrObjectData['PCMW_LOGO'];//LOGO
      if(isset($arrObjectData['PCMW_ADMIN']))
        $this->strAdminEmail = (string) $arrObjectData['PCMW_ADMIN'];//ADMIN
      if(isset($arrObjectData['PCMW_SITENAME']))
        $this->strSiteName = (string) $arrObjectData['PCMW_SITENAME'];//SITENAME
      if(isset($arrObjectData['PCMW_USERKEY']))
        $this->strUserKey = (string) $arrObjectData['PCMW_USERKEY'];//USERKEY
      if(isset($arrObjectData['PCMW_FTPADDRESS']))
        $this->strFTPAddress = (string) $arrObjectData['PCMW_FTPADDRESS'];//FTPADDRESS;//
      if(isset($arrObjectData['PCMW_GOOGLEMAPKEY']))
        $this->strGoogleMapKey = (string) $arrObjectData['PCMW_GOOGLEMAPKEY'];//GOOGLEMAPKEY;//
      if(isset($arrObjectData['PCMW_PRODUCTFOLDER']))
        $this->strProductFolder = (string) $arrObjectData['PCMW_PRODUCTFOLDER'];//PRODUCTFOLDER;//
      if(isset($arrObjectData['PCMW_HOMEPAGE']))
        $this->strHomePage = (string) $arrObjectData['PCMW_HOMEPAGE'];//HOMEPAGE;//
      if(isset($arrObjectData['PCMW_USECUSTOMLOGIN']))
        $this->boolUseCustomLogin = (bool) $arrObjectData['PCMW_USECUSTOMLOGIN'];//USECUSTOMLOGIN;//
      if(isset($arrObjectData['PCMW_LOGINMENUOPTION']))
        $this->boolUseCustomMenu = (bool) $arrObjectData['PCMW_LOGINMENUOPTION'];//LOGINMENUOPTION;//
      if(isset($arrObjectData['PCMW_LOGINPAGE']))
        $this->strLoginPage = (string) $arrObjectData['PCMW_LOGINPAGE'];//LOGINPAGE;//
      if(isset($arrObjectData['PCMW_USECUSTOMREGISTRATION']))
        $this->boolUseCustomRegistration = (bool) $arrObjectData['PCMW_USECUSTOMREGISTRATION'];//USECUSTOMREGISTRATION;//
      if(isset($arrObjectData['PCMW_REGISTRATIONMENUOPTION']))
        $this->boolRegistrationInMenu = (bool) $arrObjectData['PCMW_REGISTRATIONMENUOPTION'];//REGISTRATIONMENUOPTION;//
      if(isset($arrObjectData['PCMW_REGISTRATIONPAGE']))
        $this->strRegistrationPage = (string) $arrObjectData['PCMW_REGISTRATIONPAGE'];//REGISTRATIONPAGE;//
      if(isset($arrObjectData['PCMW_USECONTACTUS']))
        $this->boolUseContactUs = (bool) $arrObjectData['PCMW_USECONTACTUS'];//USECONTACTUS;//
      if(isset($arrObjectData['PCMW_USECUSTOMHAWD']))
        $this->boolUseCustomHAWD = (bool) $arrObjectData['PCMW_USECUSTOMHAWD'];//USECUSTOMHAWD;//
      if(isset($arrObjectData['PCMW_DEBUG_ARG']))
        $this->intDebugArg = (int) $arrObjectData['PCMW_DEBUG_ARG'];//DEBUG_ARG;//
      if(isset($arrObjectData['PCMW_RESTRICTPAGES']))
        $this->boolRestrictPages = (bool) $arrObjectData['PCMW_RESTRICTPAGES'];//RESTRICTPAGES;//
      if(isset($arrObjectData['PCMW_POSTLOGINREDIRECT']))
        $this->strPostLoginRedirect = (string) $arrObjectData['PCMW_POSTLOGINREDIRECT'];//POSTLOGINREDIRECT;//
      if(isset($arrObjectData['PCMW_COMPANYNAME']))
        $this->strCompanyName = (string) $arrObjectData['PCMW_COMPANYNAME'];//COMPANYNAME;//
      if(isset($arrObjectData['PCMW_COMPANYADDRESS']))
        $this->strCompanyAddress = (string) $arrObjectData['PCMW_COMPANYADDRESS'];//COMPANYADDRESS;//
      if(isset($arrObjectData['PCMW_COMPANYCITY']))
        $this->strCompanyCity = (string) $arrObjectData['PCMW_COMPANYCITY'];//COMPANYCITY;//
      if(isset($arrObjectData['PCMW_COMPANYSTATE']))
        $this->strCompanyState = (string) $arrObjectData['PCMW_COMPANYSTATE'];//COMPANYSTATE;//
      if(isset($arrObjectData['PCMW_COMPANYZIP']))
        $this->intCompanyZip = (int) $arrObjectData['PCMW_COMPANYZIP'];//COMPANYZIP;//
      if(isset($arrObjectData['PCMW_COMPANYTELEPHONE']))
        $this->strCompanyTelephone = (string) $arrObjectData['PCMW_COMPANYTELEPHONE'];//COMPANYTELEPHONE;//
      if(isset($arrObjectData['PCMW_SALES']))
        $this->strSalesEmail = (string) $arrObjectData['PCMW_SALES'];//SALES;//
      if(isset($arrObjectData['PCMW_CANHELPDESK']))
        $this->boolCanHelpDesk = (bool) $arrObjectData['PCMW_CANHELPDESK'];//CANHELPDESK;//
      return TRUE;
    }

    /*
    @brief load an array with the PCMW_Config object
    @param $objConfig
    @return array(PCMW_Config)
    */
    public function LoadArrayWithObject($objConfig=null){
     $arrArray = array();
    (int) $arrArray['PCPLUGINACTIVE']=$this->intPluginActive;//PCPLUGINACTIVE
    (string) $arrArray['PCMW_SERVERADDRESS']=$this->strServerAdress;//SERVERADDRESS
    (string) $arrArray['PCMW_VERSION']=$this->strPluginVersion;//VERSION
    (string) $arrArray['PCMW_PREVIOUSVERSION']=$this->strPreviousVersion;//PREVIOUSVERSION
    (string) $arrArray['PCMW_SERVERPATH'] = $this->strServerPath;//SERVERPATH
    (string) $arrArray['PCMW_LOGO'] = $this->strLogo;//LOGO
    (string) $arrArray['PCMW_ADMIN'] = $this->strAdminEmail;//ADMIN
    (string) $arrArray['PCMW_SITENAME'] = $this->strSiteName;//SITENAME
    (string) $arrArray['PCMW_USERKEY'] = $this->strUserKey;//USERKEY
    (string) $arrArray['PCMW_FTPADDRESS'] = $this->strFTPAddress;//FTPADDRESS;//
    (string) $arrArray['PCMW_GOOGLEMAPKEY'] = $this->strGoogleMapKey;//GOOGLEMAPKEY;//
    (string) $arrArray['PCMW_PRODUCTFOLDER'] = $this->strProductFolder;//PRODUCTFOLDER;//
    (string) $arrArray['PCMW_HOMEPAGE'] = $this->strHomePage;//HOMEPAGE;//
    (bool) $arrArray['PCMW_USECUSTOMLOGIN'] = $this->boolUseCustomLogin;//USECUSTOMLOGIN;//
    (bool) $arrArray['PCMW_LOGINMENUOPTION'] = $this->boolUseCustomMenu;//LOGINMENUOPTION;//
    (string) $arrArray['PCMW_LOGINPAGE'] = $this->strLoginPage;//LOGINPAGE;//
    (bool) $arrArray['PCMW_USECUSTOMREGISTRATION'] = $this->boolUseCustomRegistration;//USECUSTOMREGISTRATION;//
    (bool) $arrArray['PCMW_REGISTRATIONMENUOPTION'] = $this->boolRegistrationInMenu;//REGISTRATIONMENUOPTION;//
    (string) $arrArray['PCMW_REGISTRATIONPAGE'] = $this->strRegistrationPage;//REGISTRATIONPAGE;//
    (bool) $arrArray['PCMW_USECONTACTUS'] = $this->boolUseContactUs;//USECONTACTUS;//
    (bool) $arrArray['PCMW_USECUSTOMHAWD'] = $this->boolUseCustomHAWD;//USECUSTOMHAWD;//
    (int) $arrArray['PCMW_DEBUG_ARG'] = $this->intDebugArg;//DEBUG_ARG;//
    (bool) $arrArray['PCMW_RESTRICTPAGES'] = $this->boolRestrictPages;//RESTRICTPAGES;//
    (string) $arrArray['PCMW_POSTLOGINREDIRECT'] = $this->strPostLoginRedirect;//POSTLOGINREDIRECT;//
    (string) $arrArray['PCMW_COMPANYNAME'] = $this->strCompanyName;//COMPANYNAME;//
    (string) $arrArray['PCMW_COMPANYADDRESS'] = $this->strCompanyAddress;//COMPANYADDRESS;//
    (string) $arrArray['PCMW_COMPANYCITY'] = $this->strCompanyCity;//COMPANYCITY;//
    (string) $arrArray['PCMW_COMPANYSTATE'] = $this->strCompanyState;//COMPANYSTATE;//
    (int) $arrArray['PCMW_COMPANYZIP'] = $this->intCompanyZip;//COMPANYZIP;//
    (string) $arrArray['PCMW_COMPANYTELEPHONE'] = $this->strCompanyTelephone;//COMPANYTELEPHONE;//
    (string) $arrArray['PCMW_SALES'] = $this->strSalesEmail;//SALES;//
    (bool) $arrArray['PCMW_CANHELPDESK'] = $this->boolCanHelpDesk;//CANHELPDESK;//
     return $arrArray;
    }

}//end class PCMW_Config


/**
 * @class ConfigCore
 * -Requires
 *  -PCMW_Utility.php
 *  -PCMW_HostRequest.php
 *  -PCMW_Config.php
 *  -PCMW_Abstraction.php
 *  -PCMW_Logger.php
 */
class PCMW_ConfigCore
{
    public $objConfig;// our config object

  function __construct(){
  //construct
  }


  public static function Get(){
    //==== instantiate or retrieve singleton ====
    static $inst = NULL;
    if( $inst == NULL )
      $inst = new PCMW_ConfigCore();
    return( $inst );
  }

  /**
  * serialize and store our config
  * @return bool
  */
  function SerializeConfig(){
    if(is_object($this->objConfig))
        return $this->objConfig->storeObject($this->objConfig, 'pcsessionconfig');
    return FALSE;
  }

  /**
  * create config data for plugins
  * @return bool
  */
  function CreateConfigValues(){
    global $current_user;
    $this->objConfig = new PCMW_Config();
    $strHTTP = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')? 'http://': 'https://';
        $this->objConfig->SetServerAddress(get_site_url().'/');
        $this->objConfig->SetServerPath($_SERVER['DOCUMENT_ROOT']);
        $this->objConfig->SetPluginActive(1);
        $this->objConfig->SetDebugArg(1);
        $this->objConfig->SetSiteName(get_bloginfo());
        $this->objConfig->SetCompanyName(get_bloginfo());
        $this->objConfig->SetAdminEmail($current_user->user_email);
        $this->objConfig->SetSalesEmail($current_user->user_email);
        $this->objConfig->SetLoginPage('login');
        $this->objConfig->SetRegistrationPage('register');
        $this->objConfig->SetHomePage(get_home_url());
        $this->objConfig->SetLogo(get_header_image());
    //get the user key
    $arrRequest = array('purpose'=>'userkey','address'=>get_site_url().'/','version'=>'0','userkey'=>PCMW_ConfigCore::Get()->objConfig->GetUserKey());
    if($arrPayLoad = PCMW_HostRequest::Get()->MakeHostRequest($arrRequest)){
        $this->objConfig->SetPluginVersion($arrPayLoad['payload']['version']);
        $this->objConfig->SetUserKey($arrPayLoad['payload']['userkey']);
        $this->objConfig->SetCanHelpDesk($arrPayLoad['payload']['canhelpdesk']);
    }
    else{
        PCMW_Abstraction::Get()->AddUserMSG( 'Unable to make host request. We cannot create your database, and the plugin may not function correctly. ['.__LINE__.']',1);
    }
    $this->SerializeConfig();//update our config each time a change is made
    $strPCConfig = PCMW_Utility::Get()->JSONEncode($this->objConfig);
    if(update_option( 'PCPlugin', $strPCConfig,NULL,'yes' )){
        return TRUE;
    }
    else{
        $strSession = var_export($_SESSION,TRUE);
        //PCMW_Logger::Debug('$strSession not inserted ['.$strSession.'] FILE ['.__FILE__.'] LINE['.__LINE__.']',1);
        //send PC failure description.
        $arrrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
        $arrrFailureData['failuredate'] = $strSession;
        $this->SendFailedInstallData($arrrFailureData);
    }
    return TRUE;
  }

  /**
  * update the config record, assuming a change was made
  * @return bool
  */
  function UpdatePCConfig(){
    $arrConfig = $this->objConfig->LoadArrayWithObject();
    $strPCConfig = PCMW_Utility::Get()->JSONEncode($arrConfig);
    if(!is_admin())
        return TRUE;
    if(!update_option( 'PCPlugin', $strPCConfig,NULL,'yes' )){
        //PCMW_Abstraction::Get()->AddUserMSG( 'Settings not updated! ['.__LINE__.']',1);
        return FALSE;
    }
    else{
        //PCMW_Abstraction::Get()->AddUserMSG( 'Settings Updated! ['.__LINE__.']',3);
        return TRUE;
    }
  }

  /**
  * get the config values and load them into session if they do not exist
  * @return bool
  */
  function LoadConfigFromStorage(){
    if(!array_key_exists('pcsessionconfig',$_SESSION)){
      //throw new exception('This is the starting line!!['.__LINE__.']<br /><br />');
      $this->objConfig = new PCMW_Config();
      if($arrConfig = PCMW_Utility::Get()->JSONDecode(get_option('PCPlugin'))){
        $this->objConfig->LoadObjectWithArray($arrConfig);
        $this->SerializeConfig();
        return TRUE;
      }
      else{//no config to load. Prompt for set up
        $this->CreateConfigValues();
        if(is_admin())
            PCMW_Abstraction::Get()->AddUserMSG( 'Looks like you haven\'t configured your PC Megaworks yet. Please take a minute to configure it. ['.__LINE__.']',2);
        return FALSE;
      }
    }
    else{
      if(!is_object($this->objConfig)){
        $this->objConfig = new PCMW_Config();
      $this->objConfig->UnSerializeConfig();    
        return TRUE;
      }
      return TRUE;
    }
    return FALE;
  }

  /**
  * send failed install data
  * @param $strFailureData
  * @return string results
  */
  function SendFailedInstallData($arrFailureData){
   $arrRequest = array('purpose'=>'sendfailuredata',
                       'faildata'=>json_encode($arrFailureData),
                       'userkey'=>PCMW_ConfigCore::Get()->objConfig->GetUserKey(),
                       'address'=>get_site_url().'/');
   if($arrPayLoad = PCMW_HostRequest::Get()->MakeHostRequest($arrRequest))
    return $arrPayLoad['payload'];
   return FALSE;
  }
}//end class PCMW_ConfigCore
?>