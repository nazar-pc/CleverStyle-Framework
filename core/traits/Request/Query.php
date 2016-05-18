<?php
/**
 * @package   CleverStyle Framework
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
	 * @return mixed|mixed[]|null Query parameter (or associative array of Query parameters) if exists or `null` otherwise (in case if `$name` is an array
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
				if (!array_key_exists($n, $this->query)) {
					return null;
				}
				$result[$n] = $this->query[$n];
			}
			return $result;
		}
		/** @noinspection OffsetOperationsInspection */
		return @$this->query[$name];
	}
}
