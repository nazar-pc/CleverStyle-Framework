<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;

/**
 * Trigger class
 *
 * Provides triggers registering and running.
 *
 * @deprecated Use `cs\Event` instead
 * @todo Remove in future versions
 *
 * @method static Trigger instance($check = false)
 */
class Trigger {
	use Singleton;
	/**
	 * Registration of triggers for actions
	 *
	 * @deprecated Use `cs\Event::on()` instead
	 *
	 * @param string	$trigger	For example <i>admin/System/components/plugins/disable</i>
	 * @param callable	$callback	callable, that will be called at trigger running
	 * @param bool		$replace	If <i>true</i> - existing closures for this trigger will be removed and replaced with specified one
	 *
	 * @return Trigger
	 */
	function register ($trigger, $callback, $replace = false) {
		$Event = Event::instance();
		if ($replace) {
			$Event->off($trigger);
		}
		$Event->on($trigger, $callback);
		return $this;
	}
	/**
	 * Running triggers for some actions
	 *
	 * @deprecatedUse `cs\Event::fire()` instead
	 *
	 * @param string	$trigger	For example <i>admin/System/components/plugins/disable</i>
	 * @param mixed		$data		For example ['name'	=> <i>plugin_name</i>]
	 *
	 * @return bool
	 */
	function run ($trigger, $data = null) {
		return Event::instance()->fire($trigger, $data);
	}
}
