<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Builder
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
if (!isset($_POST['modules'][0])) {
	echo h::p('Please, specify module name');
	return;
} elseif ($_POST['modules'][0] == 'System') {
	echo h::p('Can\'t build module, System module is a part of core, it is not necessary to build it as separate module');
	return;
} elseif (!file_exists($mdir = DIR.'/components/modules/'.$_POST['modules'][0])) {
	echo h::p('Can\'t build module, module directory not found');
	return;
} elseif (!file_exists($mdir.'/meta.json')) {
	echo h::p('Can\'t build module, meta information (meta.json) not found');
	return;
}
$version	= _json_decode(file_get_contents($mdir.'/meta.json'))['version'];
$tar		= new Archive_Tar(DIR.'/build.phar.tar');
$tar->addString('meta.json', file_get_contents($mdir.'/meta.json'));
$set_stub	= false;
if (file_exists($mdir.'/readme.html')) {
	$tar->addString('readme.html', file_get_contents($mdir.'/readme.html'));
	$set_stub	= 'readme.html';
} elseif (file_exists($mdir.'/readme.txt')) {
	$tar->addString('readme.txt', file_get_contents($mdir.'/readme.txt'));
	$set_stub	= 'readme.txt';
}
$list		= array_merge(
	get_files_list($mdir, false, 'f', true, true, false, false, true)
);
$length		= strlen($mdir.'/');
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
$tar->addString('dir', $_POST['modules'][0]);
unset($list, $tar);
$phar		= new Phar(DIR.'/build.phar.tar');
$phar->convertToExecutable(Phar::TAR, Phar::BZ2, '.phar');
unlink(DIR.'/build.phar.tar');
unset($phar);
$phar		= new Phar(DIR.'/build.phar');
if ($set_stub) {
	$phar->setStub("<?php Phar::webPhar(null, '$set_stub'); __HALT_COMPILER();");
} else {
	$meta	= _json_decode(file_get_contents($mdir.'/meta.json'));
	$phar->addFromString('index.html', isset($meta['description']) ? $meta['description'] : $meta['title']);
	unset($meta);
	$phar->setStub("<?php Phar::webPhar(null, 'index.html'); __HALT_COMPILER();");
}
$phar->setSignatureAlgorithm(PHAR::SHA512);
unset($phar);
rename(DIR.'/build.phar', DIR.'/'.str_replace(' ', '_', $_POST['modules'][0]).'_'.$version.'.phar.php');
echo h::p('Done! Module '.$_POST['modules'][0].' '.$version);