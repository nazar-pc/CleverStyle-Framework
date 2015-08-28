###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
html	= document.documentElement
Polymer(
	'is'		: 'cs-tooltip'
	behaviors	: [Polymer.cs.behaviors.tooltip]
	properties	:
		show		:
			reflectToAttribute	: true
			type				: Boolean
		showQuick	:
			reflectToAttribute	: true
			type				: Boolean
		top			:
			reflectToAttribute	: true
			type				: Boolean
	attached : ->
		parent	= @parentNode
		if parent.tagName != 'HTML'
			parent.removeChild(@)
			@_tooltip_for_element(parent)
		else
			document.addEventListener('keydown',(e)  =>
				if e.keyCode == 27 && @show # Esc
					@show = false
			)
			@addEventListener('mouseenter', =>
				@show	= true
				return
			)
			@addEventListener('pointerenter', =>
				@show	= true
				return
			)
			@addEventListener('mouseleave', =>
				@show	= false
				return
			)
			@addEventListener('pointerleave', =>
				@show	= false
				return
			)
		return
	_show : (element) ->
		if !element.tooltip || @show
			return
		if @innerHTML != element.tooltip
			@innerHTML = element.tooltip
		tooltip_position		= @_get_tooltip_position(element)
		@style.top				= tooltip_position.top + 'px'
		@style.left				= tooltip_position.left + 'px'
		@top					= tooltip_position.arrow_top
		@$.arrow.style.left		= -tooltip_position.arrow_left_offset + 'px'
		@$.arrow.style.right	= tooltip_position.arrow_left_offset + 'px'
		@show					= true
		return
	_hide : ->
		if @show
			@show	= false
		return
	_get_tooltip_size : ->
		@style.left		= -html.clientWidth
		@style.top		= -html.clientHeight
		@showQuick		= true
		tooltip_size	= @getBoundingClientRect()
		@showQuick		= false
		tooltip_size
	_get_tooltip_position : (element) ->
		tooltip_size		= @_get_tooltip_size()
		element_position	= element.getBoundingClientRect()
		tooltip_position	=
			top					: scrollY
			left				: scrollX
			arrow_top			: false
			arrow_left_offset	: 0
		# Calculation of vertical position
		if element_position.top > tooltip_size.height
			tooltip_position.top	+= element_position.top - tooltip_size.height
		else
			tooltip_position.arrow_top	= true
			tooltip_position.top		+= element_position.bottom + element_position.height
		# Calculation of horizontal position
		left_offset	= element_position.left + (element_position.width / 2) - (tooltip_size.width / 2)
		if left_offset >= 0
			if left_offset + tooltip_size.width <= html.clientWidth
				tooltip_position.left += left_offset
			else
				tooltip_position.left				+= html.clientWidth - tooltip_size.width
				tooltip_position.arrow_left_offset	= html.clientWidth - (tooltip_size.width / 2) - element_position.left - (element_position.width / 2)
		else
			tooltip_position.arrow_left_offset	= (tooltip_size.width / 2) - element_position.left - (element_position.width / 2)
		tooltip_position
)
