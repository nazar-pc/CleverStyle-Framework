/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L										= cs.Language('system_admin_')
cs.{}Polymer.{}behaviors.{}admin.System	=
	components	:
		# Module enabling
		_enable_module : (component, meta) !->
			cs.api([
				"get			api/System/admin/modules/#component/dependencies"
				'get_settings	api/System/admin/system'
			]).then ([dependencies, settings]) !~>
				# During enabling we don't care about those since module should be already installed
				delete dependencies.db_support
				delete dependencies.storage_support
				title			= "<h3>#{L.modules_enabling_of_module(component)}</h3>"
				message			= ''
				message_more	= ''
				if Object.keys(dependencies).length
					message	= @_compose_dependencies_message(component, dependencies)
					if settings.simple_admin_mode
						cs.ui.notify(message, 'error', 5)
						return
				if meta && meta.optional
					message_more	+= '<p class="cs-text-success cs-block-success">' + L.for_complete_feature_set(meta.optional.join(', ')) + '</p>'
				modal	= cs.ui.confirm(
					"#title#message#message_more"
					!~>
						cs.Event.fire(
							"admin/System/modules/enable/before"
							name	: component
						)
							.then -> cs.api("enable api/System/admin/modules/#component")
							.then !~>
								@reload()
								cs.ui.notify(L.changes_saved, 'success', 5)
								cs.Event.fire(
									"admin/System/modules/enable/after"
									name	: component
								)
				)
				modal.ok.innerHTML		= L[if !message then 'enable' else 'force_enable_not_recommended']
				modal.ok.primary		= !message
				modal.cancel.primary	= !modal.ok.primary
				for p in modal.querySelectorAll('p:not([class])')
					p.classList.add('cs-text-error', 'cs-block-error')
		# Module disabling
		_disable_module : (component) !->
			cs.api([
				"get			api/System/admin/modules/#component/dependent_packages"
				'get_settings	api/System/admin/system'
			]).then ([dependent_packages, settings]) !~>
				title				= "<h3>#{L.modules_disabling_of_module(component)}</h3>"
				message				= ''
				if Object.keys(dependent_packages).length
					# TODO: type is not needed here anymore, simplify data structure
					for type, packages of dependent_packages
						for _package in packages
							message += "<p>#{L.this_package_is_used_by_module(_package)}</p>"
					message += "<p>#{L.dependencies_not_satisfied}</p>"
					if settings.simple_admin_mode
						cs.ui.notify(message, 'error', 5)
						return
				modal	= cs.ui.confirm(
					"#title#message"
					!~>
						cs.Event.fire(
							"admin/System/modules/disable/before"
							name	: component
						)
							.then -> cs.api("disable api/System/admin/modules/#component")
							.then !~>
								@reload()
								cs.ui.notify(L.changes_saved, 'success', 5)
								cs.Event.fire(
									"admin/System/modules/disable/after"
									name	: component
								)
				)
				modal.ok.innerHTML		= L[if !message then 'disable' else 'force_disable_not_recommended']
				modal.ok.primary		= !message
				modal.cancel.primary	= !modal.ok.primary
				for p in modal.querySelectorAll('p')
					p.classList.add('cs-text-error', 'cs-block-error')
		# Module/theme update
		_update_component : (existing_meta, new_meta) !->
			component		= new_meta.package
			category		= new_meta.category
			cs.api([
				"get			api/System/admin/#category/#component/update_dependencies"
				'get_settings	api/System/admin/system'
			]).then ([dependencies, settings]) !~>
				# During update we don't care about those since module should be already installed
				delete dependencies.db_support
				delete dependencies.storage_support
				translation_key	=
					switch category
					| 'modules'	=> (if component == 'System' then 'modules_updating_of_system' else 'modules_updating_of_module')
					| 'themes'	=> 'appearance_updating_theme'
				title			= "<h3>#{L[translation_key](component)}</h3>"
				message			= ''
				if component == 'System'
					message_more	= '<p class>' + L.modules_update_system(existing_meta.version, new_meta.version) + '</p>'
				else
					translation_key	=
						switch category
						| 'modules'	=> 'modules_update_module'
						| 'themes'	=> 'appearance_update_theme'
					message_more	= '<p class>' + L[translation_key](component, existing_meta.version, new_meta.version) + '</p>'
				if Object.keys(dependencies).length
					message	= @_compose_dependencies_message(component, dependencies)
					if settings.simple_admin_mode
						cs.ui.notify(message, 'error', 5)
						return
				if new_meta.optional
					message_more	+= '<p class="cs-text-success cs-block-success">' + L.for_complete_feature_set(new_meta.optional.join(', ')) + '</p>'
				modal	= cs.ui.confirm(
					"#title#message#message_more"
					!~>
						(
							if component == 'System'
								cs.Event.fire('admin/System/modules/update_system/before')
							else
								cs.Event.fire(
									"admin/System/#category/update/before"
									name	: component
								)
						)
							.then -> cs.api("update api/System/admin/#category/#component")
							.then ->
								cs.ui.notify(L.changes_saved, 'success', 5)
								if component == 'System'
									cs.Event.fire('admin/System/modules/update_system/after')
								else
									cs.Event.fire(
										"admin/System/#category/update/after"
										name	: component
									)
							.then(location~reload)
				)
				modal.ok.innerHTML		= L[if !message then 'yes' else 'force_update_not_recommended']
				modal.ok.primary		= !message
				modal.cancel.primary	= !modal.ok.primary
				for p in modal.querySelectorAll('p:not([class])')
					p.classList.add('cs-text-error', 'cs-block-error')
		# Module/theme complete removal
		_remove_completely_component : (component, category) !->
			translation_key		=
				switch category
				| 'modules' => 'modules_completely_remove_module'
				| 'themes'	=> 'appearance_completely_remove_theme'
			cs.ui.confirm(L[translation_key](component))
				.then -> cs.api("delete api/System/admin/#category/#component")
				.then !~>
					@reload()
					cs.ui.notify(L.changes_saved, 'success', 5)
		# Compose HTML representation of dependencies details
		_compose_dependencies_message : (component, dependencies) ->
			message = ''
			for what, categories of dependencies
				if categories instanceof Array
					categories = {categories : [categories]}
				for category, details of categories
					for detail in details
						message	+=
							"""<p class="cs-block-error cs-text-error">""" +
							(switch what
								case 'update_from'
									if component == 'System'
										L.modules_update_system_impossible_from_version_to(detail.from, detail.to, detail.can_update_from)
									else
										L.modules_module_cant_be_updated_from_version_to(component, detail.from, detail.to, detail.can_update_from)
								case 'update_older'
									translation_key =
										switch category
										| 'modules'	=> (if component == 'System' then 'modules_update_system_impossible_older_version' else 'modules_update_module_impossible_older_version')
										| 'themes'	=> 'appearance_update_theme_impossible_older_version'
									L[translation_key](component, detail.from, detail.to)
								case 'update_same'
									translation_key =
										switch category
										| 'modules'	=> (if component == 'System' then 'modules_update_system_impossible_same_version' else 'modules_update_module_impossible_same_version')
										| 'themes'	=> 'appearance_update_theme_impossible_same_version'
									L[translation_key](component, detail.version)
								case 'provide'
									L.module_already_provides_functionality(detail.name, detail.features.join('", "'))
								case 'require'
									for required in detail.required
										required	= if required[1] && required[1] !~= '0' then required.join(' ') else ''
										if category == 'unknown'
											L.package_or_functionality_not_found(detail.name + required)
										else
											L.modules_unsatisfactory_version_of_the_module(detail.name, required, detail.existing)
								case 'conflict'
									for conflict in detail.conflicts
										L.package_is_incompatible_with(conflict.package, conflict.conflicts_with, conflict.of_versions.filter(-> it !~= '0').join(' '))
								case 'db_support'
									L.modules_compatible_databases_not_found(detail.join('", "'))
								case 'storage_support'
									L.modules_compatible_storages_not_found(detail.join('", "'))
							) +
							"</p>"
			"""#message<p class="cs-block-error cs-text-error">#{L.dependencies_not_satisfied}</p>"""
	upload :
		# Generic package uploading, jqXHR object will be returned
		_upload_package : (file_input) ->
			if !file_input.files.length
				throw new Error('file should be selected')
			form_data	= new FormData
			form_data.append('file', file_input.files[0])
			cs.api('post api/System/admin/upload', form_data)
	settings :
		properties	:
			settings_api_url	:
				observer	: '_reload_settings'
				type		: String
			settings			: Object
			simple_admin_mode	: Boolean
		_reload_settings : !->
			cs.api([
				'get_settings ' + @settings_api_url
				'get_settings api/System/admin/system'
			]).then ([settings, system_settings]) !~>
				@simple_admin_mode	= system_settings.simple_admin_mode == 1
				@set('settings', settings)
		_apply : !->
			cs.api('apply_settings ' + @settings_api_url, @settings).then !~>
				@_reload_settings()
				cs.ui.notify(L.changes_applied, 'warning', 5)
		_save : !->
			cs.api('save_settings ' + @settings_api_url, @settings).then  !~>
				@_reload_settings()
				cs.ui.notify(L.changes_saved, 'success', 5)
		_cancel : !->
			cs.api('cancel_settings ' + @settings_api_url).then !~>
				@_reload_settings()
				cs.ui.notify(L.changes_canceled, 'success', 5)
