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
	 * @param mixed[] $search_parameters Array in form [attribute => value];<br>
	 *                                   Or [attribute => [value1, value2, value3]];<br>
	 *                                   Or [attribute => [from => a, to => b]];<br>
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
			if (!isset($this->data_model[$key])) {
				continue;
			}
			if (!is_scalar($details)) {
				$where[]  = "`$key` = '%s'";
				$params[] = $details;
			} elseif (is_array($details) && $details) {
				if (is_array_indexed($details)) {
					$where_tmp = [];
					foreach ($details as $d) {
						/** @noinspection DisconnectedForeachInstructionInspection */
						$where_tmp[] = "`$key` = '%s'";
						$params[]    = $d;
					}
					$where[] = '('.implode(' OR ', $where_tmp).')';
					unset($where_tmp, $d);
					continue;
				}
				if (isset($details['from'])) {
					$where[]  = "`$key` => '%s'";
					$params[] = $details['from'];
				}
				if (isset($details['to'])) {
					$where[]  = "`$key` <= '%s'";
					$params[] = $details['to'];
				}
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
		}
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
