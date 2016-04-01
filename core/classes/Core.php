<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

/**
 * Core class.
 * Provides loading of base system configuration
 *
 * @method static $this instance($check = false)
 *
 * @property string $domain
 * @property string $timezone
 * @property string $db_host
 * @property string $db_type
 * @property string $db_name
 * @property string $db_user
 * @property string $db_password
 * @property string $db_prefix
 * @property string $db_charset
 * @property string $storage_type
 * @property string $storage_url
 * @property string $storage_host
 * @property string $storage_user
 * @property string $storage_password
 * @property string $language
 * @property string $cache_engine
 * @property string $memcache_host
 * @property string $memcache_port
 * @property string $public_key
 */
class Core {
	use Singleton;
	/**
	 * Is object constructed
	 * @var bool
	 */
	protected $constructed = false;
	/**
	 * @var mixed[]
	 */
	protected $config = [];
	/**
	 * Loading of base system configuration, creating of missing directories
	 *
	 * @throws ExitException
	 */
	protected function construct () {
		$this->config = $this->load_config();
		_include_once(DIR.'/config/main.php', false);
		defined('DEBUG') || define('DEBUG', false);
		defined('DOMAIN') || define('DOMAIN', $this->config['domain']);
		date_default_timezone_set($this->config['timezone']);
		if (!is_dir(PUBLIC_STORAGE)) {
			/** @noinspection MkdirRaceConditionInspection */
			@mkdir(PUBLIC_STORAGE, 0775, true);
			file_put_contents(
				PUBLIC_STORAGE.'/.htaccess',
				'Allow From All
<ifModule mod_headers.c>
	Header always append X-Frame-Options DENY
	Header set Content-Type application/octet-stream
</ifModule>
'
			);
		}
		if (!is_dir(CACHE)) {
			/** @noinspection MkdirRaceConditionInspection */
			@mkdir(CACHE, 0770);
		}
		if (!is_dir(PUBLIC_CACHE)) {
			/** @noinspection MkdirRaceConditionInspection */
			@mkdir(PUBLIC_CACHE, 0770);
			file_put_contents(
				PUBLIC_CACHE.'/.htaccess',
				'<FilesMatch "\.(css|js|html)$">
	Allow From All
</FilesMatch>
<ifModule mod_expires.c>
	ExpiresActive On
	ExpiresDefault "access plus 1 month"
</ifModule>
<ifModule mod_headers.c>
	Header set Cache-Control "max-age=2592000, public"
</ifModule>
AddEncoding gzip .js
AddEncoding gzip .css
AddEncoding gzip .html
'
			);
		}
		if (!is_dir(LOGS)) {
			/** @noinspection MkdirRaceConditionInspection */
			@mkdir(LOGS, 0770);
		}
		if (!is_dir(TEMP)) {
			/** @noinspection MkdirRaceConditionInspection */
			@mkdir(TEMP, 0775);
			file_put_contents(
				TEMP.'/.htaccess',
				"Allow From All\n"
			);
		}
		$this->constructed = true;
	}
	/**
	 * Load main.json config file and return array of it contents
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	protected function load_config () {
		if (!file_exists(DIR.'/config/main.json')) {
			if (PHP_SAPI == 'cli') {
				echo <<<CONFIG_NOT_FOUND
Config file not found, is system installed properly?
How to install CleverStyle CMS: https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installation
CONFIG_NOT_FOUND;
				exit(500);
			} else {
				echo /** @lang HTML */
				<<<CONFIG_NOT_FOUND
<!doctype html>
<p>Config file not found, is system installed properly?</p>
<a href="https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installation">How to install CleverStyle CMS</a>
CONFIG_NOT_FOUND;
				http_response_code(500);
				exit;
			}
		}
		return file_get_json_nocomments(DIR.'/config/main.json');
	}
	/**
	 * Getting of base configuration parameter
	 *
	 * @param string $item
	 *
	 * @return false|string
	 */
	function get ($item) {
		return isset($this->config[$item]) ? $this->config[$item] : false;
	}
	/**
	 * Setting of base configuration parameter (available only at object construction)
	 *
	 * @param string $item
	 * @param mixed  $value
	 */
	function set ($item, $value) {
		if (!$this->constructed) {
			$this->config[$item] = $value;
		}
	}
	/**
	 * Getting of base configuration parameter
	 *
	 * @param string $item
	 *
	 * @return false|string
	 */
	function __get ($item) {
		return $this->get($item);
	}
	/**
	 * Setting of base configuration parameter (available only at object construction)
	 *
	 * @param string $item
	 * @param mixed  $value
	 */
	function __set ($item, $value) {
		$this->set($item, $value);
	}
}
