<?php
/**
 * @package   CleverStyle Framework
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
	protected $callbacks = [];
	/**
	 * @var callable[][]
	 */
	protected $callbacks_cache;
	protected function init () {
		/** @noinspection PhpUndefinedFieldInspection */
		if ($this->__request_id > 1) {
			$this->callbacks = [];
		}
	}
	/**
	 * Add event handler
	 *
	 * @param string   $event    For example `admin/System/modules/disable`
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
		$wrapped_callback = function (...$arguments) use (&$wrapped_callback, $event, $callback) {
			$this->off($event, $wrapped_callback);
			return $callback(...$arguments);
		};
		return $this->on($event, $wrapped_callback);
	}
	/**
	 * Fire event
	 *
	 * After event name it is possible to specify as many arguments as needed
	 *
	 * @param string  $event For example `admin/System/modules/disable`
	 * @param mixed[] $arguments
	 *
	 * @return bool
	 */
	function fire ($event, ...$arguments) {
		$this->ensure_events_registered();
		if (
			!$event ||
			!isset($this->callbacks[$event])
		) {
			return true;
		}
		foreach ($this->callbacks[$event] as $callback) {
			if ($callback(...$arguments) === false) {
				return false;
			}
		}
		return true;
	}
	/**
	 * Before firing events we need to ensure that events callbacks were registered
	 */
	protected function ensure_events_registered () {
		if (!$this->callbacks_cache) {
			$this->register_events();
			$this->callbacks_cache = $this->callbacks;
		} elseif (!$this->callbacks) {
			$this->callbacks = $this->callbacks_cache;
		}
	}
	/**
	 * Initialize all events handlers
	 */
	protected function register_events () {
		foreach ($this->events_files_paths() as $path) {
			include $path;
		}
	}
	/**
	 * @return string[]
	 */
	protected function events_files_paths () {
		$paths = [];
		foreach (get_files_list(MODULES, false, 'd', true) as $path) {
			if (file_exists("$path/events.php")) {
				$paths[] = "$path/events.php";
			}
		}
		return $paths;
	}
}
