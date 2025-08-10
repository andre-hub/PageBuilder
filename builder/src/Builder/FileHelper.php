<?php
function GetpathEntry($path) {
	if (!is_dir($path)) {
		return;
	}
	$entryList=array();
	if ($handle = opendir($path)) {
		while (false !== ($pathEntry = readdir($handle))) {
			$entryList = ToPathList($entryList, $pathEntry);
		}
	}
	asort($entryList);
	return $entryList;
}

function ToPathList($entryList, $pathEntry) {
	if (IsDirSpecials($pathEntry)){
		return $entryList;
	}
	$subpathEntry = getTxt(explode('.',$pathEntry));
	array_push($entryList, $subpathEntry);
	return $entryList; 
}

function LoadTplFile($buildSrcPath,$filename) {
	if (substr_count($buildSrcPath,'html') > 0 ) {
		$filename = FileWithExt($filename, '.html');
	} else {
		$filename = FileWithExt($filename, '.markdown');
	}
	$srcHtml = FileRead($buildSrcPath,$filename);
	return trim($srcHtml);
}

function FileWithExt($filename, $ext) {
	if (substr_count($filename,$ext) == 0 ) {
		$filename .= $ext;
	}
	return $filename;
}

function FileRead($path,$filename) {
	$fileContent = file_get_contents($path . $filename);
	return $fileContent;
}

function FileWrite($path,$filename,$fileContent,$add=false) {
	if ($add) {
		$return = file_put_contents($path.$filename, $fileContent, FILE_APPEND | LOCK_EX);
	} else {
		$return = file_put_contents($path.$filename, $fileContent, LOCK_EX);
	}
	return $return;	
}

function DirMake($path){
	if (DeleteDir($path)) {
		mkdir($path,0755);
	}
}

function DeleteDir($path) {
	$pathsep = '/';
	$path = str_replace($pathsep.$pathsep, $pathsep, $path);
	if(!is_dir($path) && is_file($path)) {
		unlink($path);
		return false;
	}
	foreach (scandir($path) as $item) {
	    if (IsDirSpecials($item)) {
	        continue;
	    }
	    if (!DeleteDir($path . $item)) {
	        return false;
	    }
	}
	rmdir($path);
	return true;
}

function IsDirSpecials($path) {
	return $path == '.' || $path == '..' || $path == '';
}
?>