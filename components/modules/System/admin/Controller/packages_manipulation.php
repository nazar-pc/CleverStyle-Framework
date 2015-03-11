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
		foreach (file_get_json("$target_directory/versions.json") as $version) {
			if (version_compare($old_version, $version, '<')) {
				/**
				 * PHP update script
				 */
				_include("$target_directory/meta/update/$version.php", true, false);
				/**
				 * Database update
				 */
				if ($db_array && file_exists("$target_directory/meta/db.json")) {
					$db_json = file_get_json("$target_directory/meta/db.json");
					time_limit_pause();
					foreach ($db_json as $database) {
						if ($db_array[$database] == 0) {
							$db_type = $Core->db_type;
						} else {
							$db_type = $Config->db[$db_array[$database]]['type'];
						}
						$sql_file = "$target_directory/meta/update_db/$database/$version/$db_type.sql";
						if (isset($db_array[$database]) && file_exists($sql_file)) {
							$db->{$db_array[$database]}()->q(
								explode(';', file_get_contents($sql_file))
							);
						}
					}
					unset($db_json, $database, $db_type, $sql_file);
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
	 * @param string      $name Name of new component
	 * @param string      $type Type of new component module|plugin
	 * @param null|string $dir  Path to new component (if null - component should be found among installed)
	 * @param string      $mode Mode of checking for modules install|update|enable
	 *
	 * @return bool
	 */
	static protected function check_dependencies ($name, $type, $dir = null, $mode = 'enable') {
		if (!$dir) {
			switch ($type) {
				case 'module':
					$dir = MODULES."/$name";
					break;
				case 'plugin':
					$dir = PLUGINS."/$name";
					break;
				default:
					return false;
			}
		}
		if (!file_exists("$dir/meta.json")) {
			return true;
		}
		$meta   = file_get_json("$dir/meta.json");
		$Config = Config::instance();
		$Core   = Core::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		if (isset($meta['db_support']) && !empty($meta['db_support'])) {
			$return = false;
			if (in_array($Core->db_type, $meta['db_support'])) {
				$return = true;
			} else {
				foreach ($Config->db as $database) {
					if (isset($database['type']) && in_array($database['type'], $meta['db_support'])) {
						$return = true;
						break;
					}
				}
				unset($database);
			}
			if (!$return) {
				$Page->warning(
					$L->compatible_databases_not_found(
						implode('", "', $meta['db_support'])
					)
				);
			} elseif (!$Config->core['simple_admin_mode']) {
				$Page->success(
					$L->compatible_databases(
						implode('", "', $meta['db_support'])
					)
				);
			}
		} else {
			$return = true;
		}
		if (isset($meta['storage_support']) && !empty($meta['storage_support'])) {
			$return_s = false;
			if (in_array($Core->storage_type, $meta['storage_support'])) {
				$return_s = true;
			} else {
				foreach ($Config->storage as $storage) {
					if (in_array($storage['connection'], $meta['storage_support'])) {
						$return_s = true;
						break;
					}
				}
				unset($storage);
			}
			if (!$return_s) {
				$Page->warning(
					$L->compatible_storages_not_found(
						implode('", "', $meta['storage_support'])
					)
				);
			} elseif (!$Config->core['simple_admin_mode']) {
				$Page->success(
					$L->compatible_storages(
						implode('", "', $meta['storage_support'])
					)
				);
			}
			$return = $return && $return_s;
			unset($return_s);
		}
		$provide  = [];
		$require  = [];
		$conflict = [];
		if (isset($meta['provide'])) {
			$provide = (array)$meta['provide'];
		}
		if (isset($meta['require']) && !empty($meta['require'])) {
			$require = self::dep_normal((array)$meta['require']);
		}
		if (isset($meta['conflict']) && !empty($meta['conflict'])) {
			$conflict = self::dep_normal((array)$meta['conflict']);
		}
		unset($meta);
		/**
		 * Checking for compatibility with modules
		 */
		$return_m = true;
		foreach ($Config->components['modules'] as $module => $module_data) {
			/**
			 * If module uninstalled, disabled (in enable check mode), module name is the same as checked or meta.json file absent
			 * Then skip this module
			 */
			if (!file_exists(MODULES."/$module/meta.json")) {
				continue;
			}
			$module_meta = file_get_json(MODULES."/$module/meta.json");
			/** @noinspection NotOptimalIfConditionsInspection */
			if (
				$module_data['active'] == -1 ||
				(
					$mode == 'enable' && $module_data['active'] == 0
				) ||
				(
					$module == $name && $type == 'module'
				)
			) {
				/**
				 * If module updates, check update possibility from current version
				 */
				if (
					$module == $name && $type == 'module' && $mode == 'update' &&
					isset($meta['update_from']) && version_compare($meta['update_from_version'], $module_meta['version'], '>')
				) {
					if ($return_m) {
						$Page->warning($L->dependencies_not_satisfied);
					}
					$Page->warning(
						$L->module_cant_be_updated_from_version_to_supported_only(
							$module,
							$module_meta['version'],
							$meta['version'],
							$meta['update_from_version']
						)
					);
					return false;
				}
				continue;
			}
			/**
			 * If some module already provides the same functionality
			 */
			if (
				!empty($provide) &&
				isset($module_meta['provide']) &&
				!empty($module_meta['provide']) &&
				$intersect = array_intersect($provide, (array)$module_meta['provide'])
			) {
				if ($return_m) {
					$Page->warning($L->dependencies_not_satisfied);
				}
				$return_m = false;
				$Page->warning(
					$L->module_already_provides_functionality(
						$module,
						implode('", "', $intersect)
					)
				);
			}
			unset($intersect);
			/**
			 * Checking for required packages
			 */
			if (!empty($require) && isset($require[$module_meta['package']])) {
				if (
				version_compare(
					$module_meta['version'],
					$require[$module_meta['package']][1],
					$require[$module_meta['package']][0]
				)
				) {
					unset($require[$module_meta['package']]);
				} else {
					if ($return_m) {
						$Page->warning($L->dependencies_not_satisfied);
					}
					$return_m = false;
					$Page->warning(
						$L->unsatisfactory_version_of_the_module(
							$module,
							$require[$module_meta['package']][0].' '.$require[$module_meta['package']][1],
							$module_meta['version']
						)
					);
				}
			}
			/**
			 * Checking for required functionality
			 */
			if (
				!empty($require) &&
				isset($module_meta['provide']) &&
				!empty($module_meta['provide'])
			) {
				foreach ((array)$module_meta['provide'] as $p) {
					unset($require[$p]);
				}
				unset($p);
			}
			/**
			 * Checking for conflict packages
			 */
			if (
				!empty($conflict) &&
				isset($module_meta['conflict']) &&
				version_compare(
					$module_meta['version'],
					$conflict[$module_meta['package']][1],
					$conflict[$module_meta['package']][0]
				)
			) {
				if ($return_m) {
					$Page->warning($L->dependencies_not_satisfied);
				}
				$return_m = false;
				$Page->warning(
					$L->conflict_module(
						$module_meta['package'],
						$module
					).
					(
					$conflict[$module_meta['package']][1] != 0 ? $L->compatible_package_versions(
						$require[$module_meta['package']][0].' '.$require[$module_meta['package']][1]
					) : $L->package_is_incompatible(
						$module_meta['package']
					)
					)
				);
			}
		}
		$return = $return && $return_m;
		unset($return_m, $module, $module_data, $module_meta);
		/**
		 * Checking for compatibility with plugins
		 */
		$return_p = true;
		foreach ($Config->components['plugins'] as $plugin) {
			if (
				(
					$plugin == $name && $type == 'plugin'
				) ||
				!file_exists(PLUGINS."/$plugin/meta.json")
			) {
				continue;
			}
			$plugin_meta = file_get_json(PLUGINS."/$plugin/meta.json");
			/**
			 * If some plugin already provides the same functionality
			 */
			if (
				!empty($provide) &&
				isset($plugin_meta['provide']) &&
				is_array($plugin_meta['provide']) &&
				$intersect = array_intersect($provide, $plugin_meta['provide'])
			) {
				if ($return_p) {
					$Page->warning($L->dependencies_not_satisfied);
				}
				$return_p = false;
				$Page->warning(
					$L->plugin_already_provides_functionality(
						$plugin,
						implode('", "', $intersect)
					)
				);
			}
			unset($intersect);
			/**
			 * Checking for required packages
			 */
			if (isset($require[$plugin_meta['package']])) {
				if (
				version_compare(
					$plugin_meta['version'],
					$require[$plugin_meta['package']][1],
					$require[$plugin_meta['package']][0]
				)
				) {
					unset($require[$plugin_meta['package']]);
				} else {
					if ($return_p) {
						$Page->warning($L->dependencies_not_satisfied);
					}
					$return_p = false;
					$Page->warning(
						$L->unsatisfactory_version_of_the_plugin(
							$plugin,
							$require[$plugin_meta['package']][0].' '.$require[$plugin_meta['package']][1],
							$plugin_meta['version']
						)
					);
				}
			}
			/**
			 * Checking for required functionality
			 */
			if (
				!empty($require) &&
				isset($plugin_meta['provide']) &&
				!empty($plugin_meta['provide'])
			) {
				foreach ((array)$plugin_meta['provide'] as $p) {
					unset($require[$p]);
				}
				unset($p);
			}
			/**
			 * Checking for conflict packages
			 */
			if (
				isset($plugin_meta['conflict']) &&
				is_array($plugin_meta['conflict']) &&
				version_compare(
					$plugin_meta['version'],
					$conflict[$plugin_meta['package']][1],
					$conflict[$plugin_meta['package']][0]
				)
			) {
				if ($return_p) {
					$Page->warning($L->dependencies_not_satisfied);
				}
				$return_p = false;
				$Page->warning(
					$L->conflict_plugin($plugin).
					(
					$conflict[$plugin_meta['package']][1] != 0 ? $L->compatible_package_versions(
						$require[$plugin_meta['package']][0].' '.$require[$plugin_meta['package']][1]
					) : $L->package_is_incompatible(
						$plugin_meta['package']
					)
					)
				);
			}
		}
		$return = $return && $return_p;
		unset($return_p, $plugin, $plugin_meta, $provide, $conflict);
		/**
		 * If some required packages missing
		 */
		$return_r = true;
		if (!empty($require)) {
			foreach ($require as $package => $details) {
				if ($return_r) {
					$Page->warning($L->dependencies_not_satisfied);
				}
				$return_r = false;
				$Page->warning(
					$L->package_or_functionality_not_found($details[1] ? "$package $details[0] $details[1]" : $package)
				);
			}
		}
		return $return && $return_r;
	}
	/**
	 * Check backward dependencies (during uninstalling/disabling)
	 *
	 * @param string $name Component name
	 * @param string $type Component type module|plugin
	 * @param string $mode Mode of checking for modules uninstall|disable
	 *
	 * @return bool
	 */
	static protected function check_backward_dependencies ($name, $type = 'module', $mode = 'disable') {
		switch ($type) {
			case 'module':
				$dir = MODULES."/$name";
				break;
			case 'plugin':
				$dir = PLUGINS."/$name";
				break;
			default:
				return false;
		}
		if (!file_exists("$dir/meta.json")) {
			return true;
		}
		$meta   = file_get_json("$dir/meta.json");
		$return = true;
		$Config = Config::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		/**
		 * Checking for backward dependencies of modules
		 */
		$return_m = true;
		foreach ($Config->components['modules'] as $module => $module_data) {
			/**
			 * If module uninstalled, disabled (in disable check mode), module name is the same as checking or meta.json file does not exists
			 * Then skip this module
			 */
			/** @noinspection NotOptimalIfConditionsInspection */
			if (
				$module_data['active'] == -1 ||
				(
					$mode == 'disable' && $module_data['active'] == 0
				) ||
				(
					$module == $name && $type == 'module'
				) ||
				!file_exists(MODULES."/$module/meta.json")
			) {
				continue;
			}
			$module_require = file_get_json(MODULES."/$module/meta.json");
			if (!isset($module_require['require'])) {
				continue;
			}
			$module_require = self::dep_normal($module_require['require']);
			if (
				isset($module_require[$meta['package']]) ||
				(
					isset($meta['provide']) && array_intersect(array_keys($module_require), (array)$meta['provide'])
				)
			) {
				if ($return_m) {
					$Page->warning($L->dependencies_not_satisfied);
				}
				$return_m = false;
				$Page->warning($L->this_package_is_used_by_module($module));
			}
		}
		$return = $return && $return_m;
		unset($return_m, $module, $module_data, $module_require);
		/**
		 * Checking for backward dependencies of plugins
		 */
		$return_p = true;
		foreach ($Config->components['plugins'] as $plugin) {
			if (
				(
					$plugin == $name && $type == 'plugin'
				) ||
				!file_exists(PLUGINS."/$plugin/meta.json")
			) {
				continue;
			}
			$plugin_require = file_get_json(PLUGINS."/$plugin/meta.json");
			if (!isset($plugin_require['require'])) {
				continue;
			}
			$plugin_require = self::dep_normal($plugin_require['require']);
			if (
				isset($plugin_require[$meta['package']]) ||
				(
					isset($meta['provide']) && array_intersect(array_keys($plugin_require), (array)$meta['provide'])
				)
			) {
				if ($return_p) {
					$Page->warning($L->dependencies_not_satisfied);
				}
				$return_p = false;
				$Page->warning($L->this_package_is_used_by_plugin($plugin));
			}
		}
		return $return && $return_p;
	}
	/**
	 * Function for normalization of dependence structure
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
