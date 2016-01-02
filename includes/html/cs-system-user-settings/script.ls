/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
L	= cs.Language
Polymer(
	'is'		: 'cs-system-user-setings'
	behaviors	: [cs.Polymer.behaviors.Language]
	properties	:
		languages	: Array
		timezones	: Array
		user_data	: Object
	ready : !->
		Promise.all([
			$.getJSON('api/System/languages')
			$.getJSON('api/System/timezones')
			$.getJSON('api/System/profile')
		]).then ([languages, timezones, user_data]) !~>
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
			@set('languages', languages_list)
			@set('timezones', timezones_list)
			@set('user_data', user_data)
	_save : (e) !->
		e.preventDefault()
		$.ajax(
			url		: 'api/System/profile'
			type	: 'patch'
			data	: @user_data
			success	: !->
				cs.ui.notify(L.changes_saved, 'success', 5)
		)
)
