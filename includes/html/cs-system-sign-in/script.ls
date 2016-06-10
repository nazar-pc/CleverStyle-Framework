/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
L	= cs.Language
Polymer(
	'is'		: 'cs-system-sign-in'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_profile_')
	]
	ready : !->
		cs.Event.fire('cs-system-sign-in', @)
	attached : !->
		setTimeout(@$.login~focus)
	_sign_in : (e) !->
		e.preventDefault()
		cs.sign_in(@$.login.value, @$.password.value)
	_restore_password : !->
		cs.ui.simple_modal("<cs-system-restore-password-form/>")
)
