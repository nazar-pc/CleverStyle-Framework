/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
active_switch	= (active, if_uninstalled, if_disabled, if_enabled) ->
	switch active
	| -1	=> if_uninstalled
	| 0		=> if_disabled
	| 1		=> if_enabled
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
		Promise.all([
			cs.api([
				'get			api/System/admin/modules'
				'get			api/System/admin/modules/default'
				'get_settings	api/System/admin/system'
			])
			cs.Language.ready()
		]).then ([[modules, default_module, settings]]) !~>
			texts			=
				uninstalled		: @L.uninstalled
				disabled		: @L.disabled
				default_module	: @L.default_module
				enabled			: @L.enabled
			for module in modules
				is_default				= module.name == default_module
				module.class			= active_switch(
					module.active
					'cs-block-error cs-text-error'
					'cs-block-warning cs-text-warning'
					'cs-block-success cs-text-success'
				)
				module.icon				= active_switch(
					module.active
					'times'
					'minus'
					if is_default then 'home' else 'check'
				)
				module.icon_text		= active_switch(
					module.active
					texts.uninstalled
					texts.disabled
					if is_default then texts.default_module else texts.enabled
				)
				module.name_localized			= @L[module.name] || module.name.replace(/_/g, ' ')
				enabled							= module.active ~= 1
				installed						= module.active !~= -1
				module.can_disable				= enabled && module.name != 'System'
				module.administration			= module.has_admin_section && installed
				module.db_settings				= !settings.simple_admin_mode && installed && module.meta?.db
				module.storage_settings			= !settings.simple_admin_mode && installed && module.meta?.storage
				module.can_be_set_as_default	= enabled && !is_default && module.has_user_section
				for prop in ['api', 'license', 'readme']
					if module[prop]?.type
						tag						= if module[prop].type == 'txt' then 'pre' else 'div'
						module[prop].content	= "<#tag>#{module[prop].content}</#tag>"
				if module.meta
					module.info	= @_get_module_info(module.meta)
			@default_module	= default_module
			@set('modules', modules)
	_get_module_info : (meta) ->
		none	= @L.none
		_yes	= @L.yes
		_no		= @L.no
		@L.module_info(
			meta.package
			meta.version
			meta.description
			meta.author
			meta.website || none
			meta.license
			if meta.db_support then meta.db_support.join(', ') else none
			if meta.storage_support then meta.storage_support.join(', ') else none
			if meta.provide then [].concat(meta.provide).join(', ') else none
			if meta.require then [].concat(meta.require).join(', ') else none
			if meta.conflict then [].concat(meta.conflict).join(', ') else none
			if meta.optional then [].concat(meta.optional).join(', ') else none
			if meta.multilingual && meta.multilingual.indexOf('interface') != -1 then _yes else _no
			if meta.multilingual && meta.multilingual.indexOf('content') != -1 then _yes else _no
			if meta.languages then meta.languages.join(', ') else none
		)
	/**
	 * Provides next events:
	 *  admin/System/modules/default/before
	 *  {name : module_name}
	 *
	 *  admin/System/modules/default/after
	 *  {name : module_name}
	 */
	_set_as_default : (e) !->
		module = e.model.module.name
		cs.Event.fire(
			'admin/System/modules/default/before'
			name	: module
		)
			.then -> cs.api('put api/System/admin/modules/default', {module})
			.then !~>
				@reload()
				cs.ui.notify(@L.changes_saved, 'success', 5)
				cs.Event.fire(
					'admin/System/modules/default/after'
					name	: module
				)
	/**
	 * Provides next events:
	 *  admin/System/modules/enable/before
	 *  {name : module_name}
	 *
	 *  admin/System/modules/enable/after
	 *  {name : module_name}
	 */
	_enable : (e) !->
		@_enable_module(e.model.module.name, e.model.module.meta)
	/**
	 * Provides next events:
	 *  admin/System/modules/disable/before
	 *  {name : module_name}
	 *
	 *  admin/System/modules/disable/after
	 *  {name : module_name}
	 */
	_disable : (e) !->
		@_disable_module(e.model.module.name)
	/**
	 * Provides next events:
	 *  admin/System/modules/install/before
	 *  {name : module_name}
	 *
	 *  admin/System/modules/install/after
	 *  {name : module_name}
	 */
	_install : (e) !->
		module	= e.model.module.name
		meta	= e.model.module.meta
		Promise.all([
			cs.api([
				"get			api/System/admin/modules/#module/dependencies"
				'get			api/System/admin/databases'
				'get			api/System/admin/storages'
				'get_settings	api/System/admin/system'
			])
			cs.Language('system_admin_').ready()
		]).then ([[dependencies, databases, storages, settings], L]) !~>
			message			= ''
			message_more	= ''
			if Object.keys(dependencies).length
				message	= @_compose_dependencies_message(L, module, 'modules', dependencies)
				if settings.simple_admin_mode
					cs.ui.notify(message, 'error', 5)
					return
			if meta && meta.optional
				message_more	+= '<p class="cs-text-success cs-block-success">' + @L.system_admin_for_complete_feature_set(meta.optional.join(', ')) + '</p>'
			form	= if meta then @_databases_storages_form(meta, databases, storages, settings) else ''
			modal	= cs.ui.confirm(
				"""<h3>#{@L.installation_of_module(module)}</h3>
				#message
				#message_more
				#form"""
				!~>
					cs.Event.fire(
						'admin/System/modules/install/before'
						name	: module
					)
						.then -> cs.api("install api/System/admin/modules/#module", modal.querySelector('form'))
						.then ~>
							cs.ui.notify(@L.changes_saved, 'success', 5)
							cs.Event.fire(
								'admin/System/modules/install/after'
								name	: module
							)
						.then(location~reload)
			)
			modal.ok.innerHTML		= @L[if !message then 'install' else 'force_install_not_recommended']
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
					<th tooltip="#{@L.appointment_of_db_info}">
						#{@L.appointment_of_db}
						<cs-tooltip/>
					</th>
					<th tooltip="#{@L.system_db_info}">
						#{@L.system_db}
						<cs-tooltip/>
					</th>
				</tr>"""
				db_options	= ''
				for db in databases
					if !meta.db_support || meta.db_support.indexOf(db.driver) != -1
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
					<th tooltip="#{@L.appointment_of_storage_info}">
						#{@L.appointment_of_storage}
						<cs-tooltip/>
					</th>
					<th tooltip="#{@L.system_storage_info}">
						#{@L.system_storage}
						<cs-tooltip/>
					</th>
				</tr>"""
				storage_options	= ''
				for storage in storages
					if !meta.storage_support || meta.storage_support.indexOf(storage.driver) != -1
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
				@L.core_db + " (#{db.type})"
		checked	= if db.index then '' else 'checked'
		"""<option value="#{db.index}" #checked>#name</option>"""
	_storage_option : (storage) ->
		name	=
			if storage.index
				"#{storage.host} (#{storage.driver})"
			else
				@L.core_storage + " (#{storage.driver})"
		checked	= if storage.index then '' else 'checked'
		"""<option value="#{storage.index}" #checked>#name</option>"""
	/**
	 * Provides next events:
	 *  admin/System/modules/uninstall/before
	 *  {name : module_name}
	 *
	 *  admin/System/modules/uninstall/after
	 *  {name : module_name}
	 */
	_uninstall : (e) !->
		module	= e.model.module.name
		modal	= cs.ui.confirm(
			@L.uninstallation_of_module(module)
			!~>
				cs.Event.fire(
					'admin/System/modules/uninstall/before'
					name	: module
				)
					.then -> cs.api("uninstall api/System/admin/modules/#module")
					.then ~>
						cs.ui.notify(@L.changes_saved, 'success', 5)
						cs.Event.fire(
							'admin/System/modules/uninstall/after'
							name	: module
						)
					.then(location~reload)
		)
		modal.ok.innerHTML		= @L.uninstall
		modal.ok.primary		= false
		modal.cancel.primary	= true
	_remove_completely : (e) !->
		@_remove_completely_component(e.model.module.name, 'modules')
	/**
	 * Provides next events:
	 *  admin/System/modules/update/before
	 *  {name : module_name}
	 *
	 *  admin/System/modules/update/after
	 *  {name : module_name}
	 */
	_upload : !->
		@_upload_package(@$.file).then (meta) !~>
			if meta.category != 'modules' || !meta.package || !meta.version
				cs.ui.notify(@L.this_is_not_module_installer_file, 'error', 5)
				return
			# Looking for already present module
			for module in @modules
				if module.name == meta.package
					if meta.version == module.meta.version
						cs.ui.notify(@L.update_module_impossible_same_version(meta.package, meta.version), 'warning', 5)
						return
					@_update_component(module.meta, meta)
					return
			# If module is not present yet - lets just extract it
			cs.api('extract api/System/admin/modules').then !~>
				cs.ui.notify(@L.changes_saved, 'success', 5)
				location.reload()
	/**
	 * Provides next events:
	 *  admin/System/modules/update_system/before
	 *
	 *  admin/System/modules/update_system/after
	 */
	_upload_system : !->
		# Get System's module information
		for module in @modules
			if module.name == 'System'
				break
		@_upload_package(@$.file_system).then (meta) !~>
			if meta.category != 'modules' || meta.package != 'System' || !meta.version
				cs.ui.notify(@L.this_is_not_system_installer_file, 'error', 5)
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
				"""<h3>#{@L.db_settings_for_module(module)}</h3>
				<p class="cs-block-error cs-text-error">#{@L.changing_settings_warning}</p>
				#form"""
				!~>
					cs.api("put api/System/admin/modules/#module/db", modal.querySelector('form')).then !->
						cs.ui.notify(@L.changes_saved, 'success', 5)
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
				"""<h3>#{@L.storage_settings_for_module(module)}</h3>
				<p class="cs-block-error cs-text-error">#{@L.changing_settings_warning}</p>
				#form"""
				!~>
					cs.api("put api/System/admin/modules/#module/storage", modal.querySelector('form')).then !->
						cs.ui.notify(@L.changes_saved, 'success', 5)
			)
			for storage_name, index of storages_mapping
				modal.querySelector("[name='storage[#storage_name]']").selected = index
	_update_modules_list : !->
		cs.api('update_list api/System/admin/modules').then !~>
			cs.ui.notify(@L.changes_saved, 'success', 5)
			@reload()
)
