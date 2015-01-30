<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

/**
 * Event class
 *
 * Provides events subscribing and dispatching
 *
 * @method static Event instance($check = false)
 */
class Event {
	use Singleton;
	/**
	 * @var callable[]
	 */
	protected $callbacks = [];
	/**
	 * @var bool
	 */
	protected $initialized = false;
	/**
	 * Add event handler
	 *
	 * @param string   $event    For example `admin/System/components/plugins/disable`
	 * @param callable $callback callable, that will be called at trigger running
	 *
	 * @return Event
	 */
	function on ($event, $callback) {
		if (!is_string($event) || !is_callable($callback)) {
			return $this;
		}
		if (!isset($this->callbacks[$event])) {
			$this->callbacks[$event] = [];
		}
		$this->callbacks[$event][] = $callback;
		return $this;
	}
	/**
	 * Remove event handler
	 *
	 * @param string        $event
	 * @param callable|null $callback If not specified - all callbacks for this event will be removed
	 *
	 * @return Event
	 */
	function off ($event, $callback = null) {
		if (!$callback) {
			unset($this->callbacks[$event]);
			return $this;
		}
		foreach ($this->callbacks[$event] as $i => $c) {
			if ($c === $callback) {
				unset($this->callbacks[$event][$i]);
			}
		}
		return $this;
	}
	/**
	 * Similar to `::on()`, but but removes handler after handling of first event
	 *
	 * @param string   $event
	 * @param callable $callback
	 *
	 * @return Event
	 */
	function once ($event, $callback) {
		if (!is_string($event) || !is_callable($callback)) {
			return $this;
		}
		if (!isset($this->callbacks[$event])) {
			$this->callbacks[$event] = [];
		}
		$this->callbacks[$event][] = function () use ($event, $callback) {
			$this->off($event, $callback);
			call_user_func_array($callback, func_get_args());
		};
		return $this;
	}
	/**
	 * Fire event
	 *
	 * After event name it is possible to specify as many arguments as needed
	 *
	 * @param string $event For example `admin/System/components/plugins/disable`
	 * @param mixed  $param1
	 * @param mixed  $_
	 *
	 * @return bool
	 */
	function fire ($event, $param1 = null, $_ = null) {
		if (!$this->initialized) {
			$this->initialize();
		}
		if (
			!isset($this->callbacks[$event]) ||
			empty($this->callbacks[$event]) ||
			!is_string($event)
		) {
			return true;
		}
		$return = true;
		if (func_num_args() > 1) {
			$arguments = array_slice(func_get_args(), 1);
			foreach ($this->callbacks[$event] as $callback) {
				$return =
					$return &&
					call_user_func_array($callback, $arguments) !== false;
			}
		} else {
			foreach ($this->callbacks[$event] as $callback) {
				$return =
					$return &&
					$callback() !== false;
			}
		}
		return $return;
	}
	/**
	 * Initialize all events handlers
	 */
	protected function initialize () {
		$modules = get_files_list(MODULES, false, 'd');
		foreach ($modules as $module) {
			_include_once(MODULES."/$module/trigger.php", false);
		}
		unset($modules, $module);
		$plugins = get_files_list(PLUGINS, false, 'd');
		if (!empty($plugins)) {
			foreach ($plugins as $plugin) {
				_include_once(PLUGINS."/$plugin/trigger.php", false);
			}
		}
		unset($plugins, $plugin);
		$this->initialized = true;
	}
}
