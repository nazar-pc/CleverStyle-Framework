###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
body	= document.body
html	= body.parentNode
Polymer(
	'is'		: 'cs-section-modal'
	'extends'	: 'section'
	behaviors	: [
		Polymer.cs.behaviors.this
		Polymer.cs.behaviors.tooltip
	]
	properties	:
		autoOpen	:
			type				: Boolean
		autoDestroy	:
			type				: Boolean
		opened		:
			observer			: '_opened_changed'
			reflectToAttribute	: true
			type				: Boolean
		transparent	:
			reflectToAttribute	: true
			type				: Boolean
	created : ->
		@_esc_handler	= (e) =>
			if e.keyCode == 27 # Esc
				@close()
			return
		return
	attached : ->
		if @autoOpen
			@open()
	_opened_changed : ->
		if !@_attached_to_html
			@_attached_to_html	= true
			body.parentNode.appendChild(@)
		body.modalOpened = body.modalOpened || 0
		if @opened
			document.addEventListener('keydown', @_esc_handler)
			# Actually insert content only when needed
			if @content
				@innerHTML	= @content
				# Free memory
				@content	= null
			++body.modalOpened
			@fire('open')
			document.body.setAttribute('modal-opened', '')
		else
			document.removeEventListener('keydown', @_esc_handler)
			--body.modalOpened
			@fire('close')
			if !body.modalOpened
				document.body.removeAttribute('modal-opened')
			if @autoDestroy
				@parentNode.removeChild(@)
		return
	open : ->
		if !@opened
			if !@_attached_to_html
				@_attached_to_html	= true
				body.parentNode.appendChild(@)
				# Put modal opening into stack of functions to call
				setTimeout(@open.bind(@), 0)
			else
				@opened = true
		@
	close : ->
		if @opened
			@opened = false
		@
)
