<?php
/**
 * @package    WebSockets
 * @subpackage CleverStyle Framework We
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\WebSockets;
use
	cs\Config,
	cs\DB\Accessor,
	cs\Singleton;

/**
 * @method static $this instance($check = false)
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
	public function get_all () {
		return $this->db_prime()->qfas(
			'SELECT `address`
			FROM `[prefix]websockets_pool`
			ORDER BY `date` ASC'
		) ?: [];
	}
	/**
	 * Get master server address
	 *
	 * @return false|string
	 */
	public function get_master () {
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
	public function add ($server_address) {
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
	public function del ($server_address) {
		return (bool)$this->db_prime()->q(
			"DELETE FROM `[prefix]websockets_pool`
			WHERE `address` = '%s'",
			$server_address
		);
	}
}
