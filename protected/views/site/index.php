<?php
// title
$this->pageTitle=Yii::app()->name;

// sample css for designating selected repeaters
Yii::app()->clientScript->registerCss(
'selectedRepeaters',
'.selectedRepeater {background-color:#CFDAFF;}
.makeInvisible {display:none;}'
);

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
</script>
<div onclick="$().map('toggleBoxes');">toggle route bounding boxes (for demo)</div>
<div onclick="$().map('toggleUnusedRepeaters');">toggle unused repeaters</div>
<div id="trigger">directions</div>

<div style="position:relative; top:0px; left:0px;">
  <div style="position:relative; left:20px; top:20px; width:700px;">
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


  <div style="float:left; background:url('/images/HAM-UI_04.png'); width:179px; height:286px;">
    <div style="float:right;">
      <h3 style="margin: 15px 15px 5px 0;">Recent Searches</h3>
      <div id="recentSearches">Loading...</div>
    </div>

  </div>

  <div id="map_canvas" style="float:right; height:286px; background:url('/images/HAM-UI_05.png');">
    map canvas
  </div>
</div>

<div style="position:relative; left:20px; top:250px; width:603px; height:434px; background:url('/images/HAM-UI_06.png') no-repeat center;">
  <div style="position:relative; top:0px; left:0px; font-weight:bold; background: url('/images/LeftBtnOn.png') no-repeat top left;">
    <a href="javascript:DivSwitch('Repeaters','folder');">Repeaters</a>
  </div>
  <div style="position:relative; top:0px; left:0px; font-weight:bold; background: url('/images/RightBtnOn.png') no-repeat top left;">
    <a href="javascript:DivSwitch('Directions','folder');">Directions</a>
  </div>
  <div id="folder" style="position:relative; top:50px; left:25px;">
    <div id="Repeaters" class="defaultDiv"></div>
    <div id="Directions" style="display:none;"></div>
  </div>
</div>
