<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;

trait Query {
	/**
	 * Query array, similar to `$_GET`
	 *
	 * @var array
	 */
	public $query;
	/**
	 * @param array $query Typically `$_GET`
	 */
	function init_query ($query = []) {
		$this->query = $query;
	}
	/**
	 * Get query parameter by name
	 *
	 * @param string|string[] $name
	 *
	 * @return false|mixed|mixed[] Query parameter content if exists or `false` otherwise
	 */
	function query ($name) {
		if (is_array($name)) {
			foreach ($name as &$n) {
				if (!isset($this->query[$n])) {
					return false;
				}
				$n = $this->query[$n];
			}
			return $name;
		}
		/** @noinspection OffsetOperationsInspection */
		return isset($this->query[$name]) ? $this->query[$name] : false;
	}
}
