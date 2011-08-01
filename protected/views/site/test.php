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
	$('#map_canvas').map();"
	.$content."
});", 
CClientScript::POS_HEAD);

?>

<div id="map_canvas" style="width:100%; height:400px;">map_canvas</div>
<div onclick="$().map('drawRoute', 'provo', 'orem');">directions</div>
<?php
foreach($repeaters as $key => $repeater) {
	echo "<div onclick=\"$().map('toggleRepeater', {$key});\">toggle {$key}</div>";
}
?>
