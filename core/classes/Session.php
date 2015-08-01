<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
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
 */
namespace cs;
use
	cs\Cache\Prefix;
/**
 * Class responsible for current user session
 *
 * @method static Session instance($check = false)
 */
class Session {
	use
		CRUD,
		Singleton;
	const INITIAL_SESSION_EXPIRATION = 300;
	/**
	 * Id of current session
	 *
	 * @var string
	 */
	protected $session_id;
	/**
	 * User id of current session
	 *
	 * @var bool|int
	 */
	protected $user_id  = false;
	protected $is_admin = false;
	protected $is_user  = false;
	protected $is_bot   = false;
	protected $is_guest = false;
	/**
	 * @var Prefix
	 */
	protected $cache;
	/**
	 * @var Prefix
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
	protected function construct () {
		$this->cache       = new Prefix('sessions');
		$this->users_cache = new Prefix('users');
		$this->initialize();
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('System')->db('users');
	}
	/**
	 * Use cookie as source of session id, load session
	 *
	 * Bots detection is also done here
	 */
	protected function initialize () {
		Event::instance()->fire('System/Session/init/before');
		/**
		 * If session exists
		 */
		if (_getcookie('session')) {
			$this->user_id = $this->load();
		} elseif (!api_path()) {
			/**
			 * Try to detect bot, not necessary for API request
			 */
			$this->bots_detection();
		}
		/**
		 * If session not found and visitor is not bot - create new session
		 */
		if (!$this->user_id) {
			$this->user_id = User::GUEST_ID;
			/**
			 * Do not create session for API requests
			 */
			if (!api_path()) {
				$this->add();
			}
		}
		$this->update_user_is();
		Event::instance()->fire('System/Session/init/after');
	}
	/**
	 * Try to determine whether visitor is a known bot, bots have no sessions
	 */
	protected function bots_detection () {
		$Cache = $this->users_cache;
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		/**
		 * For bots: login is user agent, email is IP
		 */
		$login    = $_SERVER->user_agent;
		$email    = $_SERVER->ip;
		$bot_hash = hash('sha224', $login.$email);
		/**
		 * If bot is cached
		 */
		$this->user_id = $Cache->$bot_hash;
		/**
		 * If bot found in cache - exit from here
		 */
		if ($this->user_id) {
			return;
		}
		/**
		 * Try to find bot among known bots
		 */
		foreach ($this->all_bots() as $bot) {
			if ($this->is_this_bot($bot, $login, $email)) {
				/**
				 * If bot found - save it in cache
				 */
				$this->user_id    = $bot['id'];
				$Cache->$bot_hash = $bot['id'];
				return;
			}
		}
	}
	/**
	 * Get list of all bots
	 *
	 * @return array
	 */
	protected function all_bots () {
		return $this->users_cache->get(
			'bots',
			function () {
				return $this->db()->qfa(
					[
						"SELECT
							`u`.`id`,
							`u`.`login`,
							`u`.`email`
						FROM `[prefix]users` AS `u`
							INNER JOIN `[prefix]users_groups` AS `g`
						ON `u`.`id` = `g`.`id`
						WHERE
							`g`.`group`		= '%s' AND
							`u`.`status`	= '%s'",
						User::BOT_GROUP_ID,
						User::STATUS_ACTIVE
					]
				) ?: [];
			}
		) ?: [];
	}
	/**
	 * Check whether user agent and IP (login and email for bots) corresponds to passed bot data
	 *
	 * @param array  $bot
	 * @param string $login
	 * @param string $email
	 *
	 * @return bool
	 */
	protected function is_this_bot ($bot, $login, $email) {
		return
			(
				$bot['login'] &&
				(
					strpos($login, $bot['login']) !== false ||
					_preg_match($bot['login'], $login)
				)
			) ||
			(
				$bot['email'] &&
				(
					$email === $bot['email'] ||
					_preg_match($bot['email'], $email)
				)
			);
	}
	/**
	 * Updates information about who is user accessed by methods ::guest() ::bot() ::user() admin()
	 */
	protected function update_user_is () {
		$this->is_guest = false;
		$this->is_bot   = false;
		$this->is_user  = false;
		$this->is_admin = false;
		if ($this->user_id == User::GUEST_ID) {
			$this->is_guest = true;
			return;
		}
		/**
		 * Checking of user type
		 */
		$groups = User::instance()->get_groups($this->user_id) ?: [];
		if (in_array(User::ADMIN_GROUP_ID, $groups)) {
			$this->is_admin = Config::instance()->can_be_admin();
			$this->is_user  = true;
		} elseif (in_array(User::USER_GROUP_ID, $groups)) {
			$this->is_user = true;
		} elseif (in_array(User::BOT_GROUP_ID, $groups)) {
			$this->is_guest = true;
			$this->is_bot   = true;
		}
	}
	/**
	 * Is admin
	 *
	 * @return bool
	 */
	function admin () {
		return $this->is_admin;
	}
	/**
	 * Is user
	 *
	 * @return bool
	 */
	function user () {
		return $this->is_user;
	}
	/**
	 * Is guest
	 *
	 * @return bool
	 */
	function guest () {
		return $this->is_guest;
	}
	/**
	 * Is bot
	 *
	 * @return bool
	 */
	function bot () {
		return $this->is_bot;
	}
	/**
	 * Returns id of current session
	 *
	 * @return false|string
	 */
	function get_id () {
		if ($this->user_id == User::GUEST_ID && $this->bot()) {
			return false;
		}
		return $this->session_id ?: false;
	}
	/**
	 * Returns user id of current session
	 *
	 * @return false|int
	 */
	function get_user () {
		return $this->user_id;
	}
	/**
	 * Returns session details by session id
	 *
	 * @param false|null|string $session_id If `null` - loaded from `$this->session_id`, and if that also empty - from cookies
	 *
	 * @return false|array
	 */
	function get ($session_id) {
		$session_data = $this->get_internal($session_id);
		if ($session_data) {
			unset($session_data['data']);
		}
		return $session_data;
	}
	/**
	 * @param false|null|string $session_id
	 *
	 * @return false|array
	 */
	protected function get_internal ($session_id) {
		if (!$session_id) {
			if (!$this->session_id) {
				$this->session_id = _getcookie('session');
			}
			$session_id = $this->session_id;
		}
		if (!is_md5($session_id)) {
			return false;
		}
		$data = $this->cache->get(
			$session_id,
			function () use ($session_id) {
				$data = $this->read($session_id);
				if (!$data || $data['expire'] <= time()) {
					return false;
				}
				$data['data'] = $data['data'] ?: [];
				return $data;
			}
		);
		return $this->is_good_session($data) ? $data : false;
	}
	/**
	 * Check whether session was not expired, user agent and IP corresponds to what is expected and user is actually active
	 *
	 * @param mixed $session_data
	 *
	 * @return bool
	 */
	protected function is_good_session ($session_data) {
		/**
		 * md5() as protection against timing attacks
		 *
		 * @var \cs\_SERVER $_SERVER
		 */
		return
			is_array($session_data) &&
			$session_data['expire'] > time() &&
			md5($session_data['user_agent']) == md5($_SERVER->user_agent) &&
			$this->is_user_active($session_data['user']) &&
			(
				!Config::instance()->core['remember_user_ip'] ||
				(
					md5($session_data['remote_addr']) == md5(ip2hex($_SERVER->remote_addr)) &&
					md5($session_data['ip']) == md5(ip2hex($_SERVER->ip))
				)
			);
	}
	/**
	 * Load session by id and return id of session owner (user), update session expiration
	 *
	 * @param false|null|string $session_id If not specified - loaded from `$this->session_id`, and if that also empty - from cookies
	 *
	 * @return int User id
	 */
	function load ($session_id = null) {
		if ($this->user_id == User::GUEST_ID && $this->bot()) {
			return User::GUEST_ID;
		}
		$session_data = $this->get_internal($session_id);
		if (!$session_data) {
			$this->add(User::GUEST_ID);
			return User::GUEST_ID;
		}
		/**
		 * Updating last online time and ip
		 */
		$Config = Config::instance();
		$time   = time();
		if ($session_data['expire'] - $time < $Config->core['session_expire'] * $Config->core['update_ratio'] / 100) {
			$session_data['expire'] = $time + $Config->core['session_expire'];
			$this->update($session_data);
			$this->cache->set($session_data['id'], $session_data);
		}
		unset($session_data['data']);
		Event::instance()->fire(
			'System/Session/load',
			[
				'session_data' => $session_data
			]
		);
		return $this->load_initialization($session_data['id'], $session_data['user']);
	}
	/**
	 * Initialize session (set user id, session id and update who user is)
	 *
	 * @param string $session_id
	 * @param int    $user_id
	 *
	 * @return int User id
	 */
	protected function load_initialization ($session_id, $user_id) {
		$this->session_id = $session_id;
		$this->user_id    = $user_id;
		$this->update_user_is();
		return $user_id;
	}
	/**
	 * Whether profile is activated, not disabled and not blocked
	 *
	 * @param int $user
	 *
	 * @return bool
	 */
	protected function is_user_active ($user) {
		/**
		 * Optimization, more data requested than actually used here, because data will be requested later, and it would be nice to have that data cached
		 */
		$data = User::instance()->get(
			[
				'login',
				'username',
				'language',
				'timezone',
				'status',
				'block_until',
				'avatar'
			],
			$user
		);
		if (!$data) {
			return false;
		}
		$L    = Language::instance();
		$Page = Page::instance();
		switch ($data['status']) {
			case User::STATUS_INACTIVE:
				/**
				 * If user is disabled
				 */
				$Page->warning($L->your_account_disabled);
				return false;
			case User::STATUS_NOT_ACTIVATED:
				/**
				 * If user is not active
				 */
				$Page->warning($L->your_account_is_not_active);
				return false;
		}
		if ($data['block_until'] > time()) {
			/**
			 * If user if blocked
			 */
			$Page->warning($L->your_account_blocked_until.' '.date($L->_datetime, $data['block_until']));
			return false;
		}
		return true;
	}
	/**
	 * Create the session for the user with specified id
	 *
	 * @param false|int $user
	 * @param bool      $delete_current_session
	 *
	 * @return false|string Session id on success, `false` otherwise
	 */
	function add ($user = false, $delete_current_session = true) {
		$user = (int)$user ?: User::GUEST_ID;
		if ($delete_current_session && is_md5($this->session_id)) {
			$this->del_internal($this->session_id, false);
		}
		if (!$this->is_user_active($user)) {
			/**
			 * If data was not loaded or account is not active - create guest session
			 */
			return $this->add(User::GUEST_ID);
		}
		$session_data = $this->create_unique_session($user);
		_setcookie('session', $session_data['id'], $session_data['expire']);
		$this->load_initialization($session_data['id'], $session_data['user']);
		/**
		 * Delete old sessions using probability and system configuration of inserts limits and update ratio
		 */
		$Config = Config::instance();
		if (mt_rand(0, $Config->core['inserts_limit']) < $Config->core['inserts_limit'] / 100 * (100 - $Config->core['update_ratio']) / 5) {
			$this->delete_old_sessions();
		}
		Event::instance()->fire(
			'System/Session/add',
			[
				'session_data' => $session_data
			]
		);
		return $session_data['id'];
	}
	/**
	 * @param int $user
	 *
	 * @return array Session data
	 */
	protected function create_unique_session ($user) {
		$Config = Config::instance();
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		$remote_addr = ip2hex($_SERVER->remote_addr);
		$ip          = ip2hex($_SERVER->ip);
		/**
		 * Many guests open only one page (or do not store any cookies), so create guest session only for 5 minutes max initially
		 */
		$expire_in = $user == User::GUEST_ID ? min($Config->core['session_expire'], self::INITIAL_SESSION_EXPIRATION) : $Config->core['session_expire'];
		$expire    = time() + $expire_in;
		/**
		 * Create unique session
		 */
		$session_data = [
			'id'          => null,
			'user'        => $user,
			'created'     => time(),
			'expire'      => $expire,
			'user_agent'  => $_SERVER->user_agent,
			'remote_addr' => $remote_addr,
			'ip'          => $ip,
			'data'        => []
		];
		do {
			$session_data['id'] = md5(random_bytes(1000));
		} while (!$this->create($session_data));
		return $session_data;
	}
	/**
	 * Destroying of the session
	 *
	 * @param null|string $session_id
	 *
	 * @return bool
	 */
	function del ($session_id = null) {
		return (bool)$this->del_internal($session_id);
	}
	/**
	 * Deletion of the session
	 *
	 * @param string|null $session_id
	 * @param bool        $create_guest_session
	 *
	 * @return bool
	 */
	protected function del_internal ($session_id = null, $create_guest_session = true) {
		$session_id = $session_id ?: $this->session_id;
		if (!is_md5($session_id)) {
			return false;
		}
		Event::instance()->fire(
			'System/Session/del/before',
			[
				'id' => $session_id
			]
		);
		unset($this->cache->$session_id);
		$this->session_id = false;
		_setcookie('session', '');
		$result = $this->delete($session_id);
		if ($result) {
			if ($create_guest_session) {
				return (bool)$this->add(User::GUEST_ID);
			}
			Event::instance()->fire(
				'System/Session/del/after',
				[
					'id' => $session_id
				]
			);
		}
		return (bool)$result;
	}
	/**
	 * Delete all old sessions from DB
	 */
	protected function delete_old_sessions () {
		$this->db_prime()->aq(
			"DELETE FROM `[prefix]sessions`
			WHERE `expire` < ".time()
		);
	}
	/**
	 * Deletion of all user sessions
	 *
	 * @param false|int $user If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_all ($user = false) {
		$user = $user ?: $this->user_id;
		Event::instance()->fire(
			'System/Session/del_all',
			[
				'id' => $user
			]
		);
		$sessions = $this->db_prime()->qfas(
			"SELECT `id`
			FROM `[prefix]sessions`
			WHERE `user` = '$user'"
		);
		if (is_array($sessions)) {
			if (!$this->delete($sessions)) {
				return false;
			}
			foreach ($sessions as $session) {
				unset($this->cache->$session);
			}
		}
		return true;
	}
	/**
	 * Get data, stored with session
	 *
	 * @param string      $item
	 * @param null|string $session_id
	 *
	 * @return false|mixed
	 *
	 */
	function get_data ($item, $session_id = null) {
		$session_id = $session_id ?: $this->session_id;
		if (!is_md5($session_id)) {
			return false;
		}
		$session_data = $this->get_internal($session_id);
		return $session_data && isset($session_data['data'][$item]) ? $session_data['data'][$item] : false;
	}
	/**
	 * Store data with session
	 *
	 * @param string      $item
	 * @param mixed       $value
	 * @param null|string $session_id
	 *
	 * @return bool
	 *
	 */
	function set_data ($item, $value, $session_id = null) {
		$session_id = $session_id ?: $this->session_id;
		if (!is_md5($session_id)) {
			return false;
		}
		$session_data = $this->get_internal($session_id);
		if (!$session_data) {
			return false;
		}
		$session_data['data'][$item] = $value;
		return $this->update($session_data) && $this->cache->del($session_id);
	}
	/**
	 * Delete data, stored with session
	 *
	 * @param string      $item
	 * @param null|string $session_id
	 *
	 * @return bool
	 *
	 */
	function del_data ($item, $session_id = null) {
		$session_id = $session_id ?: $this->session_id;
		if (!is_md5($session_id)) {
			return false;
		}
		$session_data = $this->get_internal($session_id);
		if (!$session_data) {
			return false;
		}
		if (!isset($session_data['data'][$item])) {
			return true;
		}
		return $this->update($session_data) && $this->cache->del($session_id);
	}
}
