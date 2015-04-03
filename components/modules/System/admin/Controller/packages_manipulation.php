<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\admin\Controller;
use
	cs\Config,
	cs\Core,
	cs\DB,
	cs\Language,
	cs\Page;

trait packages_manipulation {
	/**
	 * @param string $file_name File key in `$_FILES` superglobal
	 *
	 * @return false|string Path to file location if succeed or `false` on failure
	 */
	static protected function move_uploaded_file_to_tmp ($file_name) {
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
		$tmp_name = TEMP.'/'.md5(openssl_random_pseudo_bytes(1000)).'.phar';
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
	static protected function install_extract ($target_directory, $source_phar) {
		$tmp_dir = "phar://$source_phar";
		$fs      = file_get_json("$tmp_dir/fs.json");
		$extract = array_product(
			array_map(
				function ($index, $file) use ($tmp_dir, $target_directory) {
					if (
						!file_exists(dirname("$target_directory/$file")) &&
						!mkdir(dirname("$target_directory/$file"), 0770, true)
					) {
						return 0;
					}
					return (int)copy("$tmp_dir/fs/$index", "$target_directory/$file");
				},
				$fs,
				array_keys($fs)
			)
		);
		unlink($source_phar);
		if ($extract) {
			file_put_json("$target_directory/fs.json", array_keys($fs));
		}
		return (bool)$extract;
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
	static protected function update_extract ($target_directory, $source_phar, $fs_location_directory = null, $meta_location_directory = null) {
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
		$tmp_dir = "phar://$source_phar";
		$fs      = file_get_json("$tmp_dir/fs.json");
		$extract = array_product(
			array_map(
				function ($index, $file) use ($tmp_dir, $target_directory) {
					if (
						!file_exists(dirname("$target_directory/$file")) &&
						!mkdir(dirname("$target_directory/$file"), 0770, true)
					) {
						return 0;
					}
					return (int)copy("$tmp_dir/fs/$index", "$target_directory/$file");
				},
				$fs,
				array_keys($fs)
			)
		);
		unlink($source_phar);
		unset($tmp_dir);
		if (!$extract) {
			return false;
		}
		unset($extract);
		$fs = array_keys($fs);
		/**
		 * Removing of old unnecessary files and directories
		 */
		foreach (
			array_diff(
				file_get_json("$fs_location_directory/fs.json"),
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
		/**
		 * Removing backups after successful update
		 */
		unlink("$fs_location_directory/fs_backup.json");
		unlink("$meta_location_directory/meta_backup.json");
		return true;
	}
	/**
	 * Generic update for CleverStyle CMS (system and components), runs PHP scripts and does DB migrations after extracting of new distributive
	 *
	 * @param string     $target_directory
	 * @param string     $old_version
	 * @param array|null $db_array `$module_data['db']` if module or system
	 */
	static protected function update_php_sql ($target_directory, $old_version, $db_array = null) {
		$Core   = Core::instance();
		$Config = Config::instance();
		$db     = DB::instance();
		$meta   = file_get_json("$target_directory/meta.json");
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
					time_limit_pause();
					foreach ($db_array as $db_name => $index) {
						if ($index == 0) {
							$db_type = $Core->db_type;
						} else {
							$db_type = $Config->db[$index]['type'];
						}
						$sql_file = "$target_directory/meta/update_db/$db_name/$version/$db_type.sql";
						if (file_exists($sql_file)) {
							$db->$index()->q(
								explode(';', file_get_contents($sql_file))
							);
						}
					}
					unset($db_name, $db_type, $sql_file);
					time_limit_pause(false);
				}
			}
		}
	}
	/**
	 * @param string $target_directory
	 *
	 * @return bool
	 */
	static protected function recursive_directory_removal ($target_directory) {
		$ok = true;
		get_files_list(
			$target_directory,
			false,
			'fd',
			true,
			true,
			false,
			false,
			true,
			function ($item) use (&$ok) {
				if (is_writable($item)) {
					is_dir($item) ? rmdir($item) : unlink($item);
				} else {
					$ok = false;
				}
			}
		);
		if ($ok) {
			rmdir($target_directory);
		}
		return $ok;
	}
	/**
	 * Check dependencies for new component (during installation/updating/enabling)
	 *
	 * @param array $meta        `meta.json` contents of target component
	 * @param bool  $update_mode Whether target component is module that is going to update previous version
	 *
	 * @return bool
	 */
	static protected function check_dependencies ($meta, $update_mode = false) {
		/**
		 * No `meta.json` - nothing to check, allow it
		 */
		if (!$meta) {
			return true;
		}
		$meta                   = self::normalize_meta($meta);
		$Config                 = Config::instance();
		$L                      = Language::instance();
		$Page                   = Page::instance();
		$already_provided       = false;
		$satisfied_requirements = true;
		$no_conflicts           = true;
		/**
		 * Checking for compatibility with modules
		 */
		foreach ($Config->components['modules'] as $module => $module_data) {
			/**
			 * If module uninstalled - we do not care about it
			 */
			if ($module_data['active'] == -1) {
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
			$module_meta = self::normalize_meta($module_meta);
			/**
			 * Do not compare components with itself
			 */
			if (self::check_dependencies_are_the_same($meta, $module_meta)) {
				/**
				 * Unless it updates, in this case check whether update is possible from current version
				 */
				if (
					$update_mode &&
					isset($meta['update_from']) &&
					version_compare($meta['update_from_version'], $module_meta['version'], '>')
				) {
					$Page->warning(
						$L->module_cant_be_updated_from_version_to_supported_only(
							$module_meta['package'],
							$module_meta['version'],
							$meta['version'],
							$meta['update_from_version']
						)
					);
					$Page->warning($L->dependencies_not_satisfied);
					return false;
				}
				continue;
			}
			/**
			 * If module already provides the same functionality
			 */
			if (self::check_dependencies_also_provided_by($meta, $module_meta)) {
				$already_provided = true;
			}
			/**
			 * Checking for required packages
			 */
			if (self::check_dependencies_satisfies_required_package($meta, $module_meta)) {
				unset($meta['require'][$module_meta['package']]);
			} else {
				$satisfied_requirements = false;
			}
			/**
			 * Cleaning provided required functionality
			 */
			foreach ($module_meta['provide'] as $p) {
				unset($meta['require'][$p]);
			}
			unset($p);
			/**
			 * Checking for conflict packages
			 */
			if (!self::check_dependencies_conflicts($meta, $module_meta)) {
				$no_conflicts = false;
			}
		}
		unset($module, $module_data, $module_meta);
		/**
		 * Checking for compatibility with plugins
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
			$plugin_meta = self::normalize_meta($plugin_meta);
			/**
			 * Do not compare components with itself
			 */
			if (self::check_dependencies_are_the_same($meta, $plugin_meta)) {
				continue;
			}
			/**
			 * If plugin already provides the same functionality
			 */
			if (self::check_dependencies_also_provided_by($meta, $plugin_meta)) {
				$already_provided = true;
			}
			/**
			 * Checking for required packages
			 */
			if (self::check_dependencies_satisfies_required_package($meta, $plugin_meta)) {
				unset($meta['require'][$plugin_meta['package']]);
			} else {
				$satisfied_requirements = false;
			}
			/**
			 * Cleaning provided required functionality
			 */
			foreach ($plugin_meta['provide'] as $p) {
				unset($meta['require'][$p]);
			}
			unset($p);
			/**
			 * Checking for conflict packages
			 */
			if (!self::check_dependencies_conflicts($meta, $plugin_meta)) {
				$no_conflicts = false;
			}
		}
		unset($plugin, $plugin_meta);
		$missing_required_packages = false;
		/**
		 * If some required packages missing
		 */
		if (!empty($meta['require'])) {
			foreach ($meta['require'] as $package => $details) {
				$missing_required_packages = true;
				$Page->warning(
					$L->package_or_functionality_not_found($details[1] ? "$package $details[0] $details[1]" : $package)
				);
			}
			unset($package, $details);
		}
		$db_supported      = self::check_dependencies_db($meta['db_support']);
		$storage_supported = self::check_dependencies_storage($meta['storage_support']);
		$result            =
			!$already_provided &&
			$satisfied_requirements &&
			$no_conflicts &&
			!$missing_required_packages &&
			$db_supported &&
			$storage_supported;
		if (!$result) {
			$Page->warning($L->dependencies_not_satisfied);
		}
		return $result;
	}
	/**
	 * Check whether there is available supported DB engine
	 *
	 * @param string[] $db_support
	 *
	 * @return bool
	 */
	static protected function check_dependencies_db ($db_support) {
		/**
		 * Component doesn't support (and thus use) any DB engines, so we don't care what system have
		 */
		if (!$db_support) {
			return true;
		}
		$Core         = Core::instance();
		$Config       = Config::instance();
		$L            = Language::instance();
		$Page         = Page::instance();
		$check_result = false;
		if (!in_array($Core->db_type, $db_support)) {
			foreach ($Config->db as $database) {
				if (isset($database['type']) && in_array($database['type'], $db_support)) {
					$check_result = true;
					break;
				}
			}
			unset($database);
		}
		if (!$check_result) {
			$Page->warning(
				$L->compatible_databases_not_found(
					implode('", "', $db_support)
				)
			);
		} elseif (!$Config->core['simple_admin_mode']) {
			$Page->success(
				$L->compatible_databases(
					implode('", "', $db_support)
				)
			);
		}
		return $check_result;
	}
	/**
	 * Check whether there is available supported Storage engine
	 *
	 * @param string[] $storage_support
	 *
	 * @return bool
	 */
	static protected function check_dependencies_storage ($storage_support) {
		/**
		 * Component doesn't support (and thus use) any Storage engines, so we don't care what system have
		 */
		if (!$storage_support) {
			return true;
		}
		$Core         = Core::instance();
		$Config       = Config::instance();
		$L            = Language::instance();
		$Page         = Page::instance();
		$check_result = false;
		if (in_array($Core->storage_type, $storage_support)) {
			$check_result = true;
		} else {
			foreach ($Config->storage as $storage) {
				if (in_array($storage['connection'], $storage_support)) {
					$check_result = true;
					break;
				}
			}
			unset($storage);
		}
		if (!$check_result) {
			$Page->warning(
				$L->compatible_storages_not_found(
					implode('", "', $storage_support)
				)
			);
		} elseif (!$Config->core['simple_admin_mode']) {
			$Page->success(
				$L->compatible_storages(
					implode('", "', $storage_support)
				)
			);
		}
		return $check_result;
	}
	/**
	 * Check if two both components are the same
	 *
	 * @param array $new_meta      `meta.json` content of new component
	 * @param array $existing_meta `meta.json` content of existing component
	 *
	 * @return bool
	 */
	static protected function check_dependencies_are_the_same ($new_meta, $existing_meta) {
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
	 * @return bool
	 */
	static protected function check_dependencies_also_provided_by ($new_meta, $existing_meta) {
		$intersected_functionality = array_intersect($new_meta['provide'], $existing_meta['provide']);
		if (!$intersected_functionality) {
			return false;
		}
		$L    = Language::instance();
		$Page = Page::instance();
		$key  = $existing_meta['category'] == 'modules' ? 'module_already_provides_functionality' : 'plugin_already_provides_functionality';
		$Page->warning(
			$L->$key(
				$existing_meta['package'],
				implode('", "', $intersected_functionality)
			)
		);
		return true;
	}
	/**
	 * Check whether other component is required and have satisfactory version
	 *
	 * @param array $new_meta      `meta.json` content of new component
	 * @param array $existing_meta `meta.json` content of existing component
	 *
	 * @return bool
	 */
	static protected function check_dependencies_satisfies_required_package ($new_meta, $existing_meta) {
		/**
		 * If we are not interested in component - just exit, otherwise compare required version with actual present
		 */
		if (
			!isset($new_meta['require'][$existing_meta['package']]) ||
			version_compare(
				$existing_meta['version'],
				$new_meta['require'][$existing_meta['package']][1],
				$new_meta['require'][$existing_meta['package']][0]
			)
		) {
			return true;
		}
		$L    = Language::instance();
		$Page = Page::instance();
		$key  = $existing_meta['category'] == 'modules' ? 'unsatisfactory_version_of_the_module' : 'unsatisfactory_version_of_the_plugin';
		$Page->warning(
			$L->$key(
				$existing_meta['package'],
				implode(
					' ',
					$new_meta['require'][$existing_meta['package']]
				),
				$existing_meta['version']
			)
		);
		return false;
	}
	/**
	 * Check for if component conflicts other components
	 *
	 * @param array $new_meta      `meta.json` content of new component
	 * @param array $existing_meta `meta.json` content of existing component
	 *
	 * @return bool
	 */
	static protected function check_dependencies_conflicts ($new_meta, $existing_meta) {
		/**
		 * Check whether two components conflict in any direction by direct conflicts
		 */
		return
			self::check_dependencies_conflicts_one_step($new_meta, $existing_meta) &&
			self::check_dependencies_conflicts_one_step($existing_meta, $new_meta);
	}
	/**
	 * @param array $meta_from
	 * @param array $meta_to
	 *
	 * @return bool
	 */
	static protected function check_dependencies_conflicts_one_step ($meta_from, $meta_to) {
		/**
		 * Check whether two components conflict in any direction by direct conflicts
		 */
		$L    = Language::instance();
		$Page = Page::instance();
		if (
			isset($meta_from['conflict'][$meta_to['package']]) &&
			version_compare(
				$meta_to['version'],
				$meta_from['conflict'][$meta_to['package']][1],
				$meta_from['conflict'][$meta_to['package']][0]
			)
		) {
			$Page->warning(
				$L->package_is_incompatible_with(
					$meta_from['package'],
					$meta_to['package'].
					($meta_from['conflict'][$meta_to['package']]
						? implode(
							' ',
							$meta_from['conflict'][$meta_to['package']]
						)
						: ''
					)
				)
			);
			return false;
		}
		return true;
	}
	/**
	 * Check backward dependencies (during uninstalling/disabling)
	 *
	 * @param array $meta `meta.json` contents of target component
	 *
	 * @return bool
	 */
	static protected function check_backward_dependencies ($meta) {
		/**
		 * No `meta.json` - nothing to check, allow it
		 */
		if (!$meta) {
			return true;
		}
		$meta              = self::normalize_meta($meta);
		$Config            = Config::instance();
		$L                 = Language::instance();
		$Page              = Page::instance();
		$required_by_other = false;
		/**
		 * Checking for backward dependencies of modules
		 */
		$check_result_modules = true;
		foreach ($Config->components['modules'] as $module => $module_data) {
			/**
			 * If module is not active, we compare module with itself or there is no `meta.json` - we do not care about it
			 */
			if (
				$module_data['active'] != 1 ||
				(
					$meta['category'] == 'modules' &&
					$meta['package'] == $module
				) ||
				!file_exists(MODULES."/$module/meta.json")
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
				$required_by_other = true;
				$Page->warning($L->this_package_is_used_by_module($module));
			}
		}
		unset($check_result_modules, $module, $module_data, $module_require);
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
				$required_by_other = true;
				$Page->warning($L->this_package_is_used_by_plugin($plugin));
			}
		}
		if ($required_by_other) {
			$Page->warning($L->dependencies_not_satisfied);
		}
		return !$required_by_other;
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
	static protected function normalize_meta ($meta) {
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
	static protected function dep_normal ($dependence_structure) {
		$return = [];
		foreach ((array)$dependence_structure as $d) {
			preg_match('/^([^<=>!]+)([<=>!]*)(.*)$/', $d, $d);
			$return[$d[1]] = [
				isset($d[2]) && $d[2] ? str_replace('=>', '>=', $d[2]) : (isset($d[3]) && $d[3] ? '=' : '>='),
				isset($d[3]) && $d[3] ? $d[3] : 0
			];
		}
		return $return;
	}
}
