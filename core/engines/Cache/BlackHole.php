<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Cache;
/**
 * It works like black hole - i.e. does nothing. May be suitable for debugging purposes, when cache saving should be disabled, but debug mode is disabled too
 */
class BlackHole extends _Abstract {
	/**
	 * @inheritdoc
	 */
	function get ($item) {
		return false;
	}
	/**
	 * @inheritdoc
	 */
	function set ($item, $data) {
		return true;
	}
	/**
	 * @inheritdoc
	 */
	function del ($item) {
		return true;
	}
	/**
	 * @inheritdoc
	 */
	function clean () {
		return true;
	}
}
