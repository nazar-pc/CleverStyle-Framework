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
		content		: String
		opened		:
			observer			: '_opened_changed'
			reflectToAttribute	: true
			type				: Boolean
		transparent	:
			reflectToAttribute	: true
			type				: Boolean
	created : ->
		@style.display	= 'none'
		@_esc_handler	= (e) =>
			if e.keyCode == 27 # Esc
				@close()
	attached : ->
		document.addEventListener('keydown', @_esc_handler)
		body.parentNode.appendChild(@)
		setTimeout (=>
			@style.display = ''
		), 100
	detached : ->
		document.removeEventListener('keydown', @_esc_handler)
	_opened_changed : ->
		body.modalOpened = body.modalOpened || 0
		if @opened
			# Actually insert content only when needed
			if @content
				@innerHTML	= @content
				# Free memory
				@content	= null
			++body.modalOpened
			@fire('open')
			document.body.setAttribute('modal-opened', '')
		else
			--body.modalOpened
			@fire('close')
			if !body.modalOpened
				document.body.removeAttribute('modal-opened')
	open : ->
		if !@opened
			@opened = true
		@
	close : ->
		if @opened
			@opened = false
		@
)
