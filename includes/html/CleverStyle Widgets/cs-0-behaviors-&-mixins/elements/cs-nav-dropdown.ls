/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
html									= document.documentElement
Polymer.cs.behaviors.cs-nav-dropdown	= [
	Polymer.cs.behaviors.this
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
				for element in e.path
					if element == @target
						return
				@close()
		)
	attached : !->
		if !@target && @previousElementSibling.matches('button')
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
