<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;
class Key {
	/**
	 * Generates guaranteed unique key
	 *
	 * @param int			$database	Keys database
	 *
	 * @return string
	 */
	function generate ($database) {
		global $db;
		if (!is_object($database)) {
			$database = $db->$database();
		}
		while (true) {
			$key	= hash('sha224', microtime(true));
			$time	= TIME;
			if (!$database->qf(
				"SELECT `id` FROM `[prefix]keys` WHERE `key` = '$key' AND `expire` >= $time LIMIT 1"
			)) {
				return $key;
			}
		}
		return false;
	}
	/**
	 * Adding key into specified database
	 *
	 * @param int			$database	Keys database
	 * @param bool|string	$key		If <b>false</b> - key will be generated automatically, otherwise must contain 56 character [0-9a-z] key
	 * @param null|mixed	$data		Data to be stored with key
	 * @param int			$expire		Timestamp of key exiration, if not specified - default system value will be used
	 *
	 * @return bool
	 */
	function add ($database, $key = false, $data = null, $expire = 0) {
		global $db, $Config;
		if (!is_object($database)) {
			$database = $db->$database();
		}
		if (!preg_match('/^[a-z0-9]{56}$/', $key)) {
			if ($key === false) {
				$key = $this->generate($database);
			} else {
				return false;
			}
		}
		$expire = (int)$expire;
		if ($expire == 0 || $expire < TIME) {
			$expire = TIME + $Config->core['key_expire'];
		}
		$this->del($database, $key);
		$database->q(
			"INSERT INTO `[prefix]keys` (`key`, `expire`, `data`) VALUES ('%s', '%s', '%s')", $key, $expire, _json_encode($data)
		);
		$id = $database->id();
		/**
		 * Cleaning old keys
		 */
		if ($id && ($id % $Config->core['inserts_limit']) == 0) {
			$time	= TIME;
			$database->aq("DELETE FROM `[prefix]keys` WHERE `expire` < $time");
		}
		return $id;
	}
	/**
	 * Check key existence and/or getting of data stored with key
	 *
	 * @param int				$database	Keys database
	 * @param int|string		$id_key		56 character [0-9a-z] key or id of record in daravase
	 * @param bool $get_data				If <b>true</d> - stored data will be returned on success, otherwise boolean result of key existence will be returned
	 *
	 * @return bool|mixed
	 */
	function get ($database, $id_key, $get_data = false) {
		if (!(is_int($id_key) || preg_match('/^[a-z0-9]{56}$/', $id_key))) {
			return false;
		}
		global $db;
		if (!is_object($database)) {
			$database = $db->$database();
		}
		$time	= TIME;
		$result	= $database->qf([
			"SELECT `id`, `data` FROM `[prefix]keys` WHERE (`id` = '$id_key' OR `key` = '$id_key') AND `expire` >= $time ORDER BY `id` DESC LIMIT 1"
		]);
		$this->del($database, $id_key);
		if (!$result || !is_array($result) || empty($result)) {
			return false;
		} elseif ($get_data) {
			return _json_decode($result['data']);
		} else {
			return true;
		}
	}
	/**
	 * Key deletion from database
	 *
	 * @param int			$database	Keys database
	 * @param int|string	$id_key		56 character [0-9a-z] key or id of record in daravase
	 *
	 * @return bool
	 */
	function del ($database, $id_key) {
		if (!(is_int($id_key) || preg_match('/^[a-z0-9]{56}$/', $id_key))) {
			return false;
		}
		global $db;
		if (!is_object($database)) {
			$database = $db->$database();
		}
		$id_key = $database->s($id_key);
		return $database->q(
			"UPDATE `[prefix]keys` SET `expire` = 0, `data` = null, key=null WHERE (`id` = '$id_key' OR `key` = '$id_key')"
		);
	}
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	function __clone () {}
}