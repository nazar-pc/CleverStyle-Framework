/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
csw.behaviors.cs-link-button = [
	csw.behaviors.button
	csw.behaviors.tight
	csw.behaviors.tooltip
	ready		: ->
		@querySelector('a').setAttribute('role', 'button')
]
