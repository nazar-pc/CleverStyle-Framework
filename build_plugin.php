<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Modules builder
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
if (empty($_SERVER['QUERY_STRING'])) {
	echo 'Please, specify module name';
	return;
} elseif (!file_exists($pdir = __DIR__.'/components/plugins/'.$_SERVER['QUERY_STRING'])) {
	echo 'Can\'t build plugin, plugin directory not found';
	return;
} elseif (!file_exists($pdir.'/meta.json')) {
	echo 'Can\'t build plugin, meta information (meta.json) not found';
	return;
}
define('DIR',	__DIR__);
require_once	DIR.'/core/classes/class.h.php';
require_once	DIR.'/core/functions.php';
require_once	'Archive/Tar.php';
$version	= _json_decode(file_get_contents($pdir.'/meta.json'))['version'];
$tar		= new Archive_Tar(DIR.'/build.phar.tar');
$tar->addString('meta.json', file_get_contents($pdir.'/meta.json'));
$set_stub	= false;
if (file_exists($pdir.'/readme.html')) {
	$tar->addString('readme.html', file_get_contents($pdir.'/readme.html'));
	$set_stub	= 'readme.html';
} elseif (file_exists($pdir.'/readme.txt')) {
	$tar->addString('readme.txt', file_get_contents($pdir.'/readme.txt'));
	$set_stub	= 'readme.txt';
}
$list		= array_merge(
	get_files_list($pdir, false, 'f', true, true, false, false, true)
);
$length		= strlen($pdir.'/');
$list		= array_map(
	function ($index, $file) use ($tar, $length) {
		$tar->addString('fs/'.$index, file_get_contents($file));
		return substr($file, $length);
	},
	array_keys($list),
	$list
);
unset($length);
$tar->addString('fs.json', _json_encode(array_flip($list)));
$tar->addString('dir', $_SERVER['QUERY_STRING']);
unset($list, $tar);
$phar		= new Phar(DIR.'/build.phar.tar');
$phar->convertToExecutable(Phar::TAR, Phar::BZ2, '.phar');
unlink(DIR.'/build.phar.tar');
unset($phar);
$phar		= new Phar(DIR.'/build.phar');
if ($set_stub) {
	$phar->setStub("<?php Phar::webPhar(null, '$set_stub'); __HALT_COMPILER();");
} else {
	$meta	= _json_decode(file_get_contents($pdir.'/meta.json'));
	$phar->addFromString('index.html', isset($meta['description']) ? $meta['description'] : $meta['title']);
	unset($meta);
	$phar->setStub("<?php Phar::webPhar(null, 'index.html'); __HALT_COMPILER();");
}
$phar->setSignatureAlgorithm(PHAR::SHA512);
unset($phar);
rename(DIR.'/build.phar', DIR.'/'.str_replace(' ', '_', $_SERVER['QUERY_STRING']).'_'.$version.'.phar.php');
echo 'Done! Plugin '.$_SERVER['QUERY_STRING'].' '.$version;