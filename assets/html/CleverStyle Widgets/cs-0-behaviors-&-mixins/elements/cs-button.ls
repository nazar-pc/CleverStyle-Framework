/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
csw.behaviors.cs-button = [
	csw.behaviors.button
	csw.behaviors.tight
	csw.behaviors.tooltip
	properties	:
		action	: String
		bind	: Object
	listeners	:
		tap	: '_tap'
	_tap : ->
		if @bind && @action
			@bind[@action]()
]
