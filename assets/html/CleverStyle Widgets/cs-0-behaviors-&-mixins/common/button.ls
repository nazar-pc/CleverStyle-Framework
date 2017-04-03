/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.{}cs.{}behaviors.button =
	properties	:
		action	: String
		active	:
			notify				: true
			reflectToAttribute	: true
			type				: Boolean
		bind	: Object
		compact	:
			reflectToAttribute	: true
			type				: Boolean
		primary	:
			reflectToAttribute	: true
			type				: Boolean
	listeners	:
		tap	: '_tap'
	_tap : ->
		if @bind && @action
			@bind[@action]()
