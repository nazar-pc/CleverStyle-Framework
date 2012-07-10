<?php
namespace cs;
class DB {
	public		$queries				= 0,
				$time					= 0;
	protected	$connections			= [],
				$successful_connections	= [],
				$false_connections		= [],
				$mirrors				= [];
	/**
	 * @param	bool|null|string $status	<b>null</b>		- returns array of connections with objects<br>
	 * 										<b>true|1</b>	- returns array of names of succesfull connections<br>
	 * 										<b>false|0</b>	- returns array of names of failed connections<br>
	 * 										<b>mirror</b>	- returns array of names of mirror connections
	 * @return	array|null
	 */
	function get_connections_list ($status = null) {
		if ($status === null) {
			return $this->connections;
		} elseif ($status == 0) {
			return $this->false_connections;
		} elseif ($status == 1) {
			return $this->successful_connections;
		} elseif ($status == 'mirror') {
			return $this->mirrors;
		}
		return null;
	}
	//Обработка запросов получения данных БД
	//При соответствующей настройке срабатывает балансировка нагрузки на БД
	/**
	 * @param	int			$connection
	 *
	 * @return	bool|database\_Abstract
	 */
	function __get ($connection) {
		if (!is_int($connection) && $connection != '0') {
			return false;
		}
		global $Config;
		//Ищем зеркало подключения
		if (isset($this->mirrors[$connection])) {
			return $this->mirrors[$connection];
		//Ищем подключение
		} elseif (isset($this->connections[$connection])) {
			return $this->connections[$connection];
		//Проверяем, включена ли функция балансировки нагрузки и количество зеркал БД, подключаемся к БД
		} elseif (is_object($Config) && !empty($Config->core) && $Config->core['db_balance'] && $mirrors = count($Config->db[$connection]['mirrors'])) {
			$select = mt_rand(0, $Config->core['maindb_for_write'] ? $mirrors - 1 : $mirrors);
			if ($select < $mirrors) {
				$mirror = $Config->db[$connection]['mirrors'][--$select];
				$mirror_connection = $this->connecting($mirror['name'], $mirror);
				if (is_object($mirror_connection) && $mirror_connection->connected) {
					$this->mirrors[$connection] = $mirror_connection;
					return $this->mirrors[$connection];
				} else {
					unset($mirror_connection);
					return $this->__call($connection, [true]);
				}
			} else {
				return $this->connecting($connection);
			}
		//Подключаемся к БД
		} else {
			return $this->connecting($connection);
		}
	}
	//Обработка запросов получения и изменения данных БД
	/**
	 * @param	int				$connection
	 * @param	array			$mode
	 *
	 * @return	bool|database\_Abstract
	 */
	function __call ($connection, $mode) {
		if (is_int($connection) || $connection == '0') {
			return $this->connecting($connection, isset($mode[0]) ? (bool)$mode[0] : false);
		} elseif (method_exists('\\cs\\database\\_Abstract', $connection)) {
			return call_user_func_array([$this->{0}, $connection], $mode);
		} else {
			return false;
		}
	}
	//Обработка всех подключений к БД
	protected function connecting ($connection, $mirror = true) {
		//Если соединение есть в списке неудачных - выходим
		if (isset($this->false_connections[$connection])) {
			return false;
		}
		//Если зеркало подключения существует - возвращаем ссылку на подключение
		if (isset($this->mirrors[$connection]) && $mirror === true) {
			return $this->mirrors[$connection];
		}
		//Если подключение существует - возвращаем ссылку на подключение
		if (isset($this->connections[$connection])) {
			return $this->connections[$connection];
		}
		global $Config, $Core, $L;
		//Если подключается БД ядра
		if ($connection == 0 && !is_array($mirror)) {
			$db['type']		= $Core->config('db_type');
			$db['name']		= $Core->config('db_name');
			$db['user']		= $Core->config('db_user');
			$db['password']	= $Core->config('db_password');
			$db['host']		= $Core->config('db_host');
			$db['charset']	= $Core->config('db_charset');
			$db['prefix']	= $Core->config('db_prefix');
		} else {
			//Если подключается зеркало БД
			if (is_array($mirror)) {
				$db = &$mirror;
			} else {
				//Иначе ищем настройки подключения
				if (!isset($Config->db[$connection]) || !is_array($Config->db[$connection])) {
					return false;
				}
				//Загружаем настройки
				$db = &$Config->db[$connection];
			}
		}
		//Создаем новое подключение к БД
		errors_off();
		$engine_class					= '\\cs\\database\\'.$db['type'];
		$this->connections[$connection]	= new $engine_class($db['name'], $db['user'], $db['password'], $db['host'], $db['charset']);
		errors_on();
		//В случае успешного подключения - заносим в общий список подключений, и возвращаем ссылку на подключение
		if (is_object($this->connections[$connection]) && $this->connections[$connection]->connected) {
			$this->successful_connections[] = ($connection == 0 ? $L->core_db.'('.$Core->config('db_type').')' : $connection).'/'.$db['host'].'/'.$db['type'];
			//Устанавливаем текущий префикс
			$this->connections[$connection]->prefix = $db['prefix'];
			unset($db);
			//Ускоряем повторную операцию доступа к этой БД
			$this->$connection = $this->connections[$connection];
			return $this->connections[$connection];
		//Если подключение не удалось - разрушаем соединение и пытаемся подключится к зеркалу
		} else {
			unset($this->$connection);
			//Добавляем подключение в список неудачных
			$this->false_connections[$connection] = ($connection == 0 ? $L->core_db.'('.$Core->config('db_type').')' : $connection).'/'.$db['host'].'/'.$db['type'];
			unset($db);
			//Если допускается подключение к зеркалу БД, и зеркала доступны
			if (
				$mirror === true && 
				(
					($connection == 0 && isset($Config->db[0]['mirrors']) && is_array($Config->db[0]['mirrors']) && count($Config->db[0]['mirrors'])) ||
					(isset($Config->db[$connection]['mirrors']) && is_array($Config->db[$connection]['mirrors']) && count($Config->db[$connection]['mirrors']))
				)
			) {
				$dbx = ($connection == 0 ? $Config->db[0]['mirrors'] : $Config->db[$connection]['mirrors']);
				foreach ($dbx as $i => &$mirror_data) {
					$mirror_connection = $this->connecting($connection.' ('.$mirror_data['name'].')', $mirror_data);
					if (is_object($mirror_connection) && $mirror_connection->connected) {
						$this->mirrors[$connection] = $mirror_connection;
						//Ускоряем повторную операцию доступа к этой БД
						$this->$connection = $this->connections[$connection];
						//Возвращаем ссылку на подключение
						return $this->mirrors[$connection];
					}
				}
				unset($dbx, $i, $mirror_data, $mirror_connection);
			}
			//Если подключалось не зеркало - выводим ошибку подключения к БД
			if (!is_array($mirror)) {
				global $L;
				if ($connection == 0) {
					trigger_error($L->error_core_db, E_USER_ERROR);
				} else {
					trigger_error($L->error_db.' '.$this->false_connections[$connection], E_USER_WARNING);
				}
			}
			return false;
		}
	}
	//Тестовое подключение к БД
	function test ($data = false) {
		global $Core;
		if (empty($data)) {
			return false;
		} elseif (is_array($data)) {
			global $Config;
			if (isset($data[1])) {
				$db = $Config->db[$data[0]]['mirrors'][$data[1]];
			} elseif (isset($data[0])) {
				if ($data[0] == 0) {
					$db = [
						'type'		=> $Core->config('db_type'),
						'host'		=> $Core->config('db_host'),
						'name'		=> $Core->config('db_type'),
						'user'		=> $Core->config('db_user'),
						'password'	=> $Core->config('db_password'),
						'charset'	=> $Core->config('db_charset')
					];
				} else {
					$db = $Config->db[$data[0]];
				}
			} else {
				return false;
			}
		} else {
			$db = _json_decode($data);
		}
		unset($data);
		if (is_array($db)) {
			errors_off();
			$engine_class	= '\\cs\\database\\'.$db['type'];
			$test			= new $engine_class(
				$db['name'],
				$db['user'],
				$db['password'],
				$db['host'] ?: $Core->config('db_host'),
				$db['charset'] ?: $Core->config('db_charset')
			);
			errors_on();
			return $test->connected;
		} else {
			return false;
		}
	}
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	function __clone () {}
}