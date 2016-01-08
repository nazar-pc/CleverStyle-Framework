<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System;
use
	cs\Config,
	cs\Core,
	cs\DB,
	cs\Language,
	cs\Page;
/**
 * Utility functions, necessary during packages manipulation (installation/uninstallation, enabling/disabling)
 */
class Packages_manipulation {
	/**
	 * @param string $file_name File key in `$_FILES` superglobal
	 *
	 * @return false|string Path to file location if succeed or `false` on failure
	 */
	static function move_uploaded_file_to_tmp ($file_name) {
		if (!isset($_FILES[$file_name]) || !$_FILES[$file_name]['tmp_name']) {
			return false;
		}
		$L    = Language::instance();
		$Page = Page::instance();
		switch ($_FILES[$file_name]['error']) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$Page->warning($L->file_too_large);
				return false;
			case UPLOAD_ERR_NO_TMP_DIR:
				$Page->warning($L->temporary_folder_is_missing);
				return false;
			case UPLOAD_ERR_CANT_WRITE:
				$Page->warning($L->cant_write_file_to_disk);
				return false;
			case UPLOAD_ERR_PARTIAL:
			case UPLOAD_ERR_NO_FILE:
				return false;
		}
		if ($_FILES[$file_name]['error'] != UPLOAD_ERR_OK) {
			return false;
		}
		$tmp_name = TEMP.'/'.md5(random_bytes(1000)).'.phar';
		return move_uploaded_file($_FILES[$file_name]['tmp_name'], $tmp_name) ? $tmp_name : false;
	}
	/**
	 * Generic extraction of files from phar distributive for CleverStyle CMS (components installation)
	 *
	 * @param string $target_directory
	 * @param string $source_phar Will be removed after extraction
	 *
	 * @return bool
	 */
	static function install_extract ($target_directory, $source_phar) {
		if (!mkdir($target_directory, 0770)) {
			return false;
		}
		$tmp_dir   = "phar://$source_phar";
		$fs        = file_get_json("$tmp_dir/fs.json");
		$extracted = array_filter(
			array_map(
				function ($index, $file) use ($tmp_dir, $target_directory) {
					if (
						!@mkdir(dirname("$target_directory/$file"), 0770, true) &&
						!is_dir(dirname("$target_directory/$file"))
					) {
						return false;
					}
					/**
					 * TODO: copy() + file_exists() is a hack for HHVM, when bug fixed upstream (copying of empty files) this should be simplified
					 */
					copy("$tmp_dir/fs/$index", "$target_directory/$file");
					return file_exists("$target_directory/$file");
				},
				$fs,
				array_keys($fs)
			)
		);
		unlink($source_phar);
		if (count($extracted) === count($fs)) {
			file_put_json("$target_directory/fs.json", array_keys($fs));
			return true;
		}
		return false;
	}
	/**
	 * Generic extraction of files from phar distributive for CleverStyle CMS (system and components update)
	 *
	 * @param string      $target_directory
	 * @param string      $source_phar             Will be removed after extraction
	 * @param null|string $fs_location_directory   Defaults to `$target_directory`
	 * @param null|string $meta_location_directory Defaults to `$target_directory`
	 *
	 * @return bool
	 */
	static function update_extract ($target_directory, $source_phar, $fs_location_directory = null, $meta_location_directory = null) {
		$fs_location_directory   = $fs_location_directory ?: $target_directory;
		$meta_location_directory = $meta_location_directory ?: $target_directory;
		/**
		 * Backup some necessary information about current version
		 */
		copy("$fs_location_directory/fs.json", "$fs_location_directory/fs_backup.json");
		copy("$meta_location_directory/meta.json", "$meta_location_directory/meta_backup.json");
		/**
		 * Extracting new versions of files
		 */
		$tmp_dir   = "phar://$source_phar";
		$fs        = file_get_json("$tmp_dir/fs.json");
		$extracted = array_filter(
			array_map(
				function ($index, $file) use ($tmp_dir, $target_directory) {
					if (
						!@mkdir(dirname("$target_directory/$file"), 0770, true) &&
						!is_dir(dirname("$target_directory/$file"))
					) {
						return false;
					}
					/**
					 * TODO: copy() + file_exists() is a hack for HHVM, when bug fixed upstream (copying of empty files) this should be simplified
					 */
					copy("$tmp_dir/fs/$index", "$target_directory/$file");
					return file_exists("$target_directory/$file");
				},
				$fs,
				array_keys($fs)
			)
		);
		unlink($source_phar);
		unset($tmp_dir);
		if (count($extracted) !== count($fs)) {
			return false;
		}
		unset($extract);
		$fs = array_keys($fs);
		/**
		 * Removing of old unnecessary files and directories
		 */
		foreach (
			array_diff(
				file_get_json("$fs_location_directory/fs_backup.json"),
				$fs
			) as $file
		) {
			$file = "$target_directory/$file";
			if (file_exists($file) && is_writable($file)) {
				unlink($file);
				// Recursively remove all empty parent directories
				while (!get_files_list($file = dirname($file))) {
					rmdir($file);
				}
			}
		}
		unset($file, $dir);
		file_put_json("$fs_location_directory/fs.json", $fs);
		return true;
	}
	/**
	 * Generic update for CleverStyle CMS (system and components), runs PHP scripts and does DB migrations after extracting of new distributive
	 *
	 * @param string     $target_directory
	 * @param string     $old_version
	 * @param array|null $db_array `$Config->components['modules'][$module]['db']` if module or system
	 */
	static function update_php_sql ($target_directory, $old_version, $db_array = null) {
		$meta = file_get_json("$target_directory/meta.json");
		if (!$meta['update_versions']) {
			return;
		}
		foreach ($meta['update_versions'] as $version) {
			if (version_compare($old_version, $version, '<')) {
				/**
				 * PHP update script
				 */
				_include_once("$target_directory/meta/update/$version.php", false);
				/**
				 * Database update
				 */
				if ($db_array) {
					self::execute_sql_from_directory("$target_directory/meta/update_db", $db_array, $version);
				}
			}
		}
	}
	/**
	 * @param string $directory        Base path to SQL files
	 * @param array  $db_configuration Array in form [$db_name => $index]
	 * @param string $version          In case when we are working with update script we might have version subdirectory
	 */
	static function execute_sql_from_directory ($directory, $db_configuration, $version = '') {
		$Config = Config::instance();
		$Core   = Core::instance();
		$db     = DB::instance();
		time_limit_pause();
		foreach ($db_configuration as $db_name => $index) {
			$db_type  = $index == 0 ? $Core->db_type : $Config->db[$index]['type'];
			$sql_file = "$directory/$db_name/$version/$db_type.sql";
			if (file_exists($sql_file)) {
				$db->db_prime($index)->q(
					explode(';', file_get_contents($sql_file))
				);
			}
		}
		time_limit_pause(false);
	}
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
			self::get_dependencies_common_checks($dependencies, $meta, $module_meta);
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
			self::get_dependencies_common_checks($dependencies, $meta, $plugin_meta);
		}
		unset($plugin, $plugin_meta);
		/**
		 * If some required packages still missing
		 */
		if (!empty($meta['require'])) {
			foreach ($meta['require'] as $package => $details) {
				$dependencies['require']['unknown'][] = [
					'name'     => $package,
					'required' => $details
				];
			}
			unset($package, $details);
		}
		if (!self::check_dependencies_db($meta['db_support'])) {
			$dependencies['supported'] = $meta['db_support'];
		}
		if (!self::check_dependencies_storage($meta['storage_support'])) {
			$dependencies['supported'] = $meta['storage_support'];
		}
		return $dependencies;
	}
	/**
	 * @param array $dependencies
	 * @param array $meta
	 * @param array $component_meta
	 */
	protected static function get_dependencies_common_checks (&$dependencies, &$meta, $component_meta) {
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
		if ($already_provided = self::get_dependencies_also_provided_by($meta, $component_meta)) {
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
		if ($dependencies_conflicts = self::get_dependencies_conflicts($meta, $component_meta)) {
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
	protected static function get_dependencies_also_provided_by ($new_meta, $existing_meta) {
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
		if (
			isset($new_meta['require']) &&
			$conflicts = self::check_conflicts(
				$new_meta['require'],
				$existing_meta['package'],
				$existing_meta['version']
			)
		) {
			return $conflicts;
		}
		return [];
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
	protected static function get_dependencies_conflicts ($new_meta, $existing_meta) {
		/**
		 * Check whether two components conflict in any direction by direct conflicts
		 */
		return array_filter(
			[
				self::get_dependencies_conflicts_one_step($new_meta, $existing_meta),
				self::get_dependencies_conflicts_one_step($existing_meta, $new_meta)
			]
		);
	}
	/**
	 * @param array $meta_from
	 * @param array $meta_to
	 *
	 * @return array
	 */
	protected static function get_dependencies_conflicts_one_step ($meta_from, $meta_to) {
		if (
			isset($meta_from['conflict']) &&
			$conflicts = self::check_conflicts(
				$meta_from['conflict'],
				$meta_to['package'],
				$meta_to['version']
			)
		) {
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
				isset($d[2]) && $d[2] ? str_replace('=>', '>=', $d[2]) : (isset($d[3]) && $d[3] ? '=' : '>='),
				isset($d[3]) && $d[3] ? $d[3] : 0
			];
		}
		return $return;
	}
}
