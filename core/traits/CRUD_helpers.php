<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
/**
 * CRUD trait extended with useful additional methods, for example, for searching
 *
 * Might me used instead of regular CRUD trait
 */
trait CRUD_helpers {
	use
		CRUD;
	/**
	 * Generic search
	 *
	 * @param mixed[] $search_parameters Array in form [attribute => value];
	 *                                   if `total_count => 1` element is present - total number of found rows will be returned instead of rows themselves
	 * @param int     $page
	 * @param int     $count
	 * @param string  $order_by
	 * @param bool    $asc
	 *
	 * @return array|false|int
	 */
	protected function search ($search_parameters = [], $page = 1, $count = 100, $order_by = 'id', $asc = false) {
		if (!isset($this->data_model[$order_by])) {
			return false;
		}
		$where  = [];
		$params = [];
		foreach ($search_parameters as $key => $details) {
			if (isset($this->data_model[$key])) {
				$where[]  = "`$key` = '%s'";
				$params[] = $details;
			}
		}
		unset($key, $details);
		$where = $where ? 'WHERE '.implode(' AND ', $where) : '';
		if (isset($search_parameters['total_count']) && $search_parameters['total_count']) {
			return $this->db()->qfs(
				[
					"SELECT COUNT(`id`)
					FROM `$this->table`
					$where",
					$params
				]
			);
		} else {
			$params[] = ($page - 1) * $count;
			$params[] = $count;
			$asc      = $asc ? 'ASC' : 'DESC';
			return $this->db()->qfas(
				[
					"SELECT `id`
					FROM `$this->table`
					$where
					ORDER BY `$order_by` $asc
					LIMIT %d, %d",
					$params
				]
			);
		}
	}
}
