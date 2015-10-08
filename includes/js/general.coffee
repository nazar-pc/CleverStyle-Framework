###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
$ ->
	L	= cs.Language
	$.ajaxSetup(
		type	: 'post'
		error	: (xhr) ->
			cs.ui.notify(
				if xhr.responseText
					JSON.parse(xhr.responseText).error_description
				else
					L.connection_error.toString()
				'warning'
				5
			)
	)
	$('#current_password').click ->
		password	= $('.cs-profile-current-password')
		if password.prop('type') == 'password'
			password.prop('type', 'text')
			this.icon = 'unlock'
		else
			password.prop('type', 'password')
			this.icon = 'lock'
	$('#new_password').click ->
		password	= $('.cs-profile-new-password')
		if password.prop('type') == 'password'
			password.prop('type', 'text')
			this.icon = 'unlock'
		else
			password.prop('type', 'password')
			this.icon = 'lock'
	$('.cs-profile-change-password').click ->
		cs.change_password $('.cs-profile-current-password').val(), $('.cs-profile-new-password').val()
	return
