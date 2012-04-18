<?php
class MySQL extends DatabaseAbstract {
	//Создание подключения
	//(название_бд, пользователь, пароль [, хост [, кодовая страница]]
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
		$this->engine = 'mysql';
		return $this->id;
	}
	//Смена текущей БД
	function select_db ($database) {
		return @mysql_select_db($database, $this->id);
	}
	//Запрос в БД
	//(текст_запроса)
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
		if (is_resource($this->query['resource'])) {
			@mysql_free_result($this->query['resource']);
		}
		global $db;
		$this->query['time']			= microtime(true);
		$this->queries['text'][]		= $this->query['text']				= str_replace('[prefix]', $this->prefix, $query);
		$this->queries['resource'][]	= (bool)$this->query['resource']	= @mysql_query($this->query['text'], $this->id);
		$this->query['time']			= round(microtime(true) - $this->query['time'], 6);
		$this->time						+= $this->query['time'];
		$db->time						+= $this->queries['time'][]			= $this->query['time'];
		++$this->queries['num'];
		++$db->queries;
		if ($this->query['resource']) {
			return $this->query['resource'];
		} else {
			return false;
		}
	}
	//Подсчёт количества строк
	//([ресурс_запроса])
	function n ($query_resource = false) {
		if($query_resource === false) {
			$query_resource = $this->query['resource'];
		}
		if(is_resource($query_resource)) {
			return @mysql_num_rows($query_resource);
		} else {
			return false;
		}
	}
	//Получение результатов
	//([ресурс_запроса [, в_виде_массива_результатов [, тип_возвращаемого_массива]]])
	function f ($query_resource = false, $array = false, $result_type = MYSQL_ASSOC) {	//MYSQL_BOTH==3, MYSQL_ASSOC==1, MYSQL_NUM==2
		if ($query_resource === false) {
			$query_resource = $this->query['resource'];
		}
		if (is_resource($query_resource)) {
			if ($array) {
				$result = [];
				while ($current = @mysql_fetch_array($query_resource, $result_type)) {
					$result[] = $current;
				}
				return $result;
			} else {
				return @mysql_fetch_array($query_resource, $result_type);
			}
		} else {
			return false;
		}
	}
	//id последнего insert запроса
	//([ресурс_запроса])
	function insert_id () {
		return @mysql_insert_id($this->id);
	}
	//Очистка результатов запроса
	//([ресурс_запроса])
	function free ($query_resource = false) {
		if($query_resource === false) {
			$query_resource = $this->query['resource'];
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
			if (is_resource($this->query['resource'])) {
				@mysql_free_result($this->query['resource']);
				$this->query['resource'] = '';
			}
			@mysql_close($this->id);
			$this->connected = false;
		}
	}
}