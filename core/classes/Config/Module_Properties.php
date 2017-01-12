<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Config;
use
	cs\Config;
/**
 * Class for getting of db and storage configuration of module
 */
class Module_Properties {
	const ENABLED     = 1;
	const DISABLED    = 0;
	const UNINSTALLED = -1;
	/**
	 * @var array
	 */
	protected $module_data = [];
	/**
	 * @var string
	 */
	protected $module;
	/**
	 * Creating of object and saving module data inside
	 *
	 * @param array  $module_data
	 * @param string $module
	 */
	public function __construct ($module_data, $module) {
		$this->module_data = $module_data;
		$this->module      = $module;
	}
	/**
	 * Whether module is enabled
	 *
	 * @return bool
	 */
	public function enabled () {
		return $this->module_data['active'] == self::ENABLED;
	}
	/**
	 * Whether module is disabled
	 *
	 * @return bool
	 */
	public function disabled () {
		return $this->module_data['active'] == self::DISABLED;
	}
	/**
	 * Whether module is installed
	 *
	 * @return bool
	 */
	public function installed () {
		return $this->module_data['active'] != self::UNINSTALLED;
	}
	/**
	 * Whether module is uninstalled
	 *
	 * @return bool
	 */
	public function uninstalled () {
		return $this->module_data['active'] == self::UNINSTALLED;
	}
	/**
	 * Get db id by name
	 *
	 * @param string $db_name
	 *
	 * @return int
	 */
	public function db ($db_name) {
		return $this->module_data['db'][$db_name];
	}
	/**
	 * Get storage id by name
	 *
	 * @param string $storage_name
	 *
	 * @return int
	 */
	public function storage ($storage_name) {
		return $this->module_data['storage'][$storage_name];
	}
	/**
	 * Get data item of module configuration
	 *
	 * @param string $item
	 *
	 * @return false|mixed
	 */
	public function __get ($item) {
		return $this->get($item);
	}
	/**
	 * Set data item of module configuration (only for admin)
	 *
	 * @param string $item
	 * @param mixed  $value
	 */
	public function __set ($item, $value) {
		$this->set_internal($item, $value);
	}
	/**
	 * Get data item (or array of items) of module configuration
	 *
	 * @param string|string[] $item
	 *
	 * @return false|mixed|mixed[]
	 */
	public function get ($item) {
		if (is_array($item)) {
			$result = [];
			foreach ($item as $i) {
				$result[$i] = $this->get($i);
			}
			return $result;
		} elseif (isset($this->module_data['data'], $this->module_data['data'][$item])) {
			return $this->module_data['data'][$item];
		} else {
			return false;
		}
	}
	/**
	 * Set data item (or array of items) of module configuration (only for admin)
	 *
	 * @param array|string $item
	 * @param mixed|null   $value
	 *
	 * @return bool
	 *
	 * @throws \cs\ExitException
	 */
	public function set ($item, $value = null) {
		if (is_array($item)) {
			/** @noinspection SuspiciousLoopInspection */
			foreach ($item as $i => $value) {
				$this->set_internal($i, $value, false);
			}
			return Config::instance()->save();
		} else {
			return $this->set_internal($item, $value);
		}
	}
	protected function set_internal ($item, $value, $save = true) {
		$Config      = Config::instance();
		$module_data = &$Config->components['modules'][$this->module];
		if (!isset($module_data['data'])) {
			$module_data['data'] = [];
		}
		$module_data['data'][$item] = $value;
		$this->module_data          = $module_data;
		if ($save) {
			return $Config->save();
		}
		return true;
	}
}
