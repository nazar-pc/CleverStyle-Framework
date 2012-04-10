<?php
class User {
	protected	$secret,							//Secret random phrase for separating internal
													//function calling from external ones
				$current				= [
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
				$db						= false,	//Link to db object
				$db_prime				= false,	//Link to primary db object
				$cache					= [],		//Cache with some temporary data
				$init					= false,	//Current state of initialization
				$reg_id					= 0,		//User id after registration
				$users_columns			= [],		//Copy of columns list of users table for internal needs without Cache usage
				$permissions_table		= [];		//Array of all permissions for quick selecting

	function __construct () {
		$this->secret = uniqid();
		global $Cache, $Config, $Page, $L, $Key;
		//Detecting of current user
		//Last part in page path - key
		$rc = &$Config->routing['current'];
		if (
			$this->user_agent == 'CleverStyle CMS' &&
			(
				($this->login_attempts(hash('sha224', 0)) < $Config->core['login_attempts_block_count']) ||
				$Config->core['login_attempts_block_count'] == 0
			) &&
			isset($rc[count($rc) - 1]) &&
			(
				$key_data = $Key->get(
					$Config->components['modules']['System']['db']['keys'],
					$key = $rc[count($rc) - 1],
					true
				)
			) &&
			is_array($key_data)
		) {
			unset($rc[count($rc) - 1]);
			$url			= &$Config->server['url'];
			$current_url	= &$Config->server['current_url'];
			if ($this->current['is']['system'] = $key_data['url'] == $Config->server['host'].'/'.$url) {
				$this->current['is']['admin'] = true;
				$url = substr($url, 0, strrpos($url, '/'));
				$current_url = substr($current_url, 0, strrpos($current_url, '/'));
				interface_off();
				$_POST['data'] = _json_decode($_POST['data']);
				return;
			} else {
				$url = substr($url, 0, strrpos($url, '/'));
				$current_url = substr($current_url, 0, strrpos($current_url, '/'));
				$this->current['is']['guest'] = true;
				//Иммитируем неудачный вход, чтобы при намеренной попытке взлома заблокировать доступ
				$this->login_result(false, hash('sha224', 'system'));
				unset($_POST['data']);
				sleep(1);
			}
		}
		unset($key_data, $key, $rc);
		//Пользователь может устанавливать cookies
		if (setcookie($test = uniqid(), 'test')) {
			setcookie($test, '');
			unset($test);
			//Получение id пользователя по сессии
			$this->id = $this->get_session();
		//Не может установивить cookie - значит (вероятнее всего) бот
		} else {
			unset($test);
			//Получаем список известных ботов
			if (($bots = $Cache->{'users/bots'}) === false) {
				$bots = $this->db()->qfa('SELECT `id`, `login`, `email` FROM `[prefix]users` WHERE 3 IN (`groups`)');
				if (is_array($bots) && !empty($bots)) {
					foreach ($bots as &$bot) {
						$bot['login'] = _json_decode($bot['login']);
						$bot['email'] = _json_decode($bot['email']);
					}
					unset($bot);
					$Cache->{'users/bots'} = $bots;
				} else {
					$Cache->{'users/bots'} = 'null';
				}
			}
			//Устанавливаем метку, что это бот. В любом случае изменение любых настроек,
			//в том числе и языка и вида интерфейса для него недоступно
			$this->current['is']['bot'] = true;
			//Устанавливаем метку, что это гость, это нужно для упрощения доступа к материалам,
			//доступ к которым не ограничивается
			$this->current['is']['guest'] = true;
			//Для бота символически логином является $_SERVER['HTTP_USER_AGENT'] (название робота),
			//а электронной почтой  - $_SERVER['REMOTE_ADDR'] (IP робота)
			$user_agent	= $this->current['user_agent']	= $_SERVER['HTTP_USER_AGENT'];
			$ip			= $this->current['ip']			= $_SERVER['REMOTE_ADDR'];
			$bot_hash	= hash('sha224', $user_agent.$ip);
			//Если список известных ботов не пустой - определяем бота
			if (is_array($bots) && !empty($bots)) {
				//Загружаем данные
				if (($this->id = $Cache->{'users/'.$bot_hash}) === false) {
					//Данных нет - ищем бота в списке известных
					foreach ($bots as &$bot) {
						foreach ($bot['login'] as $login) {
							if ($user_agent == $login || preg_match($user_agent, $login)) {
								$this->id = $bot['id'];
								break 2;
							}
						}
						foreach ($bot['email'] as $email) {
							if ($ip == $email || preg_match($ip, $email)) {
								$this->id = $bot['id'];
								break 2;
							}
						}
					}
					unset($bots, $login, $email);
					//Если получен id - бот найден
					if ($this->id) {
						$Cache->{'users/'.$bot_hash} = $this->id;
					//Если такого бота в БД нет - определяем как гостя
					} else {
						$Cache->{'users/'.$bot_hash} = $this->id = 1;
					}
				}
			//Список ботов пустой - определяем как гостя
			} else {
				$Cache->{'users/'.$bot_hash} = $this->id = 1;
			}
		}
		//Загружаем данные пользователя
		//Точка возврата, выполняется, если аккаунт блокирован, неактивирован, или отключен
		getting_user_data:
		unset($data);
		$data = &$this->data[$this->id];
		if (($data = $Cache->{'users/'.$this->id}) === false) {
			$data = $this->db()->qf(
				'SELECT `login`, `username`, `language`, `timezone`, `status`, `block_until`, `avatar`
				FROM `[prefix]users`
				WHERE `id` = '.$this->id.'
				LIMIT 1'
			);
			if (is_array($data)) {
				$Cache->{'users/'.$this->id} = $data;
				if ($data['status'] != 1) {
					if ($data['status'] == 0) {
						$Page->warning($L->your_account_disabled);
						//Отмечаем как гостя, и получаем данные заново
						$this->id = 1;
						$this->del_session();
						goto getting_user_data;
					} else {
						$Page->warning($L->your_account_is_not_active);
						//Отмечаем как гостя, и получаем данные заново
						$this->id = 1;
						$this->del_session();
						goto getting_user_data;
					}
				} elseif ($data['block_until'] > TIME) {
					$Page->warning($L->your_account_blocked_until.' '.date($L->_datetime, $data['block_until']));
					//Отмечаем как гостя, и получаем данные заново
					$this->id = 1;
					$this->del_session();
					goto getting_user_data;
				}
			} elseif ($this->id != 1) {
				//Если данные не были получены - отмечаем, как гостя и пытаемся получить данные заново
				$this->id = 1;
				goto getting_user_data;
			}
		}
		unset($data);
		if ($this->id == 1) {
			$this->current['is']['guest'] = true;
		} else {
			//Checking of user type
			$groups = $this->get_user_groups();
			if (in_array(1, $groups)) {
				$this->current['is']['admin']	= true;
				$this->current['is']['user']	= true;
			} elseif (in_array(2, $groups)) {
				$this->current['is']['user']	= true;
			} elseif (in_array(3, $groups)) {
				$this->current['is']['bot']		= true;
			}
			unset($groups);
		}
		//If not guest - apply some individual settings
		if ($this->id != 1) {
			if ($this->timezone) {
				date_default_timezone_set($this->timezone);
			}
			if ($this->language) {
				$L->change($this->language);
			}
		}
		$this->init = true;
		if (($this->users_columns = $Cache->users_columns) === false) {
			$this->users_columns = $Cache->users_columns = $this->db()->columns('[prefix]users');
		}
		if (($this->permissions_table = $Cache->permissions_table) === false) {
			$permissions_table	= [];
			$data				= $this->db()->qfa('SELECT `id`, `label`, `group` FROM `[prefix]permissions`');
			foreach ($data as $item) {
				if (!isset($permissions_table[$item['group']])) {
					$permissions_table[$item['group']] = [];
				}
				$permissions_table[$item['group']][$item['label']] = $item['id'];
			}
			unset($data, $item);
			$this->permissions_table = $Cache->permissions_table = $permissions_table;
		}
	}
	/**
	 * @param array|string $item
	 * @param bool|int $user
	 * @param bool $stop_key
	 * @return array|bool
	 */
	function get ($item, $user = false, $stop_key = false) {
		$user = (int)($user ?: $this->id);
		if (!$user) {
			return false;
		}
		global $Cache;
		//Key of stopping, prohibits getting of data from db, when retrieves array of data
		static $_stop_key;
		if (!isset($_stop_key)) {
			$_stop_key = uniqid();
		}
		if ($item == 'user_agent') {
			return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		} elseif ($item == 'ip') {
			return $this->data[$this->id][$item] = $_SERVER['REMOTE_ADDR'];
		} elseif ($item == 'forwarded_for') {
			return $this->data[$this->id][$item] = isset($_SERVER['HTTP_X_FORWARDED_FOR'])	? $_SERVER['HTTP_X_FORWARDED_FOR'] : false;
		} elseif ($item == 'client_ip') {
			return $this->data[$this->id][$item] = isset($_SERVER['HTTP_CLIENT_IP'])		? $_SERVER['HTTP_CLIENT_IP'] : false;
		}
		//Link for simplier use
		$data = &$this->data[$user];
		//Если получаем массив значений
		if (is_array($item)) {
			$result = $new_items = [];
			//Пытаемся достать значения с локального кеша, иначе составляем массив недостающих значений
			foreach ($item as $i) {
				if (in_array($i, $this->users_columns)) {
					if (($res = $this->get($i, $user, $_stop_key)) != $_stop_key) {
						$result[$i] = $res;
					} else {
						$new_items[] = $i;
					}
				}
			}
			if (empty($new_items)) {
				return $result;
			}
			//Если есть недостающие значения - достаем их из БД
			$res = $this->db()->qf(
				'SELECT `'.implode('`, `', $new_items).'`
				FROM `[prefix]users`
				WHERE `id` = '.$user.'
				LIMIT 1'
			);
			if (is_array($res)) {
				$this->update_cache[$user] = true;
				if (isset($res['data'])) {
					$res['data'] = _json_decode($res['data']);
				}
				$data = array_merge((array)$data, $res);
				$result = array_merge($result, $res);
				//Пересортируем результирующий массив в том же порядке, что и входящий массив элементов
				$res = [];
				foreach ($item as $i) {
					$res[$i] = &$result[$i];
				}
				return $res;
			} else {
				return false;
			}
		//Если получаем одно значение
		} elseif (in_array($item, $this->users_columns)) {
			//Указатель начала получения данных
			get_data:
			//Если данные в локальном кеше - возвращаем
			if (isset($data[$item])) {
				return $data[$item];
			//Иначе если из кеша данные не доставали - пробуем достать
			} elseif (!isset($new_data) && ($new_data = $Cache->{'users/'.$user}) && is_array($new_data)) {
				//Обновляем локальный кеш
				if (is_array($new_data)) {
					$data = $new_data;
				}
				//Делаем новую попытку загрузки данных
				goto get_data;
			} elseif ($stop_key == $_stop_key) {
				return $stop_key;
			} else {
				$new_data = $this->db()->qf('SELECT `'.$item.'` FROM `[prefix]users` WHERE `id` = '.($user).' LIMIT 1');
				if (is_array($new_data)) {
					$this->update_cache[$user] = true;
					if (isset($data['data'])) {
						$data['data'] = _json_decode($data['data']);
					}
					return $data[$item] = &$new_data[$item];
				}
			}
		}
		return false;
	}
	/**
	 * @param array|string $item
	 * @param $value
	 * @param bool|int $user
	 * @return bool
	 */
	function set ($item, $value, $user = false) {
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
			if ($item == 'about') {
				$value = xap($value, true);
			} elseif ($item != 'data') {
				$value = xap($value);
			}
			$this->update_cache[$user] = true;
			$this->data[$user][$item] = $value;
			if ($this->init) {
				$this->data_set[$user][$item] = $this->data[$user][$item];
			}
		}
		return true;
	}
	function __get ($item) {
		return $this->get($item);
	}
	function __set ($item, $value = '') {
		$this->set($item, $value);
	}
	/**
	 * Returns link to the object of db for reading (can be mirror)
	 * @return DatabaseAbstract
	 */
	function db () {
		if (is_object($this->db)) {
			return $this->db;
		}
		if (is_object($this->db_prime)) {
			return $this->db = $this->db_prime;
		}
		global $Config, $db;
		//Save link for faster access
		$this->db = $db->{$Config->components['modules']['System']['db']['users']}();
		return $this->db;
	}
	/**
	 * Returns link to the object of db for writting (always main db)
	 * @return DatabaseAbstract
	 */
	function db_prime () {
		if (is_object($this->db_prime)) {
			return $this->db_prime;
		}
		global $Config, $db;
		//Save link for faster access
		$this->db_prime = $db->{$Config->components['modules']['System']['db']['users']}();
		return $this->db_prime;
	}
	/**
	 * Who is visitor
	 * @param string $mode admin|user|guest|bot|system
	 * @return bool
	 */
	function is ($mode) {
		return isset($this->current['is'][$mode]) && $this->current['is'][$mode];
	}
	/**
	 * Returns user id by login or email hash (sha224)
	 *
	 * @param  string $login_hash
	 * @return bool|int
	 */
	function get_id ($login_hash) {
		if (!preg_match('/^[0-9a-z]{56}$/', $login_hash)) {
			return false;
		}
		$data = $this->db()->qf(
			'SELECT `id` FROM `[prefix]users` WHERE '.
				'`login_hash` = '.$this->db()->sip($login_hash).' OR '.
				'`email_hash` = '.$this->db()->sip($login_hash).' '.
				'LIMIT 1'
		);
		return is_array($data) && $data['id'] != 1 ? $data['id'] : false;
	}
	/**
	 * Returns permission state for specified user
	 *
	 * @param int $group		Permission group
	 * @param string $label		Permission label
	 * @param bool|int $user
	 *
	 * @return bool				If permission exists - returns its state for specified user, otherwise returns true
	 */
	function permission ($group, $label, $user = false) {
		$user = (int)($user ?: $this->id);
		if ($this->is('system') || $user == 2) {
			return true;
		}
		if (!$user) {
			return false;
		}
		if (!isset($this->data[$user])) {
			$data[$user] = [];
		}
		if (!isset($this->data[$user]['permissions'])) {
			$groups = $this->get_user_groups($user);
			$permissions = [];
			if (is_array($groups)) {
				foreach ($groups as $group_id) {
					$permissions = array_merge($permissions, $this->get_group_permissions($group_id));
				}
			}
			unset($groups, $group_id);
			$this->data[$user]['permissions'] = array_merge($permissions, $this->get_user_permissions($user));
			unset($permissions);
		}
		if (isset($this->permissions_table[$group], $this->permissions_table[$group][$label])) {
			$permission = $this->permissions_table[$group][$label];
			if (isset($this->data[$user]['permissions'][$permission])) {
				return (bool)$this->data[$user]['permissions'][$permission];
			} else {
				return false;
			}
		}
		return true;
	}
	/**
	 * @param bool|int $user
	 * @return array|bool
	 */
	function get_user_permissions ($user = false) {
		$user = (int)($user ?: $this->id);
		if (!$user) {
			return false;
		}
		return $this->get_any_permissions($user, 'user');
	}
	/**
	 * @param	array		$data
	 * @param	bool|int	$user
	 * @return	bool
	 */
	function set_user_permissions ($data, $user = false) {
		$user = (int)($user ?: $this->id);
		if (!$user) {
			return false;
		}
		$delete = [];
		foreach ($data as $i => $val) {
			if ($val == -1) {
				$delete[] = (int)$i;
				unset($data[$i]);
			}
		}
		$return = true;
		if (!empty($delete)) {
			$return = $this->db_prime()->q('DELETE FROM `[prefix]users_permissions` WHERE `id` = '.$user.' AND `permission` IN ('.implode(', ', $delete).')');
		}
		unset($delete);
		if (empty($data)) {
			global $Cache;
			unset($Cache->{'users/permissions/'.$user});
			return $return;
		}
		return $return && $this->set_any_permissions($data, $user, 'user');
	}
	/**
	 * Get user groups
	 *
	 * @param	bool|int $user
	 * @return	array|bool
	 */
	function get_user_groups ($user = false) {
		$user = (int)($user ?: $this->id);
		if (!$user) {
			return false;
		}
		global $Cache;
		if (($groups = $Cache->{'users/groups/'.$user}) === false) {
			$groups = $this->db()->qfa('SELECT `group` FROM `[prefix]users_groups` WHERE `id` = '.$user);
			if (is_array($groups)) {
				foreach ($groups as &$group) {
					$group = $group['group'];
				}
			}
			unset($group);
			return $Cache->{'users/groups/'.$user} = $groups;
		}
		return $groups;
	}
	/**
	 * Set user groups
	 *
	 * @param	array	$data
	 * @param	int		$user
	 * @return	bool
	 */
	function set_user_groups ($data, $user) {
		$user		= (int)($user ?: $this->id);
		if (!$user) {
			return false;
		}
		$exitsing	= $this->db_prime()->qfa('SELECT `group` FROM `[prefix]users_groups` WHERE `id` = '.$user);
		$return		= true;
		foreach ($exitsing as &$group) {
			$group = $group['group'];
		}
		unset($group);
		$insert		= array_diff($data, $exitsing);
		$delete		= array_diff($exitsing, $data);
		unset($data, $exitsing);
		$return	= $return && $this->db_prime()->q(
			'DELETE FROM `[prefix]users_groups` WHERE `id` ='.$user.' AND `group` IN ('.implode(', ', $delete).')'
		);
		$q			= [];
		foreach ($insert as $group) {
			$q[] = $user.', '.(int)$group;
		}
		$return		= $return && $this->db_prime()->q('INSERT INTO `[prefix]users_groups`
				(`id`, `group`)
			VALUES
				('.implode('), (', $q).')'
		);
		global $Cache;
		unset($Cache->{'users/groups/'.$user});
		return $return;
	}
	/**
	 * Add new group
	 *
	 * @param string $title
	 * @param string $description
	 * @return bool|int
	 */
	function add_group ($title, $description) {
		$title			= $this->db_prime()->sip(xap($title, false));
		$description	= $this->db_prime()->sip(xap($description, false));
		if (!$title || !$description) {
			return false;
		}
		if ($this->db_prime()->q(
			'INSERT INTO `[prefix]groups` (`title`, `description`) VALUES ('.$title.', '.$description.')'
		)) {
			return $this->db_prime()->insert_id();
		} else {
			return false;
		}
	}
	/**
	 * Delete group
	 *
	 * @param $group
	 * @return bool
	 */
	function delete_group ($group) {
		$group = (int)$group;
		if ($group != 1 && $group != 2 && $group != 3) {
			$return = $this->db_prime()->q([
				'DELETE FROM `[prefix]groups` WHERE `id` = '.$group,
				'DELETE FROM `[prefix]users_groups` WHERE `group` = '.$group,
				'DELETE FROM `[prefix]groups_permissions` WHERE `id` = '.$group
			]);
			global $Cache;
			unset(
				$Cache->{'users/groups/'.$group},
				$Cache->{'users/permissions'},
				$Cache->{'groups/'.$group},
				$Cache->{'groups/permissions/'.$group}
			);
			return (bool)$return;
		} else {
			return false;
		}
	}
	/**
	 * @param int $group
	 * @return array|bool
	 */
	function get_group_data ($group) {
		global $Cache;
		$group = (int)$group;
		if (!$group) {
			return false;
		}
		if (($group_data = $Cache->{'groups/'.$group}) === false) {
			$group_data = $this->db()->qf(
				'SELECT `title`, `description`, `data`
				FROM `[prefix]groups`
				WHERE `id` = '.$group.'
				LIMIT 1'
			);
			$group_data['data'] = _json_decode($group_data['data']);
			$Cache->{'groups/'.$group} = $group_data;
		}
		return $group_data;
	}
	function set_group_data ($data, $group) {
		$group = (int)$group;
		if (!$group) {
			return false;
		}
		$update = [];
		if (isset($data['title'])) {
			$update[] = '`title` = '.$this->db_prime()->sip(xap($data['title'], false));
		}
		if (isset($data['description'])) {
			$update[] = '`description` = '.$this->db_prime()->sip(xap($data['description'], false));
		}
		if (isset($data['data'])) {
			$update[] = '`data` = '.$this->db_prime()->sip(_json_encode($data['data']));
		}
		if (!empty($update) && $this->db_prime()->q('UPDATE `[prefix]groups` SET '.implode(', ', $update).' WHERE `id` = '.$group.' LIMIT 1')) {
			global $Cache;
			unset($Cache->{'groups/'.$group});
			return true;
		} else {
			return false;
		}
	}
	/**
	 * @param int $group
	 * @return array
	 */
	function get_group_permissions ($group) {
		return $this->get_any_permissions($group, 'group');
	}
	function set_group_permissions ($data, $group) {
		return $this->set_any_permissions($data, (int)$group, 'group');
	}
	/**
	 * Common function for get_user_permissions() and get_group_permissions() because of their similarity
	 *
	 * @param	int			$id
	 * @param	string		$type
	 * @return	array|bool
	 */
	protected function get_any_permissions ($id, $type) {
		if (!($id = (int)$id)) {
			return false;
		}
		switch ($type) {
			case 'user':
				$table	= '[prefix]users_permissions';
				$path	= 'users/permissions/';
				break;
			case 'group':
				$table	= '[prefix]group_permissions';
				$path	= 'groups/permissions/';
				break;
			default:
				return false;
		}
		global $Cache;
		if (($permissions = $Cache->{$path.$id}) === false) {
			$permissions_array = $this->db()->qfa(
				'SELECT `permission`, `value`
				FROM `'.$table.'`
				WHERE `id` = '.$id
			);
			if (is_array($permissions_array)) {
				$permissions = [];
				foreach ($permissions_array as $permission) {
					$permissions[$permission['permission']] = (int)(bool)$permission['value'];
				}
				unset($permissions_array, $permission);
				return $Cache->{$path.$id} = $permissions;
			} else {
				return $Cache->{$path.$id} = false;
			}
		}
		return $permissions;
	}
	/**
	 * Common function for set_user_permissions() and set_group_permissions() because of their similarity
	 *
	 * @param	array	$data
	 * @param	int		$id
	 * @param	string	$type
	 * @return	bool
	 */
	protected function set_any_permissions ($data, $id, $type) {
		$id			= (int)$id;
		if (!is_array($data) || empty($data) || !$id) {
			return false;
		}
		switch ($type) {
			case 'user':
				$table	= '[prefix]users_permissions';
			break;
			case 'group':
				$table	= '[prefix]groups_permissions';
			break;
			default:
				return false;
		}
		$exitsing	= $this->db_prime()->qfa('SELECT `permission`, `value` FROM `'.$table.'` WHERE `id` = '.$id);
		$return		= true;
		if (!empty($exitsing)) {
			$update		= [];
			foreach ($exitsing as $permission => $value) {
				if (isset($data[$permission]) && $data[$permission] != $value) {
					$update[] = 'UPDATE `'.$table.'`
						SET `value` = '.(int)(bool)$data[$permission].'
						WHERE `permission` = '.$permission.' AND `id` = '.$id;
				}
				unset($data[$permission]);
			}
			unset($exitsing, $permission, $value);
			if (!empty($update)) {
				$return = $return && $this->db_prime()->q($update);
			}
			unset($update);
		}
		if (!empty($data)) {
			$insert	= [];
			foreach ($data as $permission => $value) {
				$insert[] = $id.', '.(int)$permission.', '.(int)(bool)$value;
			}
			unset($data, $permission, $value);
			if (!empty($insert)) {
				$return = $return && $this->db_prime()->q('INSERT INTO `'.$table.'`
						(`id`, `permission`, `value`)
					VALUES
						('.implode('), (', $insert).')'
				);
			}
		}
		global $Cache;
		if ($type == 'group') {
			unset($Cache->{'users/permissions'});
			unset($Cache->{'groups/permissions/'.$id});
		} elseif ($type == 'user') {
			unset($Cache->{'users/permissions/'.$id});
		}
		return $return;
	}
	/**
	 * Find the session by id, and return id of owner (user)
	 * @param string $session_id
	 * @param bool|string $secret For internal usage
	 * @return int User id
	 */
	function get_session ($session_id = '', $secret = false) {
		$this->current['session'] = _getcookie('session');
		$session_id = $session_id ?: $this->current['session'];
		global $Cache, $Config;
		$result = false;
		if ($session_id && !($result = $Cache->{'sessions/'.$session_id})) {
			$result = $this->db()->qf('SELECT
					`user`, `expire`, `user_agent`, `ip`, `forwarded_for`, `client_ip`
				FROM `[prefix]sessions`
				WHERE
					`id` = '.$this->db()->sip($session_id).' AND
					`expire` > '.TIME.' AND
					`user_agent` = '.$this->db()->sip($this->user_agent).' AND
					`ip` = \''.ip2hex($this->ip).'\' AND
					`forwarded_for` = \''.ip2hex($this->forwarded_for).'\' AND
					`client_ip` = \''.ip2hex($this->client_ip).'\''
			);
			$Cache->{'sessions/'.$session_id} = $result;
		}
		if ($secret === $this->secret) {
			return $result['user'];
		}
		if (!$session_id || !is_array($result)) {
			$this->add_session(1);
			return 1;
		}
		if ($result['expire'] - TIME < $Config->core['session_expire'] * $Config->core['update_ratio'] / 100) {
			$this->db_prime()->q('UPDATE `[prefix]sessions`
				SET `expire` = '.(TIME + $Config->core['session_expire']).'
				WHERE `id` = \''.$session_id.'\''
			);
			$result['expire'] = TIME + $Config->core['session_expire'];
			$Cache->{'sessions/'.$session_id} = $result;
		}
		return $result['user'];
	}
	/**
	 * Create the session for the user with specified id
	 * @param int $id
	 * @return bool
	 */
	function add_session ($id) {
		if (preg_match('/^[0-9a-z]{32}$/', $this->current['session'])) {
			$this->del_session();
		}
		global $Config;
		//Generate hash in cycle, to obtain unique value
		for ($i = 0; $hash = md5(MICROTIME + $i); ++$i) {
			if ($this->db_prime()->qf('SELECT `id` FROM `[prefix]sessions` WHERE `id` = \''.$hash.'\' LIMIT 1')) {
				continue;
			}
			$this->db_prime()->q([
				'INSERT INTO `[prefix]sessions`
					(`id`, `user`, `created`, `expire`, `user_agent`, `ip`, `forwarded_for`, `client_ip`)
						VALUES
					(
						\''.$hash.'\',
						'.$id.',
						'.TIME.',
						'.(TIME + $Config->core['session_expire']).',
						'.$this->db_prime()->sip($this->user_agent).',
						\''.($ip = ip2hex($this->ip)).'\',
						\''.($forwarded_for = ip2hex($this->forwarded_for)).'\',
						\''.($client_ip = ip2hex($this->client_ip)).'\'
					)',
				'UPDATE `[prefix]users`
					SET
						`lastlogin`	= '.TIME.',
						`lastip`	= \''.$ip.'\'
					WHERE `id` ='.$id
			]);
			global $Cache;
			$Cache->{'sessions/'.$hash} = $this->current['session'] = [
				'user'			=> $id,
				'expire'		=> TIME + $Config->core['session_expire'],
				'user_agent'	=> $this->user_agent,
				'ip'			=> $ip,
				'forwarded_for'	=> $forwarded_for,
				'client_ip'		=> $client_ip
			];
			_setcookie('session', $hash, TIME + $Config->core['session_expire'], false, true);
			$this->get_session();
			return true;
		}
		return false;
	}
	/**
	 * Remove the session
	 * @param string $session_id
	 * @return bool
	 */
	function del_session ($session_id = '') {
		global $Cache;
		$session_id = $session_id ?: $this->current['session'];
		$this->current['session'] = false;
		$this->add_session(1);
		if (!preg_match('/^[0-9a-z]{32}$/', $session_id)) {
			return false;
		}
		unset($Cache->{'sessions/'.$session_id});
		return $session_id ? $this->db_prime()->q(
			'UPDATE `[prefix]sessions`
			SET `expire` = 0
			WHERE `id` = \''.$session_id.'\''
		) : false;
	}
	/**
	 * Remove all user sessions
	 * @param bool|int $id
	 * @return bool
	 */
	function del_all_sessions ($id = false) {
		global $Cache;
		$id = $id ?: $this->id;
		_setcookie('session', '');
		$data = $this->db_prime()->qfa('SELECT `id` FROM `[prefix]sessions` WHERE `user` = '.$this->id);
		foreach ($data as $session) {
			unset($Cache->{'sessions/'.$session['id']});
		}
		$this->add_session(1);
		return $id ? $this->db_prime()->q('UPDATE `[prefix]sessions` SET `expire` = 0 WHERE `user` = '.$this->id) : false;
	}
	/**
	 * Check number of login attempts
	 * @param bool|string $login_hash
	 * @return int Number of attempts
	 */
	function login_attempts ($login_hash = false) {
		$login_hash = $login_hash ?: hash('sha224', $_POST['login']);
		if (!preg_match('/^[0-9a-z]{56}$/', $login_hash)) {
			return false;
		}
		if (isset($this->cache['login_attempts'][$login_hash])) {
			return $this->cache['login_attempts'][$login_hash];
		}
		$return = $this->db()->qf(
			'SELECT COUNT(`expire`) as `count` FROM `[prefix]logins` '.
				'WHERE `expire` > '.TIME.' AND ('.
					'`login_hash` = \''.$login_hash.'\' OR `ip` = \''.ip2hex($this->ip).'\''.
				')',
			false,
			MYSQL_NUM
		);
		return isset($return['count']) ? $this->cache['login_attempts'][$login_hash] = $return['count'] : 0;
	}
	/**
	 * Process login result
	 * @param bool $result
	 * @param bool|string $login_hash
	 */
	function login_result ($result, $login_hash = false) {
		$login_hash = $login_hash ?: hash('sha224', $_POST['login']);
		if (!preg_match('/^[0-9a-z]{56}$/', $login_hash)) {
			return;
		}
		if ($result) {
			$this->db_prime()->q('UPDATE `[prefix]logins`
				SET `expire` = 0
				WHERE
					`expire` > '.TIME.' AND (
						`login_hash` = \''.$login_hash.'\' OR `ip` = \''.ip2hex($this->ip).'\'
					)'
			);
		} else {
			global $Config;
			$this->db_prime()->q(
				'INSERT INTO `[prefix]logins` (
					`expire`,
					`login_hash`,
					`ip`
				) VALUES (
					'.(TIME + $Config->core['login_attempts_block_time']).',
					\''.$login_hash.'\',
					\''.ip2hex($this->ip).'\'
				)'
			);
			if (isset($this->cache['login_attempts'][$login_hash])) {
				++$this->cache['login_attempts'][$login_hash];
			}
			global $Config;
			if ($this->db_prime()->insert_id() % $Config->core['inserts_limit'] == 0) {
				$this->db_prime()->q('DELETE FROM `[prefix]logins` WHERE `expire` < '.TIME);
			}
		}
	}
	/**
	 * Processing of user registration
	 * @param string $email
	 * @return array|bool|string
	 */
	function registration ($email) {
		global $Config;
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		}
		$this->db_prime()->q('UPDATE `[prefix]users` SET
				`login` = null,
				`login_hash` = null,
				`username` = \'deleted\',
				`password_hash` = null,
				`email` = null,
				`email_hash` = null,
				`groups` = null,
				`regdate` = 0,
				`regip` = null,
				`regkey` = null
			WHERE
				`lastlogin` = 0 AND
				`status` = -1 AND
				`regdate` != 0 AND
				`regdate` < '.(TIME - $Config->core['registration_confirmation_time']*86400)
		);
		$email_ = hash('sha224', $email);
		if (!$this->db_prime()->q('SELECT `id` FROM `[prefix]users` WHERE `email_hash` = \''.$email_.'\' LIMIT 1')) {
			return 'exists';
		}
		$password	= password_generate($Config->core['password_min_length'], $Config->core['password_min_strength']);
		$reg_key	= md5($password.$this->ip);
		if ($this->db_prime()->q('INSERT INTO `[prefix]users` (
				`login`,
				`login_hash`,
				`password_hash`,
				`email`,
				`email_hash`,
				`groups`,
				`regdate`,
				`regip`,
				`regkey`,
				`status`
			) VALUES (
				'.$this->db_prime()->sip($email).',
				\''.$email_.'\',
				\''.hash('sha512', $password).'\',
				'.$this->db_prime()->sip($email).',
				\''.$email_.'\',
				2,
				'.TIME.',
				\''.ip2hex($this->ip).'\',
				\''.$reg_key.'\',
				'.($Config->core['require_registration_confirmation'] ? '-1' : '1').'
			)'
		)) {
			$this->reg_id = $this->db_prime()->insert_id();
			if (!$Config->core['require_registration_confirmation'] && $Config->core['autologin_after_registration']) {
				$this->add_session($this->reg_id);
			}
			if ($this->reg_id % $Config->core['inserts_limit'] == 0) {
				$this->db_prime()->q('DELETE FROM `[prefix]users` WHERE
					`login_hash` = null AND
					`email_hash` = null AND
					`password_hash` = null AND
					`id` != 1 AND
					`id` != 2'
				);
			}
			return [
				'reg_key'	=> $Config->core['require_registration_confirmation'] ? $reg_key : true,
				'password'	=> $password
			];
		} else {
			return 'error';
		}
	}
	/**
	 * Confirmation of registration process
	 * @param $reg_key
	 * @return array|bool
	 */
	function confirmation ($reg_key) {
		global $Config;
		if (!preg_match('/^[0-9a-z]{32}$/', $reg_key)) {
			return false;
		}
		$this->db_prime()->q('UPDATE `[prefix]users` SET
				`login` = null,
				`login_hash` = null,
				`username` = \'deleted\',
				`password_hash` = null,
				`email` = null,
				`email_hash` = null,
				`groups` = null,
				`regdate` = 0,
				`regip` = null,
				`regkey` = null
			WHERE
				`lastlogin` = 0 AND
				`status` = -1 AND
				`regdate` != 0 AND
				`regdate` < '.(TIME - $Config->core['registration_confirmation_time']*86400)
		);
		$data = $this->db_prime()->qf(
			'SELECT `id`, `email` FROM `[prefix]users` WHERE `regkey` = \''.$reg_key.'\' AND `status` = -1 LIMIT 1'
		);
		if (!isset($data['email'])) {
			return false;
		}
		$this->reg_id = $data['id'];
		$password	= password_generate($Config->core['password_min_length'], $Config->core['password_min_strength']);
		$this->db_prime()->q('UPDATE `[prefix]users`
			SET
				`password_hash` = \''.hash('sha512', $password).'\',
				`status` = 1
			WHERE `id` = '.$this->reg_id
		);
		$this->add_session($this->reg_id);
		return [
			'email'		=> $data['email'],
			'password'	=> $password
		];
	}
	/**
	 * Canceling of bad registration
	 */
	function registration_cancel () {
		if ($this->reg_id == 0) {
			return;
		}
		$this->add_session(1);
		$this->db_prime()->q('UPDATE `[prefix]users`
			SET
				`login` = null,
				`login_hash` = null,
				`username` = \'deleted\',
				`password_hash` = null,
				`email` = null,
				`email_hash` = null,
				`groups` = null,
				`regdate` = 0,
				`regip` = null,
				`regkey` = null,
				`status` = -1
			WHERE `id` = '.$this->reg_id
		);
		$this->reg_id = 0;
	}
	/**
	 * Cloning restriction
	 */
	function __clone () {}
	/**
	 * Saving cache changing, and users data
	 */
	function __finish () {
		global $Cache;
		//Update users cache
		foreach ($this->data as $id => &$data) {
			if (isset($this->update_cache[$id]) && $this->update_cache[$id]) {
				$data['id'] = $id;
				$Cache->{'users/'.$id} = $data;
			}
		}
		$this->update_cache = [];
		unset($id, $data);
		//Update users data
		$users_columns = $Cache->users_columns;
		if (is_array($this->data_set) && !empty($this->data_set)) {
			foreach ($this->data_set as $id => &$data_set) {
				$data = [];
				foreach ($data_set as $i => &$val) {
					if (in_array($i, $users_columns) && $i != 'id') {
						if ($i == 'data') {
							$val = _json_encode($val);
						} elseif ($i == 'text') {
							$val = xap($val, true);
						} else {
							$val = xap($val, false);
						}
						$data[] = '`'.$i.'` = '.$this->db_prime()->sip($val);
					}
				}
				$this->db_prime()->q('UPDATE `[prefix]users` SET '.implode(', ', $data).' WHERE `id` = '.$id);
				unset($i, $val, $data);
			}
		}
		$this->data_set = [];
	}
}