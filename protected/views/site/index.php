<?php
// title
$this->pageTitle=Yii::app()->name;

// header js
Yii::app()->clientScript->registerScriptFile('/js/search.js', CClientScript::POS_HEAD);

?>

<div id="actionBar">
	<div id="authMenu">
		<div class="label">Login</div>
	</div>

	<form id="locationForm" action="" method="GET">
		<label for="searchQuery">Query:</label><input type="text" name="searchQuery" id="searchQuery" />
		<label for="band">Band:</label>
		<select name="band" id="band">
			<option value="144">2m</option>
			<option value="440">70cm</option>
		</select>
		<input type="submit" value="Search" />
	</form>
</div>

<div id="tabBox">
	<ul id="tab_list">
		<li><a href="#" name="searches" id="searches_tab" class="tab active">Recent Searches</a></li>
		<li><a href="#" name="repeaters" id="repeaters_tab" class="tab disabled">Repeaters</a></li>
		<li><a href="#" name="directions" id="directions_tab" class="tab disabled">Directions</a></li>
	</ul>

	<div id="searches_content" class="tab-content">Recent Searches</div>
	<div id="repeaters_content" class="tab-content">Repeaters List</div>
	<div id="directions_content" class="tab-content">Driving Directions</div>
</div>

<div id="map_canvas">
map canvas
</div>

<?php return; ?>

<?php if (YII_DEBUG): ?>
<a href="#" class="myButton" id="trigger">directions</a>
<?php endif; ?>

<div id="topBox">
	<form id="locationForm" action="" method="GET">
		Start Location: <input type="text" name="fromLocation" id="fromLocation" />
		Destination: <input type="text" name="toLocation" id="toLocation" />
		Band: <select name="band" id="band">
			<option value="144">2m</option>
			<option value="440">70cm</option>
		</select>
		<input type="submit" value="Search" />
	</form>

	<?php if (YII_DEBUG): ?>
	<div class="debugButtons">
		<a href="#" class="mySubButton" onclick="$().map('toggleBoxes');return false;">toggle bounding boxes</a>
		<a href="#" class="mySubButton" onclick="$().map('toggleUnusedRepeaters');return false;">toggle unused repeaters</a>
	</div>
	<?php endif; ?>

	<div id="map_canvas">
	map canvas
	</div>
</div>

<div id="tabBox">
	<ul>
		<li><a href="#" name="searches" id="searches_tab" class="tab active">Recent Searches</a></li>
		<li><a href="#" name="repeaters" id="repeaters_tab" class="tab disabled">Repeaters</a></li>
		<li><a href="#" name="directions" id="directions_tab" class="tab disabled">Directions</a></li>
	</ul>
	
	<div id="searches_content" class="tab-content">Recent Searches</div>
	<div id="repeaters_content" class="tab-content">Repeaters List</div>
	<div id="directions_content" class="tab-content">Driving Directions</div>
</div>
