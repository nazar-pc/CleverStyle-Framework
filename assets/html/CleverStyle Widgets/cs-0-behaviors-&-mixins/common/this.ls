/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.{}cs.{}behaviors.this =
	properties	:
		this	:
			notify		: true
			readOnly	: true
			type		: Object
	attached : !->
		if !@this
			@_setThis(@)
