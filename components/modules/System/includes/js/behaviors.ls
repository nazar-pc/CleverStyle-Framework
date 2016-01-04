/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L									= cs.Language
cs.{}Polymer.{}behaviors.{}admin.System	=
	components	:
		# Module/plugin enabling
		_enable_component : (component, component_type, meta) !->
			category		= component_type + 's'
			dependencies	<~! $.getJSON("api/System/admin/#category/#component/dependencies", _)
			# During enabling we don't care about those since module should be already installed
			delete dependencies.db_support
			delete dependencies.storage_support
			translation_key	= if component_type == 'module' then 'enabling_of_module' else 'enabling_of_plugin'
			title			= "<h3>#{L[translation_key](component)}</h3>"
			message			= ''
			message_more	= ''
			if Object.keys(dependencies).length
				message	= @_compose_dependencies_message(component, dependencies)
				if cs.simple_admin_mode
					cs.ui.notify(message, 'error', 5)
					return
			if meta && meta.optional
				message_more	+= '<p class="cs-text-success cs-block-success">' + L.for_complete_feature_set(meta.optional.join(', ')) + '</p>'
			modal	= cs.ui.confirm(
				"#title#message#message_more"
				!~>
					cs.Event.fire(
						"admin/System/components/#category/enable/before"
						name	: component
					).then !~>
						$.ajax(
							url		: "api/System/admin/#category/#component"
							type	: 'enable'
							success	: !~>
								@reload()
								cs.ui.notify(L.changes_saved, 'success', 5)
								cs.Event.fire(
									"admin/System/components/#category/enable/after"
									name	: component
								)
						)
			)
			modal.ok.innerHTML		= L[if !message then 'enable' else 'force_enable_not_recommended']
			modal.ok.primary		= !message
			modal.cancel.primary	= !modal.ok.primary
			$(modal).find('p:not([class])').addClass('cs-text-error cs-block-error')
		# Module/plugin disabling
		_disable_component : (component, component_type) !->
			category			= component_type + 's'
			dependent_packages	<~! $.getJSON("api/System/admin/#category/#component/dependent_packages", _)
			translation_key		= if component_type == 'module' then 'disabling_of_module' else 'disabling_of_plugin'
			title				= "<h3>#{L[translation_key](component)}</h3>"
			message				= ''
			if Object.keys(dependent_packages).length
				for type, packages of dependent_packages
					translation_key = if type == 'modules' then 'this_package_is_used_by_module' else 'this_package_is_used_by_plugin'
					for _package in packages
						message += "<p>#{L[translation_key](_package)}</p>"
				message += "<p>#{L.dependencies_not_satisfied}</p>"
				if cs.simple_admin_mode
					cs.ui.notify(message, 'error', 5)
					return
			modal	= cs.ui.confirm(
				"#title#message"
				!~>
					cs.Event.fire(
						"admin/System/components/#category/disable/before"
						name	: component
					).then !~>
						$.ajax(
							url		: "api/System/admin/#category/#component"
							type	: 'disable'
							success	: !~>
								@reload()
								cs.ui.notify(L.changes_saved, 'success', 5)
								cs.Event.fire(
									"admin/System/components/#category/disable/after"
									name	: component
								)
						)
			)
			modal.ok.innerHTML		= L[if !message then 'disable' else 'force_disable_not_recommended']
			modal.ok.primary		= !message
			modal.cancel.primary	= !modal.ok.primary
			$(modal).find('p').addClass('cs-text-error cs-block-error')
		# Module/plugin/theme update
		_update_component : (existing_meta, new_meta) !->
			component		= new_meta.package
			category		= new_meta.category
			dependencies	<~! $.getJSON("api/System/admin/#category/#component/update_dependencies", _)
			# During update we don't care about those since module should be already installed
			delete dependencies.db_support
			delete dependencies.storage_support
			translation_key	=
				switch category
				| 'modules'	=> (if component == 'System' then 'updating_of_system' else 'updating_of_module')
				| 'plugins'	=> 'updating_of_plugin'
				| 'themes'	=> 'updating_of_theme'
			title			= "<h3>#{L[translation_key](component)}</h3>"
			message			= ''
			translation_key	=
				switch category
				| 'modules'	=> (if component == 'System' then 'update_system' else 'update_module')
				| 'plugins'	=> 'update_plugin'
				| 'themes'	=> 'update_theme'
			message_more	= '<p class>' + L[translation_key](component, existing_meta.version, new_meta.version) + '</p>'
			if Object.keys(dependencies).length
				message	= @_compose_dependencies_message(component, dependencies)
				if cs.simple_admin_mode
					cs.ui.notify(message, 'error', 5)
					return
			if new_meta.optional
				message_more	+= '<p class="cs-text-success cs-block-success">' + L.for_complete_feature_set(new_meta.optional.join(', ')) + '</p>'
			modal	= cs.ui.confirm(
				"#title#message#message_more"
				!~>
					event_promise	=
						if component == 'System'
							cs.Event.fire('admin/System/components/modules/update_system/before')
						else
							cs.Event.fire(
								"admin/System/components/#category/update/before"
								name	: component
							)
					event_promise.then !~>
						$.ajax(
							url		: "api/System/admin/#category/#component"
							type	: 'update'
							success	: !~>
								cs.ui.notify(L.changes_saved, 'success', 5)
								if component == 'System'
									cs.Event.fire('admin/System/components/modules/update_system/after').then !->
										location.reload()
								else
									cs.Event.fire(
										"admin/System/components/#category/update/after"
										name	: component
									).then !->
										location.reload()
						)
			)
			modal.ok.innerHTML		= L[if !message then 'yes' else 'force_update_not_recommended']
			modal.ok.primary		= !message
			modal.cancel.primary	= !modal.ok.primary
			$(modal).find('p:not([class])').addClass('cs-text-error cs-block-error')
		# Module/plugin/theme complete removal
		_remove_completely_component : (component, category) !->
			translation_key		=
				switch category
				| 'modules' => 'completely_remove_module'
				| 'plugins'	=> 'completely_remove_plugin'
				| 'themes'	=> 'completely_remove_theme'
			<~! cs.ui.confirm(L[translation_key](component), _)
			$.ajax(
				url		: "api/System/admin/#category/#component"
				type	: 'delete'
				success	: !~>
					@reload()
					cs.ui.notify(L.changes_saved, 'success', 5)
			)
		# Compose HTML representation of dependencies details
		_compose_dependencies_message : (component, dependencies) ->
			message = ''
			for what, categories of dependencies
				for category, details of categories
					for detail in details
						message	+=
							"<p>" +
							(switch what
								case 'update_from'
									if component == 'System'
										L.update_system_impossible_from_version_to(detail.from, detail.to, detail.can_update_from)
									else
										L.module_cant_be_updated_from_version_to(component, detail.from, detail.to, detail.can_update_from)
								case 'update_older'
									translation_key =
										switch category
										| 'modules'	=> (if component == 'System' then 'update_system_impossible_older_version' else 'update_module_impossible_older_version')
										| 'plugins'	=> 'update_plugin_impossible_older_version'
										| 'themes'	=> 'update_theme_impossible_older_version'
									L[translation_key](detail.from, detail.to)
								case 'provide'
									translation_key =
										if category == 'modules'
											'module_already_provides_functionality'
										else
											'plugin_already_provides_functionality'
									L[translation_key](detail.name, detail.features.join('", "'))
								case 'require'
									for required in detail.required
										required	= if required[1] && required[1] !~= '0' then required.join(' ') else ''
										if category == 'unknown'
											L.package_or_functionality_not_found(detail.name + required)
										else
											translation_key =
												if category == 'modules'
													'unsatisfactory_version_of_the_module'
												else
													'unsatisfactory_version_of_the_plugin'
											L[translation_key](detail.name, required, detail.existing)
								case 'conflict'
									for conflict in detail.conflicts
										L.package_is_incompatible_with(conflict.package, conflict.conflicts_with, conflict.of_versions.filter(-> it !~= '0').join(' '))
								case 'db_support'
									L.compatible_databases_not_found(detail.supported.join('", "'))
								case 'storage_support'
									L.compatible_storages_not_found(detail.supported.join('", "'))
							) +
							"</p>"
			"#message<p>#{L.dependencies_not_satisfied}</p>"
	upload :
		# Generic ackage uploading, jqXHR object will be returned
		_upload_package : (file_input, progress) ->
			if !file_input.files.length
				throw new Error('file should be selected')
			form_data	= new FormData
			form_data.append('file', file_input.files[0])
			$.ajax(
				url			: 'api/System/admin/upload'
				type		: 'post'
				data		: form_data
				xhrFields	:
					onprogress	: progress || !->
				processData	: false
				contentType	: false
			)
	settings :
		properties	:
			settings_api_url	:
				observer	: '_reload_settings'
				type		: String
			settings			: Object
			simple_admin_mode	:
				computed	: '_simple_admin_mode(settings.simple_admin_mode)'
				type		: Boolean
		_simple_admin_mode : (simple_admin_mode) ->
			simple_admin_mode ~= 1
		_reload_settings : !->
			$.ajax(
				url		: @settings_api_url
				type	: 'get_settings'
				success	: (settings) !~>
					@set('settings', settings)
			)
		_apply : !->
			$.ajax(
				url		: @settings_api_url
				type	: 'apply_settings'
				data	: @settings
				success	: !~>
					@_reload_settings()
					cs.ui.notify(L.changes_applied + L.check_applied, 'warning', 5)
				error	: !->
					cs.ui.notify(L.changes_apply_error, 'error', 5)
			)
		_save : !->
			$.ajax(
				url		: @settings_api_url
				type	: 'save_settings'
				data	: @settings
				success	: !~>
					@_reload_settings()
					cs.ui.notify(L.changes_saved, 'success', 5)
			)
		_cancel : !->
			$.ajax(
				url		: @settings_api_url
				type	: 'cancel_settings'
				success	: !~>
					@_reload_settings()
					cs.ui.notify(L.changes_canceled, 'success', 5)
			)
