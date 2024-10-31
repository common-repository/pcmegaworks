<?php
/**************************************************************************
* @CLASS PCMW_BootQueryUpdater
* @brief Module for impolmentnig custom bootstrap and jQuery libraries.
* @REQUIRES:
*  -PCMW_Database.php
*
**************************************************************************/
class PCMW_BootQueryUpdater extends PCMW_BaseClass{

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_BootQueryUpdater();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }


}//end class
?>