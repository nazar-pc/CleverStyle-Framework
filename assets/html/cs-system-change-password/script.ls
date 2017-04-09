/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-system-change-password'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_profile_')
	]
	attached : !->
		@$.current_password.focus()
	_change_password : (e) !->
		e.preventDefault()
		cs.change_password(@$.current_password.value, @$.new_password.value)
	_show_password	: (e) ->
		lock		= e.currentTarget.querySelector('cs-icon')
		password	= lock.parentElement.parentElement.previousElementSibling.firstElementChild
		if password.type == 'password'
			password.type	= 'text'
			lock.icon		= 'unlock'
		else
			password.type	= 'password'
			lock.icon		= 'lock'
)
