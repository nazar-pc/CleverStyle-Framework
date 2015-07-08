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
/**
 * @method static Pool instance($check = false)
 */
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
	/**
	 * Get addresses of all servers
	 *
	 * @return string[]
	 */
	function get_all () {
		return $this->db_prime()->qfas(
			"SELECT `address`
			FROM `[prefix]websockets_pool`
			ORDER BY `date` ASC"
		) ?: [];
	}
	/**
	 * Get master server address
	 *
	 * @return false|string
	 */
	function get_master () {
		$servers = $this->get_all();
		return $servers ? $servers[0] : false;
	}
	/**
	 * Add server with specified address to server
	 *
	 * @param string $server_address Address of WebSockets server in format wss://server/WebSockets or ws://server/WebSockets
	 *
	 * @return bool
	 */
	function add ($server_address) {
		return (bool)$this->db_prime()->q(
			"INSERT IGNORE INTO `[prefix]websockets_pool`
			(
				`date`,
				`address`
			) VALUES (
				'%s',
				'%s'
			)",
			time(),
			$server_address
		);
	}
	/**
	 * Delete server with specified address from pool
	 *
	 * @param string $server_address
	 *
	 * @return bool
	 *
	 */
	function del ($server_address) {
		return (bool)$this->db_prime()->q(
			"DELETE FROM `[prefix]websockets_pool`
			WHERE `address` = '%s'
			LIMIT 1",
			$server_address
		);
	}
}
