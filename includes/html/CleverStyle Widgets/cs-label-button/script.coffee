###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-label-button'
	'extends'	: 'label'
	properties	:
		active	:
			observer			: 'active_changed'
			reflectToAttribute	: true
			type				: Boolean
		first	:
			reflectToAttribute	: true
			type				: Boolean
		focus	:
			reflectToAttribute	: true
			type				: Boolean
		last	:
			reflectToAttribute	: true
			type				: Boolean
		value	: String
	ready : ->
		do =>
			next_node	= @nextSibling
			if next_node.nodeType == Node.TEXT_NODE && next_node.nextSibling?.getAttribute('is') == @is
				next_node.parentNode.removeChild(next_node)
		@local_input	= @querySelector('input')
		@active			= @local_input.checked
		inputs			= @.parentNode.querySelectorAll('input[name="' + @local_input.name + '"]')
		for input in inputs
			do (input = input) =>
				input.addEventListener('change', =>
					@value	= input.value
					@active	= @local_input.checked
					return
				)
				return
		if @previousElementSibling?.is != @is
			@first = true
		if @nextElementSibling?.getAttribute('is') != @is
			@last = true
		@local_input.addEventListener('focus', =>
			@focus = true
		)
		@local_input.addEventListener('blur', =>
			@focus = false
		)
		return
	active_changed : ->
		if @local_input.type == 'radio'
			# Simulate regular click for simplicity
			if @active
				@click()
		else
			# For checkbox just set checked property is enough
			@local_input.checked = @active
)
