<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     Builder
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
time_limit_pause();
if (!isset($_POST['plugins'][0])) {
	echo 'Please, specify plugin name';
	return;
} elseif (!file_exists($plugin_dir = DIR.'/components/plugins/'.$_POST['plugins'][0])) {
	echo "Can't build plugin, plugin directory not found";
	return;
} elseif (!file_exists("$plugin_dir/meta.json")) {
	echo "Can't build plugin, meta information (meta.json) not found";
	return;
}
$version = file_get_json("$plugin_dir/meta.json")['version'];
if (file_exists(DIR.'/build.phar')) {
	unlink(DIR.'/build.phar');
}
$phar = new Phar(DIR.'/build.phar');
$phar->addFromString('meta.json', file_get_contents("$plugin_dir/meta.json"));
$set_stub = false;
if (file_exists("$plugin_dir/readme.html")) {
	$phar->addFromString('readme.html', file_get_contents("$plugin_dir/readme.html"));
	$set_stub = 'readme.html';
} elseif (file_exists("$plugin_dir/readme.txt")) {
	$phar->addFromString('readme.txt', file_get_contents("$plugin_dir/readme.txt"));
	$set_stub = 'readme.txt';
}
$list   = get_files_list($plugin_dir, false, 'f', true, true, false, false, true);
$length = mb_strlen("$plugin_dir/");
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
$phar->addFromString(
	'fs.json',
	_json_encode(
		array_flip($list)
	)
);
//TODO remove in future versions
$phar->addFromString('dir', $_POST['plugins'][0]);
unset($list);
if ($set_stub) {
	$phar->setStub("<?php Phar::webPhar(null, '$set_stub'); __HALT_COMPILER();");
} else {
	$meta = file_get_json("$plugin_dir/meta.json");
	$phar->addFromString('index.html', isset($meta['description']) ? $meta['description'] : $meta['package']);
	unset($meta);
	$phar->setStub("<?php Phar::webPhar(null, 'index.html'); __HALT_COMPILER();");
}
unset($phar);
$suffix = @$_POST['suffix'] ? "_$_POST[suffix]" : '';
rename(DIR.'/build.phar', DIR.'/'.str_replace(' ', '_', 'plugin_'.$_POST['plugins'][0])."_$version$suffix.phar.php");
echo "Done! Plugin {$_POST['plugins'][0]} $version";
