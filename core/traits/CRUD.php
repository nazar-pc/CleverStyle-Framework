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
	 *
	 * @return array[]
	 */
	private function crud_arguments_preparation ($data_model, $arguments, $id = false, &$update_needed = false) {
		$arguments     = array_combine(array_keys($data_model), $arguments);
		$joined_tables = [];
		foreach ($arguments as $item => &$argument) {
			$model = $data_model[$item];
			if (is_callable($model)) {
				$argument = $model($argument);
				continue;
			}
			if (isset($model['data_model'])) {
				$joined_tables[$item] = $this->joined_tables_model($model, $argument);
				unset($arguments[$item]);
				continue;
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
			$argument = self::crud_argument_preparation(
				$type,
				isset($model[1]) ? $model[1] : null,
				$argument
			);
			/**
			 * If field is multilingual - handle multilingual storing of value automatically
			 */
			if ($multilingual_field && $this->is_multilingual()) {
				if ($id !== false) {
					$argument = Text::instance()->set($this->cdb(), "$this->data_model_ml_group/$item", $id, $argument);
				} else {
					$update_needed = true;
				}
			}
		}
		return [
			$arguments,
			$joined_tables
		];
	}
	/**
	 * @param array $structure
	 * @param array $arguments
	 *
	 * @return array
	 */
	private function joined_tables_model ($structure, $arguments) {
		$new_structure = [
			'id_field' => array_keys($structure['data_model'])[0],
			'fields'   => array_slice($structure['data_model'], 1),
			'data'     => []
		];
		if (isset($structure['language_field'])) {
			$new_structure['language_field'] = $structure['language_field'];
		}
		$arguments       = is_array_assoc($arguments) ? [$arguments] : _array((array)$arguments);
		$arguments_assoc = is_array_assoc($arguments[0]);
		foreach ($new_structure['fields'] as $field_name => $field_model) {
			$field_model = explode(':', $field_model, 2);
			$type        = $field_model[0];
			$format      = isset($field_model[1]) ? $field_model[1] : null;
			/**
			 * Both associative and indexed arrays are supported - that is why we determine key for array
			 */
			$key = $arguments_assoc ? $field_name : array_search($field_name, $new_structure['fields']);
			foreach ($arguments as $index => $arguments_local) {
				$new_structure['data'][$index][$field_name] = $this->crud_argument_preparation($type, $format, $arguments_local[$key]);
			}
		}
		return $new_structure;
	}
	/**
	 * @param string $type
	 * @param mixed  $format
	 * @param mixed  $argument
	 *
	 * @return float|int|mixed|string|\string[]
	 */
	private function crud_argument_preparation ($type, $format, $argument) {
		switch ($type) {
			case 'int':
			case 'float':
				$argument = $type == 'int' ? (int)$argument : (float)$argument;
				/**
				 * Ranges processing
				 */
				if ($format !== null) {
					/**
					 * After this `$format[0]` will contain minimum and `$format[1]` if exists - maximum
					 */
					$format   = explode('..', $format);
					$argument = max($argument, $format[0]);
					if (isset($format[1])) {
						$argument = min($argument, $format[1]);
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
				if ($format !== null) {
					/**
					 * After this `$format[0]` will contain length to truncation and `$format[1]` if exists - ending
					 */
					$format   = explode(':', $format);
					$argument = truncate($argument, $format[0], isset($format[1]) ? $format[1] : '...', true);
				}
				break;
			case 'set':
				$allowed_arguments = explode(',', $format);
				if (!in_array($argument, $allowed_arguments)) {
					$argument = $allowed_arguments[0];
				}
				break;
			case 'json':
				$argument = _json_encode($argument);
				break;
		}
		return $argument;
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
		list($prepared_arguments, $joined_tables) = self::crud_arguments_preparation(
			$insert_id !== false ? $data_model : array_slice($data_model, 1),
			$arguments,
			$insert_id,
			$update_needed
		);
		$columns = "`".implode("`,`", array_keys($prepared_arguments))."`";
		$values  = implode(',', array_fill(0, count($prepared_arguments), "'%s'"));
		$return  = $this->db_prime()->q(
			"INSERT IGNORE INTO `$table`
				(
					$columns
				) VALUES (
					$values
				)",
			$prepared_arguments
		);
		$id      = $insert_id !== false ? $insert_id : $this->db_prime()->id();
		/**
		 * Id might be 0 if insertion failed or if we insert duplicate entry (which is fine since we use 'INSERT IGNORE'
		 */
		if (!$return || $id === 0) {
			return false;
		}
		$this->update_joined_tables($id, $joined_tables);
		$this->find_update_files_tags($id, [], $arguments);
		/**
		 * If on creation request without specified primary key and multilingual fields present - update needed
		 * after creation (there is no id before creation)
		 */
		if ($update_needed) {
			$this->update_internal($table, $data_model, array_merge([$id], $prepared_arguments), false);
		}
		return $id;
	}
	/**
	 * @param int|string $id
	 * @param array      $joined_tables
	 */
	private function update_joined_tables ($id, $joined_tables) {
		$clang = $this->db_prime()->s(Language::instance()->clang, false);
		/**
		 * At first we remove all old data
		 */
		foreach ($this->data_model as $table => $model) {
			if (!isset($model['data_model'])) {
				continue;
			}
			$id_field                 = array_keys($model['data_model'])[0];
			$language_field_condition = isset($model['language_field'])
				? "AND `$model[language_field]` = '$clang'"
				: '';
			$this->db_prime()->q(
				"DELETE FROM `{$this->table}_$table`
				WHERE
					`$id_field`	= '%s'
					$language_field_condition",
				$id
			);
		}
		$id = $this->db_prime()->s($id, false);
		/**
		 * Now walk through all tables and insert new valued
		 */
		foreach ($joined_tables as $table => $model) {
			if (!$model['data']) {
				continue;
			}
			$fields = "`$model[id_field]`, ";
			/** @noinspection DisconnectedForeachInstructionInspection */
			$values = "'$id'";
			if (isset($model['language_field'])) {
				$fields .= "`$model[language_field]`, ";
				$values .= ",'$clang'";
			}
			$fields .= '`'.implode('`,`', array_keys($model['fields'])).'`';
			$values .= str_repeat(",'%s'", count($model['fields']));
			$this->db_prime()->insert(
				"INSERT INTO `{$this->table}_$table`
					(
						$fields
					) VALUES (
						$values
					)",
				$model['data']
			);
		}
	}
	/**
	 * @param int|string     $id
	 * @param int[]|string[] $data_before
	 * @param int[]|string[] $data_after
	 */
	private function find_update_files_tags ($id, $data_before, $data_after) {
		if (!$this->with_files_support()) {
			return;
		}
		$prefix = $this->data_model_files_tag_prefix;
		$clang  = Language::instance()->clang;
		$this->update_files_tags(
			"$prefix/$id/$clang",
			$this->find_urls($data_before ?: []),
			$this->find_urls($data_after ?: [])
		);
	}
	/**
	 * Find URLs (any actually) in attributes values (wrapped with `"`, other quotes are not supported) or if field itself is URL
	 *
	 * @param string[] $data
	 *
	 * @return string[]
	 */
	protected function find_urls ($data) {
		/**
		 * At first we search URLs among attributes values, then whether some field looks like URL itself
		 */
		return array_merge(
			preg_match_all('/"((http[s]?:)?\/\/.+)"/Uims', implode(' ', $data), $files)
				? array_unique($files[1])
				: [],
			array_filter(
				$data,
				function ($data) {
					return is_array($data) ? $this->find_urls($data) : preg_match('/^(http[s]?:)?\/\/.+$/Uims', $data);
				}
			)
		);
	}
	/**
	 * @param string   $tag
	 * @param string[] $old_files
	 * @param string[] $new_files
	 */
	protected function update_files_tags ($tag, $old_files, $new_files) {
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
		$columns      = array_filter(
			$data_model,
			function ($column) {
				return !isset($column['data_model']);
			}
		);
		$columns      = "`".implode("`,`", array_keys($columns))."`";
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
		if (!$data) {
			return false;
		}
		foreach ($this->data_model as $field => $model) {
			if (isset($model['data_model'])) {
				$data[$field] = $this->read_joined_table($id, $field, $model);
				continue;
			}
			if (!is_string($model)) {
				continue;
			}
			/**
			 * Handle multilingual fields automatically
			 */
			if (strpos($model, 'ml:') === 0) {
				$data[$field] = Text::instance()->process($this->cdb(), $data[$field], true);
			}
			/**
			 * Decode JSON fields
			 */
			if (in_array($model, ['json', 'ml:json'])) {
				$data[$field] = _json_decode($data[$field]);
			}
		}
		return $data;
	}
	/**
	 * @param int|string $id
	 * @param string     $table
	 * @param array      $model
	 *
	 * @return array
	 */
	private function read_joined_table ($id, $table, $model) {
		$clang                    = $this->db()->s(Language::instance()->clang, false);
		$id_field                 = array_keys($model['data_model'])[0];
		$language_field_condition = isset($model['language_field'])
			? "AND `$model[language_field]` = '$clang'"
			: '';
		$fields                   = '`'.implode('`,`', array_keys($model['data_model'])).'`';
		$rows                     = $this->db_prime()->qfa(
			[
				"SELECT $fields
				FROm `{$this->table}_$table`
				WHERE
						`$id_field`	= '%s'
						$language_field_condition",
				$id
			]
		) ?: [];
		foreach ($rows as &$row) {
			/**
			 * Remove language field from array
			 */
			if ($language_field_condition) {
				unset($row[$model['language_field']]);
			}
			/**
			 * Drop id field
			 */
			unset($row[$id_field]);
			foreach ($row as $field => &$value) {
				if ($model['data_model'][$field] == 'json') {
					$value = _json_decode($value);
				}
			}
			if (isset($model['indexed']) && $model['indexed']) {
				$row = array_values($row);
			} elseif (count($row) == 1) {
				/**
				 * If row is array that contains only one item - lets make resulting array flat
				 */
				$row = array_pop($row);
			}
		}
		return $rows;
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
		$prepared_arguments = $arguments;
		$id                 = array_shift($prepared_arguments);
		if ($files_update) {
			$data_before = $this->read_internal($table, $data_model, $id);
		}
		list($prepared_arguments, $joined_tables) = self::crud_arguments_preparation(array_slice($data_model, 1), $prepared_arguments, $id);
		$columns              = implode(
			',',
			array_map(
				function ($column) {
					return "`$column` = '%s'";
				},
				array_keys($prepared_arguments)
			)
		);
		$prepared_arguments[] = $id;
		$first_column         = array_keys($data_model)[0];
		if (!$this->db_prime()->q(
			"UPDATE `$table`
			SET $columns
			WHERE `$first_column` = '%s'
			LIMIT 1",
			$prepared_arguments
		)
		) {
			return false;
		}
		if ($files_update) {
			$this->update_joined_tables($id, $joined_tables);
			/** @noinspection PhpUndefinedVariableInspection */
			$this->find_update_files_tags($id, $data_before, $arguments);
		}
		return true;
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
			$this->update_joined_tables($i, []);
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
