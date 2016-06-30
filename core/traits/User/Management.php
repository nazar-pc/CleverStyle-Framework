<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\User;
use
	cs\Config,
	cs\Core,
	cs\Event,
	cs\Request,
	cs\Session,
	cs\User;

/**
 * Trait that contains all methods from <i>>cs\User</i> for working with user management (creation, modification, deletion)
 *
 * @property int              $id
 * @property \cs\Cache\Prefix $cache
 * @property string           $ip
 *
 * @method \cs\DB\_Abstract db()
 * @method \cs\DB\_Abstract db_prime()
 * @method false|int        get_id(string $login_hash)
 * @method bool             set_groups(int[] $groups, false|int $user = false)
 * @method false|string     get(array|string $item, false|int $user = false)
 * @method bool             set(array|string $item, mixed|null $value = null, false|int $user = false)
 * @method bool             del_permissions_all(false|int $user = false)
 */
trait Management {
	/**
	 * User id after registration
	 * @var int
	 */
	protected $reg_id = 0;
	/**
	 * Search keyword in login, username and email
	 *
	 * @param string $search_phrase
	 *
	 * @return false|int[]
	 */
	function search_users ($search_phrase) {
		$search_phrase = trim($search_phrase, "%\n");
		$found_users   = $this->db()->qfas(
			"SELECT `id`
			FROM `[prefix]users`
			WHERE
				(
					`login`		LIKE '%1\$s' OR
					`username`	LIKE '%1\$s' OR
					`email`		LIKE '%1\$s'
				) AND
				`status` != '%2\$s'",
			$search_phrase,
			User::STATUS_NOT_ACTIVATED
		);
		if (!$found_users) {
			return false;
		}
		return _int($found_users);
	}
	/**
	 * User registration
	 *
	 * @param string $email
	 * @param bool   $confirmation If <b>true</b> - default system option is used, if <b>false</b> - registration will be finished without necessity of
	 *                             confirmation, independently from default system option (is used for manual registration).
	 * @param bool   $auto_sign_in If <b>false</b> - no auto sign in, if <b>true</b> - according to system configuration
	 *
	 * @return array|false|string  <b>exists</b> - if user with such email is already registered<br>
	 *                             <b>error</b> - if error occurred<br>
	 *                             <b>false</b> - if email is incorrect<br>
	 *                             <b>[<br>
	 *                             &nbsp;&nbsp;&nbsp;&nbsp;'reg_key'     => *,</b> //Registration confirmation key, or <b>true</b> if confirmation is not
	 *                             required<br>
	 *                             &nbsp;&nbsp;&nbsp;&nbsp;<b>'password' => *,</b> //Automatically generated password (empty if confirmation is needed and
	 *                             will be set during registration confirmation)<br>
	 *                             &nbsp;&nbsp;&nbsp;&nbsp;<b>'id'       => *</b>  //Id of registered user in DB<br>
	 *                             <b>]</b>
	 */
	function registration ($email, $confirmation = true, $auto_sign_in = true) {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		}
		$email      = mb_strtolower($email);
		$email_hash = hash('sha224', $email);
		$login      = strstr($email, '@', true);
		$login_hash = hash('sha224', $login);
		if (
			$this->get_id($login_hash) !== false ||
			(
				$login &&
				in_array($login, file_get_json(MODULES.'/System/index.json')['profile'])
			)
		) {
			$login      = $email;
			$login_hash = $email_hash;
		}
		if ($this->db_prime()->qf(
			"SELECT `id`
			FROM `[prefix]users`
			WHERE `email_hash` = '%s'
			LIMIT 1",
			$email_hash
		)
		) {
			return 'exists';
		}
		$this->delete_unconfirmed_users();
		if (!Event::instance()->fire(
			'System/User/registration/before',
			[
				'email' => $email
			]
		)
		) {
			return false;
		}
		$Config       = Config::instance();
		$confirmation = $confirmation && $Config->core['require_registration_confirmation'];
		$reg_key      = md5(random_bytes(1000));
		if ($this->db_prime()->q(
			"INSERT INTO `[prefix]users` (
				`login`,
				`login_hash`,
				`email`,
				`email_hash`,
				`reg_date`,
				`reg_ip`,
				`reg_key`,
				`status`
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
			$login,
			$login_hash,
			$email,
			$email_hash,
			time(),
			ip2hex(Request::instance()->ip),
			$reg_key,
			!$confirmation ? 1 : -1
		)
		) {
			$this->reg_id = (int)$this->db_prime()->id();
			$password     = '';
			if (!$confirmation) {
				$password = password_generate($Config->core['password_min_length'], $Config->core['password_min_strength']);
				$this->set_password($password, $this->reg_id);
				$this->set_groups([User::USER_GROUP_ID], $this->reg_id);
				if ($auto_sign_in && $Config->core['auto_sign_in_after_registration']) {
					Session::instance()->add($this->reg_id);
				}
			}
			if (!Event::instance()->fire(
				'System/User/registration/after',
				[
					'id' => $this->reg_id
				]
			)
			) {
				$this->registration_cancel();
				return false;
			}
			if (!$confirmation) {
				$this->set_groups([User::USER_GROUP_ID], $this->reg_id);
			}
			unset($this->cache->$login_hash);
			return [
				'reg_key'  => !$confirmation ? true : $reg_key,
				'password' => $password,
				'id'       => $this->reg_id
			];
		} else {
			return 'error';
		}
	}
	/**
	 * Confirmation of registration process
	 *
	 * @param string $reg_key
	 *
	 * @return array|false ['id' => <i>id</i>, 'email' => <i>email</i>, 'password' => <i>password</i>] or <b>false</b> on failure
	 */
	function registration_confirmation ($reg_key) {
		if (!is_md5($reg_key)) {
			return false;
		}
		if (!Event::instance()->fire(
			'System/User/registration/confirmation/before',
			[
				'reg_key' => $reg_key
			]
		)
		) {
			$this->registration_cancel();
			return false;
		}
		$this->delete_unconfirmed_users();
		$data = $this->db_prime()->qf(
			"SELECT
				`id`,
				`login_hash`,
				`email`
			FROM `[prefix]users`
			WHERE
				`reg_key`	= '%s' AND
				`status`	= '%s'
			LIMIT 1",
			$reg_key,
			User::STATUS_NOT_ACTIVATED
		);
		if (!$data) {
			return false;
		}
		$this->reg_id = (int)$data['id'];
		$Config       = Config::instance();
		$password     = '';
		if (!$this->get('password_hash', $this->reg_id)) {
			$password = password_generate($Config->core['password_min_length'], $Config->core['password_min_strength']);
			$this->set_password($password, $this->reg_id);
		}
		$this->set('status', User::STATUS_ACTIVE, $this->reg_id);
		$this->set_groups([User::USER_GROUP_ID], $this->reg_id);
		Session::instance()->add($this->reg_id);
		if (!Event::instance()->fire(
			'System/User/registration/confirmation/after',
			[
				'id' => $this->reg_id
			]
		)
		) {
			$this->registration_cancel();
			return false;
		}
		unset($this->cache->{$data['login_hash']});
		return [
			'id'       => $this->reg_id,
			'email'    => $data['email'],
			'password' => $password
		];
	}
	/**
	 * Canceling of bad/failed registration
	 */
	function registration_cancel () {
		if ($this->reg_id) {
			Session::instance()->add(User::GUEST_ID);
			$this->del_user($this->reg_id);
			$this->reg_id = 0;
		}
	}
	/**
	 * Checks for unconfirmed registrations and deletes expired
	 */
	protected function delete_unconfirmed_users () {
		$reg_date = time() - Config::instance()->core['registration_confirmation_time'] * 86400;    //1 day = 86400 seconds
		$ids      = $this->db_prime()->qfas(
			"SELECT `id`
			FROM `[prefix]users`
			WHERE
				`status`	= '%s' AND
				`reg_date`	< $reg_date",
			User::STATUS_NOT_ACTIVATED
		);
		if ($ids) {
			$this->del_user($ids);
		}
	}
	/**
	 * Proper password setting without any need to deal with low-level implementation
	 *
	 * @param string    $new_password
	 * @param false|int $user
	 * @param bool      $already_prepared If true - assumed that `sha512(sha512(password) + public_key)` was applied to password
	 *
	 * @return bool
	 */
	function set_password ($new_password, $user = false, $already_prepared = false) {
		$public_key = Core::instance()->public_key;
		if (!$already_prepared) {
			$new_password = hash('sha512', hash('sha512', $new_password).$public_key);
		}
		/**
		 * Do not allow to set password to empty
		 */
		if ($new_password == hash('sha512', hash('sha512', '').$public_key)) {
			return false;
		}
		return $this->set('password_hash', password_hash($new_password, PASSWORD_DEFAULT), $user);
	}
	/**
	 * Proper password validation without any need to deal with low-level implementation
	 *
	 * @param string    $password
	 * @param false|int $user
	 * @param bool      $already_prepared If true - assumed that `sha512(sha512(password) + public_key)` was applied to password
	 *
	 * @return bool
	 */
	function validate_password ($password, $user = false, $already_prepared = false) {
		if (!$already_prepared) {
			$password = hash('sha512', hash('sha512', $password).Core::instance()->public_key);
		}
		$user          = (int)$user ?: $this->id;
		$password_hash = $this->get('password_hash', $user);
		if (!password_verify($password, $password_hash)) {
			return false;
		}
		/**
		 * Rehash password if needed
		 */
		if (password_needs_rehash($password_hash, PASSWORD_DEFAULT)) {
			$current_user_id = $this->id;
			$this->set_password($password, $user, true);
			if ($current_user_id == $user) {
				Session::instance()->add($current_user_id);
			}
		}
		return true;
	}
	/**
	 * Restoring of password
	 *
	 * @param int $user
	 *
	 * @return false|string Key for confirmation or <b>false</b> on failure
	 */
	function restore_password ($user) {
		if ($user && $user != User::GUEST_ID) {
			$reg_key = md5(random_bytes(1000));
			if ($this->set('reg_key', $reg_key, $user)) {
				$restore_until = time() + Config::instance()->core['registration_confirmation_time'] * 86400;
				if ($this->set_data('restore_until', $restore_until, $user)) {
					return $reg_key;
				}
			}
		}
		return false;
	}
	/**
	 * Confirmation of password restoring process
	 *
	 * @param string $key
	 *
	 * @return array|false ['id' => <i>id</i>, 'password' => <i>password</i>] or <b>false</b> on failure
	 */
	function restore_password_confirmation ($key) {
		if (!is_md5($key)) {
			return false;
		}
		$id = (int)$this->db_prime()->qfs(
			"SELECT `id`
			FROM `[prefix]users`
			WHERE
				`reg_key`	= '%s' AND
				`status`	= '%s'
			LIMIT 1",
			$key,
			User::STATUS_ACTIVE
		);
		if (!$id) {
			return false;
		}
		$restore_until = $this->get_data('restore_until', $id);
		$this->del_data('restore_until', $id);
		if ($restore_until < time()) {
			return false;
		}
		$Config   = Config::instance();
		$password = password_generate($Config->core['password_min_length'], $Config->core['password_min_strength']);
		$this->set_password($password, $id);
		Session::instance()->add($id);
		return [
			'id'       => $id,
			'password' => $password
		];
	}
	/**
	 * Delete specified user or array of users
	 *
	 * @param int|int[] $user User id or array of users ids
	 */
	function del_user ($user) {
		$this->disable_memory_cache();
		if (is_array($user)) {
			foreach ($user as $id) {
				$this->del_user($id);
			}
			return;
		}
		$user = (int)$user;
		if (in_array($user, [0, User::GUEST_ID, User::ROOT_ID]) || !$this->get('id', $user)) {
			return;
		}
		Event::instance()->fire(
			'System/User/del/before',
			[
				'id' => $user
			]
		);
		$this->set_groups([], $user);
		$this->del_permissions_all($user);
		$Cache      = $this->cache;
		$login_hash = hash('sha224', $this->get('login', $user));
		$this->db_prime()->q(
			"DELETE FROM `[prefix]users`
			WHERE `id` = $user"
		);
		unset(
			$Cache->$login_hash,
			$Cache->$user
		);
		Event::instance()->fire(
			'System/User/del/after',
			[
				'id' => $user
			]
		);
	}
	/**
	 * Returns array of user id, that are associated as contacts
	 *
	 * @deprecated
	 * @todo Remove in 5.x
	 *
	 * @param false|int $user If not specified - current user assumed
	 *
	 * @return int[] Array of user id
	 */
	function get_contacts ($user = false) {
		$user = (int)$user ?: $this->id;
		if (!$user || $user == User::GUEST_ID) {
			return [];
		}
		$contacts = [];
		Event::instance()->fire(
			'System/User/get_contacts',
			[
				'id'       => $user,
				'contacts' => &$contacts
			]
		);
		return array_unique($contacts);
	}
}
