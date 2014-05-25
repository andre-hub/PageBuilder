<?php
function showLog($output) {
	echo '<p>'.$output."</p>\n";
}

function showRunTimer($startTime) {
	if(DEBUG===true){
		$startTime = microtime(true) - $startTime;
		echo "<h3>Debug Info</h3><p>rendering time: ". round($startTime,4) . " sec</p>";
	}
}
?>