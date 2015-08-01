<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\CRUD;
use
	cs\Event,
	cs\Language,
	cs\Text;

/**
 * @property array  $data_model
 * @property string $data_model_ml_group
 * @property string $data_model_files_tag_prefix
 * @property string $table
 */
trait Data_model_processing {
	/**
	 * @return int
	 */
	abstract protected function cdb ();
	/**
	 * @param callable[]|string[] $data_model
	 * @param array               $arguments
	 *
	 * @return array
	 */
	private function fix_arguments_order ($data_model, $arguments) {
		if (is_array_indexed($arguments)) {
			return $arguments;
		}
		$arguments_ = [];
		foreach (array_keys($data_model) as $argument) {
			if (isset($arguments[$argument])) {
				$arguments_[] = $arguments[$argument];
			}
		}
		return $arguments_;
	}
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
				$joined_tables[$item] = $this->prepare_joined_tables_model($model, $argument);
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
	private function prepare_joined_tables_model ($structure, $arguments) {
		if (!$arguments) {
			return [];
		}
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
			$key = $arguments_assoc ? $field_name : array_search($field_name, array_keys($new_structure['fields']));
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
	 * @param array $data
	 *
	 * @return string[]
	 */
	protected function find_urls ($data) {
		/**
		 * At first we search URLs among attributes values, then whether some field looks like URL itself, and lastly do recursive scan
		 */
		return array_merge(
			preg_match_all('/"((http[s]?:)?\/\/.+)"/Uims', $this->recursive_implode(' ', $data), $files)
				? array_unique($files[1])
				: [],
			array_filter(
				$data,
				function ($data) {
					return !is_array($data) && preg_match('/^(http[s]?:)?\/\/.+$/Uims', $data);
				}
			),
			$data ? call_user_func_array(
				'array_merge',
				array_map(
					function ($data) {
						return is_array($data) ? $this->find_urls($data) : [];
					},
					array_values($data)
				)
			) : []
		);
	}
	/**
	 * @param string $glue
	 * @param array  $pieces
	 *
	 * @return string
	 */
	protected function recursive_implode ($glue, $pieces) {
		foreach ($pieces as &$p) {
			if (is_array($p)) {
				$p = $this->recursive_implode($glue, $p);
			}
		}
		return implode($glue, $pieces);
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
}
