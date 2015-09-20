/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L						= cs.Language
behaviors				= cs.Polymer.behaviors
behaviors.admin			= behaviors.admin || {}
behaviors.admin.System	=
	components	:
		# Module/plugin enabling
		_enable_component : (component, component_type, meta) !->
			component_type_s	= component_type + 's'
			dependencies		<~! $.getJSON("api/System/admin/#component_type_s/#component/dependencies", _)
			# During enabling we don't care about those since module should be already installed
			delete dependencies.db_support
			delete dependencies.storage_support
			translation_key		= if component_type == 'module' then 'enabling_of_module' else 'enabling_of_plugin'
			title				= "<h3>#{L[translation_key](component)}</h3>"
			message				= ''
			message_more		= ''
			if Object.keys(dependencies).length
				message	= @_compose_dependencies_message(component, dependencies)
				if cs.simple_admin_mode
					cs.ui.notify(message, 'error', 5)
					return
			if meta.optional
				message_more	+= '<p class="cs-text-success cs-block-success">' + L.for_complete_feature_set(meta.optional.join(', ')) + '</p>'
			modal	= cs.ui.confirm(
				"#title#message#message_more"
				!~>
					cs.Event.fire(
						"admin/System/components/#component_type_s/enable/before"
						name	: component
					).then !~>
						$.ajax(
							url		: "api/System/admin/#component_type_s/#component"
							type	: 'enable'
							success	: !~>
								@reload()
								cs.ui.notify(L.changes_saved, 'success', 5)
								cs.Event.fire(
									"admin/System/components/#component_type_s/enable/after"
									name	: component
								)
						)
			)
			modal.ok.innerHTML		= L[if !message then 'enable' else 'force_enable_not_recommended']
			modal.ok.primary		= !message
			modal.cancel.primary	= !modal.ok.primary
			$(modal).find('p:not([class])').addClass('cs-text-error cs-block-error')
		# Compose HTML representation of dependencies details
		_compose_dependencies_message : (component, dependencies) ->
			message = ''
			for what, components_types of dependencies
				for component_type, details of components_types
					for detail in details
						message	+=
							"<p>" +
							(switch what
								case 'update_problem'
									L.module_cant_be_updated_from_version_to_supported_only(component, detail.from, detail.to, detail.can_update_from)
								case 'provide'
									translation_key =
										if component_type == 'modules'
											'module_already_provides_functionality'
										else
											'plugin_already_provides_functionality'
									L[translation_key](detail.name, detail.features.join('", "'))
								case 'require'
									for conflict in detail.conflicts
										if component_type == 'unknown'
											L.package_or_functionality_not_found(conflict.name + conflict.required.join(' '))
										else
											translation_key =
												if component_type == 'modules'
													'unsatisfactory_version_of_the_module'
												else
													'unsatisfactory_version_of_the_plugin'
											L[translation_key](detail.name, conflict.join(' '), detail.existing)
								case 'conflict'
									for conflict in detail.conflicts
										L.package_is_incompatible_with(conflict.package, conflict.conflicts_with, conflict.of_versions.join(' '))
								case 'db_support'
									L.compatible_databases_not_found(detail.supported.join('", "'))
								case 'storage_support'
									L.compatible_storages_not_found(detail.supported.join('", "'))
							) +
							"</p>"
			"#message<p>#{L.dependencies_not_satisfied}</p>"
		# Module/plugin disabling
		_disable_component : (component, component_type) !->
			component_type_s	= component_type + 's'
			dependent_packages	<~! $.getJSON("api/System/admin/#component_type_s/#component/dependent_packages", _)
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
						"admin/System/components/#component_type_s/disable/before"
						name	: component
					).then !~>
						$.ajax(
							url		: "api/System/admin/#component_type_s/#component"
							type	: 'disable'
							success	: !~>
								@reload()
								cs.ui.notify(L.changes_saved, 'success', 5)
								cs.Event.fire(
									"admin/System/components/#component_type_s/disable/after"
									name	: component
								)
						)
			)
			modal.ok.innerHTML		= L[if !message then 'disable' else 'force_disable_not_recommended']
			modal.ok.primary		= !message
			modal.cancel.primary	= !modal.ok.primary
			$(modal).find('p').addClass('cs-text-error cs-block-error')
		# Module/plugin complete removal
		_remove_completely_component : (component, component_type) !->
			component_type_s	= component_type + 's'
			translation_key		= if component_type == 'module' then 'completely_remove_module' else 'completely_remove_plugin'
			<~! cs.ui.confirm(L[translation_key](component), _)
			$.ajax(
				url		: "api/System/admin/#component_type_s/#component"
				type	: 'delete'
				success	: !~>
					@reload()
					cs.ui.notify(L.changes_saved, 'success', 5)
			)
