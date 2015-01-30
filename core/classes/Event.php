<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	cs\Event\Once_wrapper;
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
	 * @var callable[][]
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
		if (!$event || !is_callable($callback)) {
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
		if (!isset($this->callbacks[$event])) {
			return $this;
		}
		if (!$callback) {
			unset($this->callbacks[$event]);
			return $this;
		}
		$this->callbacks[$event] = array_filter($this->callbacks[$event], function ($c) use ($callback) {
			return $c !== $callback;
		});
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
		if (!isset($this->callbacks[$event])) {
			$this->callbacks[$event] = [];
		}
		$Event     = $this;
		$callback_ = new Once_wrapper(function () use ($event, $callback, $Event) {
			/**
			 * @var Once_wrapper $this
			 */
			$Event->off($event, $this);
			call_user_func_array($callback, func_get_args());
		});
		return $this->on($event, $callback_);
	}
	/**
	 * Fire event
	 *
	 * After event name it is possible to specify up to 10 arguments if needed
	 *
	 * @param string $event For example `admin/System/components/plugins/disable`
	 * @param mixed  $param1
	 * @param mixed  $_
	 *
	 * @return bool
	 */
	function fire ($event, &$param1 = null, &$_ = null, &$_3 = null, &$_4 = null, &$_5 = null, &$_6 = null, &$_7 = null, &$_8 = null, &$_9 = null, &$_10 = null) {
		if (!$this->initialized) {
			$this->initialize();
		}
		if (
			!$event ||
			!isset($this->callbacks[$event])
		) {
			return true;
		}
		foreach ($this->callbacks[$event] as $callback) {
			/**
			 * 10 arguments that can be passed by reference if needed.
			 * Dirty, but there is no possibility to pass it by reference using `func_get_args()`
			 */
			if ($callback($param1, $_, $_3, $_4, $_5, $_6, $_7, $_8, $_9, $_10) === false) {
				return false;
			}
		}
		return true;
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
