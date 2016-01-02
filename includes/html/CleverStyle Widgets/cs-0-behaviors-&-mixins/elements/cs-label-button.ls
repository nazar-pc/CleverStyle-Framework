/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-label-button = [
	Polymer.cs.behaviors.label
	Polymer.cs.behaviors.this
	Polymer.cs.behaviors.tooltip
	hostAttributes	:
		role	: 'button'
	properties		:
		first	:
			reflectToAttribute	: true
			type				: Boolean
		last	:
			reflectToAttribute	: true
			type				: Boolean
	ready		: ->
		if @previousElementSibling?.is != @is
			@first = true
		if @nextElementSibling?.getAttribute('is') != @is
			@last = true
]
