/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
csw.behaviors.cs-label-switcher = [
	csw.behaviors.label
	csw.behaviors.tooltip
	csw.behaviors.inject-light-styles
	_styles_dom_module	: 'cs-label-switcher-styles'
	attached : !->
		if @querySelector('cs-icon')
			return
		@querySelector('input').insertAdjacentHTML(
			'afterend'
			'<cs-icon icon="check" mono></cs-icon>'
		)
		if @querySelector('input').disabled
			@querySelector('label').setAttribute('disabled', '')
]
