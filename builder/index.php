<?php
// use "using('libFilename');" for other libfiles include
define('DEBUG', true);

require_once 'src/include.php';

// start builder
BuilderMain();

// show Debug Infos (render time)
showRunTimer($RUNTIMER);
?>