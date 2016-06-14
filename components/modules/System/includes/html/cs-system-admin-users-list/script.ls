/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
const STATUS_ACTIVE		= 1
const STATUS_INACTIVE	= 0
const GUEST_ID			= 1
const ROOT_ID			= 2
L	= cs.Language('system_admin_users_')
Polymer(
	'is'		: 'cs-system-admin-users-list'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_users_')
	]
	properties	:
		search_column		: ''
		search_mode			: 'LIKE'
		search_text			:
			observer	: 'search_textChanged'
			type		: String
			value		: ''
		search_page			:
			observer	: 'search'
			type		: Number
			value		: 1
		search_pages		:
			computed	: '_search_pages(users_count, search_limit)'
			type		: Number
		search_limit		: 20
		search_columns		: []
		search_modes		: []
		all_columns			: []
		columns				: [
			'id'
			'login'
			'username'
			'email'
		]
		users				: []
		users_count			: 0
		show_pagination		:
			computed	: '_show_pagination(users_count, search_limit)'
			type		: Boolean
		searching			: false
		searching_loader	: false
		_initialized		: true
	observers	: [
		'search_again(search_column, search_mode, search_limit, _initialized)'
	]
	ready : !->
		cs.api('search_options api/System/admin/users').then (search_options) !~>
			search_columns	= []
			for column in search_options.columns
				search_columns.push(
					name		: column
					selected	: @columns.indexOf(column) != -1
				)
			@search_columns	= search_columns
			@all_columns	= search_options.columns
			@search_modes	= search_options.modes
	search : !->
		if @searching || @_initialized == undefined
			return
		@searching			= true
		searching_timeout	= setTimeout (!~>
			@searching_loader	= true
		), 200
		cs.api(
			'search api/System/admin/users'
			column	: @search_column
			mode	: @search_mode
			text	: @search_text
			page	: @search_page
			limit	: @search_limit
		)
			.then (data) !~>
				clearTimeout(searching_timeout)
				@searching			= false
				@searching_loader	= false
				@users_count	= data.count
				if !data.count
					@set('users', [])
					return
				data.users.forEach (user) !~>
					user.class		=
						switch parseInt(user.status)
							when STATUS_ACTIVE then 'cs-block-success cs-text-success'
							when STATUS_INACTIVE then 'cs-block-warning cs-text-warning'
							else ''
					user.is_guest	= user.id ~= GUEST_ID
					user.is_root	= user.id ~= ROOT_ID
					user.columns	=
						for column in @columns
							do (value = user[column]) ->
								if value instanceof Array
									value.join(', ')
								else
									value
					do ->
						type			=
							if user.is_root || user.is_admin
								'admin'
							else if user.is_user
								'user'
							else
								'guest'
						user.type		= L[type]
						user.type_info	= L[type + '_info']
				@set('users', data.users)
			.catch !~>
				clearTimeout(searching_timeout)
				@searching			= false
				@searching_loader	= false
				@set('users', [])
				@users_count	= 0
	toggle_search_column : (e) !->
		index			= e.model.index
		column			= @search_columns[index]
		@set(['search_columns', index, 'selected'], !column.selected)
		@set('columns', for column in @search_columns when column.selected then column.name)
		@search_again()
	search_again : !->
		if @search_page > 1
			# Will execute search implicitly
			@search_page	= 1
		else
			@search()
	search_textChanged : !->
		if @_initialized == undefined
			return
		clearTimeout(@search_text_timeout)
		@search_text_timeout	= setTimeout(@search_again.bind(@), 300)
	_show_pagination : (users_count, search_limit) ->
		parseInt(users_count) > parseInt(search_limit)
	_search_pages : (users_count, search_limit) ->
		Math.ceil(users_count / search_limit)
	add_user : !->
		cs.ui.simple_modal("""
			<h3>#{L.adding_a_user}</h3>
			<cs-system-admin-users-add-user-form/>
		""").addEventListener('close', @~search)
	edit_user : (e) !->
		data	= e.currentTarget.parentElement
		while !data.matches('[data-user-index]')
			data	= data.parentElement
		index	= data.dataset.user-index
		user	= @users[index]
		title	= L.editing_of_user_information(
			user.username || user.login
		)
		cs.ui.simple_modal("""
			<h2>#{title}</h2>
			<cs-system-admin-users-edit-user-form user_id="#{user.id}"/>
		""").addEventListener('close', @~search)
	edit_groups : (e) !->
		data	= e.currentTarget.parentElement
		while !data.matches('[data-user-index]')
			data	= data.parentElement
		index	= data.dataset.user-index
		user	= @users[index]
		title	= L.user_groups(user.username || user.login)
		cs.ui.simple_modal("""
			<h2>#{title}</h2>
			<cs-system-admin-users-groups-form user="#{user.id}" for="user"/>
		""")
	edit_permissions : (e) !->
		data	= e.currentTarget.parentElement
		while !data.matches('[data-user-index]')
			data	= data.parentElement
		index	= data.dataset.user-index
		user	= @users[index]
		title	= L.permissions_for_user(
			user.username || user.login
		)
		cs.ui.simple_modal("""
			<h2>#{title}</h2>
			<cs-system-admin-permissions-for user="#{user.id}" for="user"/>
		""")
)
