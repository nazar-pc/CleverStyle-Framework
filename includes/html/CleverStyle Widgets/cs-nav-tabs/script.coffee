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
		if !@querySelector('button[active]')
			@active = 0
	click : (e) ->
		buttons = @querySelectorAll('button')
		for button, index in buttons
			if button == e.target
				@active = index
	active_changed : ->
		buttons = @querySelectorAll('button')
		for button, index in buttons
			if index == @active
				button.active = true
			else
				button.active = false
)
