/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-button = [
	Polymer.cs.behaviors.button
	Polymer.cs.behaviors.this
	Polymer.cs.behaviors.tight
	Polymer.cs.behaviors.tooltip
	properties	:
		action	: String
		bind	: Object
	listeners	:
		tap	: '_tap'
	_tap : ->
		if @bind && @action
			@bind[@action]()
]
