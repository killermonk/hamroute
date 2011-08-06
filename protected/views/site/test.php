<?php
// title
$this->pageTitle=Yii::app()->name;

$content = '';
foreach($repeaters as $key => $repeater) {
	$content .= "$().map('addRepeater', {$key}, '{$repeater['geo_location']}', '{$repeater['geo_coverage']}');";
}

// header js
Yii::app()->clientScript->registerScript(
'docready', 
"$(document).ready(function(){
	$('#map_canvas').map();
	//$('#map_canvas').map('getRepeaters');

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
					$('#repeaters').map('drawRoute', info.start, info.end);
				});
			});
		}
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
		console.log('start locs:', startLocs, 'start', start);
		console.log('end locs:', endLocs, 'end', end);

		var trigger = $('#trigger');
		trigger.html('');
		$('<span/>', {text: 'directions from ' + start + ' to ' + end}).appendTo(trigger);

		// Log the search
		$.ajax({
			url: '/ajax/logSearch',
			type: 'POST',
			data: {
				start: start,
				end: end,
			},
			dataType: 'json',
			success: function(response) {
				if (response && response.searches)
					displayRecentSearches(response.searches);
			}
		});

		// Draw our map
		$('#repeaters').map('drawRoute', start, end);
	});
});", 
CClientScript::POS_HEAD);

?>

<!--<div id="map_canvas" style="width:100%; height:400px;">map_canvas</div>-->
<div id="trigger">directions</div>
<div style="float:right;">
	<h3 style="margin-bottom: 5px;">Recent Searches</h3>
	<div id="recentSearches">Loading...</div>
</div>
<div id="repeaters"></div>
<script type="text/javascript">
  function DivSwitch(obj,newdiv){
  if(document.getElementById){
  var el = document.getElementById(obj);
  var ar = document.getElementById(newdiv).getElementsByTagName('div');
  var def = document.getElementById('defaultDiv');
  if(el.style.display == 'none'){
  for (var i=0; i<ar.length; i=""
				ar=""[i=""].style.display = 'none'
			}
			el.style.display = 'block'
		}else=''
			el.style.display = ''
			def.style.display = 'block';
		}
	}
}
</script>
<div>
  <div style="position:absolute; left:20px; top:20px;">
    <form>
      Start Location: <input type="text" name="fromLocation" id="FromLocation" style="width:220px;" />
      Destination: <input type="text" name="toLocation" id="ToLocation" style="width:220px;" />
      <input type="submit" value="Search" />
    </form>
  </div>

  <div style="position:absolute; left:20px; top:50px; background:url('images/HAM-UI_04.png'); width:179px; height:286px;">
    recent searches
  </div>

  <div id="map_canvas" style="position:absolute; width:424px; height:286px; left:200px; top:50px; background:url('images/HAM-UI_05.png');">
    map canvas
  </div>
</div>
<div style="position:absolute; left:20px; top:350px; width:603px; height:434px; background:url('images/HAM-UI_06.png') no-repeat center;">
  <div style="position:absolute; top:0px; left:70px; font-weight:bold;">
    <a href="javascript:DivSwitch('Repeaters','folder');">Repeaters</a>
  </div>
  <div style="position:absolute; top:0px; left:195px; font-weight:bold;">
    <a href="javascript:DivSwitch('Directions','folder');">Directions</a>
  </div>
  <div id="folder" style="position:relative; top:50px; left:25px;">
    <div id="Repeaters">Repeaters List</div>
    <div id="Directions" style="display:none;">Directions</div>
  </div>
</div>
<?php
foreach($repeaters as $key => $repeater) {
	//echo "<div onclick=\"$().map('toggleRepeater', {$key});\">toggle {$key}</div>";
}
?>