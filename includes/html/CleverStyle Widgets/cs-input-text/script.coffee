###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-input-text'
	'extends'	: 'input'
	properties	:
		compact		:
			reflectToAttribute	: true
			type				: Boolean
		fullWidth	:
			reflectToAttribute	: true
			type				: Boolean
		tight		: Boolean
	ready : ->
		if @tight && @nextSibling.nodeType == Node.TEXT_NODE
			@nextSibling.parentNode.removeChild(@nextSibling)
		@addEventListener('change', =>
			@fire('value-changed')
		)
		@addEventListener('input', =>
			@fire('value-changed')
		)
)
