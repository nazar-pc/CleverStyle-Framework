<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Cache;
use
	cs\Core;

/**
 * Abstract class that simplifies creating cache engines without namespaces support
 *
 * This class implements methods:
 * * `::get()`
 * * `::set()`
 * * `::del()`
 * * `::clean()`
 *
 * And requires to implement simple low-level `protected` proxy-methods:
 * * `::available_internal()`
 * * `::get_internal()`
 * * `::set_internal()`
 * * `::del_internal()`
 * * `::clean_internal()`
 * * `::increment_internal()`
 *
 * The main benefit is that namespace support is provided by this class and target class only needs to implement trivial low-level methods
 */
abstract class _Abstract_with_namespace extends _Abstract {
	/**
	 * @var array
	 */
	protected $root_versions_cache = [];
	/**
	 * Whether current cache engine is available (might be `false` if necessary extension is not installed or something similar)
	 *
	 * @return bool
	 */
	abstract protected function available_internal ();
	/**
	 * @abstract
	 *
	 * @param string $item
	 *
	 * @return bool|mixed
	 */
	abstract protected function get_internal ($item);
	/**
	 * @abstract
	 *
	 * @param string $item
	 * @param mixed  $data
	 *
	 * @return bool
	 */
	abstract protected function set_internal ($item, $data);
	/**
	 * @abstract
	 *
	 * @param string $item
	 *
	 * @return bool
	 */
	abstract protected function del_internal ($item);
	/**
	 * @abstract
	 *
	 * @param string $item
	 *
	 * @return bool
	 */
	abstract protected function increment_internal ($item);
	/**
	 * @abstract
	 *
	 * @return bool
	 */
	abstract protected function clean_internal ();
	/**
	 * @inheritdoc
	 */
	function get ($item) {
		if (!$this->available_internal()) {
			return false;
		}
		return $this->get_internal(
			$this->namespaces_imitation($item)
		);
	}
	/**
	 * @inheritdoc
	 */
	function set ($item, $data) {
		if (!$this->available_internal()) {
			return false;
		}
		return $this->set_internal(
			$this->namespaces_imitation($item),
			$data
		);
	}
	/**
	 * @inheritdoc
	 */
	function del ($item) {
		if (!$this->available_internal()) {
			return false;
		}
		$domain = $this->domain();
		$this->del_internal($this->namespaces_imitation($item));
		$this->increment_internal("/$domain/$item");
		unset($this->root_versions_cache["/$domain/$item"]);
		return true;
	}
	/**
	 * @return string
	 */
	protected function domain () {
		static $domain;
		if (!isset($domain)) {
			$domain = Core::instance()->domain;
		}
		return $domain;
	}
	/**
	 * @inheritdoc
	 */
	function clean () {
		if (!$this->available_internal()) {
			return false;
		}
		return $this->clean_internal();
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
		$domain = $this->domain();
		$exploded = explode('/', $item);
		$count    = count($exploded);
		if ($count > 1) {
			$item_path = $domain;
			--$count;
			/** @noinspection ForeachInvariantsInspection */
			for ($i = 0; $i < $count; ++$i) {
				$item_path .= '/'.$exploded[$i];
				if (!$i && isset($this->root_versions_cache["/$item_path"])) {
					$exploded[$i] .= '/'.$this->root_versions_cache["/$item_path"];
					continue;
				}
				$version = $this->get_internal("/$item_path");
				if ($version === false) {
					$this->set_internal("/$item_path", 0);
					$version = 0;
				}
				$exploded[$i] .= "/$version";
				if (!$i) {
					$this->root_versions_cache["/$item_path"] = $version;
				}
			}
			return "$domain/".implode('/', $exploded);
		}
		return "$domain/$item";
	}
}
