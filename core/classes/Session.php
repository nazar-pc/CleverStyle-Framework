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
 *  System/Session/del/before
 *  ['id' => session_id]
 *
 *  System/Session/del/after
 *  ['id' => session_id]
 *
 *  System/Session/del_all
 *  ['id' => user_id]
 */
namespace cs;
use
	cs\Cache\Prefix,
	cs\DB\Accessor;
/**
 * Class responsible for current user session
 *
 * @method static Session instance($check = false)
 */
class Session {
	use
		Accessor,
		Singleton;
	/**
	 * Id of current session
	 *
	 * @var bool|string
	 */
	protected $session_id = false;
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
		 * Loading bots list
		 */
		$bots = $Cache->get(
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
		);
		/**
		 * If there are no known bots - exit from here
		 */
		if (!$bots) {
			return;
		}
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		/**
		 * For bots: login is user agent, email is IP
		 */
		$bot_hash = hash('sha224', $_SERVER->user_agent.$_SERVER->ip);
		/**
		 * Load data
		 */
		$this->user_id = $Cache->$bot_hash;
		/**
		 * If bot found in cache - exit from here
		 */
		if ($this->user_id !== false) {
			return;
		}
		/**
		 * Try to find bot among known bots
		 */
		foreach ($bots as $bot) {
			/**
			 * Check user agent
			 */
			if (
				$bot['login'] &&
				(
					strpos($_SERVER->user_agent, $bot['login']) !== false ||
					_preg_match($bot['login'], $_SERVER->user_agent)
				)
			) {
				$this->user_id = $bot['id'];
				break;
			}
			/**
			 * Check IP
			 */
			if (
				$bot['email'] &&
				(
					$_SERVER->ip == $bot['email'] ||
					_preg_match($bot['email'], $_SERVER->ip)
				)
			) {
				$this->user_id = $bot['id'];
				break;
			}
		}
		unset($bots, $bot);
		/**
		 * If bot found - save it in cache
		 */
		if ($this->user_id) {
			$Cache->$bot_hash = $this->user_id;
		}
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
		return $this->session_id;
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
	 * @param null|string $session_id If `null` - loaded from `$this->session_id`, and if that also empty - from cookies
	 *
	 * @return false|array
	 */
	function get ($session_id) {
		if (!$session_id) {
			if (!$this->session_id) {
				$this->session_id = _getcookie('session');
			}
			$session_id = $this->session_id;
		}
		if (!is_md5($session_id)) {
			return false;
		}
		$session = $this->cache->get(
			$session_id,
			function () use ($session_id) {
				return $this->db()->qf(
					[
						"SELECT
							`id`,
							`user`,
							`expire`,
							`user_agent`,
							`remote_addr`,
							`ip`
						FROM `[prefix]sessions`
						WHERE
							`id`		= '%s' AND
							`expire`	> '%s'
						LIMIT 1",
						$session_id,
						time()
					]
				) ?: false;
			}
		);
		if ($session['expire'] < time()) {
			return false;
		}
		return $session;
	}
	/**
	 * Load session by id and return id of session owner (user), updates last_sign_in, last_ip and last_online information
	 *
	 * @param null|string $session_id If not specified - loaded from `$this->session_id`, and if that also empty - from cookies
	 *
	 * @return int User id
	 */
	function load ($session_id = null) {
		if ($this->user_id == User::GUEST_ID && $this->bot()) {
			return User::GUEST_ID;
		}
		$Config  = Config::instance();
		$User    = User::instance();
		$session = $this->get($session_id);
		$time    = time();
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		if (
			!$session ||
			$session['expire'] <= $time ||
			$session['user_agent'] != $_SERVER->user_agent ||
			!$User->get('id', $session['user']) ||
			(
				$Config->core['remember_user_ip'] &&
				(
					$session['remote_addr'] != ip2hex($_SERVER->remote_addr) ||
					$session['ip'] != ip2hex($_SERVER->ip)
				)
			)
		) {
			$this->add(User::GUEST_ID);
			$this->update_user_is();
			return User::GUEST_ID;
		}
		/**
		 * Session id passed into this method might be `null`, but returned session will contain proper session id
		 * (can be loaded from `$this->session_id`, and if that also empty - from cookies)
		 */
		$session_id = $session['id'];
		$update     = [];
		/**
		 * Updating last online time and ip
		 */
		if ($User->get('last_online', $session['user']) < $time - $Config->core['online_time'] * $Config->core['update_ratio'] / 100) {
			$ip       = ip2hex($_SERVER->ip);
			$update[] = "
				UPDATE `[prefix]users`
				SET
					`last_ip`		= '$ip',
					`last_online`	= $time
				WHERE `id` = $session[user]";
			$User->set(
				[
					'last_ip'     => $ip,
					'last_online' => $time
				],
				null,
				$session['user']
			);
			unset($ip);
		}
		if ($session['expire'] - $time < $Config->core['session_expire'] * $Config->core['update_ratio'] / 100) {
			$session['expire']        = $time + $Config->core['session_expire'];
			$update[]                 = "
				UPDATE `[prefix]sessions`
				SET `expire` = $session[expire]
				WHERE `id` = '$session_id'
				LIMIT 1";
			$this->cache->$session_id = $session;
		}
		if (!empty($update)) {
			$this->db_prime()->q($update);
		}
		$this->user_id    = $session['user'];
		$this->session_id = $session_id;
		$this->update_user_is();
		return $this->user_id;
	}
	/**
	 * Create the session for the user with specified id
	 *
	 * @param false|int $user
	 * @param bool      $delete_current_session
	 *
	 * @return bool
	 */
	function add ($user = false, $delete_current_session = true) {
		$user = (int)$user ?: User::GUEST_ID;
		if ($delete_current_session && is_md5($this->session_id)) {
			$this->del_internal(null, false);
		}
		/**
		 * Load user data
		 * Return point, runs if user is blocked, inactive, or disabled
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
		if (is_array($data)) {
			$L    = Language::instance();
			$Page = Page::instance();
			switch ($data['status']) {
				case User::STATUS_INACTIVE:
					/**
					 * If user is disabled
					 */
					$Page->warning($L->your_account_disabled);
					/**
					 * Create guest session
					 */
					return $this->add(User::GUEST_ID);
				case User::STATUS_NOT_ACTIVATED:
					/**
					 * If user is not active
					 */
					$Page->warning($L->your_account_is_not_active);
					/**
					 * Create guest session
					 */
					return $this->add(User::GUEST_ID);
			}
			if ($data['block_until'] > time()) {
				/**
				 * If user if blocked
				 */
				$Page->warning($L->your_account_blocked_until.' '.date($L->_datetime, $data['block_until']));
				/**
				 * Create guest session
				 */
				return $this->add(User::GUEST_ID);
			}
		} else {
			/**
			 * If data was not loaded - create guest session
			 */
			return $this->add(User::GUEST_ID);
		}
		unset($data);
		$Config = Config::instance();
		$time   = time();
		/**
		 * Generate hash in cycle, to obtain unique value
		 */
		/** @noinspection LoopWhichDoesNotLoopInspection */
		while ($hash = md5(openssl_random_pseudo_bytes(1000))) {
			if ($this->db_prime()->qf(
				"SELECT `id`
				FROM `[prefix]sessions`
				WHERE `id` = '$hash'
				LIMIT 1"
			)
			) {
				continue;
			}
			/**
			 * @var \cs\_SERVER $_SERVER
			 */
			$remote_addr = ip2hex($_SERVER->remote_addr);
			$ip          = ip2hex($_SERVER->ip);
			$expire_in   = $Config->core['session_expire'];
			/**
			 * Many guests open only one page, so create session only for 5 min
			 */
			if ($user == User::GUEST_ID) {
				$expire_in = min($expire_in, 300);
			}
			$expire = $time + $expire_in;
			$this->db_prime()->q(
				"INSERT INTO `[prefix]sessions`
					(
						`id`,
						`user`,
						`created`,
						`expire`,
						`user_agent`,
						`remote_addr`,
						`ip`
					) VALUES (
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s'
					)",
				$hash,
				$user,
				$time,
				$expire,
				$_SERVER->user_agent,
				$remote_addr,
				$ip
			);
			if ($user != User::GUEST_ID) {
				$this->db_prime()->q(
					"UPDATE `[prefix]users`
					SET
						`last_sign_in`	= $time,
						`last_online`	= $time,
						`last_ip`		= '$ip'
					WHERE `id` ='$user'"
				);
			}
			$this->session_id   = $hash;
			$this->cache->$hash = [
				'id'          => $hash,
				'user'        => $user,
				'expire'      => $expire,
				'user_agent'  => $_SERVER->user_agent,
				'remote_addr' => $remote_addr,
				'ip'          => $ip
			];
			_setcookie('session', $hash, $expire);
			$this->load();
			$this->update_user_is();
			$ids_count = $this->db()->qfs(
				"SELECT COUNT(`id`)
				FROM `[prefix]sessions`"
			);
			if (($ids_count % $Config->core['inserts_limit']) == 0) {
				$this->db_prime()->aq(
					"DELETE FROM `[prefix]sessions`
					WHERE `expire` < $time"
				);
			}
			return true;
		}
		return false;
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
		$result = $this->db_prime()->q(
			"DELETE FROM `[prefix]sessions`
			WHERE `id` = '%s'
			LIMIT 1",
			$session_id
		);
		if ($create_guest_session) {
			return $this->add(User::GUEST_ID);
		}
		Event::instance()->fire(
			'System/Session/del/after',
			[
				'id' => $session_id
			]
		);
		return (bool)$result;
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
			foreach ($sessions as $session) {
				unset($this->cache->$session);
			}
			unset($session);
			$sessions = implode("','", $sessions);
			return (bool)$this->db_prime()->q(
				"DELETE FROM `[prefix]sessions`
				WHERE `id` IN('$sessions')"
			);
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
		$data = $this->cache->get(
			"data/$session_id",
			function () use ($session_id) {
				return _json_decode(
					$this->db()->qfs(
						[
							"SELECT `data`
							FROM `[prefix]sessions`
							WHERE `id` = '%s'
							LIMIT 1",
							$session_id
						]
					)
				) ?: false;
			}
		) ?: [];
		return isset($data[$item]) ? $data[$item] : false;
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
		$data        = $this->cache->get(
			"data/$session_id",
			function () use ($session_id) {
				return _json_decode(
					$this->db()->qfs(
						[
							"SELECT `data`
							FROM `[prefix]sessions`
							WHERE `id` = '%s'
							LIMIT 1",
							$session_id
						]
					)
				) ?: false;
			}
		) ?: [];
		$data[$item] = $value;
		if ($this->db()->q(
			"UPDATE `[prefix]sessions`
			SET `data` = '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			_json_encode($data),
			$session_id
		)
		) {
			unset($this->cache->{"data/$session_id"});
			return true;
		}
		return false;
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
		$data = $this->cache->get(
			"data/$session_id",
			function () use ($session_id) {
				return _json_decode(
					$this->db()->qfs(
						[
							"SELECT `data`
							FROM `[prefix]sessions`
							WHERE `id` = '%s'
							LIMIT 1",
							$session_id
						]
					)
				) ?: false;
			}
		) ?: [];
		if (!isset($data[$item])) {
			return true;
		}
		unset($data[$item]);
		if ($this->db()->q(
			"UPDATE `[prefix]sessions`
			SET `data` = '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			_json_encode($data),
			$session_id
		)
		) {
			unset($this->cache->{"data/$session_id"});
			return true;
		}
		return false;
	}
}
