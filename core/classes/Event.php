<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
/**
 * Event class
 *
 * Provides events subscribing and dispatching
 *
 * @method static $this instance($check = false)
 */
class Event {
	use
		Singleton;
	const INIT_STATE_METHOD = 'init';
	/**
	 * @var callable[][]
	 */
	protected $callbacks;
	/**
	 * @var bool
	 */
	protected $initialized;
	protected function init () {
		$this->callbacks   = [];
		$this->initialized = false;
	}
	/**
	 * Add event handler
	 *
	 * @param string   $event    For example `admin/System/components/plugins/disable`
	 * @param callable $callback Callable, that will be called at event dispatching
	 *
	 * @return Event
	 */
	function on ($event, $callback) {
		if (!$event || !is_callable($callback)) {
			return $this;
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
		if (!isset($this->callbacks[$event])) {
			return $this;
		}
		if (!$callback) {
			unset($this->callbacks[$event]);
			return $this;
		}
		$this->callbacks[$event] = array_filter(
			$this->callbacks[$event],
			function ($c) use ($callback) {
				return $c !== $callback;
			}
		);
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
		if (!$event || !is_callable($callback)) {
			return $this;
		}
		$wrapped_callback = function () use (&$wrapped_callback, $event, $callback) {
			$this->off($event, $wrapped_callback);
			call_user_func_array($callback, func_get_args());
		};
		return $this->on($event, $wrapped_callback);
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
			!$event ||
			!isset($this->callbacks[$event])
		) {
			return true;
		}
		$arguments = array_slice(func_get_args(), 1);
		foreach ($this->callbacks[$event] as $callback) {
			if (call_user_func_array($callback, $arguments) === false) {
				return false;
			}
		}
		return true;
	}
	/**
	 * Initialize all events handlers
	 */
	protected function initialize () {
		foreach ($this->events_files_paths() as $path) {
			include DIR."/$path";
		}
		$this->initialized = true;
	}
	/**
	 * @return string[]
	 */
	protected function events_files_paths () {
		return Cache::instance()->get(
			'events_files_paths',
			function () {
				$paths = [];
				foreach (get_files_list(MODULES, false, 'd', 'components/modules') as $path) {
					if (file_exists(DIR."/$path/events.php")) {
						$paths[] = "$path/events.php";
					}
				}
				foreach (get_files_list(PLUGINS, false, 'd', 'components/plugins') as $path) {
					if (file_exists(DIR."/$path/events.php")) {
						$paths[] = "$path/events.php";
					}
				}
				return $paths;
			}
		);
	}
}
