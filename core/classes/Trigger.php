<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			Closure;
/**
 * Core class.
 * Provides loading of base system configuration, encryption, API requests sending.
 */
class Trigger {
	use Singleton;

	/**
	 * @var Closure[]
	 */
	protected	$triggers		= [];
	/**
	 * @var bool
	 */
	protected	$initialized	= false;
	/**
	 * Registration of triggers for actions

	 *
*@param string	$trigger	For example <i>admin/System/components/plugins/disable</i>
	 * @param Closure	$closure	Closure, that will be called at trigger running
	 * @param bool		$replace	If <i>true</i> - existing closures for this trigger will be removed and replaced with specified one

	 *
*@return Trigger
	 */
	function register ($trigger, $closure, $replace = false) {
		if (!is_string($trigger) || !($closure instanceof Closure)) {
			return $this;
		}
		if (!isset($this->triggers[$trigger]) || $replace) {
			$this->triggers[$trigger]	= [];
		}
		$this->triggers[$trigger][]	= $closure;
		return $this;
	}
	/**
	 * Running triggers for some actions
	 *
	 * @param string	$trigger	For example <i>admin/System/components/plugins/disable</i>
	 * @param mixed		$data		For example ['name'	=> <i>plugin_name</i>]
	 *
	 * @return bool
	 */
	function run ($trigger, $data = null) {
		if (!is_string($trigger)) {
			return true;
		}
		if (!$this->initialized) {
			$modules = array_keys(Config::instance()->components['modules']);
			foreach ($modules as $module) {
				_include_once(MODULES.'/'.$module.'/trigger.php', false);
			}
			unset($modules, $module);
			$plugins = get_files_list(PLUGINS, false, 'd');
			if (!empty($plugins)) {
				foreach ($plugins as $plugin) {
					_include_once(PLUGINS.'/'.$plugin.'/trigger.php', false);
				}
			}
			unset($plugins, $plugin);
			$this->initialized = true;
		}
		if (!isset($this->triggers[$trigger]) || empty($this->triggers[$trigger])) {
			return true;
		}
		$return	= true;
		foreach ($this->triggers[$trigger] as $closure) {
			if ($data === null) {
				$return = $return && ($closure() === false ? false : true);
			} else {
				$return = $return && ($closure($data) === false ? false : true);
			}
		}
		return $return;
	}
}