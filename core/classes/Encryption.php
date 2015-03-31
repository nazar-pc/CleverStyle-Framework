<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

/**
 * Encryption class.
 * Provides encryption and decryption functionality
 *
 * @method static Encryption instance($check = false)
 */
class Encryption {
	use Singleton;
	/**
	 * @var string
	 */
	protected $iv;
	/**
	 * @var resource
	 */
	protected $td;
	/**
	 * @var string
	 */
	protected $key;
	/**
	 * Whether Mcrypt is available, if not - encryption will not work and return original unencrypted data
	 * @var bool
	 */
	protected $encrypt_support = false;
	/**
	 * Detection of encryption support
	 */
	protected function construct () {
		if ($this->encrypt_support = extension_loaded('mcrypt')) {
			$Core      = Core::instance();
			$this->key = $Core->key;
		}
	}
	protected function init () {
		if (!is_resource($this->td)) {
			$this->td  = mcrypt_module_open(MCRYPT_TWOFISH, '', MCRYPT_MODE_CBC, '');
			$this->key = mb_substr($this->key, 0, mcrypt_enc_get_key_size($this->td));
		}
	}
	/**
	 * Encryption of data
	 *
	 * @param string      $data Data to be encrypted
	 * @param bool|string $key  Key, if not specified - system key will be used
	 *
	 * @return bool|string
	 */
	function encrypt ($data, $key = false) {
		if (!$this->encrypt_support) {
			return $data;
		}
		$this->init();
		if ($key === false) {
			$key = $this->key;
		} else {
			$key = mb_substr(hash('sha512', $this->key.$key), 0, mcrypt_enc_get_key_size($this->td));
		}
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->td));
		mcrypt_generic_init($this->td, $key, $iv);
		$encrypted = mcrypt_generic($this->td, @serialize([
			'key'  => $key,
			'data' => $data
		]));
		mcrypt_generic_deinit($this->td);
		if ($encrypted) {
			return $iv.$encrypted;
		} else {
			return false;
		}
	}
	/**
	 * Decryption of data
	 *
	 * @param string      $data Data to be decrypted
	 * @param bool|string $key  Key, if not specified - system key will be used
	 *
	 * @return bool|mixed
	 */
	function decrypt ($data, $key = false) {
		if (!$this->encrypt_support) {
			return $data;
		}
		$this->init();
		if ($key === false) {
			$key = $this->key;
		} else {
			$key = mb_substr(hash('sha512', $this->key.$key), 0, mcrypt_enc_get_key_size($this->td));
		}
		$iv_size = mcrypt_enc_get_iv_size($this->td);
		$iv      = substr($data, 0, $iv_size);
		$data    = substr($data, $iv_size);
		mcrypt_generic_init($this->td, $key, $iv);
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
	 * Disabling of encryption.
	 * Correct termination.
	 */
	function __destruct () {
		if (is_resource($this->td)) {
			mcrypt_module_close($this->td);
			unset($this->key, $this->td);
		}
	}
}
