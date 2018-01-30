/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
Polymer(
	is			: 'cs-system-admin-groups-form'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_groups_')
	]
	properties	:
		group_id			: Number
		group_title			: ''
		group_description	: ''
	ready : !->
		if @group_id
			cs.api('get api/System/admin/groups/' + @group_id).then ({title : @group_title, description : @group_description}) !~>
	save : !->
		method	= if @group_id then 'put' else 'post'
		suffix	= if @group_id then '/' + @group_id else ''
		cs.api(
			"#method api/System/admin/groups#suffix"
			title		: @group_title
			description	: @group_description
		).then !~>
			cs.ui.notify(@L.changes_saved, 'success', 5)
)
