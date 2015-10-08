/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
L	= cs.Language
Polymer(
	'is'		: 'cs-system-registration-form'
	behaviors	: [cs.Polymer.behaviors.Language]
	attached : !->
		@$.email.focus()
	_registration : (e) !->
		e.preventDefault()
		cs.registration(@$.email.value)
)
