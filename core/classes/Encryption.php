<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

/**
 * Encryption class
 *
 * Provides encryption and decryption functionality
 *
 * @deprecated
 * @todo remove in future versions
 *
 * @method static Encryption instance($check = false)
 */
class Encryption {
	use Singleton;
	/**
	 * Whether encryption is supported
	 *
	 * @deprecated
	 * @todo remove in future versions
	 *
	 * @return bool
	 */
	function supported () {
		return extension_loaded('openssl');
	}
	/**
	 * Encryption of data
	 *
	 * @deprecated
	 * @todo remove in future versions
	 *
	 * @param mixed       $data        Data to be encrypted
	 * @param bool|string $key         Key, if not specified - system key will be used
	 * @param string      $method
	 * @param bool        $json_encode Whether to encode incoming data as JSON
	 *
	 * @return false|string
	 */
	function encrypt ($data, $key = false, $method = 'aes-256-cbc', $json_encode = true) {
		if (!$this->supported()) {
			return $data;
		}
		$key = $key ?: Core::instance()->key;
		$iv  = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
		if ($json_encode) {
			$data = _json_encode($data);
		}
		$encrypted = openssl_encrypt($data, $method, $key, true, $iv);
		if ($encrypted) {
			return $iv.$encrypted;
		} else {
			return false;
		}
	}
	/**
	 * Decryption of data
	 *
	 * @deprecated
	 * @todo remove in future versions
	 *
	 * @param string      $data         Data to be decrypted
	 * @param bool|string $key          Key, if not specified - system key will be used
	 * @param string      $method
	 * @param bool        $json_encoded Whether data were encoded JSON
	 *
	 * @return false|mixed
	 */
	function decrypt ($data, $key = false, $method = 'aes-256-cbc', $json_encoded = true) {
		if (!$this->supported()) {
			return $data;
		}
		$key       = $key ?: Core::instance()->key;
		$iv_size   = openssl_cipher_iv_length($method);
		$iv        = substr($data, 0, $iv_size);
		$data      = substr($data, $iv_size);
		$decrypted = openssl_decrypt($data, $method, $key, true, $iv);
		if (strlen($decrypted)) {
			return $json_encoded ? _json_decode($decrypted) : $decrypted;
		} else {
			return false;
		}
	}
}
