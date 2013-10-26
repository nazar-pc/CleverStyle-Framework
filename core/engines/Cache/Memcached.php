<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\Cache;
use			cs\Core;
/**
 * Provides cache functionality based on Memcached.
 * Support optionally base configuration option Core::instance()->memcached_host and Core::instance()->memcached_port
 */
class Memcached extends _Abstract {
	protected	$memcached	= false;
	function __construct () {
		if (!memcached()) {
			return;
		}
		$this->memcached	= new \Memcached(DOMAIN);
		$Core				= Core::instance();
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
		return $this->memcached->get(
			$this->namespaces_imitation($item)
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
		if (!$this->memcached) {
			return false;
		}
		return $this->memcached->set(
			$this->namespaces_imitation($item),
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
		if (!$this->memcached) {
			return false;
		}
		$this->memcached->delete($this->namespaces_imitation($item));
		if ($pos = mb_strrpos($item, '/')) {
			$this->memcached->increment(mb_substr($item, 0, $pos));
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
		$memcached	= $this->memcached;
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
				$version	= $memcached->get($item_path);
				if ($version === false) {
					$memcached->set($item_path, 0);
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
		if (!$this->memcached) {
			return false;
		}
		return $this->memcached->flush();
	}
	/**
	 * Close connections to memcached servers
	 */
	function __destruct () {
		if ($this->memcached) {
			$this->memcached->quit();
		}
	}
}