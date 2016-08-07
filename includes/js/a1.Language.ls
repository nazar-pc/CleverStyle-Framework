/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
# TODO: Remove in 6.x when translations will be loaded asynchronously
translations	= cs.Language
function Language (prefix)
	prefix_length	= prefix.length
	prefixed		= Object.create(Language)
	for key of Language
		if key.indexOf(prefix) == 0
			prefixed[key.substr(prefix_length)] = Language[key]
	prefixed
cs.Language	= Language
	..get	= (key) ->
		@[key].toString()
	..format	= (key, ...args) ->
		@[key](...args)
	..ready	= ->
		ready	= new Promise (resolve) !->
			for let key, value of translations
				Language[key]			= ->
					vsprintf(value, [...&])
				Language[key].toString	= ->
					value
			resolve(Language)
		@ready	= -> ready
		ready
# TODO: This is a transitional hack till 6.x, when translations will be loaded asynchronously
	..ready()
