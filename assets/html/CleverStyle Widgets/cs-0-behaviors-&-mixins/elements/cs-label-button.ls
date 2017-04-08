/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-label-button = [
	Polymer.cs.behaviors.label
	Polymer.cs.behaviors.tooltip
	Polymer.cs.behaviors.inject-light-styles
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
]
