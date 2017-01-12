/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
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
var vsprintf
get_formatted		= ->
	'' + (if &length then vsprintf(@, [...&]) else @)
fill_translations	= (translations) !->
	for key, value of translations
		# Only create functions where string contains formatting; this optimization makes loop ~2x faster
		if value.indexOf('%') == -1
			Language[key]			= value
		else
			Language[key]			= get_formatted.bind(value)
			Language[key].toString	= Language[key]
cs.Language		= Language
	..get	= (key) ->
		@[key].toString()
	..format	= (key, ...args) ->
		@[key](...args)
	..ready	= ->
		ready	= new Promise (resolve) !->
			Promise.all([
				if translations
					[translations]
				else
					require(["storage/public_cache/#{cs.current_language.hash}"])
				require(['sprintf-js'])
			]).then ([[translations], [sprintfjs]]) !->
				fill_translations(translations)
				vsprintf	:= sprintfjs.vsprintf
				is_ready	:= true
				resolve(Language)
		@ready	= -> ready
		ready.then !->
			translations := void
		ready
