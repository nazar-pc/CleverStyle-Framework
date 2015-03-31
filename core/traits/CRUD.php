<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	cs\DB\Accessor;

/**
 * CRUD trait
 *
 * Provides create/read/update/delete methods for faster development
 *
 * @property array  $data_model
 * @property string $data_model_ml_group
 * @property string $table
 */
trait CRUD {
	use    Accessor;
	/**
	 * @param callable[]|string[] $data_model
	 * @param array               $arguments
	 * @param bool|int            $id            On update id should be specified to work properly with multilingual fields
	 * @param bool                $update_needed If on creation request without specified primary key and multilingual fields present - update needed
	 *                                           after creation (there is no id before creation)
	 */
	private function crud_arguments_preparation ($data_model, &$arguments, $id = false, &$update_needed = false) {
		$arguments = array_combine(array_keys($data_model), $arguments);
		array_walk(
			$arguments,
			function (&$argument, $item) use ($data_model, $arguments, $id, &$update_needed) {
				$model = $data_model[$item];
				if (is_callable($model)) {
					$argument = $model($argument);
					return;
				}
				$model        = explode(':', $model, 2);
				$type         = $model[0];
				$multilingual = false;
				/**
				 * If field is multilingual
				 */
				if ($type == 'ml') {
					$multilingual = true;
					$model        = explode(':', $model[1], 2);
					$type         = $model[0];
				}
				if (isset($model[1])) {
					$format = $model[1];
				}
				switch ($type) {
					case 'int':
					case 'float':
						$argument = $type == 'int' ? (int)$argument : (float)$argument;
						/**
						 * Ranges processing
						 */
						if (isset($format)) {
							$format = explode('..', $format);
							$min    = $format[0];
							if (isset($format[1])) {
								$max = $format[1];
							}
							/**
							 * Minimum
							 */
							$argument = max($argument, $min);
							/**
							 * Maximum
							 */
							if (isset($max)) {
								$argument = min($argument, $max);
							}
						}
						break;
					case 'text':
					case 'html':
						$argument = xap($argument, $model[0] == 'text' ? 'text' : true);
						/**
						 * Truncation
						 */
						if (isset($format)) {
							$format = explode(':', $format);
							$length = $format[0];
							if (isset($format[1])) {
								$ending = $format[1];
							}
							$argument = truncate($argument, $length, isset($ending) ? $ending : '...', true);
						}
						break;
					case 'set':
						/**
						 * @var string $format
						 */
						$allowed_arguments = explode(',', $format);
						if (!in_array($argument, $allowed_arguments)) {
							$argument = $allowed_arguments[0];
						}
						break;
				}
				/**
				 * If field is multilingual - handle multilingual storing of value automatically
				 */
				/** @noinspection NotOptimalIfConditionsInspection */
				if ($multilingual && isset($this->data_model_ml_group) && $this->data_model_ml_group) {
					if ($id !== false) {
						$argument = Text::instance()->set($this->cdb(), "$this->data_model_ml_group/$item", $id, $argument);
					} else {
						$update_needed = true;
					}
				}
			}
		);
	}
	/**
	 * Create item
	 *
	 * @param string              $table
	 * @param callable[]|string[] $data_model
	 * @param array               $arguments First element `id` can be omitted if it is autoincrement field
	 *
	 * @return bool|int|string                Id of created item on success (or specified primary key), `false` otherwise
	 */
	protected function create ($table, $data_model, $arguments) {
		$insert_id = count($data_model) == count($arguments);
		self::crud_arguments_preparation(
			$insert_id ? $data_model : array_slice($data_model, 1),
			$arguments,
			$insert_id ? $arguments[0] : false,
			$update_needed
		);
		$columns = "`".implode("`,`", array_keys($insert_id ? $data_model : array_slice($data_model, 1)))."`";
		$values  = implode(',', array_fill(0, count($arguments), "'%s'"));
		$return  = $this->db_prime()->q(
			"INSERT INTO `$table`
				(
					$columns
				) VALUES (
					$values
				)",
			$arguments
		);
		if (!$return) {
			return false;
		}
		$id = $insert_id ? $arguments[0] : $this->db_prime()->id();
		/**
		 * If on creation request without specified primary key and multilingual fields present - update needed
		 * after creation (there is no id before creation)
		 */
		if ($update_needed) {
			$this->update($table, $data_model, array_merge([$id], $arguments));
		}
		return $id;
	}
	/**
	 * Wrapper for `::create()` method, when `$table` and `$data_model` arguments are expected to be a properties of class
	 *
	 * @see create
	 *
	 * @param array $arguments First element `id` can be omitted if it is autoincrement field
	 *
	 * @return bool|int            Id of created item on success, `false` otherwise
	 */
	protected function create_simple ($arguments) {
		return $this->create($this->table, $this->data_model, $arguments);
	}
	/**
	 * Read item
	 *
	 * @param string              $table
	 * @param callable[]|string[] $data_model
	 * @param int|int[]           $id
	 *
	 * @return array|bool
	 */
	protected function read ($table, $data_model, $id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i = $this->read($table, $data_model, $i);
			}
			return $id;
		}
		$columns      = "`".implode("`,`", array_keys($data_model))."`";
		$first_column = array_keys($data_model)[0];
		$data         = $this->db()->qf([
			"SELECT $columns
			FROM `$table`
			WHERE `$first_column` = '%s'
			LIMIT 1",
			$id
		]) ?: false;
		/**
		 * If there are multilingual fields - handle multilingual getting of fields automatically
		 */
		/** @noinspection NotOptimalIfConditionsInspection */
		if ($data && isset($this->data_model_ml_group) && $this->data_model_ml_group) {
			/** @noinspection ForeachOnArrayComponentsInspection */
			foreach (array_keys($this->data_model) as $field) {
				if (strpos($this->data_model[$field], 'ml:') === 0) {
					$data[$field] = Text::instance()->process($this->cdb(), $data[$field], true);
				}
			}
		}
		return $data;
	}
	/**
	 * Wrapper for `::read()` method, when `$table` and `$data_model` arguments are expected to be a properties of class
	 *
	 * @see read
	 *
	 * @param int|int[] $id
	 *
	 * @return array|bool
	 */
	protected function read_simple ($id) {
		return $this->read($this->table, $this->data_model, $id);
	}
	/**
	 * Update item
	 *
	 * @param string              $table
	 * @param callable[]|string[] $data_model
	 * @param array               $arguments
	 *
	 * @return bool
	 */
	protected function update ($table, $data_model, $arguments) {
		$id = array_shift($arguments);
		self::crud_arguments_preparation(array_slice($data_model, 1), $arguments, $id);
		$columns      = implode(',', array_map(
			function ($column) {
				return "`$column` = '%s'";
			},
			array_keys($arguments)
		));
		$arguments[]  = $id;
		$first_column = array_keys($data_model)[0];
		return (bool)$this->db_prime()->q(
			"UPDATE `$table`
			SET $columns
			WHERE `$first_column` = '%s'
			LIMIT 1",
			$arguments
		);
	}
	/**
	 * Wrapper for `::update()` method, when `$table` and `$data_model` arguments are expected to be a properties of class
	 *
	 * @see update
	 *
	 * @param array $arguments
	 *
	 * @return bool
	 */
	protected function update_simple ($arguments) {
		return $this->update($this->table, $this->data_model, $arguments);
	}
	/**
	 * Delete item
	 *
	 * @param string                    $table
	 * @param callable[]|string[]       $data_model
	 * @param int|int[]|string|string[] $id
	 *
	 * @return bool
	 */
	protected function delete ($table, $data_model, $id) {
		$id           = (array)$id;
		$result       = true;
		$multilingual = isset($this->data_model_ml_group) && $this->data_model_ml_group;
		$first_column = array_keys($data_model)[0];
		foreach (_int($id) as $i) {
			$result =
				$result &&
				$this->db_prime()->q(
					"DELETE FROM `$table`
					WHERE `$first_column` = '%s'
					LIMIT 1",
					$i
				);
			/**
			 * If there are multilingual fields - handle multilingual deleting of fields automatically
			 */
			if ($multilingual) {
				/** @noinspection ForeachOnArrayComponentsInspection */
				foreach (array_keys($this->data_model) as $field) {
					if (strpos($this->data_model[$field], 'ml:') === 0) {
						Text::instance()->del($this->cdb(), "$this->data_model_ml_group/$field", $i);
					}
				}
			}
		}
		return $result;
	}
	/**
	 * Wrapper for `::delete()` method, when `$table` argument is expected to be a property of class
	 *
	 * @see delete
	 *
	 * @param int|int[]|string|string[] $id
	 *
	 * @return bool
	 */
	protected function delete_simple ($id) {
		return $this->delete($this->table, $this->data_model, $id);
	}
}
