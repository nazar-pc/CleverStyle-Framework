###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-section-switcher'
	'extends'	: 'section'
	behaviors	: [Polymer.cs.behaviors.this]
	properties	:
		selected	:
			notify		: true
			observer	: '_selected_changed'
			type		: Number
	ready : ->
		do =>
			for element in @children
				if element.active
					return
			@selected = 0
			return
	_selected_changed : ->
		for element, index in @children
			if element.tagName == 'TEMPLATE'
				continue
			element.active = index == @selected
			if index == @selected
				element.setAttribute('active', '')
			else
				element.removeAttribute('active')
		return
)
