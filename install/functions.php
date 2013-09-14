<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Installer
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
function install_form () {
	$timezones = get_timezones_list();
	return h::{'form[method=post]'}(
		h::nav(
			h::{'input[name=mode][type=radio]'}([
				'value'		=> ['1', '0'],
				'in'		=> ['Regular user', 'Expert'],
				'onclick'	=> "var items = document.getElementsByClassName('expert');"
								."for (var i = 0; i < items.length; i++) {"
								."items.item(i).style.display = this.value == '0' ? 'table-row' : '';"
								."}"
			])
		).
		h::table(
			h::{'tr td'}(
				'Site name:',
				h::{'input[name=site_name]'}()
			).
			h::{'tr.expert td'}(
				'Database engine:',
				h::{'select[name=db_engine][size=3][selected=MySQLi]'}(
					_json_decode(file_get_contents(DIR.'/db_engines.json'))
				)
			).
			h::{'tr.expert td'}(
				'Database host:',
				h::{'input[name=db_host][value=localhost]'}()
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
				h::{'input[name=db_prefix]'}([
					'value'	=> substr(md5(uniqid(microtime(true), true)), 0, 5).'_'
				])
			).
			h::{'tr.expert td'}(
				'Database charset:',
				h::{'input[name=db_charset][value=utf8]'}()
			).
			h::{'tr td'}(
				'Timezone:',
				h::{'select[name=timezone][size=7][selected=UTC]'}([
					'in'		=> array_keys($timezones),
					'value'		=> array_values($timezones)
				])
			).
			h::{'tr td'}(
				'Language:',
				h::{'select[name=language][size=3][selected=English]'}(
					_json_decode(file_get_contents(DIR.'/languages.json'))
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
		h::{'button.readme[type=button]'}(
			'Readme',
			[
				'onclick'	=> "window.open('readme.html', 'readme', 'location=no')"
			]
		).
		h::{'button.license[type=button]'}(
			'License',
			[
				'onclick'	=> "window.open('license.txt', 'license', 'location=no')"
			]
		).
		h::{'button[type=submit]'}(
			'Install'
		)
	);
}
function install_process () {
	global $fs;
	/**
	 * Connecting to the DataBase
	 */
	define('DEBUG', false);
	require_once DIR.'/fs/'.$fs['core/traits/Singleton.php'];
	require_once DIR.'/fs/'.$fs['core/classes/DB.php'];
	require_once DIR.'/fs/'.$fs['core/engines/DB/_Abstract.php'];
	require_once DIR.'/fs/'.$fs["core/engines/DB/$_POST[db_engine].php"];
	/**
	 * @var \cs\DB\_Abstract $cdb
	 */
	$cdb							= "\\cs\\DB\\$_POST[db_engine]";
	$cdb							= new $cdb(
		$_POST['db_name'],
		$_POST['db_user'],
		$_POST['db_password'],
		$_POST['db_host'],
		$_POST['db_charset'],
		$_POST['db_prefix']
	);
	if (!(is_object($cdb) && $cdb->connected())) {
		return 'Database connection failed! Installation aborted.';
	}
	/**
	 * General system configuration
	 */
	$config							= _json_decode('{
		"name": "",
		"url": "",
		"keywords": "",
		"description": "",
		"admin_email": "",
		"admin_phone": "",
		"closed_title": "Site closed",
		"closed_text": "<p>Site closed for maintenance<\/p>",
		"site_mode": "1",
		"title_delimiter": " | ",
		"title_reverse": "0",
		"show_db_queries": "1",
		"show_cookies": "1",
		"gzip_compression": "1",
		"cache_compress_js_css": "1",
		"put_js_after_body": "1",
		"theme": "",
		"allow_change_theme": "0",
		"themes": [],
		"color_schemes": [],
		"color_scheme": "",
		"language": "",
		"allow_change_language": "0",
		"multilingual": "0",
		"db_balance": "0",
		"maindb_for_write": "0",
		"active_themes": [],
		"active_languages": [],
		"cookie_domain": "",
		"cookie_path": "\/",
		"mirrors_url": [
			""
		],
		"mirrors_cookie_domain": [
			""
		],
		"mirrors_cookie_path": [
			""
		],
		"languages": [],
		"inserts_limit": "1000",
		"key_expire": "120",
		"session_expire": "2592000",
		"update_ratio": "75",
		"login_attempts_block_count": "0",
		"login_attempts_block_time": "5",
		"cookie_prefix": "",
		"timezone": "",
		"password_min_length": "4",
		"password_min_strength": "0",
		"smtp": "0",
		"smtp_host": "",
		"smtp_port": "",
		"smtp_secure": "",
		"smtp_auth": "0",
		"smtp_user": "",
		"smtp_password": "",
		"mail_from_name": "",
		"allow_user_registration": "1",
		"require_registration_confirmation": "1",
		"autologin_after_registration": "1",
		"registration_confirmation_time": "1",
		"mail_signature": "",
		"mail_from": "",
		"rules": "<p>Site rules<\/p>",
		"show_tooltips": "1",
		"online_time": "300",
		"remember_user_ip": "0",
		"ip_black_list": [
			""
		],
		"ip_admin_list_only": "0",
		"ip_admin_list": [
			""
		],
		"simple_admin_mode": "0",
		"cookie_sync": "0",
		"auto_translation": "0",
		"auto_translation_engine": {
			"name": ""
		},
		"default_module": "System",
		"footer_text": "",
		"show_footer_info": "1",
		"og_support": "1"
	}');
	$config['name']					= $config['description']	= (string)$_POST['site_name'];
	$config['keywords']				= implode(', ', _trim(explode(' ', $config['name']), ','));
	$config['url']					= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http')."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$config['url']					= mb_substr(
		$config['url'],
		0,
		mb_strrpos($config['url'], '/', -13)
	);
	$config['admin_email']			= $_POST['admin_email'];
	$config['language']				= $_POST['language'];
	$config['languages']			= _json_decode(file_get_contents(DIR.'/languages.json'));
	$config['active_languages']		= $config['languages'];
	$config['themes']				= _json_decode(file_get_contents(DIR.'/themes.json'));
	$config['active_themes']		= $config['themes'];
	$config['theme']				= $config['themes'][0];
	$config['color_schemes']		= _json_decode(file_get_contents(DIR.'/color_schemes.json'));
	$config['color_scheme']			= $config['color_schemes'][0];
	$config['cookie_domain']		= explode('/', explode('//', $config['url'])[1], 2);
	$config['cookie_path']			= isset($config['cookie_domain'][1]) && $config['cookie_domain'][1] ? '/'.trim($config['cookie_domain'][1], '/').'/' : '/';
	$config['cookie_domain']		= $config['cookie_domain'][0];
	$config['timezone']				= $_POST['timezone'];
	$config['mail_from_name']		= 'Administrator of '.$config['name'];
	$config['mail_from']			= $_POST['admin_email'];
	$config['simple_admin_mode']	= $_POST['mode'];
	/**
	 * Extracting of engine's files
	 */
	$extract						= array_product(
		array_map(
			function ($index, $file) {
				if (
					!file_exists(pathinfo(ROOT."/$file", PATHINFO_DIRNAME)) &&
					!mkdir(pathinfo(ROOT."/$file", PATHINFO_DIRNAME), 0700, true)
				) {
					return 0;
				}
				return (int)copy(DIR."/fs/$index", ROOT."/$file");
			},
			$fs,
			array_keys($fs)
		)
	);
	unset($fs);
	if (
		!$extract ||
		!(file_exists(ROOT.'/storage') || mkdir(ROOT.'/storage')) ||
		!(file_exists(ROOT.'/components/plugins') || mkdir(ROOT.'/components/plugins')) ||
		!(file_exists(ROOT.'/components/blocks') || mkdir(ROOT.'/components/blocks')) ||
		!file_put_contents(ROOT.'/storage/.htaccess', "Deny from all\nRewriteEngine Off")
	) {
		return 'Can\'t extract system files from the archive! Installation aborted.';
	}
	/**
	 * Basic system configuration
	 */
	$public_key						= hash('sha512', uniqid(microtime(true), true));
	$main_config					= file_exists(ROOT.'/config') && file_put_contents(
		ROOT.'/config/main.json',
		str_replace(
			[
				'@domain',
				'@timezone',
				'@db_host',
				'@db_type',
				'@db_name',
				'@db_user',
				'@db_password',
				'@db_prefix',
				'@db_charset',
				'@language',
				'@key',
				'@iv',
				'@public_key'
			],
			[
				$config['cookie_domain'],
				$_POST['timezone'],
				$_POST['db_host'],
				$_POST['db_engine'],
				$_POST['db_name'],
				$_POST['db_user'],
				str_replace('"', '\\"', $_POST['db_password']),
				$_POST['db_prefix'],
				$_POST['db_charset'],
				$_POST['language'],
				hash('sha224', uniqid(microtime(true), true)),
				substr(md5(uniqid(microtime(true), true)), 0, 8),
				$public_key
			],
		'{
//Domain of main mirror
	"domain"			: "@domain",
//Base timezone
	"timezone"			: "@timezone",
//Settings of main DB
	"db_host"			: "@db_host",
	"db_type"			: "@db_type",
	"db_name"			: "@db_name",
	"db_user"			: "@db_user",
	"db_password"		: "@db_password",
	"db_prefix"			: "@db_prefix",
	"db_charset"		: "@db_charset",
//Settings of main Storage
	"storage_type"		: "Local",
	"storage_url"		: "",
	"storage_host"		: "localhost",
	"storage_user"		: "",
	"storage_password"	: "",
//Base language
	"language"			: "@language",
//Cache engine
	"cache_engine"		: "FileSystem",
//Cache size in MB for FileSystem storage engine
	"cache_size"		: "100",
//Settings of Memcached cache engine
	"memcache_host"		: "127.0.0.1",
	"memcache_port"		: "11211",
//Will be truncated to 56 symbols
	"key"				: "@key",
//Will be truncated to 8 symbols
	"iv"				: "@iv",
//Any length
	"public_key"		: "@public_key"
}'));
	apc() && apc_clear_cache('user');
	if (!$main_config) {
		return 'Can\'t write base system configuration! Installation aborted.';
	}
	chmod(ROOT.'/config/main.json', 0600);
	/**
	 * DataBase structure import
	 */
	if (!file_exists(DIR."/install/DB/$_POST[db_engine].sql")) {
		return 'Can\'t find system tables structure for selected database engine! Installation aborted.';
	}
	if (!$cdb->q(explode(';', file_get_contents(DIR."/install/DB/$_POST[db_engine].sql")))) {
		return 'Can\'t import system tables structure for selected database engine! Installation aborted.';
	}
	/**
	 * General configuration import
	 */
	$modules						= [
		'System'	=> [
			'active'	=> 1,
			'db'		=> [
				'keys'	=> '0',
				'users'	=> '0',
				'texts'	=> '0'
			]
		]
	];
	if (file_exists(DIR.'/modules.json')) {
		foreach (_json_decode(file_get_contents(DIR.'/modules.json')) as $module) {
			$modules[$module]	= [
				'active'	=> -1,
				'db'		=> [],
				'storage'	=> []
			];
		}
		unset($module);
	}
	if (!$cdb->q(
		"INSERT INTO `[prefix]config` (
			`domain`, `core`, `db`, `storage`, `components`, `replace`, `routing`
		) VALUES (
			'%1\$s', '%2\$s', '[]', '[]', '%3\$s', '%4\$s', '%4\$s'
		)",
		$config['cookie_domain'],
		_json_encode($config),
		'{"modules":'._json_encode($modules).',"plugins":[],"blocks":[]}',
		'{"in":[],"out":[]}'
	)) {
		return 'Can\'t import system configuration into database! Installation aborted.';
	}
	unset($modules);
	/**
	 * Administrator registration
	 */
	$admin_login					= strstr($_POST['admin_email'], '@', true);
	if (!$cdb->q(
		"INSERT INTO `[prefix]users` (
			`login`, `login_hash`, `password_hash`, `email`, `email_hash`, `reg_date`, `reg_ip`, `status`
		) VALUES (
			'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
		)",
		$admin_login,
		hash('sha224', $admin_login),
		hash('sha512', hash('sha512', $_POST['admin_password']).$public_key),
		$_POST['admin_email'],
		hash('sha224', $_POST['admin_email']),
		time(),
		$_SERVER['REMOTE_ADDR'],
		1
	)) {
		return 'Can\'t register administrator user! Installation aborted.';
	}
	/**
	 * Disconnecting from the DataBase, removing of installer file
	 */
	$cdb->__destruct();
	unlink(ROOT.'/'.pathinfo(DIR, PATHINFO_BASENAME));
	return h::h3(
		'Congratulations! CleverStyle CMS has been installed successfully!'
	).
	h::{'table tr| td'}(
		[
			'Your login information:',
			[
				'colspan'	=> 2
			]
		],
		[
			'Login:',
			$admin_login
		],
		[
			'Password:',
			$_POST['admin_password']
		]
	).
	h::button(
		'Go to website',
		[
			'onclick'	=> "location.href = '".addslashes($config['url'])."'"
		]
	);
}