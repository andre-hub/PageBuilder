<?php
function PluginShare($pluginSettings,$pageHtml) {
	return PluginShareParser($pluginSettings, $pageHtml);
}

function PluginShareParser($pluginSettings, $pageHtml) {
	while ($pluginSettings) {
		$setting=array_pop($pluginSettings);
		if ($setting==null) {
            continue;
		}

		foreach ($setting as $key => $value) {
			$pageHtml = MergeHtml($pageHtml,strtoupper($key),$value,false);
		}
	}
	return $pageHtml;
}
?>