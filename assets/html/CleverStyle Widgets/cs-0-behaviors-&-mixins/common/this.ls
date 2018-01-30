/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
csw.behaviors.this =
	properties	:
		this	:
			notify		: true
			readOnly	: true
			type		: Object
	attached : !->
		if !@this
			@_setThis(@)
