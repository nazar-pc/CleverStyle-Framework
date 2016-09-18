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
get_formatted		= ->
	'' + (if &length then vsprintf(@, [...&]) else @)
fill_translations	= (translations) !->
	for key, value of translations
		Language[key]			= get_formatted.bind(value)
		Language[key].toString	= Language[key]
cs.Language		= Language
	..get	= (key) ->
		@[key].toString()
	..format	= (key, ...args) ->
		@[key](...args)
	..ready	= ->
		ready	= new Promise (resolve) !->
			if translations
				fill_translations(translations)
				is_ready	:= true
				resolve(Language)
			else
				translations <-! require(["storage/pcache/languages-#{cs.current_language.language}-#{cs.current_language.hash}"], _)
				fill_translations(translations)
				is_ready	:= true
				resolve(Language)
		@ready	= -> ready
		ready
