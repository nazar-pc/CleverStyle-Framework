<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Uploader
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
chdir(__DIR__);
define('ROOT', __DIR__.'/..');

/**
 * Check whether current commit is newest in master branch
 */
$on_master = in_array(
	trim(file_get_contents(ROOT.'/.git/HEAD')),
	[
		'ref: refs/heads/master',
		trim(@file_get_contents(ROOT.'/.git/refs/heads/master'))
	]
);
/**
 * Check whether commit is tagged - if so - we are dealing with release
 */
$tag = exec('git describe --tags --exact-match HEAD 2>/dev/null');
if (!$on_master && !$tag) {
	echo "Distributive is uploaded only when on master branch or releases\n";
	return;
}
if ($tag && !preg_match('/^\d+\.\d+\.\d+\+build-\d+$/', $tag)) {
	echo "Only regular releases are uploaded, other tags ignored\n";
	return;
}
echo "Building packages...\n";
ob_start();

require_once ROOT.'/build/Builder.php';
require_once ROOT.'/core/thirdparty/nazarpc/BananaHTML.php';
require_once ROOT.'/core/classes/h/Base.php';
require_once ROOT.'/core/classes/h.php';
require_once ROOT.'/core/thirdparty/upf.php';
require_once ROOT.'/core/functions.php';

$modules = array_values(
	array_filter(
		get_files_list(ROOT.'/modules', false, 'd'),
		function ($module) {
			return $module != 'System';
		}
	)
);
$themes  = array_values(
	array_filter(
		get_files_list(ROOT.'/themes', false, 'd'),
		function ($theme) {
			return $theme != 'CleverStyle';
		}
	)
);

$Builder = new cs\Builder(ROOT, 'dist');
$Builder->core([], [], 'Core');
$Builder->core($modules, $themes, 'Full');
foreach ($modules as $module) {
	$Builder->module($module);
}
foreach ($themes as $theme) {
	$Builder->theme($theme);
}

if (ob_get_contents()) {
	echo "Build failed:\n";
	ob_end_flush();
	return;
}
ob_end_clean();
echo "Building finished, uploading to SourceForge...\n";

exec('openssl enc -d -aes-256-cbc -in gpg.asc.enc -out gpg.asc -pass env:KEYPASS 2>/dev/null');
exec('gpg --import gpg.asc');
foreach (get_files_list('dist', false, 'f', true) as $file) {
	exec("echo \$KEYPASS | gpg -abs --passphrase-fd 0 $file");
}

exec('openssl enc -d -aes-256-cbc -in id_rsa.enc -out id_rsa -pass env:KEYPASS 2>/dev/null');
chmod('id_rsa', 0600);
$target_directory = $tag ? "stable/$tag" : 'nightly';
system(
	"rsync -e 'ssh -o StrictHostKeyChecking=no -i id_rsa -o UserKnownHostsFile=/dev/null' --compress --delete --recursive --progress dist/ nazar-pc@frs.sourceforge.net:/home/frs/project/cleverstyle-framework/$target_directory/"
);
