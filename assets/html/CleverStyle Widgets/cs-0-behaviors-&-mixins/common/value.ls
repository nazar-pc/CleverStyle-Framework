/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
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
