/**
 * @package    CleverStyle CMS
 * @subpackage CleverStyle theme
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L	= cs.Language
Polymer(
	'is'		: 'cs-cleverstyle-header-user-block'
	behaviors	: [cs.Polymer.behaviors.Language]
	properties	:
		avatar		: ''
		guest		: Boolean
		username	: '' + L.guest
		login		: String
	ready : !->
		@guest	= !!cs.is_guest
	_sign_in : !->
		cs.ui.simple_modal("<cs-cleverstyle-sign-in-form/>")
	_registration : !->
		cs.ui.simple_modal("<cs-cleverstyle-registration-form/>")
	_sign_out : cs.sign_out
)
