<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Core,
	cs\DB,
	cs\Language\Prefix,
	cs\Page;

trait about_server {
	/**
	 * Get information about server
	 */
	static function admin_about_server_get () {
		$Core = Core::instance();
		$L    = new Prefix('system_filesize_');
		Page::instance()->json(
			[
				'operating_system' => php_uname('s').' '.php_uname('r').' '.php_uname('v'),
				'server_type'      => static::admin_about_server_get_server_api(),
				'available_ram'    => ini_get('memory_limit') < 0 ? false : format_filesize(
					str_replace(
						['K', 'M', 'G'],
						[" $L->KiB", " $L->MiB", " $L->GiB"],
						ini_get('memory_limit')
					)
				),
				'php_extensions'   => [
					'openssl'   => extension_loaded('openssl'),
					'curl'      => extension_loaded('curl'),
					'apc'       => extension_loaded('apc'),
					'apcu'      => extension_loaded('apcu'),
					'memcached' => extension_loaded('memcached')
				],
				'main_db'          => [
					'type'    => $Core->db_type,
					'version' => DB::instance()->server(),
					'host'    => $Core->db_host,
					'name'    => $Core->db_name,
					'prefix'  => $Core->db_prefix
				],
				'main_storage'     => [
					'type' => $Core->storage_type
				],
				'cache_engine'     => $Core->cache_engine,
				'free_disk_space'  => format_filesize(disk_free_space('./'), 2),
				'php_ini'          => [
					'allow_file_uploads'     => (bool)ini_get('file_uploads'),
					'max_file_uploads'       => (int)ini_get('max_file_uploads'),
					'upload_size_limit'      => format_filesize(
						str_replace(
							['K', 'M', 'G'],
							[" $L->KiB", " $L->MiB", " $L->GiB"],
							ini_get('upload_max_filesize') ?: ini_get('hhvm.server.upload.upload_max_file_size')
						)
					),
					'post_max_size'          => format_filesize(
						str_replace(
							['K', 'M', 'G'],
							[" $L->KiB", " $L->MiB", " $L->GiB"],
							ini_get('post_max_size') ?: ini_get('hhvm.server.max_post_size')
						)
					),
					'max_execution_time'     => format_time(ini_get('max_execution_time')),
					'max_input_time'         => format_time(ini_get('max_input_time') ?: ini_get('hhvm.http.default_timeout')),
					'default_socket_timeout' => format_time(ini_get('default_socket_timeout')),
					'allow_url_fopen'        => (bool)ini_get('allow_url_fopen'),
					'display_errors'         => (bool)ini_get('display_errors'),
				]
			]
		);
	}
	/**
	 * Returns server type
	 *
	 * @return string
	 */
	static private function admin_about_server_get_server_api () {
		$phpinfo = ob_wrapper('phpinfo');
		if (stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false) {
			preg_match(
				'/Apache[\-\/]([0-9\.\-]+)/',
				ob_wrapper('phpinfo'),
				$version
			);
			$return = "Apache $version[1]";
			if (stripos($phpinfo, 'mod_php') !== false) {
				$return .= ' + mod_php + PHP '.PHP_VERSION;
			}
			return $return;
		} elseif (stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false) {
			$return = 'Nginx '.explode('/', $_SERVER['SERVER_SOFTWARE'])[1];
			if (stripos($phpinfo, 'php-fpm') !== false) {
				$return .= ' + PHP-FPM '.PHP_VERSION;
			} elseif (defined('HHVM_VERSION')) {
				$return .= ' + HHVM '.HHVM_VERSION;
			}
			return $return;
		} elseif (isset($_SERVER['SERVER_SOFTWARE'])) {
			return $_SERVER['SERVER_SOFTWARE'];
		} else {
			return '';
		}
	}
}
