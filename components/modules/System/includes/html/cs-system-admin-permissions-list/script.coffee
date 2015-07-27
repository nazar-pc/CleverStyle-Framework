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
	tooltip_animation	:'{animation:true,delay:200}'
	L					: L
	permissions			: []
	created				: ->
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
			@permissions	= permissions_list
	domReady			: ->
		timeout	= null
		cs.observe_inserts_on(@shadowRoot, =>
			if timeout
				clearTimeout(timeout)
			timeout = setTimeout (=>
				timeout	= null
				$(@shadowRoot).cs().tooltips_inside()
			), 100
		)
	delete_permission	: (event, detail, sender) ->
		$sender		= $(sender)
		index		= $sender.closest('[data-permission-index]').data('permission-index')
		permission	= @permissions[index]
		UIkit.modal.confirm(
			"""
				<p>#{L.sure_delete_permission(permission.group + '/' + permission.label)}</p>
				<p class="uk-alert uk-alert-danger">#{L.changing_settings_warning}</p>
			"""
			=>
				$.ajax(
					url		: 'api/System/admin/permissions/' + permission.id
					type	: 'delete'
					success	: =>
						UIkit.notify(L.changes_saved.toString(), 'success')
						@permissions.splice(index, 1)
				)
		)
)