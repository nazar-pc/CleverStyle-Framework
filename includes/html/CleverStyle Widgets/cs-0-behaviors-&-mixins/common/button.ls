/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.{}cs.{}behaviors.button =
	properties	:
		action			: String
		active			:
			notify				: true
			reflectToAttribute	: true
			type				: Boolean
		bind			: Object
		empty			:
			reflectToAttribute	: true
			type				: Boolean
		force-fullsize	:
			reflectToAttribute	: true
			type				: Boolean
		icon			:
			reflectToAttribute	: true
			type				: String
		icon-after		:
			reflectToAttribute	: true
			type				: String
		primary			:
			reflectToAttribute	: true
			type				: Boolean
	listeners	:
		tap	: '_tap'
	attached : !->
		@empty = !@childNodes.length
	_tap : ->
		if @bind && @action
			@bind[@action]()
