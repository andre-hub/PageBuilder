<?php
function CaseParser($pageTree,$txtMarkdown) {
	$parseTags = $pageTree['ParseTags'];
	while ($parseTags) {
		$part=array_pop($parseTags);
		if ($part==null) {
            continue;
		}
		switch ($part) {
			case 'NAV':
				$pageTree['PageHtml'] = str_replace('{'.$part.'}',MakeHTMLMenu($pageTree),$pageTree['PageHtml']);
				break;

			case 'TEXT':
				$txtMarkdown = MergeHtml($txtMarkdown,'SITEURL',MakeUrl($pageTree['SiteUrl']),false);
				$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'],$part,$txtMarkdown);
				break;

			case 'SITEURL':
				$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'],$part,MakeUrl($pageTree['SiteUrl']),false);
				break;

			case 'SITENAME':
				$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'],$part,$pageTree['SiteName'],false);
				break;
			
			case 'SITETITLE':
				$startpageFilter = StartpageFilter();
				if (in_array(strtolower($pageTree['SiteTitle']), $startpageFilter)) {
					$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'],$part,'',false);
					break;
				}
				$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'],$part,$pageTree['SiteTitle'],false);
				break;

			default:
				$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'],$part,'');
				break;
		}
	}
	return $pageTree;
}

function ParseFilter() {
	return array('agb', 'impressum','imprint');
}

function StartpageFilter() {
	return array('index','home','startseite');
}

function MakeUrl($siteUrl = '', $isHttps = false) {
	if (!EndWith($siteUrl,'/') && !EndWith($siteUrl,'html',4)) {
		$siteUrl .= '/';
	}
	$siteUrl = AddHttp($siteUrl,$isHttps);
	return $siteUrl;
}

function AddHttp($siteUrl,$isHttps) {
	$http = 'http://';
	$https = 'https://';
	if (!BeginWith($siteUrl,$http) && !BeginWith($siteUrl,$https)) {
		$siteUrl = $isHttps === true ? $https . $siteUrl : $http . $siteUrl;
	}
	return $siteUrl;
}

function MakeHTMLMenu ($pageTree) {
	$nav = '';
	$pages = getPathEntry($pageTree['SourcePath']);
	while ($pages) {
		$pagePart=array_pop($pages);
		$nav .= MakeNav($pagePart,$pages,$pageTree['SiteUrl']);
	}
	return $nav;
}

function MakeNav($pagePart,$pages,$siteUrl) {
	$filter = ParseFilter();
	$startpageFilter = StartpageFilter();
	if (in_array($pagePart, $filter)) {
		return;
	}
	if (BeginWith($pagePart, FilenamePrefix())) {
		return;
	}
	if (!in_array($pagePart, $startpageFilter)) {
		$url = MakeUrl($siteUrl).$pagePart;
	} else  {
		$url = MakeUrl($siteUrl);
	}
	return '<li><a href="'.$url.'">'.ucfirst(strtolower($pagePart)).'</a></li>';
}

function MergeHtml($html,$part,$txt='',$filter=true) {
	$parser = new ParsedownExtra();
	if((strlen($html)==0) || (strlen($txt) == 0)) {
		return str_replace('{'.$part.'}','',$html);
	}
	if ($filter===true) {
		$tmpTxt =  $parser->text($txt);
	} else {
		$tmpTxt = $txt;
	}
	$tmpTxt = str_replace('{'.$part.'}',$tmpTxt,$html);

	return $tmpTxt;
}

function GetTxt($Source,$id=0) {
	$tmpTxt = '';
	if (!(@is_array($Source))) {
		return $tmpTxt; 
	}
	if ($id > 0) {
		$tmpTxt = trim($Source[$id]);
	} else{
		$tmpTxt = trim($Source[0]);
	}
	return $tmpTxt;
}

function FilenamePrefix() {
	$filePrefix = '_';
	return $filePrefix;
}

function AddFilenameExtension($filename) {
	$extension = 'html';
	return $filename . '.' . $extension;
}

function RemovePrefixFilename($filename) {
	if (BeginWith($filename, FilenamePrefix())) {
		$filename = substr($filename, strlen(FilenamePrefix()), strlen($filename));
	}
	return $filename;
}

function ParseFiles($pageTree, $pagePart='' ) {
	if ((IsDirSpecials($pagePart)) && (strlen(trim($parsedFileName)) < 5 )) {
		return;
	}
	$parsedFileName = RemovePrefixFilename($pagePart);
	$pageTree['SiteTitle']=FirstUpperCase($parsedFileName);

	$parsedFileName = AddFilenameExtension($parsedFileName);

	$txtMarkdown = loadTplFile($pageTree['SourcePath'],$pagePart);
	showLog($parsedFileName);
	$pageTree = caseParser($pageTree,$txtMarkdown);

	$pluginPath = 'plugins';
	$activePlugins = GetAllowPlugins($pluginPath);
	$pageTree = PluginsLoader($pluginPath,$activePlugins, $pageTree);

	FileWrite($pageTree['BuildPagePath'],$parsedFileName,$pageTree['PageHtml']);
}

function GetAllowPlugins($path) {
	$filePrefix = 'plugin';
	$pluginFilenames = getPathEntry($path);
	$toLoadPlugins = PluginsCfgLoad();
	$activePlugins = array();
	while ($pluginFilenames) {
		$pluginFilename = array_pop($pluginFilenames);
		if ($pluginFilename==null) {
			return $activePlugins;
		}
		if (BeginWith($pluginFilename, $filePrefix)) {
			$pluginName = str_replace($filePrefix, '', $pluginFilename);
			$pluginName = str_replace('_', '', $pluginName);
			$activePlugins[] = $pluginName;
		}
	}
	return $activePlugins;
}

function PluginsLoader($path, $activePlugins, $pageTree) {
	while ($activePlugins) {
		$pluginName=array_pop($activePlugins);
		if ($pluginName==null) {
			return;
		}
		$pageTree['PageHtml'] = PluginStart($path,$pluginName, $pageTree['PageHtml']);
	}
	return $pageTree;
}

function PluginStart($path,$pluginName, $pageHtml) {
	$prefix = 'plugin';
	using($prefix . '_' . $pluginName,$path);
	$pluginSettings = PluginsSettings();
	$pageHtml = call_user_func($prefix . $pluginName,$pluginSettings,$pageHtml);
	return $pageHtml;
}

function BuildPageDir($BuildPagePath){
	DirMake($BuildPagePath);
}
?>