/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-system-admin-permissions-for'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_permissions_')
	]
	properties	:
		for				:
			type	: String
			value	: ''
		group			: ''
		user			: ''
		all_permissions	: Array
		permissions		: Object
	ready : !->
		Promise.all([
			$.getJSON('api/System/admin/blocks')
			$.getJSON('api/System/admin/permissions')
			$.getJSON("api/System/admin/#{@for}s/#{@[@for]}/permissions")
		]).then ([blocks, all_permissions, permissions]) !~>
			block_index_to_title	= {}
			blocks.forEach (block) ->
				block_index_to_title[block.index] = block.title
			@all_permissions	=
				for group, labels of all_permissions
					group	: group
					labels	:
						for label, id of labels
							name		: label
							id			: id
							description	: if group == 'Block' then block_index_to_title[label] else ''
			@permissions		= permissions
	save : !->
		$.ajax(
			url		: "api/System/admin/#{@for}s/#{@[@for]}/permissions"
			data	: $(@$.form).serialize()
			type	: 'put'
			success	: !~>
				cs.ui.notify(@L.changes_saved, 'success', 5)
		)
	invert : (e) !->
		$(e.currentTarget).closest('div')
			.find(':radio:not(:checked)[value!=-1]')
				.parent()
					.click()
	allow_all : (e) !->
		$(e.currentTarget).closest('div')
			.find(':radio[value=1]')
				.parent()
					.click()
	deny_all : (e) !->
		$(e.currentTarget).closest('div')
			.find(':radio[value=0]')
				.parent()
					.click()
	permission_state : (id, expected) ->
		permission	= @permissions[id]
		permission ~= expected ||
		(
			expected ~= '-1' &&
			permission == undefined
		)
)
