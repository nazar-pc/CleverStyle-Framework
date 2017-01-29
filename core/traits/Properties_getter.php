<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

trait Properties_getter {
	/**
	 * Get items for specified property (can get single property or array of properties)
	 *
	 * @param string              $property
	 * @param string[]|string[][] $items Typically obtained as `...$items`
	 *
	 * @return mixed|mixed[]|null Property items (or associative array of items) if exists or `null` otherwise (in case if `$item` is an array even one
	 *                            missing key will cause the whole thing to fail)
	 */
	protected function get_property_items ($property, $items) {
		if (count($items) === 1) {
			$items = $items[0];
		}
		$property = $this->$property;
		/**
		 * @var string|string[] $items
		 */
		if (is_array($items)) {
			$result = [];
			foreach ($items as &$i) {
				if (!array_key_exists($i, $property)) {
					return null;
				}
				$result[$i] = $property[$i];
			}
			return $result;
		}
		return @$property[$items];
	}
}
