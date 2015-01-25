<?php
/**
 * @package    WebSockets
 * @subpackage CleverStyle CMS We
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\WebSockets;
use
	cs\Config,
	cs\DB\Accessor,
	cs\Singleton;

class Pool {
	use
		Accessor,
		Singleton;

	/**
	 * @inheritdoc
	 */
	protected function cdb () {
		return Config::instance()->module('WebSockets')->db('pool');
	}

	function get_all () {
		return $this->db_prime()->qfas(
			"SELECT `address`
			FROM `[prefix]websockets_pool`"
		) ?: [];
	}
	function get_master () {
		$servers = $this->get_all();
		return $servers ? $servers[0] : false;
	}
	/**
	 * @param string $server_address Address of WebSockets server in format wss://server/WebSockets or ws://server/WebSockets
	 *
	 * @return bool
	 */
	function add ($server_address) {
		return $this->db_prime()->q(
			"INSERT IGNORE INTO `[prefix]websockets_pool`
			(
				`address`
			) VALUES (
				'%s'
			)",
			$server_address
		);
	}
	function del ($server_address) {
		return $this->db_prime()->q(
			"DELETE FROM `[prefix]websockets_pool`
			WHERE `address` = '%s'
			LIMIT 1",
			$server_address
		);
	}
}
