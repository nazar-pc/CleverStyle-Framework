/**
 * @package CleverStyle Widgets
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
csw.behaviors.value =
	ready : !->
		@firstElementChild
			..addEventListener('change', !->
				@dispatchEvent(new CustomEvent('value-changed'))
			)
			..addEventListener('input', !->
				@dispatchEvent(new CustomEvent('value-changed'))
			)
