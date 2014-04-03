<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			Closure,
			cs\DB\Accessor;
/**
 * CRUD trait
 *
 * Provides create/read/update/delete methods for faster development
 *
 * @property array	$data_model
 * @property string	$table
 */
trait CRUD {
	use	Accessor;
	/**
	 * @param Closure[]|string[]	$data_model
	 * @param array					$arguments
	 */
	private function crud_arguments_preparation ($data_model, &$arguments) {
		$arguments	= array_combine(array_keys($data_model), $arguments);
		array_walk(
			$arguments,
			function (&$argument, $item) use ($data_model) {
				$model	= $data_model[$item];
				if ($model instanceof Closure) {
					$argument	= $model($argument);
					return;
				}
				list($type, $format) = explode(':', $model)[2];
				switch ($type) {
					case 'int':
					case 'float':
						$argument	= $model[0] == 'int' ? (int)$argument : (float)$argument;
						/**
						 * Ranges processing
						 */
						if (isset($format)) {
							list($min, $max) = explode('..', $format);
							/**
							 * Minimum
							 */
							$argument	= max($argument, $min);
							/**
							 * Maximum
							 */
							if (isset($max)) {
								$argument	= min($argument, $max);
							}
						}
					break;
					case 'text':
					case 'html':
						$argument	= xap($argument, $model[0] == 'text' ? 'text' : true);
						/**
						 * Truncation
						 */
						if (isset($format)) {
							list($length, $ending) = explode(':', $format);
							$argument	= truncate($argument, $length, isset($ending) ? $ending : '...', true);
						}
					case 'set':
						$allowed_arguments	= explode(',', $format);
						if (array_search($argument, $allowed_arguments) === false) {
							$argument	= $allowed_arguments[0];
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
		$insert_id	= count($data_model) == count($arguments);
		self::crud_arguments_preparation(
			$insert_id ? $data_model : array_slice($data_model, 1),
			$arguments
		);
		$columns	= "`".implode("`,`", array_keys($insert_id ? $data_model : array_slice($data_model, 1)))."`";
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
	 * Wrapper for create() method, when $table and $data_model arguments are expected to be a properties of class
	 *
	 * @see create
	 *
	 * @param array	$arguments	First element <i>id</i> can be omitted if it is autoincrement field
	 *
	 * @return bool|int			Id of created item on success, <i>false</i> otherwise
	 */
	protected function create_simple ($arguments) {
		return $this->create($this->table, $this->data_model, $arguments);
	}
	/**
	 * Read item
	 *
	 * @param string				$table
	 * @param Closure[]|string[]	$data_model
	 * @param int|int[]				$id
	 *
	 * @return array|bool
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
	 * Wrapper for read() method, when $table and $data_model arguments are expected to be a properties of class
	 *
	 * @see read
	 *
	 * @param int|int[]		$id
	 *
	 * @return array|bool
	 */
	protected function read_simple ($id) {
		return $this->read($this->table, $this->data_model, $id);
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
		$id			= array_shift($arguments);
		self::crud_arguments_preparation(array_slice($data_model, 1), $arguments);
		$columns	= implode(',', array_map(
			function ($column) {
				return "`$column` = '%s'";
			},
			array_keys($arguments)
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
	 * Wrapper for update() method, when $table and $data_model arguments are expected to be a properties of class
	 *
	 * @see update
	 *
	 * @param array	$arguments
	 *
	 * @return bool
	 */
	protected function update_simple ($arguments) {
		return $this->update($this->table, $this->data_model, $arguments);
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
	/**
	 * Wrapper for delete() method, when $table argument is expected to be a property of class
	 *
	 * @see delete
	 *
	 * @param int|int[]	$id
	 *
	 * @return bool
	 */
	protected function delete_simple ($id) {
		return $this->delete($this->table, $this->data_model, $id);
	}
}
