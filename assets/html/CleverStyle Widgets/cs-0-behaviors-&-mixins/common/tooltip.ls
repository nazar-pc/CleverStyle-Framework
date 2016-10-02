/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
tooltip_element = null
Polymer.{}cs.{}behaviors.tooltip =
	properties	:
		tooltip	: String
	attached : !->
		@addEventListener('mouseover', !~function add_tooltip
			@removeEventListener('mouseover', add_tooltip)
			if @tooltip
				@_tooltip_for_element(@)
		)
	_tooltip_for_element : (element) !->
		if @_tooltip_binding_added
			return
		@_tooltip_binding_added = true
		@_initialize_tooltip()
		element.tooltip	= element.tooltip || element.getAttribute('tooltip')
		show			= @_schedule_show.bind(@, element)
		hide			= !~>
			@_cancel_schedule_show()
			tooltip_element._hide(element)
		element.addEventListener('mousemove', show)
		element.addEventListener('pointermove', show)
		element.addEventListener('mouseleave', hide)
		element.addEventListener('pointerleave', hide)
	_initialize_tooltip : !->
		if !tooltip_element
			tooltip_element := document.createElement('cs-tooltip')
			document.documentElement.appendChild(tooltip_element)
	_schedule_show : (element) !->
		@_cancel_schedule_show()
		@show_timeout = setTimeout(
			tooltip_element._show.bind(tooltip_element, element)
			100
		)
	_cancel_schedule_show : !->
		if @show_timeout
			clearTimeout(@show_timeout)
