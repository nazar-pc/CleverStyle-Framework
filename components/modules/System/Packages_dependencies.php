<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System;
use
	cs\Config,
	cs\Core;

/**
 * Utility functions, necessary for determining package's dependencies and which packages depend on it
 */
class Packages_dependencies {
	/**
	 * Check dependencies for new component (during installation/updating/enabling)
	 *
	 * @param array $meta `meta.json` contents of target component
	 *
	 * @return array
	 */
	static function get_dependencies ($meta) {
		/**
		 * No `meta.json` - nothing to check, allow it
		 */
		if (!$meta) {
			return [];
		}
		$meta         = self::normalize_meta($meta);
		$Config       = Config::instance();
		$dependencies = [];
		/**
		 * Check for compatibility with modules
		 */
		foreach (array_keys($Config->components['modules']) as $module) {
			/**
			 * If module uninstalled - we do not care about it
			 */
			if ($Config->module($module)->uninstalled()) {
				continue;
			}
			/**
			 * Stub for the case if there is no `meta.json`
			 */
			$module_meta = [
				'package'  => $module,
				'category' => 'modules',
				'version'  => 0
			];
			if (file_exists(MODULES."/$module/meta.json")) {
				$module_meta = file_get_json(MODULES."/$module/meta.json");
			}
			self::common_checks($dependencies, $meta, $module_meta);
		}
		unset($module, $module_meta);
		/**
		 * Check for compatibility with plugins
		 */
		foreach ($Config->components['plugins'] as $plugin) {
			/**
			 * Stub for the case if there is no `meta.json`
			 */
			$plugin_meta = [
				'package'  => $plugin,
				'category' => 'plugins',
				'version'  => 0
			];
			if (file_exists(PLUGINS."/$plugin/meta.json")) {
				$plugin_meta = file_get_json(PLUGINS."/$plugin/meta.json");
			}
			self::common_checks($dependencies, $meta, $plugin_meta);
		}
		unset($plugin, $plugin_meta);
		/**
		 * If some required packages still missing
		 */
		foreach ($meta['require'] as $package => $details) {
			$dependencies['require']['unknown'][] = [
				'name'     => $package,
				'required' => $details
			];
		}
		unset($package, $details);
		if (!self::check_dependencies_db($meta['db_support'])) {
			$dependencies['db_support'] = $meta['db_support'];
		}
		if (!self::check_dependencies_storage($meta['storage_support'])) {
			$dependencies['storage_support'] = $meta['storage_support'];
		}
		return $dependencies;
	}
	/**
	 * @param array $dependencies
	 * @param array $meta
	 * @param array $component_meta
	 */
	protected static function common_checks (&$dependencies, &$meta, $component_meta) {
		$component_meta = self::normalize_meta($component_meta);
		$package        = $component_meta['package'];
		$category       = $component_meta['category'];
		/**
		 * Do not compare component with itself
		 */
		if (self::check_dependencies_are_the_same($meta, $component_meta)) {
			if (version_compare($meta['version'], $component_meta['version'], '<')) {
				$dependencies['update_older'] = [
					'from' => $component_meta['version'],
					'to'   => $meta['version']
				];
				return;
			}
			/**
			 * If update is supported - check whether update is possible from current version
			 */
			if (
				isset($meta['update_from']) &&
				version_compare($meta['update_from_version'], $component_meta['version'], '>')
			) {
				$dependencies['update_from'] = [
					'from'            => $component_meta['version'],
					'to'              => $meta['version'],
					'can_update_from' => $meta['update_from_version']
				];
			}
			return;
		}
		/**
		 * If component already provides the same functionality
		 */
		if ($already_provided = self::also_provided_by($meta, $component_meta)) {
			$dependencies['provide'][$category][] = [
				'name'     => $package,
				'features' => $already_provided
			];
		}
		/**
		 * Check if component is required and satisfies requirement condition
		 */
		if ($dependencies_conflicts = self::check_requirement_satisfaction($meta, $component_meta)) {
			$dependencies['require'][$category][] = [
				'name'     => $package,
				'existing' => $component_meta['version'],
				'required' => $dependencies_conflicts
			];
		}
		unset($meta['require'][$package]);
		/**
		 * Satisfy provided required functionality
		 */
		foreach ($component_meta['provide'] as $p) {
			unset($meta['require'][$p]);
		}
		/**
		 * Check for conflicts
		 */
		if ($dependencies_conflicts = self::conflicts($meta, $component_meta)) {
			$dependencies['conflict'][$category][] = [
				'name'      => $package,
				'conflicts' => $dependencies_conflicts
			];
		}
	}
	/**
	 * Check whether there is available supported DB engine
	 *
	 * @param string[] $db_support
	 *
	 * @return bool
	 */
	protected static function check_dependencies_db ($db_support) {
		/**
		 * Component doesn't support (and thus use) any DB engines, so we don't care what system have
		 */
		if (!$db_support) {
			return true;
		}
		$Core   = Core::instance();
		$Config = Config::instance();
		if (in_array($Core->db_type, $db_support)) {
			return true;
		}
		foreach ($Config->db as $database) {
			if (isset($database['type']) && in_array($database['type'], $db_support)) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Check whether there is available supported Storage engine
	 *
	 * @param string[] $storage_support
	 *
	 * @return bool
	 */
	protected static function check_dependencies_storage ($storage_support) {
		/**
		 * Component doesn't support (and thus use) any Storage engines, so we don't care what system have
		 */
		if (!$storage_support) {
			return true;
		}
		$Core   = Core::instance();
		$Config = Config::instance();
		if (in_array($Core->storage_type, $storage_support)) {
			return true;
		}
		foreach ($Config->storage as $storage) {
			if (in_array($storage['connection'], $storage_support)) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Check if two both components are the same
	 *
	 * @param array $new_meta      `meta.json` content of new component
	 * @param array $existing_meta `meta.json` content of existing component
	 *
	 * @return bool
	 */
	protected static function check_dependencies_are_the_same ($new_meta, $existing_meta) {
		return
			$new_meta['package'] == $existing_meta['package'] &&
			$new_meta['category'] == $existing_meta['category'];
	}
	/**
	 * Check for functionality provided by other components
	 *
	 * @param array $new_meta      `meta.json` content of new component
	 * @param array $existing_meta `meta.json` content of existing component
	 *
	 * @return array
	 */
	protected static function also_provided_by ($new_meta, $existing_meta) {
		return array_intersect($new_meta['provide'], $existing_meta['provide']);
	}
	/**
	 * Check whether other component is required and have satisfactory version
	 *
	 * @param array $new_meta      `meta.json` content of new component
	 * @param array $existing_meta `meta.json` content of existing component
	 *
	 * @return array
	 */
	protected static function check_requirement_satisfaction ($new_meta, $existing_meta) {
		return self::check_conflicts($new_meta['require'], $existing_meta['package'], $existing_meta['version']);
	}
	/**
	 * Check whether other component is required and have satisfactory version
	 *
	 * @param array  $requirements
	 * @param string $component
	 * @param string $version
	 *
	 * @return array
	 */
	protected static function check_conflicts ($requirements, $component, $version) {
		/**
		 * If we are not interested in component - we are good
		 */
		if (!isset($requirements[$component])) {
			return [];
		}
		/**
		 * Otherwise compare required version with actual present
		 */
		$conflicts = [];
		foreach ($requirements[$component] as $details) {
			if (!version_compare($version, $details[1], $details[0])) {
				$conflicts[] = $details;
			}
		}
		return $conflicts;
	}
	/**
	 * Check for if component conflicts other components
	 *
	 * @param array $new_meta      `meta.json` content of new component
	 * @param array $existing_meta `meta.json` content of existing component
	 *
	 * @return array
	 */
	protected static function conflicts ($new_meta, $existing_meta) {
		/**
		 * Check whether two components conflict in any direction by direct conflicts
		 */
		return array_filter(
			[
				self::conflicts_one_step($new_meta, $existing_meta),
				self::conflicts_one_step($existing_meta, $new_meta)
			]
		);
	}
	/**
	 * @param array $meta_from
	 * @param array $meta_to
	 *
	 * @return array
	 */
	protected static function conflicts_one_step ($meta_from, $meta_to) {
		$conflicts = self::check_conflicts($meta_from['conflict'], $meta_to['package'], $meta_to['version']);
		if ($conflicts) {
			return [
				'package'        => $meta_from['package'],
				'conflicts_with' => $meta_to['package'],
				'of_versions'    => $conflicts
			];
		}
		return [];
	}
	/**
	 * Check whether package is currently used by any other package (during uninstalling/disabling)
	 *
	 * @param array $meta `meta.json` contents of target component
	 *
	 * @return string[][] Empty array if dependencies are fine or array with optional keys `modules` and `plugins` that contain array of dependent packages
	 */
	static function get_dependent_packages ($meta) {
		/**
		 * No `meta.json` - nothing to check, allow it
		 */
		if (!$meta) {
			return [];
		}
		$meta    = self::normalize_meta($meta);
		$Config  = Config::instance();
		$used_by = [];
		/**
		 * Checking for backward dependencies of modules
		 */
		foreach (array_keys($Config->components['modules']) as $module) {
			/**
			 * If module is not enabled, we compare module with itself or there is no `meta.json` - we do not care about it
			 */
			if (
				(
					$meta['category'] == 'modules' &&
					$meta['package'] == $module
				) ||
				!file_exists(MODULES."/$module/meta.json") ||
				!$Config->module($module)->enabled()
			) {
				continue;
			}
			$module_meta = file_get_json(MODULES."/$module/meta.json");
			$module_meta = self::normalize_meta($module_meta);
			/**
			 * Check if component provided something important here
			 */
			if (
				isset($module_meta['require'][$meta['package']]) ||
				array_intersect(array_keys($module_meta['require']), $meta['provide'])
			) {
				$used_by['modules'][] = $module;
			}
		}
		unset($module);
		/**
		 * Checking for backward dependencies of plugins
		 */
		foreach ($Config->components['plugins'] as $plugin) {
			/**
			 * If we compare plugin with itself or there is no `meta.json` - we do not care about it
			 */
			if (
				(
					$meta['category'] == 'plugins' &&
					$meta['package'] == $plugin
				) ||
				!file_exists(PLUGINS."/$plugin/meta.json")
			) {
				continue;
			}
			$plugin_meta = file_get_json(PLUGINS."/$plugin/meta.json");
			$plugin_meta = self::normalize_meta($plugin_meta);
			if (
				isset($plugin_meta['require'][$meta['package']]) ||
				array_intersect(array_keys($plugin_meta['require']), $meta['provide'])
			) {
				$used_by['plugins'][] = $plugin;
			}
		}
		return $used_by;
	}
	/**
	 * Normalize structure of `meta.json`
	 *
	 * Addition necessary items if they are not present and casting some string values to arrays in order to decrease number of checks in further code
	 *
	 * @param array $meta
	 *
	 * @return array mixed
	 */
	protected static function normalize_meta ($meta) {
		foreach (['db_support', 'storage_support', 'provide', 'require', 'conflict'] as $item) {
			$meta[$item] = isset($meta[$item]) ? (array)$meta[$item] : [];
		}
		foreach (['require', 'conflict'] as $item) {
			$meta[$item] = self::dep_normal($meta[$item]);
		}
		return $meta;
	}
	/**
	 * Function for normalization of dependencies structure
	 *
	 * @param array|string $dependence_structure
	 *
	 * @return array
	 */
	protected static function dep_normal ($dependence_structure) {
		$return = [];
		foreach ((array)$dependence_structure as $d) {
			preg_match('/^([^<=>!]+)([<=>!]*)(.*)$/', $d, $d);
			/** @noinspection NestedTernaryOperatorInspection */
			$return[$d[1]][] = [
				isset($d[2]) && $d[2] ? $d[2] : (isset($d[3]) && $d[3] ? '=' : '>='),
				isset($d[3]) && $d[3] ? $d[3] : 0
			];
		}
		return $return;
	}
}
