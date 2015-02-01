<?php
if(!BUILDER===true) {
	die("");
}
function SiteCfgLoad() {
	$cfg = array(
		'SiteName' => 'Page Builder',
		'SiteUrl' => 'pagebuilder.projekt-matrix.de',
		'StaticPath' => '../static/',
		'BuildPagePath' => '../html/',
		'SourcePath' => '../pages/',
		'MarkdownParser' => 'ParsedownExtra', // Markdown, MarkdownExtra, Parsedown, ParsedownExtra
		'VendorLib' => 'vendor'
		);
	return $cfg;
}

function PluginsCfgLoad() {
	$loadedPlugins = array('Share');
	return $loadedPlugins;
}


function PluginsSettings() {
	$loadedPlugins = array('Share' => 
			array(
				'FlattrAccount' => 'andregroetschel',
				'FlattrDescription' => 'Private Website from a developer, hobby analyst and hobby researcher.'
				)
		);
	return $loadedPlugins;
}
?>