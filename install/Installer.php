<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Installer
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
use
	Exception;

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
//Any length
	"public_key"		: "@public_key"
}

CONFIG;
	/**
	 * @param string $source
	 * @param string $target
	 * @param string $db_engine
	 *
	 * @throws Exception
	 */
	static function install ($source, $target, $db_engine) {
		static::pre_installation_checks($source, $target, $db_engine);
		// Needed to be defined before connecting to the database
		defined('DEBUG') || define('DEBUG', false);
		$file_index_map = static::initialize_filesystem($source);
		static::extract($file_index_map, $source, $target);
	}
	/**
	 * @param string $source
	 * @param string $target
	 * @param string $db_engine
	 *
	 * @throws Exception
	 */
	protected static function pre_installation_checks ($source, $target, $db_engine) {
		if (file_exists("$target/config/main.json")) {
			throw new Exception('"config/main.json" file already present! Installation aborted.');
		}
		if (!file_exists("$source/install/DB/$db_engine.sql")) {
			throw new Exception("Can't find system tables structure for selected database engine! Installation aborted.");
		}
	}
	/**
	 * @param string $source
	 *
	 * @return array[]
	 */
	protected static function initialize_filesystem ($source) {
		$file_index_map = json_decode(file_get_contents("$source/fs.json"), true);
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
					/** @noinspection UntrustedInclusionInspection */
					require_once "$source/fs/$file";
					return true;
				}
				return false;
			},
			true,
			true
		);
		require_once "$source/fs/".$file_index_map['core/thirdparty/upf.php'];
		require_once "$source/fs/".$file_index_map['core/functions.php'];
		// Remove default autoloader, since we have special autoloader suitable for operating inside installer where default will fail hard
		spl_autoload_unregister(spl_autoload_functions()[0]);
		return $file_index_map;
	}
	/**
	 * @param array[] $file_index_map
	 * @param string  $source
	 * @param string  $target
	 *
	 * @throws Exception
	 */
	protected static function extract ($file_index_map, $source, $target) {
		/**
		 * Extracting of engine's files
		 */
		foreach ($file_index_map as $index => $file) {
			$dir = dirname("$target/$file");
			if (!@mkdir($dir, 0770, true) && !is_dir($dir)) {
				throw new Exception("Can't extract system files from the archive, creating directory $dir failed! Installation aborted.");
			}
			/**
			 * TODO: copy() + file_exists() is a hack for HHVM, when bug fixed upstream (copying of empty files) this should be simplified
			 */
			copy("$source/fs/$index", "$target/$file");
			if (!file_exists("$target/$file")) {
				throw new Exception("Can't extract system files from the archive, creating file $target/$file failed! Installation aborted.");
			}
		}
		if (
			(
				!@mkdir("$target/storage", 0770) && !is_dir("$target/storage")
			) ||
			!file_put_contents("$target/storage/.htaccess", "Deny from all\nRewriteEngine Off\n<Files *>\n\tSetHandler default-handler\n</Files>")
		) {
			throw new Exception("Can't extract system files from the archive! Installation aborted.");
		}
	}
}
