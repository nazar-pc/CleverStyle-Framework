<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Builder
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
time_limit_pause();
if (!isset($_POST['modules'][0])) {
	echo h::p('Please, specify module name');
	return;
} elseif ($_POST['modules'][0] == 'System') {
	echo h::p('Can\'t build module, System module is a part of core, it is not necessary to build it as separate module');
	return;
} elseif (!file_exists($mdir = DIR.'/components/modules/'.$_POST['modules'][0])) {
	echo h::p('Can\'t build module, module directory not found');
	return;
} elseif (!file_exists("$mdir/meta.json")) {
	echo h::p('Can\'t build module, meta information (meta.json) not found');
	return;
}
$version	= _json_decode(file_get_contents("$mdir/meta.json"))['version'];
if (file_exists(DIR.'/build.phar')) {
	unlink(DIR.'/build.phar');
}
$phar		= new Phar(DIR.'/build.phar');
$phar->addFromString('meta.json', file_get_contents("$mdir/meta.json"));
$set_stub	= false;
if (file_exists("$mdir/readme.html")) {
	$phar->addFromString('readme.html', file_get_contents("$mdir/readme.html"));
	$set_stub	= 'readme.html';
} elseif (file_exists("$mdir/readme.txt")) {
	$phar->addFromString('readme.txt', file_get_contents("$mdir/readme.txt"));
	$set_stub	= 'readme.txt';
}
$list		= array_merge(
	get_files_list($mdir, false, 'f', true, true, false, false, true)
);
$length		= strlen("$mdir/");
$list		= array_map(
	function ($index, $file) use ($phar, $length) {
		$phar->addFromString("fs/$index", file_get_contents($file));
		return substr($file, $length);
	},
	array_keys($list),
	$list
);
unset($length);
/**
 * Flip array to have direct access to files by name during extracting and installation
 */
$phar->addFromString('fs.json', _json_encode(array_flip($list)));
$phar->addFromString('dir', $_POST['modules'][0]);
unset($list);
$phar		= $phar->convertToExecutable(Phar::TAR, Phar::BZ2, '.phar.tar');
unlink(DIR.'/build.phar');
if ($set_stub) {
	$phar->setStub("<?php Phar::webPhar(null, '$set_stub'); __HALT_COMPILER();");
} else {
	$meta	= _json_decode(file_get_contents("$mdir/meta.json"));
	$phar->addFromString('index.html', isset($meta['description']) ? $meta['description'] : $meta['title']);
	unset($meta);
	$phar->setStub("<?php Phar::webPhar(null, 'index.html'); __HALT_COMPILER();");
}
$phar->setSignatureAlgorithm(PHAR::SHA512);
unset($phar);
rename(DIR.'/build.phar.tar', DIR.'/'.str_replace(' ', '_', $_POST['modules'][0])."_$version.phar.php");
echo h::p("Done! Module {$_POST['modules'][0]} $version");