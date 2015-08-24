###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
tooltip_element = null
Polymer.cs.behaviors.tooltip =
	properties :
		tooltip	:
			observer	: '_tooltip_changed'
			type		: String
	_tooltip_changed : ->
		if @tooltip
			@_tooltip_for_element(@)
		return
	_tooltip_for_element : (element) ->
		if @_tooltip_binding_added
			return
		@_tooltip_binding_added = true
		@_initialize_tooltip()
		show	= tooltip_element._show.bind(tooltip_element, element)
		hide	= tooltip_element._hide.bind(tooltip_element, element)
		element.addEventListener('mouseenter', show)
		element.addEventListener('pointerenter', show)
		element.addEventListener('mouseleave', hide)
		element.addEventListener('pointerleave', hide)
		return
	_initialize_tooltip : ->
		if !tooltip_element
			tooltip_element = document.createElement('cs-tooltip')
			document.body.parentNode.appendChild(tooltip_element)
		return
