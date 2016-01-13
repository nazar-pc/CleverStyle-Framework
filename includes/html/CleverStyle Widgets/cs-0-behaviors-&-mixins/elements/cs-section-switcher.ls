/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-section-switcher = [
	Polymer.cs.behaviors.this
	properties	:
		selected	:
			notify		: true
			observer	: '_selected_changed'
			type		: Number
	ready : !->
		for element in @children
			if element.active
				return
		if !@selected
			@selected = 0
	_selected_changed : !->
		for element, index in @children
			if element.tagName == 'TEMPLATE'
				continue
			element.active = index == @selected
			if index == @selected
				element.setAttribute('active', '')
			else
				element.removeAttribute('active')
]
