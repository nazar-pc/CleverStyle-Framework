<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs\Cache;
/**
 * Provides cache functionality based on Memcached.
 * Support optionally base configuration option $Core->memcached_host and $Core->memcached_port
 */
class Memcached extends _Abstract {
	protected	$memcached	= false;
	function __construct () {
		global $Core;
		if (!memcached()) {
			return;
		}
		$this->memcached	= new \Memcached(DOMAIN);
		$this->memcached->addServer($Core->memcached_host ?: '127.0.0.1', $Core->memcached_port ?: 11211);
	}
	/**
	 * Get item from cache
	 *
	 * @param string		$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool|mixed			Returns item on success of <b>false</b> on failure
	 */
	function get ($item) {
		if (!$this->memcached) {
			return false;
		}
		return $this->memcached->get(DOMAIN."/$item");
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
		if (!$this->memcached) {
			return false;
		}
		return $this->memcached->set(DOMAIN."/$item", $data);
	}
	/**
	 * Delete item from cache
	 *
	 * @param string	$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool
	 */
	function del ($item) {
		if (!$this->memcached) {
			return false;
		}
		$return	= true;
		$keys	=  $this->memcached->getAllKeys();
		if (!$keys) {
			return false;
		}
		$item	= DOMAIN."/$item";
		foreach ($keys as $element) {
			if (
				$item == $element ||
				(
					mb_strpos($element, $item) === 0 &&
					mb_substr($element, mb_strlen($item), 1) == '/'
				)
			) {
				$return	= $this->memcached->delete($element) && $return;
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
		if (!$this->memcached) {
			return false;
		}
		return $this->memcached->flush();
	}
}