<?php
global $Config, $Index;
$rc			= &$Config->__get('routing')['current'];
$subparts	= _json_decode(file_get_contents(MFOLDER.'/index.json'))[$rc[0]];
if (!in_array($rc[1], $subparts)) {
	$rc[2] = $rc[1];
	$rc[1] = $subparts[0];
}