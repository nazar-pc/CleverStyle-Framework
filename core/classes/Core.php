<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
/**
 * Core class.
 * Provides loading of base system configuration, API requests sending
 *
 * @method static Core instance($check = false)
 */
class Core {
	use Singleton;
	/**
	 * Is object constructed
	 * @var bool
	 */
	protected	$constructed		= false;
	/**
	 * @var mixed[]
	 */
	protected	$config				= [];
	/**
	 * Loading of base system configuration, creating of missing directories
	 */
	protected function construct () {
		$this->config	= $this->load_config();
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
		/**
		 * @var _SERVER $_SERVER
		 */
		/**
		 * Support for JSON requests, filling $_POST array for request method different than POST
		 */
		if (isset($_SERVER->content_type)) {
			if (preg_match('#^application/([^+\s]+\+)?json#', $_SERVER->content_type)) {
				foreach (_json_decode(@file_get_contents('php://input')) ?: [] as $i => $v) {
					$_POST[$i]    = $v;
					$_REQUEST[$i] = $v;
				}
			} elseif (
				strtolower($_SERVER->request_method) !== 'post' &&
				strpos($_SERVER->content_type, 'application/x-www-form-urlencoded') === 0
			) {
				@parse_str(file_get_contents('php://input'), $POST);
				foreach ($POST as $i => $v) {
					$_POST[$i]    = $v;
					$_REQUEST[$i] = $v;
				}
			}
		}
		$this->constructed	= true;
	}
	/**
	 * Load main.json config file and return array of it contents
	 *
	 * @return array
	 */
	protected function load_config () {
		if (!file_exists(DIR.'/config/main.json')) {
			error_code(500);
			Page::instance()->error(
				h::p('Config file not found, is system installed properly?').
				h::a(
					'How to install CleverStyle CMS',
					[
						'href'	=> 'https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installation'
					]
				)
			);
		}
		return file_get_json_nocomments(DIR.'/config/main.json');
	}
	/**
	 * Getting of base configuration parameter
	 *
	 * @param string		$item
	 *
	 * @return bool|string
	 */
	function get ($item) {
		return isset($this->config[$item]) ? $this->config[$item] : false;
	}
	/**
	 * Setting of base configuration parameter (available only at object construction)
	 *
	 * @param string	$item
	 * @param mixed		$value
	 */
	function set ($item, $value) {
		if (!$this->constructed) {
			$this->config[$item] = $value;
		}
	}
	/**
	 * Getting of base configuration parameter
	 *
	 * @param string		$item
	 *
	 * @return bool|string
	 */
	function __get ($item) {
		return $this->get($item);
	}
	/**
	 * Setting of base configuration parameter (available only at object construction)
	 *
	 * @param string	$item
	 * @param mixed		$value
	 */
	function __set ($item, $value) {
		$this->set($item, $value);
	}
	/**
	 * Sending system api request to all mirrors
	 *
	 * @deprecated
	 * @todo Remove in future versions
	 *
	 * @param string	$path	Path for api request, for example <i>System/admin/setcookie<i>, where
	 * 							<i>System</b> - module name, <i>admin/setcookie</b> - path to action file in current module api structure
	 * @param mixed		$data	Any type of data, will be accessible through <i>$_POST['data']</b>
	 *
	 * @return array	Array <i>[mirror_url => result]</b> in case of successful connection, <i>false</b> on failure
	 */
	function api_request ($path, $data = '') {
		$Config	= Config::instance(true);
		$result	= [];
		if ($Config && $Config->server['mirrors']['count'] > 1) {
			foreach ($Config->server['mirrors']['http'] as $domain) {
				if (!($domain == $Config->server['host'] && $Config->server['protocol'] == 'http')) {
					$result["http://$domain"] = $this->send("http://$domain/api/$path", $data);
				}
			}
			foreach ($Config->server['mirrors']['https'] as $domain) {
				if (!($domain != $Config->server['host'] && $Config->server['protocol'] == 'https')) {
					$result["https://$domain"] = $this->send("https://$domain/api/$path", $data);
				}
			}
		}
		return $result;
	}
	/**
	 * Sending of api request to the specified host
	 *
	 * @param string	$url	With prefix <i>https://</b> (<i>http://</b> can be missed), and (if necessary) with port address
	 * @param mixed		$data	Any type of data, will be accessible through <i>$_POST['data']</b>
	 *
	 * @return bool|string		Result or <i>false</i> at error
	 */
	protected function send ($url, $data) {
		if (!Config::instance(true)) {
			return false;
		}
		$Key				= Key::instance();
		$protocol			= 'http';
		if (mb_strpos($url, '://') !== false) {
			list($protocol,	$url) = explode('://', $url);
		}
		$database			= Config::instance()->module('System')->db('keys');
		$key				= $Key->generate($database);
		$url				.= "/$key";
		$Key->add(
			$database,
			$key,
			[
				'url' => $url
			],
			time()+30
		);
		list($host, $url)	= explode('/', $url, 2);
		$host				= explode(':', $host);
		$socket				= fsockopen($host[0], isset($host[1]) ? $host[1] : ($protocol == 'http' ? 80 : 443), $errno, $errstr);
		$host				= implode(':', $host);
		if(!is_resource($socket)) {
			trigger_error("#$errno $errstr", E_USER_WARNING);
			return false;
		}
		$data = 'data='.urlencode(json_encode($data));
		time_limit_pause();
		fwrite(
			$socket,
			"POST /$url HTTP/1.1\r\n".
			"Host: $host\r\n".
			"Content-Type: application/x-www-form-urlencoded\r\n".
			"Content-length:".strlen($data)."\r\n".
			"Accept:*/*\r\n".
			"User-agent: CleverStyle CMS\r\n\r\n".
			"$data\r\n\r\n"
		);
		$return = explode("\r\n\r\n", stream_get_contents($socket), 2);
		time_limit_pause(false);
		fclose($socket);
		return $return[1];
	}
}
