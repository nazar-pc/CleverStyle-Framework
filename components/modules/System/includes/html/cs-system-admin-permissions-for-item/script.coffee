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
	publish				:
		group	: ''
		label	: ''
	tooltip_animation	:'{animation:true,delay:200}'
	L					: L
	permissions			: {}
	users				: []
	found_users			: []
	groups				: []
	ready				: ->
		$.when(
			$.getJSON(
				'api/System/admin/permissions/for_item'
				group	: @group
				label	: @label
			)
			$.getJSON('api/System/admin/groups')
		).done (permissions, groups) =>
			@permissions	= permissions[0]
			@groups			= groups[0]
			if !Object.keys(@permissions.users).length
				return
			$.getJSON(
				'api/System/admin/users'
				{
					ids	: (user for user of @permissions.users).join(',')
				}
				(users) =>
					@users	= users
			)
		$shadowRoot	= $(@shadowRoot)
		$search		= $(@$.search)
		$search
			.keyup (event) =>
				text	= $search.val()
				# Only handle Enter button and if there is some text
				if event.which != 13 || !text
					return
				$shadowRoot.find('cs-table-row.changed')
					.removeClass('changed')
					.appendTo(@$.users)
				$.getJSON(
					'api/System/admin/users'
					search	: text
					(found_users) =>
						found_users	= found_users.filter (user) =>
							# Ignore already shown users in search results
							!$shadowRoot.find("[name='users[#{user}]']").length
						if !found_users.length
							UIkit.notify('404 Not Found', 'warning')
							return
						$.getJSON(
							'api/System/admin/users'
							ids	: found_users.join(',')
							(users) =>
								@found_users	= users
						)
				)
			.keydown (event) =>
				# Only handle Enter button
				event.which != 13
		$(@$['search-results']).on(
			'change'
			':radio'
			->
				$(@).closest('cs-table-row').addClass('changed')
		)
	domReady			: ->
		@workarounds(@shadowRoot)
		cs.observe_inserts_on(@shadowRoot, @workarounds)
	workarounds			: (target) ->
		$(target)
			.cs().tooltips_inside()
			.cs().radio_buttons_inside()
			.cs().tabs_inside()
	save				: ->
		default_data	= ("#{key}=#{value}" for key, value of $.ajaxSettings.data).join('&')
		$.ajax(
			url		: 'api/System/admin/permissions/for_item'
			data	: $(@$.form).serialize() + '&label=' + @label + '&group=' + @group + '&' + default_data
			type	: 'post'
			success	: ->
				UIkit.notify(L.changes_saved.toString(), 'success')
		)
	invert				: (event, detail, sender) ->
		$(sender).closest('div')
			.find(':radio:not(:checked)[value!=-1]')
				.parent()
					.click()
	allow_all			: (event, detail, sender) ->
		$(sender).closest('div')
			.find(':radio[value=1]')
				.parent()
					.click()
	deny_all			: (event, detail, sender) ->
		$(sender).closest('div')
			.find(':radio[value=0]')
				.parent()
					.click()
)
