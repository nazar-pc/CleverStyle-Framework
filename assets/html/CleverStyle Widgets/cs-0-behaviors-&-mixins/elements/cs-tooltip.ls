/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
html						= document.documentElement
csw.behaviors.cs-tooltip	= [
	csw.behaviors.tooltip
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
	listeners	:
		mouseenter		: '_set_show'
		pointerenter	: '_set_show'
		mouseleave		: '_unset_show'
		pointerleave	: '_unset_show'
	attached : !->
		parent	= @parentNode
		if !parent.matches('html')
			parent.removeChild(@)
			parent.addEventListener('mouseover', !~function add_tooltip
				parent.removeEventListener('mouseover', add_tooltip)
				@_tooltip_for_element(parent)
			)
		else
			document.addEventListener('keydown', (e) !~>
				if e.keyCode == 27 && @show # Esc
					@show = false
			)
	_set_show : !->
		if @reset_show
			@reset_show	= false
			@show		= true
	_unset_show : !->
		@show = false
	_show : (element) !->
		@reset_show = true
		if !element.tooltip || @show
			return
		@_update_content(element.tooltip)
		tooltip_position	= @_get_tooltip_position(element)
		@style
			..top	= tooltip_position.top + 'px'
			..left	= tooltip_position.left + 'px'
		@top				= tooltip_position.arrow_top
		@$.arrow.style
			..left	= -tooltip_position.arrow_left_offset + 'px'
			..right	= tooltip_position.arrow_left_offset + 'px'
		@show				= true
	_hide : !->
		if @show
			@show	= false
	_get_tooltip_position : (element) ->
		@showQuick			= true
		tooltip_size		= @getBoundingClientRect()
		element_position	= element.getBoundingClientRect()
		tooltip_position	=
			top					: scrollY
			left				: scrollX
			arrow_top			: false
			arrow_left_offset	: 0
		client_width		= html.clientWidth
		@showQuick			= false
		# Calculation of vertical position
		if element_position.top > tooltip_size.height
			tooltip_position.top	+= element_position.top - tooltip_size.height
		else
			tooltip_position.arrow_top	= true
			tooltip_position.top		+= element_position.bottom
		# Calculation of horizontal position
		left_offset	= element_position.left + (element_position.width / 2) - (tooltip_size.width / 2)
		if left_offset >= 0
			if left_offset + tooltip_size.width <= client_width
				tooltip_position.left += left_offset
			else
				tooltip_position.left				+= client_width - tooltip_size.width
				tooltip_position.arrow_left_offset	= client_width - (tooltip_size.width / 2) - element_position.left - (element_position.width / 2)
		else
			tooltip_position.arrow_left_offset	= (tooltip_size.width / 2) - element_position.left - (element_position.width / 2)
		tooltip_position
	_update_content : (content) !->
		if @innerHTML != content
			@innerHTML = content
]
