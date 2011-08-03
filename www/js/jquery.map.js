(function( $ ){
	
	var map;
	var box;
	var directionsDisplay = new google.maps.DirectionsRenderer();
	var FILLOPACITY = .2;
	var markerArray = [];
	var polygonArray = [];
	var latLngArray = [];
	
	var methods = {
	
		// draw map
		init : function() {
			//
			var utah = new google.maps.LatLng(40.1135, -111.8535);
			var myOptions = {
				zoom:6,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				center: utah
			}
			map = new google.maps.Map(document.getElementById(this.attr('id')), myOptions);
			//
		},
		
		clearMap : function() {
			// empty box
			$(box).empty();
			for (var i in markerArray) {
				// remove markers
				markerArray[i].setMap(null);
				// remove polygons
				polygonArray[i].setMap(null);
			}
			// remove direction polyline
			directionsDisplay.setMap(null);
			// empty arrays
			markerArray.length = 0;
			polygonArray.length = 0;
			latLngArray.length = 0;
		},

		// draw route
		drawRoute : function(start, end) {
			// clear map
			methods.clearMap();
			// assign content box
			box = document.getElementById(this.attr('id'));
			// draw route
			var directionsService = new google.maps.DirectionsService();
			var request = {
				origin:start,
				destination:end,
				travelMode: google.maps.TravelMode.DRIVING
			};
			directionsDisplay.setMap(map);
			directionsService.route(request, function(result, status) {
				if (status == google.maps.DirectionsStatus.OK) {
					directionsDisplay.setDirections(result);
					// send polyLoine
					methods.getRepeaters(result.routes[0].overview_path);
				}
			});
		},
		
		// get repeaters (ajax)
		getRepeaters : function(polyLine) {
			// show loading
			$(box).append('<div id="loading">Loading</div>');
			// get repeaters
			$.ajax({
				url: '/ajax/getRepeaters',
				type: "POST",
				data: {polyline: polyLine.join('|')},
				dataType: 'json',
				success: function(response) {
					// update status element
					//$('#status').html(response);
					//alert(response[1]['repeater_id']);
					methods.parseRepeaters(response);
				}
			});
		},
		
		// add repeater for each object
		parseRepeaters : function(repeatersObj) {
			// remove loading
			$('div').remove('#loading');
			for (var i in repeatersObj) {
				methods.addRepeater(repeatersObj[i]);
			}
		},

		// draw repeater (marker / info box / coverage polygon)
		addRepeater: function(repeaterObj) {
			// marker
			var myLatlng = new google.maps.LatLng(repeaterObj['location'][0]['lat'], repeaterObj['location'][0]['lon']);
			markerArray[repeaterObj['id']] = new google.maps.Marker({
				position: myLatlng,
				map: map,
				title: "title"
			});
			latLngArray[repeaterObj['id']] = myLatlng;
			// info box
			var contentString = 'content';
			var infowindow = new google.maps.InfoWindow({
				content: contentString
			});
			google.maps.event.addListener(markerArray[repeaterObj['id']], 'click', function() {
				infowindow.open(map,markerArray[repeaterObj['id']]);
			});
			// coverage polygon
			polygonArray[repeaterObj['id']] = new google.maps.Polygon({
				fillColor : 'black', 
				fillOpacity : FILLOPACITY, 
				map: map,
				paths: methods.makeMVCArray(repeaterObj['coverage'])
			});
			// add toggle to box
			$(box).append("<div onclick=\"$().map('toggleRepeater', "+repeaterObj['id']+");\">toggle "+repeaterObj['id']+"</div>");
		},
		
		makeMVCArray: function(coverageObj) {
			var points = new Array();
			for (var i in coverageObj[0]) {
				points.push(new google.maps.LatLng(coverageObj[0][i]['lat'], coverageObj[0][i]['lon']));
			}
			return points;
		},
						
		toggleRepeater: function(id) {
			if (markerArray[id].getVisible()) {
				markerArray[id].setVisible(false);
				polygonArray[id].setOptions({
					fillOpacity : 0, 
					strokeOpacity : 0
				});
			}
			else {
				markerArray[id].setVisible(true);
				polygonArray[id].setOptions({
					fillOpacity : FILLOPACITY, 
					strokeOpacity : 1
				});
				map.panTo(latLngArray[id]);
			}
		}
		
	};

	$.fn.map = function( method ) {

		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} 
		else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} 
		else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.map' );
		}    

	};

})( jQuery );