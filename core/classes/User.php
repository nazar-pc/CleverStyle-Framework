<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Provides next events:
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
 */
namespace	cs;
use
	cs\Cache\Prefix,
	cs\DB\Accessor,
	cs\User\Data as User_data,
	cs\User\Group as User_group,
	cs\User\Management as User_management,
	cs\User\Permission as User_permission;
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
 * @property	int		$last_sign_in	unix timestamp
 * @property	string	$last_ip		hex value, obtained by function ip2hex()
 * @property	int		$last_online	unix timestamp
 * @property	string	$avatar
 *
 * @method static User instance($check = false)
 */
class User {
	use
		Accessor,
		Singleton,
		User_data,
		User_group,
		User_management,
		User_permission;
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
	protected function construct () {
		$this->cache	= new Prefix('users');
		Event::instance()->fire('System/User/construct/before');
		$this->initialize_data();
		/**
		 * Initialize session
		 */
		Session::instance();
		Event::instance()->fire('System/User/construct/after');
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
		$time	= time();
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		return $this->db()->qfs([
			"SELECT COUNT(`expire`)
			FROM `[prefix]sign_ins`
			WHERE
				`expire` > $time AND
				(
					`login_hash`	= '%s' OR
					`ip`			= '%s'
				)",
			$login_hash,
			ip2hex($_SERVER->ip)
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
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		$ip		= ip2hex($_SERVER->ip);
		$time	= time();
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
				$time + $Config->core['sign_in_attempts_block_time'],
				$login_hash,
				$ip
			);
			if ($this->db_prime()->id() % $Config->core['inserts_limit'] == 0) {
				$this->db_prime()->aq("DELETE FROM `[prefix]sign_ins` WHERE `expire` < $time");
			}
		}
	}
	/**
	 * Get data item of current user
	 *
	 * @param string|string[]		$item
	 *
	 * @return array|false|string
	 */
	function __get ($item) {
		if ($item == 'id') {
			return Session::instance()->get_user();
		}
		return $this->get($item);
	}
	/**
	 * Set data item of current user
	 *
	 * @param array|string	$item	Item-value array may be specified for setting several items at once
	 * @param mixed|null	$value
	 *
	 * @return bool
	 */
	function __set ($item, $value = null) {
		$this->set($item, $value);
	}
	/**
	 * Is admin
	 *
	 * Proxy to \cs\Session::instance()->admin() for convenience
	 *
	 * @return bool
	 */
	function admin () {
		return Session::instance()->admin();
	}
	/**
	 * Is user
	 *
	 * Proxy to \cs\Session::instance()->user() for convenience
	 *
	 * @return bool
	 */
	function user () {
		return Session::instance()->user();
	}
	/**
	 * Is guest
	 *
	 * Proxy to \cs\Session::instance()->guest() for convenience
	 *
	 * @return bool
	 */
	function guest () {
		return Session::instance()->guest();
	}
	/**
	 * Is bot
	 *
	 * Proxy to \cs\Session::instance()->bot() for convenience
	 *
	 * @return bool
	 */
	function bot () {
		return Session::instance()->bot();
	}
	/**
	 * Saving changes of cache and users data
	 */
	function __finish () {
		$this->save_cache_and_user_data();
	}
}
