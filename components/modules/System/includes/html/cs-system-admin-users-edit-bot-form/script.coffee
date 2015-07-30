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
	user_data			: {}
	ready				: ->
		$.getJSON('api/System/admin/users/' + @user_id, (data) =>
			@user_data	= data
		)
	domReady			: ->
		@workarounds(@shadowRoot)
		cs.observe_inserts_on(@shadowRoot, @workarounds)
	workarounds			: (target) ->
		$(target)
			.cs().radio_buttons_inside()
			.cs().tooltips_inside()
	status_change		: (event) ->
		@user_data.status	= $(event.target).children('input').val()
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
