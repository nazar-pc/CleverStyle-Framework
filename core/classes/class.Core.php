<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			\Closure,
			\h;
/**
 * Core class.
 * Provides loading of system configuration, creating of global objects, encryption, API requests sending, and triggers processing.
 */
class Core {
	public	$Loaded				= [],				//Array with list of loaded objects, and information about amount of used memory and creation time
			$destroy_priority	= [					//Order of global objects destroying
				'Page',
				'User',
				'Config',
				'Key',
				'db',
				'L',
				'Text',
				'Cache',
				'Storage',
				'Error'
			];
	protected	$init				= false,	//For prohibition of re-initialization
				$config				= [],
				$List				= [],
				$iv,
				$td,
				$key,
				$encrypt_support	= false,
				$triggers_init		= false,
				$triggers;
	/**
	 * Loading of base system configuration, creating of missing directories
	 */
	function __construct() {
		if ($this->init) {
			return;
		}
		$this->init	= true;
		if (!file_exists(CONFIG.'/main.json')) {
			error_header(404);
			$this->__finish();
		}
		_include_once(CONFIG.'/main.php', false);
		$this->config	= file(CONFIG.'/main.json', FILE_SKIP_EMPTY_LINES);
		foreach ($this->config as $i => $line) {
			if (substr(ltrim($line), 0, 2) == '//') {
				unset($this->config[$i]);
			}
		}
		$this->config	= _json_decode(implode('', $this->config));
		define('DOMAIN', $this->config['domain']);
		date_default_timezone_set($this->config['timezone']);
		!is_dir(STORAGE)				&& @mkdir(STORAGE, 0755)	&& file_put_contents(
			STORAGE.'/.htaccess',
			'Allow From All'
		);
		!is_dir(CACHE)					&& @mkdir(CACHE, 0700);
		!is_dir(PCACHE)					&& @mkdir(PCACHE, 0755)		&& file_put_contents(
			PCACHE.'/.htaccess',
			"Allow From All\r\nAddEncoding gzip .js\r\nAddEncoding gzip .css"
		);
		!is_dir(LOGS)					&& @mkdir(LOGS, 0700);
		!is_dir(TEMP)					&& @mkdir(TEMP, 0755)		&& file_put_contents(
			TEMP.'/.htaccess',
			'Allow From All'
		);
		if ($this->encrypt_support = check_mcrypt()) {
			$this->key	= $this->config['key'];
			$this->iv	= $this->config['iv'];
		}
		unset($this->config['key'], $this->config['iv']);
	}
	/**
	 * Getting of base configuration parameter
	 *
	 * @param string $item
	 *
	 * @return bool|string
	 */
	function config ($item) {
		return isset($this->config[$item]) ? $this->config[$item] : false;
	}
	/**
	 * Creating of global object on the base of class
	 *
	 * @param array|string|string[]	$class			Class name, on the base of which object will be created. May be string of class name,
	 * 												or <b>array($class, $object_name)</b>, or indexed array of mentioned arrays
	 * @param bool					$object_name	If this parameter is <b>null</b> - name of global object will be the same as class name, otherwise,
	 * 												as name specified in this parameter
	 *
	 * @return bool|object							Created object on success or <b>false</b> on failure
	 */
	function create ($class, $object_name = null) {
		if (empty($class)) {
			return false;
		} elseif (!defined('STOP') && !is_array($class)) {
			$loader = false;
			if (substr($class, 0, 1) == '_') {
				$class	= substr($class, 1);
				$loader	= true;
			}
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
						$$object_name				= new $class();
						$this->createed[$object_name]	= [microtime(true), memory_get_usage()];
					}
				}
				return $$object_name;
			} else {
				trigger_error('Class '.h::b($class, ['level' => 0]).' not exists', E_USER_ERROR);
				return false;
			}
		} elseif (!defined('STOP') && is_array($class)) {
			foreach ($class as $c) {
				if (is_array($c)) {
					$this->create($c[0], isset($c[1]) ? $c[1] : false);
				} else {
					$this->create($c);
				}
			}
		}
		return false;
	}
	/**
	 * Destroying of global object
	 *
	 * @param string|string[]     $object	Name of global object to be destroyed, or array of names
	 */
	function destroy ($object) {
		if (is_array($object)) {
			foreach ($object as $o) {
				$this->destroy($o);
			}
		} else {
			global $$object;
			unset($this->List[$object]);
			method_exists($$object, '__finish') && $$object->__finish();
			$$object = null;
			unset($GLOBALS[$object]);
		}
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
	 * @param string $path	Path for api request, for example <b>System/admin/setcookie<b>, where
	 * <b>System</b> - module name, <b>admin/setcookie</b> - path to action file in current module api structure
	 * @param mixed  $data	$data Any type of data, will be accessible through <b>$_POST['data']</b>
	 *
	 * @return array
	 */
	function api_request ($path, $data = '') {
		global $Config;
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
	 * @param string $url With prefix <b>https://</b> (<b>http://</b> can be missed), and (if necessary) with port address
	 * @param mixed $data Any type of data, will be accessible through <b>$_POST['data']</b>
	 *
	 * @return array|bool Array <b>[0 => headers, 1 => body]</b> in case of successful connection, <b>false</b> on failure
	 */
	protected function send ($url, $data) {
		global $Key, $Config;
		if (!(is_object($Key) && is_object($Config))) {
			return false;
		}
		$protocol	= 'http';
		if (strpos($url, '://') !== false) {
			$protocol	= substr($url, 0, strpos($url, '://'));
			$url		= substr($url, strpos($url, '://')+3);
		}
		$url		= explode('/', $url, 2);
		$host		= explode(':', $url[0]);
		$url		= isset($url[1]) && !empty($url[1]) ? $url[1] : '';
		$database	= $Config->module('System')->db('keys');
		$key		= $Key->generate($database);
		$url		= $url ? $url.'/'.$key : $key;
		$Key->add(
			$database,
			$key,
			['url' => implode(':', $host).'/'.$url],
			time()+30
		);
		$socket	= fsockopen($host[0], isset($host[1]) ? $host[1] : $protocol == 'http' ? 80 : 443, $errno, $errstr);
		if(!is_resource($socket)) {
			trigger_error('#'.$errno.' '.$errstr, E_USER_WARNING);
			return false;
		}
		$data = 'data='.urlencode(json_encode($data));
		time_limit_pause();
		fwrite(
			$socket,
			'POST /'.$url." HTTP/1.1\r\n".
			'Host: '.implode(':', $host)."\r\n".
			"Content-type: application/x-www-form-urlencoded\r\n".
			"Content-length:".strlen($data)."\r\n".
			"Accept:*/*\r\n".
			"User-agent: CleverStyle CMS\r\n\r\n".
			$data."\r\n\r\n"
		);
		$return = explode("\r\n\r\n", stream_get_contents($socket), 2);
		time_limit_pause(false);
		fclose($socket);
		return $return;
	}
	/**
	 * Registration of triggers for actions
	 *
	 * @param array|string $trigger	For example it can be array like <code>[
	 *   'admin' => [
	 *     'System' => [
	 *       'components' => [
	 *         'plugins' => [
	 *           'enable' => [
	 *             function () {{ }
	 *           ]
	 *         ]
	 *       ]
	 *     ]
	 *   ]
	 * ]
	 * </code>
	 * or string<br>
	 * 'admin/System/components/plugins/disable'
	 * @param Closure|null $closure if <b>$trigger</b> is string - this parameter must containg Closure [optional]
	 *
	 * @return bool
	 */
	function register_trigger ($trigger, $closure = null) {
		if (is_string($trigger) && $closure instanceof Closure) {
			$trigger		= explode('/', $trigger);
			$new_trigger	= [];
			$tmp			= &$new_trigger;
			foreach ($trigger as $item) {
				$tmp[$item] = [];
				$tmp		= &$tmp[$item];
			}
			$tmp			= $closure;
			$trigger		= $new_trigger;
			unset($new_trigger, $tmp, $item);
		}
		if (!is_array($trigger)) {
			return false;
		}
		return $this->register_trigger_internal($trigger);
	}
	/**
	 * Registration of triggers for actions
	 *
	 * @param array $trigger
	 * @param array|null $triggers Is used for nested structure
	 *
	 * @return bool
	 */
	protected function register_trigger_internal ($trigger, &$triggers = null) {
		if ((!is_array($trigger) || empty($trigger)) && !($trigger instanceof Closure)) {
			return false;
		}
		if ($triggers === null) {
			$triggers = &$this->triggers;
		}
		if ($trigger instanceof Closure) {
			$triggers[] = $trigger;
			return true;
		}
		$return = true;
		foreach ($trigger as $item => $function) {
			if (!isset($triggers[$item])) {
				$triggers[$item] = [];
			}
			$return = $return && $this->register_trigger_internal($function, $triggers[$item]);
		}
		return $return;
	}
	/**
	 * Running trigers for some actions
	 *
	 * @param string $action	For example <i>admin/System/components/plugins/disable</i>
	 * @param mixed $data		For example ['name'	=> <i>plugin_name</i>]
	 *
	 * @return bool
	 */
	function run_trigger ($action, $data = null) {
		if (!$this->triggers_init) {
			global $Config;
			$modules = array_keys($Config->components['modules']);
			foreach ($modules as $module) {
				_include_once(MODULES.'/'.$module.'/trigger.php', false);
			}
			unset($modules, $module);
			$plugins = get_files_list(PLUGINS, false, 'd');
			if (!empty($plugins)) {
				foreach ($plugins as $plugin) {
					_include_once(PLUGINS.'/'.$plugin.'/trigger.php', false);
				}
			}
			unset($plugins, $plugin);
			$this->triggers_init = true;
		}
		$action = explode('/', $action);
		if (!is_array($action) || empty($action)) {
			return false;
		}
		$triggers = $this->triggers;
		foreach ($action as $item) {
			if (is_array($triggers) && isset($triggers[$item])) {
				$triggers = $triggers[$item];
			} else {
				return true;
			}
		}
		unset($action, $item);
		if (!is_array($triggers) || empty($triggers)) {
			return false;
		}
		$return = true;
		/**
		 * @var Closure[] $triggers
		 */
		foreach ($triggers as $trigger) {
			if ($trigger instanceof Closure) {
				if ($data === null) {
					$return = $return && $trigger();
				} else {
					$return = $return && $trigger($data);
				}
			}
		}
		return $return;
	}
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	function __clone () {}
	/**
	 * Destroying of global objects, cleaning.<br>
	 * Disabling encryption.<br>
	 * Correct termination.
	 */
	function __finish () {
		if (isset($this->List['Index'])) {
			$this->destroy('Index');
		}
		foreach ($this->List as $class) {
			if (!in_array($class, $this->destroy_priority)) {
				$this->destroy($class);
			}
		}
		foreach ($this->destroy_priority as $class) {
			if (isset($this->List[$class])) {
				$this->destroy($class);
			}
		}
		if (is_resource($this->td)) {
			mcrypt_module_close($this->td);
			unset($this->key, $this->iv, $this->td);
		}
		exit;
	}
}