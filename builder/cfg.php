<?php
if(!BUILDER===true) {
	die("");
}
function SiteCfgLoad() {
	$cfg = array(
		'SiteName' => 'André Grötschel',
		'SiteUrl' => 'andre.groetschel.eu',
		'StaticPath' => '../static/',
		'BuildPagePath' => '../html/',
		'SourcePath' => '../pages/',
		'MarkdownParser' => 'ParsedownExtra', // Markdown, MarkdownExtra, Parsedown, ParsedownExtra
		'VendorLib' => 'vendor'
		);
	return $cfg;
}
?>