###*
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
###
L	= cs.Language
Polymer(
	'is'				: 'cs-system-admin-permissions-list'
	behaviors			: [cs.Polymer.behaviors.Language]
	properties			:
		permissions	: []
	ready				: ->
		@reload()
	reload				: ->
		$.when(
			$.getJSON('api/System/admin/blocks')
			$.getJSON('api/System/admin/permissions')
		).done (blocks, permissions) =>
			index_to_title	= {}
			blocks[0].forEach (block) ->
				index_to_title[block.index] = block.title
			permissions_list	= []
			for group, labels of permissions[0]
				for label, id of labels
					permissions_list.push(
						id			: id
						group		: group
						label		: label
						description	: if group == 'Block' then index_to_title[label] else ''
					)
			@set('permissions', permissions_list)
	add_permission		: ->
		$(cs.ui.simple_modal("""
			<h3>#{L.adding_permission}</h3>
			<p class="uk-alert uk-alert-danger">#{L.changing_settings_warning}</p>
			<cs-system-admin-permissions-form/>
		""")).on('close', =>
			@reload()
		)
	edit_permission		: (e) ->
		permission	= e.model.permission
		$(cs.ui.simple_modal("""
			<h3>#{L.editing_permission(permission.group + '/' + permission.label)}</h3>
			<p class="uk-alert uk-alert-danger">#{L.changing_settings_warning}</p>
			<cs-system-admin-permissions-form permission_id="#{permission.id}" label="#{cs.prepare_attr_value(permission.label)}" group="#{cs.prepare_attr_value(permission.group)}"/>
		""")).on('close', =>
			@reload()
		)
	delete_permission	: (e) ->
		permission	= e.model.permission
		UIkit.modal.confirm(
			"""
				<h3>#{L.sure_delete_permission(permission.group + '/' + permission.label)}</h3>
				<p class="uk-alert uk-alert-danger">#{L.changing_settings_warning}</p>
			"""
			=>
				$.ajax(
					url		: 'api/System/admin/permissions/' + permission.id
					type	: 'delete'
					success	: =>
						cs.ui.notify(L.changes_saved.toString(), 'success', 5000)
						@splice('permissions', e.model.index, 1)
				)
		)
)
