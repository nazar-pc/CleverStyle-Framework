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
	'is'			: 'cs-system-admin-users-edit-bot-form'
	behaviors		: [cs.Polymer.behaviors.Language]
	properties		:
		can_save			:
			type		: Boolean
			computed	: 'can_save_(user_data.*)'
		user_id				: -1
		user_data			:
			type	: Object
			value	: {}
		tooltip_animation	:'{animation:true,delay:200}'
	ready			: ->
		$.getJSON('api/System/admin/users/' + @user_id, (data) =>
			@set('user_data', data)
		)
		@workarounds(@shadowRoot)
		cs.observe_inserts_on(@shadowRoot, @workarounds)
	workarounds		: (target) ->
		$(target)
			.cs().radio_buttons_inside()
			.cs().tooltips_inside()
	status_change		: (e) ->
		@set(
			['user_data', 'status'],
			$(e.currentTarget).children('input').val()
		)
	save			: ->
		$.ajax(
			url		: 'api/System/admin/users/' + @user_id
			type	: 'patch'
			data	:
				user	: @user_data
			success	: ->
				UIkit.notify(L.changes_saved.toString(), 'success')
		)
	status_state	: (expected) ->
		status	= @user_data.status
		`status == expected`
	status_class	: (expected) ->
		'uk-button' + (if @status_state(expected) then ' uk-active' else '')
	can_save_		: ->
		@user_data.username && (@user_data.login || @user_data.email)
)
