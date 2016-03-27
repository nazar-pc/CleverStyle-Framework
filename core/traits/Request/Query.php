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
	 * @param string[]|string[][] $name
	 *
	 * @return false|mixed|mixed[] Query parameter (or associative array of Query parameters) if exists or `false` otherwise (in case if `$name` is an array
	 *                             even one missing key will cause the whole thing to fail)
	 */
	function query (...$name) {
		if (count($name) === 1) {
			$name = $name[0];
		}
		/**
		 * @var string|string[] $name
		 */
		if (is_array($name)) {
			$result = [];
			foreach ($name as &$n) {
				if (!isset($this->query[$n])) {
					return false;
				}
				$result[$n] = $this->query[$n];
			}
			return $result;
		}
		/** @noinspection OffsetOperationsInspection */
		return isset($this->query[$name]) ? $this->query[$name] : false;
	}
}
