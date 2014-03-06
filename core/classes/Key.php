<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;

class Key {
	use	Singleton;

	/**
	 * Generates guaranteed unique key
	 *
	 * @param int|object	$database	Keys database
	 *
	 * @return string
	 */
	function generate ($database) {
		if (!is_object($database)) {
			$database = DB::instance()->$database();
		}
		while (true) {
			$key	= hash('sha224', microtime(true));
			$time	= TIME;
			if (!$database->qfs(
				"SELECT `id`
				FROM `[prefix]keys`
				WHERE
					`key`		= '$key' AND
					`expire`	>= $time
				LIMIT 1"
			)) {
				return $key;
			}
		}
		return false;
	}
	/**
	 * Adding key into specified database
	 *
	 * @param int|object	$database	Keys database
	 * @param bool|string	$key		If <b>false</b> - key will be generated automatically, otherwise must contain 56 character [0-9a-z] key
	 * @param null|mixed	$data		Data to be stored with key
	 * @param int			$expire		Timestamp of key expiration, if not specified - default system value will be used
	 *
	 * @return bool|string
	 */
	function add ($database, $key, $data = null, $expire = 0) {
		if (!is_object($database)) {
			$database = DB::instance()->$database();
		}
		if (!preg_match('/^[a-z0-9]{56}$/', $key)) {
			if ($key === false) {
				$key = $this->generate($database);
			} else {
				return false;
			}
		}
		$expire = (int)$expire;
		$Config	= Config::instance();
		if ($expire == 0 || $expire < TIME) {
			$expire = TIME + $Config->core['key_expire'];
		}
		$this->del($database, $key);
		$database->q(
			"INSERT INTO `[prefix]keys`
				(
					`key`,
					`expire`,
					`data`
				) VALUES (
					'%s',
					'%s',
					'%s'
				)",
			$key,
			$expire,
			_json_encode($data)
		);
		$id = $database->id();
		/**
		 * Cleaning old keys
		 */
		if ($id && ($id % $Config->core['inserts_limit']) == 0) {
			$time	= TIME;
			$database->aq(
				"DELETE FROM `[prefix]keys`
				WHERE `expire` < $time"
			);
		}
		return $key;
	}
	/**
	 * Check key existence and/or getting of data stored with key. After this key will be deleted automatically.
	 *
	 * @param int				$database	Keys database
	 * @param string			$key		56 character [0-9a-z] key
	 * @param bool $get_data				If <b>true</d> - stored data will be returned on success, otherwise boolean result of key existence will be returned
	 *
	 * @return bool|mixed
	 */
	function get ($database, $key, $get_data = false) {
		if (!preg_match('/^[a-z0-9]{56}$/', $key)) {
			return false;
		}
		if (!is_object($database)) {
			$database = DB::instance()->$database();
		}
		$time	= TIME;
		$result	= $database->qf([
			"SELECT
				`id`,
				`data`
			FROM `[prefix]keys`
			WHERE
				(
					`key`	= '$key'
				) AND
				`expire` >= $time
			ORDER BY `id` DESC
			LIMIT 1"
		]);
		$this->del($database, $key);
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
	 * @param int|object	$database	Keys database
	 * @param string		$key		56 character [0-9a-z] key
	 *
	 * @return bool
	 */
	function del ($database, $key) {
		if (!preg_match('/^[a-z0-9]{56}$/', $key)) {
			return false;
		}
		if (!is_object($database)) {
			$database = DB::instance()->$database();
		}
		$key = $database->s($key);
		return $database->q(
			"DELETE FROM `[prefix]keys`
			WHERE
				(
					`id`	= '$key' OR
					`key`	= '$key'
				)"
		);
	}
}
