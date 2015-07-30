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
	save				: ->
		$.ajax(
			url		: 'api/System/admin/users'
			type	: 'post'
			data	:
				name		: @name
				user_agent	: @user_agent
				ip			: @ip
				type		: 'bot'
			success	: ->
				UIkit.notify(L.changes_saved.toString(), 'success')
		)
)
