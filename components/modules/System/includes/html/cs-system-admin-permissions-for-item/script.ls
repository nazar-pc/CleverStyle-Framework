/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-system-admin-permissions-for-item'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_permissions_')
	]
	properties	:
		group		: ''
		label		: ''
		permissions	: Object
		users		: []
		found_users	: []
		groups		: Array
	ready : !->
		Promise.all([
			cs.api('get api/System/admin/permissions/for_item', {@group, @label})
			cs.api('get api/System/admin/groups')
		]).then ([@permissions, @groups]) !~>
			if !Object.keys(@permissions.users).length
				return
			ids = (for user of @permissions.users then user).join(',')
			cs.api('get api/System/admin/users', {ids}).then (users) !~>
				@set('users', users)
		$shadowRoot	= $(@shadowRoot)
		$(@$.form).submit -> false
		$search		= $(@$.search)
		$search
			.keyup (event) !~>
				search	= $search.val()
				# Only handle Enter button and if there is some search text
				if event.which != 13 || !search
					return
				$shadowRoot.find('tr.changed')
					.removeClass('changed')
					.clone()
					.appendTo(@$.users)
				@set('found_users', [])
				cs.api('get api/System/admin/users', {search}).then (found_users) !~>
					found_users	= found_users.filter (user) ~>
						# Ignore already shown users in search results
						!$shadowRoot.find("[name='users[#{user}]']").length
					if !found_users.length
						cs.ui.notify('404 Not Found', 'warning', 5)
						return
					ids = found_users.join(',')
					cs.api('get api/System/admin/users', {ids}).then (users) !~>
						@set('found_users', users)
			.keydown (event) !->
				# Only handle Enter button
				event.which != 13
		$(@$['search-results']).on(
			'change'
			':radio'
			!->
				$(@).closest('tr').addClass('changed')
		)
	save : !->
		cs.api('post api/System/admin/permissions/for_item', @$.form).then !~>
			cs.ui.notify(@L.changes_saved, 'success', 5)
	invert : (e) !->
		$(e.currentTarget).closest('div')
			.find(':radio:not(:checked)[value!=-1]')
				.parent()
					.click()
	allow_all : (e) !->
		$(e.currentTarget).closest('div')
			.find(':radio[value=1]')
				.parent()
					.click()
	deny_all : (e) !->
		$(e.currentTarget).closest('div')
			.find(':radio[value=0]')
				.parent()
					.click()
	permission_state : (type, id, expected) ->
		permission	= @permissions[type][id]
		permission ~= expected ||
		(
			expected ~= '-1' &&
			permission == undefined
		)
	group_permission_state : (id, expected) ->
		@permission_state('groups', id, expected)
	user_permission_state : (id, expected) ->
		@permission_state('users', id, expected)
	username : (user) ->
		user.username || user.login
)
