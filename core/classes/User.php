<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Provides next triggers:
 *  System/User/construct/before
 *
 *  System/User/construct/after
 *
 *  System/User/registration/before
 *  ['email'	=> <i>email</i>]
 *
 *  System/User/registration/after
 *  ['id'	=> <i>user_id</i>]
 *
 *  System/User/registration/confirmation/before
 *  ['reg_key'	=> <i>reg_key</i>]
 *
 *  System/User/registration/confirmation/after
 *  ['id'	=> <i>user_id</i>]
 *
 *  System/User/del/before
 *  ['id'	=> <i>user_id</i>]
 *
 *  System/User/del/after
 *  ['id'	=> <i>user_id</i>]
 *
 *  System/User/add_bot
 *  ['id'	=> <i>bot_id</i>]
 *
 *  System/User/get_contacts
 *  [
 *  	'id'		=> <i>user_id</i>,
 *  	'contacts'	=> <i>&$contacts</i>	//Array of user id
 *  ]
 *
 *  System/User/del_session/before
 *
 *  System/User/del_session/after
 *
 *  System/User/del_all_sessions
 *  ['id'	=> <i>user_id</i>]
 */
namespace	cs;
use
	cs\Cache\Prefix,
	cs\DB\Accessor,
	cs\Permission\Any,
	cs\User\Data as User_data,
	cs\User\Group as User_group,
	cs\User\Management as User_management,
	cs\User\Permission as User_permission,
	cs\User\Session as User_sessions;
/**
 * Class for users manipulating
 *
 * @property	int		$id
 * @property	string	$login
 * @property	string	$login_hash		sha224 hash
 * @property	string	$username
 * @property	string	$password_hash	sha512 hash
 * @property	string	$email
 * @property	string	$email_hash		sha224 hash
 * @property	string	$language
 * @property	string	$timezone
 * @property	int		$reg_date		unix timestamp
 * @property	string	$reg_ip			hex value, obtained by function ip2hex()
 * @property	string	$reg_key		random md5 hash, generated during registration
 * @property	int		$status			'-1' - not activated (for example after registration), 0 - inactive, 1 - active
 * @property	int		$block_until	unix timestamp
 * @property	int		$last_sign_in		unix timestamp
 * @property	string	$last_ip		hex value, obtained by function ip2hex()
 * @property	int		$last_online	unix timestamp
 * @property	string	$avatar
 * @property	string	$user_agent
 * @property	string	$ip
 * @property	string	$forwarded_for
 * @property	string	$client_ip
 *
 * @method static User instance($check = false)
 */
