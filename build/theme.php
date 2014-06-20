<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     Builder
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
time_limit_pause();
if (!isset($_POST['themes'][0])) {
	echo h::p('Please, specify theme name');
	return;
} elseif ($_POST['themes'][0] == 'CleverStyle') {
	echo h::p("Can't build theme, CleverStyle theme is a part of core, it is not necessary to build it as separate theme");
	return;
} elseif (!file_exists($theme_dir = DIR.'/themes/'.$_POST['themes'][0])) {
	echo h::p("Can't build theme, theme directory not found");
	return;
} elseif (!file_exists("$theme_dir/meta.json")) {
	echo h::p("Can't build theme, meta information (meta.json) not found");
	return;
}
$version = file_get_json("$theme_dir/meta.json")['version'];
if (file_exists(DIR.'/build.phar')) {
	unlink(DIR.'/build.phar');
}
$phar = new Phar(DIR.'/build.phar');
$phar->addFromString('meta.json', file_get_contents("$theme_dir/meta.json"));
$list   = array_merge(
	get_files_list($theme_dir, false, 'f', true, true, false, false, true)
);
$length = mb_strlen("$theme_dir/");
$list   = array_map(
	function ($index, $file) use ($phar, $length) {
		$phar->addFromString("fs/$index", file_get_contents($file));
		return mb_substr($file, $length);
	},
	array_keys($list),
	$list
);
unset($length);
/**
 * Flip array to have direct access to files by name during extracting and installation
 */
$phar->addFromString('fs.json', _json_encode(array_flip($list)));
$phar->addFromString('dir', $_POST['themes'][0]);
unset($list);
$phar = $phar->convertToExecutable(Phar::TAR, Phar::BZ2, '.phar.tar');
unlink(DIR.'/build.phar');
$meta = file_get_json("$theme_dir/meta.json");
$phar->addFromString('index.html', isset($meta['description']) ? $meta['description'] : $meta['package']);
unset($meta);
$phar->setStub("<?php Phar::webPhar(null, 'index.html'); __HALT_COMPILER();");
$phar->setSignatureAlgorithm(PHAR::SHA512);
unset($phar);
rename(DIR.'/build.phar.tar', DIR.'/'.str_replace(' ', '_', 'theme_'.$_POST['themes'][0])."_$version.phar.php");
echo h::p("Done! Theme {$_POST['themes'][0]} $version");
