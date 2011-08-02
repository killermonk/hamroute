(function( $ ){
	
	var map;
	var FILLOPACITY = .2;
	var markerArray = []
	var polygonArray = []
	var myArr = new Array("40.234787914385, -109.77052184771", "41.086650598867, -110.13086714134", "41.613130691624, -111.07426336775", "41.613130691624, -112.24036523332", "41.086650598867, -113.18376145974", "40.234787914385, -113.54410675337", "39.382925229903, -113.18376145974", "38.856445137146, -112.24036523332", "38.856445137146, -111.07426336775", "39.382925229903, -110.13086714134")

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
		
		// get repeaters (ajax)
		getRepeaters : function() {
			$.ajax({
				url: '/ajax/getRepeaters',
				dataType: 'json',
				success: function(response) {
					// update status element
					//$('#status').html(response);
					alert(response[0]['lat']);
				}
			});
		},
		
		// draw route
		drawRoute : function(start, end) {
			var directionsDisplay = new google.maps.DirectionsRenderer();
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
					methods.path(result.routes[0].overview_path);
				}
			});
		},
		
		// send geoline to php
		path : function(path) {
			//alert(path);
		},
		
		makeMVCArray: function(myArr) {
			var points = new Array();
			for (var i in myArr) {
				var LatLngArray = methods.splitLatLng(myArr[i]);
				points.push(new google.maps.LatLng(LatLngArray[0], LatLngArray[1]));
			}
			return points;
		},
		
		splitLatLng: function(LatLng) {
			var LatLngArray = LatLng.split(',');
			var lat = LatLngArray[0];
			var lng = LatLngArray[1];
			return LatLngArray;
		},
		
		addRepeater: function(id, LatLng, coverage) {
			LatLng = methods.splitLatLng(LatLng);
			var myLatlng = new google.maps.LatLng(LatLng[0], LatLng[1]);
			var contentString = 'content';
			var infowindow = new google.maps.InfoWindow({
				content: contentString
			});
			markerArray[id] = new google.maps.Marker({
				position: myLatlng,
				map: map,
				title: "title"
			});
			coverage = coverage.split("|");
			polygonArray[id] = new google.maps.Polygon({
				fillColor : 'black', 
				fillOpacity : FILLOPACITY, 
				map: map,
				paths: methods.makeMVCArray(coverage)
			});
			google.maps.event.addListener(markerArray[id], 'click', function() {
				infowindow.open(map,markerArray[id]);
			});
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