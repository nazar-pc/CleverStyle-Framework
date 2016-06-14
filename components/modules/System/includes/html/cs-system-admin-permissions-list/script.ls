/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L	= cs.Language('system_admin_permissions_')
Polymer(
	'is'		: 'cs-system-admin-permissions-list'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_permissions_')
	]
	properties	:
		permissions			: []
		permissions_loaded	: false
	ready : !->
		@reload()
	reload : !->
		cs.api([
			'get api/System/admin/blocks'
			'get api/System/admin/permissions'
		]).then ([blocks, permissions]) !~>
			block_index_to_title	= {}
			blocks.forEach (block) ->
				block_index_to_title[block.index] = block.title
			permissions_list	= []
			for group, labels of permissions
				for label, id of labels
					permissions_list.push(
						id			: id
						group		: group
						label		: label
						description	: if group == 'Block' then block_index_to_title[label] else ''
					)
			@set('permissions', permissions_list)
			@permissions_loaded	= true
	add_permission : !->
		cs.ui.simple_modal("""
			<h3>#{L.adding_permission}</h3>
			<p class="cs-block-error cs-text-error">#{L.changing_settings_warning}</p>
			<cs-system-admin-permissions-form/>
		""").addEventListener('close', @~reload)
	edit_permission : (e) !->
		permission	= e.model.permission
		cs.ui.simple_modal("""
			<h3>#{L.editing_permission(permission.group + '/' + permission.label)}</h3>
			<p class="cs-block-error cs-text-error">#{L.changing_settings_warning}</p>
			<cs-system-admin-permissions-form permission_id="#{permission.id}"/>
		""").addEventListener('close', @~reload)
	delete_permission : (e) !->
		permission	= e.model.permission
		cs.ui.confirm(
			"""
				<h3>#{L.sure_delete_permission(permission.group + '/' + permission.label)}</h3>
				<p class="cs-block-error cs-text-error">#{L.changing_settings_warning}</p>
			"""
		)
			.then -> cs.api('delete api/System/admin/permissions/' + permission.id)
			.then !~>
				cs.ui.notify(L.changes_saved, 'success', 5)
				@splice('permissions', e.model.index, 1)
)
