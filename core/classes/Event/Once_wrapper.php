<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Event;
/**
 * Event callback wrapper for `cs\Event::once()` method, modifies `$this` inside closure to instance of this object
 */
class Once_wrapper {
	/**
	 * @var \Closure
	 */
	protected $callback;
	/**
	 * @param \Closure $callback
	 */
	function __construct ($callback) {
		$this->callback = $callback->bindTo($this);
	}
	function __invoke () {
		return call_user_func_array($this->callback, func_get_args());
	}
}
