<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	cs\DB\Accessor,
	cs\User\Data as User_data,
	cs\User\Group as User_group,
	cs\User\Management as User_management,
	cs\User\Permission as User_permission,
	cs\User\Profile as User_profile;

/**
 * Class for users manipulating
 *
 * Provides next events:
 *  System/User/construct/before
 *
 *  System/User/construct/after
 *
 *  System/User/registration/before
 *  ['email' => <i>email</i>]
 *
 *  System/User/registration/after
 *  ['id' => <i>user_id</i>]
 *
 *  System/User/registration/confirmation/before
 *  ['reg_key' => <i>reg_key</i>]
 *
 *  System/User/registration/confirmation/after
 *  ['id' => <i>user_id</i>]
 *
 *  System/User/del/before
 *  ['id' => <i>user_id</i>]
 *
 *  System/User/del/after
 *  ['id' => <i>user_id</i>]
 *
 *  System/User/get_contacts
 *  [
 *    'id'       => <i>user_id</i>,
 *    'contacts' => <i>&$contacts</i> //Array of user id
 *  ]
 *
 * @property int    $id
 * @property string $login
 * @property string $login_hash    sha224 hash
 * @property string $username
 * @property string $password_hash sha512 hash
 * @property string $email
 * @property string $email_hash    sha224 hash
 * @property string $language
 * @property string $timezone
 * @property int    $reg_date      unix timestamp
 * @property string $reg_ip        hex value, obtained by function ip2hex()
 * @property string $reg_key       random md5 hash, generated during registration
 * @property int    $status        '-1' - not activated (for example after registration), 0 - inactive, 1 - active
 * @property int    $block_until   unix timestamp
 * @property string $avatar
 *
 * @method static $this instance($check = false)
 */
class User {
	use
		Accessor,
		Singleton,
		User_data,
		User_group,
		User_management,
		User_permission,
		User_profile;
	/**
	 * Id of system guest user
	 */
	const GUEST_ID = 1;
	/**
	 * Id of first, primary system administrator
	 */
	const ROOT_ID = 2;
	/**
	 * Id of system group for administrators
	 */
	const ADMIN_GROUP_ID = 1;
	/**
	 * Id of system group for users
	 */
	const USER_GROUP_ID = 2;
	/**
	 * Status of active user
	 */
	const STATUS_ACTIVE = 1;
	/**
	 * Status of inactive user
	 */
	const STATUS_INACTIVE = 0;
	/**
	 * Status of not activated user
	 */
	const STATUS_NOT_ACTIVATED = -1;
	/**
	 * @var Cache\Prefix
	 */
	protected $cache;
	/**
	 * Whether to use memory cache (locally, inside object, may require a lot of memory if working with many users together)
	 * @var bool
	 */
	protected $memory_cache = true;
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('System')->db('users');
	}
	protected function construct () {
		$this->cache = Cache::prefix('users');
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
	 * @param string $login_hash Hash (sha224) from login (hash from lowercase string)
	 *
	 * @return int Number of attempts
	 */
	function get_sign_in_attempts_count ($login_hash) {
		if (!preg_match('/^[0-9a-z]{56}$/', $login_hash)) {
			return false;
		}
		$time = time();
		return $this->db()->qfs(
			"SELECT COUNT(`expire`)
			FROM `[prefix]sign_ins`
			WHERE
				`expire` > $time AND
				(
					`login_hash`	= '%s' OR
					`ip`			= '%s'
				)",
			$login_hash,
			ip2hex(Request::instance()->ip)
		);
	}
	/**
	 * Process sign in result (is used by system)
	 *
	 * @param bool   $success
	 * @param string $login_hash Hash (sha224) from login (hash from lowercase string)
	 */
	function sign_in_result ($success, $login_hash) {
		if (!preg_match('/^[0-9a-z]{56}$/', $login_hash)) {
			return;
		}
		$ip   = ip2hex(Request::instance()->ip);
		$time = time();
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
			$Config = Config::instance();
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
				$this->db_prime()->q("DELETE FROM `[prefix]sign_ins` WHERE `expire` < $time");
			}
		}
	}
	/**
	 * Get data item of current user
	 *
	 * @param string|string[] $item
	 *
	 * @return false|int|mixed[]|string|User\Properties If <i>$item</i> is integer - cs\User\Properties object will be returned
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
	 * @param array|int|string $item Item-value array may be specified for setting several items at once
	 * @param mixed|null       $value
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
	 * Disable memory cache
	 *
	 * Memory cache stores users data inside User class in order to get data faster next time.
	 * But in case of working with large amount of users this cache can be too large. Disabling will cause some performance drop, but save a lot of RAM.
	 */
	function disable_memory_cache () {
		$this->memory_cache = false;
		$this->data         = [];
		$this->permissions  = [];
	}
}
