<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			Closure,
			cs\DB\Accessor;
/**
 * CRUD trait
 *
 * Provides create/read/update/delete methods for faster development
 */
trait CRUD {
	use	Accessor;
	/**
	 * @param Closure[]|string[]	$data_model
	 * @param array					$arguments
	 */
	private function crud_arguments_preparation ($data_model, &$arguments) {
		$arguments	= array_combine($data_model, $arguments);
		array_walk(
			$arguments,
			function (&$argument, $item) use ($data_model) {
				$model	= $data_model[$item];
				if ($model instanceof Closure) {
					$argument	= $model($argument);
					return;
				}
				$model	= explode(':', $model);
				switch ($model[0]) {
					case 'int':
					case 'float':
						$argument	= $model[0] == 'int' ? (int)$argument : (float)$argument;
						/**
						 * Ranges processing
						 */
						if (isset($model[1])) {
							$model[1]	= explode('..', $model[1]);
							/**
							 * Minimum
							 */
							$argument	= max($argument, $model[1][0]);
							/**
							 * Maximum
							 */
							if (isset($model[1][1])) {
								$argument	= min($argument, $model[1][1]);
							}
						}
					break;
					case 'text':
					case 'html':
						$argument	= xap($argument, $model[0] == 'text' ? 'text' : true);
						/**
						 * Truncation
						 */
						if (isset($model[1])) {
							$model[1]	= explode(':', $model[1]);
							$argument	= truncate($argument, $model[1][0], isset($model[1][1]) ? $model[1][1] : '...', true);
						}
					break;
				}
			}
		);
	}
	/**
	 * Create item
	 *
	 * @param string				$table
	 * @param Closure[]|string[]	$data_model
	 * @param array					$arguments	First element <i>id</i> can be omitted if it is autoincrement field
	 *
	 * @return bool|int							Id of created item on success, <i>false</i> otherwise
	 */
	protected function create ($table, $data_model, $arguments) {
		self::crud_arguments_preparation(
			count($data_model) == count($arguments) ? $data_model : array_slice($data_model, 1),
			$arguments
		);
		$columns	= "`".implode("`,`", array_keys($data_model))."`";
		$values		= implode(',', array_fill(0, count($arguments), "'%s'"));
		return $this->db_prime()->q(
			"INSERT INTO `$table`
				(
					$columns
				) VALUES (
					$values
				)",
				$arguments
		) ? $this->db_prime()->id() : false;
	}
	/**
	 * Read item
	 *
	 * @param string				$table
	 * @param Closure[]|string[]	$data_model
	 * @param int|int[]				$id
	 *
	 * @return bool
	 */
	protected function read ($table, $data_model, $id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i	= $this->read($table, $data_model, $i);
			}
			return $id;
		}
		$columns	= "`".implode("`,`", array_keys($data_model))."`";
		return $this->db()->qf([
			"SELECT $columns
			FROM `$table`
			WHERE `id` = '%s'
			LIMIT 1",
			$id
		]) ?: false;
	}
	/**
	 * Update item
	 *
	 * @param string				$table
	 * @param Closure[]|string[]	$data_model
	 * @param array					$arguments
	 *
	 * @return bool
	 */
	protected function update ($table, $data_model, $arguments) {
		$id			= $arguments[0];
		self::crud_arguments_preparation(array_slice($data_model, 1), $arguments);
		$columns	= implode(',', array_map(
			function ($column) {
				return "`$column` = '%s'";
			},
			array_keys($data_model)
		));
		$arguments[]	= $id;
		return (bool)$this->db_prime()->q(
			"UPDATE `$table`
			SET $columns
			WHERE `id` = '%s'
			LIMIT 1",
			$arguments
		);
	}
	/**
	 * Delete item
	 *
	 * @param string	$table
	 * @param int|int[]	$id
	 *
	 * @return bool
	 */
	protected function delete ($table, $id) {
		$id	= implode(',', _int((array)$id));
		return (bool)$this->db_prime()->q(
			"DELETE FROM `$table`
			WHERE `id` IN(%s)
			LIMIT 1",
			$id
		);
	}
}