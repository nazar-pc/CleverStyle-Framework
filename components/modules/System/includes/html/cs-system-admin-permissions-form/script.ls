/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-system-admin-permissions-form'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_permissions_')
	]
	properties	:
		permission_id	: Number
		group			: ''
		label			: ''
	ready : !->
		if @permission_id
			{group : @group, label : @label} <~! $.getJSON('api/System/admin/permissions/' + @permission_id, _)
	save : !->
		$.ajax(
			url		: 'api/System/admin/permissions' + (if @permission_id then '/' + @permission_id else '')
			type	: if @permission_id then 'put' else 'post'
			data	:
				group	: @group
				label	: @label
			success	: !~>
				cs.ui.notify(@L.changes_saved, 'success', 5)
		)
)
