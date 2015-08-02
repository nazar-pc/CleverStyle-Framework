###*
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
###
L				= cs.Language
STATUS_ACTIVE	= 1
STATUS_INACTIVE	= 0
GUEST_ID		= 1
ROOT_ID			= 2
Polymer(
	tooltip_animation		:'{animation:true,delay:200}'
	L						: L
	search_column			: ''
	search_mode				: 'LIKE'
	search_text				: ''
	search_page				: 1
	search_limit			: 20
	search_columns			: []
	search_modes			: []
	all_columns				: []
	columns					: [
		'id'
		'login'
		'username'
		'email'
	]
	users					: []
	users_count				: 0
	created					: ->
		$.ajax(
			url		: 'api/System/admin/users'
			type	: 'search_options'
			success	: (search_options) =>
				search_columns	= []
				for column in search_options.columns
					search_columns.push(
						name		: column
						selected	: @columns.indexOf(column) != -1
					)
				@search_columns	= search_columns
				@all_columns	= search_options.columns
				@search_modes	= search_options.modes
		)
		@search()
	search					: ->
		# Hack to force re-rendering pages navigation
		@users_count	= 0
		$.ajax(
			url		: 'api/System/admin/users'
			type	: 'search'
			data	:
				column	: @search_column
				mode	: @search_mode
				text	: @search_text
				page	: @search_page
				limit	: @search_limit
			success	: (data) =>
				@users_count	= data.count
				if !data.count
					@users	= []
					return
				data.users.forEach (user) =>
					user.class		=
						switch parseInt(user.status)
							when STATUS_ACTIVE then 'uk-alert-success'
							when STATUS_INACTIVE then 'uk-alert-warning'
							else ''
					user.is_active	= `user.status == STATUS_ACTIVE`
					user.is_guest	= `user.id == GUEST_ID`
					user.is_root	= `user.id == ROOT_ID`
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
								'a'
							else if user.is_user
								'u'
							else if user.is_bot
								'b'
							else
								'g'
						user.type		= L[type]
						user.type_info	= L[type + '_info']
				@users	= data.users
		)
	domReady				: ->
		@workarounds(@shadowRoot)
		cs.observe_inserts_on(@shadowRoot, @workarounds)
	workarounds				: (target) ->
		$(target)
			.cs().pagination_inside()
			.cs().tabs_inside()
			.cs().tooltips_inside()
	toggle_search_column	: (event, detail, sender) ->
		index			= $(sender).data('column-index')
		column			= @search_columns[index]
		column.selected = !column.selected
		@columns		= (column.name for column in @search_columns when column.selected)
		@search_page	= 1
		@search()
	page_click				: (event, detail, sender) ->
		$(sender).one('select.uk.pagination', (event, pageIndex) =>
			@search_page	= pageIndex + 1
			@search()
		)
	search_columnChanged	: ->
		@search_page	= 1
		@search()
	search_modeChanged		: ->
		@search_page	= 1
		@search()
	search_textChanged		: ->
		clearTimeout(@search_text_timeout)
		@search_text_timeout	= setTimeout(@search.bind(@), 300)
	search_limitChanged		: ->
		@search_page	= 1
		@search()
	add_user				: ->
		$.cs.simple_modal("""
			<h3>#{L.adding_a_user}</h3>
			<cs-system-admin-users-add-user-form/>
		""").on(
			'hide.uk.modal'
			@search.bind(@)
		)
	add_bot					: ->
		$.cs.simple_modal("""
			<h3>#{L.adding_a_bot}</h3>
			<cs-system-admin-users-add-bot-form/>
		""").on(
			'hide.uk.modal'
			@search.bind(@)
		)
	edit_user				: (event, detail, sender) ->
		$sender	= $(sender)
		index	= $sender.closest('[data-user-index]').data('user-index')
		user	= @users[index]
		if user.is_bot
			title		= L.editing_of_bot_information(
				user.username || user.login
			)
			$.cs.simple_modal("""
				<h2>#{title}</h2>
				<cs-system-admin-users-edit-bot-form user_id="#{user.id}"/>
			""").on(
				'hide.uk.modal'
				@search.bind(@)
			)
		else
			title		= L.editing_of_user_information(
				user.username || user.login
			)
			$.cs.simple_modal("""
				<h2>#{title}</h2>
				<cs-system-admin-users-edit-user-form user_id="#{user.id}"/>
			""").on(
				'hide.uk.modal'
				@search.bind(@)
			)
	edit_permissions		: (event, detail, sender) ->
		$sender		= $(sender)
		index		= $sender.closest('[data-user-index]').data('user-index')
		user		= @users[index]
		title_key	= if user.is_bot then 'permissions_for_bot' else 'permissions_for_user'
		title		= L[title_key](
			user.username || user.login
		)
		$.cs.simple_modal("""
			<h2>#{title}</h2>
			<cs-system-admin-permissions-for user="#{user.id}" for="user"/>
		""")
)
