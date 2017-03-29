<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Installer
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
use
	PharException;

$phar_path = __DIR__;
if (strpos(__DIR__, 'phar://') !== 0) {
	foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $step) {
		if (preg_match('#^phar://.+/cli.php$#', $step['file'])) {
			$phar_path = dirname($step['file']);
			break;
		}
	}
}

date_default_timezone_set('UTC');
require_once __DIR__.'/Installer.php';

$help        = false;
$interactive = false;
/**
 * @var array $options
 */
$options = [
	'db_driver' => 'MySQLi',
	'db_host'   => 'localhost',
	'db_prefix' => substr(md5(random_bytes(1000)), 0, 5).'_',
	'timezone'  => 'UTC',
	'language'  => 'English',
	'mode'      => 1
];
/**
 * @var array $argv
 * @var int   $argc
 */
for ($i = 1; $i < $argc; $i += 2) {
	$value = $argv[$i + 1];
	switch ($argv[$i]) {
		case '-h':
		case '--help':
			$help = true;
			break;
		case '-sn':
		case '--site_name':
			$options['site_name'] = $value;
			break;
		case '-su':
		case '--site_url':
			$options['site_url'] = $value;
			break;
		case '-de':
		case '--db_driver':
			$options['db_driver'] = $value;
			break;
		case '-dh':
		case '--db_host':
			$options['db_host'] = $value;
			break;
		case '-dn':
		case '--db_name':
			$options['db_name'] = $value;
			break;
		case '-du':
		case '--db_user':
			$options['db_user'] = $value;
			break;
		case '-dp':
		case '--db_password':
			$options['db_password'] = $value;
			break;
		case '-dr':
		case '--db_prefix':
			$options['db_prefix'] = $value;
			break;
		case '-t':
		case '--timezone':
			$options['timezone'] = $value;
			break;
		case '-l':
		case '--language':
			$options['language'] = $value;
			break;
		case '-m':
		case '--mode':
			$options['mode'] = $value;
			break;
		case '-ae':
		case '--admin_email':
			$options['admin_email'] = $value;
			break;
		case '-ap':
		case '--admin_password':
			$options['admin_password'] = $value;
			break;
		case '-i':
		case '--interactive':
			$interactive = true;
			break;
	}
}
if ($interactive) {
	echo "CleverStyle Framework installer, interactive mode\n";
	$required_parameters = [
		'site_name'      => 'Site name',
		'site_url'       => 'Site URL',
		'db_name'        => 'Database name',
		'db_user'        => 'Database user',
		'db_password'    => 'Database password',
		'admin_email'    => 'Email of administrator',
		'admin_password' => 'Password of administrator'
	];
	foreach ($required_parameters as $parameter => $description) {
		if (!isset($options[$parameter])) {
			echo "$description: ";
			$options[$parameter] = substr(fgets(STDIN), 0, -1);
		}
	}
}
if (
	$help ||
	$argc == 1 ||
	!isset(
		$options['site_name'],
		$options['site_url'],
		$options['db_name'],
		$options['db_user'],
		$options['db_password'],
		$options['admin_email'],
		$options['admin_password']
	)
) {
	echo <<<HELP
CleverStyle Framework installer
Installer is used for installation of CleverStyle Framework and built-in components from distributive.
Usage: php $argv[0]
         --site_name <site_name>
         --site_url <site_url>
         --db_name <db_name>
         --db_user <db_user>
         --db_password <db_password>
         --admin_email <admin_email>
         --admin_password <admin_password>
         [--help]
         [--interactive]
         [--db_driver <db_driver>]
         [--db_host <db_host>]
         [--db_prefix <db_prefix>]
         [--timezone <timezone>]
         [--language <language>]
  -h
  --help           - This information
  -i
  --interactive    - Interactive mode (can be combined with other options, will ask only required)
  -sn
  --site_name      - Name of future site, in case of few words, do not forget to take into quotes
  -su
  --site_url       - Site url with protocol prefix, without final slash
  -de
  --db_driver      - Database driver, only MySQLi currently supported
  -dh
  --db_host        - Database host
  -dn
  --db_name        - Database name
  -du
  --db_user        - Database user
  -dp
  --db_password    - Database password
  -dr
  --db_prefix      - Is used for prefixing all tables names
  -t
  --timezone       - Timezone, check http://php.net/manual/en/suffixtimezones.php for possible values
  -l
  --language       - Language, currently English, Ukrainian and Russian languages supported
  -m
  --mode           - Mode, 0 for Expert and 1 for Regular user
  -ae
  --admin_email    - Email of first, primary administrator
  -ap
  --admin_password - Password of first administrator
Examples:
  php $argv[0] -sn Web-site -su http://web.site -dn web.site -du web.site -dp pass -ae admin@web.site -ap pass
  php $argv[0] -i
  php $argv[0] -sn Web-site -i

HELP;
	return;
}

try {
	Installer::install(
		$phar_path,
		getcwd(),
		$options['site_name'],
		$options['site_url'],
		$options['timezone'],
		$options['db_host'],
		$options['db_driver'],
		$options['db_name'],
		$options['db_user'],
		$options['db_password'],
		$options['db_prefix'],
		$options['language'],
		$options['admin_email'],
		$options['admin_password'],
		$options['mode']
	);
} catch (\Exception $e) {
	echo $e->getMessage();
	exit(1);
}
$admin_login = strstr($options['admin_email'], '@', true);
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
echo <<<SUCCESS
Congratulations! CleverStyle Framework has been installed successfully!
$warning
Login: $admin_login
Password: $options[admin_password]

SUCCESS;
