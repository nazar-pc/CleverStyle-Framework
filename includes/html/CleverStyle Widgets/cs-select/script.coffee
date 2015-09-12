###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-select'
	'extends'	: 'select'
	behaviors	: [
		Polymer.cs.behaviors.size
		Polymer.cs.behaviors.tight
		Polymer.cs.behaviors.this
		Polymer.cs.behaviors.tooltip
		Polymer.cs.behaviors.value
	]
	listeners	:
		'value-changed'	: '_value_changed'
	properties	:
		selected	:
			notify		: true
			observer	: '_selected_changed'
			type		: Object
	ready : ->
		# We need to scroll because of possible changed height of `option`, so that `option[selected]` will not be visible
		scroll_once	= =>
			@_scroll_to_selected()
			document.removeEventListener('WebComponentsReady', scroll_once)
		document.addEventListener('WebComponentsReady', scroll_once)
		return
	_scroll_to_selected : ->
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
		return
	_number_of_optgroups : ->
		optgroup	= @selectedOptions[0].parentNode
		count		= 0
		if optgroup.tagName == 'OPTGROUP'
			while optgroup
				++count
				optgroup = optgroup.previousElementSibling
		count
	_value_changed : ->
		selected	= []
		[].slice.call(@selectedOptions).forEach (option) ->
			selected.push(option.value)
		if !@multiple
			selected	= selected[0] || undefined
		@set('selected', selected)
	_selected_changed : (selected) ->
		if selected == undefined
			return
		[].slice.call(@querySelectorAll('option')).forEach (option) ->
			option.selected	= selected && selected.indexOf(option.value) != -1
)
