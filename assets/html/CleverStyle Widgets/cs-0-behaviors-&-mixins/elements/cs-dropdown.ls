/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
html						= document.documentElement
csw.behaviors.cs-dropdown	= [
	hostAttributes	:
		role	: 'group'
	properties		:
		align	: 'left' # Can be `right`
		opened	:
			reflectToAttribute	: true
			type				: Boolean
		target	: Object
	created : !->
		document.addEventListener('keydown', (e)  !~>
			if e.keyCode == 27 && @opened # Esc
				@opened = false
		)
		document.addEventListener('click', (e)  !~>
			# We'll ignore clicks on button, since button will close dropdown by itself
			if @opened
				for element in e.composedPath()
					if element == @target
						return
				@close()
		)
	attached : !->
		if !@target && @previousElementSibling.matches('cs-button')
			@target			= @previousElementSibling
			@target.action	= 'toggle'
			@target.bind	= @
	toggle : !->
		if !@opened
			@open()
		else
			@close()
	open : !->
		if @opened || !@target
			return
		target_position	= @target.getBoundingClientRect()
		if @align == 'left'
			@style.left = target_position.left + 'px'
		else
			@style.right = (html.clientWidth - target_position.right - scrollX) + 'px'
		@style.top	= target_position.top + target_position.height + 'px'
		@opened		= true
		@fire('open')
	close : !->
		if @opened
			@opened	= false
			@fire('close')
]
