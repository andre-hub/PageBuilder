<?php
function BuilderMain() {
	$pageTree = InitBuilder();
	BuildingFiles($pageTree);
}

function InitBuilder() {
	SetDebug();
	$pageTree = PageTree();
	BuildPageDir($pageTree['BuildPagePath']);
	MarkdownParserLoad($pageTree['MarkdownParser'],$pageTree['VendorLib']);
	return $pageTree;
}

function PageTree() {
	using('cfg','.');
	$pageTree = SiteCfgLoad();
	$pageHtml = LoadTplFile($pageTree['StaticPath'].'/html/','index');
	$pageTree['PageHtml'] = $pageHtml;
	$pageTree['ParseTags'] = array('NAV','SITETITLE','TEXT','SITENAME','SITEURL');
	return $pageTree;
}

function MarkdownParserLoad($markdownParser,$vendorlib){
	switch ($markdownParser) {
		case 'Markdown':
			using('Markdown', $vendorlib);
			break;
		case 'MarkdownExtra':
			using('MarkdownExtra', $vendorlib);
			break;
		case 'ParsedownExtra':
			using('Parsedown', $vendorlib);
			using('ParsedownExtra', $vendorlib);
			break;
		case 'Parsedown':
		default:
			using('Parsedown', $vendorlib);
			break;
	}
}

function BuildingFiles($pageTree) {
	$pages = getPathEntry($pageTree['SourcePath']);
	while ($pages) {
		$pagePart=array_pop($pages);
		parseFiles($pageTree, $pagePart);
	}
}

function SetDebug() {
	/* debug status */
	if(DEBUG===true){
	    ini_set ('error_reporting', E_ALL);
	    ini_set ('xdebug.collect_params', '4');
	}else{
	    ini_set ('error_reporting', 0);
	}
}
?>