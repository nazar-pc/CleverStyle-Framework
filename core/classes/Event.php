<?php
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
namespace cs;
/**
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
	protected function construct () {
		foreach (glob(MODULES.'/*/events.php') as $path) {
			include $path;
		}
	}
	protected function init () {
		/**
		 * All events handlers registered before first request processing was started should be cached for further requests
		 */
		/** @noinspection PhpUndefinedFieldInspection */
		if ($this->__request_id === 1) {
			$this->callbacks_cache = $this->callbacks;
		}
		/**
		 * Starting from seconds request we'll take cache and use it as reference set of events handlers
		 */
		/** @noinspection PhpUndefinedFieldInspection */
		if ($this->__request_id > 1) {
			$this->callbacks = $this->callbacks_cache;
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
	public function on ($event, callable $callback) {
		if (!$event) {
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
	public function off ($event, $callback = null) {
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
	public function once ($event, callable $callback) {
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
	public function fire ($event, ...$arguments) {
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
}
