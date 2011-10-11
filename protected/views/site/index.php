<?php
// title
$this->pageTitle=Yii::app()->name;

// header js
Yii::app()->clientScript->registerScriptFile('js/search.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile('js/resultBox.js', CClientScript::POS_HEAD);

?>

<!-- TODO move to its own file -->
<script type="text/javascript">
	$(document).ready(function(){
		$('#newTabBox a.tab').click(function(){
			$('#newTabBox a.active').removeClass('active');
			$(this).addClass('active');
			
			$('#newTabBox .tab-content').slideUp();
			
			var content = $(this).attr('href');
			$(content+"_content").slideDown();
			
			return false;
		});
	});
</script>

<a href="#" class="myButton" id="trigger">directions</a>

<div id="topBox">
	<form id="locationForm" action="" method="GET">
		Start Location: <input type="text" name="fromLocation" id="FromLocation" />
		Destination: <input type="text" name="toLocation" id="ToLocation" />
		Band: <select name="band" id="band">
			<option value="144">2m</option>
			<option value="440">70cm</option>
		</select>
		<input type="submit" value="Search" />
	</form>

	<div class="rightContainer">
		<div class="recentSearchBox">
			<h3>Recent Searches</h3>
			<div id="recentSearches" style="margin: 0px;">Loading...</div>
		</div>

		<div class="debugButtons">
			<a href="#" class="mySubButton" onclick="$().map('toggleBoxes');return false;">toggle bounding boxes</a><br />
			<a href="#" class="mySubButton" onclick="$().map('toggleUnusedRepeaters');return false;">toggle unused repeaters</a>
		</div>
	</div>

	<div id="map_canvas">
	map canvas
	</div>
</div>

<div id="newTabBox">
	<ul>
		<li><a href="#repeaters" id="repeaters_tab" class="tab active">Repeaters</a></li>
		<li><a href="#directions" id="directions_tab" class="tab">Directions</a></li>
	</ul>
	
	<div id="repeaters_content" class="tab-content">My First Tab Content</div>
	<div id="directions_content" class="tab-content">My Second Tab Content</div>
</div>

<div id="bottomBox">
	<div id="RepeatersBtn">
		<a href="javascript:DivSwitch('Repeaters','folder'); hideBG('DirectionsBtn', 'url(/images/RightBtnOn.png) no-repeat top left', 'RepeatersBtn', 'url(/images/LeftBtnOn.png) no-repeat');">Repeaters</a>
	</div>
	<div id="DirectionsBtn">
		<a href="javascript:DivSwitch('Directions','folder'); hideBG('RepeatersBtn', 'url(/images/LeftBtnOn.png) no-repeat', 'DirectionsBtn', 'url(/images/RightBtnOn.png) no-repeat top left');">Directions</a>
	</div>
	<div id="folder">
		<div id="Repeaters" class="defaultDiv"></div>
		<div id="Directions" style="display:none;"></div>
	</div>
</div>