class User {
	use
		Accessor,
		Singleton,
		Any,
		User_data,
		User_group,
		User_management,
		User_permission,
		User_sessions;
	/**
	 * Id of system guest user
	 */
	const		GUEST_ID				= 1;
	/**
	 * Id of first, primary system administrator
	 */
	const		ROOT_ID					= 2;
	/**
	 * Id of system group for administrators
	 */
	const		ADMIN_GROUP_ID			= 1;
	/**
	 * Id of system group for users
	 */
	const		USER_GROUP_ID			= 2;
	/**
	 * Id of system group for bots
	 */
	const		BOT_GROUP_ID			= 3;
	/**
	 * Status of active user
	 */
	const		STATUS_ACTIVE			= 1;
	/**
	 * Status of inactive user
	 */
	const		STATUS_INACTIVE			= 0;
	/**
	 * Status of not activated user
	 */
	const		STATUS_NOT_ACTIVATED	= -1;
	protected	$is_admin		= false;
	protected	$is_user		= false;
	protected	$is_bot			= false;
	protected	$is_guest		= false;
	protected	$is_system		= false;
	/**
	 * Id of current user
	 * @var bool|int
	 */
	protected	$id				= false;
	/**
	 * Current state of initialization
	 * @var bool
	 */
	protected	$init			= false;
	/**
	 * Copy of columns list of users table for internal needs without Cache usage
	 * @var array
	 */
	protected	$users_columns	= [];
	/**
	 * @var Prefix
	 */
	protected	$cache;
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('System')->db('users');
	}
	/**
	 * Defining user id, type, session, personal settings
	 */
	function construct () {
		$Cache	= $this->cache	= new Prefix('users');
		$Config	= Config::instance();
		Trigger::instance()->run('System/User/construct/before');
		$this->users_columns = $Cache->get('columns', function () {
			return $this->db()->columns('[prefix]users');
		});
		if ($this->request_from_system($Config)) {
			/**
			 * No need to do anything else for system, just exit from constructor
			 */
			return;
		}
		/**
		 * If session exists
		 */
		if (_getcookie('session')) {
			$this->id = $this->get_session_user();
		/**
		 * Try to detect bot, not necessary for API request
		 */
		} elseif (!api_path()) {
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
					self::BOT_GROUP_ID,
					self::STATUS_ACTIVE
				]) ?: [];
			});
			/**
			 * For bots: login is user agent, email is IP
			 */
			$bot_hash	= hash('sha224', $this->user_agent.$this->ip);
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
								strpos($this->user_agent, $bot['login']) !== false ||
								_preg_match($bot['login'], $this->user_agent)
							)
						) {
							$this->id	= $bot['id'];
							break;
						}
						if (
							$bot['email'] &&
							(
								$this->ip == $bot['email'] ||
								_preg_match($bot['email'], $this->ip)
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
							$this->get_session_user($last_session);
						}
						if (!$last_session || $this->id == self::GUEST_ID) {
							$this->add_session($id);
							$this->set_data('last_session', $this->get_session());
						}
						unset($id, $last_session);
					}
				}
			}
			unset($bots, $bot_hash);
		}
		if (!$this->id) {
			$this->id	= self::GUEST_ID;
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
		if ($this->id != self::GUEST_ID) {
			if ($this->timezone && date_default_timezone_get() != $this->timezone) {
				date_default_timezone_set($this->timezone);
			}
			if ($Config->core['multilingual']) {
				Language::instance()->change($this->language);
			}
		} elseif ($Config->core['multilingual']) {
			/**
			 * Automatic detection of current language for guest
			 */
			Language::instance()->change('');
		}
		/**
		 * Security check
		 */
		if (!isset($_REQUEST['session']) || $_REQUEST['session'] != $this->get_session()) {
			$_REQUEST	= array_diff_key($_REQUEST, $_POST);
			$_POST		= [];
		}
		$this->init	= true;
		Trigger::instance()->run('System/User/construct/after');
	}
	protected function request_from_system ($Config) {
		/**
		 * Check for User Agent
		 */
		if ($this->user_agent != 'CleverStyle CMS') {
			return false;
		}
		/**
		 * Check for allowed sign in attempts
		 */
		if (
			$this->get_sign_in_attempts_count(hash('sha224', 0)) > $Config->core['sign_in_attempts_block_count'] && // 0 - is magical login used for blocking in such cases
			$Config->core['sign_in_attempts_block_count'] != 0
		) {
			return false;
		}
		$rc	= $Config->route;
		if (count($rc) <= 1) {
			return false;
		}
		/**
		 * Last part in page path - key
		 */
		$key_data = Key::instance()->get(
			$Config->module('System')->db('keys'),
			$key = array_slice($rc, -1)[0],
			true
		);
		if (!is_array($key_data)) {
			return false;
		}
		if ($this->is_system = ($key_data['url'] == $Config->server['host'].'/'.$Config->server['raw_relative_address'])) {
			$this->is_admin = true;
			interface_off();
			$_POST['data'] = _json_decode($_POST['data']);
			Trigger::instance()->run('System/User/construct/after');
			return true;
		}
		$this->is_guest = true;
		/**
		 * Simulate a bad sign in to block access
		 */
		$this->sign_in_result(false, hash('sha224', 'system'));
		unset($_POST['data']);
		return false;
	}
	/**
	 * Updates information about who is user accessed by methods ::guest() ::bot() ::user() admin() ::system()
	 */
	protected function update_user_is () {
		$this->is_guest		= false;
		$this->is_bot		= false;
		$this->is_user		= false;
		$this->is_admin		= false;
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
				$this->is_admin	= Config::instance()->can_be_admin;
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
	 * @return bool
	 */
	function system () {
		return $this->is_system;
	}
	/**
	 * Check number of sign in attempts (is used by system)
	 *
	 * @param bool|string	$login_hash	Hash (sha224) from login (hash from lowercase string)
	 *
	 * @return int						Number of attempts
	 */
	function get_sign_in_attempts_count ($login_hash = false) {
		$login_hash = $login_hash ?: (isset($_POST['login']) ? $_POST['login'] : false);
		if (!preg_match('/^[0-9a-z]{56}$/', $login_hash)) {
			return false;
		}
		$time	= TIME;
		return $this->db()->qfs([
			"SELECT COUNT(`expire`)
			FROM `[prefix]sign_ins`
			WHERE
				`expire` > $time AND
				(
					`login_hash` = '%s' OR `ip` = '%s'
				)",
			$login_hash,
			ip2hex($this->ip)
		]);
	}
	/**
	 * Process sign in result (is used by system)
	 *
	 * @param bool $success
	 * @param bool|string	$login_hash	Hash (sha224) from login (hash from lowercase string)
	 */
	function sign_in_result ($success, $login_hash = false) {
		$login_hash = $login_hash ?: (isset($_POST['login']) ? $_POST['login'] : false);
		if (!preg_match('/^[0-9a-z]{56}$/', $login_hash)) {
			return;
		}
		$ip		= ip2hex($this->ip);
		$time	= TIME;
		if ($success) {
			$this->db_prime()->q(
				"DELETE FROM `[prefix]sign_ins`
				WHERE
					`expire` > $time AND
					(
						`login_hash` = '%s' OR `ip` = '%s'
					)",
				$login_hash,
				$ip
			);
		} else {
			$Config	= Config::instance();
			$this->db_prime()->q(
				"INSERT INTO `[prefix]sign_ins`
					(
						`expire`,
						`login_hash`,
						`ip`
					) VALUES (
						'%s',
						'%s',
						'%s'
					)",
				TIME + $Config->core['sign_in_attempts_block_time'],
				$login_hash,
				$ip
			);
			if ($this->db_prime()->id() % $Config->core['inserts_limit'] == 0) {
				$this->db_prime()->aq("DELETE FROM `[prefix]sign_ins` WHERE `expire` < $time");
			}
		}
	}
	/**
	 * Returns array of users columns, available for getting of data
	 *
	 * @return array
	 */
	function get_users_columns () {
		return $this->users_columns;
	}
}
