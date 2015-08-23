###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-section-modal'
	'extends'	: 'section'
	properties	:
		opened		:
			observer			: '_opened_changed'
			reflectToAttribute	: true
			type				: Boolean
		transparent	:
			reflectToAttribute	: true
			type				: Boolean
	created : ->
		@style.display = 'none'
	attached : ->
		document.body.parentNode.appendChild(@)
		setTimeout (=>
			@style.display = ''
		), 100
	_opened_changed : ->
		if @opened
			@fire('open')
			document.body.setAttribute('modal-opened', '')
		else
			@fire('close')
			document.body.removeAttribute('modal-opened')
	open : ->
		@opened = true
		@
	close : ->
		@opened = false
		@
)
