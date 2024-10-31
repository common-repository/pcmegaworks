<?php
/**************************************************************************
* @CLASS PCPluginCore
* @brief This is the core functionality of the plugin. All interface items belong here
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_PluginCoreHelper.php
*  -PCMW_Utility.php
*  -PCMW_FormManager.php
*  -PCMW_BasicChat.php
*  -PCAdminPages.php
*  -PCMW_Abstraction.php
*  -PCMW_CustomMenu.php
*  -PCMW_StaticArrays.php
*  -PCPluginInstall.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_PluginCoreHelper.php');
class PCPluginCore extends PCMW_PluginCoreHelper{
   var $arrPOST = null;
   //set the manager loader
   var $boolWordPressLoaded = FALSE;
   //hold the current user
   var $objCurrentUser;
   //js loaded?
   var $boolJSLoaded = FALSE;

   public static function Get(){
        //==== instantiate or retrieve singleton====
        static $inst = NULL;
        if( $inst == NULL )
            $inst = new PCPluginCore();
        return( $inst );
  }

  function __construct(){

  }


  /**
  * since we have a bevy of initial hook wordpress loves to use, we need to
  * initiate and execute first run actions now. functions.php CANNOT be trusted.
  */
  function PCMW_SpecialHookRegister(){
    //============ Add Actions ===============//
    add_action('init', array(&$this,'PCMW_InitiateUserLoad'), 2);
    //redirect a user after login
    add_action( 'template_redirect', array(&$this,'PCMW_ControlAcccess') );
    add_action('login_head', array(&$this,'PCMW_ChangeLoginLogo'));
    add_action('login_form', array(&$this,'PCMW_ChangeLoginPage'));
    //admin menu options
    add_action( 'admin_menu', array(&$this,'PCMW_MakeAdminMenuOption'),1 );
    add_action( 'user_admin_menu', array(&$this,'PCMW_MakeAdminMenuOption'),1 );
    //add_action( 'admin_init', array(&$this,'PCMW_RestrictAdminArea'), 1 );
    //add_action('wp_before_admin_bar_render', array(&$this,'PCMW_HideAdminMenu'),1);
    add_action( 'admin_init', array(&$this,'PCMW_MakeUserPageLimit'),99);
    add_action( 'save_post', array(&$this,'PCMW_SavePostAdminData') );
    add_action( 'save_page', array(&$this,'PCMW_SavePostAdminData') );
    add_action( 'show_user_profile', array(&$this,'PCMW_AddProfileFields') );
    add_action( 'edit_user_profile', array(&$this,'PCMW_AddProfileFields') );
    add_action( 'personal_options_update', array(&$this,'PCMW_SaveProfileFields') );
    add_action( 'edit_user_profile_update', array(&$this,'PCMW_SaveProfileFields') );
    add_action( 'after_setup_theme', array(&$this,'PCMW_MakeLoginRequests'),99 );
    add_action( 'get_footer', array(&$this,'LoadAvailableChat'),999 );
    add_action( 'wp_enqueue_scripts', array(&$this,'EnqueueScripts'),99 );  //
    add_action( 'admin_enqueue_scripts', array(&$this,'EnqueueAdminScripts'),99 );  //
    add_action( 'wp_logout', array(&$this,'PCMW_ClearCredentials'));  //
    //=============== Add Filters ====================//
    //make nav
    add_filter( 'wp_nav_menu_items', array(&$this,'PCMW_MakeCustomMenu'), 10, 2 );

    //all done
    return TRUE;
  }

  /**
  * enqueue our styles and scripts
  * @return bool
  */
  function EnqueueScripts(){
    //enque styles
    wp_enqueue_style( 'pcmw_bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
    wp_enqueue_style( 'pcmw_font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css',array(),'4.6.3');
    wp_enqueue_style( 'pcmw_style', plugin_dir_url( __FILE__ ).'../assets/css/style.css');
    wp_enqueue_style( 'pcmw_jqueryui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css',array(),'1.12.1');
    wp_enqueue_style( 'pcmw_checkbox', plugin_dir_url( __FILE__ ).'../assets/css/bootstrap-checkbox.css');
    wp_enqueue_style( 'pcmw_boostrapcheckbox', plugin_dir_url( __FILE__ ).'../assets/css/desktop_bs_checkbox.css');

    //enque JS
    wp_enqueue_script('pcmw_jquery_min','//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js',array(),'3.1.1');
    wp_enqueue_script('pcmw_jquery_ui_min','//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',array(),'1.12.1');
    wp_enqueue_script('pcmw_boostrap','//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',array(),'3.3.7');
    wp_enqueue_script('pcmw_jsapi','//www.google.com/jsapi',array(),'');
    wp_enqueue_script('pcmw_checkbox_min',plugin_dir_url( __FILE__ ).'../assets/js/bootstrap-checkbox.min.js',array(),'');
    wp_enqueue_script('pcmw_pcplugin',plugin_dir_url( __FILE__ ).'../assets/js/PCPlugin.js',array(),'');
    wp_enqueue_script('pcmw_ajaxcore',plugin_dir_url( __FILE__ ).'../assets/js/AjaxCore.js',array(),'');
    return TRUE;
  }


  /**
  * enqueue our styles and scripts
  * @return bool
  */
  function EnqueueAdminScripts(){
    wp_enqueue_style( 'pcmw_datatables', '//cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css',array(),'1.10.13');
    wp_enqueue_style( 'pcmw_bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
    wp_enqueue_style( 'pcmw_font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css',array(),'4.6.3');
    wp_enqueue_style( 'pcmw_style', plugin_dir_url( __FILE__ ).'../assets/css/style.css');

    wp_enqueue_script('pcmw_jquery_min','//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js',array(),'3.1.1');
    wp_enqueue_script('pcmw_jquery_ui_min','//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',array(),'1.12.1');
    wp_enqueue_script('pcmw_boostrap_min','//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',array(),'3.3.7');
    wp_enqueue_script('pcmw_datatables_min','//cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js',array(),'1.10.13');
    wp_enqueue_script('pcmw_boostrap_checkbox',plugin_dir_url( __FILE__ ).'../assets/js/bootstrap-checkbox.min.js',array(),'');
    wp_enqueue_script('pcmw_pcplugin',plugin_dir_url( __FILE__ ).'../assets/js/PCPlugin.js',array(),'');
    wp_enqueue_script('pcmw_ajaxcore',plugin_dir_url( __FILE__ ).'../assets/js/AjaxCore.js',array(),'');
    return TRUE;
  }

  /**
  * load our logged in user
  * @return bool
  */
  function PCMW_InitiateUserLoad(){
    if(session_id() == '' || !isset($_SESSION))
        // We need session here
        session_start();
    PCMW_ConfigCore::Get()->LoadConfigFromStorage();
    //get our logged in user
    $this->VerifyUserLoginData();
    //if we're not active, return to cease operation
    if( (int)PCMW_ConfigCore::Get()->objConfig->GetPluginActive() < 1)
        return TRUE;

    //$pagenow will return index.php, when we need the specific page
    $strScriptSelf = PCMW_Utility::Get()->GetScriptSelf($_SERVER['REQUEST_URI']);
    $this->Handle404Redirection($strScriptSelf);
    //handle registration redirection
    $this->HandleRegistrationRouting($strScriptSelf);
    //handle login redirection
    $this->HandleLoginRouting($strScriptSelf);
  }

  /**
  * check to see if there is a chat interface to load
  * @return bool
  */
  function LoadAvailableChat(){
    if(is_admin())
        return TRUE;
    //make our chat happen
    if($strChat = PCMW_BasicChat::Get()->CreateChatDisplay())
        echo $strChat;
    return TRUE;
  }

  /**
  * make login requests
  * @return bool
  */
  function PCMW_MakeLoginRequests(){
    //see if any login feature needs saving
    PCMW_FormManager::Get()->HandleCustomFormActions();
    //in case of login redirect
    $this->HandleLoginRedirects();
    return TRUE;
  }
  #ENDREGION
  /**
  * on template load, determine if the logged in user has rights to see this page
  * @return exit; || bool
  */
  function PCMW_ControlAcccess(){
     //are we checking for control access?
     if((int)PCMW_ConfigCore::Get()->objConfig->GetRestrictPages() > 0){
       $intAccessLevel = get_post_meta( get_the_ID(), 'adminlimit', true);
       if((int)$intAccessLevel > 1){
        //we're checking for access here
        unset($_SESSION['previouspage']);
        //redirect will happen if no privilege exists
        PCMW_Abstraction::Get()->CheckPrivileges(PCMW_SUSPENDED,$intAccessLevel,FALSE);
       }
     }
     return TRUE;
  }
  /**
  * create a simple google map call
  * @param $strAddress
  * @return string
  */
  function PCMW_MakeAdminMenuOption(){
    //load our Admin menu option
    add_menu_page( 'PC Plugin1',
                   'PC Megaworks',
                   'manage_options',
                   'PCPluginAdmin',
                   array(&$this, 'PCMW_MakeAdminOptions'),
                   'http://progressivecoding.net/favicon.ico',
                   20 );
    return TRUE;
  }

  /**
  * add the options for our admin menu
  * @return bool
  */
  function PCMW_MakeAdminOptions(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $this->PCMW_CheckForInitialSetup();
    //sanitize first
    $arrGET = filter_var_array($_GET,FILTER_SANITIZE_STRING);
    $strFeatures = get_option('PCMW_features');
    $arrFeatures = json_decode($strFeatures,TRUE);
    if(array_key_exists('pcmw_option',$arrGET) && trim($arrGET['pcmw_option']) != '')
        return $this->ManagePCMW($arrGET['pcmw_option'],$arrFeatures);
    $arrHeaders = PCAdminPages::Get()->GetDefaultFeatureHeaders();
    $strDiv = PCAdminPages::Get()->IncludeHTMLHeader();
    $strDiv .= '<div class="wrap col-md-12">';
    $strDiv .= '<h1>PC Megaworks options</h1>';
    $strDiv .= '<hr />';
	$intRowMax = 4;
	$intColumnCount = 0;
    //let's verify we have an admin user
    if(PCPluginInstall::Get()->VerifyPluginAdmin()){
      foreach($arrHeaders as $strLink=>$arrPageData){
        if(!PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,$arrPageData['group'],FALSE,FALSE)){
          continue 1;
        }
        //make an indicator or not to show if configuration is require
        $strWarning = '';
        $strBackGround = '';
        if(array_key_exists('wpoptionname',$arrPageData) && trim($arrPageData['wpoptionname']) != ''){
          if(!($strOptions = get_option($arrPageData['wpoptionname']))){
            $strBackGround = ' bg-warning ';
            $strWarning = '&nbsp;&nbsp;<i class="fa fa-exclamation-circle fa-1x pointer'.$strBackGround.'" title="Configuration required before it can be used." data-toggle="tooltip" tooltip="Configuration required before it can be used."></i>';
          }
          else{
            $strBackGround = ' bg-green-rounded ';
            $strWarning = '&nbsp;&nbsp;<i class="fa fa-check-circle fa-1x pointer'.$strBackGround.'" title="Configuration complete." data-toggle="tooltip" tooltip="Configuration complete."></i>';
          }
        }
    	if($intColumnCount == 0){
          $strDiv .= '<div class="row">';
        }
        if(@(int)$arrPageData['feature'] < 1 || (@(int)$arrPageData['feature'] > 0 && @array_key_exists($strLink,$arrFeatures) && @(int)$arrFeatures[$strLink] > 0)){
          $strDiv .= '<div class="form-group col-md-3 floatleft align-bottom" style="text-align: center;" >';
          $strDiv .= '<a href="/wp-admin/admin.php?page=PCPluginAdmin&pcmw_option='.$strLink.'" border="0" style="outline : none;" >';
          $strDiv .= '<i class="fa fa-5x '.$arrPageData['icon'].' fa-fw"></i>';
          $strDiv .= '<br />';
          $strDiv .= '<b>'.$arrPageData['title'].'</b>';
          $strDiv .= '<br />';
          $strDiv .= '<hr />';
          $strDiv .= $arrPageData['description'].$strWarning;
          $strDiv .= '</a>';
          $strDiv .= '<br />';
          $strDiv .= '<br />';
          if((int)$arrPageData['feature'] > 0)
              $strDiv .= '<span class="align-baseline" style=""><input type="button" class="btn btn-danger" value="Un-Install '.$arrPageData['title'].'" onclick="UnInstallFeature(\''.$strLink.'\');" /></span>';
          $strDiv .= '</div>';
        }
        else{//we do not have this installed yet
          $strDiv .= '<div class="form-group col-md-3 floatleft" style="text-align: center;" >';
          $strDiv .= '<a border="0" style="outline : none;">';
          $strDiv .= '<i class="fa fa-5x '.$arrPageData['icon'].' fa-fw"></i>';
          $strDiv .= '<br />';
          $strDiv .= '<b>'.$arrPageData['title'].'</b>';
          $strDiv .= '<br />';
          $strDiv .= '<hr />';
          $strDiv .= $arrPageData['description'];
          $strDiv .= '<br />';
          $strDiv .= '<br />';
          $strDiv .= $this->CreateInstallButton($arrPageData['title'],$strLink);;
          $strDiv .= '</a>';
          $strDiv .= '</div>';
        }
  	  //increment our columns
  	  $intColumnCount++;
  	  if($intColumnCount == $intRowMax){
        	$strDiv .= '</div>';
  		$intColumnCount = 0;
  	  }
      }
    }
    else{
    PCMW_Abstraction::Get()->AddUserMSG( '<h3>Errors were detected and we cannot show you the options at this time.</h3>',2);
    }
    $strDiv .= PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
    $strDiv .= '</div><!-- /.row -->';
    $strDiv .= '</div><!-- /.wrap -->';
    $strDiv .= PCAdminPages::Get()->IncludeHTMLFooter();
    echo $strDiv;
    return TRUE;
  }

  /**
  * verify settings have been set before use
  * @return bool
  */
  function PCMW_CheckForInitialSetup(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if(!PCMW_ConfigCore::Get()->LoadConfigFromStorage()){
        //wp_safe_redirect('wp-admin/admin.php?page=pc-settings');
        $strRedirect = 'wp-admin/admin.php?page=pc-settings';
        PCMW_Abstraction::Get()->AddUserMSG( 'Please configure PC Mega Works before trying to use it. Some features need to be turned on or off in order to properly handle the requests. ['.__LINE__.']',2);
        PCMW_Abstraction::Get()->RedirectUser($strRedirect);
        exit;
    }
    return TRUE;
  }

  /**
  * load our admin options
  * @param $strOption - page option to load
  * @param $arrFeatures- installed features
  * @return bool
  */
  function ManagePCMW($strOption,$arrFeatures){
    $this->PCMW_CheckForInitialSetup();
    $strDiv = PCAdminPages::Get()->IncludeHTMLHeader();
    $boolIsAdmin = PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_ADMINISTRATOR,FALSE,FALSE);
    $boolIsModerator = PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_MODERATOR,FALSE,FALSE);
    $boolIsPremiumUser = PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_PREMIUMUSER,FALSE,FALSE);
    echo $this->MakeOptionList($strOption,$arrFeatures);
    switch($strOption){
       case 'pcmw-settings':
          if($boolIsAdmin)
            $this->PCMW_MakePCConfig();
       break;
       case 'manage-pcmw-maps':
          if($boolIsModerator){
            if(array_key_exists('manage-pcmw-maps',$arrFeatures) && (int)$arrFeatures['manage-pcmw-maps'] > 0)
                $this->PCMW_MakeMapManager();
            else//create a button for installing this feature
                echo '<br />'.$this->CreateInstallButton('Map Options','manage-pcmw-maps');
                echo '<br />'.PCMW_Utility::Get()->BackToManageOptions();
          }
       break;
       case 'manage-pcmw-forms':
          if($boolIsModerator)
            $this->PCMW_MakeFormManager();
       break;
       case 'pcmw-mail-blast':
          if($boolIsModerator && array_key_exists('pcmw-mail-blast',$arrFeatures) && (int)$arrFeatures['pcmw-mail-blast'] > 0){
            if(array_key_exists('pcmw-mail-blast',$arrFeatures) && (int)$arrFeatures['pcmw-mail-blast'] > 0)
                $this->PCMW_MakeMailBlastForm();
            else//create a button for installing this feature
                echo '<br />'.$this->CreateInstallButton('Mial Blast','pcmw-mail-blast');
                echo '<br />'.PCMW_Utility::Get()->BackToManageOptions();
          }
       break;
       case 'manage-pcmw-404-redirects':
          if($boolIsModerator && array_key_exists('manage-pcmw-404-redirects',$arrFeatures) && (int)$arrFeatures['manage-pcmw-404-redirects'] > 0){
            if(array_key_exists('manage-pcmw-404-redirects',$arrFeatures) && (int)$arrFeatures['manage-pcmw-404-redirects'] > 0)
                $this->PCMW_Make404Form();
            else{//create a button for installing this feature
                echo '<br />'.$this->CreateInstallButton('404 Redirects','manage-pcmw-404-redirects');
                echo '<br />'.PCMW_Utility::Get()->BackToManageOptions();
            }
          }
       break;
       case 'manage-pcmw-admin-users':
          if($boolIsAdmin)
            $this->PCMW_ManageAdminUsers();
       break;
       case 'pcmw-helpdesk':
          if($boolIsPremiumUser)
            $this->PCMW_MakeHelpDeskPage();
       break;
       case 'pcmw-feature-request':
          if($boolIsPremiumUser)
            $this->PCMW_MakeFeatureRequestPage();
       break;
       case 'pcmw-check-links':
          if($boolIsPremiumUser)
            $this->CrawlSitePages();
       break;
       case 'pcmw-registration-options':
          if($boolIsPremiumUser)
            $this->PCMW_MakeRegistrationText();
       break;
       case 'pcmw-check-images':
          if($boolIsPremiumUser)
            $this->CrawlSiteImages();
       break;
       case 'pcmw-howtos':
            $this->MakeHowToForm();
       break;
       case 'video-access':
            $this->PCMW_ManageVideos();
       break;
       case 'basicchat':
          if($boolIsPremiumUser && array_key_exists('basicchat',$arrFeatures) && (int)$arrFeatures['basicchat'] > 0)
            $this->PCMW_MakeChatOptions();
       break;
       default:
          return FALSE;
    }
    //nothing to do here
    return TRUE;
  }

  /**
  * make the sidebar options when drilled down
  * @param $strSelected
  * @param $arrFeatures
  * @return string HTML
  */
  function MakeOptionList($strSelected,$arrFeatures){
    $arrHeaders = PCAdminPages::Get()->GetDefaultFeatureHeaders();
    $strSideBar = '<div class="btn-group closed">';
    $strSideBar .= '<a class="btn btn-primary" href="#" style="outline : none;">';
    $strSideBar .= '<i class="fa fa-user fa-fw"></i>';
    $strSideBar .= 'PCMegaworks Options</a>';
    $strSideBar .= '<a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#" style="outline : none;">';
    $strSideBar .= '<span class="fa fa-caret-down" title="Toggle dropdown menu"></span>';
    $strSideBar .= '</a>';
    $strSideBar .= '<ul class="dropdown-menu">';
    foreach($arrHeaders as $strLink=>$arrPageData){
      if(!PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,$arrPageData['group'],FALSE,FALSE))
        continue 1;
      //make an indicator or not to show if configuration is require
      $strWarning = '';
      $strBackGround = '';
      if(array_key_exists('wpoptionname',$arrPageData) && trim($arrPageData['wpoptionname']) != ''){
        if(!($strOptions = get_option($arrPageData['wpoptionname']))){
          $strBackGround = ' bg-warning ';
          $strWarning = '&nbsp;&nbsp;<i class="fa fa-exclamation-circle fa-1x pointer'.$strBackGround.'" title="Configuration required before it can be used." data-toggle="tooltip" tooltip="Configuration required before it can be used."></i>';
        }
        else{
          $strBackGround = ' bg-green-rounded ';
          $strWarning = '&nbsp;&nbsp;<i class="fa fa-check-circle fa-1x pointer'.$strBackGround.'" title="Configuration complete." data-toggle="tooltip" tooltip="Configuration complete."></i>';
        }
      }
      if(@(int)$arrPageData['feature'] < 1 || (@(int)$arrPageData['feature'] > 0 && @array_key_exists($strLink,$arrFeatures) && @(int)$arrFeatures[$strLink] > 0)){
        $strSideBar .= '<li>';
        $strSideBar .= '<a href="/wp-admin/admin.php?page=PCPluginAdmin&pcmw_option='.$strLink.'" border="0">';
        $strSideBar .= '&nbsp;&nbsp;<i class="fa '.$arrPageData['icon'].' fa-fw'.$strBackGround.'"></i>&nbsp;&nbsp;';
        $strSideBar .= $arrPageData['title'];
        if((int)$arrPageData['feature'] > 0)
            $strSideBar .= '&nbsp;&nbsp;<i class="fa fa-unlink fa-1x bg-red-rounded pointer" data-toggle="tooltip" title="Un-Install '.$arrPageData['title'].'" tooltip="Un-Install '.$arrPageData['title'].'" onclick="UnInstallFeature(\''.$strLink.'\');" ></i>'.$strWarning;
        $strSideBar .= '</a>';
        $strSideBar .= '</li>';
      }
      else{
        $strSideBar .= '<li>';
        $strSideBar .= '<a href="#" border="0" style="outline : none;">';
        $strSideBar .= '&nbsp;&nbsp;<i class="fa '.$arrPageData['icon'].' fa-fw"></i>&nbsp;&nbsp;';
        $strSideBar .= $arrPageData['title'];
        $strSideBar .= '&nbsp;&nbsp;<i class="fa fa-link fa-1x bg-green-rounded pointer" data-toggle="tooltip" title="Install '.$arrPageData['title'].'" tooltip="Install '.$arrPageData['title'].'" onclick="InstallFeature(\''.$strLink.'\');" ></i>';//arrow-alt-circle-right
        $strSideBar .= '</a>';
        $strSideBar .= '</li>';
      }
    }
    $strSideBar .= '</ul>';
    $strSideBar .= '</div>';
    return $strSideBar;
  }

  /**
  * @brief: Make the HTML for the map manager
  * echo string ( HTML )
  * @return bool
  */
  function PCMW_MakeMapManager(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $this->PCMW_CheckForInitialSetup();
    if(!class_exists('PCMW_VendorCore'))
      require_once('PCMW_VendorCore.php');
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->MakeMapGroupManager();
    $strDiv .= PCAdminPages::Get()->MakeMapManager();
    $strDiv .= '</div>';
    echo $strDiv;
    return TRUE;
  }

  /**
  * @brief: Make the HTML for the map group manager
  * echo string ( HTML )
  * @return bool
  */
  function PCMW_MakeMapGroupManager(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $this->PCMW_CheckForInitialSetup();
    if(!class_exists('PCMW_VendorCore'))
      require_once('PCMW_VendorCore.php');
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->MakeMapGroupManager();
    $strDiv .= '</div>';
    echo $strDiv;
    return TRUE;
  }

  /**
  * @brief: Make the HTML for chat options
  * echo string ( HTML )
  * @return bool
  */
  function PCMW_MakeChatOptions(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if(!class_exists('PCMW_BasicChat'))
      require_once('PCMW_BasicChat.php');
    $this->PCMW_CheckForInitialSetup();
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->MakeChatOptions();
    $strDiv .= '</div>';
    echo $strDiv;
    return TRUE;
  }

  /**
  * @brief: Make the mail blast form
  * echo string ( HTML )
  * @return bool
  */
  function PCMW_MakeMailBlastForm(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $this->PCMW_CheckForInitialSetup();
    if(!class_exists('PCMW_MailBlast'))
      require_once('PCMW_MailBlast.php');
    //sanitize
    $arrPOST = filter_var_array($_POST,FILTER_SANITIZE_STRING);
    PCMW_MailBlast::Get()->ProcessMailBlast($arrPOST);
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->MakeMailBlastForm($arrPOST);
    $strDiv .= '</div>';
    echo $strDiv;
    return TRUE;
  }


  /**
  * @brief: Add the registration text options
  * echo string ( HTML )
  * @return bool
  */
  function PCMW_MakeRegistrationText(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $this->PCMW_CheckForInitialSetup();
    if(!class_exists('PCMW_Register'))
      require_once('PCMW_Register.php');
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->MakeRegistrationText();
    $strDiv .= '</div>';
    echo $strDiv;
    return TRUE;
  }

  /**
  * @brief: Make the form manager HTML
  * echo string ( HTML )
  * @return bool
  */
  function PCMW_MakeFormManager(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $this->PCMW_CheckForInitialSetup();
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->MakeFormManager();
    $strDiv .= '</div>';
    echo $strDiv;
    return TRUE;
  }

  /**
  * manage our admin groups
  * @return bool
  */
  function PCMW_ManageAdminUsers(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $this->PCMW_CheckForInitialSetup();
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->MakeAdminGroupManager();
    $strDiv .= '</div>';
    echo $strDiv;
    return TRUE;
  }

  /**
  * manage our video table
  * @return bool
  */
  function PCMW_ManageVideos(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $this->PCMW_CheckForInitialSetup();
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->ManageVideos();
    $strDiv .= '</div>';
    echo $strDiv;
    return TRUE;
  }
  /**
  * manage our CSS Options
  * @return bool
  */
  function PCMW_CSSOptions(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $this->PCMW_CheckForInitialSetup();
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->ManageCSSInterface();
    $strDiv .= '</div>';
    echo $strDiv;
    return TRUE;
  }

  /**
  * make the PC config form
  * @return bool
  */
  function PCMW_MakePCConfig(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $arrPOST = filter_var_array($_POST,FILTER_SANITIZE_STRING);
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->MakePCConfig($arrPOST);
    $strDiv .= '</div>';
    echo $strDiv;
    return TRUE;
  }

  /**
  * create a custom menu
  * @return HTML
  */
  function PCMW_MakeCustomMenu($strMenuItems,$objNavObject=null){
   if($strCustomMenuItems = PCMW_CustomMenu::Get()->MakeCustomMenu())
    return $strCustomMenuItems;
   return $strMenuItems;
  }

  /**
  * given a menu, adjust it for login and registration options
  * @param $strMenuItems
  * @param $objNavObject
  * @return object
  */
  function PCMW_AddLogInRegisterLink($strMenuItems,$objNavObject){
    if (is_user_logged_in() &&
        PCMW_ConfigCore::Get()->objConfig->GetUseCustomLogin() > 0 &&
        (is_null($objNavObject) ||
         @$objNavObject->theme_location == 'primary' ||
         @$objNavObject->theme_location == '')) {//
        $strMenuItems .= '<li class="pcmt_li"><a href="'. site_url('logout/') .'" class="nav-top-link pcmt_a">Log Out</a></li>';
        if(PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_ADMINISTRATOR,FALSE,FALSE))
            $strMenuItems .= '<li class="pcmt_li"><a href="'. site_url('wp-admin/') .'" class="nav-top-link pcmt_a">Admin</a></li>';
        else
            $strMenuItems .= '<li class="pcmt_li"><a href="'. site_url('wp-admin/profile.php') .'" class="nav-top-link pcmt_a">My Account</a></li>';
    }
    else if (!is_user_logged_in() &&
            (is_null($objNavObject) ||
             @$objNavObject->theme_location == 'primary' ||
             @$objNavObject->theme_location == '')){//
        if(PCMW_ConfigCore::Get()->objConfig->GetUseCustomRegistration() > 0)
            $strMenuItems .= '<li class="pcmt_li"><a href="'. site_url(PCMW_ConfigCore::Get()->objConfig->GetRegistrationPage()) .'" class="nav-top-link pcmt_a">Register</a></li>';
        if(PCMW_ConfigCore::Get()->objConfig->GetUseCustomLogin() > 0)
            $strMenuItems .= '<li class="pcmt_li"><a href="'. site_url(PCMW_ConfigCore::Get()->objConfig->GetLoginPage()) .'" class="nav-top-link pcmt_a">Login</a></li>';
    }
    else{
      //do nothing
    }
    if((int)PCMW_ConfigCore::Get()->objConfig->GetUseContactUs() > 0){
      $strMenuItems .= '<li class="pcmt_li"><a href="'. site_url('contact-us') .'" class="nav-top-link pcmt_a">Contact Us</a></li>';
    }
    if((int)PCMW_ConfigCore::Get()->objConfig->GetUseCustomHAWD() > 0){
      $strMenuItems .= '<li class="pcmt_li"><a href="'. site_url('how-are-we-doing') .'" class="nav-top-link pcmt_a">How Are We Doing</a></li>';
    }

    return $strMenuItems;
  }

  /**
  * make the 404 redirect form
  * return string
  */
  function PCMW_Make404Form(){
   if ( !current_user_can( 'manage_options' ) )  {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
   }
   $this->PCMW_CheckForInitialSetup();
   $strDiv = '<div class="wrap">';
   $strDiv .= PCAdminPages::Get()->Make404RedirectOptions();
   $strDiv .= '</div>';
   echo $strDiv;
  }

  /**
  * make the helpdesk form
  * @return bool
  */
  function PCMW_MakeHelpDeskPage(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $this->PCMW_CheckForInitialSetup();
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->IncludeHTMLHeader();
    $strDiv .= PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
    $strDiv .= '<a class="btn btn-primary" onclick="GetFormByAlias(\'reportabug\',1,0,0);" /><i class="fa fa-bug"></i>&nbsp;PC Help Desk</a>';
    $strDiv .= '<h3>You can alternatively contact us through our web form at our <a href="https://www.progressivecoding.net" target="_blank">PC home page</a></h3>';
    $strDiv .= '<h3>Or email us directly <a href="mailto:'.PCMW_SUPPORT.'">'.PCMW_SUPPORT.'</a></h3>';
    $strDiv .= PCAdminPages::Get()->IncludeHTMLFooter();
    $strDiv .= '</div>';
    echo $strDiv;
  }

  /**
  * make the feature request form
  * @return bool
  */
  function PCMW_MakeFeatureRequestPage(){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $this->PCMW_CheckForInitialSetup();
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->IncludeHTMLHeader();
    $strDiv .= PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
    $strDiv .= '<a class="btn btn-primary" onclick="GetFormByAlias(\'addfeature\',1,0);" ><li class="fa fa-road"></li>&nbsp;Request a feature</a>';
    $strDiv .= '<h3>You can alternatively contact us through our web form at our <a href="https://www.progressivecoding.net" target="_blank">PC home page</a></h3>';
    $strDiv .= '<h3>Or email us directly <a href="mailto:'.PCMW_SUPPORT.'">'.PCMW_SUPPORT.'</a></h3>';
    $strDiv .= PCAdminPages::Get()->IncludeHTMLFooter();
    $strDiv .= '</div>';
    echo $strDiv;
  }

  #ENDREGION

  #REGION ACCESS CONTROL METHODS

  /**
  * clear out any session data
  * @return bool
  */
  function PCMW_ClearCredentials(){
  if(session_id() == '' || !isset($_SESSION))
      // We need session here
      session_start();
   unset($_SESSION['CURRENTUSER']);
   unset($_SESSION['PC_Redirects']);
   unset($_SESSION['PC_noRedirects']);
   unset($_SESSION['previouspage']);
   unset($_SESSION['pc_chatsession']);
   unset($_SESSION['pc_chatid']);
   //clear this last to avoid perpetual redirects
   unset($_SESSION['pcconfig']);
   unset($_SESSION['pcsessionconfig']);
   if(is_user_logged_in()){
    wp_logout();
    //wp_safe_redirect(get_home_url().PCMW_ConfigCore::Get()->objConfig->GetLoginPage());
    exit;
   }
   //return TRUE;
  }

  /**
  * hide the admin menu?
  * should be using a constant from settings
  */
  function PCMW_HideAdminMenu(){
    if(!PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_ADMINISTRATOR))
       show_admin_bar( FALSE );
    else
        return TRUE;
    return FALSE;
  }

  /**
  * restrict non-admins from accessing the admin area
  * @should be using a constant from settings
  */
  function PCMW_RestrictAdminArea(){
    return TRUE;
    if( !PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_ADMINISTRATOR)  && !( defined( 'DOING_AJAX' ) && DOING_AJAX )){
       wp_redirect( home_url() );
       exit;
    }
  }



  function PCMW_AllowEmailLogin( $user, $username, $password ) {
      // If an email address is entered in the username box,
      // then look up the matching username and authenticate as per normal, using that.
      if ( ! empty( $username ) ) {
          //if the username doesn't contain a @ set username to blank string
          //causes authenticate to fail
          if(strpos($username, '@') == FALSE){
            $user = get_user_by( 'login', $username );
          }
          else
            $user = get_user_by( 'email', $username );
      }
      if ( isset( $user->user_login, $user ) )
          $username = $user->user_login;

      // using the username found when looking up via email
      return wp_authenticate_username_password( NULL, $username, $password );
  }

  // custom admin login logo
  function PCMW_ChangeLoginLogo() {
    if(trim(PCMW_ConfigCore::Get()->objConfig->GetLogo()) != '')
      echo '<style type="text/css">
      .login h1 a {background-image: url('.PCMW_ConfigCore::Get()->objConfig->GetLogo().') !important;  }
      </style>';
  }

  /**
  * @brief: create an HTML button for a specific module installation
  * @param $strFeatureTitle - title of the feature to install
  * @param $strFeatureName - name of the feature to install
  * @return string ( HTML )
  */
  function CreateInstallButton($strFeatureTitle,$strFeatureName){
   $strButton .= '<span class="align-baseline"><input type="button" class="btn btn-success" value="Install '.$strFeatureTitle.'" onclick="InstallFeature(\''.$strFeatureName.'\');" /></span>';
   return $strButton;
  }


  /*
  change the login URL
  */
  function PCMW_ChangeLoginPage( $strURL ){
   if(PCMW_ConfigCore::Get()->objConfig->GetUseCustomLogin() > 0){
      PCMW_Abstraction::Get()->RedirectUser(PCMW_ConfigCore::Get()->objConfig->GetLoginPage());
   }
   return $strURL;
  }

  /**
  * make the wordpress login page
  */
  function PCMW_MakeLoginForm(){
    $arrPOST = filter_var_array($_POST,FILTER_SANITIZE_STRING);
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->MakeCustomLoginForm($arrPOST);
    $strDiv .= '</div>';
    echo $strDiv;
    return $strDiv;
  }


  /**
  * make the wordpress login page
  */
  function MakeHowToForm(){
    $strDiv = '<div class="wrap">';
    $strDiv .= PCAdminPages::Get()->MakeHowToForm();
    $strDiv .= '</div>';
    echo $strDiv;
    return $strDiv;
  }

  /**
 * custom option and settings
 */
