<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			Closure,
			h;
/**
 * Core class.
 * Provides loading of base system configuration, encryption, API requests sending
 */
class Core {
	use Singleton;

	public				$Loaded				= [];
	protected			$constructed		= false,	//Is object constructed
						$config				= [],
						$List				= [],
						$iv,
						$td,
						$key,
						$encrypt_support	= false;
	/**
	 * Loading of base system configuration, creating of missing directories
	 */
	protected function construct () {
		if (!file_exists(CONFIG.'/main.json')) {
			code_header(404);
			$this->__finish();
		}
		$this->config		= _json_decode_nocomments(file_get_contents(CONFIG.'/main.json'));
		_include_once(CONFIG.'/main.php', false);
		defined('DEBUG') || define('DEBUG', false);
		define('DOMAIN', $this->config['domain']);
		date_default_timezone_set($this->config['timezone']);
		if (!is_dir(STORAGE)) {
			@mkdir(STORAGE, 0755);
			file_put_contents(
				STORAGE.'/.htaccess',
				'Allow From All'
			);
		}
		if (!is_dir(CACHE)) {
			@mkdir(CACHE, 0700);
			file_put_contents(
				CACHE.'/.gitignore',
				"#do not commit cache\n".
				"/*\n".
				"!/.gitignore\n".
				"!/.htaccess"
			);
		}
		if (!is_dir(PCACHE)) {
			@mkdir(PCACHE, 0755);
			file_put_contents(
				PCACHE.'/.htaccess',
				"Allow From All\r\nAddEncoding gzip .js\n".
				"AddEncoding gzip .css"
			);
			file_put_contents(
				PCACHE.'/.gitignore',
				"#do not commit public cache\n".
				"/*\n".
				"!/.gitignore\n".
				"!/.htaccess"
			);
		}
		if (!is_dir(LOGS)) {
			@mkdir(LOGS, 0700);
			file_put_contents(
				PCACHE.'/.gitignore',
				"#do not commit logs\n".
				"/*\n".
				"!/.gitignore\n".
				"!/.htaccess"
			);
		}
		if (!is_dir(TEMP)) {
			@mkdir(TEMP, 0755);
			file_put_contents(
				TEMP.'/.htaccess',
				'Allow From All'
			);
			file_put_contents(
				TEMP.'/.gitignore',
				"#do not commit temp files\n".
				"/*\n".
				"!/.gitignore\n".
				"!/.htaccess"
			);
		}
		if ($this->encrypt_support = check_mcrypt()) {
			$this->key	= $this->config['key'];
			$this->iv	= $this->config['iv'];
		}
		if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json') {
			$_POST	= _json_decode(@file_get_contents('php://input')) ?: [];
		}
		$this->constructed	= true;
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
	 * @deprecated
	 *
	 * Creating of global object on the base of class
	 *
	 * @param array|string|string[]	$class			Class name, on the base of which object will be created. May be string of class name,
	 * 												or <i>array($class, $object_name)</b>, or indexed array of mentioned arrays
	 * @param bool|null				$object_name	If this parameter is <i>null</b> - name of global object will be the same as class name, otherwise,
	 * 												as name specified in this parameter
	 *
	 * @return bool|object							Created object on success or <i>false</i> on failure, <i>true</i> if <i>$class</i> is array
	 */
	function create ($class, $object_name = null) {
		if (empty($class) || defined('STOP')) {
			return false;
		} elseif (!is_array($class)) {
			$loader = false;
			if (substr($class, 0, 1) == '_') {
				$class	= substr($class, 1);
				$loader	= true;
			}
			$prefix	= explode('\\', $class, 2)[0];
			if (!$loader && $prefix == 'cs' && class_exists('cs\\custom'.substr($class, 2), false)) {
				$class	= 'cs\\custom'.substr($class, 2);
			}
			unset($prefix);
			if ($loader || class_exists($class)) {
				if ($object_name === null) {
					$object_name = explode('\\', $class);
					$object_name = array_pop($object_name);
				}
				global $$object_name;
				if (!is_object($$object_name) || $$object_name instanceof Loader) {
					if ($loader) {
						$$object_name				= new Loader($class, $object_name);
					} else {
						$this->List[$object_name]	= $object_name;
						$$object_name				= new $class;
						$this->Loaded[$object_name]	= [microtime(true), memory_get_usage()];
					}
				}
				return $$object_name;
			} else {
				trigger_error('Class '.h::b($class, ['level' => 0]).' not exists', E_USER_ERROR);
				return false;
			}
		} else {
			foreach ($class as $c) {
				if (is_array($c)) {
					$this->create($c[0], isset($c[1]) ? $c[1] : false);
				} else {
					$this->create($c);
				}
			}
		}
		return true;
	}
	/**
	 * Encryption of data
	 *
	 * @param string      $data	Data to be encrypted
	 * @param bool|string $key	Key, if not specified - system key will be used
	 *
	 * @return bool|string
	 */
	function encrypt ($data, $key = false) {
		if (!$this->encrypt_support) {
			return $data;
		}
		if (!is_resource($this->td)) {
			$this->td	= mcrypt_module_open(MCRYPT_BLOWFISH,'','cbc','');
			$this->key	= mb_substr($this->key, 0, mcrypt_enc_get_key_size($this->td));
			$this->iv	= mb_substr(md5($this->iv), 0, mcrypt_enc_get_iv_size($this->td));
		}
		if ($key === false) {
			$key		= $this->key;
		} else {
			$key		= mb_substr(md5($this->key).md5($key), 0, mcrypt_enc_get_key_size($this->td));
		}
		mcrypt_generic_init($this->td, $key, $this->iv);
		$encrypted = mcrypt_generic($this->td, @serialize([
			'key'	=> $key,
			'data'	=> $data
		]));
		mcrypt_generic_deinit($this->td);
		if ($encrypted) {
			return $encrypted;
		} else {
			return false;
		}
	}
	/**
	 * Decryption of data
	 *
	 * @param string      $data	Data to be decrypted
	 * @param bool|string $key	Key, if not specified - system key will be used
	 *
	 * @return bool|mixed
	 */
	function decrypt ($data, $key = false) {
		if (!$this->encrypt_support) {
			return $data;
		}
		if (!is_resource($this->td)) {
			$this->td	= mcrypt_module_open(MCRYPT_BLOWFISH,'','cbc','');
			$this->key	= mb_substr($this->key, 0, mcrypt_enc_get_key_size($this->td));
			$this->iv	= mb_substr(md5($this->iv), 0, mcrypt_enc_get_iv_size($this->td));
		}
		if ($key === false) {
			$key		= $this->key;
		} else {
			$key		= mb_substr(md5($this->key).md5($key), 0, mcrypt_enc_get_key_size($this->td));
		}
		mcrypt_generic_init($this->td, $key, $this->iv);
		errors_off();
		$decrypted = @unserialize(mdecrypt_generic($this->td, $data));
		errors_on();
		mcrypt_generic_deinit($this->td);
		if (is_array($decrypted) && $decrypted['key'] == $key) {
			return $decrypted['data'];
		} else {
			return false;
		}
	}
	/**
	 * Sending system api request to all mirrors
	 *
	 * @param string	$path	Path for api request, for example <i>System/admin/setcookie<i>, where
	 * 							<i>System</b> - module name, <i>admin/setcookie</b> - path to action file in current module api structure
	 * @param mixed		$data	Any type of data, will be accessible through <i>$_POST['data']</b>
	 *
	 * @return array	Array <i>[mirror_url => result]</b> in case of successful connection, <i>false</b> on failure
	 */
	function api_request ($path, $data = '') {
		$Config	= Config::instance(true) ? Config::instance() : null;
		$result	= [];
		if (is_object($Config) && $Config->server['mirrors']['count'] > 1) {
			foreach ($Config->server['mirrors']['http'] as $domain) {
				if (!($domain == $Config->server['host'] && $Config->server['protocol'] == 'http')) {
					$result['http://'.$domain] = $this->send('http://'.$domain.'/api/'.$path, $data);
				}
			}
			foreach ($Config->server['mirrors']['https'] as $domain) {
				if (!($domain != $Config->server['host'] && $Config->server['protocol'] == 'https')) {
					$result['https://'.$domain] = $this->send('https://'.$domain.'/api/'.$path, $data);
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
		$url				= $url.'/'.$key;
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
		$socket				= fsockopen($host[0], isset($host[1]) ? $host[1] : $protocol == 'http' ? 80 : 443, $errno, $errstr);
		$host				= implode(':', $host);
		if(!is_resource($socket)) {
			trigger_error('#'.$errno.' '.$errstr, E_USER_WARNING);
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
			$data."\r\n\r\n"
		);
		$return = explode("\r\n\r\n", stream_get_contents($socket), 2);
		time_limit_pause(false);
		fclose($socket);
		return $return[1];
	}
	/**
	 * @deprecated
	 *
	 * Registration of triggers for actions
	 *
	 * @param string	$trigger	For example <i>admin/System/components/plugins/disable</i>
	 * @param Closure	$closure	Closure, that will be called at trigger running
	 *
	 * @return bool
	 */
	function register_trigger ($trigger, $closure) {
		return (bool)Trigger::instance()->register($trigger, $closure);
	}
	/**
	 * @deprecated
	 *
	 * Running triggers for some actions
	 *
	 * @param string	$trigger	For example <i>admin/System/components/plugins/disable</i>
	 * @param mixed		$data		For example ['name'	=> <i>plugin_name</i>]
	 *
	 * @return bool
	 */
	function run_trigger ($trigger, $data = null) {
		return (bool)Trigger::instance()->run($trigger, $data);
	}
	/**
	 * Disabling encryption.<br>
	 * Correct termination.
	 */
	function __destruct () {
		if (is_resource($this->td)) {
			mcrypt_module_close($this->td);
			unset($this->key, $this->iv, $this->td);
		}
		exit;
	}
}