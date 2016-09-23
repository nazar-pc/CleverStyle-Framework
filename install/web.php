<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Installer
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
use
	h,
	PharException;

$phar_path = __DIR__;
if (strpos(__DIR__, 'phar://') !== 0) {
	foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $step) {
		if (preg_match('#^phar://.+/web.php$#', $step['file'])) {
			$phar_path = dirname($step['file']);
			break;
		}
	}
}

date_default_timezone_set('UTC');
require_once __DIR__.'/Installer.php';

/**
 * @param string $phar_path
 *
 * @return string
 */
function install_form ($phar_path) {
	$timezones = get_timezones_list();
	return h::{'form[method=post]'}(
		h::nav(
			h::{'radio[name=mode]'}(
				[
					'value'   => ['1', '0'],
					'in'      => [h::span('Regular user'), h::span('Expert')],
					'onclick' => <<<JS
var items = document.querySelectorAll('.expert'), i; for (i = 0; i < items.length; i++) items[i].style.display = this.value == '0' ? 'table-row' : '';
JS
				]
			)
		).
		h::table(
			h::{'tr td'}(
				'Site name:',
				h::{'input[name=site_name]'}()
			).
			h::{'tr.expert td'}(
				'Database driver:',
				h::{'select[name=db_driver][size=3][selected=MySQLi]'}(
					file_get_json("$phar_path/db_drivers.json")
				)
			).
			h::{'tr.expert td'}(
				'Database host:',
				h::{'input[name=db_host][value=localhost]'}(
					[
						'placeholder' => 'Relative or absolute path to DB for SQLite'
					]
				)
			).
			h::{'tr td'}(
				'Database name:',
				h::{'input[name=db_name]'}()
			).
			h::{'tr td'}(
				'Database user:',
				h::{'input[name=db_user]'}()
			).
			h::{'tr td'}(
				'Database user password:',
				h::{'input[type=password][name=db_password]'}()
			).
			h::{'tr.expert td'}(
				'Database tables prefix:',
				h::{'input[name=db_prefix]'}(
					[
						'value' => substr(md5(random_bytes(1000)), 0, 5).'_'
					]
				)
			).
			h::{'tr td'}(
				'Timezone:',
				h::{'select[name=timezone][size=7][selected=UTC]'}(
					[
						'in'    => array_keys($timezones),
						'value' => array_values($timezones)
					]
				)
			).
			h::{'tr td'}(
				'Language:',
				h::{'select[name=language][size=3][selected=English]'}(
					file_get_json("$phar_path/languages.json")
				)
			).
			h::{'tr td'}(
				'Email of administrator:',
				h::{'input[type=email][name=admin_email]'}()
			).
			h::{'tr td'}(
				'Administrator password:',
				h::{'input[type=password][name=admin_password]'}()
			)
		).
		h::{'button.license'}(
			'License',
			[
				'onclick' => "window.open('license.txt', 'license', 'location=no')"
			]
		).
		h::{'button[type=submit]'}(
			'Install'
		)
	);
}

/**
 * @param string $phar_path
 *
 * @return string
 */
function install_process ($phar_path) {
	if (isset($_POST['site_url'])) {
		$url = $_POST['site_url'];
	} else {
		$https  = @$_SERVER['HTTPS'] ? $_SERVER['HTTPS'] !== 'off' : (
			@$_SERVER['REQUEST_SCHEME'] === 'https' ||
			@$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
		);
		$scheme = $https ? 'https' : 'http';
		$host   = explode(':', $_SERVER['HTTP_HOST'])[0];
		$path   = explode('?', $_SERVER['REQUEST_URI'])[0] ?: '/';
		$url    = "$scheme://$host$path";
		$url    = implode('/', array_slice(explode('/', $url), 0, -2)); //Remove 2 last items
	}
	try {
		Installer::install(
			$phar_path,
			getcwd(),
			$_POST['site_name'],
			$url,
			$_POST['timezone'],
			$_POST['db_host'],
			$_POST['db_driver'],
			$_POST['db_name'],
			$_POST['db_user'],
			$_POST['db_password'],
			$_POST['db_prefix'],
			$_POST['language'],
			$_POST['admin_email'],
			$_POST['admin_password'],
			$_POST['mode'] ? 1 : 0
		);
	} catch (\Exception $e) {
		return $e->getMessage();
	}
	$admin_login = strstr($_POST['admin_email'], '@', true);
	$warning     = false;
	// Removing of installer file
	$installer       = substr($phar_path, strlen('phar://'));
	$unlink_function = $phar_path == __DIR__ ? 'unlink' : ['Phar', 'unlinkArchive'];
	try {
		if (!is_writable($installer) || !$unlink_function($installer)) {
			throw new PharException;
		}
	} catch (PharException $e) {
		$warning = "Please, remove installer file $installer for security!\n";
	}
	return <<<HTML
<h3>Congratulations! CleverStyle Framework has been installed successfully!</h3>
<table>
	<tr>
		<td colspan="2">Your sign in information:</td>
	</tr>
	<tr>
		<td>Login:</td>
		<td><pre>$admin_login</pre></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><pre>$_POST[admin_password]</pre></td>
	</tr>
	<p style="color: red">$warning</p>
	<button onclick="location.href = '/';">Go to website</button>
</table>
HTML;
}

if (count(explode('/', $_SERVER['REQUEST_URI'])) > 3) {
	echo 'Installation into subdirectory is not supported!';
	return;
}

header('Content-Type: text/html; charset=utf-8');
header('Connection: close');

$fs = json_decode(file_get_contents("$phar_path/fs.json"), true);
require_once "$phar_path/fs/".$fs['core/thirdparty/upf.php'];
require_once "$phar_path/fs/".$fs['core/functions.php'];
require_once "$phar_path/fs/".$fs['core/thirdparty/nazarpc/BananaHTML.php'];
require_once "$phar_path/fs/".$fs['core/classes/h/Base.php'];
require_once "$phar_path/fs/".$fs['core/classes/h.php'];

$version = file_get_json("$phar_path/meta.json")['version'];
?>
<!doctype html>
<title>CleverStyle Framework <?=$version?> Installation</title>
<meta charset="utf-8">
<style><?=file_get_contents(__DIR__.'/style.css')?></style>
<header>
	<?=file_get_contents("$phar_path/logo.svg")?>
	<h1>Installation</h1>
</header>
<section><?=isset($_POST['site_name']) ? install_process($phar_path) : install_form($phar_path)?></section>
<footer>Copyright (c) 2011-2016, Nazar Mokrynskyi</footer>
