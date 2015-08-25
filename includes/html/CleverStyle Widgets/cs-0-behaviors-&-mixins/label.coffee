###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer.cs.behaviors.label =
	properties	:
		active	:
			notify				: true
			observer			: '_active_changed'
			reflectToAttribute	: true
			type				: Boolean
		focus	:
			reflectToAttribute	: true
			type				: Boolean
		value	:
			notify	: true
			type	: String
	# TODO: Should be `ready` according to Polymer docs, but not working as expected (see https://github.com/Polymer/polymer/issues/2075)
	attached : ->
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
		@local_input.addEventListener('focus', =>
			@focus = true
		)
		@local_input.addEventListener('blur', =>
			@focus = false
		)
		return
	_active_changed : ->
		if @local_input.type == 'radio'
			# Simulate regular click for simplicity
			if @active
				@click()
		else
			# For checkbox just set checked property is enough
			@local_input.checked = @active
