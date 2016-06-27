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
	RuntimeException;

class Installer {
	const MAIN_CONFIG_STUB = /** @lang JSON */
		<<<CONFIG
{
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
//Any length
	"public_key"		: "@public_key"
}

CONFIG;
	/**
	 * @param string $source
	 * @param string $target
	 * @param string $site_name
	 * @param string $url
	 * @param string $timezone
	 * @param string $db_host
	 * @param string $db_engine
	 * @param string $db_name
	 * @param string $db_user
	 * @param string $db_password
	 * @param string $db_prefix
	 * @param string $language
	 * @param string $admin_email
	 * @param string $admin_password
	 * @param int    $mode
	 *
	 * @throws RuntimeException
	 */
	static function install (
		$source,
		$target,
		$site_name,
		$url,
		$timezone,
		$db_host,
		$db_engine,
		$db_name,
		$db_user,
		$db_password,
		$db_prefix,
		$language,
		$admin_email,
		$admin_password,
		$mode
	) {
		static::pre_installation_checks($source, $target, $db_engine);
		// Needed to be defined before connecting to the database
		defined('DEBUG') || define('DEBUG', false);
		$file_index_map = static::initialize_filesystem($source);
		static::extract($file_index_map, $source, $target);
		$domain     = parse_url($url, PHP_URL_HOST);
		$public_key = hash('sha512', random_bytes(1000));
		static::initialize_core_config(
			$target,
			$domain,
			$timezone,
			$db_host,
			$db_engine,
			$db_name,
			$db_user,
			$db_password,
			$db_prefix,
			$language,
			$public_key
		);
		/**
		 * @var \cs\DB\_Abstract $cdb
		 */
		$cdb = "cs\\DB\\$db_engine";
		$cdb = new $cdb($db_name, $db_user, $db_password, $db_host, $db_prefix);
		if (!is_object($cdb) || !$cdb->connected()) {
			throw new RuntimeException("Can't connect to database! Installation aborted.");
		}
		static::initialize_db_structure($cdb, $source, $db_engine);
		static::initialize_system_config($cdb, $source, $site_name, $url, $admin_email, $language, $domain, $timezone, $mode);
		static::create_root_administrator($cdb, $admin_email, $admin_password, $public_key);
		unset($cdb);
	}
	/**
	 * @param string $source
	 * @param string $target
	 * @param string $db_engine
	 *
	 * @throws RuntimeException
	 */
	protected static function pre_installation_checks ($source, $target, $db_engine) {
		if (file_exists("$target/config/main.json")) {
			throw new RuntimeException('"config/main.json" file already present! Installation aborted.');
		}
		if (!file_exists("$source/DB/$db_engine.sql")) {
			throw new RuntimeException("Can't find system tables structure for selected database engine! Installation aborted.");
		}
	}
	/**
	 * @param string $source
	 *
	 * @return array[]
	 */
	protected static function initialize_filesystem ($source) {
		$file_index_map = json_decode(file_get_contents("$source/fs_installer.json"), true);
		require_once "$source/fs/".$file_index_map['core/thirdparty/upf.php'];
		require_once "$source/fs/".$file_index_map['core/functions.php'];
		// Remove default autoloader, since we have special autoloader suitable for operating inside installer where default will fail hard
		spl_autoload_unregister(spl_autoload_functions()[0]);
		/**
		 * Special autoloader for installer
		 */
		spl_autoload_register(
			function ($class) use ($file_index_map, $source) {
				$prepared_class_name = ltrim($class, '\\');
				if (strpos($prepared_class_name, 'cs\\') === 0) {
					$prepared_class_name = substr($prepared_class_name, 3);
				}
				$prepared_class_name = explode('\\', $prepared_class_name);
				$namespace           = count($prepared_class_name) > 1 ? implode('/', array_slice($prepared_class_name, 0, -1)) : '';
				$class_name          = array_pop($prepared_class_name);
				/**
				 * Try to load classes from different places. If not found in one place - try in another.
				 */
				if (
					strlen($file = @$file_index_map[str_replace('//', '/', "core/classes/$namespace/$class_name.php")]) ||    //Core classes
					strlen($file = @$file_index_map[str_replace('//', '/', "core/thirdparty/$namespace/$class_name.php")]) || //Third party classes
					strlen($file = @$file_index_map[str_replace('//', '/', "core/traits/$namespace/$class_name.php")]) ||     //Core traits
					strlen($file = @$file_index_map[str_replace('//', '/', "core/engines/$namespace/$class_name.php")]) ||    //Core engines
					strlen($file = @$file_index_map[str_replace('//', '/', "components/$namespace/$class_name.php")])         //Classes in modules and plugins
				) {
					require_once "$source/fs/$file";
					return true;
				}
				return false;
			}
		);
		return $file_index_map;
	}
	/**
	 * @param array[] $file_index_map
	 * @param string  $source
	 * @param string  $target
	 *
	 * @throws RuntimeException
	 */
	protected static function extract ($file_index_map, $source, $target) {
		/**
		 * Extracting of engine's files
		 */
		foreach ($file_index_map as $file_path => $file_index) {
			$dir = dirname("$target/$file_path");
			if (!@mkdir($dir, 0770, true) && !is_dir($dir)) {
				throw new RuntimeException("Can't extract system files from the archive, creating directory $dir failed! Installation aborted.");
			}
			if (!copy("$source/fs/$file_index", "$target/$file_path")) {
				throw new RuntimeException("Can't extract system files from the archive, creating file $target/$file_path failed! Installation aborted.");
			}
		}
		file_put_json("$target/core/fs.json", array_keys(file_get_json("$source/fs.json")));
		if (
			(
				!@mkdir("$target/storage", 0770) && !is_dir("$target/storage")
			) ||
			!file_put_contents("$target/storage/.htaccess", "Deny from all\nRewriteEngine Off\n<Files *>\n\tSetHandler default-handler\n</Files>")
		) {
			throw new RuntimeException("Can't extract system files from the archive! Installation aborted.");
		}
		chmod("$target/cli", 0775);
	}
	/**
	 * @param string $target
	 * @param string $domain
	 * @param string $timezone
	 * @param string $db_host
	 * @param string $db_engine
	 * @param string $db_name
	 * @param string $db_user
	 * @param string $db_password
	 * @param string $db_prefix
	 * @param string $language
	 * @param string $public_key
	 *
	 * @throws RuntimeException
	 */
	protected static function initialize_core_config (
		$target,
		$domain,
		$timezone,
		$db_host,
		$db_engine,
		$db_name,
		$db_user,
		$db_password,
		$db_prefix,
		$language,
		$public_key
	) {
		$db_password = str_replace('"', '\\"', $db_password);
		$config      = str_replace(
			['@domain', '@timezone', '@db_host', '@db_type', '@db_name', '@db_user', '@db_password', '@db_prefix', '@language', '@public_key'],
			[$domain, $timezone, $db_host, $db_engine, $db_name, $db_user, $db_password, $db_prefix, $language, $public_key],
			self::MAIN_CONFIG_STUB
		);
		if (!file_put_contents("$target/config/main.json", $config)) {
			throw new RuntimeException("Can't write core system configuration! Installation aborted.");
		}
		chmod("$target/config/main.json", 0600);
	}
	/**
	 * @param DB\_Abstract $cdb
	 * @param string       $source
	 * @param string       $db_engine
	 *
	 * @throws RuntimeException
	 */
	protected static function initialize_db_structure ($cdb, $source, $db_engine) {
		$query = array_filter(
			explode(';', file_get_contents("$source/DB/$db_engine.sql")),
			'trim'
		);
		if (!$cdb->q($query)) {
			throw new RuntimeException("Can't import system tables structure for selected database engine! Installation aborted.");
		}
	}
	/**
	 * @param DB\_Abstract $cdb
	 * @param string       $source
	 * @param string       $site_name
	 * @param string       $url
	 * @param string       $admin_email
	 * @param string       $language
	 * @param string       $domain
	 * @param string       $timezone
	 * @param int          $mode
	 *
	 * @throws RuntimeException
	 */
	protected static function initialize_system_config ($cdb, $source, $site_name, $url, $admin_email, $language, $domain, $timezone, $mode) {
		$config     = [
			'name'                              => $site_name,
			'url'                               => [$url],
			'admin_email'                       => $admin_email,
			'closed_title'                      => 'Site closed',
			'closed_text'                       => '<p>Site closed for maintenance</p>',
			'site_mode'                         => 1,
			'title_delimiter'                   => ' | ',
			'title_reverse'                     => 0,
			'cache_compress_js_css'             => 1,
			'frontend_load_optimization'        => 1,
			'vulcanization'                     => 1,
			'put_js_after_body'                 => 1,
			'theme'                             => 'CleverStyle',
			'language'                          => $language,
			'active_languages'                  => [$language],
			'multilingual'                      => 0,
			'db_balance'                        => 0,
			'db_mirror_mode'                    => DB::MIRROR_MODE_MASTER_MASTER,
			'cookie_prefix'                     => '',
			'cookie_domain'                     => [$domain],
			'inserts_limit'                     => 1000,
			'key_expire'                        => 60 * 2,
			'gravatar_support'                  => 0,
			'session_expire'                    => 3600 * 24 * 30,
			'update_ratio'                      => 75,
			'sign_in_attempts_block_count'      => 0,
			'sign_in_attempts_block_time'       => 5,
			'timezone'                          => $timezone,
			'password_min_length'               => 4,
			'password_min_strength'             => 3,
			'smtp'                              => 0,
			'smtp_host'                         => '',
			'smtp_port'                         => '',
			'smtp_secure'                       => '',
			'smtp_auth'                         => 0,
			'smtp_user'                         => '',
			'smtp_password'                     => '',
			'mail_from'                         => $admin_email,
			'mail_from_name'                    => "Administrator of $site_name",
			'allow_user_registration'           => 1,
			'require_registration_confirmation' => 1,
			'auto_sign_in_after_registration'   => 1,
			'registration_confirmation_time'    => 1,
			'mail_signature'                    => '',
			'remember_user_ip'                  => 0,
			'simple_admin_mode'                 => $mode,
			'default_module'                    => Config::SYSTEM_MODULE
		];
		$components = [
			'modules' => [
				'System' => [
					'active' => Config\Module_Properties::ENABLED,
					'db'     => [
						'keys'  => '0',
						'users' => '0',
						'texts' => '0'
					]
				]
			],
			'plugins' => [],
			'blocks'  => []
		];
		foreach (file_get_json("$source/modules.json") as $module) {
			$components['modules'][$module] = [
				'active'  => Config\Module_Properties::UNINSTALLED,
				'db'      => [],
				'storage' => []
			];
		}
		$result = $cdb->q(
			"INSERT INTO `[prefix]config` (
				`domain`, `core`, `db`, `storage`, `components`
			) VALUES (
				'%s', '%s', '[]', '[]', '%s'
			)",
			$domain,
			_json_encode($config),
			_json_encode($components)
		);
		if (!$result) {
			throw new RuntimeException("Can't import system configuration into database! Installation aborted.");
		}
	}
	/**
	 * @param DB\_Abstract $cdb
	 * @param string       $admin_email
	 * @param string       $admin_password
	 * @param string       $public_key
	 *
	 * @throws RuntimeException
	 */
	protected static function create_root_administrator ($cdb, $admin_email, $admin_password, $public_key) {
		$admin_email = strtolower($admin_email);
		$admin_login = strstr($admin_email, '@', true);
		$result      = $cdb->q(
			"INSERT INTO `[prefix]users` (
				`login`, `login_hash`, `password_hash`, `email`, `email_hash`, `reg_date`, `reg_ip`, `status`
			) VALUES (
				'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
			)",
			$admin_login,
			hash('sha224', $admin_login),
			password_hash(hash('sha512', hash('sha512', $admin_password).$public_key), PASSWORD_DEFAULT),
			$admin_email,
			hash('sha224', $admin_email),
			time(),
			'127.0.0.1',
			User::STATUS_ACTIVE
		);
		if (!$result) {
			throw new RuntimeException("Can't register root administrator user! Installation aborted.");
		}
	}
}
