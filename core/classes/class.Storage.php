<?php
namespace cs;
class Storage {
	public		$time					= 0;
	protected	$connections			= [],
				$successful_connections	= [],
				$failed_connections		= [];

	/**
	 * @param	bool|null|string $status	<b>null</b>		- returns array of connections with objects<br>
	 * 										<b>true|1</b>	- returns array of names of successful connections<br>
	 * 										<b>false|0</b>	- returns array of names of failed connections<br>
	 * @return	array|null
	 */
	function get_connections_list ($status = null) {
		if ($status === null) {
			return $this->connections;
		} elseif ($status == 0) {
			return $this->failed_connections;
		} elseif ($status == 1) {
			return $this->successful_connections;
		}
		return null;
	}
	//Обработка подключений к хранилищам
	/**
	 * @param	int			$connection
	 * @return	bool|object
	 */
	function __get ($connection) {
		if (!is_int($connection) && $connection != '0') {
			return false;
		}
		return $this->connecting($connection);
	}
	//Обработка запросов получения и изменения данных БД
	function __call ($connection, $mode) {
		if (method_exists('\\cs\\storage\\_Abstract', $connection)) {
			return call_user_func_array([$this->{0}, $connection], $mode);
		} else {
			return false;
		}
	}
	//Обработка всех подключений к хранилищам
	protected function connecting ($connection) {
		//Если соединение есть в списке неудачных - выходим
		if (isset($this->failed_connections[$connection])) {
			return false;
		}
		//Если подключение существует - возвращаем ссылку на подключение
		if (isset($this->connections[$connection])) {
			return $this->connections[$connection];
		}
		global $Config;
		//Ищем настройки подключения
		if (!isset($Config->storage[$connection]) || !is_array($Config->storage[$connection])) {
			return false;
		}
		//Если подключается локальное хранилище
		if ($connection == 0) {
			global $Core;
			$storage['connection']	= $Core->config('storage_type');
			$storage['url']			= $Core->config('storage_url');
			$storage['host']		= $Core->config('storage_host');
			$storage['user']		= $Core->config('storage_user');
			$storage['password']	= $Core->config('storage_password');
		} else {
			//Загружаем настройки
			$storage = &$Config->storage[$connection];
		}
		//Создаем новое подключение к хранилищу
		$engine_class					= '\\cs\\storage\\'.$storage['connection'];
		$this->connections[$connection]	= new $engine_class($storage['url'], $storage['host'], $storage['user'], $storage['password']);
		//В случае успешного подключения - заносим в общий список подключений, и возвращаем ссылку на подключение
		if (is_object($this->connections[$connection]) && $this->connections[$connection]->connected) {
			$this->successful_connections[] = $connection.'/'.$storage['host'].'/'.$storage['connection'];
			unset($storage);
			$this->$connection = $this->connections[$connection];
			return $this->connections[$connection];
		//Если подключение не удалось - разрушаем соединение
		} else {
			unset($this->$connection);
			//Добавляем подключение в список неудачных
			$this->failed_connections[$connection] = $connection.'/'.$storage['host'].'/'.$storage['connection'];
			unset($storage);
			//Выводим ошибку подключения к хранилищу
			global $L;
			trigger_error($L->error_storage.' '.$this->failed_connections[$connection], E_USER_WARNING);
			return false;
		}
	}

	/**
	 * Test connection to the Storage
	 * @param array|bool|string $data
	 *
	 * @return bool
	 */
	function test ($data = false) {
		if (empty($data)) {
			return false;
		} elseif (is_array($data)) {
			global $Config;
			if (isset($data[0])) {
				$storage = $Config->storage[$data[0]];
			} else {
				return false;
			}
		} else {
			$storage = _json_decode($data);
		}
		unset($data);
		if (is_array($storage)) {
			$connection_class	= '\\cs\\storage\\'.$storage['connection'];
			$test				= new $connection_class($storage['url'], $storage['host'], $storage['user'], $storage['password']);
			return $test->connected;
		} else {
			return false;
		}
	}
	/**
	 * Cloning restriction
	 */
	function __clone () {}
}