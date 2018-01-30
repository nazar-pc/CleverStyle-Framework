/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
body					= document.body
html					= document.documentElement
csw.behaviors.cs-modal	= [
	csw.behaviors.this
	properties	:
		asIs		:
			reflectToAttribute	: true
			type				: Boolean
		autoDestroy	: Boolean
		autoOpen	: Boolean
		manualClose	: Boolean
		opened		:
			observer	: '_opened_changed'
			type		: Boolean
	listeners	:
		transitionend	: '_transitionend'
	_esc_handler : (e) !->
		if e.keyCode == 27 && !@manualClose # Esc
			@close()
	ready : !->
		@_esc_handler = @_esc_handler.bind(@)
	attached : !->
		if @previousElementSibling?.matches('cs-button') && !@previousElementSibling.action
			@previousElementSibling.action	= 'open'
			@previousElementSibling.bind	= @
		if @autoOpen
			# Prevent repeated opening
			@autoOpen = false
			@open()
	_transitionend : !->
		if !@opened && @autoDestroy
			@parentNode?.removeChild(@)
	_overlay_tap : !->
		if !@manualClose
			@close()
	_opened_changed : (opened) !->
		if opened == undefined
			return
		if !@parentNode?.matches('html')
			html.appendChild(@)
		# Hack to make modal opening really smooth
		@distributeContent(true)
		Polymer.dom.flush()
		body.modalOpened = body.modalOpened || 0
		if opened
			document.addEventListener('keydown', @_esc_handler)
			# Actually insert content only when needed
			if @content
				@innerHTML	= @content
				# Free memory
				@content	= null
			++body.modalOpened
			@fire('open')
			document.body.setAttribute('modal-opened', '')
			setTimeout (!~>
				@setAttribute('opened', '')
			), 100
		else
			document.removeEventListener('keydown', @_esc_handler)
			--body.modalOpened
			@fire('close')
			@removeAttribute('opened')
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
]
