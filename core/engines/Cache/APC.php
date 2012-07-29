<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs\Cache;
/**
 * Provides cache functionality based on APC (Alternative PHP Cache).
 */
class APC extends _Abstract {
	function __construct () {
		global $Core;
		$this->cache_size = $Core->config('cache_size')*1048576;
	}
	/**
	 * Get item from cache
	 *
	 * @param string		$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool|mixed			Returns item on success of <b>false</b> on failure
	 */
	function get ($item) {
		return apc_fetch(DOMAIN.'/'.$item);
	}
	/**
	 * Put or change data of cache item
	 *
	 * @param string	$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 * @param mixed		$data
	 *
	 * @return bool
	 */
	function set ($item, $data) {
		return apc_store(DOMAIN.'/'.$item, $data);
	}
	/**
	 * Delete item from cache
	 *
	 * @param string	$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool
	 */
	function del ($item) {
		$item	= DOMAIN.'/'.$item;
		$return	= true;
		foreach (new \APCIterator('user') as $element) {
			if (strpos($element['key'], $item) === 0) {
				$return	= apc_delete($element['key']) && $return;
			}
		}
		return $return;
	}
	/**
	 * Clean cache by deleting all items
	 *
	 * @return bool
	 */
	function clean () {
		return apc_clear_cache('user');
	}
}