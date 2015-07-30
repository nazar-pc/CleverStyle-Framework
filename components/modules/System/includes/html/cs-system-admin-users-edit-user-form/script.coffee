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
	tooltip_animation	:'{animation:true,delay:200}'
	L					: L
	publish				:
		user_id	: -1
	languages			: []
	timezones			: []
	user_data			: {}
	ready				: ->
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
			@languages		= languages_list
			@timezones		= timezones_list
			@block_until	= do ->
				block_until	= data[0].block_until
				date		= new Date
				if parseInt(block_until)
					date.setTime(parseInt(block_until) * 1000)
				z	= (number) ->
					('0' + number).substr(-2)
				date.getFullYear() + '-' + z(date.getMonth() + 1) + '-' + z(date.getDate()) + 'T' + z(date.getHours()) + ':' + z(date.getMinutes())
			@user_data		= data[0]
	domReady			: ->
		@workarounds(@shadowRoot)
		cs.observe_inserts_on(@shadowRoot, @workarounds)
	workarounds			: (target) ->
		$(target)
			.cs().radio_buttons_inside()
			.cs().tooltips_inside()
	status_change		: (event) ->
		@user_data.status	= $(event.target).children('input').val()
	show_password		: (event, details, sender) ->
		element	= @shadowRoot.querySelector('#password')
		if element.type == 'password'
			element.type	= 'text'
			$(sender).removeClass('uk-icon-lock').addClass('uk-icon-unlock')
		else
			element.type	= 'password'
			$(sender).removeClass('uk-icon-unlock').addClass('uk-icon-lock')
	block_untilChanged	: ->
		block_until	= @block_until
		date		= new Date
		date.setFullYear(block_until.substr(0, 4))
		date.setMonth(block_until.substr(5, 2) - 1)
		date.setDate(block_until.substr(8, 2))
		date.setHours(block_until.substr(11, 2))
		date.setMinutes(block_until.substr(14, 2))
		date.setSeconds(0)
		date.setMilliseconds(0)
		@user_data.block_until = date.getTime() / 1000
	save				: ->
		$.ajax(
			url		: 'api/System/admin/users/' + @user_id
			type	: 'patch'
			data	:
				user	: @user_data
			success	: ->
				UIkit.notify(L.changes_saved.toString(), 'success')
		)
)
