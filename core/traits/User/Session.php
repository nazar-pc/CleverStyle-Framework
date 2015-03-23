<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\User;
use
	cs\Config,
	cs\Event,
	cs\Language,
	cs\Page,
	cs\User;

/**
 * Trait that contains all methods from <i>>cs\User</i> for working with user sessions
 *
 * @property int                 $id
 * @property \cs\Cache\Prefix    $cache
 */
trait Session {
	/**
	 * Session id of current user
	 * @var bool|string
	 */
	protected $session_id = false;
	protected $is_admin   = false;
	protected $is_user    = false;
	protected $is_bot     = false;
	protected $is_guest   = false;
	/**
	 * @deprecated
	 * @todo Remove in future versions
	 *
	 * @var bool
	 */
	protected	$is_system		= false;
	/**
	 * Use cookie as source of session id, load session
	 *
	 * Bots detection is also done here
	 */
	protected function initialize_session () {
		/**
		 * If session exists
		 */
		if (_getcookie('session')) {
			$this->id = $this->load_session();
		/**
		 * Try to detect bot, not necessary for API request
		 */
		} elseif (!api_path()) {
			$Cache = $this->cache;
			/**
			 * Loading bots list
			 */
			$bots = $Cache->get('bots', function () {
				return $this->db()->qfa([
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
				]) ?: [];
			});
			/**
			 * @var \cs\_SERVER $_SERVER
			 */
			/**
			 * For bots: login is user agent, email is IP
			 */
			$bot_hash	= hash('sha224', $_SERVER->user_agent.$_SERVER->ip);
			/**
			 * If list is not empty - try to find bot
			 */
			if (is_array($bots) && !empty($bots)) {
				/**
				 * Load data
				 */
				$this->id = $Cache->$bot_hash;
				if ($this->id === false) {
					/**
					 * If no data - try to find bot in list of known bots
					 */
					foreach ($bots as $bot) {
						if (
							$bot['login'] &&
							(
								strpos($_SERVER->user_agent, $bot['login']) !== false ||
								_preg_match($bot['login'], $_SERVER->user_agent)
							)
						) {
							$this->id	= $bot['id'];
							break;
						}
						if (
							$bot['email'] &&
							(
								$_SERVER->ip == $bot['email'] ||
								_preg_match($bot['email'], $_SERVER->ip)
							)
						) {
							$this->id	= $bot['id'];
							break;
						}
					}
					unset($bots, $bot, $login, $email);
					/**
					 * If found id - this is bot
					 */
					if ($this->id) {
						$Cache->$bot_hash	= $this->id;
						/**
						 * Searching for last bot session, if exists - load it, otherwise create new one
						 */
						$last_session		= $this->get_data('last_session');
						$id					= $this->id;
						if ($last_session) {
							$this->load_session($last_session);
						}
						if (!$last_session || $this->id == User::GUEST_ID) {
							$this->add_session($id);
							$this->set_data('last_session', $this->get_session_id());
						}
						unset($id, $last_session);
					}
				}
			}
			unset($bots, $bot_hash);
		}
		if (!$this->id) {
			$this->id	= User::GUEST_ID;
			/**
			 * Do not create session for API request
			 */
			if (!api_path()) {
				$this->add_session();
			}
		}
		$this->update_user_is();
		/**
		 * If not guest - apply some individual settings
		 */
		if ($this->id != User::GUEST_ID) {
			if ($this->timezone && date_default_timezone_get() != $this->timezone) {
				date_default_timezone_set($this->timezone);
			}
			$L = Language::instance();
			/**
			 * Change language if configuration is multilingual and this is not page with localized url
			 */
			if ($Config->core['multilingual'] && !$L->url_language()) {
				$L->change($this->language);
			}
		}
		/**
		 * Security check
		 *
		 * @todo Probably, better move to System/User/construct/after event handler somewhere instead
		 */
		if (!isset($_REQUEST['session']) || $_REQUEST['session'] != $this->get_session_id()) {
			foreach (array_keys((array)$_POST) as $key) {
				unset($_POST[$key], $_REQUEST[$key]);
			}
		}
	}
	/**
	 * Updates information about who is user accessed by methods ::guest() ::bot() ::user() admin() ::system()
	 */
	protected function update_user_is () {
		$this->is_guest		= false;
		$this->is_bot		= false;
		$this->is_user		= false;
		$this->is_admin		= false;
		//TODO Remove in future versions
		$this->is_system	= false;
		if ($this->id == self::GUEST_ID) {
			$this->is_guest = true;
			return;
		} else {
			/**
			 * Checking of user type
			 */
			$groups = $this->get_groups() ?: [];
			if (in_array(self::ADMIN_GROUP_ID, $groups)) {
				$this->is_admin	= Config::instance()->can_be_admin();
				$this->is_user	= true;
			} elseif (in_array(self::USER_GROUP_ID, $groups)) {
				$this->is_user	= true;
			} elseif (in_array(self::BOT_GROUP_ID, $groups)) {
				$this->is_guest	= true;
				$this->is_bot	= true;
			}
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
	 * Is system
	 *
	 * @deprecated
	 * @todo Remove in future versions
	 *
	 * @return bool
	 */
	function system () {
		return $this->is_system;
	}
	/**
	 * Returns current session id
	 *
	 * @return bool|string
	 */
	function get_session_id () {
		if ($this->id == User::GUEST_ID && $this->bot()) {
			return '';
		}
		return $this->session_id;
	}
	/**
	 * Returns session details by session id
	 *
	 * @param null|string $session_id If `null` - loaded from `$this->session_id`, and if that also empty - from cookies
	 *
	 * @return bool|array
	 */
	function get_session ($session_id) {
		if (func_num_args() == 0) {
			trigger_error('calling User::get_session() without arguments is deprecated, use ::get_session_id() instead', E_USER_DEPRECATED);
			return $this->get_session_id();
		}
		if (!$session_id) {
			if (!$this->session_id) {
				$this->session_id = _getcookie('session');
			}
			$session_id = $this->session_id;
		}
		if (!is_md5($session_id)) {
			return false;
		}
		$Cache = $this->cache;
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		$session = $Cache->get("sessions/$session_id", function () use ($session_id) {
			return $this->db()->qf([
				"SELECT
					`id`,
					`user`,
					`expire`,
					`user_agent`,
					`remote_addr`,
					`ip`
				FROM `[prefix]sessions`
				WHERE
					`id`			= '%s' AND
					`expire`		> '%s'
				LIMIT 1",
				$session_id,
				time()
			]) ?: false;
		});
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
	function load_session ($session_id = null) {
		if ($this->id == User::GUEST_ID && $this->bot()) {
			return User::GUEST_ID;
		}
		$Config  = Config::instance();
		$session = $this->get_session($session_id);
		$time    = time();
		if (
			!$session ||
			$session['user_agent'] != $_SERVER->user_agent ||
			$session['expire'] <= $time ||
			!$this->get('id', $session['user']) ||
			(
				$Config->core['remember_user_ip'] &&
				(
					$session['remote_addr'] != ip2hex($_SERVER->remote_addr) ||
					$session['ip'] != ip2hex($_SERVER->ip)
				)
			)
		) {
			$this->add_session(User::GUEST_ID);
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
		 * Updating last online time
		 */
		if (
			$session['user'] != 0 &&
			$this->get('last_online', $session['user']) < $time - $Config->core['online_time'] * $Config->core['update_ratio'] / 100
		) {
			/**
			 * Updating last sign in time and ip
			 */
			if ($this->get('last_online', $session['user']) < $time - $Config->core['online_time']) {
				$ip       = ip2hex($_SERVER->ip);
				$update[] = "
					UPDATE `[prefix]users`
					SET
						`last_sign_in`	= $time,
						`last_ip`		= '$ip',
						`last_online`	= $time
					WHERE `id` =$session[user]";
				$this->set(
					[
						'last_sign_in' => $time,
						'last_ip'      => $ip,
						'last_online'  => $time
					],
					null,
					$session['user']
				);
				unset($ip);
			} else {
				$update[] = "
					UPDATE `[prefix]users`
					SET `last_online` = $time
					WHERE `id` = $session[user]";
				$this->set(
					'last_online',
					$time,
					$session['user']
				);
			}
		}
		if ($session['expire'] - $time < $Config->core['session_expire'] * $Config->core['update_ratio'] / 100) {
			$session['expire']                     = $time + $Config->core['session_expire'];
			$update[]                              = "
				UPDATE `[prefix]sessions`
				SET `expire` = $session[expire]
				WHERE `id` = '$session_id'
				LIMIT 1";
			$this->cache->{"sessions/$session_id"} = $session;
		}
		if (!empty($update)) {
			$this->db_prime()->q($update);
		}
		$this->id         = $session['user'];
		$this->session_id = $session_id;
		$this->update_user_is();
		return $this->id;
	}
	/**
	 * Create the session for the user with specified id
	 *
	 * @param bool|int $user
	 * @param bool     $delete_current_session
	 *
	 * @return bool
	 */
	function add_session ($user = false, $delete_current_session = true) {
		$user = (int)$user ?: User::GUEST_ID;
		if ($delete_current_session && is_md5($this->session_id)) {
			$this->del_session_internal(null, false);
		}
		/**
		 * Load user data
		 * Return point, runs if user is blocked, inactive, or disabled
		 */
		getting_user_data:
		$data = $this->get(
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
			if ($data['status'] != User::STATUS_ACTIVE) {
				if ($data['status'] == User::STATUS_INACTIVE) {
					/**
					 * If user is disabled
					 */
					$Page->warning($L->your_account_disabled);
					/**
					 * Mark user as guest, load data again
					 */
					$user = User::GUEST_ID;
					goto getting_user_data;
				} else {
					/**
					 * If user is not active
					 */
					$Page->warning($L->your_account_is_not_active);
					/**
					 * Mark user as guest, load data again
					 */
					$user = User::GUEST_ID;
					goto getting_user_data;
				}
			} elseif ($data['block_until'] > time()) {
				/**
				 * If user if blocked
				 */
				$Page->warning($L->your_account_blocked_until.' '.date($L->_datetime, $data['block_until']));
				/**
				 * Mark user as guest, load data again
				 */
				$user = User::GUEST_ID;
				goto getting_user_data;
			}
		} elseif ($this->id != User::GUEST_ID) {
			/**
			 * If data was not loaded - mark user as guest, load data again
			 */
			$user = User::GUEST_ID;
			goto getting_user_data;
		}
		unset($data);
		$Config = Config::instance();
		$time   = time();
		/**
		 * Generate hash in cycle, to obtain unique value
		 */
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
				/**
				 * Many guests open only one page, so create session only for 5 min
				 */
				$time + ($user != User::GUEST_ID || $Config->core['session_expire'] < 300 ? $Config->core['session_expire'] : 300),
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
						`last_ip`		= '$ip.'
					WHERE `id` ='$user'"
				);
			}
			$this->session_id                = $hash;
			$this->cache->{"sessions/$hash"} = [
				'id'          => $hash,
				'user'        => $user,
				'expire'      => $time + $Config->core['session_expire'],
				'user_agent'  => $_SERVER->user_agent,
				'remote_addr' => $remote_addr,
				'ip'          => $ip
			];
			_setcookie('session', $hash, $time + $Config->core['session_expire']);
			$this->load_session();
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
	function del_session ($session_id = null) {
		return $this->del_session_internal($session_id);
	}
	/**
	 * Deletion of the session
	 *
	 * @param string $session_id
	 * @param bool   $create_guest_session
	 *
	 * @return bool
	 */
	protected function del_session_internal ($session_id = null, $create_guest_session = true) {
		$session_id = $session_id ?: $this->session_id;
		if (!is_md5($session_id)) {
			return false;
		}
		Event::instance()->fire('System/User/del_session/before', [
			'id' => $session_id
		]);
		unset($this->cache->{"sessions/$session_id"});
		$this->session_id = false;
		_setcookie('session', '');
		$result = $this->db_prime()->q(
			"DELETE FROM `[prefix]sessions`
			WHERE `id` = '%s'
			LIMIT 1",
			$session_id
		);
		if ($create_guest_session) {
			return $this->add_session(User::GUEST_ID);
		}
		Event::instance()->fire('System/User/del_session/after', [
			'id' => $session_id
		]);
		return $result;
	}
	/**
	 * Deletion of all user sessions
	 *
	 * @param bool|int $user If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_all_sessions ($user = false) {
		Event::instance()->fire('System/User/del_all_sessions', [
			'id' => $user
		]);
		$user     = $user ?: $this->id;
		$sessions = $this->db_prime()->qfas(
			"SELECT `id`
			FROM `[prefix]sessions`
			WHERE `user` = '$user'"
		);
		if (is_array($sessions)) {
			foreach ($sessions as $session) {
				unset($this->cache->{"sessions/$session"});
			}
			unset($session);
			$sessions = implode("','", $sessions);
			return $this->db_prime()->q(
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
	 * @return bool|mixed
	 *
	 */
	function get_session_data ($item, $session_id = null) {
		$session_id = $session_id ?: $this->session_id;
		if (!is_md5($session_id)) {
			return false;
		}
		$data = $this->cache->get("sessions/data/$session_id", function () use ($session_id) {
			return _json_decode(
				$this->db()->qfs([
					"SELECT `data`
					FROM `[prefix]sessions`
					WHERE `id` = '%s'
					LIMIT 1",
					$session_id
				])
			) ?: false;
		}) ?: [];
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
	function set_session_data ($item, $value, $session_id = null) {
		$session_id = $session_id ?: $this->session_id;
		if (!is_md5($session_id)) {
			return false;
		}
		$data        = $this->cache->get("sessions/data/$session_id", function () use ($session_id) {
			return _json_decode(
				$this->db()->qfs([
					"SELECT `data`
					FROM `[prefix]sessions`
					WHERE `id` = '%s'
					LIMIT 1",
					$session_id
				])
			) ?: false;
		}) ?: [];
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
			unset($this->cache->{"sessions/data/$session_id"});
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
	function del_session_data ($item, $session_id = null) {
		$session_id = $session_id ?: $this->session_id;
		if (!is_md5($session_id)) {
			return false;
		}
		$data = $this->cache->get("sessions/data/$session_id", function () use ($session_id) {
			return _json_decode(
				$this->db()->qfs([
					"SELECT `data`
					FROM `[prefix]sessions`
					WHERE `id` = '%s'
					LIMIT 1",
					$session_id
				])
			) ?: false;
		}) ?: [];
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
			unset($this->cache->{"sessions/data/$session_id"});
			return true;
		}
		return false;
	}
}
