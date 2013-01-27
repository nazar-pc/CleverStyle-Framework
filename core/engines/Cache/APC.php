<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs\Cache;
/**
 * Provides cache functionality based on APC (Alternative PHP Cache).
 */
class APC extends _Abstract {
	protected	$apc;
	function __construct () {
		$this->apc	= apc();
	}
	/**
	 * Get item from cache
	 *
	 * @param string		$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool|mixed			Returns item on success of <b>false</b> on failure
	 */
	function get ($item) {
		if (!$this->apc) {
			return false;
		}
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
		if (!$this->apc) {
			return false;
		}
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
		if (!$this->apc) {
			return false;
		}
		$item	= DOMAIN.'/'.$item;
		$return	= true;
		foreach (new \APCIterator('user') as $element) {
			if (
				$item == $element['key'] ||
				(
					mb_strpos($element['key'], $item) === 0 &&
					mb_substr($element['key'], mb_strlen($item), 1) == '/'
				)
			) {
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
		if (!$this->apc) {
			return false;
		}
		return apc_clear_cache('user');
	}
}