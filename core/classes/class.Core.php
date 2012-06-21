<?php
//Класс ядра
class Core {
	protected	$iv,
				$td,
				$key,
				$encrypt_support	= false,
				$triggers_init		= false,
				$triggers;

	function __construct() {
		if (!_require(CONFIG.DS.CDOMAIN.DS.'main.php', true, false)) {
			error_header(404);
			__finish();
		}
		define('STORAGE',	STORAGES.DS.DOMAIN.DS.'public');	//Local public storage for current domain
		define('CACHE',		STORAGES.DS.DOMAIN.DS.'cache');		//Cache directory for current domain
		define('LOGS',		STORAGES.DS.DOMAIN.DS.'logs');		//Log directory for current domain
		define('TEMP',		STORAGES.DS.DOMAIN.DS.'temp');		//Temp directory for current domain
		!_is_dir(STORAGES.DS.DOMAIN)	&& @_mkdir(STORAGES.DS.DOMAIN, 0770);
		!_is_dir(STORAGE)				&& @_mkdir(STORAGE, 0777)	&& _file_put_contents(
			STORAGE.DS.'.htaccess',
			'Allow From All'
		);
		!_is_dir(CACHE)					&& @_mkdir(CACHE, 0770);
		!_is_dir(PCACHE)				&& @_mkdir(PCACHE, 0777)	&& _file_put_contents(
			PCACHE.DS.'.htaccess',
			"Allow From All\r\nAddEncoding gzip .js\r\nAddEncoding gzip .css"
		);
		!_is_dir(LOGS)					&& @_mkdir(LOGS, 0770);
		!_is_dir(TEMP)					&& @_mkdir(TEMP, 0777)		&& _file_put_contents(
			TEMP.DS.'.htaccess',
			'Allow From All'
		);
		if ($this->encrypt_support = check_mcrypt()) {
			global	$KEY, $IV;
			$this->key	= $KEY;
			$this->iv	= $IV;
			unset($GLOBALS['KEY'], $GLOBALS['IV']);
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
		$database	= $Config->components['modules']['System']['db']['keys'];
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
			$this->connected = false;
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
				_include(MODULES.DS.$module.DS.'trigger.php', true, false);
			}
			unset($modules, $module);
			$plugins = get_list(PLUGINS, false, 'd');
			foreach ($plugins as $plugin) {
				_include(PLUGINS.DS.$plugin.DS.'trigger.php', true, false);
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
		 * @var Closure $trigger
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
	 */
	function __clone () {}
	/**
	 * Disabling encryption
	 * @return mixed
	 */
	function __finish () {
		if (is_resource($this->td)) {
			mcrypt_module_close($this->td);
			unset($this->key, $this->iv, $this->td);
		}
	}
}