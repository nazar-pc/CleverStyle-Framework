<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
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
use			cs\Cache\Prefix,
			cs\User\Properties,
			cs\DB\Accessor,
			cs\Permission\Any,
			h;
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
 */
class User {
	use	Accessor,
		Singleton,
		Any;
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

	protected	$current				= [
					'session'		=> false,
					'is'			=> [
						'admin'			=> false,
						'user'			=> false,
						'bot'			=> false,
						'guest'			=> false,
						'system'		=> false
					]
				],
				$id						= false,	//id of current user
				$update_cache			= [],		//Do we need to update users cache
				$data					= [],		//Local cache of users data
				$data_set				= [],		//Changed users data, at the finish, data in db must be replaced by this data
				$init					= false,	//Current state of initialization
				$reg_id					= 0,		//User id after registration
				$users_columns			= [],		//Copy of columns list of users table for internal needs without Cache usage
				$permissions			= [],		//Permissions cache
				$memory_cache			= true;
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
		/**
		 * Detecting of current user
		 * Last part in page path - key
		 */
		$rc	= $Config->route;
		if (
			$this->user_agent == 'CleverStyle CMS' &&
			(
				($this->get_sign_in_attempts_count(hash('sha224', 0)) < $Config->core['sign_in_attempts_block_count']) ||
				$Config->core['sign_in_attempts_block_count'] == 0
			) &&
			count($rc) > 1 &&
			(
				$key_data = Key::instance()->get(
					$Config->module('System')->db('keys'),
					$key = array_slice($rc, -1)[0],
					true
				)
			) &&
			is_array($key_data)
		) {
			if ($this->current['is']['system'] = ($key_data['url'] == $Config->server['host'].'/'.$Config->server['raw_relative_address'])) {
				$this->current['is']['admin'] = true;
				interface_off();
				$_POST['data'] = _json_decode($_POST['data']);
				Trigger::instance()->run('System/User/construct/after');
				return;
			} else {
				$this->current['is']['guest'] = true;
				/**
				 * Simulate a bad sign in to block access
				 */
				$this->sign_in_result(false, hash('sha224', 'system'));
				unset($_POST['data']);
				sleep(1);
			}
		}
		unset($key_data, $key, $rc);
		/**
		 * If session exists
		 */
		if (_getcookie('session')) {
			$this->id = $this->get_session_user();
		/**
		 * Try to detect bot, not necessary for API request
		 */
		} elseif (!API) {
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
				if (($this->id = $Cache->$bot_hash) === false) {
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
						$last_session					= $this->get_data('last_session');
						$id								= $this->id;
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
			if (!API) {
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
	/**
	 * Updates information about who is user accessed by methods ::guest() ::bot() ::user() admin() ::system()
	 */
	protected function update_user_is () {
		$this->current['is']['guest']	= false;
		$this->current['is']['bot']		= false;
		$this->current['is']['user']	= false;
		$this->current['is']['admin']	= false;
		$this->current['is']['system']	= false;
		if ($this->id == self::GUEST_ID) {
			$this->current['is']['guest'] = true;
		} else {
			/**
			 * Checking of user type
			 */
			$groups = $this->get_groups() ?: [];
			if (in_array(self::ADMIN_GROUP_ID, $groups)) {
				$this->current['is']['admin']	= Config::instance()->can_be_admin;
				$this->current['is']['user']	= true;
			} elseif (in_array(self::USER_GROUP_ID, $groups)) {
				$this->current['is']['user']	= true;
			} elseif (in_array(self::BOT_GROUP_ID, $groups)) {
				$this->current['is']['guest']	= true;
				$this->current['is']['bot']		= true;
			}
		}
	}
	/**
	 * Get data item of specified user
	 *
	 * @param string|string[]					$item
	 * @param bool|int 							$user	If not specified - current user assumed
	 *
	 * @return bool|string|mixed[]|Properties			If <i>$item</i> is integer - cs\User\Properties object will be returned
	 */
	function get ($item, $user = false) {
		if (is_scalar($item) && preg_match('/^[0-9]+$/', $item)) {
			return new Properties($item);
		}
		switch ($item) {
			case 'user_agent':
				return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
			case 'ip':
				return $_SERVER['REMOTE_ADDR'];
			case 'forwarded_for':
				if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					return false;
				}
				$tmp	= explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				return preg_replace('/[^a-f0-9\.:]/i', '', array_pop($tmp));
			case 'client_ip':
				return isset($_SERVER['HTTP_CLIENT_IP']) ? preg_replace('/[^a-f0-9\.:]/i', '', $_SERVER['HTTP_CLIENT_IP']) : false;
		}
		$result	= $this->get_internal($item, $user);
		if (!$this->memory_cache) {
			$this->__finish();
		}
		return $result;
	}
	/**
	 * Get data item of specified user
	 *
	 * @param string|string[]		$item
	 * @param bool|int 				$user		If not specified - current user assumed
	 * @param bool					$cache_only
	 *
	 * @return bool|string|mixed[]
	 */
	protected function get_internal ($item, $user = false, $cache_only = false) {
		$user = (int)($user ?: $this->id);
		if (!$user) {
			return false;
		}
		/**
		 * Reference for simpler usage
		 */
		$data = &$this->data[$user];
		/**
		 * If get an array of values
		 */
		if (is_array($item)) {
			$result = $new_items = [];
			/**
			 * Trying to get value from the local cache, or make up an array of missing values
			 */
			foreach ($item as $i) {
				if (in_array($i, $this->users_columns)) {
					if (($res = $this->get_internal($i, $user, true)) !== false) {
						$result[$i] = $res;
					} else {
						$new_items[] = $i;
					}
				}
			}
			if (empty($new_items)) {
				return $result;
			}
			/**
			 * If there are missing values - get them from the database
			 */
			$new_items	= '`'.implode('`, `', $new_items).'`';
			$res		= $this->db()->qf(
				"SELECT $new_items
				FROM `[prefix]users`
				WHERE `id` = '$user'
				LIMIT 1"
			);
			unset($new_items);
			if (is_array($res)) {
				$this->update_cache[$user]	= true;
				$data						= array_merge($res, $data ?: []);
				$result						= array_merge($result, $res);
				/**
				 * Sorting the resulting array in the same manner as the input array
				 */
				$res = [];
				foreach ($item as $i) {
					$res[$i] = &$result[$i];
				}
				return $res;
			} else {
				return false;
			}
		/**
		 * If get one value
		 */
		} elseif (in_array($item, $this->users_columns)) {
			/**
			 * Pointer to the beginning of getting the data
			 */
			get_data:
			/**
			 * If data in local cache - return them
			 */
			if (isset($data[$item])) {
				return $data[$item];
			/**
			 * Try to get data from the cache
			 */
			} elseif (!isset($new_data) && ($new_data = $this->cache->$user) !== false && is_array($new_data)) {
				/**
				 * Update the local cache
				 */
				if (is_array($new_data)) {
					$data = array_merge($new_data, $data ?: []);
				}
				/**
				 * New attempt of getting the data
				 */
				goto get_data;
			} elseif (!$cache_only) {
				$new_data = $this->db()->qfs(
					"SELECT `$item`
					FROM `[prefix]users`
					WHERE `id` = '$user'
					LIMIT 1"
				);
				if ($new_data !== false) {
					$this->update_cache[$user]	= true;
					return $data[$item] = $new_data;
				}
			}
		}
		return false;
	}
	/**
	 * Set data item of specified user
	 *
	 * @param array|string	$item	Item-value array may be specified for setting several items at once
	 * @param mixed|null	$value
	 * @param bool|int		$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function set ($item, $value = null, $user = false) {
		$result	= $this->set_internal($item, $value, $user);
		if (!$this->memory_cache) {
			$this->__finish();
		}
		return $result;
	}
	/**
	 * Set data item of specified user
	 *
	 * @param array|string	$item	Item-value array may be specified for setting several items at once
	 * @param mixed|null	$value
	 * @param bool|int		$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	protected function set_internal ($item, $value = null, $user = false) {
		$user = (int)($user ?: $this->id);
		if (!$user) {
			return false;
		}
		if (is_array($item)) {
			foreach ($item as $i => &$v) {
				if (in_array($i, $this->users_columns) && $i != 'id') {
					$this->set($i, $v, $user);
				}
			}
		} elseif (in_array($item, $this->users_columns) && $item != 'id') {
			if (in_array($item, ['login_hash', 'email_hash'])) {
				return true;
			}
			if ($item == 'login' || $item == 'email') {
				$value	= mb_strtolower($value);
				if ($this->get_id(hash('sha224', $value)) !== false) {
					return false;
				}
			} elseif ($item == 'language') {
				$L	= Language::instance();
				if ($user == $this->id) {
					$L->change($value);
					$value	= $value ? $L->clanguage : '';
				}
			} elseif ($item == 'avatar') {
				if (
					$value &&
					strpos($value, 'http') === false
				) {
					$value	= '';
				}
			}
			$this->update_cache[$user]		= true;
			$this->data[$user][$item]		= $value;
			$this->data_set[$user][$item]	= $value;
			if ($item == 'login' || $item == 'email') {
				$this->data[$user][$item.'_hash']		= hash('sha224', $value);
				$this->data_set[$user][$item.'_hash']	= hash('sha224', $value);
				unset($this->cache->{hash('sha224', $this->$item)});
			} elseif ($item == 'password_hash' || ($item == 'status' && $value == 0)) {
				$this->del_all_sessions($user);
			}
		}
		return true;
	}
	/**
	 * Get data item of current user
	 *
	 * @param string|string[]		$item
	 *
	 * @return array|bool|string
	 */
	function __get ($item) {
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
		return $this->set($item, $value);
	}
	/**
	 * Getting additional data item(s) of specified user
	 *
	 * @param string|string[]		$item
	 * @param bool|int				$user	If not specified - current user assumed
	 *
	 * @return bool|string|mixed[]
	 */
	function get_data ($item, $user = false) {
		$user	= (int)($user ?: $this->id);
		if (!$user || $user == self::GUEST_ID || !$item) {
			return false;
		}
		$Cache	= $this->cache;
		$data	= $Cache->{"data/$user"} ?: [];
		if (is_array($item)) {
			$result	= [];
			$absent	= [];
			foreach ($item as $i) {
				if (isset($data[$i])) {
					$result[$i]	= $data[$i];
				} else {
					$absent[]	= $i;
				}
			}
			unset($i);
			if ($absent) {
				$absent					= implode(
					',',
					$this->db()->s($absent)
				);
				$absent					= array_column(
					$this->db()->qfa([
						"SELECT `item`, `value`
						FROM `[prefix]users_data`
						WHERE
							`id`	= '$user' AND
							`item`	IN($absent)",
					]),
					'value',
					'item'
				);
				foreach ($absent as &$a) {
					$a	= _json_decode($a);
					if (is_null($a)) {
						$a	= false;
					}
				}
				unset($a);
				$result					+= $absent;
				$data					+= $absent;
				$Cache->{"data/$user"}	= $data;
			}
			return $result;
		}
		if ($data === false || !isset($data[$item])) {
			if (!is_array($data)) {
				$data	= [];
			}
			$data[$item]			= _json_decode($this->db()->qfs([
				"SELECT `value`
				FROM `[prefix]users_data`
				WHERE
					`id`	= '$user' AND
					`item`	= '%s'",
				$item
			]));
			if (is_null($data[$item])) {
				$data[$item]	= false;
			}
			$Cache->{"data/$user"}	= $data;
		}
		return $data[$item];
	}
	/**
	 * Setting additional data item(s) of specified user
	 *
	 * @param array|string	$item	Item-value array may be specified for setting several items at once
	 * @param mixed|null	$value
	 * @param bool|int		$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function set_data ($item, $value = null, $user = false) {
		$user	= (int)($user ?: $this->id);
		if (!$user || $user == self::GUEST_ID || !$item) {
			return false;
		}
		if (is_array($item)) {
			$params	= [];
			foreach ($item as $i => $v) {
				$params[]	= [
					$i,
					_json_encode($v)
				];
			}
			unset($i, $v);
			$result			= $this->db_prime()->insert(
				"INSERT INTO `[prefix]users_data`
					(
						`id`,
						`item`,
						`value`
					) VALUES (
						$user,
						'%s',
						'%s'
					)
				ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
				$params
			);
		} else {
			$result	= $this->db_prime()->q(
				"INSERT INTO `[prefix]users_data`
					(
						`id`,
						`item`,
						`value`
					) VALUES (
						'$user',
						'%s',
						'%s'
					)
				ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
				$item,
				_json_encode($value)
			);
		}
		unset($this->cache->{"data/$user"});
		return $result;
	}
	/**
	 * Deletion of additional data item(s) of specified user
	 *
	 * @param string|string[]	$item
	 * @param bool|int			$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_data ($item, $user = false) {
		$user	= (int)($user ?: $this->id);
		if (!$user || $user == self::GUEST_ID || !$item) {
			return false;
		}
		$item	= implode(
			',',
			$this->db_prime()->s((array)$item)
		);
		$result	= $this->db_prime()->q(
			"DELETE FROM `[prefix]users_data`
			WHERE
				`id`	= '$user' AND
				`item`	IN($item)"
		);
		unset($this->cache->{"data/$user"});
		return $result;
	}
	/**
	 * Is admin
	 *
	 * @return bool
	 */
	function admin () {
		return $this->current['is']['admin'];
	}
	/**
	 * Is user
	 *
	 * @return bool
	 */
	function user () {
		return $this->current['is']['user'];
	}
	/**
	 * Is guest
	 *
	 * @return bool
	 */
	function guest () {
		return $this->current['is']['guest'];
	}
	/**
	 * Is bot
	 *
	 * @return bool
	 */
	function bot () {
		return $this->current['is']['bot'];
	}
	/**
	 * Is system
	 *
	 * @return bool
	 */
	function system () {
		return $this->current['is']['system'];
	}
	/**
	 * Get user id by login or email hash (sha224) (hash from lowercase string)
	 *
	 * @param  string $login_hash	Login or email hash
	 *
	 * @return bool|int				User id if found and not guest, otherwise - boolean <i>false</i>
	 */
	function get_id ($login_hash) {
		if (!preg_match('/^[0-9a-z]{56}$/', $login_hash)) {
			return false;
		}
		$id	= $this->cache->get($login_hash, function () use ($login_hash) {
			return $this->db()->qfs([
				"SELECT `id`
				FROM `[prefix]users`
				WHERE
					`login_hash`	= '%1\$s' OR
					`email_hash`	= '%1\$s'
				LIMIT 1",
				$login_hash
			]) ?: false;
		});
		return $id && $id != self::GUEST_ID ? $id : false;
	}
	/**
	 * Get user avatar, if no one present - uses Gravatar
	 *
	 * @param int|null	$size	Avatar size, if not specified or resizing is not possible - original image is used
	 * @param bool|int	$user	If not specified - current user assumed
	 *
	 * @return string
	 */
	function avatar ($size = null, $user = false) {
		$user	= (int)($user ?: $this->id);
		$avatar	= $this->get('avatar', $user);
		if (!$avatar && $this->id != self::GUEST_ID) {
			$avatar	= 'https://www.gravatar.com/avatar/'.md5($this->get('email', $user))."?d=mm&s=$size";
			$avatar	.= '&d='.urlencode(Config::instance()->base_url().'/includes/img/guest.gif');
		}
		if (!$avatar) {
			$avatar	= '/includes/img/guest.gif';
		}
		return h::prepare_url($avatar, true);
	}
	/**
	 * Get user name or login or email, depending on existing information
	 *
	 * @param bool|int $user	If not specified - current user assumed
	 *
	 * @return string
	 */
	function username ($user = false) {
		$user = (int)($user ?: $this->id);
		return $this->get('username', $user) ?: ($this->get('login', $user) ?: $this->get('email', $user));
	}
	/**
	 * Search keyword in login, username and email
	 *
	 * @param string		$search_phrase
	 *
	 * @return int[]|bool
	 */
	function search_users ($search_phrase) {
		$search_phrase = trim($search_phrase, "%\n");
		$found_users = $this->db()->qfas([
			"SELECT `id`
			FROM `[prefix]users`
			WHERE
				(
					`login`		LIKE '%1\$s' OR
					`username`	LIKE '%1\$s' OR
					`email`		LIKE '%1\$s'
				) AND
				`status` != '%s'",
			$search_phrase,
			self::STATUS_NOT_ACTIVATED
		]);
		return $found_users;
	}
	/**
	 * Get permission state for specified user
	 *
	 * Rule: if not denied - allowed (users), if not allowed - denied (admins)
	 *
	 * @param string	$group	Permission group
	 * @param string	$label	Permission label
	 * @param bool|int	$user	If not specified - current user assumed
	 *
	 * @return bool				If permission exists - returns its state for specified user, otherwise for admin permissions returns <b>false</b> and for
	 * 							others <b>true</b>
	 */
	function get_permission ($group, $label, $user = false) {
		$user			= (int)($user ?: $this->id);
		if ($this->system() || $user == self::ROOT_ID) {
			return true;
		}
		if (!$user) {
			return false;
		}
		if (!isset($this->permissions[$user])) {
			$this->permissions[$user]	= $this->cache->get("permissions/$user", function () use ($user) {
				$permissions	= [];
				if ($user != self::GUEST_ID) {
					$groups							= $this->get_groups($user);
					if (is_array($groups)) {
						$Group	= Group::instance();
						foreach ($groups as $group_id) {
							foreach ($Group->get_permissions($group_id) ?: [] as $p => $v) {
								$permissions[$p]	= $v;
							}
							unset($p, $v);
						}
					}
					unset($groups, $group_id);
				}
				foreach ($this->get_permissions($user) ?: [] as $p => $v) {
					$permissions[$p]	= $v;
				}
				return $permissions;
			});
		}
		$all_permission	= Cache::instance()->{'permissions/all'} ?: Permission::instance()->get_all();
		if (isset($all_permission[$group], $all_permission[$group][$label])) {
			$permission	= $all_permission[$group][$label];
			if (isset($this->permissions[$user][$permission])) {
				return (bool)$this->permissions[$user][$permission];
			} else {
				return $this->admin() ? true : strpos($group, 'admin/') !== 0;
			}
		} else {
			return true;
		}
	}
	/**
	 * Set permission state for specified user
	 *
	 * @param string	$group	Permission group
	 * @param string	$label	Permission label
	 * @param int		$value	1 - allow, 0 - deny, -1 - undefined (remove permission, and use default value)
	 * @param bool|int	$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function set_permission ($group, $label, $value, $user = false) {
		if ($permission = $this->get_permission(null, $group, $label)) {
			return $this->set_permissions(
				[
					$permission['id']	=> $value
				],
				$user
			);
		}
		return false;
	}
	/**
	 * Delete permission state for specified user
	 *
	 * @param string	$group	Permission group
	 * @param string	$label	Permission label
	 * @param bool|int	$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_permission ($group, $label, $user = false) {
		return $this->set_permission($group, $label, -1, $user);
	}
	/**
	 * Get array of all permissions states for specified user
	 *
	 * @param bool|int		$user	If not specified - current user assumed
	 *
	 * @return array|bool
	 */
	function get_permissions ($user = false) {
		$user = (int)($user ?: $this->id);
		if (!$user) {
			return false;
		}
		return $this->get_any_permissions($user, 'user');
	}
	/**
	 * Set user's permissions according to the given array
	 *
	 * @param array		$data
	 * @param bool|int	$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function set_permissions ($data, $user = false) {
		$user = (int)($user ?: $this->id);
		if (!$user) {
			return false;
		}
		return $this->set_any_permissions($data, $user, 'user');
	}
	/**
	 * Delete all user's permissions
	 *
	 * @param bool|int	$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_permissions_all ($user = false) {
		$user = (int)($user ?: $this->id);
		if (!$user) {
			return false;
		}
		return $this->del_any_permissions_all($user, 'user');
	}
	/**
	 * Add user's groups
	 *
	 * @param int|int[]		$group	Group id
	 * @param bool|int		$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function add_groups ($group, $user = false) {
		$user	= (int)($user ?: $this->id);
		if (!$user || $user == self::GUEST_ID) {
			return false;
		}
		$groups	= $this->get_groups($user);
		foreach ((array)_int($group) as $g) {
			$groups[]	= $g;
		}
		unset($g);
;		return $this->set_groups($groups, $user);
	}
	/**
	 * Get user's groups
	 *
	 * @param bool|int		$user	If not specified - current user assumed
	 *
	 * @return bool|int[]
	 */
	function get_groups ($user = false) {
		$user	= (int)($user ?: $this->id);
		if (!$user || $user == self::GUEST_ID) {
			return false;
		}
		return $this->cache->get("groups/$user", function () use ($user) {
			return $this->db()->qfas(
				"SELECT `group`
				FROM `[prefix]users_groups`
				WHERE `id` = '$user'
				ORDER BY `priority` DESC"
			);
		});
	}
	/**
	 * Set user's groups
	 *
	 * @param int[]		$groups
	 * @param bool|int	$user
	 *
	 * @return bool
	 */
	function set_groups ($groups, $user = false) {
		$user		= (int)($user ?: $this->id);
		if (!$user) {
			return false;
		}
		if (!empty($groups) && is_array_indexed($groups)) {
			foreach ($groups as $i => &$group) {
				if (!($group = (int)$group)) {
					unset($groups[$i]);
				}
			}
		}
		unset($i, $group);
		$existing	= $this->get_groups($user);
		$return		= true;
		$insert		= array_diff($groups, $existing);
		$delete		= array_diff($existing, $groups);
		unset($existing);
		if (!empty($delete)) {
			$delete	= implode(', ', $delete);
			$return	= $return && $this->db_prime()->q(
				"DELETE FROM `[prefix]users_groups`
				WHERE
					`id`	='$user' AND
					`group`	IN ($delete)"
			);
		}
		unset($delete);
		if (!empty($insert)) {
			foreach ($insert as &$i) {
				$i = [$user, $i];
			}
			unset($i);
			$return	= $return && $this->db_prime()->insert(
				"INSERT INTO `[prefix]users_groups`
					(
						`id`,
						`group`
					) VALUES (
						'%s',
						'%s'
					)",
				$insert
			);
		}
		unset($insert);
		$update		= [];
		foreach ($groups as $i => $group) {
			$update[] =
				"UPDATE `[prefix]users_groups`
				SET `priority` = '$i'
				WHERE
					`id`	= '$user' AND
					`group`	= '$group'
				LIMIT 1";
		}
		$return		= $return && $this->db_prime()->q($update);
		$Cache		= $this->cache;
		unset(
			$Cache->{"groups/$user"},
			$Cache->{"permissions/$user"}
		);
		return $return;
	}
	/**
	 * Delete user's groups
	 *
	 * @param int|int[]		$group	Group id
	 * @param bool|int		$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_groups ($group, $user = false) {
		$user	= (int)($user ?: $this->id);
		if (!$user || $user == self::GUEST_ID) {
			return false;
		}
		$groups	= array_diff(
			$this->get_groups($user),
			(array)_int($group)
		);
;		return $this->set_groups($groups, $user);
	}
	/**
	 * Returns current session id
	 *
	 * @return bool|string
	 */
	function get_session () {
		if ($this->bot() && $this->id == self::GUEST_ID) {
			return '';
		}
		return $this->current['session'];
	}
	/**
	 * Find the session by id as applies it, and return id of owner (user), updates last_sign_in, last_ip and last_online information
	 *
	 * @param null|string	$session_id
	 *
	 * @return int						User id
	 */
	function get_session_user ($session_id = null) {
		if ($this->bot() && $this->id == self::GUEST_ID) {
			return self::GUEST_ID;
		}
		if (!$session_id) {
			if (!$this->current['session']) {
				$this->current['session'] = _getcookie('session');
			}
			$session_id = $session_id ?: $this->current['session'];
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
			$this->add_session(self::GUEST_ID);
			$this->update_user_is();
			return self::GUEST_ID;
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
		$user	= (int)$user ?: self::GUEST_ID;
		if ($delete_current_session && is_md5($this->current['session'])) {
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
			if ($data['status'] != self::STATUS_ACTIVE) {
				/**
				 * If user is disabled
				 */
				if ($data['status'] == self::STATUS_INACTIVE) {
					$Page->warning($L->your_account_disabled);
					/**
					 * Mark user as guest, load data again
					 */
					$user	= self::GUEST_ID;
					goto getting_user_data;
				/**
				 * If user is not active
				 */
				} else {
					$Page->warning($L->your_account_is_not_active);
					/**
					 * Mark user as guest, load data again
					 */
					$user	= self::GUEST_ID;
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
				$user	= self::GUEST_ID;
				goto getting_user_data;
			}
		} elseif ($this->id != self::GUEST_ID) {
			/**
			 * If data was not loaded - mark user as guest, load data again
			 */
			$user	= self::GUEST_ID;
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
				TIME + ($user != self::GUEST_ID || $Config->core['session_expire'] < 300 ? $Config->core['session_expire'] : 300),
				$this->user_agent,
				$ip				= ip2hex($this->ip),
				$forwarded_for	= ip2hex($this->forwarded_for),
				$client_ip		= ip2hex($this->client_ip)
			);
			$time								= TIME;
			if ($user != self::GUEST_ID) {
				$this->db_prime()->q("UPDATE `[prefix]users` SET `last_sign_in` = $time, `last_online` = $time, `last_ip` = '$ip.' WHERE `id` ='$user'");
			}
			$this->current['session']			= $hash;
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
		$session_id = $session_id ?: $this->current['session'];
		if (!is_md5($session_id)) {
			return false;
		}
		unset($this->cache->{"sessions/$session_id"});
		$this->current['session'] = false;
		_setcookie('session', '');
		$result =  $this->db_prime()->q(
			"DELETE FROM `[prefix]sessions`
			WHERE `id` = '%s'
			LIMIT 1",
			$session_id
		);
		if ($create_guest_session) {
			return $this->add_session(self::GUEST_ID);
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
		$session_id	= $session_id ?: $this->current['session'];
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
		$session_id		= $session_id ?: $this->current['session'];
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
		$session_id	= $session_id ?: $this->current['session'];
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
		$ip	= ip2hex($this->ip);
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
	 * User registration
	 *
	 * @param string 				$email
	 * @param bool					$confirmation	If <b>true</b> - default system option is used, if <b>false</b> - registration will be
	 *												finished without necessity of confirmation, independently from default system option
	 *												(is used for manual registration).
	 * @param bool					$auto_sign_in	If <b>false</b> - no auto sign in, if <b>true</b> - according to system configuration
	 *
	 * @return array|bool|string					<b>exists</b>	- if user with such email is already registered<br>
	 * 												<b>error</b>	- if error occurred<br>
	 * 												<b>false</b>	- if email is incorrect<br>
	 * 												<b>array(<br>
	 * 												&nbsp;'reg_key'		=> *,</b>	//Registration confirmation key, or <b>true</b>
	 * 																					if confirmation is not required<br>
	 * 												&nbsp;<b>'password'	=> *,</b>	//Automatically generated password<br>
	 * 												&nbsp;<b>'id'		=> *</b>	//Id of registered user in DB<br>
	 * 												<b>)</b>
	 */
	function registration ($email, $confirmation = true, $auto_sign_in = true) {
		$email			= mb_strtolower($email);
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		}
		$this->delete_unconfirmed_users();
		if (!Trigger::instance()->run(
			'System/User/registration/before',
			[
				'email'	=> $email
			]
		)) {
			return false;
		}
		$email_hash		= hash('sha224', $email);
		$login			= strstr($email, '@', true);
		$login_hash		= hash('sha224', $login);
		if ($login && in_array($login, file_get_json(MODULES.'/System/index.json')['profile']) || $this->get_id($login_hash) !== false) {
			$login		= $email;
			$login_hash	= $email_hash;
		}
		if ($this->db_prime()->qf([
			"SELECT `id`
			FROM `[prefix]users`
			WHERE `email_hash` = '%s'
			LIMIT 1",
			$email_hash
		])) {
			return 'exists';
		}
		$Config			= Config::instance();
		$password		= password_generate($Config->core['password_min_length'], $Config->core['password_min_strength']);
		$password_hash	= hash('sha512', hash('sha512', $password).Core::instance()->public_key);
		$reg_key		= md5($password.$this->ip);
		$confirmation	= $confirmation && $Config->core['require_registration_confirmation'];
		if ($this->db_prime()->q(
			"INSERT INTO `[prefix]users` (
				`login`,
				`login_hash`,
				`password_hash`,
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
				'%s',
				'%s'
			)",
			$login,
			$login_hash,
			$password_hash,
			$email,
			$email_hash,
			TIME,
			ip2hex($this->ip),
			$reg_key,
			!$confirmation ? 1 : -1
		)) {
			$this->reg_id = $this->db_prime()->id();
			if (!$confirmation) {
				$this->set_groups([self::USER_GROUP_ID], $this->reg_id);
			}
			if (!$confirmation && $auto_sign_in && $Config->core['auto_sign_in_after_registration']) {
				$this->add_session($this->reg_id);
			}
			if (!Trigger::instance()->run(
				'System/User/registration/after',
				[
					'id'	=> $this->reg_id
				]
			)) {
				$this->registration_cancel();
				return false;
			}
			if (!$confirmation) {
				$this->set_groups([self::USER_GROUP_ID], $this->reg_id);
			}
			unset($this->cache->$login_hash);
			return [
				'reg_key'	=> !$confirmation ? true : $reg_key,
				'password'	=> $password,
				'id'		=> $this->reg_id
			];
		} else {
			return 'error';
		}
	}
	/**
	 * Confirmation of registration process
	 *
	 * @param string		$reg_key
	 *
	 * @return array|bool				array('id' => <i>id</i>, 'email' => <i>email</i>, 'password' => <i>password</i>) or <b>false</b> on failure
	 */
	function registration_confirmation ($reg_key) {
		if (!is_md5($reg_key)) {
			return false;
		}
		if (!Trigger::instance()->run(
			'System/User/registration/confirmation/before',
			[
				'reg_key'	=> $reg_key
			]
		)) {
			$this->registration_cancel();
			return false;
		}
		$this->delete_unconfirmed_users();
		$data			= $this->db_prime()->qf([
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
			self::STATUS_NOT_ACTIVATED
		]);
		if (!$data) {
			return false;
		}
		$this->reg_id	= $data['id'];
		$Config			= Config::instance();
		$password		= password_generate($Config->core['password_min_length'], $Config->core['password_min_strength']);
		$this->set(
			[
				'password_hash'	=> hash('sha512', hash('sha512', $password).Core::instance()->public_key),
				'status'		=> self::STATUS_ACTIVE
			],
			null,
			$this->reg_id
		);
		$this->set_groups([self::USER_GROUP_ID], $this->reg_id);
		$this->add_session($this->reg_id);
		if (!Trigger::instance()->run(
			'System/User/registration/confirmation/after',
			[
				'id'	=> $this->reg_id
			]
		)) {
			$this->registration_cancel();
			return false;
		}
		unset($this->cache->{$data['login_hash']});
		return [
			'id'		=> $this->reg_id,
			'email'		=> $data['email'],
			'password'	=> $password
		];
	}
	/**
	 * Canceling of bad/failed registration
	 */
	function registration_cancel () {
		if ($this->reg_id == 0) {
			return;
		}
		$this->add_session(self::GUEST_ID);
		$this->del_user($this->reg_id);
		$this->reg_id = 0;
	}
	/**
	 * Checks for unconfirmed registrations and deletes expired
	 */
	protected function delete_unconfirmed_users () {
		$reg_date		= TIME - Config::instance()->core['registration_confirmation_time'] * 86400;	//1 day = 86400 seconds
		$ids			= $this->db_prime()->qfas([
			"SELECT `id`
			FROM `[prefix]users`
			WHERE
				`last_sign_in`	= 0 AND
				`status`		= '%s' AND
				`reg_date`		< $reg_date",
			self::STATUS_NOT_ACTIVATED
		]);
		$this->del_user($ids);

	}
	/**
	 * Restoring of password
	 *
	 * @param int			$user
	 *
	 * @return bool|string			Key for confirmation or <b>false</b> on failure
	 */
	function restore_password ($user) {
		if ($user && $user != self::GUEST_ID) {
			$reg_key		= md5(MICROTIME.$this->ip);
			if ($this->set('reg_key', $reg_key, $user)) {
				$data					= $this->get('data', $user);
				$data['restore_until']	= TIME + Config::instance()->core['registration_confirmation_time'] * 86400;
				if ($this->set('data', $data, $user)) {
					return $reg_key;
				}
			}
		}
		return false;
	}
	/**
	 * Confirmation of password restoring process
	 *
	 * @param string		$key
	 *
	 * @return array|bool			array('id' => <i>id</i>, 'password' => <i>password</i>) or <b>false</b> on failure
	 */
	function restore_password_confirmation ($key) {
		if (!is_md5($key)) {
			return false;
		}
		$id			= $this->db_prime()->qfs([
			"SELECT `id`
			FROM `[prefix]users`
			WHERE
				`reg_key`	= '%s' AND
				`status`	= '%s'
			LIMIT 1",
			$key,
			self::STATUS_ACTIVE
		]);
		if (!$id) {
			return false;
		}
		$data		= $this->get('data', $id);
		if (!isset($data['restore_until'])) {
			return false;
		} elseif ($data['restore_until'] < TIME) {
			unset($data['restore_until']);
			$this->set('data', $data, $id);
			return false;
		}
		unset($data['restore_until']);
		$Config		= Config::instance();
		$password	= password_generate($Config->core['password_min_length'], $Config->core['password_min_strength']);
		$this->set(
			[
				'password_hash'	=> hash('sha512', hash('sha512', $password).Core::instance()->public_key),
				'data'			=> $data
			],
			null,
			$id
		);
		$this->add_session($id);
		return [
			'id'		=> $id,
			'password'	=> $password
		];
	}
	/**
	 * Delete specified user or array of users
	 *
	 * @param int|int[]	$user	User id or array of users ids
	 */
	function del_user ($user) {
		$this->del_user_internal($user);
	}
	/**
	 * Delete specified user or array of users
	 *
	 * @param int|int[]	$user
	 * @param bool		$update
	 */
	protected function del_user_internal ($user, $update = true) {
		$Cache	= $this->cache;
		Trigger::instance()->run(
			'System/User/del/before',
			[
				'id'	=> $user
			]
		);
		if (is_array($user)) {
			foreach ($user as $id) {
				$this->del_user_internal($id, false);
			}
			$user = implode(',', $user);
			$this->db_prime()->q(
				"DELETE FROM `[prefix]users`
				WHERE `id` IN ($user)"
			);
			unset($Cache->{'/'});
			return;
		}
		$user = (int)$user;
		if (!$user) {
			return;
		}
		$this->set_groups([], $user);
		$this->del_permissions_all($user);
		if ($update) {
			unset(
				$Cache->{hash('sha224', $this->get('login', $user))},
				$Cache->$user
			);
			$this->db_prime()->q(
				"DELETE FROM `[prefix]users`
				WHERE `id` = $user
				LIMIT 1"
			);
			Trigger::instance()->run(
				'System/User/del/after',
				[
					'id'	=> $user
				]
			);
		}
	}
	/**
	 * Add bot
	 *
	 * @param string	$name		Bot name
	 * @param string	$user_agent	User Agent string or regular expression
	 * @param string	$ip			IP string or regular expression
	 *
	 * @return bool|int				Bot <b>id</b> in DB or <b>false</b> on failure
	 */
	function add_bot ($name, $user_agent, $ip) {
		if ($this->db_prime()->q(
			"INSERT INTO `[prefix]users`
				(
					`username`,
					`login`,
					`email`,
					`status`
				) VALUES (
					'%s',
					'%s',
					'%s',
					'%s'
				)",
			xap($name),
			xap($user_agent),
			xap($ip),
			self::STATUS_ACTIVE
		)) {
			$id	= $this->db_prime()->id();
			$this->set_groups([self::BOT_GROUP_ID], $id);
			Trigger::instance()->run(
				'System/User/add_bot',
				[
					'id'	=> $id
				]
			);
			return $id;
		} else {
			return false;
		}
	}
	/**
	 * Set bot
	 *
	 * @param int		$id			Bot id
	 * @param string	$name		Bot name
	 * @param string	$user_agent	User Agent string or regular expression
	 * @param string	$ip			IP string or regular expression
	 *
	 * @return bool
	 */
	function set_bot ($id, $name, $user_agent, $ip) {
		$result	= $this->set(
			[
				'username'	=> $name,
				'login'		=> $user_agent,
				'email'		=> $ip
			],
			'',
			$id
		);
		unset($this->cache->bots);
		return $result;
	}
	/**
	 * Delete specified bot or array of bots
	 *
	 * @param int|int[]	$bot	Bot id or array of bots ids
	 */
	function del_bot ($bot) {
		$this->del_user($bot);
		unset($this->cache->bots);
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
	 * Do not track checking
	 *
	 * @return bool	<b>true</b> if tracking is not desired, <b>false</b> otherwise
	 */
	function dnt () {
		return isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1;
	}
	/**
	 * Returns array of user id, that are associated as contacts
	 *
	 * @param	bool|int	$user	If not specified - current user assumed
	 *
	 * @return	int[]				Array of user id
	 */
	function get_contacts ($user = false) {
		$user = (int)($user ?: $this->id);
		if (!$user || $user == self::GUEST_ID) {
			return [];
		}
		$contacts	= [];
		Trigger::instance()->run(
			'System/User/get_contacts',
			[
				'id'		=> $user,
				'contacts'	=> &$contacts
			]
		);
		return array_unique($contacts);
	}
	/**
	 * Disable memory cache
	 *
	 * Memory cache stores users data inside User class in order to get data faster next time.
	 * But in case of working with large amount of users this cache can be too large. Disabling will cause some performance drop, but save a lot of RAM.
	 */
	function disable_memory_cache () {
		$this->memory_cache	= false;
	}
	/**
	 * Saving changes of cache and users data
	 */
	function __finish () {
		/**
		 * Updating users data
		 */
		if (is_array($this->data_set) && !empty($this->data_set)) {
			$update = [];
			foreach ($this->data_set as $id => &$data_set) {
				$data = [];
				foreach ($data_set as $i => &$val) {
					if (in_array($i, $this->users_columns) && $i != 'id') {
						$val	= xap($val, false);
						$data[]	= "`$i` = ".$this->db_prime()->s($val);
					} elseif ($i != 'id') {
						unset($data_set[$i]);
					}
				}
				if (!empty($data)) {
					$data		= implode(', ', $data);
					$update[]	= "UPDATE `[prefix]users`
						SET $data
						WHERE `id` = '$id'";
					unset($i, $val, $data);
				}
			}
			if (!empty($update)) {
				$this->db_prime()->q($update);
			}
			unset($update);
		}
		/**
		 * Updating users cache
		 */
		foreach ($this->data as $id => &$data) {
			if (isset($this->update_cache[$id]) && $this->update_cache[$id]) {
				$data['id']			= $id;
				$this->cache->$id	= $data;
			}
		}
		$this->update_cache = [];
		unset($id, $data);
		$this->data_set = [];
	}
}
