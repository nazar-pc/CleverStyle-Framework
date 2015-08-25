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
	'is'				: 'cs-system-admin-permissions-for'
	behaviors			: [cs.Polymer.behaviors.Language]
	properties			:
		'for'	:
			type	: String
			value	: ''
		group	: ''
		user	: ''
	all_permissions		: {}
	permissions			: {}
	ready				: ->
		$.when(
			$.getJSON('api/System/admin/permissions')
			$.getJSON("api/System/admin/#{@for}s/#{@[@for]}/permissions")
		).done (all_permissions, permissions) =>
			@all_permissions	=
				for group, labels of all_permissions[0]
					group	: group
					labels	:
						for label, id of labels
							name	: label
							id		: id
			@permissions		= permissions[0]
	save				: ->
		default_data	= (key + '=' + value for key, value of $.ajaxSettings.data).join('&')
		$.ajax(
			url		: "api/System/admin/#{@for}s/#{@[@for]}/permissions"
			data	: $(@$.form).serialize() + '&' + default_data
			type	: 'post'
			success	: ->
				UIkit.notify(L.changes_saved.toString(), 'success')
		)
	invert				: (e) ->
		$(e.currentTarget).closest('div')
			.find(':radio:not(:checked)[value!=-1]')
				.parent()
					.click()
	allow_all			: (e) ->
		$(e.currentTarget).closest('div')
			.find(':radio[value=1]')
				.parent()
					.click()
	deny_all			: (e) ->
		$(e.currentTarget).closest('div')
			.find(':radio[value=0]')
				.parent()
					.click()
	permission_state	: (id, expected) ->
		permission	= @permissions[id]
		`permission == expected` ||
		(
			`expected == '-1'` &&
			permission == undefined
		)
)
