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
		return apc_fetch(
			$this->namespaces_imitation(DOMAIN."/$item")
		);
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
		return apc_store(
			$this->namespaces_imitation(DOMAIN."/$item"),
			$data
		);
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
		$item	= DOMAIN."/$item";
		apc_delete($this->namespaces_imitation($item));
		if ($pos = mb_strrpos($item, '/')) {
			apc_inc(mb_substr($item, 0, $pos));
		}
		return true;
	}
	/**
	 * Namespaces imitation
	 *
	 * Accepts item as parameter, returns item string that uses namespaces (needed for fast deletion of large branches of cache elements).
	 *
	 * @param $item
	 *
	 * @return string
	 */
	protected function namespaces_imitation ($item) {
		static $root_items_cache = [];
		$exploded	= explode('/', $item);
		$count		= count($exploded);
		if ($count > 1) {
			$item_path	= DOMAIN;
			--$count;
			for ($i = 0; $i < $count; ++$i) {
				$item_path	.= '/'.$exploded[$i];
				if (!$i && isset($root_items_cache[$item_path])) {
					$exploded[$i]	.= $root_items_cache[$item_path];
					continue;
				}
				$version	= apc_fetch($item_path);
				if ($version === false) {
					apc_store($item_path, 0);
					$version	= 0;
				}
				$exploded[$i]	.= $version;
				if (!$i) {
					$root_items_cache[$item_path]	= $version;
				}
			}
			return DOMAIN.'/'.implode('/', $exploded);
		}
		return DOMAIN."/$item";
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