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
        map: null
        ,
        /**
         *infowindow contents
         */
        infowindow_contents: []
        ,
        /**
         *infowindow
         */
        infowindow: null
        ,
        options: {
                lat: null,
                lng: null,
                map_wrapper_id: 'map_canvas',
                geocode_url: null,
                lat_field_id: null,
                lng_field_id: null,
                address_field_id: null,
                address_search_field_id: null,
                mapType: 'ROADMAP',
                zoom: 16,
                panControl: true,
                zoomControl: true,
                scaleControl: true,
                marker_color: 'FF0000',
        }
        ,
        init: function(options) {
                'use strict';
                var $this = this
                        , map_options;
                $this.options = $.extend({}, $this.options, options || {});
                //set the map options
                map_options = {
                        zoom: $this.options.zoom,
                        panControl: $this.options.panControl,
                        zoomControl: $this.options.zoomControl,
                        scaleControl: $this.options.scaleControl,
                        mapTypeId: google.maps.MapTypeId[$this.options.mapType]
                };
                //set the center
                if ($this.options.lat && $this.options.lng) {
                        var position = new google.maps.LatLng($this.options.lat, $this.options.lng);
                        map_options.center = position;
                }
                //create the map
                $this.map = new google.maps.Map(document.getElementById($this.options.map_wrapper_id), map_options);
                //place marker when clicked
                if (position)
                        $this.place_marker(position);
                google.maps.event.addListener($this.map, 'click', function(event) {
                        $this.place_marker(event.latLng, true);
                });

                $('#' + $this.options.address_search_field_id).on('click', function() {
                        $this.geocode();
                        return false;
                });
        }
        ,
        geocode: function() {
                var $this = this
                        , address = $('#' + $this.options.address_field_id).val();

                $.ajax({
                        type: 'get',
                        url: $this.options.geocode_url,
                        dataType: 'json',
                        data: 'address=' + address,
                        success: function(response) {
                                if (response) {
                                        if (response[0]) {
                                                var location = response[0].geometry.location;
                                                $this.place_marker(new google.maps.LatLng(location.lat, location.lng));
                                        }

                                }
                        },
                        beforeSend: function() {
                                MyUtils.startBlockUI('Loading...');
                        },
                        complete: function() {
                                MyUtils.stopBlockUI();
                        },
                        error: function(response) {
                                console.log(response);
                        }
                });
                return false;
        }
        ,
        place_marker: function(position, reverse_geocode) {

                this.deleteOverlays();
                var $this = this
                        , icon = 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|' + this.options.marker_color
                        , marker = new google.maps.Marker({
                                position: position,
                                map: $this.map,
                                icon: icon
                        });

                $this.map.setCenter(position);
                $this.markersArray.push(marker);
                //markersArray.push(marker);
                google.maps.event.addListener(marker, 'click', function() {
                        $this.show_info_window(marker);
                });
                //show info window
                $this.update_lat_lng(position);
                if (!MyUtils.empty(reverse_geocode))
                        $this.reverse_geocode(marker, true);
                return marker;
        }
        ,
        /**
         * Delete overlays (markers)
         */
        deleteOverlays: function() {
                var $this = this;
                if ($this.markersArray) {
                        for (i in $this.markersArray) {
                                $this.markersArray[i].setMap(null);
                        }
                        $this.markersArray.length = 0;
                }
        }
        ,
        update_location: function(location) {
                var $this = this;
                $('#' + $this.options.address_field_id).val(location);
        }
        ,
        update_lat_lng: function(position) {
                var $this = this;
                $('#' + $this.options.lat_field_id).val(position.lat());
                $('#' + $this.options.lng_field_id).val(position.lng());
        }
        ,
        //reverse geocode
        reverse_geocode: function(marker) {
                var $this = this
                        , latlng = marker.getPosition()
                        , geocoder = new google.maps.Geocoder();

                geocoder.geocode({latLng: latlng}, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                                if (results[0]) {
                                        console.log(results[0]);
                                        var location_name = results[0].formatted_address;
                                        if (location_name)
                                                $this.update_location(location_name);
                                        //$this[update_address](location_name);
                                }
                        }
                });
        }
        ,
        show_info_window: function(marker, id) {
                var $this = this
                        , content;
                //remove any info window opened
                $this.remove_info_window();

                if (MyUtils.empty(id) || MyUtils.empty($this.infowindow_contents[id])) {
                        content = $this.reverse_geocode(marker);
                }
                else {
                        content = $this.infowindow_contents[id];
                }
                if (MyUtils.empty(content))
                        return false;

                var options = {
                        content: content,
                        maxWidth: 200,
                        pixelOffset: new google.maps.Size(0, 20)
                };
                $this.infowindow = new google.maps.InfoWindow(options);
                $this.infowindow.open($this.map, marker);
        }
        ,
        remove_info_window: function() {
                var $this = this;
                if ($this.infowindow)
                        $this.infowindow.close();
                return false;
        }
        ,
}