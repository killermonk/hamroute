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
		
		// draw route
		directions : function(start, end) {
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
		
		mark: function(id, lat, lng) {
			var myLatlng = new google.maps.LatLng(lat, lng);
			var contentString = 'content';
			var infowindow = new google.maps.InfoWindow({
				content: contentString
			});
			markerArray[id] = new google.maps.Marker({
				position: myLatlng,
				map: map,
				title: "title"
			});
			var points = new Array();
			for (var i in myArr) {
				var latlngArray = myArr[i].split(',');
				var lat = latlngArray[0];
				var lng = latlngArray[1];
				points.push(new google.maps.LatLng(latlngArray[0], latlngArray[1]));
			}
			polygonArray[id] = new google.maps.Polygon({
				fillColor : 'black', 
				fillOpacity : FILLOPACITY, 
				map: map,
				paths: points
			});
			google.maps.event.addListener(markerArray[id], 'click', function() {
				infowindow.open(map,markerArray[id]);
			});
		},
		
		toggle: function(id) {
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
		},
		
		show : function( ) {    },
		hide : function( ) {  },
		update : function( content ) {  }
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