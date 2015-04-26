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
 * @property string $data_model_files_tag_prefix
 * @property string $table
 */
trait CRUD {
	use
		Accessor;
	/**
	 * @param callable[]|string[] $data_model
	 * @param array               $arguments
	 * @param false|int           $id            On update id should be specified to work properly with multilingual fields
	 * @param bool                $update_needed If on creation request without specified primary key and multilingual fields present - update needed
	 *                                           after creation (there is no id before creation)
	 */
	private function crud_arguments_preparation ($data_model, &$arguments, $id = false, &$update_needed = false) {
		$arguments       = array_combine(array_keys($data_model), $arguments);
		$is_multilingual = $this->is_multilingual();
		array_walk(
			$arguments,
			function (&$argument, $item) use ($data_model, $id, $is_multilingual, &$update_needed) {
				$model = $data_model[$item];
				if (is_callable($model)) {
					$argument = $model($argument);
					return;
				}
				$model              = explode(':', $model, 2);
				$type               = $model[0];
				$multilingual_field = false;
				/**
				 * If field is multilingual
				 */
				if ($type == 'ml') {
					$multilingual_field = true;
					$model              = explode(':', $model[1], 2);
					$type               = $model[0];
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
					case 'html_iframe':
						$argument = xap(
							$argument,
							$type == 'text' ? 'text' : true,
							$type == 'html_iframe'
						);
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
				if ($multilingual_field && $is_multilingual) {
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
	 * @return bool
	 */
	private function is_multilingual () {
		return isset($this->data_model_ml_group) && $this->data_model_ml_group;
	}
	/**
	 * @return bool
	 */
	private function with_files_support () {
		return isset($this->data_model_files_tag_prefix) && $this->data_model_files_tag_prefix;
	}
	/**
	 * Create item
	 *
	 * @param array $arguments First element `id` can be omitted if it is autoincrement field
	 *
	 * @return false|int|string Id of created item on success, `false` otherwise
	 */
	protected function create ($arguments) {
		//TODO remove in future versions
		if (func_num_args() === 3) {
			return call_user_func([$this, 'create_internal'], func_get_args());
		}
		return $this->create_internal($this->table, $this->data_model, $arguments);
	}
	/**
	 * Create item
	 *
	 * @param string              $table
	 * @param callable[]|string[] $data_model
	 * @param array               $arguments First element `id` can be omitted if it is autoincrement field
	 *
	 * @return false|int|string Id of created item on success (or specified primary key), `false` otherwise
	 */
	private function create_internal ($table, $data_model, $arguments) {
		$insert_id = count($data_model) == count($arguments) ? $arguments[0] : false;
		self::crud_arguments_preparation(
			$insert_id !== false ? $data_model : array_slice($data_model, 1),
			$arguments,
			$insert_id,
			$update_needed
		);
		$columns = "`".implode("`,`", array_keys($insert_id !== false ? $data_model : array_slice($data_model, 1)))."`";
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
		$id = $insert_id !== false ? $insert_id : $this->db_prime()->id();
		$this->update_files_tags($id, [], $arguments);
		/**
		 * If on creation request without specified primary key and multilingual fields present - update needed
		 * after creation (there is no id before creation)
		 */
		if ($update_needed) {
			$this->update_internal($table, $data_model, array_merge([$id], $arguments), false);
		}
		return $id;
	}
	/**
	 * @deprecated
	 * @todo remove in future versions
	 *
	 * @param array $arguments First element `id` can be omitted if it is autoincrement field
	 *
	 * @return false|int|string Id of created item on success, `false` otherwise
	 */
	protected function create_simple ($arguments) {
		return $this->create($arguments);
	}
	/**
	 * Read item
	 *
	 * @param int|int[]|string|string[] $id
	 *
	 * @return array|false
	 */
	protected function read ($id) {
		//TODO remove in future versions
		if (func_num_args() === 3) {
			return call_user_func([$this, 'read_internal'], func_get_args());
		}
		return $this->read_internal($this->table, $this->data_model, $id);
	}
	/**
	 * Read item
	 *
	 * @param string                    $table
	 * @param callable[]|string[]       $data_model
	 * @param int|int[]|string|string[] $id
	 *
	 * @return array|false
	 */
	private function read_internal ($table, $data_model, $id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i = $this->read_internal($table, $data_model, $i);
			}
			return $id;
		}
		$columns      = "`".implode("`,`", array_keys($data_model))."`";
		$first_column = array_keys($data_model)[0];
		$data         = $this->db()->qf(
			[
				"SELECT $columns
				FROM `$table`
				WHERE `$first_column` = '%s'
				LIMIT 1",
				$id
			]
		) ?: false;
		/**
		 * If there are multilingual fields - handle multilingual getting of fields automatically
		 */
		/** @noinspection NotOptimalIfConditionsInspection */
		if ($data && $this->is_multilingual()) {
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
	 * @deprecated
	 * @todo remove in future versions
	 *
	 * @param int|int[]|string|string[] $id
	 *
	 * @return array|false
	 */
	protected function read_simple ($id) {
		return $this->read($id);
	}
	/**
	 * Update item
	 *
	 * @param array $arguments
	 *
	 * @return bool
	 */
	protected function update ($arguments) {
		//TODO remove in future versions
		if (func_num_args() === 3) {
			return call_user_func([$this, 'update_internal'], func_get_args());
		}
		return $this->update_internal($this->table, $this->data_model, $arguments);
	}
	/**
	 * Update item
	 *
	 * @param string              $table
	 * @param callable[]|string[] $data_model
	 * @param array               $arguments
	 * @param bool                $files_update
	 *
	 * @return bool
	 */
	private function update_internal ($table, $data_model, $arguments, $files_update = true) {
		$id = array_shift($arguments);
		if ($files_update) {
			$data_before = $this->read_internal($table, $data_model, $id);
		}
		self::crud_arguments_preparation(array_slice($data_model, 1), $arguments, $id);
		$columns      = implode(
			',',
			array_map(
				function ($column) {
					return "`$column` = '%s'";
				},
				array_keys($arguments)
			)
		);
		$arguments[]  = $id;
		$first_column = array_keys($data_model)[0];
		if (!$this->db_prime()->q(
			"UPDATE `$table`
			SET $columns
			WHERE `$first_column` = '%s'
			LIMIT 1",
			$arguments
		)
		) {
			return false;
		}
		if ($files_update) {
			/** @noinspection PhpUndefinedVariableInspection */
			$this->update_files_tags($id, $data_before, func_get_args()[2]);
		}
		return true;
	}
	/**
	 * @param int|string     $id
	 * @param int[]|string[] $data_before
	 * @param int[]|string[] $data_after
	 */
	private function update_files_tags ($id, $data_before, $data_after) {
		if (!$this->with_files_support()) {
			return;
		}
		$prefix    = $this->data_model_files_tag_prefix;
		$clang     = Language::instance()->clang;
		$tag       = "$prefix/$id/$clang";
		$old_files = $this->find_urls($data_before ?: []);
		$new_files = $this->find_urls($data_after ?: []);
		if ($old_files || $new_files) {
			foreach (array_diff($old_files, $new_files) as $file) {
				Event::instance()->fire(
					'System/upload_files/del_tag',
					[
						'tag' => $tag,
						'url' => $file
					]
				);
			}
			foreach (array_diff($new_files, $old_files) as $file) {
				Event::instance()->fire(
					'System/upload_files/add_tag',
					[
						'tag' => $tag,
						'url' => $file
					]
				);
			}
		}
	}
	/**
	 * Find urls (any actually) in attributes values (wrapped with `"`, other quotes are not supported)
	 *
	 * @param string[] $data
	 *
	 * @return string[]
	 */
	private function find_urls ($data) {
		return preg_match_all('/"(http[s]?:\/\/.+)"/Uims', implode(' ', $data), $files)
			? array_unique($files[1])
			: [];
	}
	/**
	 * @deprecated
	 * @todo remove in future versions
	 *
	 * @param array $arguments
	 *
	 * @return bool
	 */
	protected function update_simple ($arguments) {
		return $this->update($arguments);
	}
	/**
	 * Delete item
	 *
	 * @param int|int[]|string|string[] $id
	 *
	 * @return bool
	 */
	protected function delete ($id) {
		//TODO remove in future versions
		if (func_num_args() === 3) {
			return call_user_func([$this, 'delete_internal'], func_get_args());
		}
		return $this->delete_internal($this->table, $this->data_model, $id);
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
	private function delete_internal ($table, $data_model, $id) {
		$id           = (array)$id;
		$result       = true;
		$multilingual = $this->is_multilingual();
		$first_column = array_keys($data_model)[0];
		foreach ($id as $i) {
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
			$this->delete_files_tags($i);
		}
		return $result;
	}
	/**
	 * @param int|string $id
	 */
	private function delete_files_tags ($id) {
		if (!$this->with_files_support()) {
			return;
		}
		Event::instance()->fire(
			'System/upload_files/del_tag',
			[
				'tag' => "$this->data_model_files_tag_prefix/$id%"
			]
		);
	}
	/**
	 * @deprecated
	 * @todo remove in future versions
	 *
	 * @param int|int[]|string|string[] $id
	 *
	 * @return bool
	 */
	protected function delete_simple ($id) {
		return $this->delete($id);
	}
}
