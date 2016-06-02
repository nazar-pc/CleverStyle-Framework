<?php
/**
 * @package   CleverStyle Framework
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
	 */
	protected function construct () {
		$this->config = $this->load_config();
		include_once DIR.'/config/main.php';
		$this->constructed = true;
	}
	/**
	 * Load main.json config file and return array of it contents
	 *
	 * @return array
	 */
	protected function load_config () {
		if (!file_exists(DIR.'/config/main.json')) {
			if (PHP_SAPI == 'cli') {
				echo <<<CONFIG_NOT_FOUND
Config file not found, is system installed properly?
How to install CleverStyle Framework: https://github.com/nazar-pc/CleverStyle-Framework/tree/master/docs/installation/Installation.md

CONFIG_NOT_FOUND;
			} else {
				echo /** @lang HTML */
				<<<CONFIG_NOT_FOUND
<!doctype html>
<p>Config file not found, is system installed properly?</p>
<a href="https://github.com/nazar-pc/CleverStyle-Framework/tree/master/docs/installation/Installation.md">How to install CleverStyle Framework</a>
CONFIG_NOT_FOUND;
				http_response_code(500);
			}
			// Can't proceed without config
			exit(500);
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
