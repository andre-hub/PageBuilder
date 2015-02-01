<?php
function FirstUpperCase($value)
{
	return ucfirst(strtolower($value));
}

function BeginWith($checkStr, $searchStr) {
	if (substr($checkStr, 0, strlen($searchStr)) == $searchStr) {
		return true;
	}
	return false;
}

function EndWith($checkStr, $searchStr, $endPos=1) {
	if (substr($checkStr, strlen($checkStr)-$endPos,strlen($checkStr)) == $searchStr) {
		return true;
	}
	return false;
}
?>