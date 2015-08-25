###*
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
###
L	= cs.Language
Polymer(
	'is'			: 'cs-system-admin-users-edit-user-form'
	behaviors		: [cs.Polymer.behaviors.Language]
	properties		:
		user_id				: -1
		user_data			:
			type	: Object
			value	: {}
		languages			: []
		timezones			: []
		block_until			:
			observer	: '_block_until'
			type		: String
	ready			: ->
		$.when(
			$.getJSON('api/System/admin/languages')
			$.getJSON('api/System/admin/timezones')
			$.getJSON('api/System/admin/users/' + @user_id)
		).done (languages, timezones, data) =>
			languages_list	= []
			languages_list.push(
				clanguage	: ''
				description	: L.system_default
			)
			for language in languages[0]
				languages_list.push(
					clanguage	: language
					description	: language
				)
			timezones_list	= []
			timezones_list.push(
				timezone	: ''
				description	: L.system_default
			)
			for description, timezone of timezones[0]
				timezones_list.push(
					timezone	: timezone
					description	: description
				)
			@set('languages', languages_list)
			@set('timezones', timezones_list)
			block_until	= do ->
				block_until	= data[0].block_until
				date		= new Date
				if parseInt(block_until)
					date.setTime(parseInt(block_until) * 1000)
				z	= (number) ->
					('0' + number).substr(-2)
				date.getFullYear() + '-' + z(date.getMonth() + 1) + '-' + z(date.getDate()) + 'T' + z(date.getHours()) + ':' + z(date.getMinutes())
			@set('block_until', block_until)
			@set('user_data', data[0])
		@workarounds(@shadowRoot)
		cs.observe_inserts_on(@shadowRoot, @workarounds)
	workarounds		: (target) ->
		$(target)
			.cs().radio_buttons_inside()
			.cs().tooltips_inside()
	status_change	: (e) ->
		@set('user_data.status', $(e.currentTarget).children('input').val())
	show_password	: (e) ->
		$lock		= $(e.currentTarget)
		password	= $lock.next()[0]
		if password.type == 'password'
			password.type	= 'text'
			$lock.removeClass('uk-icon-lock').addClass('uk-icon-unlock')
		else
			password.type	= 'password'
			$lock.removeClass('uk-icon-unlock').addClass('uk-icon-lock')
	_block_until	: ->
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
	status_state	: (expected) ->
		status	= @user_data.status
		`status == expected`
	status_class	: (expected) ->
		'uk-button' + (if @status_state(expected) then ' uk-active' else '')
	save			: ->
		$.ajax(
			url		: 'api/System/admin/users/' + @user_id
			type	: 'patch'
			data	:
				user	: @user_data
			success	: ->
				UIkit.notify(L.changes_saved.toString(), 'success')
		)
)
