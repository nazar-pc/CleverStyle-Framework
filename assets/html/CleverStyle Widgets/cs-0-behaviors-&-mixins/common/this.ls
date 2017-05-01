/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
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
