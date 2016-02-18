/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L	= cs.Language('system_admin_users_')
Polymer(
	'is'		: 'cs-system-admin-users-edit-user-form'
	behaviors	: [
		cs.Polymer.behaviors.cs
		cs.Polymer.behaviors.Language('system_admin_users_')
	]
	properties	:
		user_id		: -1
		user_data	:
			type	: Object
			value	: {}
		languages	: Array
		timezones	: Array
		block_until	:
			observer	: '_block_until'
			type		: String
	ready : !->
		Promise.all([
			$.getJSON('api/System/languages')
			$.getJSON('api/System/timezones')
			$.getJSON('api/System/admin/users/' + @user_id)
		]).then ([languages, timezones, data]) !~>
			languages_list	= []
			languages_list.push(
				clanguage	: ''
				description	: L.system_default
			)
			for language in languages
				languages_list.push(
					clanguage	: language
					description	: language
				)
			timezones_list	= []
			timezones_list.push(
				timezone	: ''
				description	: L.system_default
			)
			for description, timezone of timezones
				timezones_list.push(
					timezone	: timezone
					description	: description
				)
			@languages	= languages_list
			@timezones	= timezones_list
			block_until	= do ->
				block_until	:= data.block_until
				date		= new Date
				if parseInt(block_until)
					date.setTime(parseInt(block_until) * 1000)
				z	= (number) ->
					('0' + number).substr(-2)
				date.getFullYear() + '-' + z(date.getMonth() + 1) + '-' + z(date.getDate()) + 'T' + z(date.getHours()) + ':' + z(date.getMinutes())
			@block_until	= block_until
			@user_data		= data
		cs.file_upload?(
			@$['upload-avatar']
			(files) !~>
				if files.length
					@set('user_data.avatar', files[0])
		)
	_show_password : (e) !->
		lock		= e.currentTarget
		password	= lock.previousElementSibling
		if password.type == 'password'
			password.type	= 'text'
			lock.icon		= 'unlock'
		else
			password.type	= 'password'
			lock.icon		= 'lock'
	_block_until	: !->
		block_until	= @block_until
		date		= new Date
		date.setFullYear(block_until.substr(0, 4))
		date.setMonth(block_until.substr(5, 2) - 1)
		date.setDate(block_until.substr(8, 2))
		date.setHours(block_until.substr(11, 2))
		date.setMinutes(block_until.substr(14, 2))
		date.setSeconds(0)
		date.setMilliseconds(0)
		@set('user_data.block_until', date.getTime() / 1000)
	save			: !->
		$.ajax(
			url		: 'api/System/admin/users/' + @user_id
			type	: 'patch'
			data	:
				user	: @user_data
			success	: !->
				cs.ui.notify(L.changes_saved, 'success', 5)
		)
)
