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
		Polymer.cs.behaviors.value
		Polymer.cs.behaviors.tight
		Polymer.cs.behaviors.size
	]
	ready : ->
		# We need to scroll because oof possible changed height of `option`, so that `option[selected]` will not be visible
		scroll_once	= =>
			@_scroll_to_selected()
			document.removeEventListener('WebComponentsReady', scroll_once)
		document.addEventListener('WebComponentsReady', scroll_once)
	_scroll_to_selected : ->
		if @size > 1
			option_height	= parseFloat(getComputedStyle(@selectedOptions[0]).height)
			@scrollTop		= option_height * (@selectedIndex - Math.floor(@size / 2)) + @_number_of_optgroups()
	_number_of_optgroups : ->
		optgroup	= @selectedOptions[0].parentNode
		count		= 0
		if optgroup.tagName == 'OPTGROUP'
			while optgroup
				++count
				optgroup = optgroup.previousElementSibling
		count
)
