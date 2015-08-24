###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
body	= document.body
Polymer(
	'is'		: 'cs-section-modal'
	'extends'	: 'section'
	behaviors	: [Polymer.cs.behaviors.this]
	properties	:
		content		:
			observer	: '_content_changed'
			type		: String
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
		body.parentNode.appendChild(@)
		setTimeout (=>
			@style.display = ''
		), 100
	_content_changed : ->
		@innerHTML = @content
	_opened_changed : ->
		body.modalOpened = body.modalOpened || 0
		if @opened
			@fire('open')
			document.body.setAttribute('modal-opened', '')
			++body.modalOpened
		else
			@fire('close')
			--body.modalOpened
			if !body.modalOpened
				document.body.removeAttribute('modal-opened')
	open : ->
		@opened = true
		@
	close : ->
		@opened = false
		@
)
