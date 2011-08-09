(function( $ ){
	
	var map;
	var box;
	var repeaterBand;
	var directionsDisplay = new google.maps.DirectionsRenderer({draggable:true});
	var boxArray = [];
	var latLngArray = [];
	var markerArray = [];
	var polygonArray = [];
	var FILLOPACITY = 0;
	
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
			// directions change
			google.maps.event.addListener(directionsDisplay, 'directions_changed', function() {  
				methods.clearMap(true);  
				methods.getRepeaters(directionsDisplay.getDirections().routes[0].overview_path);
			});
		},
		
		clearMap : function(leavePath) {
			leavePath = typeof(leavePath) != 'undefined' ? leavePath : false;
			// empty box
			$(box).empty();
			for (var i in markerArray) {
				// remove markers
				markerArray[i].setMap(null);
				// remove polygons
				polygonArray[i].setMap(null);
			}
			// remove direction polyline
			if(leavePath != true){
				directionsDisplay.setMap(null);
			}
			// remove bounding boxes
			methods.clearBoxes();
			// empty arrays
			markerArray.length = 0;
			polygonArray.length = 0;
			latLngArray.length = 0;
		},

		// draw route
		drawRoute : function(start, end, band) {
			repeaterBand = band;
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
			directionsService.route(request, function(result, status) {
				if (status == google.maps.DirectionsStatus.OK) {
					directionsDisplay.setMap(map);
					directionsDisplay.setDirections(result);
				}
			});
		},
		
		// get repeaters (ajax)
		getRepeaters : function(polyLine) {
			// turn polyLine into array of rectangles
			var routeBoxer = new RouteBoxer();
			var boxes = routeBoxer.box(polyLine, 1);
			methods.drawBoxes(boxes);

			var pathBoxes = [];
			for (var i=0; i<boxes.length; i++)
			{
				pathBoxes.push(boxes[i].toUrlValue().split(','));
			}
			
			// show loading
			$(box).append('<div id="loading">Loading</div>');
			// get repeaters
			$.ajax({
				url: '/ajax/getRepeaters',
				type: "POST",
				data: {
					boxes: pathBoxes,
					band: repeaterBand
				},
				dataType: 'json',
				success: function(response) {
					methods.parseRepeaters(response);
				}
			});
		},
		
		// temp function to show new bounding boxes
		drawBoxes : function(boxes) {
			boxArray = Array(boxes.length);
			for (var i = 0; i < boxes.length; i++) {
				boxArray[i] = new google.maps.Rectangle({
				bounds: boxes[i],
				fillOpacity: 0,
				strokeOpacity: 1.0,
				strokeColor: '#000000',
				strokeWeight: 1,
				clickable: false,
				map: map
			});
			}
		},
		
		// temp function to clean up drawBoxes
		clearBoxes : function() {
			if (boxArray != null) {
				for (var i = 0; i < boxArray.length; i++) {
					boxArray[i].setMap(null);
				}
			}
			boxArray = null;
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
				strokeWeight : 1,
				clickable: false,
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
			return null;
		}    

	};

})( jQuery );