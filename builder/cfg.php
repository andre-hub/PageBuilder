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
?>