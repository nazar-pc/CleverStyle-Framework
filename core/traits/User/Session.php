<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\User;
use
	cs\Config,
	cs\Language,
	cs\Page,
	cs\Trigger,
	cs\User;

/**
 * Trait that contains all methods from <i>>cs\User</i> for working with user sessions
 *
 * @property int				$id
 * @property \cs\Cache\Prefix	$cache
 * @property string				$user_agent
 * @property string				$ip
 * @property string				$forwarded_for
 * @property string				$client_ip
 */
trait Session {
	/**
	 * Session id of current user
	 * @var bool|string
	 */
	protected	$session_id		= false;
	/**
	 * Returns current session id
	 *
	 * @return bool|string
	 */
	function get_session () {
		if ($this->bot() && $this->id == User::GUEST_ID) {
			return '';
		}
		return $this->session_id;
	}
	/**
	 * Find the session by id as applies it, and return id of owner (user), updates last_sign_in, last_ip and last_online information
	 *
	 * @param null|string	$session_id
	 *
	 * @return int						User id
	 */
	function get_session_user ($session_id = null) {
		if ($this->bot() && $this->id == User::GUEST_ID) {
			return User::GUEST_ID;
		}
		if (!$session_id) {
			if (!$this->session_id) {
				$this->session_id = _getcookie('session');
			}
			$session_id = $session_id ?: $this->session_id;
		}
		if (!is_md5($session_id)) {
			return false;
		}
		$Cache	= $this->cache;
		$Config	= Config::instance();
		$result	= false;
		if ($session_id && !($result = $Cache->{"sessions/$session_id"})) {
			$condition	= $Config->core['remember_user_ip'] ?
				"AND
				`ip`			= '".ip2hex($this->ip)."' AND
				`forwarded_for`	= '".ip2hex($this->forwarded_for)."' AND
				`client_ip`		= '".ip2hex($this->client_ip)."'"
				: '';
			$result	= $this->db()->qf([
				"SELECT
					`user`,
					`expire`,
					`user_agent`,
					`ip`,
					`forwarded_for`,
					`client_ip`
				FROM `[prefix]sessions`
				WHERE
					`id`			= '%s' AND
					`expire`		> '%s' AND
					`user_agent`	= '%s'
					$condition
				LIMIT 1",
				$session_id,
				TIME,
				$this->user_agent
			]);
			unset($condition);
			if ($result) {
				$Cache->{"sessions/$session_id"} = $result;
			}
		}
		if (!(
			$session_id &&
			is_array($result) &&
			$result['expire'] > TIME &&
			$this->get('id', $result['user'])
		)) {
			$this->add_session(User::GUEST_ID);
			$this->update_user_is();
			return User::GUEST_ID;
		}
		$update	= [];
		/**
		 * Updating last online time
		 */
		if ($result['user'] != 0 && $this->get('last_online', $result['user']) < TIME - $Config->core['online_time'] * $Config->core['update_ratio'] / 100) {
			/**
			 * Updating last sign in time and ip
			 */
			$time	= TIME;
			if ($this->get('last_online', $result['user']) < TIME - $Config->core['online_time']) {
				$ip			= ip2hex($this->ip);
				$update[]	= "
					UPDATE `[prefix]users`
					SET
						`last_sign_in`	= $time,
						`last_ip`		= '$ip',
						`last_online`	= $time
					WHERE `id` =$result[user]";
				$this->set(
					[
						'last_sign_in'	=> TIME,
						'last_ip'		=> $ip,
						'last_online'	=> TIME
					],
					null,
					$result['user']
				);
				unset($ip);
			} else {
				$update[]	= "
					UPDATE `[prefix]users`
					SET `last_online` = $time
					WHERE `id` = $result[user]";
				$this->set(
					'last_online',
					TIME,
					$result['user']
				);
			}
			unset($time);
		}
		if ($result['expire'] - TIME < $Config->core['session_expire'] * $Config->core['update_ratio'] / 100) {
			$result['expire']	= TIME + $Config->core['session_expire'];
			$update[]			= "
				UPDATE `[prefix]sessions`
				SET `expire` = $result[expire]
				WHERE `id` = '$session_id'
				LIMIT 1";
			$Cache->{"sessions/$session_id"} = $result;
		}
		if (!empty($update)) {
			$this->db_prime()->q($update);
		}
		$this->update_user_is();
		return $this->id = $result['user'];
	}
	/**
	 * Create the session for the user with specified id
	 *
	 * @param bool|int	$user
	 * @param bool		$delete_current_session
	 *
	 * @return bool
	 */
	function add_session ($user = false, $delete_current_session = true) {
		$user	= (int)$user ?: User::GUEST_ID;
		if ($delete_current_session && is_md5($this->session_id)) {
			$this->del_session_internal(null, false);
		}
		/**
		 * Load user data
		 * Return point, runs if user is blocked, inactive, or disabled
		 */
		getting_user_data:
		$data	= $this->get(
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
			$L		= Language::instance();
			$Page	= Page::instance();
			if ($data['status'] != User::STATUS_ACTIVE) {
				/**
				 * If user is disabled
				 */
				if ($data['status'] == User::STATUS_INACTIVE) {
					$Page->warning($L->your_account_disabled);
					/**
					 * Mark user as guest, load data again
					 */
					$user	= User::GUEST_ID;
					goto getting_user_data;
				/**
				 * If user is not active
				 */
				} else {
					$Page->warning($L->your_account_is_not_active);
					/**
					 * Mark user as guest, load data again
					 */
					$user	= User::GUEST_ID;
					goto getting_user_data;
				}
			/**
			 * If user if blocked
			 */
			} elseif ($data['block_until'] > TIME) {
				$Page->warning($L->your_account_blocked_until.' '.date($L->_datetime, $data['block_until']));
				/**
				 * Mark user as guest, load data again
				 */
				$user	= User::GUEST_ID;
				goto getting_user_data;
			}
		} elseif ($this->id != User::GUEST_ID) {
			/**
			 * If data was not loaded - mark user as guest, load data again
			 */
			$user	= User::GUEST_ID;
			goto getting_user_data;
		}
		unset($data);
		$Config	= Config::instance();
		/**
		 * Generate hash in cycle, to obtain unique value
		 */
		for ($i = 0; $hash = md5(MICROTIME.uniqid($i, true)); ++$i) {
			if ($this->db_prime()->qf(
				"SELECT `id`
				FROM `[prefix]sessions`
				WHERE `id` = '$hash'
				LIMIT 1"
			)) {
				continue;
			}
			$this->db_prime()->q(
				"INSERT INTO `[prefix]sessions`
					(
						`id`,
						`user`,
						`created`,
						`expire`,
						`user_agent`,
						`ip`,
						`forwarded_for`,
						`client_ip`
					) VALUES (
						'%s',
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
				TIME,
				/**
				 * Many guests open only one page, so create session only for 5 min
				 */
				TIME + ($user != User::GUEST_ID || $Config->core['session_expire'] < 300 ? $Config->core['session_expire'] : 300),
				$this->user_agent,
				$ip				= ip2hex($this->ip),
				$forwarded_for	= ip2hex($this->forwarded_for),
				$client_ip		= ip2hex($this->client_ip)
			);
			$time								= TIME;
			if ($user != User::GUEST_ID) {
				$this->db_prime()->q("UPDATE `[prefix]users` SET `last_sign_in` = $time, `last_online` = $time, `last_ip` = '$ip.' WHERE `id` ='$user'");
			}
			$this->session_id			= $hash;
			$this->cache->{"sessions/$hash"}	= [
				'user'			=> $user,
				'expire'		=> TIME + $Config->core['session_expire'],
				'user_agent'	=> $this->user_agent,
				'ip'			=> $ip,
				'forwarded_for'	=> $forwarded_for,
				'client_ip'		=> $client_ip
			];
			_setcookie('session', $hash, TIME + $Config->core['session_expire']);
			$this->id							= $this->get_session_user();
			$this->update_user_is();
			if (
				($this->db()->qfs(
					 "SELECT COUNT(`id`)
					 FROM `[prefix]sessions`"
				 ) % $Config->core['inserts_limit']) == 0
			) {
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
	 * @param null|string	$session_id
	 *
	 * @return bool
	 */
	function del_session ($session_id = null) {
		Trigger::instance()->run(
			'System/User/del_session/before'
		);
		$result	= $this->del_session_internal($session_id);
		Trigger::instance()->run(
			'System/User/del_session/after'
		);
		return $result;
	}
	/**
	 * Deletion of the session
	 *
	 * @param string	$session_id
	 * @param bool		$create_guest_session
	 *
	 * @return bool
	 */
	protected function del_session_internal ($session_id = null, $create_guest_session = true) {
		$session_id = $session_id ?: $this->session_id;
		if (!is_md5($session_id)) {
			return false;
		}
		unset($this->cache->{"sessions/$session_id"});
		$this->session_id = false;
		_setcookie('session', '');
		$result =  $this->db_prime()->q(
			"DELETE FROM `[prefix]sessions`
			WHERE `id` = '%s'
			LIMIT 1",
			$session_id
		);
		if ($create_guest_session) {
			return $this->add_session(User::GUEST_ID);
		}
		return $result;
	}
	/**
	 * Deletion of all user sessions
	 *
	 * @param bool|int	$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_all_sessions ($user = false) {
		Trigger::instance()->run(
			'System/User/del_all_sessions',
			[
				'id'	=> $user
			]
		);
		$user		= $user ?: $this->id;
		$sessions	= $this->db_prime()->qfas(
			"SELECT `id`
			FROM `[prefix]sessions`
			WHERE `user` = '$user'"
		);
		if (is_array($sessions)) {
			foreach ($sessions as $session) {
				unset($this->cache->{"sessions/$session"});
			}
			unset($session);
			$sessions	= implode("','", $sessions);
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
	 * @param string		$item
	 * @param null|string	$session_id
	 *
	 * @return bool|mixed
	 *
	 */
	function get_session_data ($item, $session_id = null) {
		$session_id	= $session_id ?: $this->session_id;
		if (!is_md5($session_id)) {
			return false;
		}
		$data	= $this->cache->get("sessions/data/$session_id", function () use ($session_id) {
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
	 * @param string		$item
	 * @param mixed			$value
	 * @param null|string	$session_id
	 *
	 * @return bool
	 *
	 */
	function set_session_data ($item, $value, $session_id = null) {
		$session_id		= $session_id ?: $this->session_id;
		if (!is_md5($session_id)) {
			return false;
		}
		$data			= $this->cache->get("sessions/data/$session_id", function () use ($session_id) {
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
		$data[$item]	= $value;
		if ($this->db()->q(
			"UPDATE `[prefix]sessions`
			SET `data` = '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			_json_encode($data),
			$session_id
		)) {
			unset($this->cache->{"sessions/data/$session_id"});
			return true;
		}
		return false;
	}
	/**
	 * Delete data, stored with session
	 *
	 * @param string		$item
	 * @param null|string	$session_id
	 *
	 * @return bool
	 *
	 */
	function del_session_data ($item, $session_id = null) {
		$session_id	= $session_id ?: $this->session_id;
		if (!is_md5($session_id)) {
			return false;
		}
		$data		= $this->cache->get("sessions/data/$session_id", function () use ($session_id) {
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
		)) {
			unset($this->cache->{"sessions/data/$session_id"});
			return true;
		}
		return false;
	}
}
