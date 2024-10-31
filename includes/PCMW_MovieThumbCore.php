<?php
/**************************************************************************
* @CLASS PCMW_MovieThumb
* @brief Create a thumbnail for a video file.
* @REQUIRES:
*  -PCMW_WatermarkCore.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_WatermarkCore.php');
class PCMW_MovieThumb EXTENDS PCMW_Watermark{
   var $boolMakeWatermark = TRUE;
   var $strFileName = '';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new MovieThumb();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
    parent::__construct();
  }

    //copied from
    function createMovieThumb($srcFile, $destFile = "GreatNews.jpg")
    {
        $output = array();
        $cmd = sprintf('%sffmpeg.exe -i file:"%s" -an -ss 00:00:15 -r 1 -vframes 1 -y file:"%s"',FFMPEGPATH, $srcFile, $destFile.'/'.$this->strFileName);
        if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN'))
            $cmd = str_replace('/', DIRECTORY_SEPARATOR, $cmd);
        else
            $cmd = str_replace('\\', DIRECTORY_SEPARATOR, $cmd);
        $varReults = exec($cmd, $output, $retval);
        //$this->LoadDebugLog('$cmd ['.$cmd.']-  LINE '.__LINE__."\r\n",TRUE,__METHOD__);
        if ($retval){
        $strLastError = var_export(error_get_last(),TRUE);
        $this->LoadDebugLog('$retval ['.$retval.'] $strLastError ['.$strLastError.'] $varReults ['.$varReults.']-  LINE '.__LINE__."\r\n",TRUE,__METHOD__);
            return false;
        }
        if($this->boolMakeWatermark){
            $this->strImageSource = $destFile.'/'.$this->strFileName;
            $this->strNewImage = $destFile.'/'.$this->strFileName;
            $this->LoadDebugLog('$this->strImageSource ['.$this->strImageSource.']-  LINE '.__LINE__."\r\n",TRUE,__METHOD__);
            $this->MakeWaterMark();
        }
        else{
         $this->LoadDebugLog('Cannot make watermark ['.$destFile.'/'.$this->strFileName.']-  LINE '.__LINE__."\r\n",TRUE,__METHOD__);
        }
        return $this->strImageSource;
    }

}//end class
?>