<?php
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
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
	 *                                   Or [attribute => [...]] in case of joined tables, where ... is any of three constructions mentioned above;<br>
	 *                                   if `total_count => true` element is present - total number of found rows will be returned instead of rows themselves
	 * @param int     $page
	 * @param int     $count
	 * @param string  $order_by
	 * @param bool    $asc
	 *
	 * @return false|int|int[]|string[] Array of `id` or number of elements
	 */
	protected function search ($search_parameters = [], $page = 1, $count = 100, $order_by = 'id', $asc = false) {
		$joins       = '';
		$join_params = [];
		$join_index  = 0;
		$where       = [];
		$params      = [];
		/**
		 * @var $key string
		 */
		foreach ($search_parameters as $key => $details) {
			if (isset($this->data_model[$key])) {
				$this->search_process_parameter($this->data_model[$key], $key, $details, $where, $params, $joins, $join_params, $join_index);
			}
		}
		return $this->search_do('t', @$search_parameters['total_count'], $where, $params, $joins, $join_params, $page, $count, $order_by, $asc);
	}
	/**
	 * @param mixed    $data_model
	 * @param string   $key
	 * @param string   $details
	 * @param string[] $where
	 * @param array    $params
	 * @param string   $joins
	 * @param array    $join_params
	 * @param int      $join_index
	 */
	private function search_process_parameter ($data_model, $key, $details, &$where, &$params, &$joins, &$join_params, &$join_index) {
		if (isset($data_model['data_model'])) {
			$this->search_conditions_join_table('t', $key, $details, $joins, $join_params, $join_index);
		} else {
			if (strpos($data_model, 'ml:') === 0) {
				list($where_local, $params_local) = $this->search_conditions_multilingual('t', $key, $details);
			} else {
				list($where_local, $params_local) = $this->search_conditions('t', $key, $details);
			}
			if ($where_local) {
				$where[] = $where_local;
				array_push($params, ...$params_local);
			}
		}
	}
	/**
	 * @param string   $table_alias
	 * @param bool     $total_count
	 * @param string[] $where
	 * @param array    $params
	 * @param string   $joins
	 * @param array    $join_params
	 * @param int      $page
	 * @param int      $count
	 * @param string   $order_by
	 * @param bool     $asc
	 *
	 * @return false|int|int[]|string[]
	 */
	private function search_do ($table_alias, $total_count, $where, $params, $joins, $join_params, $page, $count, $order_by, $asc) {
		$first_column = array_keys($this->data_model)[0];
		$where        = $where ? 'WHERE '.implode(' AND ', $where) : '';
		if ($total_count) {
			return (int)$this->db()->qfs(
				"SELECT COUNT(*) FROM (
					SELECT `$table_alias`.`$first_column`
					FROM `$this->table` AS `$table_alias`
					$joins
					$where
					GROUP BY `$table_alias`.`$first_column`
				) AS `count`",
				array_merge($join_params, $params)
			);
		}
		$params[] = $count;
		$params[] = ($page - 1) * $count;
		$group_by = $this->search_group_by($table_alias, $order_by, $joins, $join_index);
		$order_by = $this->search_order_by($group_by, $asc);
		$return   = $this->db()->qfas(
			"SELECT `$table_alias`.`$first_column`
			FROM `$this->table` AS `$table_alias`
			$joins
			$where
			GROUP BY `$table_alias`.`$first_column`, $group_by
			ORDER BY $order_by
			LIMIT ? OFFSET ?",
			array_merge($join_params, $params)
		);
		return $this->read_field_post_processing($return, array_values($this->data_model)[0]);
	}
	/**
	 * @param string $table_alias
	 * @param string $key
	 * @param array  $details
	 *
	 * @return array First element is string `where` clause, second is an array of parameters
	 */
	private function search_conditions ($table_alias, $key, $details) {
		if (is_scalar($details)) {
			return ["`$table_alias`.`$key` = ?", [$details]];
		}
		if (is_array($details) && $details) {
			$where  = [];
			$params = [];
			if (is_array_indexed($details)) {
				foreach ($details as $d) {
					$where[]  = "`$table_alias`.`$key` = ?";
					$params[] = $d;
				}
				$where = implode(' OR ', $where);
				return ["($where)", $params];
			}
			if (isset($details['from'])) {
				$where[]  = "`$table_alias`.`$key` >= ?";
				$params[] = $details['from'];
			}
			if (isset($details['to'])) {
				$where[]  = "`$table_alias`.`$key` <= ?";
				$params[] = $details['to'];
			}
			return [implode(' AND ', $where), $params];
		}
		return ['', []];
	}
	/**
	 * @param string $table_alias
	 * @param string $key
	 * @param array  $details
	 *
	 * @return array First element is string `where` clause, second is an array of parameters
	 */
	private function search_conditions_multilingual ($table_alias, $key, $details) {
		list($where1, $params1) = $this->search_conditions($table_alias, $key, $details);
		if ($where1) {
			list($where2, $params2) = $this->search_conditions('td', 'text', $details);
			return [
				"($where1 OR `$table_alias`.`$key` IN (SELECT `td`.`id_` FROM `[prefix]texts_data` AS `td` WHERE $where2))",
				array_merge($params1, $params2)
			];
		}
		return ['', []];
	}
	/**
	 * @param string           $table_alias
	 * @param string           $key
	 * @param array|int|string $details
	 * @param string           $joins
	 * @param array            $join_params
	 * @param int              $join_index
	 */
	private function search_conditions_join_table ($table_alias, $key, $details, &$joins, &$join_params, &$join_index) {
		$data_model        = $this->data_model[$key];
		$first_column      = array_keys($this->data_model)[0];
		$first_column_join = array_keys($data_model['data_model'])[0];
		if (is_scalar($details)) {
			$details = [
				array_keys($data_model['data_model'])[1] => $details
			];
		}
		++$join_index;
		$joins .=
			"INNER JOIN `{$this->table}_$key` AS `j$join_index`
			ON
				`$table_alias`.`$first_column` = `j$join_index`.`$first_column_join`";
		$language_field = isset($data_model['language_field']) ? $data_model['language_field'] : false;
		/** @noinspection ForeachSourceInspection */
		foreach ($details as $field => $value) {
			if ($language_field === $field) {
				continue;
			}
			list($where, $params) = $this->search_conditions("j$join_index", $field, $value);
			if ($where) {
				$joins .= " AND $where";
				array_push($join_params, ...$params);
			}
		}
		if ($language_field) {
			$clang = Language::instance()->clang;
			$joins .=
				" AND
				(
					`j$join_index`.`lang`	= '$clang' OR
					`j$join_index`.`lang`	= ''
				)";
		}
		$joins .= "\n";
	}
	/**
	 * @param string $table_alias
	 * @param string $order_by
	 * @param string $joins
	 * @param int    $join_index
	 *
	 * @return string
	 */
	private function search_group_by ($table_alias, $order_by, &$joins, &$join_index) {
		$order_by     = explode(':', $order_by);
		$first_column = array_keys($this->data_model)[0];
		if (!isset($this->data_model[$order_by[0]])) {
			/**
			 * Non-existing field
			 */
			$order_by = [$first_column];
		}
		$model = $this->data_model[$order_by[0]];
		if (isset($order_by[1])) {
			/**
			 * Non-existing field in joined table
			 */
			if (!isset($model['data_model'][$order_by[1]])) {
				$order_by = [$first_column];
				$model    = $this->data_model[$order_by[0]];
			}
		} elseif (is_array($model) && isset($model['data_model'])) {
			/**
			 * Default field in joined table
			 */
			$order_by[1] = array_keys($model['data_model'])[1];
		}
		if (isset($order_by[1])) {
			++$join_index;
			$first_column_join = array_keys($model['data_model'])[0];
			$joins .=
				"INNER JOIN `{$this->table}_$order_by[0]` AS `j$join_index`
				ON
					`$table_alias`.`$first_column`	= `j$join_index`.`$first_column_join`";
			return "`j$join_index`.`$order_by[1]`";
		}
		if (is_string($model) && strpos($model, 'ml:') === 0) {
			$clang = Language::instance()->clang;
			++$join_index;
			$joins .=
				"JOIN `[prefix]texts_data` AS `j$join_index`
				ON
					`$table_alias`.`$order_by[0]`	= `j$join_index`.`id_` AND
					(
						`j$join_index`.`lang`	= '$clang' OR
						`j$join_index`.`lang`	= ''
					)";
			return "`j$join_index`.`text`, `$table_alias`.`$order_by[0]`";
		} else {
			return "`$table_alias`.`$order_by[0]`";
		}
	}
	/**
	 * @param string $group_by
	 * @param bool   $asc
	 *
	 * @return string
	 */
	private function search_order_by ($group_by, $asc) {
		$direction = $asc ? 'ASC' : 'DESC';
		$order_by  = explode(', ', $group_by);
		foreach ($order_by as &$o) {
			$o = "$o $direction";
		}
		return implode(', ', $order_by);
	}
}
