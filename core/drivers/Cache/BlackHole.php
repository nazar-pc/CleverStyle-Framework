<?php
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
namespace cs\Cache;
/**
 * It works like black hole - i.e. does nothing. May be suitable for debugging purposes, when cache saving should be disabled, but debug mode is disabled too
 */
class BlackHole extends _Abstract {
	/**
	 * @inheritdoc
	 */
	public function get ($item) {
		return false;
	}
	/**
	 * @inheritdoc
	 */
	public function set ($item, $data) {
		return true;
	}
	/**
	 * @inheritdoc
	 */
	public function del ($item) {
		return true;
	}
	/**
	 * @inheritdoc
	 */
	public function clean () {
		return true;
	}
}
