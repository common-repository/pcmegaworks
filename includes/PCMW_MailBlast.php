<?php
/**************************************************************************
* @CLASS PCMW_MailBlast
* @brief Handle all actions for mail blasts
* @REQUIRES:
*  -Database.php
*
**************************************************************************/
class PCMW_MailBlast extends PCMW_BaseClass{

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_MailBlast();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }

  /**
  * given the post data, check to see if a blast has action to take
  * @param $arrPOST
  * @return bool
  */
  function ProcessMailBlast(&$arrPOST){
    //make sure we have something to do, and verify our nonce
    if(array_key_exists('action',$arrPOST) &&
       trim($arrPOST['action']) != '' &&
      wp_verify_nonce(@$arrPOST['wp_nonce'],@$arrPOST['submissionid'])){
      if($arrPOST['action'] == 'get' && (int)$arrPOST['mailblastid'] > 0)
        $this->GetMailBlastData($arrPOST);
      //we've been posted a request
      else if($arrPOST['action'] == 'save' && trim($arrPOST['mailsubject']) != '' && trim($arrPOST['mailmessage']) != '')
        $this->SaveMailBlastData($arrPOST);
      else{
        //do we send it?
        if($arrPOST['action'] == 'send')
            $this->SendMailBlast($arrPOST);
        if($arrPOST['action'] == 'delete' && (int)$arrPOST['mailblastid'] > 0)
            $this->DeleteMailBlastData($arrPOST);
      }
    }
    return TRUE;
  }

  /**
  * get a mail blast for content
  * @param $arrPOST
  * @return array Blast data
  */
  function GetMailBlastData(&$arrPOST){
    //we're getting a previous mailblast record
    $arrHistory = PCMW_Database::Get()->GetMailBlastHistory($arrPOST['mailblastid']);
    $arrPOST = $arrHistory[0];
    $arrPOST['action'] = 'update';
    return TRUE;
  }

  /**
  * save mail blast content
  * @param $arrPOST
  * @return bool
  */
  function SaveMailBlastData($arrPOST){
    if(array_key_exists('mailblastid',$arrPOST) && (int)$arrPOST['mailblastid'] > 0){
      if(!PCMW_Database::Get()->UpdateMailBlast($arrPOST['mailblastid'],$arrPOST['mailsubject'],$arrPOST['mailmessage']))
        PCMW_Abstraction::Get()->AddUserMSG( 'Could not update mail blast. Please contact helpdesk and paste the contents of the debug log in the ticket. ['.__LINE__.']',1);
      else
        PCMW_Abstraction::Get()->AddUserMSG( 'Mail blast updated! ['.__LINE__.']',3);
    }
    else{
      if(!($arrPOST['mailblastid'] = PCMW_Database::Get()->StoreMailBlast(get_current_user_id(),
                                           $arrPOST['mailsubject'],
                                           $arrPOST['mailmessage'])))
        PCMW_Abstraction::Get()->AddUserMSG( 'Could not save mail blast. Please contact helpdesk and paste the contents of the debug log in the ticket. ['.__LINE__.']',1);
      else
        PCMW_Abstraction::Get()->AddUserMSG( 'Mail blast saved! ['.__LINE__.']',3);
    }
    return TRUE;
  }

  /**
  * send a mail blast
  * @param $arrPOST
  * @return bool
  */
  function SendMailBlast($arrPOST){
    //get all users with our meta value
    $arrUsers = get_users(array(
        'meta_key'     => 'pcmw_mail_blast',
    ));
    $strFailedMails = '';
    foreach($arrUsers as $objUser){
      //send the mail
      if(!PCMW_Abstraction::Get()->Send_Mail($objUser->data->user_email ,
                                           $arrPOST['mailsubject'] ,
                                           $arrPOST['mailmessage'] ,
                                           PCMW_SUPPORT))
        $strFailedMails .= 'Mail not sent to '.$objUser->data->user_email.'<br />';
    }
    //let the user know the emails weren't sent
    if(trim($strFailedMails) != ''){
      PCMW_Abstraction::Get()->AddUserMSG( $strFailedMails.' ['.__LINE__.']',1);
      PCMW_Logger::Debug($strFailedMails.' ['.__LINE__.']',1);
    }
    else
      PCMW_Abstraction::Get()->AddUserMSG( 'Mail blast sent! ['.__LINE__.']',3);
    return TRUE;
  }

  /**
  * delete mail blast content
  * @param $arrPOST
  * @return bool
  */
  function DeleteMailBlastData($arrPOST){
    if(Database::Get()->DeleteMailBlast($arrPOST['mailblastid']))
      PCMW_Abstraction::Get()->AddUserMSG( 'Mail blast deleted! ['.__LINE__.']',3);
    else
      PCMW_Abstraction::Get()->AddUserMSG( 'Mail blast not deleted! ['.__LINE__.']',1);
    return TRUE;
  }

}//end class
?>