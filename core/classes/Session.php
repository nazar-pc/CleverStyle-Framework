<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	cs\Session\Data,
	cs\Session\Management;

/**
 * Class responsible for current user session
 *
 * Provides next events:
 *
 *  System/Session/init/before
 *
 *  System/Session/init/after
 *
 *  System/Session/load
 *  ['session_data' => $session_data]
 *
 *  System/Session/add
 *  ['session_data' => $session_data]
 *
 *  System/Session/del/before
 *  ['id' => $session_id]
 *
 *  System/Session/del/after
 *  ['id' => $session_id]
 *
 *  System/Session/del_all
 *  ['id' => $user_id]
 *
 * @method static $this instance($check = false)
 */
class Session {
	use
		CRUD,
		Singleton,
		Data,
		Management;
	const INIT_STATE_METHOD          = 'init';
	const INITIAL_SESSION_EXPIRATION = 300;
	/**
	 * @var Cache\Prefix
	 */
	protected $cache;
	/**
	 * @var Cache\Prefix
	 */
	protected $users_cache;
	protected $data_model = [
		'id'          => 'text',
		'user'        => 'int:0',
		'created'     => 'int:0',
		'expire'      => 'int:0',
		'user_agent'  => 'text',
		'remote_addr' => 'text',
		'ip'          => 'text',
		'data'        => 'json'
	];
	protected $table      = '[prefix]sessions';
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('System')->db('users');
	}
	protected function init () {
		if (!$this->cache) {
			$this->cache       = Cache::prefix('sessions');
			$this->users_cache = Cache::prefix('users');
		}
		$this->session_id = null;
		$this->user_id    = User::GUEST_ID;
		Event::instance()->fire('System/Session/init/before');
		$this->init_session();
		Event::instance()->fire('System/Session/init/after');
	}
}
