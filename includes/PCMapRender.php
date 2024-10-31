<?php
/**************************************************************************
* @CLASS PCMapRender
* @brief Render our map for a page or container
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_ConfigCore.php
*  -PCMW_Utility.php
*  -PCMW_Abstraction.php
*
**************************************************************************/
class PCMapRender{
    var $arrLocations;
    var $arrLabels;
    var $objGroupData;
    var $fltCenterLatitude = 41.850033;
    var $fltCenterLongitude = -87.6500523;
    public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMapRender();
		return( $inst );
   }

  /**
  * @brief: Load our map group object
  * @param $intMapGroupId - map group which handles display settings
  * @return $objMapGroup
  */
  function LoadGroupData($intMapGroupId){
    if(($arrMapGroup = PCMW_Database::Get()->GetMapGroupById($intMapGroupId))){
      $this->objGroupData = new PCMW_MapGroup();
      $this->objGroupData->LoadObjectWithArray($arrMapGroup[0]);
      $this->objGroupData->arrSettingsData = PCMW_Utility::Get()->JSONDecode($this->objGroupData->strMapGroupSettings);
      return TRUE;
    }
    else return FALSE;
  }

  /**
  * given a map group ID, load the group data
  * @param $intGroupId
  * @return bool
  */
  function LoadMapGroupData($intGroupId){
    if($this->LoadGroupData($intGroupId)){
      return $this->FillMarkerArray();
    }
    return FALSE;
  }

  /**
  * Load and return a map
  * @param $arrParams
  * @return string (HTML)
  */
  function RenderMap($arrParams){
    if($this->LoadMapGroupData($arrParams['groupid'])){
      $this->InsertMapScripts();
      $strMapData = '<div id="'.$this->objGroupData->arrSettingsData['groupname'].'"';
      $strMapData .= ' style="width: '.$this->objGroupData->arrSettingsData['width'].'px;';
      $strMapData .= 'height:'.$this->objGroupData->arrSettingsData['height'].'px;';
      $strMapData .= ' height="'.$this->objGroupData->arrSettingsData['height'].'"';
      $strMapData .= ' width="'.$this->objGroupData->arrSettingsData['width'].'"';
      $strMapData .= ' "></div>';
      $strMapData .= ' <div id="dir_box"></div>';
      $strMapData .= $this->MakeMapsJS($this->objGroupData->arrSettingsData['groupname'],
                                       $this->arrLabels,
                                       $this->arrLocations,
                                       $this->objGroupData->arrSettingsData['zoomlevel']);
      return $strMapData;
    }
    else{
     $strParams = var_export($arrParams,TRUE);
     return '<div class="panel panel-primary">'.
          '  <div class="panel-heading">Map data unavailable. Error:'.__LINE__.'</div>'.
          '  <div class="panel-body">'.$strParams.'</div>'.
          '</div>';
    }
  }

  /**
  * @brief: enqueue the maps scripts
  * @return bool
  */
  function InsertMapScripts(){
    wp_enqueue_script('pcmw_pcplugin',plugin_dir_url( __FILE__ ).'../assets/js/PCPlugin.js',array(),'');
    wp_enqueue_script('pcmw_ajaxcore',plugin_dir_url( __FILE__ ).'../assets/js/AjaxCore.js',array(),'');
    wp_enqueue_script('pcmw_pcmaps',plugin_dir_url( __FILE__ ).'../assets/js/PCMW_Maps.js',array(),'');
    wp_enqueue_script('pcmw_cluster','https://unpkg.com/@google/markerclustererplus@4.0.1/dist/markerclustererplus.min.js',array(),'4.0.1');
    wp_enqueue_script('pcmw_mapsapi','https://maps.googleapis.com/maps/api/js?key='.PCMW_ConfigCore::Get()->objConfig->GetGoogleMapKey().'&callback=initMap',array(),'1.0.0');

  }

  /**
  * @brief: get the geocode data for our mapgroup
  * @param $strAddress - address to be returned
  * @return array(lat,long)
  */
  function GeoCodeAddress($strAddress){
    $strURL = 'https://maps.googleapis.com/maps/api/geocode/json';
    $strFields = '?address='.str_replace(' ','%20',$strAddress.'&key='.PCMW_ConfigCore::Get()->objConfig->GetGoogleMapKey());
    if(!$arrResults = PCMW_Utility::Get()->MakeQuickCURL($strURL.$strFields)){
      $strResults = var_export($arrResults,TRUE);
      PCMW_Logger::Debug('$arrResults ['.$strResults.'] $strPayLoad ['.$strPayLoad.'] $strValues ['.$strValues.']'.__LINE__,1);
    }
    $arrCoords = PCMW_Utility::Get()->JSONDecode($arrResults['result']);
    return $arrCoords['results'][0]['geometry']['location'];
  }

  /**
  * @brief: get the directions
  * @return JSON
  */
  function GetDirections(){
   $strCall = 'https://maps.googleapis.com/maps/api/directions/json?
origin=Chicago,IL&destination=Los+Angeles,CA
&waypoints=Joplin,MO|Oklahoma+City,OK
&key=YOUR_API_KEY';
  }

  /**
  * @brief: load the JavaScript for the maps API
  * @param $strMapName - div name for unique map creation
  * @param $arrLabels - labels for each map tooltips
  * @param $arrLocations - location data which matches the labels
  * @param $intZoom - default zoom level
  * @return string ( Javascript )
  */
  function MakeMapsJS($strMapName,$arrLabels,$arrLocations,$intZoom=9){
   $strJS = '<script>';
   $strJS .= 'var intMapZoom = '.$intZoom.';'."\r\n";
   $strJS .= 'var fltCenterLatitude = '.$this->fltCenterLatitude.';'."\r\n";
   $strJS .= 'var fltCenterLongitude = '.$this->fltCenterLongitude.';'."\r\n";
   $strJS .= 'var jsonLabels = ["'.implode('","', $arrLabels).'"];'."\r\n";
   $strJS .= 'var strImagePath = "'.plugin_dir_url( __FILE__ ).'../assets/images/m'.'";'."\r\n";
   $strJS .= 'var strMapName = "'.$strMapName.'";'."\r\n";
   $strJS .= 'var jsonLocations = '.str_replace('"','',json_encode($arrLocations)).';'."\r\n";
   $strJS .= 'var boolUseTraffic = '.json_encode((bool)$this->objGroupData->arrSettingsData['trafficoverlay']).';'."\r\n";
   $strJS .= 'var boolUseDirections = '.json_encode((bool)$this->objGroupData->arrSettingsData['directions']).';'."\r\n";
   $strJS .= 'var boolUseElevation = '.json_encode((bool)$this->objGroupData->arrSettingsData['elevation']).';'."\r\n";
   $strJS .= 'var boolUseCluster = '.json_encode((bool)$this->objGroupData->arrSettingsData['clusterlocations']).';'."\r\n";
   $strJS .= 'var strMapType = "roadmap";'."\r\n";
   $strJS .= '</script>';
   //$strJS .= '<script src="'.plugin_dir_url( __FILE__ ).'../assets/js/PCMW_Maps.js"></script>';
    return $strJS;
  }


  /**
  * @brief: load the locations and labels for a given group ID
  * @return bool
  */
  function FillMarkerArray(){
    $this->arrLocations = array();
    $this->arrLabels = array();
    if(trim(PCMW_ConfigCore::Get()->objConfig->GetPluginVersion()) != ''){
      if(($arrLocations = PCMW_Database::Get()->GetVendorsByGroup($this->objGroupData->intMapGroupId,TRUE))){
        foreach($arrLocations as $arrLocation){
          $strAddress = $arrLocation['vendorname'].'<br />';
          $strDirections = '';
          if((bool)$this->objGroupData->arrSettingsData['showaddress']){
            $strDirections .= $arrLocation['vendoraddress'];
            $strDirections .= ' '.$arrLocation['vendorcity'];
            $strDirections .= ' '.$arrLocation['vendorstate'];
            $strDirections .= ' '.$arrLocation['vendorzip'];
            $strAddress .= $strDirections;
          }
          //geocode this now since it wasn't previously somehow
          if(empty($arrLocation['latitude'])){
            $arrCoordinates = $this->GeoCodeAddress($strAddress);
            $arrLocation['latitude'] = $arrCoordinates['lat'];
            $arrLocation['longitude'] = $arrCoordinates['lng'];
            $objVendor = new PCMW_Vendor();
            $objVendor->LoadObjectWithArray($arrLocation);
            PCMW_Database::Get()->UpdateVendor($objVendor);
          }
          if(!empty($arrLocation['latitude'])){
            $this->fltCenterLatitude = $arrLocation['latitude'];
            $this->fltCenterLongitude = $arrLocation['longitude'];
          }
          $strAddress .= '<br />'.$arrLocation['vendortelephone'];
          if(trim($arrLocation['vendorwebsite']) != '')
            $strAddress .= '<br /><a href="'.$arrLocation['vendorwebsite'].'" >'.$arrLocation['vendorwebsite'].'</a>';
          $strAddress .= '<br />'.$arrLocation['vendordescription'];
          if(trim($arrLocation['vendoricon']) != '')
            $strAddress .= '<br /><img src="'.$arrLocation['vendoricon'].'" height="20" width="20" class="rounded mx-auto d-block" />';
          $this->arrLocations[] = array('lat'=>$arrLocation['latitude'],'lng'=>$arrLocation['longitude'],'data'=>"'".trim($strAddress)."'",'directions'=>"'".str_replace(' ','+',$strDirections)."'");
          $this->arrLabels[] = $arrLocation['vendorname'];
        }
        return TRUE;
     }
     else{
        PCMW_Abstraction::Get()->AddUserMSG('Unable to load Map values. Exiting... LINE ['.__LINE__.']',1);
        return FALSE;
     }
   }
   return FALSE;
  }

}//end class
?>