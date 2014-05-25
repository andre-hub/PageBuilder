<?php
/* For security */
@ini_set( 'allow_url_fopen', 0 );
@ini_set('display_errors', 'on');
define('BUILDER', true);

/* Debug Timer */
$RUNTIMER = microtime(true);

using('builder');
using('fileHelper');
using('debug');
using('parserHelper');

/*  include lib files for classes and functions  */
function using($filename,$path='') {
	require_once  ( $path != '' ? $path : 'lib') . '/' . $filename . '.php';
}
?>