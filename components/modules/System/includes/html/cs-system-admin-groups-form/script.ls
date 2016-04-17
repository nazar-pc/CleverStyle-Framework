/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-system-admin-groups-form'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_groups_')
	]
	properties	:
		group_id			: Number
		group_title			: ''
		group_description	: ''
	ready : !->
		if @group_id
			{title : @group_title, description : @group_description} <~! $.getJSON('api/System/admin/groups/' + @group_id, _)
	save : !->
		$.ajax(
			url		: 'api/System/admin/groups' + (if @group_id then '/' + @group_id else '')
			type	: if @group_id then 'put' else 'post'
			data	:
				title		: @group_title
				description	: @group_description
			success	: !~>
				cs.ui.notify(@L.changes_saved, 'success', 5)
		)
)
