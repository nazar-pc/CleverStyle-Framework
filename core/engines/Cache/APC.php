<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Cache;
/**
 * Provides cache functionality based on APCu
 */
class APC extends _Abstract_with_namespace {
	/**
	 * @inheritdoc
	 */
	protected function available_internal () {
		return function_exists('apc_fetch');
	}
	/**
	 * @inheritdoc
	 */
	protected function get_internal ($item) {
		return apc_fetch($item);
	}
	/**
	 * @inheritdoc
	 */
	protected function set_internal ($item, $data) {
		return apc_store($item, $data);
	}
	/**
	 * @inheritdoc
	 */
	protected function del_internal ($item) {
		return apc_delete($item);
	}
	/**
	 * @inheritdoc
	 */
	protected function increment_internal ($item) {
		return apc_inc($item);
	}
	/**
	 * @inheritdoc
	 */
	protected function clean_internal () {
		return apc_clear_cache();
	}
}
