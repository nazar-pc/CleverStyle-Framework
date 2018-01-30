/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
Polymer(
	is			: 'cs-system-restore-password-form'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_profile_')
	]
	attached : !->
		setTimeout(@$.login~focus)
	_restore_password : (e) !->
		e.preventDefault()
		cs.restore_password(@$.login.value)
)
