/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-select = [
	Polymer.cs.behaviors.ready
	Polymer.cs.behaviors.size
	Polymer.cs.behaviors.tight
	Polymer.cs.behaviors.tooltip
	Polymer.cs.behaviors.value
	Polymer.cs.behaviors.inject-light-styles
	_styles_dom_module	: 'cs-select-styles'
	properties	:
		selected	:
			notify		: true
			observer	: '_selected_changed'
			type		: Object
	ready : !->
		# We need to scroll because of possible changed height of `option`, so that `option[selected]` will not be visible
		@_when_ready(@~_scroll_to_selected)
		# Hack to work nicely with `dom-repeat`-created options inside
		timeout		= null
		callback	= !~>
			@_select	= @firstElementChild
			@_select.addEventListener('value-changed', @~_value_changed)
			if @selected == undefined
				@selected = @_select.value
			clearTimeout(timeout)
			timeout	= setTimeout (!~>
				@removeEventListener('dom-change', callback)
				if @selected != undefined
					@_selected_changed(@selected)
			), 100
			if @_height_updated
				return
			# Only affects selects when multiple elements shown simultaneously
			if @_select.size <= 1
				@_height_updated	= true
				return
			if @querySelectorAll('option').length
				# Set select height relatively to font size
				# Fixes select height in modal
				height_in_px	= @querySelector('option').getBoundingClientRect().height * @_select.size
				if height_in_px == 0
					return
				@_height_updated	= true
				font_size		= parseFloat(getComputedStyle(@_select).fontSize)
				@_select.style.height	= "calc(#{height_in_px}em / #{font_size})"
		@addEventListener('dom-change', callback)
	_scroll_to_selected : !->
		option			= @querySelector('option')
		if !option
			return
		option_height	= option.getBoundingClientRect().height
		if @_select.size > 1 && @_select.selectedOptions[0]
			@_select.scrollTop	= option_height * (@_select.selectedIndex - Math.floor(@_select.size / 2)) + @_number_of_optgroups()
		select_height	= @_select.getBoundingClientRect().height
		# Do not use `overflow-y : auto` all the time, because it will cause cropped options on the right or horizontal scroll in Chromium
		if select_height >= option_height * (@querySelectorAll('option').length + @querySelectorAll('optgroup').length)
			@_select.style.overflowY = 'auto'
	_number_of_optgroups : ->
		optgroup	= @_select.selectedOptions[0].parentNode
		count		= 0
		if optgroup.matches('optgroup')
			while optgroup
				++count
				optgroup = optgroup.previousElementSibling
		count
	_value_changed : !->
		selected	= []
		Array::forEach.call(
			@_select.selectedOptions
			(option) ->
				selected.push(option.value)
		)
		if !@_select.multiple
			selected	= selected[0]
		@set('selected', selected)
	_selected_changed : (selected) !->
		if selected == undefined
			return
		selected	=
			if selected instanceof Array
				selected.map(String)
			else
				String(selected)
		Array::forEach.call(
			@querySelectorAll('option')
			(option) ->
				option.selected	=
					selected == option.value ||
					(
						selected instanceof Array &&
						selected.indexOf(option.value) != -1
					)
		)
		@fire('selected')
]
