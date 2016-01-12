/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
 * Simplified access to translations in Polymer elements
 */
cs.{}Polymer.behaviors =
	# Access to whole `window.cs` inside Polymer element (useful for using configuration in data bindings)
	cs : cs
	# This will add `Language` property (and its short alias `L`) alongside with `__()` method which can be used for formatted translations
	# Also might be called as function with prefix
	Language : class
		::				= @
		create_property	= (L) ->
			readOnly	: true
			type  		: Object
			value		: -> L
		(prefix) ~>
			@Language	= @Language(prefix)
			@properties	=
				Language	: create_property(@Language)
				L			: create_property(@Language)
			@__			= @__
		Language	: cs.Language
		properties	:
			Language	: create_property(::Language)
			L			: create_property(::Language)
		__			: (key) ->
			if arguments.length == 1
				@Language.get(key)
			else
				@Language.format(...&)
