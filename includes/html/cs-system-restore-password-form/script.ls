/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
L	= cs.Language
Polymer(
	'is'		: 'cs-system-restore-password-form'
	behaviors	: [cs.Polymer.behaviors.Language]
	attached : !->
		@$.login.focus()
	_restore_password : (e) !->
		e.preventDefault()
		cs.restore_password(@$.login.value)
)
