/**
 *Contains js functions that call the google map API for using Gmap services on a website
 *@author Fredrick <mconyango@gmail.com>
 *@link https://developers.google.com/maps/documentation/javascript/
 *@date Mon 3rd Dec 2012
 */

/**
 *mother container of all methods and properties
 */
var MyGmapGeocode = {
        /**
         *stores all the created markers
         */
        markersArray: [],
        /**
         *stores the map object
         */
        map: null,
        /**
         *center of the map
         */
        center: null,
        /**
         *Shows directions on a map
         */
        directionsDisplay: null,
        /**
         *google direction service
         */
        directionsService: new google.maps.DirectionsService(),
        /**
         *infobox
         */
        infobox: null,
        /**
         *infowindow contents
         */
        infowindowContents: [],
        /**
         *infowindow
         */
        infowindow: null,
        /**
         *map top???
         */
        mapTop: null,
        /**
         *zoom
         */
        zoom: 12,
        /**
         *panControl
         */
        panControl: true,
        /**
         *zoom control
         */
        zoomControl: true,
        /**
         *scale control
         */
        scaleControl: true,
        /**
         *map type
         */
        mapType: 'ROADMAP',
        /**
         *map options
         */
        options: null,
        /**
         *the html container ID that will house the map
         */
        mapCanvasID: 'map_canvas',
        /**
         *Where the direction texts from google are displayed
         */
        directionsPanel: 'map_direction_panel',
        /**
         * whether to place a marker when clicked
         */
        markerOnClick: true,
        /**
         *Whether to set the marker (center marker) when the map is created
         */
        setCenterMarker: false,
        /**
         *direction point A input ID
         */
        directionPointAId: 'direction-point-a',
        /**
         *direction point B input ID
         */
        directionPointBId: 'direction-point-b',
        /**
         *travel mode input ID
         */
        directionTravelModeID: 'direction-travelMode',
        /**
         *direction travel mode
         *e.g DRIVING,WALKING
         */
        directionTravelMode: 'DRIVING',
        /**
         *get directions form container ID
         */
        getDirectionsFormContainer: 'get_directions_form_container',
        /**
         *get directions link ID
         */
        getDirectionsLink: 'get_directions_link',
        geocodeUrl: '',
        geocode_lat_field_id: 'CoreRetailOutlet_lat',
        geocode_lng_field_id: 'CoreRetailOutlet_lng',
        geocode_loc_field_id: 'CoreRetailOutlet_address',
        /**
         * initialize a map
         * @param lat: Center Latitude
         * @param lng :Center Longitude
         * @param options: Array Other options e.g option={mapType:'Fred',mapCanvasID:'map_canvas'}
         */
        init: function(lat, lng, options) {
                'use strict';
                //set the properties
                if (options) {
                        $.each(options, function(key, value) {
                                if (MyGMap.hasOwnProperty(key))
                                        MyGMap[key] = value;
                        });
                }
                //set the center
                MyGMap.center = new google.maps.LatLng(lat, lng);
                //set the map options
                MyGMap.options = {
                        center: MyGMap.center,
                        zoom: MyGMap.zoom,
                        panControl: MyGMap.panControl,
                        zoomControl: MyGMap.zoomControl,
                        mapTypeId: google.maps.MapTypeId[MyGMap.mapType]
                };
                //create the map
                MyGMap.map = new google.maps.Map(document.getElementById(MyGMap.mapCanvasID), MyGMap.options);
                //create center marker
                //alert(MyGMap.options.panControl);
                if (MyGMap.setCenterMarker) {
                        var marker = new google.maps.Marker({
                                position: MyGMap.center,
                                map: MyGMap.map
                                        /*icon:icon*/
                        });
                }
                //remove a marker on mouseout
                google.maps.event.addListener(MyGMap.map, 'mouseout', function(event) {
                        if (MyGMap.infobox) {
                                MyGMap.infobox.close();
                        }
                });
        },
        /**
         *
         * add markers to a map
         * @param position: position of the marker
         * @param id: unique ID
         * @param icon: the icon
         */
        placeMarker: function(lat, lng, iconColor, id) {
                var iconLink = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|' + iconColor;
                if (!lat || !lng)
                        return false;
                var position = new google.maps.LatLng(lat, lng);
                var marker = new google.maps.Marker({
                        position: position,
                        map: MyGMap.map,
                        icon: iconLink
                });
                MyGMap.map.setCenter(position);
                if (typeof id == 'undefined')
                        MyGMap.markersArray.push(marker);
                else
                        MyGMap.markersArray[id] = marker;
                //add marker events e.g onclick set centre, onclick show infowindow etc
                google.maps.event.addListener(marker, 'click', function() {
                        //set centre
                        MyGMap.map.setCenter(marker.getPosition());
                        if (typeof id != 'undefined') {
                                //show infowindow
                                MyGMap.ShowInfoWindow(marker, id);
                        }
                });
                return marker;
        },
        /**
         * Delete overlays (markers)
         */
        deleteOverlays: function() {
                if (MyGMap.markersArray) {
                        for (i in MyGMap.markersArray) {
                                MyGMap.markersArray[i].setMap(null);
                        }
                        MyGMap.markersArray.length = 0;
                }
        },
        /**
         * Shows any overlays currently in the array
         */
        showOverlays: function() {
                if (MyGMap.markersArray) {
                        for (i in MyGMap.markersArray) {
                                MyGMap.markersArray[i].setMap(MyGMap.map);
                        }
                }
        },
        initCrowdMap: function(data, isAdmin) {
                'use strict';
                MyGMap.populateMarkers(data, isAdmin);
                $(".category-filter-link").on("click", function(event) {
                        $('.category-filter-link').removeClass('active');
                        $(this).addClass('active');
                        MyGMap.filterByCategory(this);
                        return false;
                });
        },
        filterByCategory: function(e) {
                'use strict';
                $.ajax({
                        type: 'get',
                        url: $(e).attr('data-ajax-url'),
                        dataType: 'json',
                        success: function(response) {
                                MyGMap.populateMarkers(response.data, response.isAdmin);
                        },
                        beforeSend: function() {
                                MyUtils.startBlockUI('Loading...');
                        },
                        complete: function() {
                                MyUtils.stopBlockUI();
                        },
                        error: function() {
                        }
                });
                return false;
        },
        /**
         * Populate  markers
         * @params data: json
         */
        populateMarkers: function(data, isAdmin) {
                'use strict';
                if (typeof isAdmin == 'undefined')
                        isAdmin = false;
                //delete any overlays
                MyGMap.deleteOverlays();
                MyGMap.infowindowContents = [];
                $.each(data, function(index) {
                        if (data[index].lat && data[index].lng) {
                                var infoboxContent = '';
                                if (isAdmin)
                                        infoboxContent += '<p><a href=\"' + data[index].row_url + '\" target=\"_blank\">' + data[index].name + '</a></p>';
                                else
                                        infoboxContent += '<p>' + data[index].name + '</p>';
                                infoboxContent += '<p>' + '<i class="icon-map-marker"></i> ' + data[index].address + '</p>';
                                infoboxContent += '<p>' + data[index].phone + '</p>';
                                MyGMap.placeMarker(data[index].lat, data[index].lng, data[index].color_code, data[index].id);
                                MyGMap.infowindowContents[data[index].id] = infoboxContent;
                        }
                });
        },
        /**
         * initialize directions map
         */
        initDirectionMap: function(lat, lng, options) {
                MyGMap.init(lat, lng, options);
                MyGMap.directionsDisplay = new google.maps.DirectionsRenderer();
                MyGMap.directionsDisplay.setMap(MyGMap.map);
                MyGMap.directionsDisplay.setPanel(document.getElementById(MyGMap.directionsPanel));
                //directionsDisplay.suppressMarkers = true;
        },
        /**
         * calculate route
         */
        calcRoute: function() {
                'use strict';
                var showLoading = function() {
                        $('#' + MyGMap.mapCanvasID + '_loading').removeClass('hidden');
                        $('#' + MyGMap.mapCanvasID).css({"opacity": "0.5"});
                },
                        hideLoading = function() {
                                $('#' + MyGMap.mapCanvasID + '_loading').addClass('hidden');
                                $('#' + MyGMap.mapCanvasID).css({"opacity": "1"});
                        };
                showLoading();
                var start = document.getElementById(MyGMap.directionPointAId).value;
                var end = document.getElementById(MyGMap.directionPointBId).value;
                if (!$.trim(start) || !$.trim(end)) {
                        var msg = 'Point A (From) and Point B (To) must be filled!';
                        $('#' + MyGMap.mapCanvasID + '_error').html(msg).removeClass('hidden');
                        hideLoading();
                        return false;
                }
                var request = {
                        origin: start,
                        destination: end,
                        travelMode: google.maps.TravelMode[MyGMap.directionTravelMode]
                };
                MyGMap.directionsService.route(request, function(result, status) {
                        if (status == google.maps.DirectionsStatus.OK) {
                                $('#' + MyGMap.directionsPanel).removeClass('hidden');
                                MyGMap.directionsDisplay.setDirections(result);
                                $('#' + MyGMap.mapCanvasID + '_direction_form_container').addClass('hidden');
                        }
                        else {
                                var msg = 'Could not find the direction. Try another location';
                                $('#' + MyGMap.mapCanvasID + '_error').html(msg).removeClass('hidden');
                        }
                        //hideloadng
                        hideLoading();
                });
                //hide error
                $('#' + MyGMap.mapCanvasID + '_error').addClass('hidden');
                return false;
        },
        //toggle get directions form
        toggleGetDirectionsForm: function() {
                'use strict';
                $('#' + MyGMap.getDirectionsFormContainer).toggle();
                return false;
        },
        /**
         * show info window
         * @param marker : The marker
         * @param infoboxContent: The infobox Content
         */
        ShowInfoWindow: function(marker, id) {
                'use strict';
                //remove any info window opened
                MyGMap.RemoveInfoWindow();
                var content;
                if (!id || !MyGMap.infowindowContents[id]) {
                        content = MyGMap.reverseGeocode(marker);
                }
                else {
                        content = MyGMap.infowindowContents[id];
                }
                if (!content || content == '' || typeof content == 'undefined')
                        return false;
                var options = {
                        content: content,
                        maxWidth: 200,
                        pixelOffset: new google.maps.Size(0, 20)
                };
                MyGMap.infowindow = new google.maps.InfoWindow(options);
                MyGMap.infowindow.open(MyGMap.map, marker);
        },
        /**
         * remove info window
         */
        RemoveInfoWindow: function() {
                if (MyGMap.infowindow)
                        MyGMap.infowindow.close();
                return false;
        },
        filterRequests: function(elem) {
                'use strict';
                //ajax request
                $.ajax({
                        type: 'post',
                        url: elem.href,
                        dataType: 'json',
                        success: function(data) {
                                //console.log(data);
                                MyGMap.populateMarkers(data);
                        },
                        beforeSend: function() {
                                $('#' + MyGMap.mapCanvasID + '_loading').removeClass('hidden');
                                $('#' + MyGMap.mapCanvasID).css({"opacity": "0.5"});
                        },
                        complete: function() {
                                $('#' + MyGMap.mapCanvasID + '_loading').addClass('hidden');
                                $('#' + MyGMap.mapCanvasID).css({"opacity": "1"});
                                ;
                        },
                        error: function(XHR) {
                                alert(XHR.responseText);
                        }
                });
                return false;
        },
        geocode: function(markerColor) {
                'use strict';
                var place = $('#' + MyGMap.geocode_loc_field_id).val();
                $.ajax({
                        type: 'get',
                        url: MyGMap.geocodeUrl,
                        dataType: 'json',
                        data: 'address=' + place,
                        success: function(response) {
                                //console.log(response);
                                if (response) {
                                        //$('#mipages_geocode_warning').hide();
                                        var position = new google.maps.LatLng(response.lat, response.lng);
                                        MyGMap.placeGeocodeMarker(position, markerColor, false);
                                }
                                else {
                                        //$('#mipages_geocode_warning').html();
                                        //$('#mipages_geocode_warning').show();
                                }
                        },
                        beforeSend: function() {
                                MyUtils.startBlockUI('Loading...');
                        },
                        complete: function() {
                                MyUtils.stopBlockUI();
                        },
                        error: function() {
                        }
                });
                return false;
        },
        initGeocode: function(markerColor, lat, lng) {
                //place marker when clicked
                if (lat && lng) {
                        var position = new google.maps.LatLng(lat, lng);
                        MyGMap.placeGeocodeMarker(position, markerColor, false);
                }
                google.maps.event.addListener(MyGMap.map, 'click', function(event) {
                        var marker = MyGMap.placeGeocodeMarker(event.latLng, markerColor);
                });
                $('#' + MyGMap.geocode_loc_field_id).keyup(function(event) {
                        if (event.keyCode == 13) {
                                MyGMap.geocode(markerColor);
                        }
                });
                $("#search-in-map").on("click", function(event) {
                        return MyGMap.geocode(markerColor);
                });
        },
        placeGeocodeMarker: function(position, markerColor, updateLoc) {
                'use strict';
                if (typeof updateLoc == 'undefined') {
                        updateLoc = true;
                }
                MyGMap.deleteOverlays();
                var iconLink = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|' + markerColor;
                var marker = new google.maps.Marker({
                        position: position,
                        map: MyGMap.map,
                        icon: iconLink
                });
                MyGMap.map.setCenter(position);
                MyGMap.markersArray.push(marker);
                //markersArray.push(marker);
                google.maps.event.addListener(marker, 'click', function() {
                        MyGMap.ShowInfoWindow(marker);
                });
                //show info window
                MyGMap.updateLatLng(position);
                if (updateLoc)
                        MyGMap.reverseGeocode(marker, 'updateLocation');
                return marker;
        },
        updateLocation: function(location) {
                $('#' + MyGMap.geocode_loc_field_id).val(location);
        },
        updateLatLng: function(position) {
                $('#' + MyGMap.geocode_lat_field_id).val(position.lat());
                $('#' + MyGMap.geocode_lng_field_id).val(position.lng());
        },
        //reverse geocode
        reverseGeocode: function(marker, cb) {
                var latlng = marker.getPosition();
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({'latLng': latlng}, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                                if (results[0]) {
                                        var location_name = results[0].formatted_address;
                                        if (cb === 'updateLocation') {
                                                MyGMap[cb](location_name);
                                        }
                                }
                        }
                });
        }
}