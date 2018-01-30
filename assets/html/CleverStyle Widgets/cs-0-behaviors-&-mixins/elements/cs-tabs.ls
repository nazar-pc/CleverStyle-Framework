/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
csw.behaviors.cs-tabs = [
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
			for element, index in e.composedPath()
				if element == @
					# `-3` because `-1` is Shadow Root and `-2` is `<content>` element
					return e.composedPath()[index - 3]
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
	_selected_changed : (selected) !->
		if selected == undefined
			return
		for element, index in @children
			if element.matches('template')
				continue
			element.active = index == selected
			if index == selected
				element.setAttribute('active', '')
			else
				element.removeAttribute('active')
		if @nextElementSibling?.matches?('cs-switcher')
			@nextElementSibling.selected = selected
]
