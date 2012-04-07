<?php
global $Config;
$rc = &$Config->routing['current'];
//If we provide some changes, but don't save them - we'll see popup window with proposition to save changes
//In case, when we agree with saving - $_POST['subpart'] contain name of previous subpart for providing correct saving
if (isset($rc[1]) || isset($_POST['subpart'])) {
	_include(
		MFOLDER.DS.$rc[0].DS.'save.'.(isset($_POST['subpart']) ? $_POST['subpart'] : $rc[1]).'.php',
		true,
		false
	);
}