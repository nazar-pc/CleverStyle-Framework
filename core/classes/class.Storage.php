<?php
class Storage {
	public		$time					= 0,
				$successful_connections	= [],//TODO make hidden property
				$false_connections		= [],//TODO make hidden property
				$connections			= [];//TODO make hidden property
	//TODO get_connections_list like in DB class for Debugging
	//Обработка подключений к хранилищам
	function __get ($connection) {
		if (!is_int($connection) && $connection != '0') {
			return false;
		}
		return $this->connecting($connection);
	}
	//Обработка запросов получения и изменения данных БД
	function __call ($connection, $mode) {
		if (method_exists('StorageAbstract', $connection)) {
			return call_user_func_array([$this->{0}, $connection], $mode);
		} else {
			return false;
		}
	}
	//Обработка всех подключений к хранилищам
	protected function connecting ($connection) {
		//Если соединение есть в списке неудачных - выходим
		if (isset($this->false_connections[$connection])) {
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
			$storage['connection']	= 'Local';
			$storage['url']			= '';
			$storage['host']		= 'localhost';
			$storage['user']		= '';
			$storage['password']	= '';
		} else {
			//Загружаем настройки
			$storage = &$Config->storage[$connection];
		}
		//Создаем новое подключение к хранилищу
		$this->connections[$connection] = new $storage['connection']($storage['url'], $storage['host'], $storage['user'], $storage['password']);
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
			$this->false_connections[$connection] = $connection.'/'.$storage['host'].'/'.$storage['connection'];
			unset($storage);
			//Выводим ошибку подключения к хранилищу
			global $L;
			trigger_error($L->error_storage.' '.$this->false_connections[$connection], E_WARNING);
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
			$test = new $storage['connection']($storage['url'], $storage['host'], $storage['user'], $storage['password']);
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