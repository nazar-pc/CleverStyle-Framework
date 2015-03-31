<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Cache;
/**
 * Provides cache functionality based on APC (Alternative PHP Cache).
 */
class APC extends _Abstract {
	protected $apc;
	protected $root_versions_cache = [];
	function __construct () {
		$this->apc = extension_loaded('apc');
	}
	/**
	 * @inheritdoc
	 */
	function get ($item) {
		if (!$this->apc) {
			return false;
		}
		return apc_fetch(
			$this->namespaces_imitation($item)
		);
	}
	/**
	 * @inheritdoc
	 */
	function set ($item, $data) {
		if (!$this->apc) {
			return false;
		}
		return apc_store(
			$this->namespaces_imitation($item),
			$data
		);
	}
	/**
	 * @inheritdoc
	 */
	function del ($item) {
		if (!$this->apc) {
			return false;
		}
		apc_delete($this->namespaces_imitation($item));
		apc_inc('/'.DOMAIN."/$item");
		unset($this->root_versions_cache['/'.DOMAIN."/$item"]);
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
		$exploded = explode('/', $item);
		$count    = count($exploded);
		if ($count > 1) {
			$item_path = DOMAIN;
			--$count;
			for ($i = 0; $i < $count; ++$i) {
				$item_path .= '/'.$exploded[$i];
				if (!$i && isset($this->root_versions_cache["/$item_path"])) {
					$exploded[$i] .= '/'.$this->root_versions_cache["/$item_path"];
					continue;
				}
				$version = apc_fetch("/$item_path");
				if ($version === false) {
					apc_store("/$item_path", 0);
					$version = 0;
				}
				$exploded[$i] .= "/$version";
				if (!$i) {
					$this->root_versions_cache["/$item_path"] = $version;
				}
			}
			return DOMAIN.'/'.implode('/', $exploded);
		}
		return DOMAIN."/$item";
	}
	/**
	 * @inheritdoc
	 */
	function clean () {
		if (!$this->apc) {
			return false;
		}
		return version_compare(PHP_VERSION, '5.5', '>=') ? apc_clear_cache() : apc_clear_cache('user');
	}
}
