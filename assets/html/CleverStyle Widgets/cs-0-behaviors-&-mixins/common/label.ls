/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
csw.behaviors.label =
	properties	:
		active	:
			notify				: true
			observer			: '_active_changed'
			reflectToAttribute	: true
			type				: Boolean
		value	:
			notify		: true
			observer	: '_value_changed'
			type		: String
	attached : !->
		do !~>
			next_node	= @nextSibling
			if next_node && next_node.nodeType == Node.TEXT_NODE && next_node.nextSibling?.matches?(@is)
				next_node.parentNode.removeChild(next_node)
		@local_input		= @querySelector('input')
		@local_input.label	= @
		@active				= @local_input.checked
		inputs				= @_get_inputs()
		if @value != undefined
			@_value_changed(@value)
		for input in inputs
			let (input = input)
				input.addEventListener('change', !~>
					if @local_input.type == 'radio'
						@value					= input.value
						@active					= @local_input.value ~= input.value
						@local_input.checked	= @local_input.value ~= input.value
					else
						# For checkbox just alternating active property is enough
						@active					= !@active
				)
				if input.checked
					@value	= input.value
		@local_input.addEventListener('focus', !~>
			@focus = true
		)
		@local_input.addEventListener('blur', !~>
			@focus = false
		)
	_get_inputs : ->
		if @local_input.name
			@parentNode.querySelectorAll('input[name="' + @local_input.name + '"]')
		else
			@_inputs_around()
	_inputs_around : ->
		inputs	= []
		inputs.push(@local_input)
		label	= @
		while label = label.previousElementSibling
			if !label.matches(@is)
				break
			input	= label.querySelector('input')
			if !input
				break
			inputs.push(input)
		label	= @
		while label = label.nextElementSibling
			if !label.matches(@is)
				break
			input	= label.querySelector('input')
			if !input
				break
			inputs.push(input)
		inputs
	_active_changed : (active) !->
		if active == undefined
			return
		# If checked state is already correct - skip, just micro optimization
		if @local_input.checked == active
			return
		if @local_input.type == 'radio'
			# Simulate regular click for simplicity
			if active
				@click()
		else
			# For checkbox just set checked property is enough
			@local_input.checked = active
	_value_changed : (value) !->
		if value == undefined
			return
		if @local_input
			for input in @_get_inputs()
				state				= input.value ~= value
				input.checked		= state
				input.label?.active	= state
