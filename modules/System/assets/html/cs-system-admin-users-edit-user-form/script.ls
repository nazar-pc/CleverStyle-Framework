/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
Polymer(
	is			: 'cs-system-admin-users-edit-user-form'
	behaviors	: [
		cs.Polymer.behaviors.computed_bindings
		cs.Polymer.behaviors.Language('system_admin_users_')
	]
	properties	:
		user_id		: -1
		user_data	:
			type	: Object
			value	: {}
		languages	: Array
		timezones	: Array
		can_upload	: 'file_upload' of cs
	ready : !->
		cs.api([
			'get api/System/languages'
			'get api/System/timezones'
			'get api/System/admin/users/' + @user_id
		]).then ([languages, timezones, data]) !~>
			languages_list	= []
			languages_list.push(
				clanguage	: ''
				description	: @L.system_default
			)
			for language in languages
				languages_list.push(
					clanguage	: language
					description	: language
				)
			timezones_list	= []
			timezones_list.push(
				timezone	: ''
				description	: @L.system_default
			)
			for description, timezone of timezones
				timezones_list.push(
					timezone	: timezone
					description	: description
				)
			@languages	= languages_list
			@timezones	= timezones_list
			@user_data	= data
		cs.file_upload?(
			@$['upload-avatar']
			(files) !~>
				if files.length
					@set('user_data.avatar', files[0])
		)
	_show_password : (e) !->
		lock		= e.currentTarget.querySelector('cs-icon')
		password	= lock.parentElement.parentElement.previousElementSibling.firstElementChild
		if password.type == 'password'
			password.type	= 'text'
			lock.icon		= 'unlock'
		else
			password.type	= 'password'
			lock.icon		= 'lock'
	save : !->
		cs.api('patch api/System/admin/users/' + @user_id, {user : @user_data}).then !~>
			cs.ui.notify(@L.changes_saved, 'success', 5)
)
