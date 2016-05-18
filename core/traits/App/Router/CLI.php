<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\App\Router;
use
	cli\Table,
	cs\Config,
	cs\Config\Module_Properties,
	cs\Page;

trait CLI {
	protected function print_cli_structure ($path) {
		$Config = Config::instance();
		$result = [];
		foreach ($Config->components['modules'] as $module_name => $data) {
			if ($data['active'] == Module_Properties::ENABLED) {
				$working_dir = MODULES."/$module_name/cli";
				$structure   = file_exists("$working_dir/index.json") ? file_get_json("$working_dir/index.json") : [];
				$this->print_cli_structure_internal($working_dir, $module_name, '', $structure, $result[$module_name]);
			}
		}
		$result = $this->print_cli_structure_normalize_result($result);
		$Page   = Page::instance();
		// Cut `/cli/` prefix
		$path = substr($path, 5);
		if ($path) {
			$Page->content("%yPaths and methods for \"$path\":%n\n");
			$result = array_filter(
				$result,
				function ($item) use ($path) {
					return strpos($item[0], $path) === 0;
				}
			);
		} else {
			$Page->content("%yAll paths and methods:%n\n");
		}
		$Page->content(
			implode("\n", (new Table(['Path', 'Methods available'], $result))->getDisplayLines())."\n"
		);
	}
	/**
	 * @param string $dir
	 * @param string $module_name
	 * @param string $basename
	 * @param array  $structure
	 * @param array  $result
	 */
	protected function print_cli_structure_internal ($dir, $module_name, $basename, $structure, &$result) {
		/** @noinspection NestedTernaryOperatorInspection */
		foreach ($structure ?: (!$basename ? ['index'] : []) as $path => $nested_structure) {
			if (!is_array($nested_structure)) {
				$path             = $nested_structure;
				$nested_structure = [];
			}
			$key = $path == '_' ? 0 : $path;
			if (file_exists("$dir/Controller.php")) {
				$result[$key] = $this->controller_router_available_methods(
					$dir,
					"\\cs\\modules\\$module_name\\cli\\Controller",
					$basename ? $basename.'_'.$path : $path
				);
				$new_dir      = $dir;
				$new_basename = $basename ? $basename.'_'.$path : $path;
			} else {
				$result[$key] = $this->files_router_available_methods($dir, $path);
				$new_dir      = "$dir/$path";
				$new_basename = $basename;
			}
			if ($structure && $nested_structure) {
				$this->print_cli_structure_internal($new_dir, $module_name, $new_basename, $nested_structure, $result[$key]);
			}
		}
	}
	/**
	 * @param array  $result
	 * @param string $prefix
	 *
	 * @return string[]
	 */
	protected function print_cli_structure_normalize_result ($result, $prefix = '') {
		$normalized = [];
		foreach ($result as $key => $value) {
			if (is_array_assoc($value)) {
				if (!$prefix && isset($value['index'])) {
					$value[0] = $value['index'];
					unset($value['index']);
				}
				if (is_array(@$value[0]) && $value[0]) {
					$normalized[] = [$prefix.$key, strtolower(implode(', ', $value[0]))];
				}
				unset($value[0]);
				/** @noinspection SlowArrayOperationsInLoopInspection */
				$normalized = array_merge($normalized, $this->print_cli_structure_normalize_result($value, $prefix.$key.'/'));
			} elseif (is_array($value) && $value) {
				$normalized[] = [$prefix.$key, strtolower(implode(', ', $value))];
			}
		}
		return $normalized;
	}
}
