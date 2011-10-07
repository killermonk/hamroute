$(document).ready(function(){
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
		var band = (Math.random() < .5) ? "144" : "440";
		var extra = [];
			extra[0] = band;
		
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
});
