<?php
/**************************************************************************
* @CLASS PCMW_Login
* @brief Handle all things login related
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_Abstraction.php
*
**************************************************************************/
class PCMW_Login extends PCMW_BaseClass{

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_Login();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
    //
  }

  /**
  * Check $_POST for a login attempt
  * return array ( cleaned $_POST )
  */
  function CheckForLogin($arrPOST){
   //do we have an action?
   if(array_key_exists('action',$arrPOST) &&
      $arrPOST['action'] == 'login'){
     if(!$objWPUser = $this->PCMW_LogUserIn($arrPOST)){
        PCMW_Abstraction::Get()->AddUserMSG( 'Username or password not recognized.  ['.__LINE__.']',1);
     }
     else{
      //if they don't have privileges, redirect
      if(PCMW_Abstraction::Get()->CheckPrivileges(PCMW_ConfigCore::Get()->objConfig->GetAdminEmail(),
                                                  PCMW_ADMINISTRATOR,
                                                  FALSE,
                                                  FALSE))
        wp_safe_redirect(get_site_url().'wp-admin');//
      else{
        $strHomePage = (trim(PCMW_ConfigCore::Get()->objConfig->GetHomePage()) != '')? PCMW_ConfigCore::Get()->objConfig->GetHomePage() : '/';
        wp_safe_redirect(get_site_url().$strHomePage);
      }
      exit;
     }
   }
   else
      PCMW_Abstraction::Get()->AddUserMSG( 'Cannot validate '.$arrPOST['action'].'. ['.__LINE__.']',1);
   return $arrPOST;
  }

  /**
  * given a username or password, log in a user
  * @param $arrPOST
  * @return bool
  */
  function PCMW_LogUserIn($arrPOST){
    if(session_id() == '' || !isset($_SESSION))
        // We need session here
        session_start();
    $strUserName = '';
    $strPassWord = '';
    $strRememberMe = 0;
    //the only way this form is called is if we're using a custom form, get the data
    foreach(PCMW_FormManager::Get()->GetDefinitionByAlias('loginform',1,TRUE) as $objDefinition){
      //check to see if this is designated as the username or password field
     $arrDefinitionAttributes = PCMW_Utility::Get()->DecomposeCurlString($objDefinition->strElementAttributes);
     if(array_key_exists('formrole',$arrDefinitionAttributes)){
       if($arrDefinitionAttributes['formrole'] == 'username')
         $strUserName = $arrPOST[$objDefinition->strDefinitionName];
       if($arrDefinitionAttributes['formrole'] == 'password')
         $strPassWord = $arrPOST[$objDefinition->strDefinitionName];
       if($arrDefinitionAttributes['formrole'] == 'rememberme')
         $strRememberMe = $arrPOST[$objDefinition->strDefinitionName];
     }
     continue 1;
    }
    // If an email address is entered in the username box,
    // then look up the matching username and authenticate as per normal, using that.
    if ( ! empty( $strUserName ) ) {
        //if the username doesn't contain a @ set username to blank string
        //causes authenticate to fail
        if(strpos($strUserName, '@') == FALSE){
          $objUser = get_user_by( 'login', $strUserName );
        }
        else
          $objUser = get_user_by( 'email', $strUserName );
    }
    // using the username found when looking up via email
    if ( isset( $objUser->user_login, $objUser ) )
        $strUserName = $objUser->user_login;
    $arrCredentials = array();
    $arrCredentials['user_login'] = $strUserName;
    $arrCredentials['user_password'] = $strPassWord;
    $arrCredentials['rememberme'] = (array_key_exists('cookies',$arrPOST) && $arrPOST['cookies'] == 'cookies')? TRUE:FALSE ;
    $arrCredentials['remember'] = $arrCredentials['rememberme'];
    //set our cookie 
    if($arrCredentials['rememberme']){
        if(!PCMW_Utility::Get()->SetPCCookie('pclogincookie',array('username'=>$strUserName,'cookies'=>1)))
            PCMW_Abstraction::Get()->AddUserMSG( 'Cookie not set! ['.__LINE__.']',1);
        else
            PCMW_Abstraction::Get()->AddUserMSG( 'Cookie set! ['.__LINE__.']',3);
    }

    try{
      //$objWPLogin = wp_signon( $arrCredentials,false );
	  $objWPLogin = wp_authenticate($arrCredentials['user_login'], $arrCredentials['user_password']);
      if(!$objWPLogin || is_wp_error( $objWPLogin )){
          $objWPLogin->get_error_message();
          $strErrors = var_export($objWPLogin,TRUE);
          PCMW_Abstraction::Get()->AddUserMSG( '<pre>'.$strErrors.'</pre> ['.__LINE__.']',1);
          return FALSE;
      }
      else{
        wp_clear_auth_cookie();
        wp_set_current_user($objWPLogin->ID, $strUserName);
        wp_set_auth_cookie($objWPLogin->ID,TRUE);
        do_action( 'wp_login', $objWPLogin->user_login, $objWPLogin );
        $_SESSION['CURRENTUSER'] = array();
        $_SESSION['CURRENTUSER']['WPUSEROBJECT'] = $objWPLogin;
        $_SESSION['CURRENTUSER']['pcgroup'] = PCMW_AdminUserCore::GetAdminUserId(0,$objWPLogin->ID);
        PCPluginCore::Get()->objCurrentUser = $_SESSION['CURRENTUSER'];           
        return $objWPLogin;
      }
    }
    catch(Exception $arrException){
        $strErrors = var_export($objWPLogin,TRUE);
        $strException = var_export($arrException,TRUE);
        PCMW_Abstraction::Get()->AddUserMSG( '<pre>'.$strErrors.'<br />'.$strException.'</pre> ['.__LINE__.']',1);
    }
    return FALSE;
  }
 
}//end class
?>