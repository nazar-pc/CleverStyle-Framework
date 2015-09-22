/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L				= cs.Language
active_switch	= (uninstalled, disabled, enabled) ->
	switch @active
	| -1	=> uninstalled
	| 0		=> disabled
	| 1		=> enabled
Polymer(
	'is'		: 'cs-system-admin-components-modules-list'
	behaviors	: [
		cs.Polymer.behaviors.Language
		cs.Polymer.behaviors.admin.System.components
	]
	ready : !->
		@reload()
	reload : !->
		modules <~! $.getJSON('api/System/admin/modules', _)
		modules.forEach (module) !->
			module.can_disable		= module.active ~= 1 && module.name != 'System'
			active_switch_local		= active_switch.bind(module)
			module.class			= active_switch_local(
				'cs-block-error cs-text-error'
				'cs-block-warning cs-text-warning'
				'cs-block-success cs-text-success'
			)
			module.icon				= active_switch_local(
				'times'
				'minus'
				if module.is_default then 'home' else 'check'
			)
			module.icon_text		= active_switch_local(
				L.uninstalled
				L.disabled
				if module.is_default then L.default_module else L.enabled
			)
			module.name_localized	= L[module.name] || module.name.replace('_', ' ')
			do !->
				for prop in ['api', 'license', 'readme']
					if module[prop]?.type
						tag						= if module[prop].type == 'txt' then 'pre' else 'div'
						module[prop].content	= "<#tag>#{module[prop].content}</#tag>"
			if module.meta
				module.info	= let (@ = module.meta)
					L.module_info(
						@package,
						@version,
						@description,
						@author,
						@website || L.none,
						@license,
						if @db_support then @db_support.join(', ') else L.none,
						if @storage_support then @storage_support.join(', ') else L.none,
						if @provide then [].concat(@provide).join(', ') else L.none,
						if @require then [].concat(@require).join(', ') else L.none,
						if @conflict then [].concat(@conflict).join(', ') else L.none,
						if @optional then [].concat(@optional).join(', ') else L.none,
						if @multilingual && @multilingual.indexOf('interface') != -1 then L.yes else L.no,
						if @multilingual && @multilingual.indexOf('content') != -1 then L.yes else L.no,
						if @languages then @languages.join(', ') else L.none
					)
		@set('modules', modules)
	/**
	 * Provides next events:
	 *  admin/System/components/modules/default/before
	 *  {name : module_name}
	 *
	 *  admin/System/components/modules/default/after
	 *  {name : module_name}
	 */
	_set_as_default : (e) !->
		module = e.model.module.name
		cs.Event.fire(
			'admin/System/components/modules/default/before'
			name	: module
		).then !~>
			$.ajax(
				url		: 'api/System/admin/modules/default'
				type	: 'put'
				data	:
					module	: module
				success	: !~>
					@reload()
					cs.ui.notify(L.changes_saved, 'success', 5)
					cs.Event.fire(
						'admin/System/components/modules/default/after'
						name	: module
					)
			)
	/**
	 * Provides next events:
	 *  admin/System/components/modules/enable/before
	 *  {name : module_name}
	 *
	 *  admin/System/components/modules/enable/after
	 *  {name : module_name}
	 */
	_enable : (e) !->
		@_enable_component(e.model.module.name, 'module', e.model.module.meta)
	/**
	 * Provides next events:
	 *  admin/System/components/modules/disable/before
	 *  {name : module_name}
	 *
	 *  admin/System/components/modules/disable/after
	 *  {name : module_name}
	 */
	_disable : (e) !->
		@_disable_component(e.model.module.name, 'module')
	/**
	 * Provides next events:
	 *  admin/System/components/modules/uninstall/before
	 *  {name : module_name}
	 *
	 *  admin/System/components/modules/uninstall/after
	 *  {name : module_name}
	 */
	_uninstall : (e) !->
		module = e.model.module.name
		modal	= cs.ui.confirm(
			L.uninstallation_of_module(module)
			!~>
				cs.Event.fire(
					'admin/System/components/modules/uninstall/before'
					name	: module
				).then !~>
					$.ajax(
						url		: "api/System/admin/modules/#module"
						type	: 'disable'
						success	: !~>
							@reload()
							cs.ui.notify(L.changes_saved, 'success', 5)
							cs.Event.fire(
								'admin/System/components/modules/uninstall/after'
								name	: module
							)
					)
		)
		modal.ok.innerHTML		= L.uninstall
		modal.ok.primary		= false
		modal.cancel.primary	= true
	_remove_completely : (e) !->
		@_remove_completely_component(e.model.module.name, 'module')
	/**
	 * Provides next events:
	 *  admin/System/components/modules/update/before
	 *  {name : plugin_name}
	 *
	 *  admin/System/components/modules/update/after
	 *  {name : plugin_name}
	 */
	_upload : !->
		@_upload_package(@$.file).then (meta) !~>
			if meta.category != 'modules' || !meta.package || !meta.version
				cs.ui.notify(L.this_is_not_plugin_installer_file, 'error', 5)
				return
			# Lookign for already present module
			for module in @modules
				if module.name == meta.package
					@_update_component(module.meta, meta)
					return
			# If module is not present yet - lest just extract it
			@_extract(meta)
	_extract : (meta) !->
		$.ajax(
			url		: 'api/System/admin/modules'
			type	: 'extract'
			success	: !~>
				@reload()
				#TODO ask for installation right here
		)
)
