/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L = cs.Language
active_switch	= (disabled, enabled) ->
	switch @active
	| 0		=> disabled
	| 1		=> enabled
Polymer(
	'is'		: 'cs-system-admin-components-plugins-list'
	behaviors	: [cs.Polymer.behaviors.Language]
	ready : !->
		@reload()
	reload : ->
		plugins <~! $.getJSON('api/System/admin/plugins', _)
		plugins.forEach (plugin) !->
			active_switch_local		= active_switch.bind(plugin)
			plugin.class			= active_switch_local('cs-block-warning cs-text-warning', 'cs-block-success cs-text-success')
			plugin.icon				= active_switch_local('minus', 'check')
			plugin.icon_text		= active_switch_local(L.disabled, L.enabled)
			plugin.name_localized	= L[plugin.name] || plugin.name.replace('_', ' ')
			do !->
				for prop in ['license', 'readme']
					if plugin[prop]?.type
						tag						= if plugin[prop].type == 'txt' then 'pre' else 'div'
						plugin[prop].content	= "<#tag>#{plugin[prop].content}</#tag>"
			if plugin.meta
				plugin.info	= let (@ = plugin.meta)
					L.plugin_info(
						@package,
						@version,
						@description,
						@author,
						@website || L.none,
						@license,
						if @provide then [].concat(@provide).join(', ') else L.none,
						if @require then [].concat(@require).join(', ') else L.none,
						if @conflict then [].concat(@conflict).join(', ') else L.none,
						if @optional then [].concat(@optional).join(', ') else L.none,
						if @multilingual && @multilingual.indexOf('interface') != -1 then L.yes else L.no,
						if @multilingual && @multilingual.indexOf('content') != -1 then L.yes else L.no,
						if @languages then @languages.join(', ') else L.none
					)
		@set('plugins', plugins)
	/**
	 * Provides next events:
	 *  aadmin/System/components/plugins/disable/before
	 *  {name : plugin_name}
	 *
	 *  admin/System/components/plugins/disable/after
	 *  {name : plugin_name}
	 */
	_disable : (e) !->
		plugin	= e.model.plugin.name
		dependent_packages <~! $.getJSON("api/System/admin/plugins/#plugin/dependent_packages", _)
		title	= "<h3>#{L.disabling_of_plugin(plugin)}</h3>"
		message	= ''
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
					'admin/System/components/plugins/disable/before'
					name	: plugin
				).then !~>
					$.ajax(
						url		: 'api/System/admin/plugins/' + plugin
						type	: 'disable'
						success	: !~>
							@reload()
							cs.ui.notify(L.changes_saved, 'success', 5)
							cs.Event.fire(
								'admin/System/components/plugins/disable/after'
								name	: plugin
							)
					)
		)
		modal.ok.innerHTML		= L[if !message then 'disable' else 'force_disable_not_recommended']
		modal.ok.primary		= !message
		modal.cancel.primary	= !modal.ok.primary
		$(modal).find('p').addClass('cs-text-error cs-block-error')
	_remove_completely : (e) !->
		plugin = e.model.plugin.name
		<~! cs.ui.confirm(L.completely_remove_plugin(plugin), _)
		$.ajax(
			url		: 'api/System/admin/plugins/' + plugin
			type	: 'delete'
			success	: !~>
				@reload()
				cs.ui.notify(L.changes_saved, 'success', 5)
		)
)
