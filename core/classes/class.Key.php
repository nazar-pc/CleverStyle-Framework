<?php
class Key {
	function generate ($database) {
		global $db;
		while (true) {
			$key = hash('sha224', microtime(true));
			if (!$db->$database->qf(
				'SELECT `id` FROM `[prefix]keys` WHERE `key` = \''.$key.'\' AND `expire` >= '.TIME.' LIMIT 1'
			)) {
				return $key;
			}
		}
	}
	function add ($database, $key = false, $data = null, $expire = 0) {
		if (!preg_match('/^[a-z0-9]{56}$/', $key)) {
			if ($key === false) {
				$key = $this->generate($database);
			} else {
				return false;
			}
		}
		global $db, $Config;
		$expire = (int)$expire;
		if ($expire == 0 && $expire < TIME) {
			$expire = TIME + $Config->core['key_expire'];
		}
		$this->del($database, $key);
		$id = $db->$database()->insert_id(
			$db->$database()->q(
				'INSERT INTO `[prefix]keys`
					(
						`key`,
						`expire`,
						`data`
					)
				VALUES
					(
						'.$db->$database()->sip($key).',
						'.$expire.',
						'.$db->$database()->sip(_json_encode($data)).'
					)'
			)
		);
		if ($id && ($id % $Config->core['inserts_limit']) == 0) { //Cleaning old keys
			$db->$database()->q('DELETE FROM `[prefix]keys` WHERE `expire` < '.TIME);
		}
		return $id;
	}
	function get ($database, $id_key, $get_data = false) {
		if (!(is_int($id_key) || preg_match('/^[a-z0-9]{56}$/', $id_key))) {
			return false;
		}
		global $db;
		$result = $db->$database->qf(
			'SELECT `id`'.($get_data ? ', `data`' : '').' FROM `[prefix]keys`
			WHERE
				(
					`id` = '.$db->$database()->sip($id_key).' OR `key` = '.$db->$database()->sip($id_key).'
				) AND `expire` >= '.TIME.' ORDER BY `id` DESC LIMIT 1'
		);
		$this->del($database, $id_key);
		if (!$result || !is_array($result) || empty($result)) {
			return false;
		} elseif ($get_data) {
			return _json_decode($result['data']);
		} else {
			return true;
		}
	}
	function del ($database, $id_key) {
		if (!(is_int($id_key) || preg_match('/^[a-z0-9]{56}$/', $id_key))) {
			return false;
		}
		global $db;
		$id_key = $db->$database()->sip($id_key);
		return $db->$database()->q('UPDATE `[prefix]keys`
			SET `expire` = 0, `data` = null, key=null
			WHERE (`id` = '.$id_key.' OR `key` = '.$id_key.')'
		);
	}
	/**
	 * Cloning restriction
	 */
	function __clone () {}
}