/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-nav-tabs = [
	Polymer.cs.behaviors.this
	hostAttributes	:
		role	: 'group'
	properties		:
		selected	:
			notify		: true
			observer	: '_selected_changed'
			type		: Number
	listeners		:
		tap		: '_tap'
	ready : !->
		for element in @children
			if element.active
				return
		if !@selected
			@selected = 0
	_tap : (e) !->
		target = do ~>
			for path, index in e.path
				if path == @
					# `-3` because `-1` is Shadow Root and `-2` is `<content>` element
					return e.path[index - 3]
		if !target
			return
		for element, index in @children
			if element.matches('template')
				continue
			if element == target
				@selected = index
				element.setAttribute('active', '')
			else
				element.removeAttribute('active')
	_selected_changed : !->
		for element, index in @children
			if element.matches('template')
				continue
			element.active = index == @selected
			if index == @selected
				element.setAttribute('active', '')
			else
				element.removeAttribute('active')
		if @nextElementSibling?.is == 'cs-section-switcher'
			@nextElementSibling.selected = @selected
]
