/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
csw.behaviors.cs-label-button = [
	csw.behaviors.label
	csw.behaviors.tooltip
	csw.behaviors.inject-light-styles
	_styles_dom_module	: 'cs-label-button-styles'
	properties			:
		first	:
			reflectToAttribute	: true
			type				: Boolean
		last	:
			reflectToAttribute	: true
			type				: Boolean
		primary	:
			reflectToAttribute	: true
			type				: Boolean
	ready		: ->
		if @previousElementSibling?.is != @is
			@first = true
		if @nextElementSibling?.is != @is
			@last = true
		@querySelector('label').setAttribute('role', 'button')
		if @querySelector('input').disabled
			@querySelector('label').setAttribute('disabled', '')
]
