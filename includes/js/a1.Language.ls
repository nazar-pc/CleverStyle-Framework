/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
# Object with translations, also might be called as function with prefix
cs.Language = class Language
	::	= @
	(prefix) ~>
		prefix_length = prefix.length
		for key of @@
			if key.indexOf(prefix) == 0
				@[key.substr(prefix_length)] = @@[key]
	get			: (key) ->
		@[key].toString()
	format		: (key, ...args) ->
		@[key](...args)
	for let key, translation of cs.Language
		::[key]				= ->
			vsprintf(translation, [...&])
		::[key].toString	= -> translation
