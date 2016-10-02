/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-system-user-settings'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_profile_')
	]
	properties	:
		languages	: Array
		timezones	: Array
		user_data	: Object
		can_upload	: 'file_upload' of cs
	ready : !->
		cs.api([
			'get api/System/languages'
			'get api/System/timezones'
			'get api/System/profile'
		]).then ([languages, timezones, user_data]) !~>
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
			@set('languages', languages_list)
			@set('timezones', timezones_list)
			@set('user_data', user_data)
		cs.file_upload?(
			@$['upload-avatar']
			(files) !~>
				if files.length
					@set('user_data.avatar', files[0])
		)
	_save : (e) !->
		e.preventDefault()
		cs.api('patch api/System/profile', @user_data).then !~>
			cs.ui.notify(@L.changes_saved, 'success', 5)
)
