(function( $ ){
	
	var map;
	var box;
	var repeaterBand;
	var directionsPanel;
	var directionsDisplay = new google.maps.DirectionsRenderer({draggable:true});
	var hiddenRepeaters = false;
	var boxArray = [];
	var latLngArray = [];
	var markerArray = [];
	var polygonArray = [];
	var selectedRepeaters = [];
	var FILLOPACITY = .1;
	
	var methods = {
	
		/*****************
		 * basic map functionality
		 *****************/

		/**
		 * constuctor: create google map and center on utah;
		 * create listener to detect route changes
		 * @param string directions: the div id where we send driving directions
		 */
		init : function(directions)
		{
			directionsPanel = directions;
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
				directionsDisplay.setPanel(document.getElementById(directionsPanel));
				methods.buildBoxes(directionsDisplay.getDirections().routes[0].overview_path);
			});
		},
		
		/**
		 * clear google map of all objects;
		 * @param bool leavePath: if true, remove all objects except the route
		 */
		clearMap : function(leavePath) 
		{
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
			selectedRepeaters.length = 0;
			latLngArray.length = 0;
		},

		/*****************
		 * route creation / manipulation
		 *****************/

		 /**
		 * draw the driving route on the google map;
		 * @param string start: start location
		 * @param string end: end location
		 * @param int band: search for repeaters along given band; assume null as all bands
		 */
		drawRoute : function(start, end, band)
		{
			band = typeof(band) != 'undefined' ? band : null;
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
		
		 /**
		 * build boxes along given path
		 * @param Array.<LatLng> polyLine: array of LatLngs representing a route
		 */
		buildBoxes : function(polyLine)
		{
			// turn polyLine into array of rectangles
			var routeBoxer = new RouteBoxer();
			var boxes = routeBoxer.box(polyLine, 1);
			methods.drawBoxes(boxes);

			var pathBoxes = [];
			for (var i=0; i<boxes.length; i++) {
				pathBoxes.push(boxes[i].toUrlValue().split(','));
			}
			methods.getRepeaters(pathBoxes);
		},
		
		/*****************
		 * ajax
		 *****************/

		/**
		 * send boxes along path to AjaxController
		 * @param array pathBoxes: array of LatLngs representing boxes along route
		 */
		getRepeaters : function(pathBoxes)
		{
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

		/**
		 * feed json from AjaxController to addRepeater one repeter at a time
		 * @param object repeatersObj: object containing all repeaters along route
		 */
		parseRepeaters : function(repeatersObj)
		{
			// remove loading
			$('div').remove('#loading');
			for (var i in repeatersObj) {
				methods.addRepeater(repeatersObj[i]);
			}
		},

		/*****************
		 * repeater creation / management
		 *****************/

		/**
		 * draws repeaters, polygons and infowindows
		 * creates associated events
		 * @param object repeatersObj: object containing 1 repeater along route
		 */
		addRepeater : function(repeaterObj)
		{
			// marker
			var myLatlng = new google.maps.LatLng(repeaterObj['location'][0]['lat'], repeaterObj['location'][0]['lon']);
			markerArray[repeaterObj['id']] = new google.maps.Marker({
				position: myLatlng,
				map: map,
				title: "Repeater " + repeaterObj['id']
			});
			latLngArray[repeaterObj['id']] = myLatlng;
			// info box
			var ctcssString = "";
			if (repeaterObj['ctcss_in']) {
				ctcssString = "<br />CTCSS tone: " + repeaterObj['ctcss_in'];
			}
			var contentString = "<strong>Repeater " + repeaterObj['id'] + "</strong>" + " (" +
								repeaterObj['location'][0]['lat'] + ", " + repeaterObj['location'][0]['lon'] + ")<br />" +
								"Output frequency: " + repeaterObj['output'] + "<br />" +
								"Input frequency: " + repeaterObj['input'] +
								ctcssString;
			var infowindow = new google.maps.InfoWindow({
				content: contentString
			});
			// marker click
			google.maps.event.addListener(markerArray[repeaterObj['id']], 'click', function() {
				methods.repeaterClick(repeaterObj['id']);
			});
			// marker rightclick
			google.maps.event.addListener(markerArray[repeaterObj['id']], 'rightclick', function() {
				infowindow.open(map,markerArray[repeaterObj['id']]);
			});
			// marker mouseover
			google.maps.event.addListener(markerArray[repeaterObj['id']], 'mouseover', function() {
				methods.repeaterMouseover(repeaterObj['id']);
			});
			// marker mouseout
			google.maps.event.addListener(markerArray[repeaterObj['id']], 'mouseout', function() {
				methods.repeaterMouseout(repeaterObj['id']);
			});
			// coverage polygon
			polygonArray[repeaterObj['id']] = new google.maps.Polygon({
				fillColor : 'blue', 
				fillOpacity : 0, 
				strokeWeight : 1,
				strokeOpacity : 0,
				clickable: false,
				map: map,
				paths: methods.makeMVCArray(repeaterObj['coverage'])
			});
			// add toggle to box
			$(box).append("<div id=\""+repeaterObj['id']+"\" onclick=\"$().map('repeaterClick', "+repeaterObj['id']+");\" onmouseover=\"$().map('repeaterMouseover', "+repeaterObj['id']+");\" onmouseout=\"$().map('repeaterMouseout', "+repeaterObj['id']+");\">" + contentString + "<br /></div>");
		},
		
		/**
		 * converts polygon into google readable MVCArray
		 * @param object coverageObj: object containing polygon paths
		 * @return MVCArray points: google readable polygon array
		 */
		makeMVCArray : function(coverageObj)
		{
			var points = new Array();
			for (var i in coverageObj[0]) {
				points.push(new google.maps.LatLng(coverageObj[0][i]['lat'], coverageObj[0][i]['lon']));
			}
			return points;
		},

		/**
		 * select / deselect repeater on map or in list
		 * @param int id: unique identifier for repeater
		 */
		repeaterClick : function(id)
		{
			if(selectedRepeaters[id] != true){
				selectedRepeaters[id] = true;
				methods.showRepeater(id);
				$(document.getElementById(id)).addClass("selectedRepeater");
			}
			else {
				selectedRepeaters[id] = false;
				methods.hideRepeater(id);
				$(document.getElementById(id)).removeClass("selectedRepeater");
			}
		},
		
		/**
		 * highlight repeater on map or in list
		 * @param int id: unique identifier for repeater
		 */
		repeaterMouseover : function(id)
		{
			methods.showRepeater(id);
			$(document.getElementById(id)).addClass("selectedRepeater");
		},
		
		/**
		 * unhighlight repeater on map or in list
		 * @param int id: unique identifier for repeater
		 */
		repeaterMouseout : function(id)
		{
			if(selectedRepeaters[id] != true){
				methods.hideRepeater(id);
				$(document.getElementById(id)).removeClass("selectedRepeater");
			}
		},
						
		/**
		 * basic repeater visibility: true
		 * @param int id: unique identifier for repeater
		 * @param bool doPan: if true, pan map to center on shown repeater
		 */
		showRepeater : function(id, doPan)
		{
				doPan = typeof(doPan) != 'undefined' ? doPan : false;
				polygonArray[id].setOptions({
				fillOpacity : FILLOPACITY, 
				strokeOpacity : 1
			});
			if(doPan == true){
				map.panTo(latLngArray[id]);
			}
		},

		/**
		 * basic repeater visibility: false
		 * @param int id: unique identifier for repeater
		 */
		hideRepeater : function(id)
		{
			polygonArray[id].setOptions({
				fillOpacity : 0, 
				strokeOpacity : 0
			});
		},
						
		/*****************
		 * manage unused repeaters
		 *****************/

		/**
		 * toggle unused repeater visibility
		 */
		toggleUnusedRepeaters : function()
		{
			if(hiddenRepeaters == true) {
				hiddenRepeaters = false;
				methods.showUnusedRepeaters();
			}
			else {
				hiddenRepeaters = true;
				methods.hideUnusedRepeaters();
			}
		},
		
		/**
		 * basic repeater (list & marker) visibility: set hidden
		 */
		hideUnusedRepeaters : function()
		{
			for (var i in markerArray) {
				markerArray[i].setVisible(false);
				$(document.getElementById(i)).addClass("makeInvisible");
			}
			for (var i in selectedRepeaters) {
				if (selectedRepeaters[i] == true) {
					markerArray[i].setVisible(true);
					$(document.getElementById(i)).removeClass("makeInvisible");
				}
			}
		},
		
		/**
		 * basic repeater (list & marker) visibility: set visible
		 */
		showUnusedRepeaters : function()
		{
			for (var i in markerArray) {
				markerArray[i].setVisible(true);
				$(document.getElementById(i)).removeClass("makeInvisible");
			}
		},

		/*****************
		 * temporary route bounding boxes (for demo)
		 *****************/

		/**
		 * draw route bounding boxes on map / hide them
		 * @param array boxes: array of rectangles to draw on map
		 */
		drawBoxes : function(boxes)
		{
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
			methods.toggleBoxes();
		},

		/**
		 * remove route bounding boxes from map
		 */
		clearBoxes : function()
		{
			if (boxArray != null) {
				for (var i = 0; i < boxArray.length; i++) {
					boxArray[i].setMap(null);
				}
			}
			boxArray = null;
		},
		
		/**
		 * toggle visibility on route bounding boxes
		 */
		toggleBoxes : function()
		{
			if (boxArray[0].strokeOpacity != 0) {
				var boxStrokeOpacity = 0;
			}
			else {
				var boxStrokeOpacity = 1;
			}
			for (var i in boxArray) {
				boxArray[i].setOptions({
					strokeOpacity: boxStrokeOpacity,
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
			return null;
		}    

	};

})( jQuery );