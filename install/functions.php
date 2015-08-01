<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Installer
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
function install_form () {
	$timezones = get_timezones_list();
	return h::{'form[method=post]'}(
		h::nav(
			h::{'radio[name=mode]'}(
				[
					'value'   => ['1', '0'],
					'in'      => [h::span('Regular user'), h::span('Expert')],
					'onclick' =>
						"var items = document.getElementsByClassName('expert');"
						."for (var i = 0; i < items.length; i++) {"
						."items.item(i).style.display = this.value == '0' ? 'table-row' : '';"
						."}"
				]
			)
		).
		h::table(
			h::{'tr td'}(
				'Site name:',
				h::{'input[name=site_name]'}()
			).
			h::{'tr.expert td'}(
				'Database engine:',
				h::{'select[name=db_engine][size=3][selected=MySQLi]'}(
					file_get_json(DIR.'/db_engines.json')
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
				h::{'input[name=db_prefix]'}(
					[
						'value' => substr(md5(random_bytes(1000)), 0, 5).'_'
					]
				)
			).
			h::{'tr.expert td'}(
				'Database charset:',
				h::{'input[name=db_charset][value=utf8mb4]'}()
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
					file_get_json(DIR.'/languages.json')
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
		h::{'button.uk-button.readme'}(
			'Readme',
			[
				'onclick' => "window.open('readme.html', 'readme', 'location=no')"
			]
		).
		h::{'button.uk-button.license'}(
			'License',
			[
				'onclick' => "window.open('license.txt', 'license', 'location=no')"
			]
		).
		h::{'button.uk-button[type=submit]'}(
			'Install'
		)
	);
}

/**
 * @param array[]    $fs
 * @param array|null $argv
 *
 * @return string
 */
function install_process ($fs, $argv = null) {
	/**
	 * Connecting to the DataBase
	 */
	define('DEBUG', false);
	require_once DIR.'/fs/'.$fs['core/classes/_SERVER.php'];
	require_once DIR.'/fs/'.$fs['core/traits/Singleton/Base.php'];
	require_once DIR.'/fs/'.$fs['core/traits/Singleton.php'];
	require_once DIR.'/fs/'.$fs['core/classes/DB.php'];
	require_once DIR.'/fs/'.$fs['core/engines/DB/_Abstract.php'];
	require_once DIR.'/fs/'.$fs["core/engines/DB/$_POST[db_engine].php"];
	require_once DIR.'/fs/'.$fs['core/classes/False_class.php'];
	$_SERVER = new \cs\_SERVER($_SERVER);
	/**
	 * @var \cs\DB\_Abstract $cdb
	 */
	$cdb = "cs\\DB\\$_POST[db_engine]";
	$cdb = new $cdb(
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
	$config         = [
		'name'                              => '',
		'url'                               => [],
		'admin_email'                       => '',
		'closed_title'                      => 'Site closed',
		'closed_text'                       => '<p>Site closed for maintenance</p>',
		'site_mode'                         => 1,
		'title_delimiter'                   => ' | ',
		'title_reverse'                     => 0,
		'cache_compress_js_css'             => 1,
		'vulcanization'                     => 1,
		'put_js_after_body'                 => 1,
		'theme'                             => '',
		'themes'                            => [],
		'language'                          => '',
		'allow_change_language'             => 0,
		'multilingual'                      => 0,
		'db_balance'                        => 0,
		'db_mirror_mode'                    => \cs\DB::MIRROR_MODE_MASTER_MASTER,
		'active_languages'                  => [],
		'cookie_domain'                     => [],
		'cookie_path'                       => [],
		'languages'                         => [],
		'inserts_limit'                     => 1000,
		'key_expire'                        => 120,
		'session_expire'                    => 2592000,
		'update_ratio'                      => 75,
		'sign_in_attempts_block_count'      => 0,
		'sign_in_attempts_block_time'       => 5,
		'cookie_prefix'                     => '',
		'timezone'                          => '',
		'password_min_length'               => 4,
		'password_min_strength'             => 3,
		'smtp'                              => 0,
		'smtp_host'                         => '',
		'smtp_port'                         => '',
		'smtp_secure'                       => '',
		'smtp_auth'                         => 0,
		'smtp_user'                         => '',
		'smtp_password'                     => '',
		'mail_from_name'                    => '',
		'allow_user_registration'           => 1,
		'require_registration_confirmation' => 1,
		'auto_sign_in_after_registration'   => 1,
		'registration_confirmation_time'    => 1,
		'mail_signature'                    => '',
		'mail_from'                         => '',
		'rules'                             => '',
		'show_tooltips'                     => 1,
		'remember_user_ip'                  => 0,
		'ip_black_list'                     => [],
		'ip_admin_list_only'                => 0,
		'ip_admin_list'                     => [],
		'simple_admin_mode'                 => 1,
		'default_module'                    => 'System'
	];
	$config['name'] = (string)$_POST['site_name'];
	if (isset($_POST['site_url'])) {
		$url = $_POST['site_url'];
	} else {
		$url = @$_SERVER->protocol;
		$url .= "://$_SERVER->host$_SERVER->request_uri";
		$url = implode('/', array_slice(explode('/', $url), 0, -2));    //Remove 2 last items
	}
	$config['url'][]            = $url;
	$config['admin_email']      = $_POST['admin_email'];
	$config['language']         = $_POST['language'];
	$config['languages']        = file_get_json(DIR.'/languages.json');
	$config['active_languages'] = $config['languages'];
	$config['themes']           = file_get_json(DIR.'/themes.json');
	$config['theme']            = in_array('CleverStyle', $config['themes']) ? 'CleverStyle' : $config['themes'][0];
	$url                        = explode('/', explode('//', $url)[1], 2);
	$config['cookie_domain'][]  = explode(':', $url[0])[0];
	$config['cookie_path'][]    = isset($url[1]) && $url[1] ? '/'.trim($url[1], '/').'/' : '/';
	unset($url);
	$config['timezone']          = $_POST['timezone'];
	$config['mail_from_name']    = 'Administrator of '.$config['name'];
	$config['mail_from']         = $_POST['admin_email'];
	$config['simple_admin_mode'] = !isset($_POST['mode']) || $_POST['mode'] ? 1 : 0;
	/**
	 * Extracting of engine's files
	 */
	$extracted = array_filter(
		array_map(
			function ($index, $file) {
				if (
					!file_exists(dirname(ROOT."/$file")) &&
					!mkdir(dirname(ROOT."/$file"), 0770, true)
				) {
					return false;
				}
				/**
				 * TODO: copy() + file_exists() is a hack for HHVM, when bug fixed upstream (copying of empty files) this should be simplified
				 */
				copy(DIR."/fs/$index", ROOT."/$file");
				return file_exists(ROOT."/$file");
			},
			$fs,
			array_keys($fs)
		)
	);
	if (
		count($extracted) !== count($fs) ||
		!(file_exists(ROOT.'/storage') || mkdir(ROOT.'/storage', 0770)) ||
		!file_put_contents(ROOT.'/storage/.htaccess', "Deny from all\nRewriteEngine Off\n<Files *>\n\tSetHandler default-handler\n</Files>")
	) {
		return 'Can\'t extract system files from the archive! Installation aborted.';
	}
	/**
	 * Basic system configuration
	 */
	$public_key  = hash('sha512', random_bytes(1000));
	$main_config = file_exists(ROOT.'/config') && file_put_contents(
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
					'@public_key'
				],
				[
					$config['cookie_domain'][0],
					$_POST['timezone'],
					$_POST['db_host'],
					$_POST['db_engine'],
					$_POST['db_name'],
					$_POST['db_user'],
					str_replace('"', '\\"', $_POST['db_password']),
					$_POST['db_prefix'],
					$_POST['db_charset'],
					$_POST['language'],
					hash('sha512', random_bytes(1000)),
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
//Settings of Memcached cache engine
	"memcache_host"		: "127.0.0.1",
	"memcache_port"		: "11211",
//Default encryption key
	"key"				: "@key",
//Any length
	"public_key"		: "@public_key"
}'
			)
		);
	extension_loaded('apc') && apc_clear_cache('user');
	if (!$main_config) {
		return "Can't write base system configuration! Installation aborted.";
	}
	chmod(ROOT.'/config/main.json', 0600);
	/**
	 * DataBase structure import
	 */
	if (!file_exists(DIR."/install/DB/$_POST[db_engine].sql")) {
		return "Can't find system tables structure for selected database engine! Installation aborted.";
	}
	if (!$cdb->q(
		array_filter(
			explode(';', file_get_contents(DIR."/install/DB/$_POST[db_engine].sql")),
			'_trim'
		)
	)
	) {
		return "Can't import system tables structure for selected database engine! Installation aborted.";
	}
	/**
	 * General configuration import
	 */
	$modules = [
		'System' => [
			'active' => 1,
			'db'     => [
				'keys'  => '0',
				'users' => '0',
				'texts' => '0'
			]
		]
	];
	if (file_exists(DIR.'/modules.json')) {
		foreach (file_get_json(DIR.'/modules.json') as $module) {
			$modules[$module] = [
				'active'  => -1,
				'db'      => [],
				'storage' => []
			];
		}
		unset($module);
	}
	if (!$cdb->q(
		"INSERT INTO `[prefix]config` (
			`domain`, `core`, `db`, `storage`, `components`, `replace`, `routing`
		) VALUES (
			'%s', '%s', '[]', '[]', '%s', '%s', '%s'
		)",
		$config['cookie_domain'][0],
		_json_encode($config),
		'{"modules":'._json_encode($modules).',"plugins":[],"blocks":[]}',
		'{"in":[],"out":[]}',
		'{"in":[],"out":[]}'
	)
	) {
		return "Can't import system configuration into database! Installation aborted.";
	}
	unset($modules);
	/**
	 * Administrator registration
	 */
	$admin_login = strstr($_POST['admin_email'], '@', true);
	if (!$cdb->q(
		"INSERT INTO `[prefix]users` (
			`login`, `login_hash`, `password_hash`, `email`, `email_hash`, `reg_date`, `reg_ip`, `status`
		) VALUES (
			'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
		)",
		$admin_login,
		hash('sha224', $admin_login),
		password_hash(hash('sha512', hash('sha512', $_POST['admin_password']).$public_key), PASSWORD_DEFAULT),
		$_POST['admin_email'],
		hash('sha224', $_POST['admin_email']),
		time(),
		$_SERVER->remote_addr,
		1
	)
	) {
		return "Can't register administrator user! Installation aborted.";
	}
	/**
	 * Disconnecting from the DataBase, removing of installer file
	 */
	$cdb->__destruct();
	$warning   = false;
	$cli       = PHP_SAPI == 'cli';
	$installer = $cli ? ROOT."/$argv[0]" : ROOT.'/'.pathinfo(DIR, PATHINFO_BASENAME);
	if (is_writable($installer)) {
		unlink($installer);
	} else {
		$warning = "Please, remove installer file $installer for security!\n";
	}
	if ($cli) {
		return "Congratulations! CleverStyle CMS has been installed successfully!\n$warning\nLogin: $admin_login\nPassword: $_POST[admin_password]";
	} else {
		return
			h::h3(
				'Congratulations! CleverStyle CMS has been installed successfully!'
			).
			h::{'table tr| td'}(
				[
					'Your sign in information:',
					[
						'colspan' => 2
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
			h::p(
				$warning,
				[
					'style' => 'color: red;'
				]
			).
			h::{'button.uk-button'}(
				'Go to website',
				[
					'onclick' => "location.href = '/';"
				]
			);
	}
}
