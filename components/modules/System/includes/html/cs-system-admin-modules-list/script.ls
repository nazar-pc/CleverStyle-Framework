/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L				= cs.Language('system_admin_modules_')
active_switch	= (uninstalled, disabled, enabled) ->
	switch @active
	| -1	=> uninstalled
	| 0		=> disabled
	| 1		=> enabled
Polymer(
	'is'		: 'cs-system-admin-modules-list'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_modules_')
		cs.Polymer.behaviors.admin.System.components
		cs.Polymer.behaviors.admin.System.upload
	]
	properties	:
		default_module	: String
	ready : !->
		@reload()
	reload : !->
		cs.api([
			'get			api/System/admin/modules'
			'get			api/System/admin/modules/default'
			'get_settings	api/System/admin/system'
		]).then ([modules, default_module, settings]) !~>
			@default_module	= default_module
			modules.forEach (module) !->
				active_switch_local		= active_switch.bind(module)
				module.class			= active_switch_local(
					'cs-block-error cs-text-error'
					'cs-block-warning cs-text-warning'
					'cs-block-success cs-text-success'
				)
				module.icon				= active_switch_local(
					'times'
					'minus'
					if module.name == default_module then 'home' else 'check'
				)
				module.icon_text		= active_switch_local(
					L.uninstalled
					L.disabled
					if module.name == default_module then L.default_module else L.enabled
				)
				module.name_localized			= L[module.name] || module.name.replace(/_/g, ' ')
				enabled							= module.active ~= 1
				installed						= module.active !~= -1
				module.can_disable				= enabled && module.name != 'System'
				module.administration			= module.has_admin_section && installed
				module.db_settings				= !settings.simple_admin_mode && installed && module.meta && module.meta.db
				module.storage_settings			= !settings.simple_admin_mode && installed && module.meta && module.meta.storage
				module.can_be_set_as_default	= enabled && module.name != default_module && module.has_user_section
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
		)
			.then -> cs.api('put api/System/admin/modules/default', {module})
			.then !~>
				@reload()
				cs.ui.notify(L.changes_saved, 'success', 5)
				cs.Event.fire(
					'admin/System/components/modules/default/after'
					name	: module
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
	 *  admin/System/components/modules/install/before
	 *  {name : module_name}
	 *
	 *  admin/System/components/modules/install/after
	 *  {name : module_name}
	 */
	_install : (e) !->
		module	= e.model.module.name
		meta	= e.model.module.meta
		cs.api([
			"get			api/System/admin/modules/#module/dependencies"
			'get			api/System/admin/databases'
			'get			api/System/admin/storages'
			'get_settings	api/System/admin/system'
		]).then ([dependencies, databases, storages, settings]) !~>
			message			= ''
			message_more	= ''
			if Object.keys(dependencies).length
				message	= @_compose_dependencies_message(module, dependencies)
				if settings.simple_admin_mode
					cs.ui.notify(message, 'error', 5)
					return
			if meta && meta.optional
				message_more	+= '<p class="cs-text-success cs-block-success">' + L.system_admin_for_complete_feature_set(meta.optional.join(', ')) + '</p>'
			form	= if meta then @_databases_storages_form(meta, databases, storages, settings) else ''
			modal	= cs.ui.confirm(
				"""<h3>#{L.installation_of_module(module)}</h3>
				#message
				#message_more
				#form"""
				!~>
					cs.Event.fire(
						'admin/System/components/modules/install/before'
						name	: module
					)
						.then -> cs.api("install api/System/admin/modules/#module", modal.querySelector('form'))
						.then ->
							cs.ui.notify(L.changes_saved, 'success', 5)
							cs.Event.fire(
								'admin/System/components/modules/install/after'
								name	: module
							)
						.then !->
							location.reload()
			)
			modal.ok.innerHTML		= L[if !message then 'install' else 'force_install_not_recommended']
			modal.ok.primary		= !message
			modal.cancel.primary	= !modal.ok.primary
	_databases_storages_form : (meta, databases, storages, settings) ->
		content	= ''
		if meta.db && databases.length
			if settings.simple_admin_mode
				for db_name in meta.db
					content	+= """<input type="hidden" name="db[#db_name]" value="0">"""
			else
				content	+= """<tr>
					<th tooltip="#{L.appointment_of_db_info}">
						#{L.appointment_of_db}
						<cs-tooltip/>
					</th>
					<th tooltip="#{L.system_db_info}">
						#{L.system_db}
						<cs-tooltip/>
					</th>
				</tr>"""
				db_options	= ''
				for db in databases
					if !meta.db_support || meta.db_support.indexOf(db.type) != -1
						db_options	+= @_db_option(db)
				for db_name in meta.db
					content	+= """<tr>
						<td>#db_name</td>
						<td>
							<select is="cs-select" name="db[#db_name]">#db_options</select>
						</td>
					</tr>"""
		if meta.storage && storages.length
			if settings.simple_admin_mode
				for storage_name in meta.storage
					content	+= """<input type="hidden" name="storage[#storage_name]" value="0">"""
			else
				content	+= """<tr>
					<th tooltip="#{L.appointment_of_storage_info}">
						#{L.appointment_of_storage}
						<cs-tooltip/>
					</th>
					<th tooltip="#{L.system_storage_info}">
						#{L.system_storage}
						<cs-tooltip/>
					</th>
				</tr>"""
				storage_options	= ''
				for storage in storages
					if !meta.storage_support || meta.storage_support.indexOf(storage.type) != -1
						storage_options	+= @_storage_option(storage)
				for storage_name in meta.storage
					content	+= """<tr>
						<td>#storage_name</td>
						<td>
							<select is="cs-select" name="storage[#storage_name]">#storage_options</select>
						</td>
					</tr>"""
		if settings.simple_admin_mode
			"<form>#content</form>"
		else
			"""<form>
				<table class="cs-table">
					#content
				</table>
			</form>"""
	_db_option : (db) ->
		name	=
			if db.index
				"#{db.host}/#{db.name} (#{db.type})"
			else
				L.core_db + " (#{db.type})"
		checked	= if db.index then '' else 'checked'
		"""<option value="#{db.index}" #checked>#name</option>"""
	_storage_option : (storage) ->
		name	=
			if storage.index
				"#{storage.host} (#{storage.connection})"
			else
				L.core_storage + " (#{storage.connection})"
		checked	= if storage.index then '' else 'checked'
		"""<option value="#{storage.index}" #checked>#name</option>"""
	/**
	 * Provides next events:
	 *  admin/System/components/modules/uninstall/before
	 *  {name : module_name}
	 *
	 *  admin/System/components/modules/uninstall/after
	 *  {name : module_name}
	 */
	_uninstall : (e) !->
		module	= e.model.module.name
		modal	= cs.ui.confirm(
			L.uninstallation_of_module(module)
			!~>
				cs.Event.fire(
					'admin/System/components/modules/uninstall/before'
					name	: module
				)
					.then -> cs.api("uninstall api/System/admin/modules/#module")
					.then !~>
						@reload()
						cs.ui.notify(L.changes_saved, 'success', 5)
						cs.Event.fire(
							'admin/System/components/modules/uninstall/after'
							name	: module
						)
		)
		modal.ok.innerHTML		= L.uninstall
		modal.ok.primary		= false
		modal.cancel.primary	= true
	_remove_completely : (e) !->
		@_remove_completely_component(e.model.module.name, 'modules')
	/**
	 * Provides next events:
	 *  admin/System/components/modules/update/before
	 *  {name : module_name}
	 *
	 *  admin/System/components/modules/update/after
	 *  {name : module_name}
	 */
	_upload : !->
		@_upload_package(@$.file).then (meta) !~>
			if meta.category != 'modules' || !meta.package || !meta.version
				cs.ui.notify(L.this_is_not_module_installer_file, 'error', 5)
				return
			# Looking for already present module
			for module in @modules
				if module.name == meta.package
					if meta.version == module.meta.version
						cs.ui.notify(L.update_module_impossible_same_version(meta.package, meta.version), 'warning', 5)
						return
					@_update_component(module.meta, meta)
					return
			# If module is not present yet - lets just extract it
			cs.api('extract api/System/admin/modules').then !->
				cs.ui.notify(L.changes_saved, 'success', 5)
				location.reload()
	/**
	 * Provides next events:
	 *  admin/System/components/modules/update_system/before
	 *
	 *  admin/System/components/modules/update_system/after
	 */
	_upload_system : !->
		# Get System's module information
		for module in @modules
			if module.name == 'System'
				break
		@_upload_package(@$.file_system).then (meta) !~>
			if meta.category != 'modules' || meta.package != 'System' || !meta.version
				cs.ui.notify(L.this_is_not_system_installer_file, 'error', 5)
				return
			@_update_component(module.meta, meta)
	_db_settings : (e) !->
		module	= e.model.module.name
		meta	= e.model.module.meta
		cs.api([
			'get			api/System/admin/databases'
			"get			api/System/admin/modules/#module/db"
			'get_settings	api/System/admin/system'
		]).then ([databases, databases_mapping, settings]) !~>
			form	= if meta then @_databases_storages_form(meta, databases, [], settings) else ''
			modal	= cs.ui.confirm(
				"""<h3>#{L.db_settings_for_module(module)}</h3>
				<p class="cs-block-error cs-text-error">#{L.changing_settings_warning}</p>
				#form"""
				!~>
					cs.api("put api/System/admin/modules/#module/db", modal.querySelector('form')).then !->
						cs.ui.notify(L.changes_saved, 'success', 5)
			)
			for db_name, index of databases_mapping
				modal.querySelector("[name='db[#db_name]']").selected = index
	_storage_settings : (e) !->
		module	= e.model.module.name
		meta	= e.model.module.meta
		cs.api([
			'get			api/System/admin/storages'
			"get			api/System/admin/modules/#module/storage"
			'get_settings	api/System/admin/system'
		]).then ([storages, storages_mapping, settings]) !~>
			form	= if meta then @_databases_storages_form(meta, [], storages, settings) else ''
			modal	= cs.ui.confirm(
				"""<h3>#{L.storage_settings_for_module(module)}</h3>
				<p class="cs-block-error cs-text-error">#{L.changing_settings_warning}</p>
				#form"""
				!~>
					cs.api("put api/System/admin/modules/#module/storage", modal.querySelector('form')).then !->
						cs.ui.notify(L.changes_saved, 'success', 5)
			)
			for storage_name, index of storages_mapping
				modal.querySelector("[name='storage[#storage_name]']").selected = index
	_update_modules_list : !->
		cs.api('update_list api/System/admin/modules').then !~>
			cs.ui.notify(L.changes_saved, 'success', 5)
			@reload()
)
