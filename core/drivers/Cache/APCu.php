<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Cache;
/**
 * Provides cache functionality based on APCu
 */
class APCu extends _Abstract_with_namespace {
	/**
	 * @inheritdoc
	 */
	protected function available_internal () {
		return function_exists('apcu_fetch');
	}
	/**
	 * @inheritdoc
	 */
	protected function get_internal ($item) {
		return apcu_fetch($item);
	}
	/**
	 * @inheritdoc
	 */
	protected function set_internal ($item, $data) {
		return apcu_store($item, $data);
	}
	/**
	 * @inheritdoc
	 */
	protected function del_internal ($item) {
		return apcu_delete($item);
	}
	/**
	 * @inheritdoc
	 */
	protected function increment_internal ($item) {
		return apcu_inc($item);
	}
	/**
	 * @inheritdoc
	 */
	protected function clean_internal () {
		return apcu_clear_cache();
	}
}