function PCMW_MakeUserPageLimit(){
  if((int)PCMW_ConfigCore::Get()->objConfig->GetRestrictPages() < 1)
    return FALSE;
    //first the post
  add_meta_box( 'pcplugin',
              __( 'Limit user access', 'pcplugin' ),
              array( &$this, 'PCMW_MakePostOptions' ),
              'post', 'normal', 'high'
              );
  //now for pages
  add_meta_box( 'pcplugin',
              __( 'Limit user access', 'pcplugin' ),
              array( &$this, 'PCMW_MakePostOptions' ),
              'page', 'normal', 'high'
              );
  return TRUE;
}

/**
* make the POST settings
* @param none
* @return HTML
*/
function PCMW_MakePostOptions($objPOST){
 wp_nonce_field( plugin_basename( __FILE__ ), $objPOST->post_type . '_noncename' );
 $arrMetaInfo = array('adminlimit'=>get_post_meta( $objPOST->ID, 'adminlimit', true));
 $arrAccessLevels = PCMW_StaticArrays::Get()->LoadStaticArrayType('accesslevels',FALSE,0,TRUE);
 //add everyone
 $arrAccessLevels[1] = 'Public page';
 $strSelect = '<div style="text-align:left;">';
 $strSelect .= 'User group who can see this page <br />';
 $strSelect .= '<h6 style="color:#FF2424;">The limit restricts users below the given limit.</h6>';
 $strSelect .= PCAdminPages::Get()->IncludeHTMLHeader();
 $strSelect .= PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
 //make our attributes array
 $arrMetaInfo['selectname'] = 'adminlimit';
 //make our select box
 $strSelect .= PCMW_Utility::Get()->MakeSimpleSelectBox($arrAccessLevels,$arrMetaInfo,$arrMetaInfo);
 echo $strSelect.'</div>';
 return TRUE;
}

