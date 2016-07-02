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
			cs.api('get api/System/admin/permissions/' + @permission_id).then ({group : @group, label : @label}) !~>
	save : !->
		method	= if @permission_id then 'put' else 'post'
		suffix	= if @permission_id then '/' + @permission_id else ''
		cs.api("#method api/System/admin/permissions #suffix", {@group, @label}).then !~>
			cs.ui.notify(@L.changes_saved, 'success', 5)
)
