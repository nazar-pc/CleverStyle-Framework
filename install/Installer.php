<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Installer
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
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
	"db_driver"			: "@db_driver",
	"db_name"			: "@db_name",
	"db_user"			: "@db_user",
	"db_password"		: "@db_password",
	"db_prefix"			: "@db_prefix",
//Settings of main Storage
	"storage_driver"		: "Local",
	"storage_url"		: "",
	"storage_host"		: "localhost",
	"storage_user"		: "",
	"storage_password"	: "",
//Base language
	"language"			: "@language",
//Cache driver
	"cache_driver"		: "FileSystem",
//Settings of Memcached cache driver
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
	 * @param string $db_driver
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
	public static function install (
		$source,
		$target,
		$site_name,
		$url,
		$timezone,
		$db_host,
		$db_driver,
		$db_name,
		$db_user,
		$db_password,
		$db_prefix,
		$language,
		$admin_email,
		$admin_password,
		$mode
	) {
		static::pre_installation_checks($source, $target, $db_driver);
		$file_index_map = static::initialize_filesystem($source);
		static::extract($file_index_map, $source, $target);
		$domain     = explode('/', $url)[2];
		$public_key = hash('sha512', random_bytes(1000));
		static::initialize_core_config(
			$target,
			$domain,
			$timezone,
			$db_host,
			$db_driver,
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
		$cdb = "cs\\DB\\$db_driver";
		$cdb = new $cdb($db_name, $db_user, $db_password, $db_host, $db_prefix);
		if (!is_object($cdb) || !$cdb->connected()) {
			throw new RuntimeException("Can't connect to database! Installation aborted.");
		}
		static::initialize_db_structure($cdb, $source, $db_driver);
		static::initialize_system_config($cdb, $source, $site_name, $url, $admin_email, $language, $domain, $timezone, $mode);
		static::create_root_administrator($cdb, $admin_email, $admin_password, $public_key);
		unset($cdb);
	}
	/**
	 * @param string $source
	 * @param string $target
	 * @param string $db_driver
	 *
	 * @throws RuntimeException
	 */
	protected static function pre_installation_checks ($source, $target, $db_driver) {
		if (file_exists("$target/config/main.json")) {
			throw new RuntimeException('"config/main.json" file already present! Installation aborted.');
		}
		if (!file_exists("$source/DB/$db_driver.sql")) {
			throw new RuntimeException("Can't find system tables structure for selected database driver! Installation aborted.");
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
		spl_autoload_unregister(array_reverse(spl_autoload_functions())[0]);
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
					strlen($file = @$file_index_map[str_replace('//', '/', "core/drivers/$namespace/$class_name.php")]) ||    //Core drivers
					strlen($file = @$file_index_map[str_replace('//', '/', "$namespace/$class_name.php")])                    //Classes in modules
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
		 * Extracting of system files
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
		/**
		 * Make CLI executable
		 */
		chmod("$target/cli", 0770);
	}
	/**
	 * @param string $target
	 * @param string $domain
	 * @param string $timezone
	 * @param string $db_host
	 * @param string $db_driver
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
		$db_driver,
		$db_name,
		$db_user,
		$db_password,
		$db_prefix,
		$language,
		$public_key
	) {
		$db_password = str_replace('"', '\\"', $db_password);
		$config      = str_replace(
			['@domain', '@timezone', '@db_host', '@db_driver', '@db_name', '@db_user', '@db_password', '@db_prefix', '@language', '@public_key'],
			[$domain, $timezone, $db_host, $db_driver, $db_name, $db_user, $db_password, $db_prefix, $language, $public_key],
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
	 * @param string       $db_driver
	 *
	 * @throws RuntimeException
	 */
	protected static function initialize_db_structure ($cdb, $source, $db_driver) {
		$query = array_filter(
			explode(';', file_get_contents("$source/DB/$db_driver.sql")),
			'trim'
		);
		if (!$cdb->q($query)) {
			throw new RuntimeException("Can't import system tables structure for selected database driver! Installation aborted.");
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
			'site_name'         => $site_name,
			'url'               => [$url],
			'admin_email'       => $admin_email,
			'language'          => $language,
			'active_languages'  => [$language],
			'cookie_domain'     => [$domain],
			'timezone'          => $timezone,
			'mail_from'         => $admin_email,
			'mail_from_name'    => "Administrator of $site_name",
			'simple_admin_mode' => $mode
		];
		$components = [
			'modules' => [
				'System' => [
					'active' => Config\Module_Properties::ENABLED,
					'db'     => [
						'keys'  => 0,
						'users' => 0,
						'texts' => 0
					]
				]
			],
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
