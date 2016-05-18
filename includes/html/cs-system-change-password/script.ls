/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
L	= cs.Language
Polymer(
	'is'		: 'cs-system-change-password'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_profile_')
	]
	attached : !->
		@$.current_password.focus()
	_change_password : (e) !->
		e.preventDefault()
		cs.change_password(@$.current_password.value, @$.new_password.value)
	_show_password	: (e) ->
		lock		= e.currentTarget
		password	= lock.previousElementSibling
		if password.type == 'password'
			password.type	= 'text'
			lock.icon		= 'unlock'
		else
			password.type	= 'password'
			lock.icon		= 'lock'
)
