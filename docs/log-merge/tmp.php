<?php

$file = realpath( $argv[1] );

xdebug_start_code_coverage(XDEBUG_CC_DEAD_CODE);
include $file;

$coverage = xdebug_get_code_coverage();

var_export( $coverage[$file] );
?>