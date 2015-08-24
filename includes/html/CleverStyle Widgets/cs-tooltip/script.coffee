###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
tooltip_element	= null
Polymer(
	'is'	: 'cs-tooltip'
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
			@_initialize_tooltip()
			show	= tooltip_element.show.bind(tooltip_element, parent)
			hide	= tooltip_element.hide.bind(tooltip_element, parent)
			parent.addEventListener('mouseenter', show)
			parent.addEventListener('pointerenter', show)
			parent.addEventListener('mouseleave', hide)
			parent.addEventListener('pointerleave', hide)
		else
			@addEventListener('mouseenter', =>
				@show	= true
			)
			@addEventListener('pointerenter', =>
				@show	= true
			)
			@addEventListener('mouseleave', =>
				@show	= false
			)
			@addEventListener('pointerleave', =>
				@show	= false
			)
	_initialize_tooltip : ->
		if !tooltip_element
			tooltip_element = document.createElement('cs-tooltip')
			document.body.parentNode.appendChild(tooltip_element)
		return
	show : (element) ->
		if @innerHTML != element.tooltip
			@innerHTML = element.tooltip
		tooltip_position		= @_get_tooltip_position(element)
		@style.top				= tooltip_position.top + 'px'
		@style.left				= tooltip_position.left + 'px'
		@top					= tooltip_position.arrow_top
		@$.arrow.style.left		= -tooltip_position.arrow_left_offset + 'px'
		@$.arrow.style.right	= tooltip_position.arrow_left_offset + 'px'
		@show					= true
	hide : ->
		@show	= false
	_get_tooltip_size : ->
		@style.left		= -innerWidth
		@style.top		= -innerHeight
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
		if left_offset > 0
			tooltip_position.left += left_offset
		else
			console.log tooltip_size.width / 2
			console.log element_position.left
			console.log element_position.width
			tooltip_position.arrow_left_offset	= (tooltip_size.width / 2) - element_position.left - (element_position.width / 2)
		tooltip_position
)
