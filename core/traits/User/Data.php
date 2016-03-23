<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\User;
use
	cs\Config,
	cs\Event,
	cs\Language,
	cs\Session,
	cs\User,
	h;

/**
 * Trait that contains all methods from <i>>cs\User</i> for working with user data
 *
 * @property \cs\Cache\Prefix $cache
 * @property int              $id
 *
 * @method \cs\DB\_Abstract db()
 * @method \cs\DB\_Abstract db_prime()
 * @method false|int[]        get_groups(false|int $user)
 */
trait Data {
	/**
	 * Copy of columns list of users table for internal needs without Cache usage
	 * @var array
	 */
	protected $users_columns = [];
	/**
	 * Local cache of users data
	 * @var array
	 */
	protected $data = [];
	/**
	 * Changed users data, at the finish, data in db must be replaced by this data
	 * @var array
	 */
	protected $data_set = [];
	/**
	 * Whether to use memory cache (locally, inside object, may require a lot of memory if working with many users together)
	 * @var bool
	 */
	protected $memory_cache = true;
	protected function initialize_data () {
		$this->users_columns = $this->cache->get(
			'columns',
			function () {
				return $this->db()->columns('[prefix]users');
			}
		);
	}
	/**
	 * Get data item of specified user
	 *
	 * @param string|string[] $item
	 * @param false|int       $user If not specified - current user assumed
	 *
	 * @return false|int|mixed[]|string|Properties If <i>$item</i> is integer - cs\User\Properties object will be returned
	 */
	function get ($item, $user = false) {
		if (is_scalar($item) && ctype_digit((string)$item)) {
			return new Properties($item);
		}
		return $this->get_internal($item, $user);
	}
	/**
	 * Get data item of specified user
	 *
	 * @param string|string[] $item
	 * @param false|int       $user If not specified - current user assumed
	 *
	 * @return false|int|string|mixed[]
	 */
	protected function get_internal ($item, $user = false) {
		$user = (int)$user ?: $this->id;
		if (!$user) {
			return false;
		}
		if (isset($this->data[$user])) {
			$data = $this->data[$user];
		} else {
			$data = $this->cache->get(
				$user,
				function () use ($user) {
					return $this->db()->qf(
						"SELECT *
						FROM `[prefix]users`
						WHERE `id` = $user
						LIMIT 1"
					) ?: false;
				}
			);
			if (!$data) {
				return false;
			} elseif ($this->memory_cache || $user = User::GUEST_ID) {
				$this->data[$user] = $data;
			}
		}
		/**
		 * If get an array of values
		 */
		if (is_array($item)) {
			$result = [];
			/**
			 * Trying to get value from the local cache, or make up an array of missing values
			 */
			foreach ($item as $i) {
				if (in_array($i, $this->users_columns)) {
					$result[$i] = $data[$i];
				}
			}
			return $result;
		}
		return in_array($item, $this->users_columns) ? $data[$item] : false;
	}
	/**
	 * Set data item of specified user
	 *
	 * @param array|string    $item Item-value array may be specified for setting several items at once
	 * @param int|null|string $value
	 * @param false|int       $user If not specified - current user assumed
	 *
	 * @return bool
	 */
	function set ($item, $value = null, $user = false) {
		$result = $this->set_internal($item, $value, $user);
		$this->persist_data();
		return $result;
	}
	/**
	 * Set data item of specified user
	 *
	 * @param array|string    $item Item-value array may be specified for setting several items at once
	 * @param int|null|string $value
	 * @param false|int       $user If not specified - current user assumed
	 *
	 * @return bool
	 */
	protected function set_internal ($item, $value = null, $user = false) {
		$user = (int)$user ?: $this->id;
		if (!$user) {
			return false;
		}
		if (is_array($item)) {
			$result = true;
			foreach ($item as $i => $v) {
				$result = $result && $this->set($i, $v, $user);
			}
			return $result;
		}
		if (!$this->set_internal_allowed($user, $item, $value)) {
			return false;
		}
		if ($item === 'language') {
			$value = $value ? Language::instance()->get('clanguage', $value) : '';
		} elseif ($item === 'timezone') {
			$value = in_array($value, get_timezones_list(), true) ? $value : '';
		} elseif ($item == 'avatar') {
			if (
				strpos($value, 'http://') !== 0 &&
				strpos($value, 'https://') !== 0
			) {
				$value = '';
			} else {
				$old_value = $this->get($item, $user);
				if ($value !== $old_value) {
					$Event = Event::instance();
					$Event->fire(
						'System/upload_files/del_tag',
						[
							'url' => $old_value,
							'tag' => "users/$user/avatar"
						]
					);
					$Event->fire(
						'System/upload_files/add_tag',
						[
							'url' => $value,
							'tag' => "users/$user/avatar"
						]
					);
				}
			}
		}
		$this->data_set[$user][$item] = $value;
		if (in_array($item, ['login', 'email'], true)) {
			$old_value                            = $this->get($item.'_hash', $user);
			$this->data_set[$user][$item.'_hash'] = hash('sha224', $value);
			unset($this->cache->$old_value);
		} elseif ($item === 'password_hash' || ($item === 'status' && $value == 0)) {
			Session::instance()->del_all($user);
		}
		return true;
	}
	/**
	 * Check whether setting specified item to specified value for specified user is allowed
	 *
	 * @param int    $user
	 * @param string $item
	 * @param string $value
	 *
	 * @return bool
	 */
	protected function set_internal_allowed ($user, $item, $value) {
		if (
			$user === User::GUEST_ID ||
			$item === 'id' ||
			!in_array($item, $this->users_columns, true)
		) {
			return false;
		}
		if (in_array($item, ['login', 'email'], true)) {
			$value = mb_strtolower($value);
			if (
				$item === 'email' &&
				!filter_var($value, FILTER_VALIDATE_EMAIL) &&
				!in_array(User::BOT_GROUP_ID, $this->get_groups($user))
			) {
				return false;
			}
			if ($value === $this->get($item, $user)) {
				return true;
			}
			return !$this->get_id(hash('sha224', $value));
		}
		return true;
	}
	/**
	 * Getting additional data item(s) of specified user
	 *
	 * @param string|string[] $item
	 * @param false|int       $user If not specified - current user assumed
	 *
	 * @return false|string|mixed[]
	 */
	function get_data ($item, $user = false) {
		$user = (int)$user ?: $this->id;
		if (!$user || !$item || $user == User::GUEST_ID) {
			return false;
		}
		$Cache = $this->cache;
		$data  = $Cache->{"data/$user"} ?: [];
		if (is_array($item)) {
			$result = [];
			$absent = [];
			foreach ($item as $i) {
				if (isset($data[$i])) {
					$result[$i] = $data[$i];
				} else {
					$absent[] = $i;
				}
			}
			if ($absent) {
				$absent = implode(
					',',
					$this->db()->s($absent)
				);
				$absent = array_column(
					$this->db()->qfa(
						[
							"SELECT `item`, `value`
							FROM `[prefix]users_data`
							WHERE
								`id`	= '$user' AND
								`item`	IN($absent)"
						]
					),
					'value',
					'item'
				);
				foreach ($absent as &$a) {
					$a = _json_decode($a);
					if ($a === null) {
						$a = false;
					}
				}
				unset($a);
				$result += $absent;
				$data += $absent;
				$Cache->{"data/$user"} = $data;
			}
			return $result;
		}
		if ($data === false || !isset($data[$item])) {
			if (!is_array($data)) {
				$data = [];
			}
			$data[$item] = _json_decode(
				$this->db()->qfs(
					[
						"SELECT `value`
						FROM `[prefix]users_data`
						WHERE
							`id`	= '$user' AND
							`item`	= '%s'",
						$item
					]
				)
			);
			if ($data[$item] === null) {
				$data[$item] = false;
			}
			$Cache->{"data/$user"} = $data;
		}
		return $data[$item];
	}
	/**
	 * Setting additional data item(s) of specified user
	 *
	 * @param array|string $item Item-value array may be specified for setting several items at once
	 * @param mixed|null   $value
	 * @param false|int    $user If not specified - current user assumed
	 *
	 * @return bool
	 */
	function set_data ($item, $value = null, $user = false) {
		$user = (int)$user ?: $this->id;
		if (!$user || !$item || $user == User::GUEST_ID) {
			return false;
		}
		if (is_array($item)) {
			$params = [];
			foreach ($item as $i => $v) {
				$params[] = [
					$i,
					_json_encode($v)
				];
			}
			unset($i, $v);
			$result = $this->db_prime()->insert(
				"REPLACE INTO `[prefix]users_data`
					(
						`id`,
						`item`,
						`value`
					) VALUES (
						$user,
						'%s',
						'%s'
					)",
				$params
			);
		} else {
			$result = $this->db_prime()->q(
				"REPLACE INTO `[prefix]users_data`
					(
						`id`,
						`item`,
						`value`
					) VALUES (
						'$user',
						'%s',
						'%s'
					)",
				$item,
				_json_encode($value)
			);
		}
		unset($this->cache->{"data/$user"});
		return (bool)$result;
	}
	/**
	 * Deletion of additional data item(s) of specified user
	 *
	 * @param string|string[] $item
	 * @param false|int       $user If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_data ($item, $user = false) {
		$user = (int)$user ?: $this->id;
		if (!$user || !$item || $user == User::GUEST_ID) {
			return false;
		}
		$item   = implode(
			',',
			$this->db_prime()->s((array)$item)
		);
		$result = $this->db_prime()->q(
			"DELETE FROM `[prefix]users_data`
			WHERE
				`id`	= '$user' AND
				`item`	IN($item)"
		);
		unset($this->cache->{"data/$user"});
		return (bool)$result;
	}
	/**
	 * Get user id by login or email hash (sha224) (hash from lowercase string)
	 *
	 * @param  string $login_hash Login or email hash
	 *
	 * @return false|int User id if found and not guest, otherwise - boolean <i>false</i>
	 */
	function get_id ($login_hash) {
		if (!preg_match('/^[0-9a-z]{56}$/', $login_hash)) {
			return false;
		}
		$id = $this->cache->get(
			$login_hash,
			function () use ($login_hash) {
				return $this->db()->qfs(
					[
						"SELECT `id`
						FROM `[prefix]users`
						WHERE
							`login_hash`	= '%s' OR
							`email_hash`	= '%s'
						LIMIT 1",
						$login_hash,
						$login_hash
					]
				) ?: false;
			}
		);
		return $id && $id != User::GUEST_ID ? $id : false;
	}
	/**
	 * Get user avatar, if no one present - uses Gravatar
	 *
	 * @param int|null  $size Avatar size, if not specified or resizing is not possible - original image is used
	 * @param false|int $user If not specified - current user assumed
	 *
	 * @return string
	 */
	function avatar ($size = null, $user = false) {
		$user   = (int)$user ?: $this->id;
		$avatar = $this->get('avatar', $user);
		$Config = Config::instance();
		if (!$avatar && $this->id != User::GUEST_ID && $Config->core['gravatar_support']) {
			$email_hash     = md5($this->get('email', $user));
			$default_avatar = urlencode($Config->core_url().'/includes/img/guest.svg');
			$avatar         = "https://www.gravatar.com/avatar/$email_hash?d=mm&s=$size&d=$default_avatar";
		}
		if (!$avatar) {
			$avatar = '/includes/img/guest.svg';
		}
		return h::prepare_url($avatar, true);
	}
	/**
	 * Get user name or login or email, depending on existing information
	 *
	 * @param false|int $user If not specified - current user assumed
	 *
	 * @return string
	 */
	function username ($user = false) {
		$user = (int)$user ?: $this->id;
		if ($user === User::GUEST_ID) {
			return Language::instance()->system_profile_guest;
		}
		$username = $this->get('username', $user);
		if (!$username) {
			$username = $this->get('login', $user);
		}
		if (!$username) {
			$username = $this->get('email', $user);
		}
		return $username;
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
	}
	/**
	 * Returns array of users columns, available for getting of data
	 *
	 * @return array
	 */
	function get_users_columns () {
		return $this->users_columns;
	}
	/**
	 * Saving changes of cache and users data
	 */
	protected function persist_data () {
		foreach ($this->data_set as $user => $data_set) {
			$update = [];
			foreach ($data_set as $item => $value) {
				if ($item != 'id' && in_array($item, $this->users_columns)) {
					$value    = xap($value, false);
					$update[] = "`$item` = ".$this->db_prime()->s($value);
				}
			}
			if ($update) {
				$update = implode(', ', $update);
				$this->db_prime()->q(
					"UPDATE `[prefix]users`
					SET $update
					WHERE `id` = '$user'"
				);
				unset($this->data[$user], $this->cache->$user);
			}
		}
		$this->data_set = [];
	}
}