/**
 * When the post is saved, saves our custom data
 */
function PCMW_SavePostAdminData( $intPostId ) {
  if(!is_user_logged_in() || !$this->is_edit_page())
    return TRUE;
  // verify if this is an auto save routine.
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
      return TRUE;
  $arrPOST = filter_var_array($_POST,FILTER_SANITIZE_STRING);
  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  if ( !wp_verify_nonce( @$arrPOST[$arrPOST['post_type'] . '_noncename'], plugin_basename( __FILE__ ) ) ){
      PCMW_Abstraction::Get()->AddUserMSG( 'Settings NOT saved! ['.__LINE__.']',1);
      return TRUE;
  }
  // Check permissions
  if ( !current_user_can( 'edit_post', $intPostId ) ){
     PCMW_Abstraction::Get()->AddUserMSG( 'Settings NOT saved! ['.__LINE__.']',1);
     return TRUE;
  }
  // OK, we're authenticated: we need to find and save the data
  if( 'post' == $arrPOST['post_type'] ) {
      if ( !current_user_can( 'edit_post', $intPostId ) ) {
          PCMW_Abstraction::Get()->AddUserMSG( 'Settings NOT saved! ['.__LINE__.']',1);
          return TRUE;
      }
      else {
          if(update_post_meta( $intPostId, 'adminlimit', $arrPOST['adminlimit'] ))
            PCMW_Abstraction::Get()->AddUserMSG( 'Settings saved! ['.__LINE__.']',3);
          else
            PCMW_Abstraction::Get()->AddUserMSG( 'Settings NOT saved! ['.__LINE__.']',1);
        return TRUE;
      }
  }
  if( 'page' == $arrPOST['post_type'] ) {
      if ( !current_user_can( 'edit_page', $intPostId ) ) {
          PCMW_Abstraction::Get()->AddUserMSG( 'Settings NOT saved! ['.__LINE__.']',1);
          return TRUE;
      }
      else {
          if(update_post_meta( $intPostId, 'adminlimit', $arrPOST['adminlimit'] ))
            PCMW_Abstraction::Get()->AddUserMSG( 'Settings saved! ['.__LINE__.']',3);
          else
            PCMW_Abstraction::Get()->AddUserMSG( 'Settings NOT saved! ['.__LINE__.']',1);
        return TRUE;
      }
  }
  return TRUE;
}

  /**
   * is_edit_page
   * function to check if the current page is a post edit page
   *
   * @author Ohad Raz <admin@bainternet.info> from stack exchange
   *
   * @param  string  $new_edit what page to check for accepts new - new post page ,edit - edit post page, null for either
   * @return boolean
   */
  function is_edit_page($new_edit = null){
      global $pagenow;
      //make sure we are on the backend
      if (!is_admin()) return false;


      if($new_edit == "edit")
          return in_array( $pagenow, array( 'post.php',  ) );
      elseif($new_edit == "new") //check for new post page
          return in_array( $pagenow, array( 'post-new.php' ) );
      else //check for either new or edit
          return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
  }

  /**
  * load the custom fields to a profile update
  * @param $user presented user
  * @return bool
  */
  function PCMW_AddProfileFields($user){
    ?>
    <h3>Extra profile information</h3>

	<table class="form-table">

		<tr>
			<th><label for="pcmw_mail_blast">Join Mail Blast</label></th>

			<td>
			    <?php
                 $arrUserMeta = get_user_meta($user->data->ID);
                 $strChecked = (@(int)$arrUserMeta['pcmw_mail_blast'] > 0)? ' CHECKED ':'' ;
                 //print_r($user);
                ?>
				<input type="checkbox" name="pcmw_mail_blast" id="pcmw_mail_blast" value="1" class="" <?php echo $strChecked; ?>/>Join future Mail Blasts!<br />
			</td>
		</tr>

	</table>
    <?php
    return TRUE;
  }

  /**
  * save the custom fields to a profile update
  * @param $user_id user id of the profile we're updating
  * @return bool
  */
  function PCMW_SaveProfileFields($user_id){
    if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
    $arrPOST = filter_var_array($_POST,FILTER_SANITIZE_STRING);
    if(array_key_exists('pcmw_mail_blast',$arrPOST) && $arrPOST['pcmw_mail_blast'] == 1)
        add_user_meta( $user_id, 'pcmw_mail_blast', '1', TRUE );
    else
        delete_user_meta( $user_id, 'pcmw_mail_blast' );
    return TRUE;
  }



  /**
  * get the crawl results of the site, and display them
  * @param $boolShowAll
  * @return bool
  */
  function CrawlSitePages($boolShowAll=TRUE){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $this->PCMW_CheckForInitialSetup();
    $arrCrawlResults = array();
    $this->CrawlPageLinks(get_site_url().'/',3,$arrCrawlResults);
    PCMW_Abstraction::Get()->AddUserMSG( 'This may take some time, depending the number of links on your site. ',2);
    $strDiv = '<div class="wrap" style="text-align:left;">';
    $strDiv .= PCAdminPages::Get()->IncludeHTMLHeader();
    $strDiv .= PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
    foreach($arrCrawlResults as $strSourceURL=>$arrLinkStatus){
      $strDiv .= '<div class="form-control input-md" style="height:100%;">';
      $strDiv .= '<h4>Page: '.$strSourceURL.'</h4>';
      foreach($arrLinkStatus as $strLink=>$intCode){
        $arrCodeResults = PCMW_Utility::Get()->GetHTTPResponse($intCode);
        $strDiv .= '<p style="font-size:17px;">Link: '.$strLink.'. <b style="background-color:'.$arrCodeResults[1].';color:#000000;">&nbsp;Status ['.$intCode.']&nbsp;'.$arrCodeResults[0].'</b></p>';
      }
      $strDiv .= '</div>';
    }
    $strDiv .= PCAdminPages::Get()->IncludeHTMLFooter();
    $strDiv .= '</div>';
    echo $strDiv;
  }


  /**
  * get the crawl results of the site, and display them
  * @param $boolShowAll
  * @return bool
  */
  function CrawlSiteImages($boolShowAll=TRUE){
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    $this->PCMW_CheckForInitialSetup();
    $arrCrawlResults = $this->CrawlPageForImages();
    PCMW_Abstraction::Get()->AddUserMSG( 'This may take some time, depending the number of images on your site. ',2);
    $strDiv = '<div class="wrap" style="text-align:left;">';
    $strDiv .= PCAdminPages::Get()->IncludeHTMLHeader();
    $strDiv .= PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
    foreach($arrCrawlResults as $strSourceURL=>$arrImageBase){
      $strDiv .= '<div class="form-control input-md" style="height:100%;">';
      $strDiv .= '<h3>Page: '.$strSourceURL.'</h3>';
      $strCSSImages = '<h3>CSS Images</h3>';
      foreach($arrImageBase as $strSourceFile=>$arrResults){
        if($strSourceFile == 'page' || $strSourceURL == $strSourceFile){
          foreach($arrResults as $strImageLocation=>$arrCodeDescription){
            $strDiv .= '<p style="font-size:17px;">Location: '.$strImageLocation.'.&nbsp;<b style="background-color:'.$arrCodeDescription[1].';color:#000000;">&nbsp;Status ['.$arrCodeDescription[2].']&nbsp;'.$arrCodeDescription[0].'</b>';
            if(@getimagesize($strImageLocation))
                $strDiv .= '&nbsp;&nbsp;<img src="'.$strImageLocation.'" style="height:50px;" />';
            $strDiv .= '</p>';
          }
        }
        else{
          $strCSSImages .= '<p style="font-size:17px;">Location: '.$strSourceFile;
          $strCSSImages .= '<ul>';
          foreach($arrResults as $strImageLocation=>$arrCodeDescription){
              $strCSSImages .= '<li>';
              $strCSSImages .= $strImageLocation.'&nbsp;<b style="background-color:'.$arrCodeDescription[1].';color:#000000;">&nbsp;Status ['.$arrCodeDescription[2].'] '.$arrCodeDescription[0].'</b>';
              if(@getimagesize($strImageLocation))
                $strCSSImages .= '&nbsp;&nbsp;<img src="'.$strImageLocation.'" style="height:50px;" />';
              $strCSSImages .= '</li>';
          }
          $strCSSImages .= '</ul>';
          $strCSSImages .= '</p>';
        }
      }
      if(trim($strCSSImages) != '<h3>CSS Images</h3>')
        $strDiv .= $strCSSImages;
      $strDiv .= '</div>';
    }
    $strDiv .= PCAdminPages::Get()->IncludeHTMLFooter();
    $strDiv .= '</div>';
    echo $strDiv;
  }



  /*
  let's remove menu suboptions not permitted by for customers
  function PCMW_RemoveWooMenuOptions(){
        remove_submenu_page( 'admin.php','wc-addons' );
        remove_submenu_page(  'admin.php','wc-settings' );
        remove_submenu_page(  'admin.php');
        remove_submenu_page(  'admin.php','page=wc-settings' );
        remove_menu_page( 'index.php' );                  //Dashboard
        remove_menu_page( 'jetpack' );                    //Jetpack*
        remove_menu_page( 'edit.php' );                   //Posts
        remove_menu_page( 'upload.php' );                 //Media
        remove_menu_page( 'edit.php?post_type=page' );    //Pages
        remove_menu_page( 'edit-comments.php' );          //Comments
        remove_menu_page( 'themes.php' );                 //Appearance
        remove_menu_page( 'plugins.php' );                //Plugins
        remove_menu_page( 'users.php' );                  //Users
        remove_menu_page( 'tools.php' );                  //Tools
        remove_menu_page( 'options-general.php' );        //Settings
  //add_role('shop_manager', 'Shop Manager', array('read'=>TRUE,'edit_posts'=>TRUE,'delete_posts'=>FALSE,'create_users'=>TRUE));
    if (!User::Get()->CanUser('manage_options')) {
      remove_submenu_page('woocommerce', 'woocommerce_settings');
      remove_submenu_page('woocommerce', 'woocommerce_status');
      //remove_role('shop_manager');

        //remove_menu_page('admin.php','page=wc-addons');
        remove_menu_page('admin.php?page=wc-settings');
        remove_menu_page('admin.php?page=wc-status');
        remove_submenu_page( 'admin.php','wc-addons' );
        remove_submenu_page(  'admin.php','wc-settings' );
        remove_submenu_page(  'admin.php');
        remove_submenu_page(  'admin.php','page=wc-settings' );
    }
    return FALSE;
  }
   */


}//end class
?>