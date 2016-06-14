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
		@$.form.addEventListener('submit', (e) !-> e.preventDefault())
		@$.search
			..addEventListener('keyup', (e) !~>
				search	= e.target.value
				# Only handle Enter button and if there is some search text
				if e.which != 13 || !search
					return
				for row in @shadowRoot.querySelectorAll('tr.changed')
					row.classList.remove('changed')
					@$.users.insertAdjacentHTML('beforeend', row.outerHTML)
				@set('found_users', [])
				cs.api('get api/System/admin/users', {search}).then (found_users) !~>
					found_users	= found_users.filter (user) ~>
						# Ignore already shown users in search results
						!@shadowRoot.querySelector("[name='users[#user]']")
					if !found_users.length
						cs.ui.notify('404 Not Found', 'warning', 5)
						return
					ids = found_users.join(',')
					cs.api('get api/System/admin/users', {ids}).then (users) !~>
						@set('found_users', users)
			)
			..addEventListener('keydown', (e) !->
				# Only handle Enter button
				if e.which == 13
					e.preventDefault()
			)
		@$['search-results'].addEventListener('click', (e) !->
			if !e.target.matches('[type=radio]')
				return
			tr = e.target.parentElement
			while !tr.matches('tr')
				tr	= tr.parentElement
			tr.classList.add('changed')
		)
	save : !->
		cs.api('post api/System/admin/permissions/for_item', @$.form).then !~>
			cs.ui.notify(@L.changes_saved, 'success', 5)
	invert : (e) !->
		div	= e.currentTarget
		while !div.matches('div')
			div	= div.parentElement
		radios = Array::filter.call(
			div.querySelectorAll("[type=radio]:not([value='-1'])")
			-> !it.checked
		)
		for radio in radios
			radio.parentElement.click()
	allow_all : (e) !->
		div	= e.currentTarget
		while !div.matches('div')
			div	= div.parentElement
		for radio in div.querySelectorAll("[type=radio][value='1']")
			radio.parentElement.click()
	deny_all : (e) !->
		div	= e.currentTarget
		while !div.matches('div')
			div	= div.parentElement
		for radio in div.querySelectorAll("[type=radio][value='0']")
			radio.parentElement.click()
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
