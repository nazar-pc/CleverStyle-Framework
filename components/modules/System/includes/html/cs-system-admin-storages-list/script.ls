/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L = cs.Language('system_admin_storages_')
Polymer(
	'is'		: 'cs-system-admin-storages-list'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_storages_')
	]
	ready : !->
		@reload()
	reload : !->
		storages <~! $.getJSON('api/System/admin/storages', _)
		@set('storages', storages)
	_add : !->
		$(cs.ui.simple_modal("""
			<h3>#{L.adding_of_storage}</h3>
			<cs-system-admin-storages-form add/>
		""")).on('close', !~>
			@reload()
		)
	_edit : (e) !->
		# Hack: ugly, but the only way to do it while https://github.com/Polymer/polymer/issues/1865 not resolved
		storage_model	= @$.storages_list.modelForElement(e.target)
		storage			= e.model.storage || storage_model.storage
		name			= storage.host + '/' + storage.connection
		$(cs.ui.simple_modal("""
			<h3>#{L.editing_of_storage(name)}</h3>
			<cs-system-admin-storages-form storage-index="#{storage.index}"/>
		""")).on('close', !~>
			@reload()
		)
	_delete : (e) !->
		# Hack: ugly, but the only way to do it while https://github.com/Polymer/polymer/issues/1865 not resolved
		storage_model	= @$.storages_list.modelForElement(e.target)
		storage			= e.model.storage || storage_model.storage
		name			= storage.host + '/' + storage.connection
		cs.ui.confirm(
			L.sure_to_delete(name)
			!~>
				$.ajax(
					url		: 'api/System/admin/storages/' + storage.index
					type	: 'delete'
					success	: !~>
						cs.ui.notify(L.changes_saved, 'success', 5)
						@reload()
				)
		)
)
