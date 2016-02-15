/**
 * @package    CleverStyle CMS
 * @subpackage CleverStyle theme
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L	= cs.Language
Polymer(
	'is'		: 'cs-cleverstyle-header-user-block'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_profile_')
	]
	properties	:
		avatar		: String
		guest		: Boolean
		username	: String
		login		: String
	ready : !->
		@guest	= !!cs.is_guest
	_sign_in : !->
		cs.ui.simple_modal("<cs-system-sign-in/>")
	_registration : !->
		cs.ui.simple_modal("<cs-system-registration/>")
	_sign_out : cs.sign_out
	_change_password : !->
		cs.ui.simple_modal("<cs-system-change-password/>")
	_general_settings : !->
		cs.ui.simple_modal("<cs-system-user-settings/>")
)
