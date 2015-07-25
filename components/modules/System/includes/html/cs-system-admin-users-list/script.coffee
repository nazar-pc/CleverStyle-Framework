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
	tooltip_animation	:'{animation:true,delay:200}'
	L					: L
	columns				: []
	users				: []
	created				: ->
		data		= JSON.parse(@querySelector('script').innerHTML)
		@columns	= data.columns
		data.users.forEach (user) ->
			user.class		=
				switch parseInt(user.status)
					when STATUS_ACTIVE then 'uk-alert-success'
					when STATUS_INACTIVE then 'uk-alert-warning'
					else ''
			user.is_active	= `user.status == STATUS_ACTIVE`
			user.is_guest	= `user.id == GUEST_ID`
			user.is_root	= `user.id == ROOT_ID`
			user.columns	=
				for column in data.columns
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
	domReady			: ->
		$(@shadowRoot).cs().tooltips_inside()
	edit_permissions	: (event, detail, sender) ->
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
