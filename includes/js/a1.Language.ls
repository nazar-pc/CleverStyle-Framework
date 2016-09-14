/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
# TODO: Remove in 6.x when translations will be loaded asynchronously
translations	= cs.Language
is_ready		= false
fill_prefixed	= (prefix) !->
	prefix_length	= prefix.length
	for key of Language
		if key.indexOf(prefix) == 0
			@[key.substr(prefix_length)] = Language[key]
function Language (prefix)
	prefixed		= Object.create(Language)
	prefixed.ready	= ->
		Language.ready().then -> prefixed
	if is_ready
		fill_prefixed.call(prefixed, prefix)
	else
		Language.ready().then(fill_prefixed.bind(prefixed, prefix))
	prefixed
get_formatted	= ->
	'' + (if &length then vsprintf(@, [...&]) else @)
cs.Language		= Language
	..get	= (key) ->
		@[key].toString()
	..format	= (key, ...args) ->
		@[key](...args)
	..ready	= ->
		ready	= new Promise (resolve) !->
			for key, value of translations
				Language[key]			= get_formatted.bind(value)
				Language[key].toString	= Language[key]
			is_ready	:= true
			resolve(Language)
		@ready	= -> ready
		ready
# TODO: This is a transitional hack till 6.x, when translations will be loaded asynchronously
	..ready()
