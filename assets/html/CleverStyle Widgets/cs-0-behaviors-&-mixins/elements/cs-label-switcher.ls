/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-label-switcher = [
	Polymer.cs.behaviors.label
	Polymer.cs.behaviors.tooltip
	Polymer.cs.behaviors.inject-light-styles
	_styles_dom_module	: 'cs-label-switcher-styles'
	ready : !->
		@querySelector('input').insertAdjacentHTML?(
			'afterend'
			'<cs-icon icon="check" mono></cs-icon>'
		)
		if @querySelector('input').disabled
			@querySelector('label').setAttribute('disabled', '')
]
