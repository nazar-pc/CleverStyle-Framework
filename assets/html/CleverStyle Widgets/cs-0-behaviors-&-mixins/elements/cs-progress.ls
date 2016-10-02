/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-progress = [
	Polymer.cs.behaviors.tight
	Polymer.cs.behaviors.tooltip
	properties	:
		infinite		: Boolean
		textProgress	: Boolean # Chromium only
	created		: !->
		if !@getAttribute('max')
			@max = 100
		if @value
			@setAttribute('value', @value)
	attached : !->
		if @infinite
			update_progress	= !~>
				if !@parentNode
					return
				@value = (@value + 9) % 100
				setTimeout(update_progress, 200)
			update_progress()
	attributeChanged : (name) !->
		if name == 'value' && @textProgress
			@setAttribute('text', @[name] + '%')
]
