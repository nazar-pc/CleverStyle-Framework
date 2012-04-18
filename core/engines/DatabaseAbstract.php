<?php
abstract class DatabaseAbstract {
	public		$connected	= false,				//Метка наличия соединения
				$engine		= false,				//Тип движка БД, может использоваться при построении запросов,
													//чтобы учитывать особенности конкретного движка (название в нижнем регистре)
				$database,							//Текущая БД
				$prefix,							//Текущий префикс
				$time,								//Массив для хранения общей длительности выполнения запросов
				$query		= array(				//Массив для хранения данных последнего выполненого запроса
								'start'		=> '',
								'end'		=> '',
								'time'		=> '',
								'text'		=> '',
								'resource'	=> '',
								'id'		=> ''
							),
				$queries	= array(				//Массив для хранения данных всех выполненых запросов
								'num'		=> '',
								'time'		=> [],
								'text'		=> [],
								'result'	=> []
							),
				$connecting_time;					//Время соединения
	protected	$id;								//Указатель на соединение с БД
	
	//Создание подключения
	//(название_бд, пользователь, пароль [, хост [, кодовая страница]]
	/**
	 * @param string $database
	 * @param string $user
	 * @param string $password
	 * @param string $host
	 * @param bool|string $codepage
	 */
	abstract function __construct ($database, $user = '', $password = '', $host = 'localhost', $codepage = false);
	//Смена текущей БД
	/**
	 * @abstract
	 * @param string $database
	 */
	abstract function select_db ($database);
	//Запрос в БД
	//(текст_запроса)
	/**
	 * @abstract
	 * @param array|string $query
	 * @return resource
	 */
	abstract function q ($query);
	//Подсчёт количества строк
	//([ресурс_запроса])
	/**
	 * @abstract
	 * @param bool|resource $query_resource
	 * @return int|bool
	 */
	abstract function n ($query_resource = false);
	//Получение результатов
	//([ресурс_запроса [, в_виде_массива_результатов [, тип_возвращаемого_массива]]])
	/**
	 * @abstract
	 * @param bool|resource $query_resource
	 * @param bool $array
	 * @param int $result_type
	 * @return array|bool
	 */
	abstract function f ($query_resource = false, $array = false, $result_type = MYSQL_ASSOC);	//MYSQL_BOTH==3, MYSQL_ASSOC==1, MYSQL_NUM==2
	//Упрощенный интерфейс метода для получения результата в виде массива
	//([ресурс_запроса [, тип_возвращаемого_массива]])
	/**
	 * @param bool|resource $query_resource
	 * @param int $result_type
	 * @return array|bool
	 */
	function fa ($query_resource = false, $result_type = MYSQL_ASSOC) {
		return $this->f($query_resource, true, $result_type);
	}
	//Запрос с получением результатов, результаты запросов кешируются при соответствующей настройке сайта
	//(текст_запроса [, тип_возвращаемого_массива [, в_виде массива]])
	/**
	 * @param string $query
	 * @param bool $array
	 * @param int $result_type
	 * @return array|bool
	 */
	function qf ($query = '', $array = false, $result_type = MYSQL_ASSOC) {
		if (!$query) {
			return false;
		}
		return $this->f($this->q($query), $array, $result_type);
	}
	//Упрощенный интерфейс метода выполнения запроса с получением результата в виде массива
	//(текст_запроса [, тип_возвращаемого_массива])
	/**
	 * @param string $query
	 * @param int $result_type
	 * @return array|bool
	 */
	function qfa ($query = '', $result_type = MYSQL_ASSOC) {
		if (!$query) {
			return false;
		}
		return $this->qf($query, true, $result_type);
	}
	//id последнего insert запроса
	/**
	 * @abstract
	 * @return int Id of last inserted row
	 */
	abstract function insert_id ();
	//Очистка результатов запроса
	//([ресурс_запроса])
	/**
	 * @abstract
	 * @param bool|resource $query_resource
	 * @result bool
	 */
	abstract function free ($query_resource = false);
	//Получение списка полей таблицы
	//(название_таблицы [, похожих_на [, тип_возвращаемого_массива]])
	/**
	 * @param string $table
	 * @param bool|string $like
	 * @return array|bool
	 */
	function fields ($table, $like = false) {
		if(!$table) {
			return false;
		}
		if ($like) {
			$fields = $this->qfa('SHOW FIELDS FROM `'.$table.'` LIKE \''.$like.'\'');
		} else {
			$fields = $this->qfa('SHOW FIELDS FROM `'.$table.'`');
		}
		foreach ($fields as &$field) {
			$field = $field['Field'];
		}
		return $fields;
	}
	//Получение списка колонок таблицы
	//(название_таблицы [, похожих_на [, тип_возвращаемого_массива]])
	/**
	 * @param string $table
	 * @param bool|string $like
	 * @return array|bool
	 */
	function columns ($table, $like = false) {
		if(!$table) {
			return false;
		}
		if ($like) {
			$columns = $this->qfa('SHOW COLUMNS FROM `'.$table.'` LIKE \''.$like.'\'');
		} else {
			$columns = $this->qfa('SHOW COLUMNS FROM `'.$table.'`');
		}
		foreach ($columns as &$column) {
			$column = $column['Field'];
		}
		return $columns;
	}
	//Получение списка таблиц БД (если БД не указана - используется текущая)
	//([похожих_на [, тип_возвращаемого_массива]]])
	function tables ($like = false) {
		if ($like) {
			return $this->qfa('SHOW TABLES FROM `'.$this->database.'` LIKE \''.$like.'\'');
		} else {
			return $this->qfa('SHOW TEBLES FROM `'.$this->database.'`');
		}
	}
	/**
	 * Preparing string for using in SQL query
	 * SQL Injection Protection
	 * @param $data
	 * @return string
	 */
	abstract function sip ($data);
	//Информация о сервере
	abstract function server ();
	/**
	 * Cloning restriction
	 */
	final function __clone () {}
	//Отключение от БД
	abstract function __destruct ();
}