/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
Polymer(
	is			: 'cs-system-registration'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_profile_')
	]
	attached : !->
		@$.email.focus()
	_registration : (e) !->
		e.preventDefault()
		cs.registration(@$.email.value)
)
