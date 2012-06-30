<?php
namespace cs;
use \h;
//For working with global system objects
class Objects {
	public	$Loaded				= [],		//Array with list of loaded objects, and information about amount of used memory
											//после их создания, и длительностью содания
			$unload_priority	= [
				'Page',
				'User',
				'Config',
				'Key',
				'db',
				'L',
				'Text',
				'Cache',
				'Core',
				'Storage',
				'Error'
			];
	private	$List				= [];
	//Добавление в список объектов для их разрушения по окончанию работы
	function add ($name) {
		$this->List[$name] = $name;
	}
	/**
	 * @param array|string     $class
	 * @param bool             $object_name
	 *
	 * @return bool|object
	 */
	function load ($class, $object_name = null) {
		if (empty($class)) {
			return false;
		} elseif (!defined('STOP') && !is_array($class)) {
			$loader = false;
			if (substr($class, 0, 1) == '_') {
				$class	= substr($class, 1);
				$loader	= true;
			}
			if ($loader || class_exists($class)) {
				if ($object_name === null) {
					$object_name = explode('\\', $class);
					$object_name = array_pop($object_name);
				}
				global $$object_name;
				if (!is_object($$object_name) || $$object_name instanceof Loader) {
					if ($loader) {
						$$object_name				= new Loader($class, $object_name);
					} else {
						$this->List[$object_name]	= $object_name;
						$$object_name				= new $class();
						$this->Loaded[$object_name]	= [microtime(true), memory_get_usage()];
					}
				}
				return $$object_name;
			} else {
				trigger_error('Class '.h::b($class, ['level' => 0]).' not exists', E_USER_ERROR);
				return false;
			}
		} elseif (!defined('STOP') && is_array($class)) {
			foreach ($class as $c) {
				if (is_array($c)) {
					$this->load($c[0], isset($c[1]) ? $c[1] : false);
				} else {
					$this->load($c);
				}
			}
		}
		return false;
	}
	//Метод уничтожения объектов
	function unload ($class) {
		if (is_array($class)) {
			foreach ($class as $c) {
				$this->unload($c);
			}
		} else {
			global $$class;
			unset($this->List[$class]);
			method_exists($$class, '__finish') && $$class->__finish();
			$$class = null;
			unset($GLOBALS[$class]);
		}
	}
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	function __clone () {}
	//При уничтожении этого объекта уничтожаются все зарегистрированные глобальные объекты,
	//проводится зачистка работы и корректное завершение
	function __finish () {
		if (isset($this->List['Index'])) {
			$this->unload('Index');
		}
		foreach ($this->List as $class) {
			if (!in_array($class, $this->unload_priority)) {
				$this->unload($class);
			}
		}
		foreach ($this->unload_priority as $class) {
			if (isset($this->List[$class])) {
				$this->unload($class);
			}
		}
		exit;
	}
}