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
	cs\Singleton;

class Pool {
	use
		Singleton;

	function get () {
		// TODO
	}
	function get_master () {
		return $this->get()[0];
	}
	/**
	 * @param string $server_address Address of WebSockets server in format wss://server/WebSockets or ws://server/WebSockets
	 *
	 * @return bool|mixed
	 */
	function add ($server_address) {
		// TODO
	}
	function del () {
		// TODO
	}
}
