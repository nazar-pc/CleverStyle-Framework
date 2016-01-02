/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L				= cs.Language
active_switch	= (uninstalled, disabled, enabled) ->
	switch @active
	| -1	=> uninstalled
	| 0		=> disabled
	| 1		=> enabled
Polymer(
	'is'		: 'cs-system-admin-modules-list'
	behaviors	: [
		cs.Polymer.behaviors.Language
		cs.Polymer.behaviors.admin.System.components
		cs.Polymer.behaviors.admin.System.upload
	]
	properties	:
		default_module	: String
	ready : !->
		@reload()
	reload : !->
		Promise.all([
			$.getJSON('api/System/admin/modules')
			$.getJSON('api/System/admin/modules/default')
		]).then ([modules, default_module]) !~>
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
				module.db_settings				= !cs.simple_admin_mode && installed && module.meta && module.meta.db
				module.storage_settings			= !cs.simple_admin_mode && installed && module.meta && module.meta.storage
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
	 *  admin/System/components/modules/install/before
	 *  {name : module_name}
	 *
	 *  admin/System/components/modules/install/after
	 *  {name : module_name}
	 */
	_install : (e) !->
		module	= e.model.module.name
		meta	= e.model.module.meta
		Promise.all([
			$.getJSON("api/System/admin/modules/#module/dependencies")
			$.getJSON('api/System/admin/databases')
			$.getJSON('api/System/admin/storages')
		]).then ([dependencies, databases, storages]) !~>
			message			= ''
			message_more	= ''
			if Object.keys(dependencies).length
				message	= @_compose_dependencies_message(module, dependencies)
				if cs.simple_admin_mode
					cs.ui.notify(message, 'error', 5)
					return
			if meta && meta.optional
				message_more	+= '<p class="cs-text-success cs-block-success">' + L.for_complete_feature_set(meta.optional.join(', ')) + '</p>'
			form	= if meta then @_databases_storages_form(meta, databases, storages) else ''
			modal	= cs.ui.confirm(
				"""<h3>#{L.installation_of_module(module)}</h3>
				#message
				#message_more
				#form"""
				!~>
					cs.Event.fire(
						'admin/System/components/modules/install/before'
						name	: module
					).then !~>
						$.ajax(
							url			: "api/System/admin/modules/#module"
							data		: $(modal.querySelector('form')).serialize()
							type		: 'install'
							success		: !~>
								cs.ui.notify(L.changes_saved, 'success', 5)
								cs.Event.fire(
									'admin/System/components/modules/install/after'
									name	: module
								).then !->
									location.reload()
						)
			)
			modal.ok.innerHTML		= L[if !message then 'install' else 'force_install_not_recommended']
			modal.ok.primary		= !message
			modal.cancel.primary	= !modal.ok.primary
	_databases_storages_form : (meta, databases, storages) ->
		content	= ''
		if meta.db && databases.length
			if cs.simple_admin_mode
				for db_name in meta.db
					content	+= """<input type="hidden" name="db[#db_name]" value="0">"""
			else
				content	+= """<tr>
					<th tooltip="#{cs.prepare_attr_value(L.appointment_of_db_info)}">
						#{L.appointment_of_db}
						<cs-tooltip/>
					</th>
					<th tooltip="#{cs.prepare_attr_value(L.system_db_info)}">
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
			if cs.simple_admin_mode
				for storage_name in meta.storage
					content	+= """<input type="hidden" name="storage[#storage_name]" value="0">"""
			else
				content	+= """<tr>
					<th tooltip="#{cs.prepare_attr_value(L.appointment_of_storage_info)}">
						#{L.appointment_of_storage}
						<cs-tooltip/>
					</th>
					<th tooltip="#{cs.prepare_attr_value(L.system_storage_info)}">
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
		if cs.simple_admin_mode
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
				).then !~>
					$.ajax(
						url		: "api/System/admin/modules/#module"
						type	: 'uninstall'
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
					@_update_component(module.meta, meta)
					return
			# If module is not present yet - lest just extract it
			$.ajax(
				url		: 'api/System/admin/modules'
				type	: 'extract'
				success	: !~>
					cs.ui.notify(L.changes_saved, 'success', 5)
					location.reload()
			)
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
		Promise.all([
			$.getJSON('api/System/admin/databases')
			$.getJSON("api/System/admin/modules/#module/db")
		]).then ([databases, databases_mapping]) !~>
			form	= if meta then @_databases_storages_form(meta, databases, []) else ''
			modal	= cs.ui.confirm(
				"""<h3>#{L.db_settings_for_module(module)}</h3>
				<p class="cs-block-error cs-text-error">#{L.changing_settings_warning}</p>
				#form"""
				!~>
					$.ajax(
						url			: "api/System/admin/modules/#module/db"
						data		: $(modal.querySelector('form')).serialize()
						type		: 'put'
						success		: !->
							cs.ui.notify(L.changes_saved, 'success', 5)
					)
			)
			for index, db_name of databases_mapping
				modal.querySelector("[name=db[#db_name]]").selected = index
	_storage_settings : (e) !->
		module	= e.model.module.name
		meta	= e.model.module.meta
		Promise.all([
			$.getJSON('api/System/admin/storages')
			$.getJSON("api/System/admin/modules/#module/storage")
		]).then ([storages, storages_mapping]) !~>
			form	= if meta then @_databases_storages_form(meta, [], storages) else ''
			modal	= cs.ui.confirm(
				"""<h3>#{L.storage_settings_for_module(module)}</h3>
				<p class="cs-block-error cs-text-error">#{L.changing_settings_warning}</p>
				#form"""
				!~>
					$.ajax(
						url			: "api/System/admin/modules/#module/storage"
						data		: $(modal.querySelector('form')).serialize()
						type		: 'put'
						success		: !->
							cs.ui.notify(L.changes_saved, 'success', 5)
					)
			)
			for index, storage_name of storages_mapping
				modal.querySelector("[name=storage[#storage_name]]").selected = index
	_update_modules_list : !->
		$.ajax(
			url		: 'api/System/admin/modules'
			type	: 'update_list'
			success	: !~>
				cs.ui.notify(L.changes_saved, 'success', 5)
				@reload()
			)
)
