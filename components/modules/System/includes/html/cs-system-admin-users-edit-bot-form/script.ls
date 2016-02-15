/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L	= cs.Language
Polymer(
	'is'		: 'cs-system-admin-users-edit-bot-form'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_users_')
	]
	properties	:
		can_save	:
			type		: Boolean
			computed	: 'can_save_(user_data.*)'
		user_id		: -1
		user_data	:
			type	: Object
			value	: {}
	ready : !->
		$.getJSON('api/System/admin/users/' + @user_id, (data) !~>
			@set('user_data', data)
		)
	save : !->
		$.ajax(
			url		: 'api/System/admin/users/' + @user_id
			type	: 'patch'
			data	:
				user	: @user_data
			success	: !->
				cs.ui.notify(L.changes_saved, 'success', 5)
		)
	can_save_ : ->
		@user_data.username && (@user_data.login || @user_data.email)
)
