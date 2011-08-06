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

<div id="map_canvas" style="width:100%; height:400px;">map_canvas</div>
<div id="trigger">directions</div>
<div style="float:right;">
	<h3 style="margin-bottom: 5px;">Recent Searches</h3>
	<div id="recentSearches">Loading...</div>
</div>
<div id="repeaters"></div>
<?php
foreach($repeaters as $key => $repeater) {
	//echo "<div onclick=\"$().map('toggleRepeater', {$key});\">toggle {$key}</div>";
}
?>
