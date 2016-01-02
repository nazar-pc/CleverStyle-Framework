<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use            h;
/**
 * Core class.
 * Provides loading of base system configuration
 *
 * @method static Core instance($check = false)
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
		_include_once(DIR.'/config/main.php', false);
		defined('DEBUG') || define('DEBUG', false);
		defined('DOMAIN') || define('DOMAIN', $this->config['domain']);
		date_default_timezone_set($this->config['timezone']);
		if (!is_dir(PUBLIC_STORAGE)) {
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
			@mkdir(CACHE, 0770);
		}
		if (!is_dir(PUBLIC_CACHE)) {
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
			@mkdir(LOGS, 0770);
		}
		if (!is_dir(TEMP)) {
			@mkdir(TEMP, 0775);
			file_put_contents(
				TEMP.'/.htaccess',
				"Allow From All\n"
			);
		}
		$this->fill_post_request();
		$this->constructed = true;
	}
	/**
	 * Fill `$_POST` and `$_REQUEST` when there is request method different than POST or if Content-Type is JSON
	 */
	protected function fill_post_request () {
		/**
		 * @var _SERVER $_SERVER
		 */
		if (!$_SERVER->content_type) {
			return;
		}
		/**
		 * Support for JSON requests, filling $_POST array for request method different than POST
		 */
		if (preg_match('#^application/([^+\s]+\+)?json#', $_SERVER->content_type)) {
			$_POST = _json_decode(@file_get_contents('php://input')) ?: [];
		} elseif (
			strtolower($_SERVER->request_method) !== 'post' &&
			strpos($_SERVER->content_type, 'application/x-www-form-urlencoded') === 0
		) {
			@parse_str(file_get_contents('php://input'), $_POST);
		}
		$_REQUEST = $_POST + $_REQUEST;
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
			throw new ExitException(
				h::p('Config file not found, is system installed properly?').
				h::a(
					'How to install CleverStyle CMS',
					[
						'href' => 'https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installation'
					]
				),
				500
			);
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
