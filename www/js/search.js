$(document).ready(function(){
	var resizeTabContent = function(){
		var tabBoxHeight = $('#tabBox').height();
		var tabListHeight = $('#tab_list').height();
		$('#tabBox > .tab-content').height(tabBoxHeight - tabListHeight - 15);
	};

	var resizeCanvas = function(){
		var contentHeight = $('#content').height();
		if (contentHeight)
		{
			var lessTop = $('#actionBar').height() || 45;
			$('#map_canvas').height(contentHeight - lessTop);
			$('#tabBox').height(contentHeight - lessTop - 20); // padding adjustment

			resizeTabContent();
		}
	};
	$(window).resize(resizeCanvas);
	resizeCanvas();


	$('#tabBox').tabs();
	$('#map_canvas').map('init', 'directions_content', function(){
		$('#tabBox').show().tabs('enableAll').tabs('toggleTab', '#repeaters_tab');
		resizeTabContent();
	});

	var displayRecentSearches = function(searches) {
		var searchEl = $('#searches_content');

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
					var query = info.start + ' to ' + info.end;
					$('#searchQuery').val(query);
					$('#band').val(info.extra[0]);
					$('#repeaters_content').map('drawRoute', info.start, info.end, info.extra[0]);
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

	var startLocs = ['Ogden', 'Logan', 'Price', 'Roosevelt', 'Vernal'];
	var endLocs = ['Orem', 'Provo', 'Cedar City', 'Moab', 'Heber', 'Tooele'];

	// Set what happens when we click the 'Directions' button
	$('#trigger').click(function(e){
		var start = startLocs[ Math.floor(Math.random()*100) % startLocs.length ] + ', Utah';
		var end = endLocs[ Math.floor(Math.random()*100) % endLocs.length ] + ', Utah';
		var band = (Math.random() < .5) ? "144" : "440";
		var extra = [];
			extra[0] = band;
		
		// move directions to form
		$('#fromLocation').val(start);
		$('#toLocation').val(end);
		$('#band').val(band);

		var trigger = $('#trigger');
		trigger.html('');
		$('<span/>', {text: 'directions from ' + start + ' to ' + end}).appendTo(trigger);

		// Log the search
		logSearch(start, end, extra);

		// Draw our map
		$('#repeaters_content').map('drawRoute', start, end, band);
		
		// Done, do not continue
		return false;
	});

	// Capture the form submission and use it
	$('#locationForm').submit(function(){
		var query = $('#searchQuery').first().val();

		var searchRegex = new RegExp("^(.*)\\s+to\\s+(.*)", "gi");
		var searchItems = searchRegex.exec(query);

		if (!searchItems || searchItems.length != 3)
		{
			alert("Invalid search query. Enter as follows: Denver, CO to Fresno, California");
		}
		else
		{
			var start = searchItems[1];
			var end = searchItems[2];
			var band = $('#band').first().val();
			var extra = [];
				extra[0] = band;

			// Log the search
			logSearch(start, end, extra);

			// Draw our map
			$('#repeaters_content').map('drawRoute', start, end, band);
		}

		// Always handle with ajax
		return false;
	});
});
