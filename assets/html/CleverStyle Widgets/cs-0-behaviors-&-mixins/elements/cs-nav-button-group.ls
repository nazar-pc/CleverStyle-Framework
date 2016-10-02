/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-nav-button-group = [
	Polymer.cs.behaviors.this
	hostAttributes	:
		role	: 'group'
	properties		:
		vertical	:
			reflectToAttribute	: true
			type				: Boolean
]
