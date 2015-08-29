###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
html	= document.documentElement
Polymer(
	'is'			: 'cs-nav-dropdown'
	'extends'		: 'nav'
	behaviors		: [Polymer.cs.behaviors.this]
	hostAttributes	:
		role	: 'group'
	properties		:
		asIs	:
			reflectToAttribute	: true
			type				: Boolean
		opened	:
			reflectToAttribute	: true
			type				: Boolean
		target	: Object
	created : ->
		document.addEventListener('keydown', (e)  =>
			if e.keyCode == 27 && @opened # Esc
				@opened = false
		)
		document.addEventListener('click', (e)  =>
			# We'll ignore clicks on button, since button will close dropdown by itself
			if @opened
				for element in e.path
					if element == @target
						return
				@close()
		)
	attached : ->
		if !@target && @previousElementSibling.tagName == 'BUTTON'
			@target			= @previousElementSibling
			@target.action	= 'toggle'
			@target.bind	= @
		return
	toggle : ->
		if !@opened
			@open()
		else
			@close()
		return
	open : ->
		if @opened || !@target
			return
		target_position	= @target.getBoundingClientRect()
		@style.left		= target_position.left + 'px'
		@style.top		= target_position.top + target_position.height + 'px'
		@opened			= true
		@fire('open')
		return
	close : ->
		if @opened
			@opened	= false
			@fire('close')
		return
)
