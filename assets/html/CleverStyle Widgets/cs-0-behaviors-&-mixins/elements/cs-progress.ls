/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
csw.behaviors.cs-progress = [
	csw.behaviors.size
	csw.behaviors.tight
	csw.behaviors.tooltip
	csw.behaviors.inject-light-styles
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
	_value_changed : (value) !->
		if value == undefined
			return
		@firstElementChild?.value	= value
]
