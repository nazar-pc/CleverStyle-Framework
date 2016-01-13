/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-select = [
	Polymer.cs.behaviors.size
	Polymer.cs.behaviors.tight
	Polymer.cs.behaviors.this
	Polymer.cs.behaviors.tooltip
	listeners	:
		'value-changed'	: '_value_changed'
	properties	:
		selected	:
			notify		: true
			observer	: '_selected_changed'
			type		: Object
	ready : !->
		# We need to scroll because of possible changed height of `option`, so that `option[selected]` will not be visible
		scroll_once	= !~>
			@_scroll_to_selected()
			document.removeEventListener('WebComponentsReady', scroll_once)
		document.addEventListener('WebComponentsReady', scroll_once)
		# Hack to work nicely with `dom-repeat`-created options inside
		timeout		= null
		callback	= !~>
			clearTimeout(timeout)
			timeout	= setTimeout (!~>
				@removeEventListener(callback)
				if @selected != undefined
					@_selected_changed(@selected)
			), 100
			if @_height_updated
				return
			# Only affects selects when multiple elements shown simultaneously
			if @size <= 1
				@_height_updated	= true
				return
			if @querySelectorAll('option').length
				# Set select height relatively to font size
				# Fixes select height in modal
				height_in_px	= @querySelector('option').getBoundingClientRect().height * @size
				if height_in_px == 0
					return
				@_height_updated	= true
				font_size		= parseFloat(getComputedStyle(@).fontSize)
				@style.height	= "calc(#{height_in_px}em / #{font_size})"
		@addEventListener('dom-change', callback)
	_scroll_to_selected : !->
		option			= @querySelector('option')
		if !option
			return
		option_height	= option.getBoundingClientRect().height
		if @size > 1 && @selectedOptions[0]
			@scrollTop	= option_height * (@selectedIndex - Math.floor(@size / 2)) + @_number_of_optgroups()
		select_height	= @getBoundingClientRect().height
		# Do not use `overflow-y : auto` all the time, because it will cause cropped options on the right or horizontal scroll in Chromium
		if select_height >= option_height * (@querySelectorAll('option').length + @querySelectorAll('optgroup').length)
			@style.overflowY = 'auto'
	_number_of_optgroups : ->
		optgroup	= @selectedOptions[0].parentNode
		count		= 0
		if optgroup.tagName == 'OPTGROUP'
			while optgroup
				++count
				optgroup = optgroup.previousElementSibling
		count
	_value_changed : !->
		selected	= []
		Array::forEach.call(
			@selectedOptions
			(option) ->
				selected.push(option.value)
		)
		if !@multiple
			selected	= selected[0] || undefined
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
]
