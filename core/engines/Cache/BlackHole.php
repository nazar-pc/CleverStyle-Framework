<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs\Cache;
/**
 * It works like black hole - i.e. does nothing. May be suitable for debugging purposes, when cache saving should be disabled, but debug mode is disabled too
 */
class BlackHole extends _Abstract {
	function get ($item) {
		return false;
	}
	function set ($item, $data) {
		return false;
	}
	function del ($item) {
		return true;
	}
	function clean () {
		return true;
	}
}
