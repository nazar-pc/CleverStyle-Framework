<?php
function system_input_core ($item, $type = 'text', $info_item = null, $disabled = false, $min = false, $max = false, $post_text = '') {
	global $Config;
	if ($type != 'radio') {
		return [
			h::info($info_item ?: $item),
			h::input([
				'name'		=> "core[$item]",
				'value'		=> $Config->core[$item],
				'class'		=> 'cs-form-element',
				'min'		=> $min,
				'max'		=> $max,
				'type'		=> $type,
				($disabled ? 'disabled' : '')
			]).
			$post_text
		];
	} else {
		global $L;
		return [
			h::info($info_item ?: $item),
			h::input([
				'name'		=> "core[$item]",
				'checked'	=> $Config->core[$item],
				'value'		=> [0, 1],
				'in'		=> [$L->off, $L->on],
				'type'		=> $type
			])
		];
	}
}
function system_textarea_core ($item, $wide = true, $editor = null, $info_item = null) {
	global $Config;
	return [
		h::info($info_item ?: $item),
		h::textarea(
			$Config->core[$item],
			[
				'name'	=> "core[$item]",
				'class'	=> 'cs-form-element'.($wide ? ' cs-wide-textarea' : '').($editor ? ' '.$editor : '')
			]
		)
	];
}
function system_select_core ($items_array, $item, $id = null, $info_item = null, $multiple = false, $size = 5) {
	global $Config;
	return [
		h::info($info_item ?: $item),
		h::select(
			$items_array,
			[
				'name'		=> "core[$item]".($multiple ? '[]' : ''),
				'selected'	=> $Config->core[$item],
				'size'		=> $size,
				'id'		=> $id ?: false,
				'class'		=> 'cs-form-element',
				$multiple ? 'multiple' : false
			]
		)
	];
}
function check_dependencies ($name, $type = 'module') {
	switch ($type) {
		case 'module':
			$dir	= MODULES.'/'.$name;
		break;
		case 'plugin':
			$dir	= PLUGINS.'/'.$name;
		break;
		default:
			return false;
	}
	if (!file_exists($dir.'/meta.json')) {
		return true;
	}
	$meta		= _json_decode(file_get_contents($dir.'/meta.json'));
	global $Config, $Page, $L;
	if (isset($meta['db_support']) && !empty($meta['db_support'])) {
		$return		= false;
		foreach ($Config->db as $database) {
			if (in_array($database, $meta['db_support'])) {
				$return	= true;
				break;
			}
		}
		if (!$return) {
			$Page->warning(
				$L->compatible_databases_not_found(
					implode('", "', $meta['db_support'])
				)
			);
		}
	} else {
		$return		= true;
	}
	$provide	= [];
	$require	= [];
	$conflict	= [];
	if (isset($meta['provide'])) {
		$provide	= (array)$meta['provide'];
	}
	/**
	 * Function for normalization of dependence structure
	 *
	 * @param array	$dependence_structure
	 */
	$dep_normal = function ($dependence_structure) {
		$return	= [];
		foreach ($dependence_structure as $d) {//TODO reexp package name and version in require and conflict "somename=>1.0.5"
			if (is_array($d)) {
				$return[$d[0]]	= [
					isset($d[1]) ? $d[1] : 0,
					isset($d[2]) ? $d[2] : (isset($d[1]) ? '=' : '=>')
				];
			} else {
				$return[$d]	= [
					0,
					'=>'
				];
			}
		}
	};
	if (isset($meta['require']) && !empty($meta['require'])) {
		$require	= $dep_normal((array)$meta['require']);
	}
	if (isset($meta['conflict']) && !empty($meta['conflict'])) {
		$conflict	= $dep_normal((array)$meta['conflict']);
	}
	unset($meta, $dep_normal);
	/**
	 * Checking for compatibility with modules
	 */
	foreach ($Config->components['modules'] as $module) {
		if (
			$module['active'] == -1 ||
			(
				$module == $name && $type == 'module'
			) ||
			!file_exists(MODULES.'/'.$module.'/meta.json')
		) {
			continue;
		}
		$module_meta	= _json_decode(file_get_contents(MODULES.'/'.$module.'/meta.json'));
		/**
		 * If some module already provides the same functionality
		 */
		if (
			!empty($provide) &&
			isset($module_meta['provide']) &&
			is_array($module_meta['provide']) &&
			!empty($module_meta['provide']) &&
			$intersect = array_intersect($provide, $module_meta['provide'])
		) {
			if ($return) {
				$Page->warning($L->dependencies_not_satisfied);
			}
			$return	= false;
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
		if (isset($require[$module_meta['package']])) {
			if (
				version_compare(
					$require[$module_meta['package']][0],
					$module_meta['version'],
					$require[$module_meta['package']][1]
				)
			) {
				unset($require[$module_meta['package']]);
			} else {
				if ($return) {
					$Page->warning($L->dependencies_not_satisfied);
				}
				$return	= false;
				$Page->warning(
					$L->unsatisfactory_version_of_the_module_package(
						$module_meta['package'],
						$module,
						$require[$module_meta['package']][1].' '.$require[$module_meta['package']][0],
						$module_meta['version']
					)
				);
			}
		}
		/**
		 * Checking for conflict packages
		 */
		if (isset($module_meta['conflict']) && is_array($module_meta['conflict']) && !empty($module_meta['conflict'])) {
			if (
				version_compare(
					$conflict[$module_meta['package']][0],
					$module_meta['version'],
					$conflict[$module_meta['package']][1]
				)
			) {
				if ($return) {
					$Page->warning($L->dependencies_not_satisfied);
				}
				$return	= false;
				$Page->warning(
					$L->conflict_module_package(
						$module_meta['package'],
						$module
					).
					(
						$conflict[$module_meta['package']][0] != 0 ? $L->compatible_package_versions(
							$require[$module_meta['package']][1].' '.$require[$module_meta['package']][0]
						) : $L->package_is_incompatible(
							$module_meta['package']
						)
					)
				);
			}
		}
	}
	unset($module, $module_meta);
	/**
	 * Checking for compatibility with plugins
	 */
	foreach ($Config->components['plugins'] as $plugin) {
		if (
			$plugin['active'] == 0 ||
			(
				$plugin == $name && $type == 'plugin'
			) ||
			!file_exists(PLUGINS.'/'.$plugin.'/meta.json')
		) {
			continue;
		}
		$plugin_meta	= _json_decode(file_get_contents(PLUGINS.'/'.$plugin.'/meta.json'));
		/**
		 * If some plugin already provides the same functionality
		 */
		if (
			!empty($provide) &&
			isset($plugin_meta['provide']) &&
			is_array($plugin_meta['provide']) &&
			!empty($plugin_meta['provide']) &&
			$intersect = array_intersect($provide, $plugin_meta['provide'])
		) {
			if ($return) {
				$Page->warning($L->dependencies_not_satisfied);
			}
			$return	= false;
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
					$require[$plugin_meta['package']][0],
					$plugin_meta['version'],
					$require[$plugin_meta['package']][1]
				)
			) {
				unset($require[$plugin_meta['package']]);
			} else {
				if ($return) {
					$Page->warning($L->dependencies_not_satisfied);
				}
				$return	= false;
				$Page->warning(
					$L->unsatisfactory_version_of_the_plugin_package(
						$plugin_meta['package'],
						$plugin,
						$require[$plugin_meta['package']][1].' '.$require[$plugin_meta['package']][0],
						$plugin_meta['version']
					)
				);
			}
		}
		/**
		 * Checking for conflict packages
		 */
		if (isset($plugin_meta['conflict']) && is_array($plugin_meta['conflict']) && !empty($plugin_meta['conflict'])) {
			if (
				version_compare(
					$conflict[$plugin_meta['package']][0],
					$plugin_meta['version'],
					$conflict[$plugin_meta['package']][1]
				)
			) {
				if ($return) {
					$Page->warning($L->dependencies_not_satisfied);
				}
				$return	= false;
				$Page->warning(
					$L->conflict_plugin_package(
						$plugin_meta['package'],
						$plugin
					).
					(
						$conflict[$plugin_meta['package']][0] != 0 ? $L->compatible_package_versions(
							$require[$plugin_meta['package']][1].' '.$require[$plugin_meta['package']][0]
						) : $L->package_is_incompatible(
							$plugin_meta['package']
						)
					)
				);
			}
		}
	}
	unset($plugin, $plugin_meta);
	/**
	 * If some required packages missing
	 */
	if (!empty($require)) {
		foreach ($require as $package => $details) {
			if ($return) {
				$Page->warning($L->dependencies_not_satisfied);
			}
			$return					= false;
			$Page->warning(
				$L->package_not_found(
					$package.' '.$details[1].' '.$details[0]
				)
			);
		}
	}
	return $return;
}