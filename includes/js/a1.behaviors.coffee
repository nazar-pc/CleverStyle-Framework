###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
###*
 * Simplified access to translations in Polymer elements
###
cs.Polymer	= cs.Polymer || {}
(->
	# This will add `Language` property (and its short alias `L`) alongside with `__()` method which can be used for formatted translations
	@Language	=
		properties	:
			L			:
				readOnly	: true
				type  		: Object
				value		: cs.Language
			Language	:
				readOnly	: true
				type  		: Object
				value		: cs.Language
		__			: (key) ->
			if arguments.length == 1
				cs.Language.get(key)
			else
				cs.Language.format.apply(cs.Language, arguments)
).call(cs.Polymer.behaviors = {})
