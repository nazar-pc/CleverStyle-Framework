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
	L		: L
	save	: ->
		$.ajax(
			url		: 'api/System/admin/users'
			type	: 'post'
			data	:
				email	: @email
				type	: 'user'
			success	: (result) ->
				UIkit.modal.alert("""
					<p class="uk-alert uk-alert-success">#{L.user_was_added(result.login, result.password)}</p>
				""")
		)
)
