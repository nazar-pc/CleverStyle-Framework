<?php
class Storage {//TODO total refactoring!!!!!!!!!!!! like DB class
	public		$time					= 0,
				$successful_connections	= [],
				$false_connections		= [],
				$connections			= [];

	//Обработка подключений к хранилищам
	function __get ($connection) {
		return $this->connecting($connection);
	}
	//Обработка всех подключений к хранилищам
	private function connecting ($connection) {
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
		if ($connection == 'core' || $connection == 0) {
			$storage['connection'] = 'StorageLocal';
			$storage['url'] = url_by_source(STORAGE);
			$storage['host'] = '';
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
			//Ускоряем повторную операцию доступа к этому хранилищу
			if ($connection == 'core') {
				$zero = 0;
				$this->$zero = $this->$connection;
				unset($zero);
			}
			if ($connection == 0) {
				$this->core = $this->$connection;
			}
			$this->$connection = $this->connections[$connection];
			return $this->connections[$connection];
		//Если подключение не удалось - разрушаем соединение
		} else {
			unset($this->$connection);
			//Добавляем подключение в список неудачных
			$this->false_connections[$connection] = $connection.'/'.$storage['host'].'/'.$storage['connection'];
			unset($storage);
			//Выводим ошибку подключения к хранилищу
			global $Error, $L;
			$Error->process($L->error_storage.' '.$this->false_connections[$connection]);
			return false;
		}
	}
	//Тестовое подключение к хранилищу
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