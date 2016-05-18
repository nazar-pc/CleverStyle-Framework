<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Session;
use
	cs\Config,
	cs\Event,
	cs\Language,
	cs\Page,
	cs\Request,
	cs\Response,
	cs\User;

/**
 * @method \cs\DB\_Abstract db()
 * @method \cs\DB\_Abstract db_prime()
 */
trait Management {
	/**
	 * Id of current session
	 *
	 * @var false|string
	 */
	protected $session_id;
	/**
	 * User id of current session
	 *
	 * @var int
	 */
	protected $user_id;
	/**
	 * @var bool
	 */
	protected $is_admin;
	/**
	 * @var bool
	 */
	protected $is_user;
	/**
	 * @var bool
	 */
	protected $is_guest;
	/**
	 * Use cookie as source of session id, load session
	 */
	protected function init_session () {
		$Request = Request::instance();
		/**
		 * If session exists
		 */
		if ($Request->cookie('session')) {
			$this->user_id = $this->load();
		}
		$this->update_user_is();
	}
	/**
	 * Updates information about who is user accessed by methods ::guest() ::user() admin()
	 */
	protected function update_user_is () {
		$this->is_guest = $this->user_id == User::GUEST_ID;
		$this->is_user  = false;
		$this->is_admin = false;
		if ($this->is_guest) {
			return;
		}
		/**
		 * Checking of user type
		 */
		$groups = User::instance()->get_groups($this->user_id) ?: [];
		if (in_array(User::ADMIN_GROUP_ID, $groups)) {
			$this->is_admin = true;
			$this->is_user  = true;
		} elseif (in_array(User::USER_GROUP_ID, $groups)) {
			$this->is_user = true;
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
	 * Returns id of current session
	 *
	 * @return false|string
	 */
	function get_id () {
		return $this->session_id ?: false;
	}
	/**
	 * Returns user id of current session
	 *
	 * @return int
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
		unset($session_data['data']);
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
				$this->session_id = Request::instance()->cookie('session');
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
		return
			isset($session_data['expire'], $session_data['user']) &&
			$session_data['expire'] > time() &&
			$this->is_user_active($session_data['user']);
	}
	/**
	 * Whether session data belongs to current visitor (user agent, remote addr and ip check)
	 *
	 * @param string $session_id
	 * @param string $user_agent
	 * @param string $remote_addr
	 * @param string $ip
	 *
	 * @return bool
	 */
	function is_session_owner ($session_id, $user_agent, $remote_addr, $ip) {
		$session_data = $this->get($session_id);
		return $session_data ? $this->is_session_owner_internal($session_data, $user_agent, $remote_addr, $ip) : false;
	}
	/**
	 * Whether session data belongs to current visitor (user agent, remote addr and ip check)
	 *
	 * @param array       $session_data
	 * @param string|null $user_agent
	 * @param string|null $remote_addr
	 * @param string|null $ip
	 *
	 * @return bool
	 */
	protected function is_session_owner_internal ($session_data, $user_agent = null, $remote_addr = null, $ip = null) {
		/**
		 * md5() as protection against timing attacks
		 */
		if ($user_agent === null && $remote_addr === null && $ip === null) {
			$Request     = Request::instance();
			$user_agent  = $Request->header('user-agent');
			$remote_addr = $Request->remote_addr;
			$ip          = $Request->ip;
		}
		return
			md5($session_data['user_agent']) == md5($user_agent) &&
			(
				!Config::instance()->core['remember_user_ip'] ||
				(
					md5($session_data['remote_addr']) == md5(ip2hex($remote_addr)) &&
					md5($session_data['ip']) == md5(ip2hex($ip))
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
		$session_data = $this->get_internal($session_id);
		if (!$session_data || !$this->is_session_owner_internal($session_data)) {
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
		$L    = Language::prefix('system_profile_sign_in_');
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
			$Page->warning($L->your_account_blocked_until(date($L->_datetime, $data['block_until'])));
			return false;
		}
		return true;
	}
	/**
	 * Create the session for the user with specified id
	 *
	 * @param int  $user
	 * @param bool $delete_current_session
	 *
	 * @return false|string Session id on success, `false` otherwise
	 */
	function add ($user, $delete_current_session = true) {
		$user = (int)$user;
		if (!$user) {
			return false;
		}
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
		Response::instance()->cookie('session', $session_data['id'], $session_data['expire'], true);
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
		$Config      = Config::instance();
		$Request     = Request::instance();
		$remote_addr = ip2hex($Request->remote_addr);
		$ip          = ip2hex($Request->ip);
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
			'user_agent'  => $Request->header('user-agent'),
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
		Response::instance()->cookie('session', '');
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
		$this->db_prime()->q(
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
}
