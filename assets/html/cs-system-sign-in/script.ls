/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
Polymer(
	is			: 'cs-system-sign-in'
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
