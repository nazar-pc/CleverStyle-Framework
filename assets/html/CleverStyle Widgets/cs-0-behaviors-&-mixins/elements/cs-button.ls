/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
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
