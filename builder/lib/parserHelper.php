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
				$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'],$part,$txtMarkdown);
				break;

			case 'SITEURL':
				$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'],$part,MakeUrl($pageTree['SiteUrl']),false);
				break;

			case 'SITENAME':
				$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'],$part,$pageTree['SiteName'],false);
				break;
			
			case 'SITETITLE':
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

function StrBeginHas($checkStr, $searchStr, $endPos=1) {
	if (substr($checkStr, strlen($checkStr)-$endPos,strlen($checkStr)) == $searchStr) {
		return true;
	}
	return false;
}

function StrEndHas($checkStr, $searchStr, $endPos=1) {
	if (substr($checkStr, strlen($checkStr)-$endPos,strlen($checkStr)) == $searchStr) {
		return true;
	}
	return false;
}

function MakeUrl($siteUrl = '', $isHttps = false) {
	if (!StrEndHas($siteUrl,'/') && !StrEndHas($siteUrl,'html',4)) {
		$siteUrl .= '/';
	}
	$siteUrl = AddHttp($siteUrl,$isHttps);
	return $siteUrl;
}

function AddHttp($siteUrl,$isHttps) {
	$http = 'http://';
	$https = 'https://';
	if (!StrBeginHas($siteUrl,$http,7) && !StrBeginHas($siteUrl,$https, 8)) {
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
		//$tmpTxt = Markdown($txt);
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

function ParseFiles($pageTree, $pagePart='' ) {
	if(IsDirSpecials($pagePart)) {
		return;
	}
	$parsedFileName = $pagePart.'.html';

	if(strlen(trim($parsedFileName)) < 5 ){
		return;
	}

	$pageTree['SiteTitle']=ucfirst(strtolower($pagePart));

	$txtMarkdown = loadTplFile($pageTree['SourcePath'],$pagePart);
	showLog($parsedFileName);
	$pageTree = caseParser($pageTree,$txtMarkdown);
	fileWrite($pageTree['BuildPagePath'],$parsedFileName,$pageTree['PageHtml']);
}

function BuildPageDir($BuildPagePath){
	DirMake($BuildPagePath);
}
?>