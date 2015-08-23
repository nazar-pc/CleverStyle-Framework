###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-nav-tabs'
	'extends'	: 'nav'
	properties	:
		active	:
			observer	: 'active_changed'
			type		: Number
	ready : ->
		@addEventListener('tap', @click.bind(@))
		@addEventListener('click', @click.bind(@))
		do =>
			for element in @children
				if element.active
					return
			@active = 0
			return
	click : (e) ->
		for element, index in @children
			if element.tagName == 'TEMPLATE'
				continue
			if element == e.target
				@active = index
	active_changed : ->
		for element, index in @children
			if element.tagName == 'TEMPLATE'
				continue
			element.active = index == @active
		return
)
