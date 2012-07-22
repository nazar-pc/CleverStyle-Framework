<?php
global $Config, $Index, $User;
$rc			= &$Config->__get('routing')['current'];
$subparts	= _json_decode(file_get_contents(MFOLDER.'/index.json'))[$rc[0]];
if (
	(
		!isset($rc[1]) && $User->is('user')
	) ||
	(
		isset($rc[1]) && !in_array($rc[1], $subparts)
	)
) {
	if (isset($rc[1])) {
		$rc[2]	= $rc[1];
	} else {
		$rc[2]	= $User->login;
	}
	$rc[1]	= $subparts[0];
}