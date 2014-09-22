<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;

/**
 * Trigger class
 *
 * Provides triggers registering and running.
 *
 * @method static Trigger instance($check = false)
 */
class Trigger {
	use Singleton;
	/**
	 * @var callable[]
	 */
	protected	$triggers		= [];
	/**
	 * @var bool
	 */
	protected	$initialized	= false;
	/**
	 * Registration of triggers for actions
	 * @param string	$trigger	For example <i>admin/System/components/plugins/disable</i>
	 * @param callable	$callback	callable, that will be called at trigger running
	 * @param bool		$replace	If <i>true</i> - existing closures for this trigger will be removed and replaced with specified one
	 *
	 * @return Trigger
	 */
	function register ($trigger, $callback, $replace = false) {
		if (!is_string($trigger) || !is_callable($callback)) {
			return $this;
		}
		if (!isset($this->triggers[$trigger]) || $replace) {
			$this->triggers[$trigger]	= [];
		}
		$this->triggers[$trigger][]	= $callback;
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
			$modules = get_files_list(MODULES, false, 'd');
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
		foreach ($this->triggers[$trigger] as $callback) {
			if ($data === null) {
				$return = $return && ($callback() === false ? false : true);
			} else {
				$return = $return && ($callback($data) === false ? false : true);
			}
		}
		return $return;
	}
}
