<?php
class MySQL extends DatabaseAbstract {
	//Создание подключения
	function __construct ($database, $user = '', $password = '', $host = 'localhost', $codepage = false) {
		$this->connecting_time = microtime(true);
		$this->id = @mysql_connect($host, $user, $password);
		if(is_resource($this->id)) {
			if(!$this->select_db($database)) {
				return false;
			}
			$this->database = $database;
			//Смена кодировки соеденения с БД
			if ($codepage) {
				if ($codepage != @mysql_client_encoding($this->id)) {
					@mysql_set_charset($codepage, $this->id);
				}
			}
			$this->connected = true;
		} else {
			return false;
		}
		$this->connecting_time = microtime(true) - $this->connecting_time;
		global $db;
		$db->time += $this->connecting_time;
		$this->engine = 'MySQL';
		return $this->id;
	}
	//Смена текущей БД
	function select_db ($database) {
		return @mysql_select_db($database, $this->id);
	}
	//Запрос в БД
	function q ($query) {
		if (is_array($query) && !empty($query)) {
			$return = true;
			foreach ($query as $q) {
				$return = $return && $this->q($q);
			}
			return $return;
		}
		if(!$query) {
			return false;
		}
		global $db;
		$this->query['time']			= microtime(true);
		$this->queries['text'][]		= $this->query['text']				= str_replace('[prefix]', $this->prefix, $query);
		$resource						= @mysql_query($this->query['text'], $this->id);
		$this->queries['resource'][]	= $resource;
		$this->query['time']			= round(microtime(true) - $this->query['time'], 6);
		$this->time						+= $this->query['time'];
		$this->queries['time'][]		= $this->query['time'];
		$db->time						+= $this->query['time'];
		++$this->queries['num'];
		++$db->queries;
		return $resource;
	}
	//Подсчёт количества строк
	/**
	 * @param bool|resource $query_resource
	 * @return int|bool
	 */
	function n ($query_resource = false) {
		if($query_resource === false) {
			$query_resource = $this->queries['resource'][count($this->queries['resource'])-1];
		}
		if(is_resource($query_resource)) {
			return @mysql_num_rows($query_resource);
		} else {
			return false;
		}
	}
	//Получение результатов
	/**
	 * @param bool|resource $query_resource
	 * @param bool|string   $one_column
	 * @param bool $array
	 *
	 * @return array|bool
	 */
	function f ($query_resource = false, $one_column = false, $array = false) {
		if ($query_resource === false) {
			$query_resource = $this->queries['resource'][count($this->queries['resource'])-1];
		}
		if (is_resource($query_resource)) {
			if ($array) {
				$result = [];
				if ($one_column === false) {
					while ($current = @mysql_fetch_array($query_resource, MYSQL_ASSOC)) {
						$result[] = $current;
					}
				} else {
					$one_column = (string)$one_column;
					while ($current = @mysql_fetch_array($query_resource, MYSQL_ASSOC)) {
						$result[] = $current[$one_column];
					}
				}
				return $result;
			} else {
				$result	= @mysql_fetch_array($query_resource, MYSQL_ASSOC);
				if ($one_column && is_array($result)) {
					return $result[$one_column];
				}
				return $result;
			}
		} else {
			return false;
		}
	}
	//id последнего insert запроса
	function insert_id () {
		return @mysql_insert_id($this->id);
	}
	//Очистка результатов запроса
	/**
	 * @param bool|resource $query_resource
	 * @return bool
	 */
	function free ($query_resource = false) {
		if($query_resource === false) {
			$query_resource = $this->queries['resource'][count($this->queries['resource'])-1];
		}
		if(is_resource($query_resource)) {
			return @mysql_free_result($query_resource);
		} else {
			return true;
		}
	}
	/**
	 * Preparing string for using in SQL query
	 * SQL Injection Protection
	 * @param $data
	 * @return string
	 */
	function sip ($data) {
		if (is_int($data)) {
			return $data;
		} else {
			//return '\''.mysql_real_escape_string($data).'\'';
			return 'unhex(\''.bin2hex((string)$data).'\')';
		}
	}
	//Информация о MySQL-сервере
	function server () {
		return @mysql_get_server_info($this->id);
	}
	//Отключение от БД
	function __destruct () {
		if($this->connected && is_resource($this->id)) {
			if (is_array($this->queries['resource'])) {
				foreach ($this->queries['resource'] as &$resource) {
					if (is_resource($resource)) {
						@mysql_free_result($resource);
						$resource = false;
					}
				}
			}
			@mysql_close($this->id);
			$this->connected = false;
		}
	}
}