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
		# Module/plugin disabling
		_disable_component : (component, component_type) !->
			component_type_s	= component_type + 's'
			translation_key		= if component_type == 'module' then 'disabling_of_module' else 'disabling_of_plugin'
			title				= "<h3>#{L[translation_key](component)}</h3>"
			message				= ''
			dependent_packages	<~! $.getJSON("api/System/admin/#component_type_s/#component/dependent_packages", _)
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
