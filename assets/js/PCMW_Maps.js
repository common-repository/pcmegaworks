var infowindows = [];
var markers = [];
var map = null;
var arrCaseIds = {};
var directionsService;
var directionsDisplay;
function initMap() {
  map = new google.maps.Map(document.getElementById(strMapName), {
    zoom: intMapZoom,
    center: {lat: fltCenterLatitude, lng: fltCenterLongitude},
    /**
      -'roadmap' displays the default road map view. This is the default map type.
      -'satellite' displays Google Earth satellite images.
      -'hybrid' displays a mixture of normal and satellite views.
      -'terrain' displays a physical map based on terrain information.
    */
    mapTypeId: strMapType,
  });//
  //taffic overlay
  if(boolUseTraffic){
    const trafficLayer = new google.maps.TrafficLayer();
    trafficLayer.setMap(map);
  }
  //marker clustering
  if(boolUseElevation){
    const elevator = new google.maps.ElevationService();
    const infowindow = new google.maps.InfoWindow({});
    infowindow.open(map);
    // Add a listener for the click event. Display the elevation for the LatLng of
    // the click inside the infowindow.
    map.addListener("click", (event) => {
      displayLocationElevation(event.latLng, elevator, infowindow);
    });
  }
  //satellite view kills markers for some reason
  //map.setMapTypeId(google.maps.MapTypeId.SATELLITE);
  if(boolUseDirections){
    directionsService = new google.maps.DirectionsService();
    directionsDisplay = new google.maps.DirectionsRenderer();
    directionsDisplay.setMap(map);
    directionsDisplay.setPanel(document.getElementById('dir_box'));
  }
  var labels = jsonLabels;

  //manually build the markers
  for (var key in jsonLocations) {
    if (jsonLocations.hasOwnProperty(key)) {
      markers[key] = new google.maps.Marker({
              position: new google.maps.LatLng(jsonLocations[key].lat, jsonLocations[key].lng),
              map: map,
              title: labels[key],
              draggable: false,
              label: labels[key % labels.length],
              locdata:jsonLocations[key].data

      });
    }
    //add single entry tags
    var strWindowContent = '<h5>'+jsonLocations[key].data+'</h5><br>';
    //do we allow the directions service?
    if(boolUseDirections){
      strWindowContent += '<input type="text" id="pcmwmap_'+key+'" class="form-control input-md" placeholder="From Address" /><br>';
      strWindowContent += '<input type="button" value="Get Directions" onclick="LoadDirections(\''+key+'\','+jsonLocations[key].lat+','+jsonLocations[key].lng+')" class="btn btn-primary" />';
    }
    infowindows[key] = new google.maps.InfoWindow({
                        content:strWindowContent
                      });
    AddSingleInfoWindow(key,jsonLocations[key].data);
  }
  //marker clustering
  if(boolUseCluster){
    // Add a marker clusterer to manage the markers.
    var markerCluster = new MarkerClusterer(map, markers,
      {imagePath: strImagePath});
    //remove the tooltip to add our own custom controls
    map.addListener('mousemove', function() {
        $("[role='tooltip']").remove();
    });
    //add our maps
    google.maps.event.addListener(markerCluster, 'click', function(objCluster) {
      var info = new google.maps.MVCObject;
      info.set('position', objCluster.center_);
      info.set('label', labels[(objCluster.length-1)]);
      if(IsMaxZoom()){
        var infowindow = new google.maps.InfoWindow();
        infowindow.close(); // closes previous open infowindows
        infowindow.setContent(AddInfoWindow(objCluster));
        infowindow.open(map, info);
        google.maps.event.addListener(infowindow, 'domready', function() {
         $('[data-toggle="tooltip"]').tooltip();
         });
      }
    });
  }

}

/**
* @brief: Add elevation data to an info window
* @param location
* @param elevator
* @param infowindow
* @return void
*/
function displayLocationElevation(location, elevator, infowindow) {
  // Initiate the location request
  elevator.getElevationForLocations(
    {
      locations: [location],
    },
    (results, status) => {
      infowindow.setPosition(location);

      if (status === "OK") {
        // Retrieve the first result
        if (results[0]) {
          // Open the infowindow indicating the elevation at the clicked position.
          infowindow.setContent(
            "The elevation at this point <br>is " +
              results[0].elevation +
              " meters."
          );
        } else {
          infowindow.setContent("No results found");
        }
      } else {
        infowindow.setContent("Elevation service failed due to: " + status);
      }
    }
  );
}

/**
* @brief: Add a single info window content
* @param varKey
* @param varData
* @return void
*/
function AddSingleInfoWindow(varKey,varData){
  (function (varKeyData, varDataText) {
    google.maps.event.addListener(markers[varKeyData], 'click', function() {
      infowindows[varKeyData].open(map,markers[varKeyData]);
    });
  })(varKey, varData);
}

/**
* @brief:set our info window now given a cluster
* @param objCluster
* @return string ( HTML )
*/
function AddInfoWindow(objCluster){
  var arrClusterMarks = objCluster.getMarkers();
  var strOutput = '';
  for (var i = 0; i < arrClusterMarks.length; i++) {
    strOutput += '<h4><span class="badge badge-primary">'+arrClusterMarks[i].locdata+'</span></h4><br>';
  }
  return '<div class="panel panel-primary">'+
          '  <div class="panel-heading">Locations</div>'+
          '  <div class="panel-body">'+strOutput+'</div>'+
          '</div>';
}

/**
* @brief:determine if we are at max zoom
* @return bool
*/
function IsMaxZoom(){
  return (map.getZoom() == map.mapTypes[map.getMapTypeId()].maxZoom)
}

/**
* given an address and an origin show the directions
* @param intKey
* @param fltLatitude
* @param fltLongitude
* @return bool
*/
function LoadDirections(intKey,fltLatitude,fltLongitude){
  var address = $('#pcmwmap_'+intKey).val();
  if(address != ''){
    var request = {
        origin: address,
        destination: fltLatitude+','+fltLongitude,
        travelMode: google.maps.DirectionsTravelMode.DRIVING
    };

    directionsService.route(request, function(response, status) {
        if (status == google.maps.DirectionsStatus.OK) {
            directionsDisplay.setDirections(response);
        }
    });
  }
}