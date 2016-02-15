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
	'is'		: 'cs-system-admin-users-add-bot-form'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_users_')
	]
	properties	:
		can_save			:
			type		: Boolean
			computed	: 'can_save_(name, user_agent, ip)'
		name				: ''
		user_agent			: ''
		ip					: ''
	save : !->
		$.ajax(
			url		: 'api/System/admin/users'
			type	: 'post'
			data	:
				name		: @name
				user_agent	: @user_agent
				ip			: @ip
				type		: 'bot'
			success	: !->
				cs.ui.notify(L.changes_saved, 'success', 5)
		)
	can_save_ : (name, user_agent, ip) ->
		name && (user_agent || ip)
)
