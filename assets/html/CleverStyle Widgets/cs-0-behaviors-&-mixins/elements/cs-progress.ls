/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-progress = [
	Polymer.cs.behaviors.full-width
	Polymer.cs.behaviors.tight
	Polymer.cs.behaviors.tooltip
	Polymer.cs.behaviors.inject-light-styles
	_styles_dom_module	: 'cs-progress-styles'
	properties	:
		infinite		: Boolean
		primary			:
			reflectToAttribute	: true
			type				: Boolean
		text-progress	:
			type				: Boolean
			value				: false
		value			:
			observer			: '_value_changed'
			reflectToAttribute	: true
			type				: Number
	attached : !->
		if !@firstElementChild.getAttribute('max')
			@firstElementChild.max = 100
		value = @firstElementChild.getAttribute('value')
		if !@value
			@value = value || 0
		else
			@firstElementChild.setAttribute('value', @value)
		if @infinite
			update_progress	= !~>
				if !@parentNode
					return
				@value						= (@value + 9) % @firstElementChild.max
				@firstElementChild.value	= @value
				setTimeout(update_progress, 200)
			update_progress()
	_value_changed : !->
		@firstElementChild?.value	= @value
]
