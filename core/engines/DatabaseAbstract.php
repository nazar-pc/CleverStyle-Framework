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
								'id'		=> ''
							),
				$queries	= array(				//Массив для хранения данных всех выполненых запросов
								'num'		=> '',
								'time'		=> [],
								'text'		=> [],
								'resource'	=> []
							),
				$connecting_time;					//Время соединения
	protected	$id;								//Указатель на соединение с БД
	
	//Создание подключения
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
	/**
	 * @abstract
	 * @param array|string $query
	 * @return resource
	 */
	abstract function q ($query);
	//Подсчёт количества строк
	/**
	 * @abstract
	 * @param bool|resource $query_resource
	 *
	 * @return int|bool
	 */
	abstract function n ($query_resource = false);
	//Получение результатов
	/**
	 * @abstract
	 *
	 * @param bool|resource $query_resource
	 * @param bool          $array
	 * @param bool|string   $one_column
	 *
	 * @return array|bool
	 */
	abstract function f ($query_resource = false, $one_column = false, $array = false);
	//Упрощенный интерфейс метода для получения результата в виде массива
	/**
	 * @param bool|resource $query_resource
	 * @param bool|string   $one_column
	 *
	 * @return array|bool
	 */
	function fa ($query_resource = false, $one_column = false) {
		return $this->f($query_resource, $one_column, true);
	}
	//Запрос с получением результатов, результаты запросов кешируются при соответствующей настройке сайта
	/**
	 * @param string $query
	 * @param bool|string   $one_column
	 *
	 * @return array|bool
	 */
	function qf ($query = '', $one_column = false) {
		if (!$query) {
			return false;
		}
		return $this->f($this->q($query), $one_column, false);
	}
	//Упрощенный интерфейс метода выполнения запроса с получением результата в виде массива
	/**
	 * @param string        $query
	 * @param bool|string   $one_column
	 *
	 * @return array|bool
	 */
	function qfa ($query = '', $one_column = false) {
		if (!$query) {
			return false;
		}
		return $this->f($this->q($query), $one_column, true);
	}
	/**
	 * Get id of last inserted row
	 * @abstract
	 *
	 * @return int
	 */
	abstract function id ();
	//Очистка результатов запроса
	/**
	 * @abstract
	 * @param bool|resource $query_resource
	 * @return bool
	 */
	abstract function free ($query_resource = false);
	//Получение списка полей таблицы
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
	abstract function s ($data);
	//Информация о сервере
	abstract function server ();
	/**
	 * Cloning restriction
	 */
	final function __clone () {}
	//Отключение от БД
	abstract function __destruct ();
}