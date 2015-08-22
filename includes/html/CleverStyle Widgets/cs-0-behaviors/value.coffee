###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer.cs.behaviors.value =
	ready : ->
		@addEventListener('change', =>
			@fire('value-changed')
		)
		@addEventListener('input', =>
			@fire('value-changed')
		)
