<?php
/**************************************************************************
* @CLASS PCMW_HostRequest
* @brief Send the host server a request for data or updates.
* @REQUIRES:
*  -PCMW_Utility.php
*  -PCMW_Abstraction.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
class PCMW_HostRequest extends PCMW_BaseClass{

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_HostRequest();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }

  /**
  * request data from the host server
  * @param $arrPayLoad
  * @return array || FALSE    PCMW_Logger
  */
  function MakeHostRequest($arrPayLoad){
   $strPayLoad = PCMW_Utility::Get()->JSONEncode($arrPayLoad);
   if($strResults = PCMW_Abstraction::Get()->FireCurl('payload='.$strPayLoad)){
      if($strResults && trim($strResults) != '' && !is_numeric($strResults)){
      $strResults = urldecode($strResults);
      //remove carriage returns
      $strResults = preg_replace('/\R/', '', $strResults);
      //remove artifact line breaks, tabs and properly escape double and single
      $strResults = str_replace(array("\\n","\\r",'\\r\\n','\\n\\r','\\t',"\\'",'\\"'),array(' ',' ','','','',"\\\'",'\\\\\\"'),$strResults);
      $arrResults = PCMW_Utility::Get()->JSONDecode($strResults);                                     
        return $arrResults;
      }
   }
   return FALSE;
  }

}//end class
?>