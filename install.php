<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Installer
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
if (version_compare(PHP_VERSION, '5.4', '<')) {
	exit('CleverStyle CMS require PHP 5.4 or higher');
}
$cli	= PHP_SAPI == 'cli';
/**
 * Path to installer dir
 */
if ($cli) {
	define('DIR', 'phar://cleverstyle_cms.phar');
} else {
	define('DIR', __DIR__);
}
$ROOT	= dirname(__DIR__);
mb_internal_encoding('utf-8');
define('ROOT',	mb_strpos($ROOT, 'phar://') === 0 ? substr($ROOT, 7) : $ROOT);	//Path to site root
unset($ROOT);
global $fs;
$fs		= json_decode(file_get_contents(DIR.'/fs.json'), true);
require_once DIR.'/fs/'.$fs['core/thirdparty/upf.php'];
require_once DIR.'/fs/'.$fs['core/functions.php'];
require_once DIR.'/fs/'.$fs['core/thirdparty/nazarpc/BananaHTML.php'];
require_once DIR.'/fs/'.$fs['core/classes/h/Base.php'];
require_once DIR.'/fs/'.$fs['core/classes/h.php'];
require_once DIR.'/install/functions.php';
date_default_timezone_set('UTC');
if ($cli) {
	$help	= false;
	for ($i = 1; $i < $argc; $i += 2) {
		switch ($argv[$i]) {
			case '-h':
				$help	= true;
			break;
			case '-sn':
			case '-site_name':
				$_POST['site_name'] = $argv[$i + 1];
			break;
			case '-su':
			case '-site_url':
				$_POST['site_url'] = $argv[$i + 1];
			break;
			case '-de':
			case '-db_engine':
				$_POST['db_engine'] = $argv[$i + 1];
			break;
			case '-dh':
			case '-db_host':
				$_POST['db_host'] = $argv[$i + 1];
			break;
			case '-dn':
			case '-db_name':
				$_POST['db_name'] = $argv[$i + 1];
			break;
			case '-du':
			case '-db_user':
				$_POST['db_user'] = $argv[$i + 1];
			break;
			case '-dp':
			case '-db_password':
				$_POST['db_password'] = $argv[$i + 1];
			break;
			case '-dr':
			case '-db_prefix':
				$_POST['db_prefix'] = $argv[$i + 1];
			break;
			case '-dc':
			case '-db_charset':
				$_POST['db_charset'] = $argv[$i + 1];
			break;
			case '-t':
			case '-timezone':
				$_POST['timezone'] = $argv[$i + 1];
			break;
			case '-l':
			case '-language':
				$_POST['language'] = $argv[$i + 1];
			break;
			case '-ae':
			case '-admin_email':
				$_POST['admin_email'] = $argv[$i + 1];
			break;
			case '-ap':
			case '-admin_password':
				$_POST['admin_password'] = $argv[$i + 1];
			break;
		}
	}
	if (
		$help ||
		$argc == 1 ||
		!isset(
			$_POST['site_name'],
			$_POST['site_url'],
			$_POST['db_name'],
			$_POST['db_user'],
			$_POST['db_password'],
			$_POST['admin_email'],
			$_POST['admin_password']
		)
	) {
		exit(
'CleverStyle CMS installer
Installer is used for installation of CleverStyle CMS and built-in components from distributive.
Usage: php CleverStyle_CMS.phar.php
         -site_name <site_name>
         -site_url <site_url>
         -db_name <db_name>
         -db_user <db_user>
         -db_password <db_password>
         -admin_email <admin_email>
         -admin_password <admin_password>
         [-h]
         [-db_engine <db_engine>]
         [-db_host <db_host>]
         [-db_prefix <db_prefix>]
         [-db_charset <db_charset>]
         [-timezone <timezone>]
         [-language <language>]
  -h              - This information
  -sn
  -site_name      - Name of future site, in case of few words, do not forget to take into quotes
  -su
  -site_url       - Site url with protocol prefix, without final slash
  -de
  -db_engine      - Database engine, only MySQLi currently supported
  -dh
  -db_host        - Database host
  -dn
  -db_name        - Database name
  -du
  -db_user        - Database user
  -dp
  -db_password    - Database password
  -dr
  -db_prefix      - Is used for prefixing all tables names
  -dc
  -db_charset     - Database charset
  -t
  -timezone       - Timezone, check http://php.net/manual/en/suffixtimezones.php for possible values
  -l
  -language       - Language, currently English, Українська and Русский languages supported
  -ae
  -admin_email    - Email of first, primary administrator
  -ap
  -admin_password - Password of first administrator
Example:
  php CleverStyle_CMS.phar.php -sn Web-site -su http://web.site -dn web.site -du web.site -dp pass -ae admin@web.site -ap pass
'
		);
	} else {
		if (!isset($_POST['db_engine'])) {
			$_POST['db_engine'] = 'MySQLi';
		}
		if (!isset($_POST['db_host'])) {
			$_POST['db_host'] = 'localhost';
		}
		if (!isset($_POST['db_prefix'])) {
			$_POST['db_prefix'] = substr(md5(uniqid(microtime(true), true)), 0, 5).'_';
		}
		if (!isset($_POST['db_charset'])) {
			$_POST['db_charset'] = 'utf8';
		}
		if (!isset($_POST['timezone'])) {
			$_POST['timezone'] = 'UTC';
		}
		if (!isset($_POST['language'])) {
			$_POST['language'] = 'English';
		}
		echo install_process($argv);
	}
	echo "\n";
	return;
}
if (count(explode('/', $_SERVER['REQUEST_URI'])) > 3) {
	exit('Installation into subdirectory is not supported!');
}
header('Content-Type: text/html; charset=utf-8');
header('Connection: close');
echo	"<!doctype html>\n".
		h::title('CleverStyle CMS $version$ Installation').
		h::meta([
			'charset'	=> 'utf-8'
		]).
		h::style(file_get_contents(DIR.'/install/style.css')).
		h::header(
			h::img([
				'src'	=> 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(DIR.'/install/logo.png'))
			]).
			h::h1('CleverStyle CMS $version$ Installation')
		).
		h::section(
			isset($_POST['site_name']) ? install_process() : install_form()
		).
		h::footer(
			'Copyright (c) 2011-2014, Nazar Mokrynskyi'
		);
