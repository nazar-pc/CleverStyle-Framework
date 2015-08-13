###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
###*
 * Simplified access to translations in Polymer elements
###
Polymer.Base._addFeature(
	behaviors : [
		properties	:
			L :
				readOnly	: true
				type  		: Object
				value		: cs.Language
		__			: (key) ->
			if arguments.length == 1
				cs.Language.get(key)
			else
				cs.Language.format.apply(cs.Language, arguments)
	]
)
