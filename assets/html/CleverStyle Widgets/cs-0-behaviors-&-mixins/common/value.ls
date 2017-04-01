/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.value =
	ready : !->
		#TODO: Should always be firstChild when everything converted to wrapper elements
		if this.extends
			target	= @
		else
			target	= @firstChild
		target
			..addEventListener('change', !->
				@dispatchEvent(new CustomEvent('value-changed'))
			)
			..addEventListener('input', !->
				@dispatchEvent(new CustomEvent('value-changed'))
			)
