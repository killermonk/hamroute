<?php
// title
$this->pageTitle=Yii::app()->name;

// sample css for designating selected repeaters
Yii::app()->clientScript->registerCss(
'selectedRepeaters',
'.selectedRepeater {background-color:#CFDAFF;}
.allRepeaters {margin:2px;padding:6px;cursor:pointer;}
.makeInvisible {display:none;}
.myButton {
	-moz-box-shadow:inset 0px 1px 0px 0px #cae3fc;
	-webkit-box-shadow:inset 0px 1px 0px 0px #cae3fc;
	box-shadow:inset 0px 1px 0px 0px #cae3fc;
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #79bbff), color-stop(1, #4197ee) );
	background:-moz-linear-gradient( center top, #79bbff 5%, #4197ee 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#79bbff\', endColorstr=\'#4197ee\');
	background-color:#79bbff;
	-moz-border-radius:6px;
	-webkit-border-radius:6px;
	border-radius:6px;
	border:1px solid #469df5;
	display:inline-block;
	color:#ffffff;
	font-family:arial;
	font-size:15px;
	font-weight:bold;
	padding:6px 24px;
	text-decoration:none;
	text-shadow:1px 1px 0px #287ace;
}
.myButton:hover {
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #4197ee), color-stop(1, #79bbff) );
	background:-moz-linear-gradient( center top, #4197ee 5%, #79bbff 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#4197ee\', endColorstr=\'#79bbff\');
	background-color:#4197ee;
	color:#fff;
}
.myButton:active, .mySubButton:active {
	position:relative;
	top:1px;
}
.mySubButton {
	-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
	box-shadow:inset 0px 1px 0px 0px #ffffff;
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #ededed), color-stop(1, #dfdfdf) );
	background:-moz-linear-gradient( center top, #ededed 5%, #dfdfdf 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#ededed\', endColorstr=\'#dfdfdf\');
	background-color:#ededed;
	-moz-border-radius:6px;
	-webkit-border-radius:6px;
	border-radius:6px;
	border:1px solid #dcdcdc;
	display:inline-block;
	color:#777777;
	font-family:arial;
	font-size:12px;
	font-weight:bold;
	padding:6px 24px;
	text-decoration:none;
	text-shadow:1px 1px 0px #ffffff;
	margin-bottom: 4px;
}.mySubButton:hover {
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #dfdfdf), color-stop(1, #ededed) );
	background:-moz-linear-gradient( center top, #dfdfdf 5%, #ededed 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#dfdfdf\', endColorstr=\'#ededed\');
	background-color:#dfdfdf;
}.myButton:active {
	position:relative;
	top:1px;
}
');

// header js
Yii::app()->clientScript->registerScript(
'docready', 
"$(document).ready(function(){
	$('#map_canvas').map('init', 'Directions');

	var displayRecentSearches = function(searches) {
		var searchEl = $('#recentSearches');

		if (!searches || !$.isArray(searches) || searches.length == 0)
			searchEl.html('none');
		else
		{
			searchEl.html('');
			$(searches).each(function(i, info){
				var main = $('<div/>', {'class':'recentSearch'}).appendTo(searchEl);
				$('<span/>', {'class':'start', text: info.start}).appendTo(main);
				$('<span/>', {text:' to '}).appendTo(main);
				$('<span/>', {'class':'end', text: info.end}).appendTo(main);
        
				main.click(function(){
					// Change our map
					$('#FromLocation').val(info.start);
					$('#ToLocation').val(info.end);
					$('#band').val(info.extra[0]);
					$('#Repeaters').map('drawRoute', info.start, info.end, info.extra[0]);
				});
			});
		}
	};

	var logSearch = function(start, end, extra) {
		// Log the search
		$.ajax({
			url: '/ajax/logSearch',
			type: 'POST',
			data: {
				start: start,
				end: end,
				extra: extra
			},
			dataType: 'json',
			success: function(response) {
				if (response && response.searches)
					displayRecentSearches(response.searches);
			}
		});
	};

	// Load our recent searches
	$.ajax({
		url: '/ajax/getRecentSearches',
		type: 'POST',
		data: {},
		dataType: 'json',
		success: function(response) {
			if (response)
				displayRecentSearches(response);
		}
	});

	var startLocs = ['ogden', 'logan', 'price', 'roosevelt', 'vernal'];
	var endLocs = ['orem', 'provo', 'cedar city', 'moab', 'heber', 'tooele'];

	// Set what happens when we click the 'Directions' button
	$('#trigger').click(function(e){
		var start = startLocs[ Math.floor(Math.random()*100) % startLocs.length ] + ', utah';
		var end = endLocs[ Math.floor(Math.random()*100) % endLocs.length ] + ', utah';
		var band = (Math.random() < .5)?\"144\" : \"440\";
		var extra = [];
			extra[0] = band;
		
		if (console && console.log)
		{
			console.log('start locs:', startLocs, 'start', start);
			console.log('end locs:', endLocs, 'end', end);
			console.log('band:', band);
		}

		// move directions to form
		$('#FromLocation').val(start);
		$('#ToLocation').val(end);
		$('#band').val(band);

		var trigger = $('#trigger');
		trigger.html('');
		$('<span/>', {text: 'directions from ' + start + ' to ' + end}).appendTo(trigger);

		// Log the search
		logSearch(start, end, extra);

		// Draw our map
		$('#Repeaters').map('drawRoute', start, end, band);
	});

	// Capture the form submission and use it
	$('#locationForm').submit(function(){
		var start = $('#FromLocation').first().val();
		var end = $('#ToLocation').first().val();
		var band = $('#band').first().val();
		var extra = [];
			extra[0] = band;

		// Log the search
		logSearch(start, end, extra);

		// Draw our map
		$('#Repeaters').map('drawRoute', start, end, band);

		// Always handle with ajax
		return false;
	});
});", 
CClientScript::POS_HEAD);

?>

<script type="text/javascript">
	function DivSwitch(obj,newdiv){
		if(document.getElementById){
			var el = $('#'+obj).first(); //document.getElementById(obj);
			var ar = $('#'+newdiv+' > div'); //document.getElementById(newdiv).getElementsByTagName('div');
			var def = $('.defaultDiv').first(); //document.getElementById('defaultDiv');
			//if(el.style.display == 'none'){
			if (el.css('display') == 'none'){
				for (var i=0; i<ar.length; i++){
					//ar[i].style.display = 'none';
					$(ar[i]).css('display', 'none');
				}
				//el.style.display = 'block';
				el.css('display', 'block');
			}else{
				//el.style.display = '';
				el.css('display', '');
				//def.style.display = 'block';
				def.css('display', 'block');
			}
		}
	}
  
  function hideBG(offDiv, offBG, onDiv, onBG){
    if(document.getElementById){
      var elOff = $('#'+offDiv).first();
      var elOn = $('#'+onDiv).first();
      if (elOff.css('background') == offBG){
        elOff.css('background', 'none');
        elOn.css('background'), onBG);
      }
    }
  }
</script>
<a href="#" class="myButton" id="trigger">directions</a>

<div style="position:relative; top:0px; left:0px;">
	<div style="position:relative; left:2px; top:20px; width:700px;">
		<form id="locationForm" action="" method="GET">
			Start Location: <input type="text" name="fromLocation" id="FromLocation" style="width:180;" />
			Destination: <input type="text" name="toLocation" id="ToLocation" style="width:180;" />
			Band: <select name="band" id="band">
				<option value="144">2m</option>
				<option value="440">70cm</option>
			</select>
			<input type="submit" value="Search" />
		</form>
	</div>

	<div style="float:left; margin-top: 25px; background:url('/images/HAM-UI_04.png') no-repeat; width:210px; height:286px;">
		<div style="float:left; margin-left:3px; padding: 4px;">
			<h3 style="margin: 15px 15px 5px 0px;">Recent Searches</h3>
			<div id="recentSearches" style="margin: 0px;">Loading...</div>
		</div>
	</div>

	<div id="map_canvas" style="position:relative; top:31px; left:5px; height:360px; width: 540px;">
	map canvas
	</div>
	
	<div style="margin-top:-44px;">
		<a href="#" class="mySubButton" onclick="$().map('toggleBoxes');return false;">toggle bounding boxes</a><br />
		<a href="#" class="mySubButton" onclick="$().map('toggleUnusedRepeaters');return false;">toggle unused repeaters</a>
	</div>
</div>

<div style="position:relative; left:0px; top:20px; width:603px; height:434px; background:url(/images/HAM-UI_06.png) no-repeat center;">
	<div id="RepeatersBtn" style="position:relative; left:0px; top:0px; width:167px; height:28px; padding-left:65px; padding-top:5px; font-weight:bold; background: url(/images/LeftBtnOn.png) no-repeat;">       
		<a href="javascript:DivSwitch('Repeaters','folder');" onclick="javascript:hideBG('DirectionsBtn', 'url(/images/RightBtnOn.png) no-repeat top left', 'RepeatersBtn', 'url(/images/LeftBtnOn.png) no-repeat');">Repeaters</a>
	</div>
	<div id="DirectionsBtn" style="position:relative; top:-33px; left:167px; width:126px; height:28px; padding-left:20px; padding-top:5px; font-weight:bold; background: url(/images/RightBtnOn.png) no-repeat top left;">
		<a href="javascript:DivSwitch('Directions','folder');" onclick="javascript:hideBG('RepeatersBtn', 'url(/images/LeftBtnOn.png) no-repeat', 'DirectionsBtn', 'url(/images/RightBtnOn.png) no-repeat top left');">Directions</a>
	</div>
	<div id="folder" style="position:relative; top:-10px; left:25px; width:530px; height:355px; overflow:auto;">
		<div id="Repeaters" class="defaultDiv"></div>
		<div id="Directions" style="display:none;"></div>
	</div>
</div>
