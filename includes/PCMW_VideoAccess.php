<?php
/**************************************************************************
* @CLASS PCMW_VideoAccess
* @brief Handle all things video related.
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_Video.php
*  -PCMW_Abstraction.php
**************************************************************************/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_Video.php');
class PCMW_VideoAccess extends PCMW_BaseClass{

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_VideoAccess();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }

  /**
  * given a source and location, get a video form and return it
  * @param $arrAttributes
  * @return HTML
  */
  function PCMW_GetVideo($arrAttributes){                                     
    $objVideoData = $this->GetVideo($arrAttributes['videoid'],$_SESSION['CURRENTUSER']['pcgroup']['admingroupid'],TRUE);
    //make the video player
    $strControls = ($objVideoData->intShowControls > 0)? ' controls ': '';
    $strPoster = (trim(PCMW_ConfigCore::Get()->objConfig->GetLogo()) == '')? 'poster="'.get_header_image().'"':'poster="'.PCMW_ConfigCore::Get()->objConfig->GetLogo().'"' ;
    $strSource = $objVideoData->strVideoSource;
    if($objVideoData->intHideSource > 0 && stristr($strSource,get_site_url().'/'))
        $strSource = plugin_dir_url( __FILE__ ).'../Video.php?videoid='.$arrAttributes['videoid'];
    $strPlayer = '<video class="'.$objVideoData->strCSSClass.'" height="'.$objVideoData->intVideoHeight.'" width="'.$objVideoData->intVideoWidth.'" '.$strControls.' '.$strPoster.' >';
    $strPlayer .= '<source src="'.$strSource.'" type="'.$objVideoData->strVideoType.'">';
    $strPlayer .= '</video>';
    return $strPlayer;
  }

  /**
  * get the available video list for a user
  * @return array()
  */
  public static function GetVideoList(){
    $arrVideoQuery = array(
      'post_type'      => 'attachment',
      'post_mime_type' => 'video',
      'post_status'    => 'inherit',
      'posts_per_page' => - 1,
    );
    //get the results
    $arrVideoData = new WP_Query( $arrVideoQuery );
    //load our return array
    $arrVideos = array();
    foreach ( $arrVideoData->posts as $objVideo ) {
        $arrImages[] = wp_get_attachment_url( $objVideo->ID );
    }
    return $arrImages;
  }


  /**
  * load the video and thumbnail for a given product
  * @return HTML
  function PCMW_LoadVideo($arrAttributes){

    if((int)PCMW_ConfigCore::Get()->objConfig->GetRestrictPages() > 0){
        $_SESSION['previouspage'] = $_SERVER['REQUEST_URI'];
        PCMW_Abstraction::Get()->RedirectUser(PCMW_ConfigCore::Get()->objConfig->GetUseCustomLogin());
    }
    $strFileLocation = PARENTADDRESS.'/'.HOMEFOLDER.'/video.php?videoid='.$arrAttributes['videoid'];
    $strThumbLocation = PARENTADDRESS.'/'.HOMEFOLDER.'/Thumbnail.php?thumbnail='.get_the_ID();
    //$arrAssetDetails = QueServerAPI::Get()->MakeServerRequest('getassetbyproductid',array('productid'=>get_the_ID()));
    $_SESSION['thumbnail'] = $arrAssetDetails['strProductThumbnail'];

    return '<script  type="text/javascript">LoadProductVideo("'.$strThumbLocation.'","'.$strFileLocation.'")</script>';
  }
  */


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
  function MakePCMW_VideoTable(){
    $arrTableData = array();
    $arrTableData['tabledescription'] = 'Manage Video Access';
    //define our columns
    $arrTableData['tableheader'] = array('videoid'=>'ID',
                                         'videotitle'=>'Title',
                                         'shortcode'=>'Shortcode',
                                         'accesslevel'=>'Access Group',
                                         'showcontrols'=>'Controls',
                                         'hidesource'=>'Hide Source',
                                         'delete'=>'Delete Video');
    //get the status list
    $arrStatus = PCMW_Abstraction::Get()->GetAvailableAccessLevels();
    //make the vendor table data
    $arrTableData['tabledata'] = array();
    if(sizeof(($arrVideos = $this->GetVideo(0,$_SESSION['CURRENTUSER']['pcgroup']['admingroupid']))) > 0 && is_array($arrVideos)){
      foreach($arrVideos as $arrVideo){
        $arrTableData['tabledata'][$arrVideo['videoid']] = array();
        foreach($arrTableData['tableheader'] as $strKey=>$strValue){
          $arrTableData['tabledata'][$arrVideo['videoid']][$strKey] = array();
          if($strKey == 'videoid'){
            $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['linkbadge'] = 'fa fa-1x fa-cog';
            $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['linkclass'] = 'btn btn-primary';
            $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['onclickvalue'] = 'GetFormAndDataByAlias(\'videoaccess\','.$arrVideo['videoid'].',1,0,\'\')';
            $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['value'] = $arrVideo['videoid'].' Edit';
          }
          else if($strKey == 'accesslevel'){
            @$arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['value'] = $arrStatus[$arrVideo[$strKey]];
          }
          else if($strKey == 'showcontrols'){
            if($arrVideo['showcontrols'] > 0)
                $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['linkbadge'] = 'fa fa-1x fa-thumbs-up txt-success';
            else
                $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['linkbadge'] = 'fa fa-1x fa-thumbs-down txt-success';
          }
          else if($strKey == 'hidesource'){
            if($arrVideo['hidesource'] > 0)
                $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['linkbadge'] = 'fa fa-1x fa-thumbs-up txt-success';
            else
                $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['linkbadge'] = 'fa fa-1x fa-thumbs-down txt-success';
          }
          else if($strKey == 'shortcode'){
            $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['linkbadge'] = 'fa fa-1x fa-flash pointer';
            //$arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['linkclass'] = 'btn btn-primary';
            $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['tooltip'] = $arrVideo['videosource'];
            //$arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['onclickvalue'] = 'pcmw_CopyInputText(\'\')';
            $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['badgeclick'] = 'pcmw_CopyInputText(\''.$strKey.'_'.$arrVideo['videoid'].'\')';
            $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['inputvalue'] = '[PCMW_Video videoid=\''.$arrVideo['videoid'].'\']';
          }
          else if($strKey == 'delete'){
            $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['linkbadge'] = 'fa fa-1x fa-times-circle';
            $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['linkclass'] = 'btn btn-danger';
            $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['onclickvalue'] = 'DeleteVideo('.$arrVideo['videoid'].')';
            $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['value'] = $arrVideo['videoid'].' Delete';
          }
          else
              $arrTableData['tabledata'][$arrVideo['videoid']][$strKey]['value'] = $arrVideo[$strKey];
        }
      }
    }
    else{
     PCMW_Abstraction::Get()->AddUserMSG( 'There are no videos to edit. Please create one. ['.__LINE__.']',2);
    }
    return PCMW_FormManager::Get()->MakeBootStrapTable($arrTableData);
  }

  /**
  * save or update a video record
  * @param $arrVideoData
  * @return bool
  */
  function SaveOrUpdateVideo($arrVideoData){
   //validate our nonce
   if(PCMW_Abstraction::Get()->ValidateNonce($arrVideoData)){
      //load our video object
      $objVideo = new PCMW_Video();
      $objVideo->LoadObjectWithArray($arrVideoData);
      $strInsertType = 'insert';
      if((int)$objVideo->intVideoId > 0)
        $strInsertType = 'update';
      $objFormManager = new PCMW_FormManager();
      $objFormManager->arrIgnoreFields['savevideo'] = 1;
      $objFormManager->arrIgnoreFields['dir'] = 1;
      //validate our video object
      if(!$objFormManager->ValidateDefinitionRequires(0,$arrVideoData,$objVideo,'videoaccess',array(),$strInsertType)){
        return FALSE;
      }
      else{//passed validation. Insert or update
        if((int)$objVideo->intVideoId > 0)
          return PCMW_Database::Get()->UpdateVideo($objVideo);
        else
          return PCMW_Database::Get()->InsertVideo($objVideo);
      }
    }
  }

  /**
  * get a video record or records
  * @param $intVideoId
  * @param $intVideoAccess
  * @param $boolAsObjects
  * @return object || FALSE
  */
  function GetVideo($intVideoId=0,$intVideoAccess=0,$boolAsObjects=FALSE){
   if($intVideoAccess === 0)
    $intVideoAccess = PCMW_SUSPENDED;
    if($arrVideoData = PCMW_Database::Get()->GetVideo($intVideoId,$intVideoAccess)){
     if(!$boolAsObjects){
      if($intVideoId > 0)
        return $arrVideoData[0];
      else
        return $arrVideoData;
     }
     else{
      //load our objects
      if($intVideoId > 0){
        $objVideo = new PCMW_Video();
        return $objVideo->LoadObjectWithArray($arrVideoData[0]);
      }
      else{
        $arrVideoObjects = array();
        //load our objects
        foreach($arrVideoData as $arrVideo){
          $objVideo = new PCMW_Video();
          $arrVideoObjects[$arrVideo['videoid']] = $objVideo->LoadObjectWithArray($arrVideo);
        }
        return $arrVideoObjects;
      }
     }
    }
    else{
  PCMW_Logger::Debug('$strVideoData [blank] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
       return FALSE;
    }
  }
            
}//end class
?>