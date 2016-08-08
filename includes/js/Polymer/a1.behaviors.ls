/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
 * Simplified access to translations in Polymer elements
 */
window.{}cs.{}Polymer.behaviors =
	# This will add `Language` property (and its short alias `L`) alongside with `__()` method which can be used for formatted translations
	# Also might be called as function with prefix
	Language : do ->
		function Language (prefix)
			# Need to copy properties to own properties
			Object.create(Language)
				..properties				= ..properties
				.._set_language_properties	= .._set_language_properties
				.._compute__				= .._compute__
				..ready						= !->
					cs.Language.ready().then (L) !~>
						L	= L(prefix)
						@_set_language_properties(L)
		Language
			..properties				=
				Language	:
					readOnly	: true
					type  		: Object
				L			:
					readOnly	: true
					type  		: Object
				__			:
					type		: Function
					computed	: '_compute__(Language)'
			..ready						= !->
				cs.Language.ready().then (L) !~>
					@_set_language_properties(L)
			.._compute__				= (L) ->
				(key) ->
					if arguments.length == 1
						L.get(key)
					else
						L.format(...&)
			.._set_language_properties	= (L) !->
				@_setLanguage(L)
				@_setL(L)
