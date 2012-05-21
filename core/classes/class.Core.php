<?php
//Класс ядра
class Core {
	protected	$iv					= [],
				$td					= [],
				$key				= [],
				$encrypt_support	= false,
				$KEY,
				$IV;
	//Инициализация начальных параметров и функций шифрования
	function __construct() {
		if (!_require(CONFIG.DS.CDOMAIN.DS.'main.php', true, false)) {
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
			__finish();
		}
		define('STORAGE',	STORAGES.DS.DOMAIN.DS.'public');	//Локальное публичное хранилище домена
		define('CACHE',		STORAGES.DS.DOMAIN.DS.'cache');		//Папка с кешем домена
		define('LOGS',		STORAGES.DS.DOMAIN.DS.'logs');		//Папка для логов домена
		define('TEMP',		STORAGES.DS.DOMAIN.DS.'temp');		//Папка для временных файлов домена
		global	$DB_HOST,
				$DB_TYPE,
				$DB_NAME,
				$DB_USER,
				$DB_PASSWORD,
				$DB_PREFIX,
				$DB_CODEPAGE,
				$KEY;
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
			$this->KEY	= $KEY;
			$this->IV	= $DB_HOST.$DB_TYPE.$DB_NAME.$DB_USER.$DB_PASSWORD.$DB_PREFIX.$DB_CODEPAGE;
		}
		unset($GLOBALS['KEY']);
	}
	/**
	 * Encryption initialization
	 * @param string $name
	 * @param string $key
	 * @param string $iv
	 * @param bool|resource $td
	 * @return mixed
	 */
	function crypt_open ($name, $key, $iv, $td = false) {
		if (!$this->encrypt_support || empty($name) || empty($key) || empty($iv)) {
			return;
		}
		$this->key[$name] = $key;
		$this->iv[$name] = $iv;
		if ($td === false) {
			$this->td[$name] = $td['core'];
		} else {
			$this->td[$name] = $td;
		}
	}
	/**
	 * Encryption of data
	 * @param string $data
	 * @param string $name
	 * @return bool|string
	 */
	function encrypt ($data, $name = 'core') {
		if (!$this->encrypt_support) {
			return $data;
		}
		if ($name == 'core' && !isset($this->td[$name])) {
			$td = mcrypt_module_open(MCRYPT_BLOWFISH,'','cbc','');
			$this->crypt_open(
				'core',
				mb_substr($this->KEY, 0, mcrypt_enc_get_key_size($td)),
				mb_substr(md5($this->IV), 0, mcrypt_enc_get_iv_size($td)),
				$td
			);
			unset($td);
		}
		mcrypt_generic_init($this->td[$name], $this->key[$name], $this->iv[$name]);
		$encrypted = mcrypt_generic($this->td[$name], @serialize(array('key' => $this->key[$name], 'data' => $data)));
		mcrypt_generic_deinit($this->td[$name]);
		if ($encrypted) {
			return $encrypted;
		} else {
			return false;
		}
	}
	/**
	 * Decryption of data
	 * @param string $data
	 * @param string $name
	 * @return bool|mixed
	 */
	function decrypt ($data, $name = 'core') {
		if (!$this->encrypt_support) {
			return $data;
		}
		mcrypt_generic_init($this->td[$name], $this->key[$name], $this->iv[$name]);
		errors_off();
		$decrypted = @unserialize(mdecrypt_generic($this->td[$name], $data));
		errors_on();
		mcrypt_generic_deinit($this->td[$name]);
		if (is_array($decrypted) && $decrypted['key'] == $this->key[$name]) {
			return $decrypted['data'];
		} else {
			return false;
		}
	}
	/**
	 * Encryption deinitialization
	 * @param string $name
	 */
	function crypt_close ($name) {
		if ($this->encrypt_support && isset($this->td[$name]) && is_resource($this->td[$name])) {
			mcrypt_module_close($this->td[$name]);
			unset($this->key[$name], $this->iv[$name], $this->td[$name]);
		}
	}

	/**
	 * Sending system api request to all mirrors
	 *
	 * @param        $path
	 * @param string $data
	 *
	 * @return array
	 */
	function api_request ($path, $data = '') {
		global $Config;
		$result	= [];
		if (is_object($Config) && $Config->server['mirrors']['count'] > 1) {
			foreach ($Config->server['mirrors']['http'] as $url) {
				if (!($url == $Config->server['host'] && $Config->server['protocol'] == 'http')) {
					$result['http://'.$url] = $this->send('http://'.$url.'/api/'.$path, $data);
				}
			}
			foreach ($Config->server['mirrors']['https'] as $url) {
				if (!($url != $Config->server['host'] && $Config->server['protocol'] == 'https')) {
					$result['https://'.$url] = $this->send('https://'.$url.'/api/'.$path, $data);
				}
			}
		}
		return $result;
	}
	/**
	 * Sending of api request to the specified host
	 *
	 * @param string $url With prefix <b>https://</b> (<b>http://</b> can be missed), and (if necessary) with port address
	 * @param mixed $data Any type of data, will be accessable through <b>$_POST['data']</b>
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
			global $Error;
			$Error->process('#'.$errno.' '.$errstr);
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
	 * Cloning restriction
	 */
	function __clone () {}
	/**
	 * Disabling encryption
	 * @return mixed
	 */
	function __finish () {
		if (!$this->encrypt_support) {
			return;
		}
		foreach ($this->td as $td) {
			 is_resource($td) && mcrypt_module_close($td);
		}
		unset($this->key, $this->iv, $this->td);
	}
}